<?php
/**
 * Shipping method rules — list, delete, reorder, toggle, and instant studio save.
 * All mutations and queries are encapsulated in ShippingService (Rule 7).
 */

$pageTitle = 'روش‌های ارسال';
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $res = deleteShippingMethodRecord($id);
        if ($res['ok']) {
            setFlash('success', 'روش ارسال با موفقیت حذف شد.');
        } else {
            setFlash('error', $res['error']);
        }
        redirect('shipping_methods.php');
    }

    if ($action === 'toggle_active' && $id > 0) {
        $res = toggleShippingMethodActive($id);
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit;
        }
        redirect('shipping_methods.php');
    }

    if ($action === 'move' && $id > 0) {
        $direction = $_POST['direction'] ?? '';
        $res = moveShippingMethodOrder($id, $direction);
        if (!$res['ok']) {
            setFlash('error', $res['error']);
        }
        redirect('shipping_methods.php');
    }

    if ($action === 'reorder') {
        $orderedIds = array_map('intval', (array) ($_POST['order'] ?? []));
        $res = reorderShippingMethods($orderedIds);
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit;
        }
        redirect('shipping_methods.php');
    }

    if ($action === 'save') {
        $res = saveShippingMethodRecord($id, $_POST);
        if ($res['ok']) {
            setFlash('success', $id > 0 ? 'روش ارسال به‌روزرسانی شد.' : 'روش ارسال جدید اضافه شد.');
        } else {
            setFlash('error', $res['error'] ?? 'خطا در ثبت روش ارسال.');
        }
        redirect('shipping_methods.php');
    }

    redirect('shipping_methods.php');
}

$editId = (int) ($_GET['edit'] ?? 0);
$editMethod = $editId > 0 ? getShippingMethodById($editId) : null;
$methods = getAdminShippingMethods();
$metrics = getShippingSummaryMetrics();

renderView('admin/shipping_methods', compact('pageTitle', 'methods', 'metrics', 'editMethod'));
