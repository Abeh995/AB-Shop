<?php
$id = (int) ($_GET['id'] ?? 0);
$order = OrderService::getOrder($id);
if (!$order) redirect('orders.php');

$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $res = OrderService::deleteOrder($id);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'سفارش ' . $order['order_code'] . ' حذف شد.' : $res['error']);
        redirect('orders.php');
    }

    if ($action === 'assign_gift') {
        $giftItemId = (int) ($_POST['gift_item_id'] ?? 0);
        $qty = (int) ($_POST['quantity'] ?? 1);
        $note = trim($_POST['note'] ?? '') ?: null;
        $adminId = (int) ($_SESSION['admin_id'] ?? 0);

        $result = assignGiftToOrder($id, $giftItemId, $qty, $adminId, $note);
        setFlash($result['ok'] ? 'success' : 'error', $result['ok'] ? 'هدیه به سفارش اضافه شد.' : $result['error']);
        redirect('order_detail.php?id=' . $id);
    }

    if ($action === 'payment_status') {
        $newPaymentStatus = trim($_POST['payment_status'] ?? '');
        $res = OrderService::updatePaymentStatus($id, $newPaymentStatus);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'وضعیت پرداخت به‌روزرسانی شد.' : $res['error']);
        redirect('order_detail.php?id=' . $id);
    }

    if ($action === 'update_tracking') {
        $code = trim($_POST['tracking_code'] ?? '');
        $res = OrderService::updateTrackingCode($id, $code);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'کد رهگیری پستی ذخیره شد.' : $res['error']);
        redirect('order_detail.php?id=' . $id);
    }

    if ($action === 'verify_c2c') {
        $decision = ($_POST['decision'] ?? '') === 'approved';
        $res = OrderService::verifyCardToCardReceipt($id, $decision);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? ($decision ? 'فیش کارت‌به‌کارت تأیید شد.' : 'فیش کارت‌به‌کارت رد شد.') : $res['error']);
        redirect('order_detail.php?id=' . $id);
    }

    $newStatus = trim($_POST['status'] ?? '');
    if (isset($statusLabels[$newStatus]) && $newStatus !== $order['status']) {
        $tracking = isset($_POST['tracking_code']) ? trim($_POST['tracking_code']) : null;
        $res = OrderService::updateOrderStatus($id, $newStatus, $tracking);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'وضعیت سفارش به‌روزرسانی شد.' : $res['error']);
        redirect('order_detail.php?id=' . $id);
    }
}

$items = OrderService::getOrderItemsWithGallery($id);
$orderGiftItems = getOrderGiftItems($id);
$giftableItems = getGiftableItems();

$pageTitle = 'سفارش ' . $order['order_code'];
$profitability = getOrderProfitability($id);
$invoiceFooterNote = getSetting('invoice_footer_note', 'از خرید و اعتماد شما به جوراب AB سپاسگزاریم.');

renderView('admin/order_detail', compact('pageTitle', 'order', 'items', 'statusLabels', 'orderGiftItems', 'giftableItems', 'profitability', 'invoiceFooterNote'));

