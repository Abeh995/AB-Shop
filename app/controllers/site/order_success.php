<?php

$code = $_GET['code'] ?? '';

$stmt = db()->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
$stmt->execute([$code]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    require __DIR__ . '/not_found.php';
    return;
}

$pageTitle = 'ثبت سفارش موفق';
renderView('site/order_success', compact('pageTitle', 'order'));
