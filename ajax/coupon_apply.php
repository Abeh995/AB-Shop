<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $code = trim($_POST['coupon_code'] ?? '');
    $cart = cartDetails();

    $result = CouponService::validate($code, $cart['subtotal']);
    if ($result['ok']) {
        $_SESSION['coupon'] = ['code' => $code, 'coupon_id' => $result['coupon']['id']];
        setFlash('success', $result['message']);
    } else {
        unset($_SESSION['coupon']);
        setFlash('error', $result['message']);
    }
}

redirect('/cart');
