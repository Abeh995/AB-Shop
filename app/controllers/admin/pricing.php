<?php
/**
 * Bulk Price Operations Controller
 * Thin controller managing two-phase bulk price previews and executions.
 */

$pageTitle = 'تغییر قیمت گروهی';
$adminId = (int) ($_SESSION['admin_id'] ?? 0);

$preview = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $productIds = array_values(array_unique(array_map('intval', $_POST['product_ids'] ?? [])));
    $field = ($_POST['field'] ?? '') === 'cost_price' ? 'cost_price' : 'sale_price';
    $method = in_array($_POST['method'] ?? '', ['fixed_amount', 'percentage', 'direct_value'], true)
        ? $_POST['method'] : 'fixed_amount';
    $value = (float) str_replace(',', '', $_POST['value'] ?? '0');
    $reason = trim($_POST['reason'] ?? '') ?: null;

    if ($action === 'preview' && !empty($productIds)) {
        $previewRows = getPricingPreviewRows($productIds, $field, $method, $value);
        $preview = [
            'product_ids' => $productIds,
            'field' => $field,
            'method' => $method,
            'value' => $value,
            'reason' => $reason,
            'rows' => $previewRows,
        ];
    } elseif ($action === 'apply' && !empty($productIds)) {
        $result = applyBulkPriceChange($productIds, $field, $method, $value, $adminId, $reason);
        $msg = toPersianDigits((string) $result['succeeded']) . ' محصول با موفقیت به‌روزرسانی شد.';
        if (!empty($result['skipped'])) {
            $msg .= ' ' . toPersianDigits((string) count($result['skipped'])) . ' محصول رد شد.';
        }
        setFlash('success', $msg);
        redirect('pricing.php');
    }
}

$search = trim($_GET['q'] ?? '');
$products = getBulkPricingCandidates($search);
$recentOps = getRecentBulkPriceOperations(10);

renderView('admin/pricing', compact(
    'pageTitle',
    'search',
    'products',
    'preview',
    'recentOps'
));
