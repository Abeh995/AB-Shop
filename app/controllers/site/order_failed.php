<?php
/**
 * Controller for the payment failure page after a failed ZarinPal return or gateway error.
 */

$code = $_GET['code'] ?? '';
$errorMessage = $_GET['err'] ?? null;

$stmt = db()->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
$stmt->execute([$code]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    require __DIR__ . '/not_found.php';
    return;
}

$pageTitle = 'پرداخت ناموفق';
renderView('site/order_failed', compact('pageTitle', 'order', 'errorMessage'));
