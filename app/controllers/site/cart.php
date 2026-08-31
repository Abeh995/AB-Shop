<?php
/**
 * Cart page controller
 */

$pageTitle = 'سبد خرید';
$cart = cartDetails();
$appliedCoupon = $_SESSION['coupon'] ?? null;

// Re-validate an already-applied coupon in case it no longer matches the current cart (e.g. the total dropped)
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
