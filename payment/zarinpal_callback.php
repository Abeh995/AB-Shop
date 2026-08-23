<?php
/**
 * نقطه بازگشت از درگاه زرین‌پال بعد از پرداخت (موفق یا ناموفق)
 * زرین‌پال کاربر را با این پارامترها به این آدرس هدایت می‌کند: ?Authority=...&Status=OK|NOK
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
    // چنین سفارشی با این Authority پیدا نشد (لینک نامعتبر/تکراری)
    redirect('/');
}

// اگر قبلا verify و paid شده (مثلا کاربر دکمه Back را زده و دوباره این صفحه لود شده)، دوباره از کاربر پول کم نمی‌شود
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

    redirect('/order/success/' . $order['order_code']);
} else {
    db()->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?")->execute([$order['id']]);
    redirect('/order/failed/' . $order['order_code'] . '?err=' . urlencode($verify['error']));
}
