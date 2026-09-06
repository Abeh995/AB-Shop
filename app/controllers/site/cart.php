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

// Post-order add-ons: what the customer has selected so far (re-validated
// against the live catalog) plus what else is currently offerable
$postOrderResult = validatePostOrderSelection($_SESSION['post_order_selection'] ?? []);
$selectedPostOrderIds = array_column($postOrderResult['lines'], 'gift_item_id');
$availablePostOrderItems = array_filter(
    getAvailablePostOrderItems(),
    fn($item) => !in_array((int) $item['id'], $selectedPostOrderIds, true)
);

renderView('site/cart', compact('pageTitle', 'cart', 'appliedCoupon', 'discount', 'postOrderResult', 'availablePostOrderItems'));
