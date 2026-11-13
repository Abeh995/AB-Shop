<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $key = $_POST['key'] ?? '';
    cartRemove($key);
    setFlash('success', 'محصول از سبد خرید حذف شد.');

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        || !empty($_POST['ajax']);

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'cartCount' => cartCount()]);
        exit;
    }
}

redirect('/cart');
