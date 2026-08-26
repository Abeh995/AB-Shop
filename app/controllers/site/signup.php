<?php
/**
 * Customer registration by mobile number.
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

    if ($password !== $passwordConfirm) {
        $errors[] = 'تکرار رمز عبور مطابقت ندارد.';
    } else {
        $result = customerSignup($phone, $password, $fullName ?: null);
        if ($result['ok']) {
            setFlash('success', 'ثبت‌نام با موفقیت انجام شد. خوش آمدید!');
            redirect('/account');
        } else {
            $errors[] = $result['error'];
        }
    }
}

renderView('site/signup', compact('pageTitle', 'errors'));
