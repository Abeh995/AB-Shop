<?php
/**
 * Shipping method rules — list, delete, reorder.
 * All mutations and queries are encapsulated in ShippingService (Rule 7).
 */

$pageTitle = 'روش‌های ارسال';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $res = deleteShippingMethodRecord($id);
        if ($res['ok']) {
            setFlash('success', 'روش ارسال حذف شد.');
        } else {
            setFlash('error', $res['error']);
        }
    } elseif ($action === 'move' && $id > 0) {
        $direction = $_POST['direction'] ?? '';
        $res = moveShippingMethodOrder($id, $direction);
        if (!$res['ok']) {
            setFlash('error', $res['error']);
        }
    }
    redirect('shipping_methods.php');
}

$methods = getAdminShippingMethods();

renderView('admin/shipping_methods', compact('pageTitle', 'methods'));
