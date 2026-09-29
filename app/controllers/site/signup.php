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

$formData = [
    'phone' => '',
    'full_name' => '',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['full_name'] = trim($_POST['full_name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $_SESSION['pending_auth_next'] = $next;

    if ($password !== $passwordConfirm) {
        $errors[] = 'تکرار رمز عبور مطابقت ندارد.';
    } else {
        $result = customerSignup($formData['phone'], $password, $formData['full_name'] ?: null, $formData['email'] ?: null);
        if ($result['ok']) {
            VerificationService::sendCode($result['customer_id'], 'phone', $formData['phone']);
            redirect('/verify-phone');
        } else {
            $errors[] = $result['error'];
        }
    }
}

renderView('site/signup', compact('pageTitle', 'errors', 'next', 'formData'));
