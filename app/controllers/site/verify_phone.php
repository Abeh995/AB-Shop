<?php
/**
 * Phone-verification page — after signup or an incomplete login, the
 * customer enters their SMS code here.
 */

$customerId = pendingCustomerId();
if (!$customerId) {
    redirect(isCustomerLoggedIn() ? '/account' : '/login');
}

$stmt = db()->prepare("SELECT phone FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$phone = $stmt->fetchColumn();

$pageTitle = 'احراز شماره موبایل';
$error = '';
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'verify';

    if ($action === 'resend') {
        $result = VerificationService::sendCode($customerId, 'phone', $phone);
        if ($result['ok']) {
            $info = 'کد جدید پیامک شد.';
        } else {
            $error = $result['error'] ?: 'امکان ارسال کد در حال حاضر وجود ندارد. با پشتیبانی تماس بگیرید.';
        }
    } else {
        $code = trim($_POST['code'] ?? '');
        $result = VerificationService::verifyCode($customerId, 'phone', $code);
        if ($result['ok']) {
            completeCustomerLogin($customerId);
            setFlash('success', 'شماره موبایل شما با موفقیت تایید شد. خوش آمدید!');

            // If an email was also given at signup and isn't verified yet, the next step is email verification
            $emailStmt = db()->prepare("SELECT email, email_verified_at FROM customers WHERE id = ?");
            $emailStmt->execute([$customerId]);
            $customerRow = $emailStmt->fetch();
            if (!empty($customerRow['email']) && empty($customerRow['email_verified_at'])) {
                VerificationService::sendCode($customerId, 'email', $customerRow['email']);
                redirect('/verify-email');
            }

            redirect('/account');
        } else {
            $error = $result['error'];
        }
    }
}

renderView('site/verify_phone', compact('pageTitle', 'phone', 'error', 'info'));
