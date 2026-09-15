<?php
/**
 * Customer signup by mobile number — after a successful signup, the customer
 * must verify their phone number with an SMS code before login is complete.
 */

if (isCustomerLoggedIn()) {
    redirect('/account');
}

$pageTitle = 'ثبت‌نام';
$errors = [];
$next = $_GET['next'] ?? $_POST['next'] ?? '/account';
if (!is_string($next) || strpos($next, '/') !== 0 || strpos($next, '//') === 0) {
    $next = '/account';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $_SESSION['pending_auth_next'] = $next;

    if ($password !== $passwordConfirm) {
        $errors[] = 'تکرار رمز عبور مطابقت ندارد.';
    } else {
        $result = customerSignup($phone, $password, $fullName ?: null, $email ?: null);
        if ($result['ok']) {
            VerificationService::sendCode($result['customer_id'], 'phone', $phone);
            redirect('/verify-phone');
        } else {
            $errors[] = $result['error'];
        }
    }
}

renderView('site/signup', compact('pageTitle', 'errors', 'next'));
