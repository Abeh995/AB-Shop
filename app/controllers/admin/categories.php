<?php
/**
 * Categories Management Controller
 * Thin controller managing category tree, quick edits, and deletions.
 */

$pageTitle = 'دسته‌بندی‌ها';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $result = saveCategory($_POST);
        if ($result['ok']) {
            setFlash('success', $action === 'create' ? 'دسته‌بندی با موفقیت اضافه شد.' : 'دسته‌بندی به‌روزرسانی شد.');
        } else {
            setFlash('error', $result['error'] ?? 'خطا در ثبت دسته‌بندی.');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $result = deleteCategory($id);
        if ($result['ok']) {
            setFlash('success', 'دسته‌بندی با موفقیت حذف شد.');
        } else {
            setFlash('error', $result['error'] ?? 'خطا در حذف دسته‌بندی.');
        }
    }

    redirect('categories.php');
}

$categories = getAllCategoriesWithHierarchy();
$topLevelCategories = array_values(array_filter($categories, fn($c) => $c['parent_id'] === null));

renderView('admin/categories', compact(
    'pageTitle',
    'categories',
    'topLevelCategories'
));
