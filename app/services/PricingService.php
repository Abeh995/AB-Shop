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
function computeNewPrice(string $method, ?int $previousValue, float $inputValue): array
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
    ?int $bulkOperationId = null
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
        $result = computeNewPrice($method, $previousValue, $inputValue);

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
 * Apply the same price change to an arbitrary set of products (product-level
 * only — bulk operations don't reach into individual variants). Always
 * creates a bulk_price_operations record first so every affected product's
 * price_history row can be traced back to the request that caused it, even
 * for products that ended up being skipped.
 *
 * @param int[] $productIds
 * @return array{bulk_operation_id:int, succeeded:int, skipped:array<int,string>}
 */
function applyBulkPriceChange(
    array $productIds,
    string $field,
    string $method,
    float $inputValue,
    int $adminId,
    ?string $reason = null
): array {
    $requestedChange = match ($method) {
        'percentage'    => ($inputValue >= 0 ? '+' : '') . rtrim(rtrim(number_format($inputValue, 4, '.', ''), '0'), '.') . '%',
        'direct_value'  => '=' . number_format($inputValue, 0, '.', ''),
        default         => ($inputValue >= 0 ? '+' : '') . number_format($inputValue, 0, '.', ''),
    };

    $opStmt = db()->prepare("
        INSERT INTO bulk_price_operations (admin_id, field_changed, method, requested_change, reason, product_count)
        VALUES (?, ?, ?, ?, ?, 0)
    ");
    $opStmt->execute([$adminId, $field === 'cost_price' ? 'cost_price' : 'sale_price', $method, $requestedChange, $reason]);
    $bulkId = (int) db()->lastInsertId();

    $succeeded = 0;
    $skipped = [];
    foreach ($productIds as $productId) {
        $result = recordPriceChange((int) $productId, null, $field, $method, $inputValue, $adminId, $reason, $bulkId);
        if ($result['ok']) {
            $succeeded++;
        } else {
            $skipped[(int) $productId] = $result['error'];
        }
    }

    db()->prepare("UPDATE bulk_price_operations SET product_count = ? WHERE id = ?")->execute([$succeeded, $bulkId]);

    return ['bulk_operation_id' => $bulkId, 'succeeded' => $succeeded, 'skipped' => $skipped];
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
