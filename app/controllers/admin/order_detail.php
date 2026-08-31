<?php
$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) redirect('orders.php');

$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    if (($_POST['action'] ?? '') === 'delete') {
        requireSuperAdmin(); // Server-side protection, even if the button is hidden in the UI
        db()->prepare("DELETE FROM orders WHERE id = ?")->execute([$id]); // order_items are removed via CASCADE
        setFlash('success', 'سفارش ' . $order['order_code'] . ' حذف شد.');
        redirect('orders.php');
    }

    $newStatus = $_POST['status'] ?? '';
    if (isset($statusLabels[$newStatus]) && $newStatus !== $order['status']) {
        db()->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $id]);

        // Notify the customer by SMS about the status change (if the SMS service is enabled, otherwise it's just logged)
        SmsService::notifyOrderStatusChanged($order['phone'], $order['order_code'], $statusLabels[$newStatus]);

        setFlash('success', 'وضعیت سفارش به‌روزرسانی شد.');
        redirect('order_detail.php?id=' . $id);
    }
}

$itemsStmt = db()->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

$pageTitle = 'سفارش ' . $order['order_code'];
renderView('admin/order_detail', compact('pageTitle', 'order', 'items', 'statusLabels'));
