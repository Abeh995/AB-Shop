<?php
/**
 * Customer account: editable profile data, current cart, and order history.
 */

requireCustomer();
$customer = currentCustomer();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'ایمیل وارد شده معتبر نیست.';
    } else {
        $emailChanged = $email !== ($customer['email'] ?? '');
        if ($emailChanged) {
            // Changing the email address resets its verification status and requires re-verification.
            db()->prepare("UPDATE customers SET full_name = ?, email = ?, email_verified_at = NULL WHERE id = ?")
                ->execute([$fullName ?: null, $email ?: null, $customer['id']]);
        } else {
            db()->prepare("UPDATE customers SET full_name = ? WHERE id = ?")->execute([$fullName ?: null, $customer['id']]);
        }
        setFlash('success', 'اطلاعات پروفایل به‌روزرسانی شد.' . ($emailChanged && $email !== '' ? ' برای فعال‌سازی، ایمیل جدید را تایید کنید.' : ''));
        redirect('/account');
    }
}

$customer = currentCustomer(); // Reload after a possible update because currentCustomer() caches its result.
$freshStmt = db()->prepare("SELECT * FROM customers WHERE id = ?");
$freshStmt->execute([$customer['id']]);
$customer = $freshStmt->fetch();

$cart = cartDetails();

$stmt = db()->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$customer['id']]);
$orders = $stmt->fetchAll();

$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

$pageTitle = 'حساب کاربری';
renderView('site/account', compact('pageTitle', 'customer', 'cart', 'orders', 'statusLabels', 'errors'));
