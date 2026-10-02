<?php
/**
 * Store Accounting Service
 *
 * Handles order-level profitability calculations, financial summaries (P&L),
 * operational expense ledger management, and category distributions.
 *
 * Architectural Invariants:
 * - Read-only reporting over financial snapshots (Rule 2).
 * - All SQL queries for accounting and expense mutations are strictly encapsulated here (Rule 7).
 * - Controllers must remain thin delegators (< 80 lines).
 */

// ==========================================
// 1. Order Profitability & Financial Summary
// ==========================================

/**
 * Full revenue/cost/profit breakdown for one order.
 *
 * @return array{
 *   ok:bool, revenue:int, product_revenue:int, discount:int,
 *   post_order_revenue:int, shipping_revenue:int, product_cost:int,
 *   gift_cost:int, shipping_cost:int, total_cost:int, gross_profit:int,
 *   margin_percent:float, has_incomplete_cost_data:bool
 * }
 */
function getOrderProfitability(int $orderId): array
{
    $pdo = db();
    $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch();
    if (!$order) {
        return ['ok' => false];
    }

    $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$orderId]);

    $productRevenue = 0;
    $productCost = 0;
    $hasIncompleteCostData = false;

    foreach ($itemsStmt->fetchAll() as $it) {
        $productRevenue += (int) $it['line_total'];
        if ($it['unit_cost_price'] === null) {
            $hasIncompleteCostData = true;
        } else {
            $productCost += (int) $it['unit_cost_price'] * (int) $it['quantity'];
        }
    }

    $giftStmt = $pdo->prepare("SELECT * FROM order_gift_items WHERE order_id = ?");
    $giftStmt->execute([$orderId]);

    $postOrderRevenue = 0;
    $giftCost = 0;
    foreach ($giftStmt->fetchAll() as $gi) {
        $postOrderRevenue += (int) $gi['unit_selling_price'] * (int) $gi['quantity'];
        $giftCost += (int) $gi['unit_cost_price'] * (int) $gi['quantity'];
    }

    $shippingRevenue = (int) $order['shipping_cost'];
    $shippingCost = $order['shipping_actual_cost'] !== null
        ? (int) $order['shipping_actual_cost']
        : $shippingRevenue;

    $discount = (int) $order['discount_total'];
    $revenue = $productRevenue - $discount + $postOrderRevenue + $shippingRevenue;
    $totalCost = $productCost + $giftCost + $shippingCost;
    $grossProfit = $revenue - $totalCost;
    $marginPercent = ($revenue > 0) ? round(($grossProfit / $revenue) * 100, 1) : 0.0;

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
        'gross_profit' => $grossProfit,
        'margin_percent' => $marginPercent,
        'has_incomplete_cost_data' => $hasIncompleteCostData,
    ];
}

/**
 * Resolves a date preset key into valid [startDate, endDate, normalizedRange] strings ('Y-m-d').
 * Supports precise Shamsi calendar boundaries for this_month, last_month, and this_year.
 *
 * @param string $range
 * @param string|null $customStart
 * @param string|null $customEnd
 * @return array{0: string, 1: string, 2: string}
 */
function resolveFinancialDateRange(string $range, ?string $customStart = null, ?string $customEnd = null): array
{
    $today = date('Y-m-d');
    $nowInfo = function_exists('appDateTime') ? appDateTime(null, 'array') : null;
    $jy = $nowInfo ? (int)$nowInfo['jalali']['year'] : 1405;
    $jm = $nowInfo ? (int)$nowInfo['jalali']['month'] : 1;

    switch ($range) {
        case 'today':
            $startDate = $endDate = $today;
            break;
        case 'yesterday':
            $startDate = $endDate = date('Y-m-d', strtotime('-1 day'));
            break;
        case '7days':
            $startDate = date('Y-m-d', strtotime('-6 days'));
            $endDate = $today;
            break;
        case '30days':
            $startDate = date('Y-m-d', strtotime('-29 days'));
            $endDate = $today;
            break;
        case 'last_month':
            // Previous Shamsi month
            $prevM = ($jm === 1) ? 12 : ($jm - 1);
            $prevY = ($jm === 1) ? ($jy - 1) : $jy;
            $prevMaxDays = ($prevM <= 6) ? 31 : (($prevM <= 11) ? 30 : 29);
            if (function_exists('jalaliToGregorian')) {
                [$gy1, $gm1, $gd1] = jalaliToGregorian($prevY, $prevM, 1);
                [$gy2, $gm2, $gd2] = jalaliToGregorian($prevY, $prevM, $prevMaxDays);
                $startDate = sprintf('%04d-%02d-%02d', $gy1, $gm1, $gd1);
                $endDate = sprintf('%04d-%02d-%02d', $gy2, $gm2, $gd2);
            } else {
                $startDate = date('Y-m-01', strtotime('first day of last month'));
                $endDate = date('Y-m-t', strtotime('last day of last month'));
            }
            break;
        case 'this_year':
            // 1st Farvardin of current Jalali year
            if (function_exists('jalaliToGregorian')) {
                [$gy, $gm, $gd] = jalaliToGregorian($jy, 1, 1);
                $startDate = sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
            } else {
                $startDate = date('Y-01-01');
            }
            $endDate = $today;
            break;
        case 'this_month':
        default:
            if (!empty($customStart) && !empty($customEnd)) {
                $startDate = trim($customStart);
                $endDate = trim($customEnd);
            } elseif (function_exists('jalaliToGregorian')) {
                // 1st of current Shamsi month
                [$gy, $gm, $gd] = jalaliToGregorian($jy, $jm, 1);
                $startDate = sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
                $endDate = $today;
            } else {
                $startDate = date('Y-m-01');
                $endDate = $today;
            }
            break;
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !strtotime($startDate)) {
        $startDate = date('Y-m-01');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) || !strtotime($endDate)) {
        $endDate = $today;
    }
    if ($startDate > $endDate) {
        [$startDate, $endDate] = [$endDate, $startDate];
    }

    return [$startDate, $endDate, $range];
}

