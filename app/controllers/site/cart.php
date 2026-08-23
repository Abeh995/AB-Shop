<?php

$pageTitle = 'سبد خرید';
$cart = cartDetails();
$appliedCoupon = $_SESSION['coupon'] ?? null;

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
