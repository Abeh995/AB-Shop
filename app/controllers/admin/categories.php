<?php
/**
 * Categories Management Controller
 * Thin controller managing category tree, quick edits, toggles, and deletions.
 */

$pageTitle = 'دسته‌بندی‌ها';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_POST['ajax']) && $_POST['ajax'] === '1');

    $result = match ($action) {
        'create' => saveCategory($_POST, $_FILES['image'] ?? null),
        'update' => saveCategory($_POST, $_FILES['image'] ?? null),
        'toggle' => $id > 0 ? quickToggleCategoryActive($id) : ['ok' => false, 'error' => 'شناسه نامعتبر است.'],
        'delete' => $id > 0 ? deleteCategory($id) : ['ok' => false, 'error' => 'شناسه نامعتبر است.'],
        default => ['ok' => false, 'error' => 'عملیات نامعتبر است.'],
    };

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit;
    }

    if ($result['ok']) {
        setFlash('success', match ($action) {
            'create' => 'دسته‌بندی جدید با موفقیت اضافه شد.',
            'update' => 'اطلاعات دسته‌بندی با موفقیت به‌روزرسانی شد.',
            'toggle' => 'وضعیت دسته‌بندی به‌روز شد.',
            'delete' => 'دسته‌بندی با موفقیت حذف شد.',
            default => 'عملیات با موفقیت انجام شد.',
        });
    } else {
        setFlash('error', $result['error'] ?? 'خطا در انجام عملیات.');
    }

    redirect('categories.php');
}

$categories = getAllCategoriesWithHierarchy();
$topLevelCategories = array_values(array_filter($categories, fn($c) => $c['parent_id'] === null));
$stats = getCategoryCatalogStats();

renderView('admin/categories', compact(
    'pageTitle',
    'categories',
    'topLevelCategories',
    'stats'
));
