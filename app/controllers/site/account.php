<?php
/**
 * Customer account page — editable profile info, current cart, order history
 */

requireCustomer();
$customer = currentCustomer();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $res = CustomerService::updateProfile((int) $customer['id'], $fullName, $email);
    if (!$res['ok']) {
        $errors[] = $res['error'];
    } else {
        setFlash('success', 'اطلاعات پروفایل به‌روزرسانی شد.' . ($res['email_changed'] && $email !== '' ? ' برای فعال‌سازی، ایمیل جدید را تایید کنید.' : ''));
        redirect('/account');
    }
}

$customer = CustomerService::getById((int) $customer['id']) ?? $customer;
$cart = cartDetails();
$orders = CustomerService::getOrders((int) $customer['id']);
$statusLabels = OrderService::statusLabels();

$pageTitle = 'حساب کاربری';
renderView('site/account', compact('pageTitle', 'customer', 'cart', 'orders', 'statusLabels', 'errors'));
