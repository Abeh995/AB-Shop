<?php
/**
 * Email-verification page — reachable both from the signup flow (after
 * phone verification) and from the customer's profile page.
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

// Auto-send the code only on the first visit to this page (GET), not on
// every request — if this also ran on every POST (whether resending or
// submitting the entered code), a fresh code could replace the previous
// one at the exact moment the user submits it, wrongly rejecting their
// correct code (if more than 60 seconds passed between receiving the code
// and submitting the form).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $result = VerificationService::sendCode($customer['id'], 'email', $customer['email']);
    if (!$result['ok'] && $result['error']) {
        $error = $result['error'];
    }
}

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
