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

renderView('admin/orders', compact('pageTitle', 'statusFilter', 'statusLabels', 'orders'));
