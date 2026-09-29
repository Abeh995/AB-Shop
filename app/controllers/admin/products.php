<?php
/**
 * Products Catalog Controller
 * Thin controller managing catalog listing, quick toggles, filters, and deletions.
 */

$pageTitle = 'محصولات';

// Handle POST actions (delete, toggle)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $result = deleteProduct($id);
        if ($result['ok']) {
            setFlash('success', 'محصول با موفقیت حذف شد.');
        } else {
            setFlash('error', $result['error'] ?? 'خطا در حذف محصول.');
        }
    } elseif ($action === 'toggle' && $id > 0) {
        $field = $_POST['field'] ?? '';
        $toggleRes = quickToggleProductField($id, $field);
        if ($toggleRes['ok']) {
            setFlash('success', 'وضعیت محصول به‌روزرسانی شد.');
        } else {
            setFlash('error', $toggleRes['error'] ?? 'خطا در تغییر وضعیت.');
        }
    }

    $redirectUrl = $_POST['return_url'] ?? 'products.php';
    redirect($redirectUrl);
}

// Map query parameters and legacy filters
$search = trim($_GET['q'] ?? '');
$categoryId = (int) ($_GET['category_id'] ?? 0);
$status = trim($_GET['status'] ?? '');
if ($status === '' && isset($_GET['featured'])) {
    $status = 'featured';
}
if ($status === '') {
    $status = 'all';
}
$sort = trim($_GET['sort'] ?? 'newest');
$page = (int) ($_GET['page'] ?? 1);

$filters = [
    'q' => $search,
    'category_id' => $categoryId,
    'status' => $status,
    'sort' => $sort,
];

// Delegate to service
$catalog = getProductsCatalog($filters, $page, 20);
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
    'categories'
));
