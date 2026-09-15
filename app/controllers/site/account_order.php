<?php
/**
 * A single order's details, viewable only by that order's owner.
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

// Post-order add-ons the customer paid for on this order (free gifts are
// intentionally excluded here — they're an internal/admin-facing record,
// not something the customer needs itemized on their own order view).
$orderGiftItems = array_filter(getOrderGiftItems($order['id']), fn($gi) => $gi['role'] === 'post_order');

$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

$paymentLabels = ['unpaid' => 'در انتظار پرداخت/بررسی', 'paid' => 'پرداخت‌شده', 'failed' => 'ناموفق'];
$paymentMethodLabels = ['zarinpal' => 'زرین‌پال', 'card_to_card' => 'کارت‌به‌کارت'];

$pageTitle = 'سفارش ' . $order['order_code'];
renderView('site/account_order', compact('pageTitle', 'order', 'items', 'statusLabels', 'orderGiftItems', 'paymentLabels', 'paymentMethodLabels'));
