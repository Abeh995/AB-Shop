<?php
$pageTitle = 'داشبورد';

$productCount = db()->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$orderCount = db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingCount = db()->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$revenue = db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status != 'failed' AND status != 'cancelled'")->fetchColumn();
$lowStock = db()->query("SELECT COUNT(*) FROM products WHERE is_active = 1 AND stock <= 5")->fetchColumn();

$recentOrders = db()->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 8")->fetchAll();

$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

renderView('admin/dashboard', compact('pageTitle', 'productCount', 'orderCount', 'pendingCount', 'revenue', 'lowStock', 'recentOrders', 'statusLabels'));
