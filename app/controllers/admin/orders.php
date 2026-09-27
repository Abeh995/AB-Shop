<?php
/**
 * Modern Admin Orders Tab Controller
 * Handles filtering, pagination, live actions, and hands data to views/admin/orders.php.
 */
$pageTitle = 'مدیریت سفارش‌ها';

$statusLabels = [
    'pending'    => 'در انتظار بررسی',
    'confirmed'  => 'تأیید شده',
    'processing' => 'در حال بسته‌بندی',
    'shipped'    => 'ارسال شده با پست',
    'delivered'  => 'تحویل داده شده',
    'cancelled'  => 'لغو شده',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $res = OrderService::deleteOrder($id);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'سفارش با موفقیت حذف شد.' : $res['error']);
        redirect('orders.php');
    }

    if ($action === 'update_status') {
        $id = (int) ($_POST['id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');
        $trackingCode = isset($_POST['tracking_code']) ? trim($_POST['tracking_code']) : null;
        $res = OrderService::updateOrderStatus($id, $newStatus, $trackingCode);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'وضعیت سفارش به‌روزرسانی شد.' : $res['error']);
        redirect('orders.php');
    }

    if ($action === 'update_tracking') {
        $id = (int) ($_POST['id'] ?? 0);
        $code = trim($_POST['tracking_code'] ?? '');
        $res = OrderService::updateTrackingCode($id, $code);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'کد رهگیری پستی ذخیره شد.' : $res['error']);
        redirect('orders.php');
    }

    if ($action === 'verify_c2c') {
        $id = (int) ($_POST['id'] ?? 0);
        $decision = ($_POST['decision'] ?? '') === 'approved';
        $res = OrderService::verifyCardToCardReceipt($id, $decision);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? ($decision ? 'فیش کارت‌به‌کارت تأیید و وضعیت به در حال بسته‌بندی تغییر یافت.' : 'فیش کارت‌به‌کارت رد شد.') : $res['error']);
        redirect('orders.php');
    }

    if ($action === 'bulk_status') {
        $orderIds = explode(',', (string)($_POST['order_ids'] ?? ''));
        $newStatus = trim($_POST['bulk_new_status'] ?? '');
        $res = OrderService::bulkUpdateStatus($orderIds, $newStatus);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? "وضعیت {$res['count']} سفارش تغییر یافت." : $res['error']);
        redirect('orders.php');
    }
}

// Read Filter and Pagination Parameters
$filters = [
    'status'         => trim($_GET['status'] ?? ''),
    'search'         => trim($_GET['search'] ?? ''),
    'payment_method' => trim($_GET['payment_method'] ?? ''),
    'payment_status' => trim($_GET['payment_status'] ?? ''),
    'date_range'     => trim($_GET['date_range'] ?? ''),
];
$page = (int) ($_GET['page'] ?? 1);
$perPage = 15;

$ordersData   = OrderService::getAdminOrders($filters, $page, $perPage);
$orderStats   = OrderService::getAdminOrderStats();
$statusCounts = OrderService::getAdminStatusCounts();

renderView('admin/orders', compact('pageTitle', 'statusLabels', 'filters', 'ordersData', 'orderStats', 'statusCounts'));
