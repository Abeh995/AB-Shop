<?php
/**
 * Pricing service — the single place that changes a product's or variant's
 * cost_price/price and, in the same breath, writes an immutable
 * price_history row. Nothing else in the codebase should UPDATE those
 * columns directly, or the change goes unaudited.
 *
 * Concurrency: each individual price change is its own transaction that
 * reads the current value with SELECT ... FOR UPDATE before computing the
 * new one, so two admins changing the same product at the same moment
 * can't produce a lost update.
 *
 * Bulk operations are NOT one all-or-nothing transaction across every
 * selected product: each product is its own atomic change, and the bulk
 * operation reports which products succeeded and which were skipped (and
 * why). This is deliberate — with dozens of products in one request, an
 * unrelated failure on one row (e.g. a concurrent edit) shouldn't discard
 * every other row's already-valid change.
 */

/**
 * Compute a new value plus the audit fields (amount/percentage changed)
 * for a single price change, without touching the database.
 *
 * @param string $method 'fixed_amount' | 'percentage' | 'direct_value'
 * @param int|null $previousValue Current value in the database (null if never set)
 * @param float $inputValue The admin's input: a Toman amount, a percentage, or the new absolute price
 * @return array{new_value:int, change_amount:int, change_percentage:?float}
 */
function computeNewPrice(string $method, ?int $previousValue, float $inputValue, int $roundingStep = 0): array
{
    $base = $previousValue ?? 0;

    switch ($method) {
        case 'percentage':
            $newValue = (int) round($base * (1 + $inputValue / 100));
            break;
        case 'direct_value':
            $newValue = (int) round($inputValue);
            break;
        case 'fixed_amount':
        default:
            $newValue = (int) round($base + $inputValue);
            break;
    }
    $newValue = max(0, $newValue);

    if ($roundingStep > 0 && $newValue > 0) {
        $newValue = (int) round($newValue / $roundingStep) * $roundingStep;
        $newValue = max(0, $newValue);
    }

    $changeAmount = $newValue - $base;
    $changePercentage = $base > 0 ? round(($changeAmount / $base) * 100, 4) : null;

    return ['new_value' => $newValue, 'change_amount' => $changeAmount, 'change_percentage' => $changePercentage];
}

/**
 * Change one product's (or, if $variantId is given, one variant's)
 * cost_price or sale price, and record the change in price_history.
 * Wrapped in its own transaction with a row lock on the target so
 * concurrent changes to the same row serialize instead of racing.
 *
 * @param string $field 'cost_price' | 'sale_price' — 'sale_price' maps to the `price` column
 * @return array{ok:bool, error?:string, previous_value?:?int, new_value?:int}
 */
function recordPriceChange(
    int $productId,
    ?int $variantId,
    string $field,
    string $method,
    float $inputValue,
    int $adminId,
    ?string $reason = null,
    ?int $bulkOperationId = null,
    int $roundingStep = 0
): array {
    $column = $field === 'cost_price' ? 'cost_price' : 'price';
    $pdo = db();

    $pdo->beginTransaction();
    try {
        if ($variantId) {
            $stmt = $pdo->prepare("SELECT $column, size, color FROM product_variants WHERE id = ? AND product_id = ? FOR UPDATE");
            $stmt->execute([$variantId, $productId]);
            $row = $stmt->fetch();
            if (!$row) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'واریانت مورد نظر پیدا نشد.'];
            }
            $variantLabel = trim(($row['size'] ?? '') . ' ' . ($row['color'] ?? '')) ?: null;
        } else {
            $stmt = $pdo->prepare("SELECT $column FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$productId]);
            $row = $stmt->fetch();
            if (!$row) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'محصول مورد نظر پیدا نشد.'];
            }
            $variantLabel = null;
        }

        $previousValue = $row[$column] !== null ? (int) $row[$column] : null;
        $result = computeNewPrice($method, $previousValue, $inputValue, $roundingStep);

        if ($variantId) {
            $pdo->prepare("UPDATE product_variants SET $column = ? WHERE id = ?")->execute([$result['new_value'], $variantId]);
        } else {
            $pdo->prepare("UPDATE products SET $column = ? WHERE id = ?")->execute([$result['new_value'], $productId]);
        }

        $historyField = $field === 'cost_price' ? 'cost_price' : 'sale_price';
        $insert = $pdo->prepare("
            INSERT INTO price_history
                (product_id, variant_id, variant_label, field_changed, previous_value, new_value,
                 change_amount, change_percentage, method, reason, bulk_operation_id, admin_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->execute([
            $productId, $variantId, $variantLabel, $historyField,
            $previousValue, $result['new_value'], $result['change_amount'], $result['change_percentage'],
            $method, $reason, $bulkOperationId, $adminId,
        ]);

        $pdo->commit();
        return ['ok' => true, 'previous_value' => $previousValue, 'new_value' => $result['new_value']];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'خطای پایگاه‌داده: ' . $e->getMessage()];
    }
}

