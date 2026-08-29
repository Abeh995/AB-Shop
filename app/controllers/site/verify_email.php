<?php
/**
 * Email verification page, accessible after mobile verification during signup and from the customer profile.
 */

requireCustomer();
$customer = currentCustomer();

if (empty($customer['email'])) {
    redirect('/account');
}
if (!empty($customer['email_verified_at'])) {
    setFlash('info', 'ایمیل شما قبلاً تایید شده است.');
    redirect('/account');
}

$pageTitle = 'احراز ایمیل';
$error = '';
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'verify';

    if ($action === 'resend') {
        $result = VerificationService::sendCode($customer['id'], 'email', $customer['email']);
        if ($result['ok']) {
            $info = 'کد جدید ایمیل شد.';
        } else {
            $error = $result['error'] ?: 'امکان ارسال ایمیل در حال حاضر وجود ندارد. با پشتیبانی تماس بگیرید.';
        }
    } else {
        $code = trim($_POST['code'] ?? '');
        $result = VerificationService::verifyCode($customer['id'], 'email', $code);
        if ($result['ok']) {
            setFlash('success', 'ایمیل شما با موفقیت تایید شد.');
            redirect('/account');
        } else {
            $error = $result['error'];
        }
    }
}

renderView('site/verify_email', compact('pageTitle', 'customer', 'error', 'info'));
