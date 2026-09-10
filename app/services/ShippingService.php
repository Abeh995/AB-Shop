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
