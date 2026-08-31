<?php
/**
 * Customer login by mobile number
 */

if (isCustomerLoggedIn()) {
    redirect('/account');
}

$pageTitle = 'ورود به حساب کاربری';
$error = '';
$next = $_GET['next'] ?? $_POST['next'] ?? '/account';
// Prevent an open redirect: only internal paths (starting with /) are accepted
if (!is_string($next) || strpos($next, '/') !== 0 || strpos($next, '//') === 0) {
    $next = '/account';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = attemptCustomerLogin($phone, $password);
    if ($result['ok']) {
        redirect($next);
    } elseif ($result['needs_verification']) {
        VerificationService::sendCode($result['customer_id'], 'phone', $phone);
        setFlash('info', 'حساب شما هنوز احراز نشده؛ کد تایید مجدد برای شما پیامک شد.');
        redirect('/verify-phone');
    } else {
        $error = $result['error'];
    }
}

renderView('site/login', compact('pageTitle', 'error', 'next'));
