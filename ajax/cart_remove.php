<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isStorefrontAjaxRequest()) {
        jsonResponse(['ok' => false, 'message' => 'روش درخواست نامعتبر است'], 405);
    }
    redirect('/cart');
}

verifyCsrf();
$key = $_POST['key'] ?? '';
cartRemove($key);

respondCartMutation(true, 'محصول از سبد خرید حذف شد.', true);
