<?php
/**
 * Store accounting — order-level profitability and financial summaries.
 *
 * Every number here is built from data already snapshotted at the moment
 * it happened (order_items.unit_cost_price, order_gift_items.unit_cost_price/
 * unit_selling_price, orders.shipping_cost/shipping_actual_cost) rather than
 * looked up from current product/gift-item/shipping-method rows — a price
 * change today must not silently rewrite yesterday's profit. Nothing here
 * writes to the database; it's all read-only reporting over existing tables.
 */

/**
 * Full revenue/cost/profit breakdown for one order.
 *
 * @return array{
 *   ok:bool, revenue:int, product_revenue:int, discount:int,
 *   post_order_revenue:int, shipping_revenue:int, product_cost:int,
 *   gift_cost:int, shipping_cost:int, total_cost:int, gross_profit:int,
 *   has_incomplete_cost_data:bool
 * }
 */
function getOrderProfitability(int $orderId): array
{
    $orderStmt = db()->prepare("SELECT * FROM orders WHERE id = ?");
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch();
    if (!$order) {
        return ['ok' => false];
    }

    $items = db()->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items->execute([$orderId]);

    $productRevenue = 0;
    $productCost = 0;
    $hasIncompleteCostData = false;
    foreach ($items->fetchAll() as $it) {
        $productRevenue += (int) $it['line_total'];
        if ($it['unit_cost_price'] === null) {
            // No cost was on record for this product/variant at sale time
            // (either it predates cost tracking, or an admin never set one) —
            // excluded from cost rather than guessed at, and flagged so the
            // caller can show the resulting profit figure as incomplete.
            $hasIncompleteCostData = true;
        } else {
            $productCost += (int) $it['unit_cost_price'] * (int) $it['quantity'];
        }
    }

    $giftItems = db()->prepare("SELECT * FROM order_gift_items WHERE order_id = ?");
    $giftItems->execute([$orderId]);

    $postOrderRevenue = 0;
    $giftCost = 0; // cost of both free gifts and paid post-order lines
    foreach ($giftItems->fetchAll() as $gi) {
        $postOrderRevenue += (int) $gi['unit_selling_price'] * (int) $gi['quantity']; // 0 for role='gift'
        $giftCost += (int) $gi['unit_cost_price'] * (int) $gi['quantity'];
    }

    $shippingRevenue = (int) $order['shipping_cost'];
    // Orders placed before 1.8.0 (or a method with no actual_cost set) have
    // no recorded shipping cost — treated as break-even against what was
    // charged rather than assumed free, so profit isn't overstated.
    $shippingCost = $order['shipping_actual_cost'] !== null
        ? (int) $order['shipping_actual_cost']
        : $shippingRevenue;

    $discount = (int) $order['discount_total'];
    $revenue = $productRevenue - $discount + $postOrderRevenue + $shippingRevenue;
    $totalCost = $productCost + $giftCost + $shippingCost;

    return [
        'ok' => true,
        'revenue' => $revenue,
        'product_revenue' => $productRevenue,
        'discount' => $discount,
        'post_order_revenue' => $postOrderRevenue,
        'shipping_revenue' => $shippingRevenue,
        'product_cost' => $productCost,
        'gift_cost' => $giftCost,
        'shipping_cost' => $shippingCost,
        'total_cost' => $totalCost,
        'gross_profit' => $revenue - $totalCost,
        'has_incomplete_cost_data' => $hasIncompleteCostData,
    ];
}

/**
 * Store-wide financial summary for a date range (inclusive), combining
 * every order's profitability with the manually-recorded expense ledger.
 * Cancelled orders are excluded — they were never realized revenue.
 *
 * @param string $startDate 'Y-m-d'
 * @param string $endDate 'Y-m-d'
 */
function getFinancialSummary(string $startDate, string $endDate): array
{
    $orderIdsStmt = db()->prepare("SELECT id FROM orders WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'");
    $orderIdsStmt->execute([$startDate, $endDate]);
    $orderIds = array_column($orderIdsStmt->fetchAll(), 'id');

    $totalRevenue = 0;
    $totalCogs = 0;
    $grossProfit = 0;
    $ordersWithIncompleteCostData = 0;

    foreach ($orderIds as $orderId) {
        $p = getOrderProfitability((int) $orderId);
        if (!$p['ok']) continue;
        $totalRevenue += $p['revenue'];
        $totalCogs += $p['total_cost'];
        $grossProfit += $p['gross_profit'];
        if ($p['has_incomplete_cost_data']) $ordersWithIncompleteCostData++;
    }

    $expenseStmt = db()->prepare("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE status = 'active' AND expense_date BETWEEN ? AND ?");
    $expenseStmt->execute([$startDate, $endDate]);
    $totalExpenses = (int) $expenseStmt->fetchColumn();

    $expenseByCategoryStmt = db()->prepare("
        SELECT category, SUM(amount) AS total FROM expenses
        WHERE status = 'active' AND expense_date BETWEEN ? AND ?
        GROUP BY category ORDER BY total DESC
    ");
    $expenseByCategoryStmt->execute([$startDate, $endDate]);

    return [
        'order_count' => count($orderIds),
        'total_revenue' => $totalRevenue,
        'total_cogs' => $totalCogs,
        'gross_profit' => $grossProfit,
        'total_expenses' => $totalExpenses,
        'net_profit' => $grossProfit - $totalExpenses,
        'orders_with_incomplete_cost_data' => $ordersWithIncompleteCostData,
        'expenses_by_category' => $expenseByCategoryStmt->fetchAll(),
    ];
}
