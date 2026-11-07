<?php
/**
 * Product Edit & Create Controller
 * Thin controller managing the product workstation form submission.
 */

$id = (int) ($_GET['id'] ?? 0);
$editData = $id > 0 ? getProductForEdit($id) : null;

if ($id > 0 && !$editData) {
    setFlash('error', 'محصول مورد نظر یافت نشد.');
    redirect('products.php');
}

$product = $editData['product'] ?? null;
$variants = $editData['variants'] ?? [];
$galleryImages = $editData['gallery_images'] ?? [];
$productTagIds = $editData['product_tag_ids'] ?? [];
$priceHistory = $editData['price_history'] ?? [];
$hasVariantsInitial = count($variants) > 0;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $adminId = (int) ($_SESSION['admin_id'] ?? 0);
    $payload = $_POST;
    $payload['id'] = $id;

    $saveResult = saveProduct($payload, $_FILES, $adminId);

    if ($saveResult['ok']) {
        setFlash('success', $id > 0 ? 'محصول با موفقیت به‌روزرسانی شد.' : 'محصول جدید با موفقیت اضافه شد.');
        redirect('products.php');
    } else {
        $errors = $saveResult['errors'] ?? ['خطا در ذخیره‌سازی محصول.'];
        // Preserve user input on validation error
        $product = array_merge($product ?? [], $_POST, [
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'stock' => (int) ($_POST['stock'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        ]);
        $hasVariantsInitial = isset($_POST['has_variants']);
        $productTagIds = array_map('intval', $_POST['tag_ids'] ?? []);
    }
}

$pageTitle = $product && !empty($product['id']) ? 'ویرایش محصول: ' . $product['name'] : 'محصول جدید';
$categories = getCategoriesForDropdown();
$allTags = getAllTags();
$specPresets = getProductSpecPresets();

renderView('admin/product_edit', compact(
    'pageTitle',
    'id',
    'product',
    'variants',
    'galleryImages',
    'categories',
    'allTags',
    'productTagIds',
    'priceHistory',
    'hasVariantsInitial',
    'specPresets',
    'errors'
));
