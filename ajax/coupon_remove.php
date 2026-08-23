<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    unset($_SESSION['coupon']);
    setFlash('success', 'کد تخفیف حذف شد.');
}

redirect('/cart');
