<?php
/**
 * Gift box / post-order item catalog — list, delete.
 */

$pageTitle = 'آیتم‌های هدیه و پیشنهاد بعد از سبد';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);

    $stmt = db()->prepare("SELECT image FROM gift_items WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists(UPLOAD_DIR . $img)) {
        @unlink(UPLOAD_DIR . $img);
    }

    // order_gift_items.gift_item_id is ON DELETE SET NULL, so past orders
    // keep their snapshot (name/image/cost/price) even after this delete.
    db()->prepare("DELETE FROM gift_items WHERE id = ?")->execute([$id]);
    setFlash('success', 'آیتم حذف شد.');
    redirect('gift_items.php');
}

$search = trim($_GET['q'] ?? '');
$where = '1=1';
$params = [];
if ($search !== '') {
    $where .= ' AND name LIKE ?';
    $params[] = '%' . $search . '%';
}

$stmt = db()->prepare("SELECT * FROM gift_items WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$items = $stmt->fetchAll();

renderView('admin/gift_items', compact('pageTitle', 'search', 'items'));
