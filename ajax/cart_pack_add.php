<?php
require_once __DIR__ . '/../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'روش درخواست نامعتبر است']);
    exit;
}

// CSRF verification
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'درخواست نامعتبر است، صفحه را رفرش کنید.']);
    exit;
}

$packName = trim($_POST['name'] ?? 'پک اختصاصی');
$note = trim($_POST['note'] ?? '');
$rawProductIds = $_POST['product_ids'] ?? '[]';
$productIds = json_decode($rawProductIds, true);

if (!is_array($productIds) || empty($productIds)) {
    // If no specific product IDs provided, attempt to add default featured active sock
    $stmt = db()->query("SELECT id FROM products WHERE is_active = 1 LIMIT 1");
    $defaultPid = $stmt->fetchColumn();
    if ($defaultPid) {
        $productIds = [(int) $defaultPid];
    }
}

// Add chosen socks to the real cart
if (is_array($productIds)) {
    foreach ($productIds as $pid) {
        $productId = (int) $pid;
        if ($productId <= 0) continue;

        $stmt = db()->prepare("SELECT id, stock, is_active FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $prod = $stmt->fetch();
        if ($prod && $prod['is_active'] && (int)$prod['stock'] > 0) {
            cartAdd($productId, null, 1);
        }
    }
}

if ($note !== '') {
    $_SESSION['order_custom_pack'] = [
        'name' => $packName,
        'note' => $note,
        'added_at' => date('Y-m-d H:i:s'),
    ];
}

echo json_encode([
    'ok' => true,
    'cartCount' => cartCount(),
    'message' => 'پک اختصاصی با موفقیت به سبد خرید اضافه شد.',
]);
