<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $key = $_POST['key'] ?? '';
    cartRemove($key);
    setFlash('success', 'محصول از سبد خرید حذف شد.');
}

redirect('/cart');