/**
 * Apply the same price change to an arbitrary set of products with optional
 * variant synchronization and negative margin guards.
 *
 * @param int[] $productIds
 * @return array{bulk_operation_id:int, succeeded:int, skipped:array<int,string>, ok:bool, error?:string}
 */
function applyBulkPriceChange(
    array $productIds,
    string $field,
    string $method,
    float $inputValue,
    int $adminId,
    ?string $reason = null,
    int $roundingStep = 0,
    bool $applyToVariants = false,
    bool $allowNegativeMargin = false
): array {
    if (empty($productIds)) {
        return ['ok' => false, 'error' => 'هیچ محصولی انتخاب نشده است.', 'bulk_operation_id' => 0, 'succeeded' => 0, 'skipped' => []];
    }

    // Safety guard: prevent selling below cost price unless explicitly allowed
    if ($field === 'sale_price' && !$allowNegativeMargin) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $checkStmt = db()->prepare("SELECT id, name, price, cost_price FROM products WHERE id IN ($placeholders)");
        $checkStmt->execute($productIds);
        $checkRows = $checkStmt->fetchAll();

        $negativeCount = 0;
        foreach ($checkRows as $r) {
            $cost = $r['cost_price'] !== null ? (int) $r['cost_price'] : 0;
            if ($cost > 0) {
                $computed = computeNewPrice($method, (int) $r['price'], $inputValue, $roundingStep);
                if ($computed['new_value'] < $cost) {
                    $negativeCount++;
                }
            }
        }

        if ($negativeCount > 0) {
            return [
                'ok' => false,
                'error' => 'خطای ایمنی مالی: تعداد ' . toPersianDigits((string) $negativeCount) . ' محصول با حاشیه سود منفی (قیمت فروش کمتر از بهای تمام‌شده) مواجه خواهند شد و تایید صریح ارسال نشده است.',
                'bulk_operation_id' => 0,
                'succeeded' => 0,
                'skipped' => [],
            ];
        }
    }

    // Construct human-readable formula representation for audit log
    $baseChange = match ($method) {
        'percentage'    => ($inputValue >= 0 ? '+' : '') . rtrim(rtrim(number_format($inputValue, 2, '.', ''), '0'), '.') . '%',
        'direct_value'  => '=' . number_format($inputValue, 0, '.', ''),
        default         => ($inputValue >= 0 ? '+' : '') . number_format($inputValue, 0, '.', ''),
    };

    $suffix = '';
    if ($roundingStep > 0) {
        $suffix .= ' (رند:' . number_format($roundingStep) . ')';
    }
    if ($applyToVariants) {
        $suffix .= ' +تنوع‌ها';
    }

    $requestedChange = mb_substr($baseChange . $suffix, 0, 40);

    $opStmt = db()->prepare("
        INSERT INTO bulk_price_operations (admin_id, field_changed, method, requested_change, reason, product_count)
        VALUES (?, ?, ?, ?, ?, 0)
    ");
    $opStmt->execute([$adminId, $field === 'cost_price' ? 'cost_price' : 'sale_price', $method, $requestedChange, $reason]);
    $bulkId = (int) db()->lastInsertId();

    $succeeded = 0;
    $skipped = [];
    foreach ($productIds as $productId) {
        $result = recordPriceChange((int) $productId, null, $field, $method, $inputValue, $adminId, $reason, $bulkId, $roundingStep);
        if ($result['ok']) {
            $succeeded++;

            // Synchronize variants if requested
            if ($applyToVariants) {
                $varStmt = db()->prepare("SELECT id FROM product_variants WHERE product_id = ?");
                $varStmt->execute([$productId]);
                $variantIds = $varStmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($variantIds as $vId) {
                    recordPriceChange((int) $productId, (int) $vId, $field, $method, $inputValue, $adminId, $reason, $bulkId, $roundingStep);
                }
            }
        } else {
            $skipped[(int) $productId] = $result['error'];
        }
    }

    db()->prepare("UPDATE bulk_price_operations SET product_count = ? WHERE id = ?")->execute([$succeeded, $bulkId]);

    return ['ok' => true, 'bulk_operation_id' => $bulkId, 'succeeded' => $succeeded, 'skipped' => $skipped];
}

/**
 * A product's full price_history, newest first, with the admin's username
 * joined in for display.
 */
