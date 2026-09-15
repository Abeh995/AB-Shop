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

    if (($_POST['action'] ?? '') === 'assign_gift') {
        $giftItemId = (int) ($_POST['gift_item_id'] ?? 0);
        $qty = (int) ($_POST['quantity'] ?? 1);
        $note = trim($_POST['note'] ?? '') ?: null;
        $adminId = (int) ($_SESSION['admin_id'] ?? 0);

        $result = assignGiftToOrder($id, $giftItemId, $qty, $adminId, $note);
        setFlash($result['ok'] ? 'success' : 'error', $result['ok'] ? 'هدیه به سفارش اضافه شد.' : $result['error']);
        redirect('order_detail.php?id=' . $id);
    }

    if (($_POST['action'] ?? '') === 'payment_status') {
        $newPaymentStatus = $_POST['payment_status'] ?? '';
        $allowedPaymentStatuses = ['unpaid', 'paid', 'failed'];

        if (!in_array($newPaymentStatus, $allowedPaymentStatuses, true)) {
            setFlash('error', 'وضعیت پرداخت نامعتبر است.');
        } elseif ($newPaymentStatus === 'paid' && $order['payment_method'] === 'card_to_card' && empty($order['card_to_card_receipt'])) {
            setFlash('error', 'برای تایید پرداخت کارت‌به‌کارت ابتدا باید رسید موجود باشد.');
        } else {
            db()->prepare("UPDATE orders SET payment_status = ? WHERE id = ?")->execute([$newPaymentStatus, $id]);
            if ($newPaymentStatus !== $order['payment_status'] && in_array($newPaymentStatus, ['paid', 'failed'], true)) {
                SmsService::notifyPaymentStatusChanged($order['phone'], $order['order_code'], $newPaymentStatus);
            }
            setFlash('success', 'وضعیت پرداخت به‌روزرسانی شد.');
        }
        redirect('order_detail.php?id=' . $id);
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

$orderGiftItems = getOrderGiftItems($id);
$giftableItems = getGiftableItems();

$pageTitle = 'سفارش ' . $order['order_code'];
$profitability = getOrderProfitability($id);

renderView('admin/order_detail', compact('pageTitle', 'order', 'items', 'statusLabels', 'orderGiftItems', 'giftableItems', 'profitability'));
