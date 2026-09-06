<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $giftItemId = (int) ($_POST['gift_item_id'] ?? 0);
    unset($_SESSION['post_order_selection'][$giftItemId]);
}

redirect('/cart');