function getProductPriceHistory(int $productId): array
{
    $stmt = db()->prepare("
        SELECT ph.*, a.username AS admin_username
        FROM price_history ph
        LEFT JOIN admins a ON a.id = ph.admin_id
        WHERE ph.product_id = ?
        ORDER BY ph.created_at DESC, ph.id DESC
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

/**
 * Fetch products list for bulk pricing selection with advanced filtering.
 *
 * @param array|string $filters Array of [search, category_id, cost_status] or search string
 */
function getBulkPricingCandidates(array|string $filters = []): array
{
    if (is_string($filters)) {
        $filters = ['search' => $filters];
    }

    $search = trim($filters['search'] ?? '');
    $categoryId = !empty($filters['category_id']) ? (int) $filters['category_id'] : null;
    $costStatus = trim($filters['cost_status'] ?? '');

    $where = 'p.is_active = 1';
    $params = [];

    if ($search !== '') {
        $where .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    if ($categoryId !== null && $categoryId > 0) {
        $childIds = getCategoryAndChildIds($categoryId);
        if (!empty($childIds)) {
            $inClause = implode(',', array_fill(0, count($childIds), '?'));
            $where .= " AND p.category_id IN ($inClause)";
            foreach ($childIds as $cid) {
                $params[] = (int) $cid;
            }
        }
    }

    if ($costStatus === 'missing') {
        $where .= ' AND (p.cost_price IS NULL OR p.cost_price = 0)';
    } elseif ($costStatus === 'has_cost') {
        $where .= ' AND p.cost_price IS NOT NULL AND p.cost_price > 0';
    }

    $stmt = db()->prepare("
        SELECT p.id, p.name, p.sku, p.price, p.cost_price, p.image, c.name AS category_name, c.id AS category_id
        FROM products p 
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE $where 
        ORDER BY p.name ASC
    ");
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Fetch high-level pricing KPI metrics for store catalog.
 *
 * @return array{total_products:int, avg_sale_price:int, avg_cost_price:int, missing_cost_count:int, avg_margin_percentage:float}
 */
function getCatalogPricingMetrics(): array
{
    $stmt = db()->query("
        SELECT 
            COUNT(*) AS total_products,
            AVG(price) AS avg_sale_price,
            AVG(CASE WHEN cost_price > 0 THEN cost_price ELSE NULL END) AS avg_cost_price,
            COUNT(CASE WHEN cost_price IS NULL OR cost_price = 0 THEN 1 ELSE NULL END) AS missing_cost_count
        FROM products
        WHERE is_active = 1
    ");
    $row = $stmt->fetch() ?: [];

    $total = (int) ($row['total_products'] ?? 0);
    $avgSale = (float) ($row['avg_sale_price'] ?? 0);
    $avgCost = (float) ($row['avg_cost_price'] ?? 0);
    $missingCost = (int) ($row['missing_cost_count'] ?? 0);

    $avgMargin = 0.0;
    if ($avgSale > 0 && $avgCost > 0) {
        $avgMargin = round((($avgSale - $avgCost) / $avgSale) * 100, 1);
    }

    return [
        'total_products' => $total,
        'avg_sale_price' => (int) round($avgSale),
        'avg_cost_price' => (int) round($avgCost),
        'missing_cost_count' => $missingCost,
        'avg_margin_percentage' => $avgMargin,
    ];
}

/**
 * Fetch recent bulk price operations log.
 */
function getRecentBulkPriceOperations(int $limit = 10): array
{
    $stmt = db()->prepare("
        SELECT bo.*, a.username AS admin_username
        FROM bulk_price_operations bo
        LEFT JOIN admins a ON a.id = bo.admin_id
        ORDER BY bo.created_at DESC 
        LIMIT " . (int) $limit . "
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Calculate preview rows for a proposed bulk price change without committing to DB.
 */
function getPricingPreviewRows(array $productIds, string $field, string $method, float $value, int $roundingStep = 0): array
{
    if (empty($productIds)) {
        return [];
    }

    $column = $field === 'cost_price' ? 'cost_price' : 'price';
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = db()->prepare("
        SELECT id, name, sku, image, price, cost_price, $column AS current_value 
        FROM products 
        WHERE id IN ($placeholders) 
        ORDER BY name ASC
    ");
    $stmt->execute($productIds);
    $rows = $stmt->fetchAll();

    $previewRows = [];
    foreach ($rows as $row) {
        $current = $row['current_value'] !== null ? (int) $row['current_value'] : null;
        $computed = computeNewPrice($method, $current, $value, $roundingStep);
        $cost = $row['cost_price'] !== null ? (int) $row['cost_price'] : 0;

        $isNegative = false;
        if ($field === 'sale_price' && $cost > 0 && $computed['new_value'] < $cost) {
            $isNegative = true;
        }

        $previewRows[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'sku' => $row['sku'],
            'image' => $row['image'],
            'price' => (int) $row['price'],
            'cost_price' => $cost,
            'current_value' => $current,
            'new_value' => $computed['new_value'],
            'change_amount' => $computed['change_amount'],
            'change_percentage' => $computed['change_percentage'],
            'is_negative_margin' => $isNegative,
        ];
    }

    return $previewRows;
}
