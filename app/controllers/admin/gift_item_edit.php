<?php
/**
 * Gift Item Edit & Create Controller
 * Thin controller managing creation and edits of gift/post-order catalog items.
 */

$id = (int) ($_GET['id'] ?? 0);
$item = $id > 0 ? getAdminGiftItemById($id) : null;

if ($id > 0 && !$item) {
    setFlash('error', 'آیتم مورد نظر یافت نشد.');
    redirect('gift_items.php');
}

$pageTitle = $item ? 'ویرایش آیتم هدیه' : 'آیتم هدیه جدید';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $adminId = (int) ($_SESSION['admin_id'] ?? 0);
    $payload = $_POST;
    $payload['id'] = $id;

    $result = saveGiftItem($payload, $_FILES['image'] ?? null, $adminId);

    if ($result['ok']) {
        setFlash('success', $id > 0 ? 'آیتم به‌روزرسانی شد.' : 'آیتم جدید با موفقیت اضافه شد.');
        redirect('gift_items.php');
    } else {
        $errors = $result['errors'] ?? ['خطا در ثبت اطلاعات.'];
        // Preserve user input
        $item = array_merge($item ?? [], [
            'name' => trim($_POST['name'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_giftable' => isset($_POST['is_giftable']) ? 1 : 0,
            'is_post_orderable' => isset($_POST['is_post_orderable']) ? 1 : 0,
            'cost_price' => trim($_POST['cost_price'] ?? ''),
            'post_order_price' => trim($_POST['post_order_price'] ?? ''),
            'stock' => (int) ($_POST['stock'] ?? 0),
        ]);
    }
}

renderView('admin/gift_item_edit', compact('pageTitle', 'item', 'errors'));
