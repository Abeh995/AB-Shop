<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isStorefrontAjaxRequest()) {
        jsonResponse(['ok' => false, 'message' => 'روش درخواست نامعتبر است'], 405);
    }
    redirect('/cart');
}

verifyCsrf();
$giftItemId = (int) ($_POST['gift_item_id'] ?? 0);
$qty = max(1, (int) ($_POST['quantity'] ?? 1));

$stmt = db()->prepare("SELECT id, stock, min_cart_total FROM gift_items WHERE id = ? AND is_active = 1 AND is_post_orderable = 1 AND stock > 0");
$stmt->execute([$giftItemId]);
$giftRow = $stmt->fetch();

if (!$giftRow) {
    respondCartMutation(false, 'این آیتم در دسترس نیست.', true);
}

$cart = cartDetails();
$cartSubtotal = (float) ($cart['subtotal'] ?? 0);
$minTotal = (float) ($giftRow['min_cart_total'] ?? 0);
if ($minTotal > 0 && $cartSubtotal < $minTotal) {
    respondCartMutation(false, 'این پیشنهاد برای سبدهای بالای ' . formatPrice($minTotal) . ' فعال می‌شود.', true);
}
if ($qty > (int) $giftRow['stock']) {
    respondCartMutation(false, 'موجودی این آیتم کافی نیست.', true);
}

$_SESSION['post_order_selection'][$giftItemId] = $qty;
respondCartMutation(true, 'به سبد اضافه شد.', true);
