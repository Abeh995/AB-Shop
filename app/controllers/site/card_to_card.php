<?php
/**
 * Card-to-card payment page — shown after checkout details are validated.
 * The order is created only after a receipt has been uploaded and submitted.
 */

requireCustomer();

$pending = $_SESSION['pending_card_to_card_checkout'] ?? null;
if (!is_array($pending)) {
    redirect('/checkout');
}

$cardNumber = trim((string) getSetting('card_to_card_number', ''));
$cardHolder = trim((string) getSetting('card_to_card_holder', ''));
$cardNote = trim((string) getSetting('card_to_card_note', ''));
if ($cardNumber === '' || $cardHolder === '') {
    unset($_SESSION['pending_card_to_card_checkout']);
    setFlash('error', 'اطلاعات پرداخت کارت‌به‌کارت هنوز توسط فروشگاه تکمیل نشده است.');
    redirect('/checkout');
}

$pageTitle = 'پرداخت کارت‌به‌کارت';
$errors = [];

$cart = cartDetails();
if (empty($cart['items'])) {
    unset($_SESSION['pending_card_to_card_checkout']);
    redirect('/cart');
}

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
$postOrderResult = validatePostOrderSelection($_SESSION['post_order_selection'] ?? []);
$shippingPreview = calculateShippingCost($pending['province'], $cart['subtotal']);
$grandTotal = (int) max(0, $cart['subtotal'] - $discount + $shippingPreview['cost'] + $postOrderResult['total']);
$receiptUploaded = CardToCardReceiptService::hasPending();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    if (!CardToCardReceiptService::hasPending()) {
        $errors[] = 'لطفاً تصویر فیش واریز را بارگذاری کنید.';
    } else {
        $result = OrderService::createFromCheckout($pending, 'card_to_card');
        if ($result['ok']) {
            unset($_SESSION['pending_card_to_card_checkout']);
            redirect('/order/success/' . $result['order_code']);
        }
        $errors[] = $result['error'];
    }
    $receiptUploaded = CardToCardReceiptService::hasPending();
}

renderView('site/card_to_card', compact(
    'pageTitle', 'errors', 'pending', 'cardNumber', 'cardHolder', 'cardNote',
    'cart', 'discount', 'appliedCoupon', 'postOrderResult', 'shippingPreview',
    'grandTotal', 'receiptUploaded'
));
