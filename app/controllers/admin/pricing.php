<?php
/**
 * Bulk Price Operations Controller
 * Manages desktop bulk pricing workstation, instant previews, and safe executions.
 */

$pageTitle = 'تغییر قیمت گروهی';
$adminId = (int) ($_SESSION['admin_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $productIds = array_values(array_unique(array_map('intval', $_POST['product_ids'] ?? [])));
    $field = ($_POST['field'] ?? '') === 'cost_price' ? 'cost_price' : 'sale_price';
    $method = in_array($_POST['method'] ?? '', ['fixed_amount', 'percentage', 'direct_value'], true)
        ? $_POST['method'] : 'fixed_amount';
    $value = (float) str_replace(',', '', $_POST['value'] ?? '0');
    $roundingStep = max(0, (int) str_replace(',', '', $_POST['rounding_step'] ?? '0'));
    $applyToVariants = !empty($_POST['apply_to_variants']);
    $allowNegativeMargin = !empty($_POST['allow_negative_margin']);
    $reason = trim($_POST['reason'] ?? '') ?: null;

    if ($action === 'apply' && !empty($productIds)) {
        $result = applyBulkPriceChange(
            $productIds,
            $field,
            $method,
            $value,
            $adminId,
            $reason,
            $roundingStep,
            $applyToVariants,
            $allowNegativeMargin
        );

        if (!$result['ok']) {
            setFlash('error', $result['error'] ?? 'خطا در ثبت تغییرات قیمت.');
        } else {
            $msg = toPersianDigits((string) $result['succeeded']) . ' محصول با موفقیت به‌روزرسانی شد.';
            if (!empty($result['skipped'])) {
                $msg .= ' ' . toPersianDigits((string) count($result['skipped'])) . ' محصول رد شد.';
            }
            setFlash('success', $msg);
        }
    }
    redirect('pricing.php');
}

$filters = [
    'search' => trim($_GET['q'] ?? ''),
    'category_id' => !empty($_GET['category_id']) ? (int) $_GET['category_id'] : null,
    'cost_status' => trim($_GET['cost_status'] ?? ''),
];

$products = getBulkPricingCandidates($filters);
$categories = getAllCategoriesWithHierarchy();
$metrics = getCatalogPricingMetrics();
$recentOps = getRecentBulkPriceOperations(10);

renderView('admin/pricing', compact(
    'pageTitle',
    'filters',
    'products',
    'categories',
    'metrics',
    'recentOps'
));

