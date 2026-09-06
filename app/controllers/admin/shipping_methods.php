<?php
/**
 * Shipping method rules — list, delete, reorder.
 */

$pageTitle = 'روش‌های ارسال';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete') {
        db()->prepare("DELETE FROM shipping_methods WHERE id = ?")->execute([$id]);
        setFlash('success', 'روش ارسال حذف شد.');
    } elseif ($action === 'move') {
        // Swap sort_order with the adjacent row in the requested direction,
        // so relative order changes without needing to renumber every row.
        $direction = $_POST['direction'] ?? '';
        $current = db()->prepare("SELECT id, sort_order FROM shipping_methods WHERE id = ?");
        $current->execute([$id]);
        $currentRow = $current->fetch();

        if ($currentRow) {
            $cmp = $direction === 'up' ? '<' : '>';
            $order = $direction === 'up' ? 'DESC' : 'ASC';
            $neighborStmt = db()->prepare("SELECT id, sort_order FROM shipping_methods WHERE sort_order $cmp ? ORDER BY sort_order $order LIMIT 1");
            $neighborStmt->execute([$currentRow['sort_order']]);
            $neighbor = $neighborStmt->fetch();

            if ($neighbor) {
                db()->prepare("UPDATE shipping_methods SET sort_order = ? WHERE id = ?")->execute([$neighbor['sort_order'], $currentRow['id']]);
                db()->prepare("UPDATE shipping_methods SET sort_order = ? WHERE id = ?")->execute([$currentRow['sort_order'], $neighbor['id']]);
            }
        }
    }
    redirect('shipping_methods.php');
}

$methods = db()->query("SELECT * FROM shipping_methods ORDER BY sort_order ASC")->fetchAll();

renderView('admin/shipping_methods', compact('pageTitle', 'methods'));
