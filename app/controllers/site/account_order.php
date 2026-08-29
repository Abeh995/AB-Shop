<?php
/**
 * Details for a specific order; only the order owner may view it.
 */

requireCustomer();
$customer = currentCustomer();
$code = $_GET['code'] ?? '';

$stmt = db()->prepare("SELECT * FROM orders WHERE order_code = ? AND customer_id = ? LIMIT 1");
$stmt->execute([$code, $customer['id']]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    require __DIR__ . '/not_found.php';
    return;
}

$itemsStmt = db()->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemsStmt->execute([$order['id']]);
$items = $itemsStmt->fetchAll();

$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

$pageTitle = 'سفارش ' . $order['order_code'];
renderView('site/account_order', compact('pageTitle', 'order', 'items', 'statusLabels'));
