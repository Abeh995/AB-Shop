<?php
/**
 * Customer login by mobile number.
 */

if (isCustomerLoggedIn()) {
    redirect('/account');
}

$pageTitle = 'ورود به حساب کاربری';
$error = '';
$next = $_GET['next'] ?? $_POST['next'] ?? '/account';
// Prevent open redirects: accept only internal paths that start with /.
if (!is_string($next) || strpos($next, '/') !== 0 || strpos($next, '//') === 0) {
    $next = '/account';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (attemptCustomerLogin($phone, $password)) {
        redirect($next);
    } else {
        $error = 'شماره موبایل یا رمز عبور اشتباه است.';
    }
}

renderView('site/login', compact('pageTitle', 'error', 'next'));
