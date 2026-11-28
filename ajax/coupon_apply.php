<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isStorefrontAjaxRequest()) {
        jsonResponse(['ok' => false, 'message' => 'روش درخواست نامعتبر است'], 405);
    }
    redirect('/cart');
}

verifyCsrf();
$code = trim($_POST['coupon_code'] ?? '');
$cart = cartDetails();
$cust = currentCustomer();
$phone = $cust['phone'] ?? null;

$result = CouponService::validate($code, $cart['subtotal'], $phone, null, $cart['items'] ?? []);
if ($result['ok']) {
    $_SESSION['coupon'] = ['code' => $code, 'coupon_id' => $result['coupon']['id']];
} else {
    unset($_SESSION['coupon']);
}

respondCartMutation((bool) $result['ok'], (string) $result['message'], true);
