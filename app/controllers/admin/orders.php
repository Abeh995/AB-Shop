<?php
$pageTitle = 'سفارش‌ها';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    verifyCsrf();
    requireSuperAdmin();
    $id = (int) ($_POST['id'] ?? 0);
    db()->prepare("DELETE FROM orders WHERE id = ?")->execute([$id]);
    setFlash('success', 'سفارش حذف شد.');
    redirect('orders.php');
}

$statusFilter = $_GET['status'] ?? '';
$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

$where = '1=1';
$params = [];
if ($statusFilter && isset($statusLabels[$statusFilter])) {
    $where = 'status = ?';
    $params[] = $statusFilter;
}

$stmt = db()->prepare("SELECT * FROM orders WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Load a compact "product (variant) x qty" summary per order in a single query,
// so the admin can see which variant was purchased directly from the list
// without opening every order individually.
$itemsByOrder = [];
if ($orders) {
    $orderIds = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $itemsStmt = db()->prepare("SELECT order_id, product_name, variant_label, quantity
                                 FROM order_items WHERE order_id IN ($placeholders)
                                 ORDER BY id ASC");
    $itemsStmt->execute($orderIds);
    foreach ($itemsStmt->fetchAll() as $row) {
        $itemsByOrder[$row['order_id']][] = $row;
    }
}

renderView('admin/orders', compact('pageTitle', 'statusFilter', 'statusLabels', 'orders', 'itemsByOrder'));
