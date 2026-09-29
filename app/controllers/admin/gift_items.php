<?php
/**
 * Gift Box & Post-Order Catalog Controller
 * Thin controller managing the list and deletion of gift items.
 */

$pageTitle = 'اقلام هدیه و پیشنهاد بعد از سبد';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        deleteGiftItem($id);
        setFlash('success', 'آیتم با موفقیت حذف شد.');
    }
    redirect('gift_items.php');
}

$search = trim($_GET['q'] ?? '');
$items = getAdminGiftItemsList($search);

renderView('admin/gift_items', compact('pageTitle', 'search', 'items'));
