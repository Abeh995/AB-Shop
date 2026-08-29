<?php
/**
 * Shopping cart page controller.
 */

$pageTitle = 'سبد خرید';
$cart = cartDetails();
$appliedCoupon = $_SESSION['coupon'] ?? null;

// Revalidate the applied coupon if the updated cart no longer meets its conditions.
$discount = 0;
if ($appliedCoupon) {
    $check = CouponService::validate($appliedCoupon['code'], $cart['subtotal']);
    if ($check['ok']) {
        $discount = $check['discount'];
    } else {
        unset($_SESSION['coupon']);
        $appliedCoupon = null;
    }
}

renderView('site/cart', compact('pageTitle', 'cart', 'appliedCoupon', 'discount'));
