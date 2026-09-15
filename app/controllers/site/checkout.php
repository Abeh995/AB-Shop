<?php
/**
 * Checkout controller — collects and validates customer/order details, then
 * routes the customer to the selected payment flow.
 */

if (!isCustomerLoggedIn()) {
    setFlash('info', 'برای نهایی کردن سفارش ابتدا یک حساب کاربری بسازید یا وارد شوید.');
    redirect('/signup?next=' . urlencode('/checkout'));
}

$pageTitle = 'تسویه حساب';
$errors = [];
$prefillCustomer = currentCustomer();

$cart = cartDetails();
if (empty($cart['items'])) {
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
$shippingPreview = calculateShippingCost($_POST['province'] ?? '', $cart['subtotal']);

$zarinpalEnabled = getSetting('payment_zarinpal_enabled', '1') === '1';
$cardToCardConfigured = getSetting('card_to_card_number', '') !== '' && getSetting('card_to_card_holder', '') !== '';
$paymentMethodsAvailable = $zarinpalEnabled || $cardToCardConfigured;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $data = [
        'customer_name' => trim($_POST['customer_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'province' => trim($_POST['province'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'postal_code' => trim($_POST['postal_code'] ?? ''),
        'notes' => trim($_POST['notes'] ?? ''),
    ];
    $paymentMethod = $_POST['payment_method'] ?? '';
    $errors = OrderService::validateCheckoutData($data);

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
        $_SESSION['pending_card_to_card_checkout'] = $data;
        CardToCardReceiptService::discardPending();
        redirect('/payment/card-to-card');
    }

    if (!$errors && $paymentMethod === 'zarinpal') {
        $result = OrderService::createFromCheckout($data, 'zarinpal');
        if ($result['ok']) {
            $callbackUrl = rtrim(SITE_URL, '/') . '/payment/zarinpal_callback.php';
            $payResult = ZarinpalService::request((int) $result['total'], 'پرداخت سفارش ' . $result['order_code'], $callbackUrl, $data['phone'], $data['email'] ?: null);

            if ($payResult['ok']) {
                db()->prepare("UPDATE orders SET payment_authority = ? WHERE id = ?")->execute([$payResult['authority'], $result['order_id']]);
                redirect($payResult['pay_url']);
            }

            redirect('/order/failed/' . $result['order_code'] . '?err=' . urlencode($payResult['error']));
        }
        $errors[] = $result['error'];
    }
}

renderView('site/checkout', compact(
    'pageTitle', 'errors', 'cart', 'appliedCoupon', 'discount', 'prefillCustomer',
    'postOrderResult', 'shippingPreview', 'zarinpalEnabled', 'cardToCardConfigured', 'paymentMethodsAvailable'
));
