<?php
/**
 * Shipping cost calculation.
 *
 * A `shipping_methods` row matches an order in one of two ways, checked in
 * `sort_order`: `province_contains` matches when the customer's (free-text)
 * province field contains `match_value`, and `default` is the fallback
 * used when nothing more specific matched. `free_above_amount`, when set,
 * waives that method's cost once the order subtotal reaches it.
 *
 * This intentionally does not do per-city or weight-based calculation —
 * province/city are free-text checkout fields today and products carry no
 * weight — but `match_type` is an enum specifically so a `city_contains`
 * or a future weight-tier rule can be added later without restructuring
 * `shipping_methods` or this service's callers.
 */

/**
 * Determine which shipping method applies to an order and what it costs.
 * Never trusts a client-supplied cost — only the province string and the
 * subtotal (itself always computed server-side by the caller) go in.
 *
 * @return array{method_id:?int, method_name:?string, cost:int, actual_cost:?int, is_free:bool}
 */
function calculateShippingCost(string $province, int $subtotal): array
{
    $methods = db()->query("SELECT * FROM shipping_methods WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

    $matched = null;
    foreach ($methods as $method) {
        if ($method['match_type'] === 'province_contains') {
            if ($method['match_value'] !== null && mb_strpos($province, $method['match_value']) !== false) {
                $matched = $method;
                break;
            }
            continue;
        }
        // 'default' — only used as a fallback if nothing more specific matches
        $matched = $matched ?? $method;
    }

    if (!$matched) {
        return ['method_id' => null, 'method_name' => null, 'cost' => 0, 'actual_cost' => null, 'is_free' => false];
    }

    $cost = (int) $matched['cost'];
    $actualCost = $matched['actual_cost'] !== null ? (int) $matched['actual_cost'] : null;
    $isFree = false;
    if ($matched['free_above_amount'] !== null && $subtotal >= (int) $matched['free_above_amount']) {
        // Waiving the charge to the customer doesn't waive what the store
        // actually pays the courier — only the revenue side becomes free.
        $cost = 0;
        $isFree = true;
    }

    return ['method_id' => (int) $matched['id'], 'method_name' => $matched['name'], 'cost' => $cost, 'actual_cost' => $actualCost, 'is_free' => $isFree];
}

/**
 * Fetch all shipping methods ordered by sort_order.
 */
function getAdminShippingMethods(): array
{
    return db()->query("SELECT * FROM shipping_methods ORDER BY sort_order ASC")->fetchAll();
}

/**
 * Fetch a single shipping method by ID.
 */
function getShippingMethodById(int $id): ?array
{
    $stmt = db()->prepare("SELECT * FROM shipping_methods WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Save (create or update) a shipping method.
 *
 * @return array{ok: bool, error: ?string, id: ?int}
 */
function saveShippingMethodRecord(?int $id, array $data): array
{
    $pdo = db();
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $matchType = ($data['match_type'] ?? '') === 'province_contains' ? 'province_contains' : 'default';
    $matchValue = trim($data['match_value'] ?? '');
    $cost = (int) preg_replace('/\D/', '', $data['cost'] ?? '0');
    $actualCostRaw = trim($data['actual_cost'] ?? '');
    $actualCost = $actualCostRaw === '' ? null : (int) preg_replace('/\D/', '', $actualCostRaw);
    $freeAboveRaw = trim($data['free_above_amount'] ?? '');
    $freeAbove = $freeAboveRaw === '' ? null : (int) preg_replace('/\D/', '', $freeAboveRaw);
    $isActive = !empty($data['is_active']) ? 1 : 0;

    if ($name === '') {
        return ['ok' => false, 'error' => 'نام روش ارسال الزامی است.', 'id' => null];
    }
    if ($matchType === 'province_contains' && $matchValue === '') {
        return ['ok' => false, 'error' => 'برای «تطبیق با استان»، متن استان را وارد کنید (مثلا تهران).', 'id' => null];
    }
    if ($cost < 0) {
        return ['ok' => false, 'error' => 'هزینه نمی‌تواند منفی باشد.', 'id' => null];
    }

    try {
        if ($id && $id > 0) {
            $stmt = $pdo->prepare("
                UPDATE shipping_methods 
                SET name=?, description=?, match_type=?, match_value=?, cost=?, actual_cost=?, free_above_amount=?, is_active=? 
                WHERE id=?
            ");
            $stmt->execute([
                $name,
                $description ?: null,
                $matchType,
                $matchType === 'province_contains' ? $matchValue : null,
                $cost,
                $actualCost,
                $freeAbove,
                $isActive,
                $id,
            ]);
        } else {
            $maxSortStmt = $pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM shipping_methods");
            $nextSort = ((int) $maxSortStmt->fetchColumn()) + 1;
            $stmt = $pdo->prepare("
                INSERT INTO shipping_methods 
                    (name, description, match_type, match_value, cost, actual_cost, free_above_amount, is_active, sort_order) 
                VALUES (?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $name,
                $description ?: null,
                $matchType,
                $matchType === 'province_contains' ? $matchValue : null,
                $cost,
                $actualCost,
                $freeAbove,
                $isActive,
                $nextSort,
            ]);
            $id = (int) $pdo->lastInsertId();
        }
        return ['ok' => true, 'error' => null, 'id' => $id];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'خطا در ذخیره روش ارسال: ' . $e->getMessage(), 'id' => null];
    }
}

/**
 * Delete a shipping method safely.
 *
 * @return array{ok: bool, error: ?string}
 */
function deleteShippingMethodRecord(int $id): array
{
    $stmt = db()->prepare("DELETE FROM shipping_methods WHERE id = ?");
    $stmt->execute([$id]);
    return ['ok' => true, 'error' => null];
}

/**
 * Swap sort order of adjacent shipping methods.
 *
 * @return array{ok: bool, error: ?string}
 */
function moveShippingMethodOrder(int $id, string $direction): array
{
    $pdo = db();
    $current = $pdo->prepare("SELECT id, sort_order FROM shipping_methods WHERE id = ?");
    $current->execute([$id]);
    $currentRow = $current->fetch();

    if (!$currentRow) {
        return ['ok' => false, 'error' => 'روش ارسال یافت نشد.'];
    }

    $cmp = $direction === 'up' ? '<' : '>';
    $order = $direction === 'up' ? 'DESC' : 'ASC';
    $neighborStmt = $pdo->prepare("
        SELECT id, sort_order FROM shipping_methods 
        WHERE sort_order $cmp ? 
        ORDER BY sort_order $order 
        LIMIT 1
    ");
    $neighborStmt->execute([$currentRow['sort_order']]);
    $neighbor = $neighborStmt->fetch();

    if ($neighbor) {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE shipping_methods SET sort_order = ? WHERE id = ?")->execute([$neighbor['sort_order'], $currentRow['id']]);
            $pdo->prepare("UPDATE shipping_methods SET sort_order = ? WHERE id = ?")->execute([$currentRow['sort_order'], $neighbor['id']]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'خطا در تغییر اولویت: ' . $e->getMessage()];
        }
    }

    return ['ok' => true, 'error' => null];
}

