<?php
/**
 * Products Catalog Controller
 * Thin controller managing catalog listing, quick toggles, filters, and deletions.
 */

$pageTitle = 'محصولات';

// Handle POST actions (delete, toggle, quick_stock, bulk mutations)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_POST['ajax']) && $_POST['ajax'] === '1');

    $result = match ($action) {
        'delete' => $id > 0 ? deleteProduct($id) : ['ok' => false, 'error' => 'شناسه نامعتبر است.'],
        'toggle' => $id > 0 ? quickToggleProductField($id, $_POST['field'] ?? '') : ['ok' => false, 'error' => 'شناسه نامعتبر است.'],
        'quick_stock' => $id > 0 ? quickUpdateProductStock($id, (array)($_POST['variant_stocks'] ?? []), isset($_POST['stock']) ? (int)$_POST['stock'] : null) : ['ok' => false, 'error' => 'شناسه نامعتبر است.'],
        'bulk_status' => bulkUpdateProductsStatus((array)($_POST['ids'] ?? []), $_POST['field'] ?? 'is_active', (int)($_POST['value'] ?? 1)),
        'bulk_category' => bulkUpdateProductsCategory((array)($_POST['ids'] ?? []), (int)($_POST['category_id'] ?? 0)),
        'bulk_delete' => bulkDeleteProducts((array)($_POST['ids'] ?? [])),
        default => ['ok' => false, 'error' => 'عملیات نامعتبر است.'],
    };

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit;
    }

    if ($result['ok']) {
        setFlash('success', 'عملیات با موفقیت انجام شد.');
    } else {
        setFlash('error', $result['error'] ?? 'خطا در انجام عملیات.');
    }
    redirect($_POST['return_url'] ?? 'products.php');
}

// Map query parameters and filters
$search = trim($_GET['q'] ?? '');
$categoryId = (int) ($_GET['category_id'] ?? 0);
$status = trim($_GET['status'] ?? '');
if ($status === '' && isset($_GET['featured'])) {
    $status = 'featured';
}
$status = $status === '' ? 'all' : $status;
$sort = trim($_GET['sort'] ?? 'newest');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = (int) ($_GET['per_page'] ?? 20);
if (!in_array($perPage, [20, 50, 100], true)) {
    $perPage = 20;
}

$filters = [
    'q' => $search,
    'category_id' => $categoryId,
    'status' => $status,
    'sort' => $sort,
];

// Delegate to service
$catalog = getProductsCatalog($filters, $page, $perPage);
$stats = getProductCatalogStats();
$categories = getCategoriesForDropdown();

renderView('admin/products', compact(
    'pageTitle',
    'search',
    'categoryId',
    'status',
    'sort',
    'catalog',
    'stats',
    'categories',
    'perPage'
));
