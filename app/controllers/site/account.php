<?php
/**
 * Customer account page — profile information and order history.
 */

requireCustomer();

$pageTitle = 'حساب کاربری';
$customer = currentCustomer();

$stmt = db()->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$customer['id']]);
$orders = $stmt->fetchAll();

$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

renderView('site/account', compact('pageTitle', 'customer', 'orders', 'statusLabels'));
