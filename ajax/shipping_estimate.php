<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'روش درخواست نامعتبر است'], 405);
}

verifyCsrf();

$province = trim($_POST['province'] ?? '');

// Recompute every part of the total server-side — subtotal, an applied
// coupon, and post-order add-ons — using identical parameters to checkout.php.
$cart = cartDetails();
$subtotal = (float) ($cart['subtotal'] ?? 0);
$shipping = calculateShippingCost($province, $subtotal);

$discount = 0;
$appliedCoupon = $_SESSION['coupon'] ?? null;
if ($appliedCoupon) {
    $cust = currentCustomer();
    $phone = $cust['phone'] ?? null;
    $check = CouponService::validate(
        $appliedCoupon['code'],
        $subtotal,
        $phone,
        (int) ($shipping['cost'] ?? 0),
        $cart['items'] ?? []
    );
    if ($check['ok']) {
        $discount = (int) $check['discount'];
    }
}

$postOrderResult = validatePostOrderSelection($_SESSION['post_order_selection'] ?? [], (int) $subtotal);
$grandTotal = max(0, $subtotal - $discount + (float) ($shipping['cost'] ?? 0) + (float) ($postOrderResult['total'] ?? 0));
jsonResponse([
    'ok' => true,
    'cost' => $shipping['cost'],
    'cost_formatted' => formatPrice($shipping['cost']),
    'method_name' => $shipping['method_name'],
    'estimated_delivery' => $shipping['estimated_delivery'] ?? null,
    'is_free' => $shipping['is_free'],
    'grand_total_formatted' => formatPrice($grandTotal),
]);
