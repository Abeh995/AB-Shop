<?php
/**
 * Retry payment for an existing order whose previous payment failed or was not completed.
 * The order and its items already exist; this endpoint only creates a new ZarinPal payment request.
 */

require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $code = $_POST['code'] ?? '';
} else {
    $code = $_GET['code'] ?? '';
}

$stmt = db()->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
$stmt->execute([$code]);
$order = $stmt->fetch();

if (!$order) {
    redirect('/');
}

if ($order['payment_status'] === 'paid') {
    redirect('/order/success/' . $order['order_code']);
}

if ($order['status'] === 'cancelled') {
    redirect('/order/failed/' . $order['order_code'] . '?err=' . urlencode('این سفارش لغو شده و امکان پرداخت آن وجود ندارد.'));
}

$callbackUrl = rtrim(SITE_URL, '/') . '/payment/zarinpal_callback.php';
$payResult = ZarinpalService::request((int) $order['total'], 'پرداخت سفارش ' . $order['order_code'], $callbackUrl, $order['phone'], $order['email']);

if ($payResult['ok']) {
    db()->prepare("UPDATE orders SET payment_authority = ? WHERE id = ?")->execute([$payResult['authority'], $order['id']]);
    redirect($payResult['pay_url']);
}

redirect('/order/failed/' . $order['order_code'] . '?err=' . urlencode($payResult['error']));
