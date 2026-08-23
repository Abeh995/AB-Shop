<?php
$pageTitle = 'سفارش‌ها';

$statusFilter = $_GET['status'] ?? '';
$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

$where = '1=1';
$params = [];
if ($statusFilter && isset($statusLabels[$statusFilter])) {
    $where = 'status = ?';
    $params[] = $statusFilter;
}

$stmt = db()->prepare("SELECT * FROM orders WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

renderView('admin/orders', compact('pageTitle', 'statusFilter', 'statusLabels', 'orders'));
