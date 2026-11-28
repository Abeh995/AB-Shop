<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isStorefrontAjaxRequest()) {
        jsonResponse(['ok' => false, 'message' => 'روش درخواست نامعتبر است'], 405);
    }
    redirect('/cart');
}

verifyCsrf();
$giftItemId = (int) ($_POST['gift_item_id'] ?? 0);
unset($_SESSION['post_order_selection'][$giftItemId]);

respondCartMutation(true, 'آیتم پیشنهادی از سبد حذف شد.', true);
