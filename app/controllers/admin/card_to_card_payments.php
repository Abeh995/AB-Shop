<?php
/**
 * Admin queue for card-to-card payment receipts awaiting review.
 */

$pageTitle = 'بررسی پرداخت‌های کارت‌به‌کارت';

$stmt = db()->query("SELECT * FROM orders WHERE payment_method = 'card_to_card' ORDER BY created_at DESC");
$orders = $stmt->fetchAll();

$paymentLabels = ['unpaid' => 'در انتظار بررسی', 'paid' => 'تایید شده', 'failed' => 'رد شده'];
$paymentClasses = ['unpaid' => 'status-pending', 'paid' => 'status-delivered', 'failed' => 'status-cancelled'];
$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

renderView('admin/card_to_card_payments', compact('pageTitle', 'orders', 'paymentLabels', 'paymentClasses', 'statusLabels'));
