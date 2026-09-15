<?php
/**
 * AJAX endpoint for the card-to-card receipt uploader.
 */

require_once __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!isCustomerLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'برای بارگذاری رسید ابتدا وارد حساب کاربری شوید.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    verifyCsrf();
    $result = CardToCardReceiptService::upload($_FILES['receipt'] ?? []);
    if (!$result['ok']) {
        http_response_code(422);
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Card-to-card receipt upload failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'خطا در بارگذاری تصویر رسید.'], JSON_UNESCAPED_UNICODE);
}
