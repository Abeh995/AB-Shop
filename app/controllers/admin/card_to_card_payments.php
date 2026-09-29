<?php
/**
 * Admin Card-to-Card Payment Reconciliation Controller.
 * Thin controller (< 80 lines): parses inputs, delegates POST actions to OrderService, and renders view.
 */

$pageTitle = 'بررسی پرداخت‌های کارت‌به‌کارت';
$tab = in_array($_GET['tab'] ?? '', ['pending', 'paid', 'failed', 'no_receipt', 'all'], true) ? $_GET['tab'] : 'pending';
$search = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

// Handle State-Changing POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = trim((string) ($_POST['action'] ?? ''));
    $redirectUrl = 'card_to_card_payments.php?' . http_build_query(array_filter([
        'tab' => $tab !== 'pending' ? $tab : null, 'page' => $page > 1 ? $page : null, 'search' => $search ?: null,
    ]));

    if ($action === 'verify_c2c') {
        $res = OrderService::verifyCardToCardReceipt((int) ($_POST['order_id'] ?? 0), true);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'فیش واریز با موفقیت تأیید شد.' : $res['error']);
    } elseif ($action === 'reject_c2c') {
        $res = OrderService::rejectCardToCardReceiptWithReason((int) ($_POST['order_id'] ?? 0), trim((string) ($_POST['reason'] ?? '')));
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'فیش واریز رد شد و پیامک برای مشتری ارسال گردید.' : $res['error']);
    } elseif ($action === 'batch_verify') {
        $res = OrderService::batchVerifyCardToCardReceipts((array) ($_POST['selected_orders'] ?? []));
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? "تعداد {$res['count']} فیش واریز به‌صورت گروهی تأیید شدند." : $res['error']);
    } elseif ($action === 'upload_receipt') {
        $file = $_FILES['receipt_file'] ?? null;
        $res = $file ? OrderService::attachAdminUploadedReceipt((int) ($_POST['order_id'] ?? 0), $file) : ['ok' => false, 'error' => 'فایلی انتخاب نشده است.'];
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'فیش واریز با موفقیت به سفارش پیوست شد.' : $res['error']);
    }
    redirect($redirectUrl);
}

// Fetch Paginated Orders & Global Stats
$c2cData = OrderService::getCardToCardOrders(['tab' => $tab, 'search' => $search], $page, 25);
$stats = OrderService::getCardToCardStats();
$storeCard = [
    'number' => (string) getSetting('card_to_card_number', ''),
    'holder' => (string) getSetting('card_to_card_holder', ''),
    'bank'   => (string) getSetting('card_to_card_bank', ''),
    'note'   => (string) getSetting('card_to_card_note', ''),
];
$paymentLabels = ['unpaid' => 'در انتظار بررسی', 'paid' => 'تأیید شده', 'failed' => 'رد شده'];
$paymentClasses = ['unpaid' => 'status-pending', 'paid' => 'status-delivered', 'failed' => 'status-cancelled'];
$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

renderView('admin/card_to_card_payments', compact(
    'pageTitle', 'tab', 'search', 'page', 'c2cData', 'stats', 'storeCard',
    'paymentLabels', 'paymentClasses', 'statusLabels'
));
