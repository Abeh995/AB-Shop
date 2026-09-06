<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $giftItemId = (int) ($_POST['gift_item_id'] ?? 0);
    $qty = max(1, (int) ($_POST['quantity'] ?? 1));

    $stmt = db()->prepare("SELECT id FROM gift_items WHERE id = ? AND is_active = 1 AND is_post_orderable = 1 AND stock > 0");
    $stmt->execute([$giftItemId]);
    if ($stmt->fetch()) {
        $_SESSION['post_order_selection'][$giftItemId] = $qty;
        setFlash('success', 'به سبد اضافه شد.');
    } else {
        setFlash('error', 'این آیتم در دسترس نیست.');
    }
}

redirect('/cart');