/**
 * High-performance store-wide financial summary for a date range.
 * Uses batch aggregation queries instead of N+1 individual queries.
 *
 * @param string $startDate 'Y-m-d'
 * @param string $endDate 'Y-m-d'
 */
function getFinancialSummary(string $startDate, string $endDate): array
{
    $pdo = db();

    // 1. Fetch orders in date range
    $ordersStmt = $pdo->prepare("
        SELECT id, discount_total, shipping_cost, shipping_actual_cost
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'
    ");
    $ordersStmt->execute([$startDate, $endDate]);
    $orders = $ordersStmt->fetchAll();

    $orderCount = count($orders);
    $totalRevenue = 0;
    $totalProductCost = 0;
    $totalGiftCost = 0;
    $totalShippingCost = 0;
    $totalProductRevenue = 0;
    $totalPostOrderRevenue = 0;
    $totalShippingRevenue = 0;
    $totalDiscount = 0;
    $ordersWithIncompleteCostData = 0;

    if ($orderCount > 0) {
        $orderIds = array_column($orders, 'id');
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

        // Batch aggregate order_items
        $itemsStmt = $pdo->prepare("
            SELECT 
                order_id,
                SUM(line_total) AS prod_rev,
                SUM(CASE WHEN unit_cost_price IS NOT NULL THEN unit_cost_price * quantity ELSE 0 END) AS prod_cost,
                SUM(CASE WHEN unit_cost_price IS NULL THEN 1 ELSE 0 END) AS missing_cost_count
            FROM order_items 
            WHERE order_id IN ($placeholders)
            GROUP BY order_id
        ");
        $itemsStmt->execute($orderIds);
        $itemsByOrder = [];
        foreach ($itemsStmt->fetchAll() as $row) {
            $itemsByOrder[(int)$row['order_id']] = $row;
        }

        // Batch aggregate order_gift_items
        $giftsStmt = $pdo->prepare("
            SELECT 
                order_id,
                SUM(unit_selling_price * quantity) AS gift_rev,
                SUM(unit_cost_price * quantity) AS gift_cost
            FROM order_gift_items 
            WHERE order_id IN ($placeholders)
            GROUP BY order_id
        ");
        $giftsStmt->execute($orderIds);
        $giftsByOrder = [];
        foreach ($giftsStmt->fetchAll() as $row) {
            $giftsByOrder[(int)$row['order_id']] = $row;
        }

        // Aggregate order financials
        foreach ($orders as $ord) {
            $oid = (int) $ord['id'];
            $discount = (int) $ord['discount_total'];
            $shippingRev = (int) $ord['shipping_cost'];
            $shippingCost = $ord['shipping_actual_cost'] !== null
                ? (int) $ord['shipping_actual_cost']
                : $shippingRev;

            $itemData = $itemsByOrder[$oid] ?? ['prod_rev' => 0, 'prod_cost' => 0, 'missing_cost_count' => 0];
            $giftData = $giftsByOrder[$oid] ?? ['gift_rev' => 0, 'gift_cost' => 0];

            if ((int)$itemData['missing_cost_count'] > 0) {
                $ordersWithIncompleteCostData++;
            }

            $totalProductRevenue += (int) $itemData['prod_rev'];
            $totalProductCost += (int) $itemData['prod_cost'];
            $totalPostOrderRevenue += (int) $giftData['gift_rev'];
            $totalGiftCost += (int) $giftData['gift_cost'];
            $totalShippingRevenue += $shippingRev;
            $totalShippingCost += $shippingCost;
            $totalDiscount += $discount;
        }

        $totalRevenue = ($totalProductRevenue - $totalDiscount) + $totalPostOrderRevenue + $totalShippingRevenue;
    }

    $totalCogs = $totalProductCost + $totalGiftCost + $totalShippingCost;
    $grossProfit = $totalRevenue - $totalCogs;
    $grossMarginPercent = ($totalRevenue > 0) ? round(($grossProfit / $totalRevenue) * 100, 1) : 0.0;

    // Operational expenses query with fixed/variable/capital breakdown
    $expenseStmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(amount), 0) AS total_expenses,
            COALESCE(SUM(CASE WHEN expense_nature = 'fixed' THEN amount ELSE 0 END), 0) AS fixed_expenses,
            COALESCE(SUM(CASE WHEN expense_nature = 'variable' THEN amount ELSE 0 END), 0) AS variable_expenses,
            COALESCE(SUM(CASE WHEN expense_nature = 'capital' THEN amount ELSE 0 END), 0) AS capital_expenses
        FROM expenses 
        WHERE status = 'active' AND expense_date BETWEEN ? AND ?
    ");
    $expenseStmt->execute([$startDate, $endDate]);
    $expRow = $expenseStmt->fetch();
    $totalExpenses = (int) ($expRow['total_expenses'] ?? 0);
    $fixedExpenses = (int) ($expRow['fixed_expenses'] ?? 0);
    $variableExpenses = (int) ($expRow['variable_expenses'] ?? 0);
    $capitalExpenses = (int) ($expRow['capital_expenses'] ?? 0);

    // Expenses breakdown by category with percentage share
    $expenseByCategoryStmt = $pdo->prepare("
        SELECT category, SUM(amount) AS total, COUNT(*) AS entry_count
        FROM expenses
        WHERE status = 'active' AND expense_date BETWEEN ? AND ?
        GROUP BY category 
        ORDER BY total DESC
    ");
    $expenseByCategoryStmt->execute([$startDate, $endDate]);
    $rawCategoryExpenses = $expenseByCategoryStmt->fetchAll();

    $expensesByCategory = [];
    foreach ($rawCategoryExpenses as $catRow) {
        $catTotal = (int) $catRow['total'];
        $catShare = ($totalExpenses > 0) ? round(($catTotal / $totalExpenses) * 100, 1) : 0.0;
        $expensesByCategory[] = [
            'category' => $catRow['category'],
            'total' => $catTotal,
            'entry_count' => (int) $catRow['entry_count'],
            'share_percent' => $catShare,
        ];
    }

    $netProfit = $grossProfit - $totalExpenses;
    $netMarginPercent = ($totalRevenue > 0) ? round(($netProfit / $totalRevenue) * 100, 1) : 0.0;

    // Unit economics & balance indicators
    $aov = ($orderCount > 0) ? (int) round($totalRevenue / $orderCount) : 0;
    $netProfitPerOrder = ($orderCount > 0) ? (int) round($netProfit / $orderCount) : 0;
    $shippingBalance = $totalShippingRevenue - $totalShippingCost;
    $shippingSubsidy = max(0, -$shippingBalance);

    $effectiveGross = $totalRevenue + $totalDiscount;
    $discountRate = ($effectiveGross > 0) ? round(($totalDiscount / $effectiveGross) * 100, 1) : 0.0;

    // Break-even threshold calculations (utilizing fixed overhead if specified)
    $overheadBase = ($fixedExpenses > 0) ? $fixedExpenses : $totalExpenses;
    $grossMarginRatio = ($totalRevenue > 0) ? ($grossProfit / $totalRevenue) : 0.0;
    $breakevenRevenue = ($grossMarginRatio > 0) ? (int) round($overheadBase / $grossMarginRatio) : 0;
    $breakevenOrders = ($aov > 0 && $breakevenRevenue > 0) ? (int) ceil($breakevenRevenue / $aov) : 0;
    $breakevenProgressPercent = ($breakevenRevenue > 0) ? round(($totalRevenue / $breakevenRevenue) * 100, 1) : 0.0;
    $isBreakevenReached = ($totalRevenue >= $breakevenRevenue && $breakevenRevenue > 0);

    $costHealthPercent = ($orderCount > 0)
        ? round((($orderCount - $ordersWithIncompleteCostData) / $orderCount) * 100, 1)
        : 100.0;

    return [
        'order_count' => $orderCount,
        'total_revenue' => $totalRevenue,
        'product_revenue' => $totalProductRevenue,
        'discount_total' => $totalDiscount,
        'post_order_revenue' => $totalPostOrderRevenue,
        'shipping_revenue' => $totalShippingRevenue,
        'product_cost' => $totalProductCost,
        'gift_cost' => $totalGiftCost,
        'shipping_cost' => $totalShippingCost,
        'total_cogs' => $totalCogs,
        'gross_profit' => $grossProfit,
        'gross_margin_percent' => $grossMarginPercent,
        'total_expenses' => $totalExpenses,
        'fixed_expenses' => $fixedExpenses,
        'variable_expenses' => $variableExpenses,
        'capital_expenses' => $capitalExpenses,
        'net_profit' => $netProfit,
        'net_margin_percent' => $netMarginPercent,
        'orders_with_incomplete_cost_data' => $ordersWithIncompleteCostData,
        'cost_health_percent' => $costHealthPercent,
        'aov' => $aov,
        'net_profit_per_order' => $netProfitPerOrder,
        'shipping_balance' => $shippingBalance,
        'shipping_subsidy' => $shippingSubsidy,
        'discount_rate' => $discountRate,
        'breakeven_revenue' => $breakevenRevenue,
        'breakeven_orders' => $breakevenOrders,
        'breakeven_progress_percent' => $breakevenProgressPercent,
        'is_breakeven_reached' => $isBreakevenReached,
        'expenses_by_category' => $expensesByCategory,
    ];
}

/**
 * Time-series daily financial performance trends (Revenue, COGS, Expenses, Net Profit).
 * Optimized with batch grouping queries over indexed dates.
 *
 * @param string $startDate 'Y-m-d'
 * @param string $endDate 'Y-m-d'
 * @return array<int, array{
 *   date: string,
 *   date_shamsi: string,
 *   label_shamsi: string,
 *   order_count: int,
 *   revenue: int,
 *   cogs: int,
 *   gross_profit: int,
 *   expenses: int,
 *   net_profit: int
 * }>
 */
function getFinancialDailyTrends(string $startDate, string $endDate): array
{
    $pdo = db();

    // 1. Generate date interval (capped at 366 days)
    $periodDates = [];
    $currentTs = strtotime($startDate);
    $endTs = strtotime($endDate);
    if ($currentTs <= $endTs) {
        while ($currentTs <= $endTs && count($periodDates) < 366) {
            $periodDates[] = date('Y-m-d', $currentTs);
            $currentTs = strtotime('+1 day', $currentTs);
        }
    }

    if (empty($periodDates)) {
        return [];
    }

    // 2. Query orders daily summary
    $ordersStmt = $pdo->prepare("
        SELECT 
            DATE(created_at) AS date_str,
            COUNT(id) AS order_count,
            COALESCE(SUM(discount_total), 0) AS discount_total,
            COALESCE(SUM(shipping_cost), 0) AS shipping_revenue,
            COALESCE(SUM(CASE WHEN shipping_actual_cost IS NOT NULL THEN shipping_actual_cost ELSE shipping_cost END), 0) AS shipping_cost
        FROM orders
        WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'
        GROUP BY DATE(created_at)
    ");
    $ordersStmt->execute([$startDate, $endDate]);
    $ordersByDate = [];
    foreach ($ordersStmt->fetchAll() as $row) {
        $ordersByDate[$row['date_str']] = $row;
    }

    // 3. Query order items daily summary
    $itemsStmt = $pdo->prepare("
        SELECT 
            DATE(o.created_at) AS date_str,
            COALESCE(SUM(oi.line_total), 0) AS product_revenue,
            COALESCE(SUM(CASE WHEN oi.unit_cost_price IS NOT NULL THEN oi.unit_cost_price * oi.quantity ELSE 0 END) , 0) AS product_cost
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status != 'cancelled'
        GROUP BY DATE(o.created_at)
    ");
    $itemsStmt->execute([$startDate, $endDate]);
    $itemsByDate = [];
    foreach ($itemsStmt->fetchAll() as $row) {
        $itemsByDate[$row['date_str']] = $row;
    }

    // 4. Query gift items daily summary
    $giftsStmt = $pdo->prepare("
        SELECT 
            DATE(o.created_at) AS date_str,
            COALESCE(SUM(ogi.unit_selling_price * ogi.quantity), 0) AS gift_revenue,
            COALESCE(SUM(ogi.unit_cost_price * ogi.quantity), 0) AS gift_cost
        FROM order_gift_items ogi
        JOIN orders o ON o.id = ogi.order_id
        WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status != 'cancelled'
        GROUP BY DATE(o.created_at)
    ");
    $giftsStmt->execute([$startDate, $endDate]);
    $giftsByDate = [];
    foreach ($giftsStmt->fetchAll() as $row) {
        $giftsByDate[$row['date_str']] = $row;
    }

    // 5. Query expenses daily summary
    $expensesStmt = $pdo->prepare("
        SELECT 
            expense_date AS date_str,
            COALESCE(SUM(amount), 0) AS expense_total
        FROM expenses
        WHERE status = 'active' AND expense_date BETWEEN ? AND ?
        GROUP BY expense_date
    ");
    $expensesStmt->execute([$startDate, $endDate]);
    $expensesByDate = [];
    foreach ($expensesStmt->fetchAll() as $row) {
        $expensesByDate[$row['date_str']] = (int) $row['expense_total'];
    }

    // 6. Merge across timeline
    $trends = [];
    foreach ($periodDates as $d) {
        $ord = $ordersByDate[$d] ?? ['order_count' => 0, 'discount_total' => 0, 'shipping_revenue' => 0, 'shipping_cost' => 0];
        $it = $itemsByDate[$d] ?? ['product_revenue' => 0, 'product_cost' => 0];
        $gf = $giftsByDate[$d] ?? ['gift_revenue' => 0, 'gift_cost' => 0];
        $exp = $expensesByDate[$d] ?? 0;

        $rev = ((int)$it['product_revenue'] - (int)$ord['discount_total']) + (int)$gf['gift_revenue'] + (int)$ord['shipping_revenue'];
        $cogs = (int)$it['product_cost'] + (int)$gf['gift_cost'] + (int)$ord['shipping_cost'];
        $gross = $rev - $cogs;
        $net = $gross - $exp;

        $dateInfo = function_exists('appDateTime') ? appDateTime($d, 'array') : null;
        $labelShamsi = $dateInfo ? ($dateInfo['jalali']['day'] . ' ' . $dateInfo['jalali']['month_name']) : $d;
        $dateShamsi = $dateInfo ? $dateInfo['jalali']['formatted'] : $d;

        $trends[] = [
            'date' => $d,
            'date_shamsi' => $dateShamsi,
            'label_shamsi' => $labelShamsi,
            'order_count' => (int) $ord['order_count'],
            'revenue' => max(0, $rev),
            'cogs' => max(0, $cogs),
            'gross_profit' => $gross,
            'expenses' => $exp,
            'net_profit' => $net,
        ];
    }

    return $trends;
}

/**
 * Fetch top profit-driving products within the given date range.
 *
 * @param string $startDate 'Y-m-d'
 * @param string $endDate 'Y-m-d'
 * @param int $limit
 * @return array
 */
function getTopProfitProducts(string $startDate, string $endDate, int $limit = 5): array
{
    $pdo = db();
    $limit = max(1, min(50, $limit));

    $sql = "
        SELECT 
            oi.product_id,
            oi.product_name,
            SUM(oi.quantity) AS total_sold_qty,
            SUM(oi.line_total) AS total_revenue,
            SUM(CASE WHEN oi.unit_cost_price IS NOT NULL THEN oi.unit_cost_price * oi.quantity ELSE 0 END) AS total_cost,
            SUM(CASE WHEN oi.unit_cost_price IS NOT NULL THEN (oi.unit_price - oi.unit_cost_price) * oi.quantity ELSE 0 END) AS total_profit,
            SUM(CASE WHEN oi.unit_cost_price IS NULL THEN oi.quantity ELSE 0 END) AS missing_cost_qty,
            p.image AS product_image,
            p.price AS current_price,
            p.cost_price AS current_cost_price
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        LEFT JOIN products p ON p.id = oi.product_id
        WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status != 'cancelled'
        GROUP BY oi.product_id, oi.product_name, p.image, p.price, p.cost_price
        ORDER BY total_profit DESC
        LIMIT " . (int)$limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$startDate, $endDate]);
    $rows = $stmt->fetchAll();

    $results = [];
    foreach ($rows as $r) {
        $rev = (int) $r['total_revenue'];
        $profit = (int) $r['total_profit'];
        $margin = ($rev > 0) ? round(($profit / $rev) * 100, 1) : 0.0;
        $imageUrl = !empty($r['product_image']) ? UPLOAD_URL . $r['product_image'] : '/assets/img/placeholder-sock.svg';

        $results[] = [
            'product_id' => (int) $r['product_id'],
            'product_name' => $r['product_name'],
            'total_sold_qty' => (int) $r['total_sold_qty'],
            'total_revenue' => $rev,
            'total_cost' => (int) $r['total_cost'],
            'total_profit' => $profit,
            'margin_percent' => $margin,
            'missing_cost_qty' => (int) $r['missing_cost_qty'],
            'image_url' => $imageUrl,
            'current_price' => (int) ($r['current_price'] ?? 0),
            'current_cost_price' => ($r['current_cost_price'] !== null) ? (int) $r['current_cost_price'] : null,
        ];
    }

    return $results;
}

/**
 * Breakdown of financial performance by payment gateway & offline channels.
 *
 * @param string $startDate 'Y-m-d'
 * @param string $endDate 'Y-m-d'
 * @return array
 */
function getPaymentMethodBreakdown(string $startDate, string $endDate): array
{
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT 
            payment_method,
            COUNT(id) AS order_count,
            COALESCE(SUM(total), 0) AS total_amount,
            SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) AS paid_count,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END), 0) AS paid_amount
        FROM orders
        WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'
        GROUP BY payment_method
        ORDER BY total_amount DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    $rows = $stmt->fetchAll();

    $grandTotal = 0;
    foreach ($rows as $r) {
        $grandTotal += (int) $r['total_amount'];
    }

    $breakdown = [];
    foreach ($rows as $r) {
        $method = $r['payment_method'];
        $title = ($method === 'card_to_card') ? 'کارت‌به‌کارت' : (($method === 'zarinpal') ? 'درگاه زرین‌پال' : $method);
        $amount = (int) $r['total_amount'];
        $share = ($grandTotal > 0) ? round(($amount / $grandTotal) * 100, 1) : 0.0;

        $breakdown[] = [
            'method' => $method,
            'title' => $title,
            'order_count' => (int) $r['order_count'],
            'total_amount' => $amount,
            'paid_count' => (int) $r['paid_count'],
            'paid_amount' => (int) $r['paid_amount'],
            'share_percent' => $share,
        ];
    }

    return $breakdown;
}

/**
 * Breakdown of shipping collected from customers vs actual courier expenses.
 *
 * @param string $startDate 'Y-m-d'
 * @param string $endDate 'Y-m-d'
 * @return array
 */
function getShippingMethodFinancialBreakdown(string $startDate, string $endDate): array
{
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(NULLIF(TRIM(shipping_method_name), ''), 'روش پیش‌فرض') AS method_name,
            COUNT(id) AS order_count,
            COALESCE(SUM(shipping_cost), 0) AS shipping_revenue,
            COALESCE(SUM(CASE WHEN shipping_actual_cost IS NOT NULL THEN shipping_actual_cost ELSE shipping_cost END), 0) AS shipping_cost
        FROM orders
        WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'
        GROUP BY COALESCE(NULLIF(TRIM(shipping_method_name), ''), 'روش پیش‌فرض')
        ORDER BY shipping_revenue DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    $rows = $stmt->fetchAll();

    $results = [];
    foreach ($rows as $r) {
        $rev = (int) $r['shipping_revenue'];
        $cost = (int) $r['shipping_cost'];
        $balance = $rev - $cost;
        $subsidy = max(0, -$balance);

        $results[] = [
            'method_name' => $r['method_name'],
            'order_count' => (int) $r['order_count'],
            'shipping_revenue' => $rev,
            'shipping_cost' => $cost,
            'balance' => $balance,
            'subsidy' => $subsidy,
            'subsidy_rate' => ($cost > 0) ? round(($subsidy / $cost) * 100, 1) : 0.0,
        ];
    }

    return $results;
}

/**
 * Products with missing cost data in orders during the date window.
 *
 * @param string $startDate 'Y-m-d'
 * @param string $endDate 'Y-m-d'
 * @param int $limit
 * @return array
 */
function getIncompleteCostProducts(string $startDate, string $endDate, int $limit = 10): array
{
    $pdo = db();
    $limit = max(1, min(50, $limit));

    $sql = "
        SELECT 
            oi.product_id,
            oi.product_name,
            COUNT(DISTINCT oi.order_id) AS impacted_orders,
            SUM(oi.quantity) AS sold_qty,
            p.price AS current_price,
            p.cost_price AS current_cost_price,
            p.image AS product_image
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        LEFT JOIN products p ON p.id = oi.product_id
        WHERE DATE(o.created_at) BETWEEN ? AND ? 
          AND o.status != 'cancelled' 
          AND oi.unit_cost_price IS NULL
        GROUP BY oi.product_id, oi.product_name, p.price, p.cost_price, p.image
        ORDER BY impacted_orders DESC
        LIMIT " . (int)$limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$startDate, $endDate]);
    $rows = $stmt->fetchAll();

    $results = [];
    foreach ($rows as $r) {
        $imageUrl = !empty($r['product_image']) ? UPLOAD_URL . $r['product_image'] : '/assets/img/placeholder-sock.svg';
        $results[] = [
            'product_id' => (int) $r['product_id'],
            'product_name' => $r['product_name'],
            'impacted_orders' => (int) $r['impacted_orders'],
            'sold_qty' => (int) $r['sold_qty'],
            'current_price' => (int) ($r['current_price'] ?? 0),
            'current_cost_price' => ($r['current_cost_price'] !== null) ? (int) $r['current_cost_price'] : null,
            'image_url' => $imageUrl,
        ];
    }

    return $results;
}

/**
 * Fetch detailed order-level rows for CSV / Excel financial exports.
 *
 * @param string $startDate 'Y-m-d'
 * @param string $endDate 'Y-m-d'
 * @return array
 */
function getFinancialExportRows(string $startDate, string $endDate): array
{
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.order_code,
            o.customer_name,
            o.phone,
            o.subtotal,
            o.discount_total,
            o.shipping_cost,
            o.shipping_actual_cost,
            o.gift_items_total,
            o.total,
            o.payment_method,
            o.payment_status,
            o.status,
            o.created_at
        FROM orders o
        WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status != 'cancelled'
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    $orders = $stmt->fetchAll();

    if (empty($orders)) {
        return [];
    }

    $orderIds = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

    // Batch items cost
    $itemsStmt = $pdo->prepare("
        SELECT 
            order_id,
            SUM(CASE WHEN unit_cost_price IS NOT NULL THEN unit_cost_price * quantity ELSE 0 END) AS prod_cost
        FROM order_items
        WHERE order_id IN ($placeholders)
        GROUP BY order_id
    ");
    $itemsStmt->execute($orderIds);
    $itemsCostMap = [];
    foreach ($itemsStmt->fetchAll() as $row) {
        $itemsCostMap[(int)$row['order_id']] = (int) $row['prod_cost'];
    }

    // Batch gifts cost
    $giftsStmt = $pdo->prepare("
        SELECT 
            order_id,
            SUM(unit_cost_price * quantity) AS gift_cost
        FROM order_gift_items
        WHERE order_id IN ($placeholders)
        GROUP BY order_id
    ");
    $giftsStmt->execute($orderIds);
    $giftsCostMap = [];
    foreach ($giftsStmt->fetchAll() as $row) {
        $giftsCostMap[(int)$row['order_id']] = (int) $row['gift_cost'];
    }

    $exportRows = [];
    foreach ($orders as $ord) {
        $oid = (int) $ord['id'];
        $prodCost = $itemsCostMap[$oid] ?? 0;
        $giftCost = $giftsCostMap[$oid] ?? 0;
        $shippingRev = (int) $ord['shipping_cost'];
        $shippingCost = ($ord['shipping_actual_cost'] !== null) ? (int) $ord['shipping_actual_cost'] : $shippingRev;
        $totalCogs = $prodCost + $giftCost + $shippingCost;
        $totalRev = (int) $ord['total'];
        $grossProfit = $totalRev - $totalCogs;

        $dateInfo = function_exists('appDateTime') ? appDateTime($ord['created_at'], 'array') : null;
        $dateShamsi = $dateInfo ? $dateInfo['jalali']['formatted'] : date('Y/m/d', strtotime($ord['created_at']));

        $exportRows[] = [
            'order_code' => $ord['order_code'],
            'created_at' => $ord['created_at'],
            'created_at_shamsi' => $dateShamsi,
            'customer_name' => $ord['customer_name'],
            'phone' => $ord['phone'],
            'subtotal' => (int) $ord['subtotal'],
            'discount' => (int) $ord['discount_total'],
            'shipping_revenue' => $shippingRev,
            'shipping_cost' => $shippingCost,
            'total_revenue' => $totalRev,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'payment_method' => $ord['payment_method'],
            'payment_status' => $ord['payment_status'],
            'order_status' => $ord['status'],
        ];
    }

    return $exportRows;
}

/**
 * Stream download a clean UTF-8 CSV financial report for spreadsheet programs.
 *
 * @param string $startDate 'Y-m-d'
 * @param string $endDate 'Y-m-d'
 */
function exportFinancialCsv(string $startDate, string $endDate): void
{
    $rows = getFinancialExportRows($startDate, $endDate);
    $filename = 'financial_report_' . $startDate . '_to_' . $endDate . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    // Prepend UTF-8 BOM so Excel opens Persian text without encoding issues
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($out, [
        'شماره سفارش',
        'تاریخ میلادی',
        'تاریخ شمسی',
        'نام مشتری',
        'تلفن',
        'جمع اقلام (تومان)',
        'تخفیف (تومان)',
        'کرایه دریافتی (تومان)',
        'کرایه واقعی پست (تومان)',
        'مجموع فاکتور (تومان)',
        'بهای تمام‌شده کل (تومان)',
        'سود ناخالص (تومان)',
        'روش پرداخت',
        'وضعیت پرداخت',
        'وضعیت سفارش',
    ]);

    foreach ($rows as $r) {
        fputcsv($out, [
            $r['order_code'],
            $r['created_at'],
            $r['created_at_shamsi'],
            $r['customer_name'],
            $r['phone'],
            $r['subtotal'],
            $r['discount'],
            $r['shipping_revenue'],
            $r['shipping_cost'],
            $r['total_revenue'],
            $r['total_cogs'],
            $r['gross_profit'],
            $r['payment_method'],
            $r['payment_status'],
            $r['order_status'],
        ]);
    }

    fclose($out);
    exit;
}

// ==========================================
// 2. Inventory Valuation & Capital Health Report
// ==========================================

/**
 * Comprehensive Inventory Valuation & Capital Health Report.
 *
 * Vectorized analysis over products and variants to calculate:
 * - Total inventory cost valuation (capital locked in stock)
 * - Total retail valuation & potential unrealized gross profit
 * - Category-level capital distribution
 * - Top capital-concentrated products
 * - Dead / Slow-moving stock (high inventory with zero sales in last 60 days)
 * - Items with incomplete/missing cost data
 *
 * @return array
 */
function getInventoryValuationReport(): array
{
    $pdo = db();

    // 1. Fetch all products and active variants
    $productsStmt = $pdo->query("
        SELECT 
            p.id, p.name, p.category_id, p.price, p.cost_price, p.stock, p.has_variants, p.status,
            c.name AS category_name,
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC, pi.id ASC LIMIT 1) AS image_path
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.status != 'deleted'
        ORDER BY p.id ASC
    ");
    $products = $productsStmt->fetchAll();

    $variantsStmt = $pdo->query("
        SELECT 
            pv.id, pv.product_id, pv.label, pv.stock, pv.cost_price, pv.price_override
        FROM product_variants pv
        INNER JOIN products p ON p.id = pv.product_id
        WHERE p.status != 'deleted'
        ORDER BY pv.id ASC
    ");
    $variantsByProduct = [];
    foreach ($variantsStmt->fetchAll() as $v) {
        $variantsByProduct[$v['product_id']][] = $v;
    }

    // 2. Sales in last 60 days for dead stock detection
    $cutoffDate = date('Y-m-d', strtotime('-60 days'));
    $salesStmt = $pdo->prepare("
        SELECT oi.product_id, SUM(oi.quantity) AS sold_qty_60d
        FROM order_items oi
        INNER JOIN orders o ON o.id = oi.order_id
        WHERE o.status NOT IN ('cancelled') AND o.created_at >= ?
        GROUP BY oi.product_id
    ");
    $salesStmt->execute([$cutoffDate . ' 00:00:00']);
    $sales60d = $salesStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $totalUnits = 0;
    $totalCostValue = 0;
    $totalRetailValue = 0;
    $missingCostCount = 0;

    $categoryStats = [];
    $productCapitalList = [];
    $deadStockList = [];

    foreach ($products as $p) {
        $pId = (int) $p['id'];
        $catName = $p['category_name'] ?: 'دسته‌بندی‌نشده';
        $sold60d = (int) ($sales60d[$pId] ?? 0);

        $prodUnits = 0;
        $prodCostVal = 0;
        $prodRetailVal = 0;
        $hasMissingCost = false;

        if (!empty($p['has_variants']) && isset($variantsByProduct[$pId])) {
            foreach ($variantsByProduct[$pId] as $var) {
                $vStock = max(0, (int) $var['stock']);
                $vCost = ($var['cost_price'] !== null && (int)$var['cost_price'] > 0)
                    ? (int) $var['cost_price']
                    : (int) ($p['cost_price'] ?? 0);
                $vPrice = ($var['price_override'] !== null && (int)$var['price_override'] > 0)
                    ? (int) $var['price_override']
                    : (int) $p['price'];

                if ($vStock > 0 && $vCost <= 0) {
                    $hasMissingCost = true;
                }

                $prodUnits += $vStock;
                $prodCostVal += ($vStock * $vCost);
                $prodRetailVal += ($vStock * $vPrice);
            }
        } else {
            $pStock = max(0, (int) $p['stock']);
            $pCost = (int) ($p['cost_price'] ?? 0);
            $pPrice = (int) $p['price'];

            if ($pStock > 0 && $pCost <= 0) {
                $hasMissingCost = true;
            }

            $prodUnits = $pStock;
            $prodCostVal = ($pStock * $pCost);
            $prodRetailVal = ($pStock * $pPrice);
        }

        if ($hasMissingCost) {
            $missingCostCount++;
        }

        $totalUnits += $prodUnits;
        $totalCostValue += $prodCostVal;
        $totalRetailValue += $prodRetailVal;

        // Group by category
        if (!isset($categoryStats[$catName])) {
            $categoryStats[$catName] = [
                'name' => $catName,
                'product_count' => 0,
                'total_units' => 0,
                'cost_value' => 0,
                'retail_value' => 0,
            ];
        }
        $categoryStats[$catName]['product_count']++;
        $categoryStats[$catName]['total_units'] += $prodUnits;
        $categoryStats[$catName]['cost_value'] += $prodCostVal;
        $categoryStats[$catName]['retail_value'] += $prodRetailVal;

        if ($prodUnits > 0) {
            $itemProfit = $prodRetailVal - $prodCostVal;
            $itemMargin = ($prodRetailVal > 0) ? round(($itemProfit / $prodRetailVal) * 100, 1) : 0.0;
            $imgUrl = !empty($p['image_path']) ? (UPLOAD_URL . $p['image_path']) : '/assets/img/placeholder-sock.svg';

            $productSummary = [
                'id' => $pId,
                'name' => $p['name'],
                'category_name' => $catName,
                'units' => $prodUnits,
                'cost_value' => $prodCostVal,
                'retail_value' => $prodRetailVal,
                'potential_profit' => $itemProfit,
                'margin_percent' => $itemMargin,
                'has_missing_cost' => $hasMissingCost,
                'image_url' => $imgUrl,
                'sold_60d' => $sold60d,
            ];

            $productCapitalList[] = $productSummary;

            // Dead stock candidate: stock >= 3 and 0 sold in last 60 days
            if ($prodUnits >= 3 && $sold60d === 0) {
                $deadStockList[] = $productSummary;
            }
        }
    }

    // Sort categories by cost value DESC
    usort($categoryStats, fn($a, $b) => $b['cost_value'] <=> $a['cost_value']);
    foreach ($categoryStats as &$cat) {
        $cat['share_percent'] = ($totalCostValue > 0) ? round(($cat['cost_value'] / $totalCostValue) * 100, 1) : 0.0;
        $profit = $cat['retail_value'] - $cat['cost_value'];
        $cat['potential_profit'] = $profit;
        $cat['margin_percent'] = ($cat['retail_value'] > 0) ? round(($profit / $cat['retail_value']) * 100, 1) : 0.0;
    }
    unset($cat);

    // Top capital invested products
    usort($productCapitalList, fn($a, $b) => $b['cost_value'] <=> $a['cost_value']);
    $topInvested = array_slice($productCapitalList, 0, 8);

    // Dead stock sorted by locked capital DESC
    usort($deadStockList, fn($a, $b) => $b['cost_value'] <=> $a['cost_value']);

    $potentialProfit = $totalRetailValue - $totalCostValue;
    $potentialMarginPercent = ($totalRetailValue > 0) ? round(($potentialProfit / $totalRetailValue) * 100, 1) : 0.0;
    $deadStockCapital = array_sum(array_column($deadStockList, 'cost_value'));

    return [
        'total_units' => $totalUnits,
        'total_cost_value' => $totalCostValue,
        'total_retail_value' => $totalRetailValue,
        'potential_profit' => $potentialProfit,
        'potential_margin_percent' => $potentialMarginPercent,
        'category_stats' => $categoryStats,
        'top_invested' => $topInvested,
        'dead_stock_items' => $deadStockList,
        'dead_stock_capital' => $deadStockCapital,
        'missing_cost_count' => $missingCostCount,
    ];
}
