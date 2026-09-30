<?php
/**
 * Return point from the Zarinpal gateway after payment (successful or not)
 * Zarinpal redirects the customer to this URL with these parameters:
 * ?Authority=...&Status=OK|NOK
 */

require_once __DIR__ . '/../app/bootstrap.php';

$authority = $_GET['Authority'] ?? '';
$status = $_GET['Status'] ?? '';

if ($authority === '') {
    redirect('/');
}

$stmt = db()->prepare("SELECT * FROM orders WHERE payment_authority = ? LIMIT 1");
$stmt->execute([$authority]);
$order = $stmt->fetch();

if (!$order) {
    // No order was found with this Authority (an invalid/duplicate link)
    redirect('/');
}

// If it was already verified and paid (e.g. the customer hit Back and this page loaded again), the customer isn't charged again
if ($order['payment_status'] === 'paid') {
    redirect('/order/success/' . $order['order_code']);
}

if ($status !== 'OK') {
    db()->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?")->execute([$order['id']]);
    redirect('/order/failed/' . $order['order_code'] . '?err=' . urlencode('پرداخت توسط شما لغو شد.'));
}

$verify = ZarinpalService::verify((int) $order['total'], $authority);

if ($verify['ok']) {
    db()->prepare("UPDATE orders SET payment_status = 'paid', payment_ref_id = ?, status = 'confirmed' WHERE id = ?")
        ->execute([$verify['ref_id'], $order['id']]);

    SmsService::notifyOrderConfirmed($order['phone'], $order['order_code']);

    dispatchSmsEvent('order_paid', [
        'order_code'     => $order['order_code'],
        'customer_name'  => $order['customer_name'] ?? '',
        'customer_phone' => $order['phone'] ?? '',
        'total_price'    => number_format((int) ($order['total'] ?? 0)),
        'ref_id'         => (string) $verify['ref_id'],
        'site_title'     => defined('SITE_NAME') ? SITE_NAME : '',
    ], $order['phone']);

    redirect('/order/success/' . $order['order_code']);
} else {
    db()->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?")->execute([$order['id']]);
    redirect('/order/failed/' . $order['order_code'] . '?err=' . urlencode($verify['error']));
}
