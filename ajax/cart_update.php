<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $key = $_POST['key'] ?? '';
    $qty = max(0, (int) ($_POST['qty'] ?? 1));
    cartUpdateQty($key, $qty);
}

redirect('/cart');
