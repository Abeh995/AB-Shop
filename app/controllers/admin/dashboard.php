<?php
/**
 * Admin Dashboard Controller
 * Prepares store analytics, 7-day revenue trend, pending queues, and inventory alerts.
 * Wrapped defensively in try/catch to ensure shared hosting stability.
 */

$pageTitle = 'داشبورد';

$productCount = 0;
$orderCount = 0;
$pendingCount = 0;
$revenue = 0;
$lowStock = 0;
$criticalStockItems = [];
$latestC2cOrder = null;
$recentOrders = [];
$dailyRows = [];

// 1. Basic Counts
try {
    $productCount = (int) db()->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
} catch (Throwable $e) {}

try {
    $orderCount = (int) db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();
} catch (Throwable $e) {}

try {
    $pendingCount = (int) db()->query("SELECT COUNT(*) FROM orders WHERE status = 'pending' OR payment_status = 'unpaid'")->fetchColumn();
} catch (Throwable $e) {}

try {
    $revenue = (float) db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status != 'failed' AND status != 'cancelled'")->fetchColumn();
} catch (Throwable $e) {}

try {
    $lowStock = (int) db()->query("SELECT COUNT(*) FROM products WHERE is_active = 1 AND stock <= 5")->fetchColumn();
} catch (Throwable $e) {}

// 2. Critical low stock items for inventory alert widget (Column is `name`, not `title`)
try {
    $criticalStockItems = db()->query("
        SELECT id, name, stock, IFNULL(image, '') as image 
        FROM products 
        WHERE is_active = 1 AND stock <= 5 
        ORDER BY stock ASC 
        LIMIT 4
    ")->fetchAll();
} catch (Throwable $e) {}

// 3. Latest Card to Card payment awaiting review
try {
    $latestC2cOrder = db()->query("
        SELECT id, order_code, customer_name, total, card_to_card_receipt, created_at 
        FROM orders 
        WHERE payment_method = 'card_to_card' AND card_to_card_receipt IS NOT NULL AND payment_status = 'unpaid' 
        ORDER BY created_at DESC 
        LIMIT 1
    ")->fetch();
} catch (Throwable $e) {}

// 4. Recent 10 orders
try {
    $recentOrders = db()->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10")->fetchAll();
} catch (Throwable $e) {}

// 5. 7-day revenue trend for interactive SVG chart
$chartDaysPersian = [
    1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنج‌شنبه', 5 => 'جمعه', 6 => 'شنبه', 7 => 'یک‌شنبه'
];
$sevenDaysAgo = date('Y-m-d 00:00:00', strtotime('-6 days'));

try {
    $dailyRowsRaw = db()->query("
        SELECT DATE(created_at) as o_date, COALESCE(SUM(total), 0) as day_total, COUNT(*) as day_count
        FROM orders 
        WHERE created_at >= '$sevenDaysAgo' AND payment_status != 'failed' AND status != 'cancelled'
        GROUP BY DATE(created_at)
    ")->fetchAll();

    foreach ($dailyRowsRaw as $row) {
        $dailyRows[$row['o_date']] = $row;
    }
} catch (Throwable $e) {}

$chartPeriodTotal = 0;
$chartPeriodOrders = 0;
$chartPoints = [];
for ($i = 6; $i >= 0; $i--) {
    $dStr = date('Y-m-d', strtotime("-$i days"));
    $dayOfWeek = (int) date('N', strtotime($dStr));
    $label = ($i === 0) ? 'امروز' : ($chartDaysPersian[$dayOfWeek] ?? $dStr);
    $dayTotal = isset($dailyRows[$dStr]) ? (float) $dailyRows[$dStr]['day_total'] : 0;
    $dayCount = isset($dailyRows[$dStr]) ? (int) $dailyRows[$dStr]['day_count'] : 0;
    $chartPeriodTotal += $dayTotal;
    $chartPeriodOrders += $dayCount;
    $chartPoints[] = [
        'date' => $label,
        'date_raw' => $dStr,
        'total' => $dayTotal,
        'count' => $dayCount,
    ];
}

$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

renderView('admin/dashboard', compact(
    'pageTitle', 'productCount', 'orderCount', 'pendingCount', 'revenue', 'lowStock',
    'recentOrders', 'statusLabels', 'criticalStockItems', 'latestC2cOrder',
    'chartPoints', 'chartPeriodTotal', 'chartPeriodOrders'
));
