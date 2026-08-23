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
    $newStatus = $_POST['status'] ?? '';
    if (isset($statusLabels[$newStatus]) && $newStatus !== $order['status']) {
        db()->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $id]);

        // اطلاع‌رسانی پیامکی تغییر وضعیت به مشتری (اگر سرویس پیامک فعال باشد، وگرنه فقط لاگ می‌شود)
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
