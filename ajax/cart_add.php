<?php
require_once __DIR__ . '/../app/bootstrap.php';

$respond = static function (bool $ok, string $message, string $fallbackUrl = '/cart', int $status = 200): void {
    if (isStorefrontAjaxRequest()) {
        jsonResponse(['ok' => $ok, 'message' => $message, 'cartCount' => cartCount()], $status);
    }
    if ($message !== '') {
        setFlash($ok ? 'success' : 'error', $message);
    }
    redirect($fallbackUrl);
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $respond(false, 'روش درخواست نامعتبر است.', '/cart', 405);
}

verifyCsrf();

$productId = (int) ($_POST['product_id'] ?? 0);
$variantId = !empty($_POST['variant_id']) ? (int) $_POST['variant_id'] : null;
$qty = max(1, (int) ($_POST['qty'] ?? 1));

// Real validation of the product and its stock against the database (never trust user input)
$stmt = db()->prepare('SELECT id, slug, stock, is_active FROM products WHERE id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product || !$product['is_active']) {
    $respond(false, 'محصول یافت نشد یا در دسترس نیست.', '/cart');
}

$productUrl = '/product/' . rawurlencode((string) $product['slug']);

// Check if this product has variants
$vCountStmt = db()->prepare('SELECT COUNT(*) FROM product_variants WHERE product_id = ?');
$vCountStmt->execute([$productId]);
$variantCount = (int) $vCountStmt->fetchColumn();

if ($variantCount > 0 && !$variantId) {
    $respond(false, 'لطفاً یکی از گزینه‌های محصول (سایز / رنگ) را انتخاب کنید.', $productUrl);
}

$availableStock = (int) $product['stock'];
if ($variantId) {
    $vstmt = db()->prepare('SELECT id, stock FROM product_variants WHERE id = ? AND product_id = ?');
    $vstmt->execute([$variantId, $productId]);
    $variant = $vstmt->fetch();
    if (!$variant) {
        $respond(false, 'گزینه انتخابی معتبر نیست.', $productUrl);
    }
    $availableStock = (int) $variant['stock'];
}

if ($availableStock < 1) {
    $respond(false, 'این گزینه در حال حاضر موجود نیست.', $productUrl);
}

$existingQty = cartGetItemQty($productId, $variantId);
if (($existingQty + $qty) > $availableStock) {
    $remaining = max(0, $availableStock - $existingQty);
    $msg = $remaining > 0
        ? 'در سبد شما ' . toPersianDigits((string) $existingQty) . ' عدد موجود است؛ حداکثر ' . toPersianDigits((string) $remaining) . ' عدد دیگر می‌توانید اضافه کنید.'
        : 'حداکثر موجودی انبار از این کالا هم‌اکنون در سبد خرید شماست.';
    $respond(false, $msg, $productUrl);
}

cartAdd($productId, $variantId, $qty);

$respond(true, 'محصول به سبد خرید اضافه شد.', '/cart');
