<?php
/**
 * Checkout controller — collects customer details and routes to payment flow.
 */
if (!isCustomerLoggedIn()) {
    setFlash('info', 'برای نهایی کردن سفارش ابتدا یک حساب کاربری بسازید یا وارد شوید.');
    redirect('/signup?next=' . urlencode('/checkout'));
}

$pageTitle = 'تسویه حساب';
$errors = [];
$prefillCustomer = currentCustomer() ?: [];

$cart = cartDetails();
if (empty($cart['items'])) redirect('/cart');

if (getSetting('store_order_status', 'active') === 'paused') {
    setFlash('error', getSetting('store_paused_message', 'ثبت سفارش موقتاً به دلیل انبارگردانی یا تعطیلات متوقف شده است.'));
    redirect('/cart');
}
$minOrderAmount = (int) getSetting('min_order_amount', '0');
if ($minOrderAmount > 0 && $cart['subtotal'] < $minOrderAmount) {
    setFlash('error', 'حداقل مبلغ سفارش برای ثبت نهایی ' . number_format($minOrderAmount) . ' تومان می‌باشد.');
    redirect('/cart');
}

$appliedCoupon = $_SESSION['coupon'] ?? null;
$discount = 0;
if ($appliedCoupon) {
    $check = CouponService::validate($appliedCoupon['code'], $cart['subtotal']);
    if ($check['ok']) { $discount = $check['discount']; } else { unset($_SESSION['coupon']); $appliedCoupon = null; }
}

$postOrderResult = validatePostOrderSelection($_SESSION['post_order_selection'] ?? []);
$zarinpalEnabled = getSetting('payment_zarinpal_enabled', '1') === '1';
$cardToCardConfigured = getSetting('card_to_card_number', '') !== '' && getSetting('card_to_card_holder', '') !== '';
$paymentMethodsAvailable = $zarinpalEnabled || $cardToCardConfigured;

$formData = [
    'customer_name' => $prefillCustomer['full_name'] ?? '',
    'phone' => $prefillCustomer['phone'] ?? '',
    'email' => $prefillCustomer['email'] ?? '',
    'province' => '', 'city' => '', 'address' => '', 'postal_code' => '', 'notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach ($formData as $k => $v) $formData[$k] = trim($_POST[$k] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? '';
    $errors = OrderService::validateCheckoutData($formData);

    if (!$paymentMethodsAvailable) {
        $errors[] = 'در حال حاضر هیچ روش پرداخت فعالی برای فروشگاه تنظیم نشده است.';
    } elseif ($paymentMethod === 'zarinpal' && !$zarinpalEnabled) {
        $errors[] = 'پرداخت زرین‌پال در حال حاضر توسط فروشگاه غیرفعال است.';
    } elseif ($paymentMethod === 'card_to_card' && !$cardToCardConfigured) {
        $errors[] = 'پرداخت کارت‌به‌کارت هنوز توسط فروشگاه پیکربندی نشده است.';
    } elseif (!in_array($paymentMethod, ['zarinpal', 'card_to_card'], true)) {
        $errors[] = 'لطفاً یک روش پرداخت معتبر انتخاب کنید.';
    }

    if (!$errors && $paymentMethod === 'card_to_card') {
        $_SESSION['pending_card_to_card_checkout'] = $formData;
        CardToCardReceiptService::discardPending();
        redirect('/payment/card-to-card');
    }
    if (!$errors && $paymentMethod === 'zarinpal') {
        $result = OrderService::createFromCheckout($formData, 'zarinpal');
        if ($result['ok']) {
            $callbackUrl = rtrim(SITE_URL, '/') . '/payment/zarinpal_callback.php';
            $payResult = ZarinpalService::request((int)$result['total'], 'پرداخت سفارش ' . $result['order_code'], $callbackUrl, $formData['phone'], $formData['email'] ?: null);
            if ($payResult['ok']) {
                OrderService::setPaymentAuthority((int)$result['order_id'], (string)$payResult['authority']);
                redirect($payResult['pay_url']);
            }
            redirect('/order/failed/' . $result['order_code'] . '?err=' . urlencode($payResult['error']));
        }
        $errors[] = $result['error'];
    }
}
$shippingPreview = calculateShippingCost($formData['province'], $cart['subtotal']);

renderView('site/checkout', compact(
    'pageTitle', 'errors', 'cart', 'appliedCoupon', 'discount', 'formData',
    'postOrderResult', 'shippingPreview', 'zarinpalEnabled', 'cardToCardConfigured', 'paymentMethodsAvailable'
));
