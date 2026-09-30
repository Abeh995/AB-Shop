<?php
/**
 * Gift Box & Post-Order Catalog Workstation Controller
 * Thin controller managing the list, metrics, and instant creation/edit operations.
 */

$pageTitle = 'اقلام هدیه و پیشنهاد بعد از سبد';
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        deleteGiftItem($id);
        setFlash('success', 'آیتم با موفقیت حذف شد.');
        redirect('gift_items.php');
    }

    if ($action === 'toggle_active' && $id > 0) {
        $res = toggleGiftItemActive($id);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }
        redirect('gift_items.php');
    }

    if ($action === 'quick_stock' && $id > 0) {
        $newStock = (int) ($_POST['stock'] ?? 0);
        $res = updateGiftItemStock($id, $newStock);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }
        redirect('gift_items.php');
    }

    if ($action === 'save') {
        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        $res = saveGiftItem($_POST, $_FILES['image'] ?? null, $adminId);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }
        if ($res['ok']) {
            setFlash('success', $id > 0 ? 'آیتم به‌روزرسانی شد.' : 'آیتم جدید با موفقیت اضافه شد.');
        } else {
            setFlash('error', implode(' ', $res['errors'] ?? ['خطا در ثبت اطلاعات.']));
        }
        redirect('gift_items.php');
    }
}

$search = trim($_GET['q'] ?? '');
$roleFilter = trim($_GET['role'] ?? 'all');
$editId = (int) ($_GET['edit'] ?? 0);

$metrics = getAdminGiftItemsMetrics();
$items = getAdminGiftItemsList($search, $roleFilter);
$editItem = $editId > 0 ? getAdminGiftItemById($editId) : null;

renderView('admin/gift_items', compact('pageTitle', 'search', 'roleFilter', 'metrics', 'items', 'editItem'));
