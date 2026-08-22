<?php
require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'روش درخواست نامعتبر است']);
    exit;
}

// CSRF
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'درخواست نامعتبر است، صفحه را رفرش کنید.']);
    exit;
}

$productId = (int) ($_POST['product_id'] ?? 0);
$variantId = !empty($_POST['variant_id']) ? (int) $_POST['variant_id'] : null;
$qty = max(1, (int) ($_POST['qty'] ?? 1));

$stmt = db()->prepare('SELECT id, stock, is_active FROM products WHERE id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product || !$product['is_active']) {
    echo json_encode(['ok' => false, 'message' => 'محصول یافت نشد یا در دسترس نیست.']);
    exit;
}

$availableStock = (int) $product['stock'];
if ($variantId) {
    $vstmt = db()->prepare('SELECT id, stock FROM product_variants WHERE id = ? AND product_id = ?');
    $vstmt->execute([$variantId, $productId]);
    $variant = $vstmt->fetch();
    if (!$variant) {
        echo json_encode(['ok' => false, 'message' => 'گزینه انتخابی معتبر نیست.']);
        exit;
    }
    $availableStock = (int) $variant['stock'];
}

if ($availableStock < 1) {
    echo json_encode(['ok' => false, 'message' => 'این محصول در حال حاضر موجود نیست.']);
    exit;
}

cartAdd($productId, $variantId, $qty);

echo json_encode(['ok' => true, 'cartCount' => cartCount()]);
