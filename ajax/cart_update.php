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
$qty = max(0, (int) ($_POST['qty'] ?? 1));
cartUpdateQty($key, $qty);

$msg = $qty <= 0 ? 'محصول از سبد خرید حذف شد.' : 'تعداد محصول در سبد خرید به‌روزرسانی شد.';
respondCartMutation(true, $msg, true);
