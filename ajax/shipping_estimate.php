<?php
require_once __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$province = trim($_POST['province'] ?? '');

// Recompute every part of the total server-side — subtotal, an applied
// coupon, and post-order add-ons — the same way checkout.php itself does,
// so the JSON response can hand back one ready-to-display grand total
// instead of the client trying to reproduce formatPrice()'s formatting.
$cart = cartDetails();
$subtotal = $cart['subtotal'];

$discount = 0;
$appliedCoupon = $_SESSION['coupon'] ?? null;
if ($appliedCoupon) {
    $check = CouponService::validate($appliedCoupon['code'], $subtotal);
    if ($check['ok']) {
        $discount = $check['discount'];
    }
}

$postOrderResult = validatePostOrderSelection($_SESSION['post_order_selection'] ?? []);
$shipping = calculateShippingCost($province, $subtotal);
$grandTotal = max(0, $subtotal - $discount + $shipping['cost'] + $postOrderResult['total']);

echo json_encode([
    'ok' => true,
    'cost' => $shipping['cost'],
    'cost_formatted' => formatPrice($shipping['cost']),
    'method_name' => $shipping['method_name'],
    'is_free' => $shipping['is_free'],
    'grand_total_formatted' => formatPrice($grandTotal),
], JSON_UNESCAPED_UNICODE);
