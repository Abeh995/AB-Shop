<?php
/**
 * Customer registration using a mobile number. After registration, the mobile number
 * must be verified by SMS before the login session is completed.
 */

if (isCustomerLoggedIn()) {
    redirect('/account');
}

$pageTitle = 'ثبت‌نام';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

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

renderView('site/signup', compact('pageTitle', 'errors'));
