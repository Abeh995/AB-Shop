<?php
$id = (int) ($_GET['id'] ?? 0);
$product = null;
$variants = [];

if ($id) {
    $stmt = db()->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) redirect('products.php');

    $vstmt = db()->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
    $vstmt->execute([$id]);
    $variants = $vstmt->fetchAll();
}

$pageTitle = $product ? 'ویرایش محصول' : 'محصول جدید';
$categories = getCategoriesForDropdown();
$allTags = getAllTags();
$productTagIds = $id ? array_column(getProductTags($id), 'id') : [];
$errors = [];
// Determine the current variant-enabled state for the initial form checkbox.
$hasVariantsInitial = count($variants) > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (int) preg_replace('/\D/', '', $_POST['price'] ?? '0');
    $discountPrice = trim($_POST['discount_price'] ?? '');
    $discountPrice = $discountPrice === '' ? null : (int) preg_replace('/\D/', '', $discountPrice);
    $sku = trim($_POST['sku'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $hasVariants = isset($_POST['has_variants']);

    // Products with variants do not use a meaningful aggregate stock value; each variant tracks its own stock.
    // Store zero so aggregate stock values are not misleading in reports.
    $stock = $hasVariants ? 0 : (int) ($_POST['stock'] ?? 0);

    if ($name === '') $errors[] = 'نام محصول الزامی است.';
    if ($categoryId < 1) $errors[] = 'دسته‌بندی را انتخاب کنید.';
    if ($price < 1) $errors[] = 'قیمت معتبر وارد کنید.';
    if ($discountPrice !== null && $discountPrice >= $price) $errors[] = 'قیمت با تخفیف باید کمتر از قیمت اصلی باشد.';

    // ---------- SKU: generate one when empty; otherwise validate uniqueness ----------
    if ($sku === '') {
        $sku = generateUniqueSku();
    } else {
        $skuCheck = db()->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
        $skuCheck->execute([$sku, $id]);
        if ($skuCheck->fetch()) {
            $errors[] = 'این کد محصول (SKU) قبلاً برای محصول دیگری استفاده شده است.';
        }
    }

    $newImageName = $product['image'] ?? null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleProductImageUpload($_FILES['image']);
        if ($uploadResult['ok']) {
            if ($newImageName && file_exists(UPLOAD_DIR . $newImageName)) {
                @unlink(UPLOAD_DIR . $newImageName);
            }
            $newImageName = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }

    if (empty($errors)) {
        $slug = slugify($name);
        $check = db()->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
        $check->execute([$slug, $id]);
        if ($check->fetch()) {
            $slug .= '-' . substr(md5(uniqid('', true)), 0, 4);
        }

        if ($product) {
            $stmt = db()->prepare("UPDATE products SET category_id=?, name=?, slug=?, description=?, price=?, discount_price=?, sku=?, stock=?, image=?, is_active=?, is_featured=? WHERE id=?");
            $stmt->execute([$categoryId, $name, $slug, $description, $price, $discountPrice, $sku, $stock, $newImageName, $isActive, $isFeatured, $id]);
            $productId = $id;
            setFlash('success', 'محصول به‌روزرسانی شد.');
        } else {
            $stmt = db()->prepare("INSERT INTO products (category_id, name, slug, description, price, discount_price, sku, stock, image, is_active, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$categoryId, $name, $slug, $description, $price, $discountPrice, $sku, $stock, $newImageName, $isActive, $isFeatured]);
            $productId = db()->lastInsertId();
            setFlash('success', 'محصول با موفقیت اضافه شد.');
        }

        // ---------- Variants: process only when variant support is enabled ----------
        db()->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$productId]);
        if ($hasVariants) {
            $sizes = $_POST['variant_size'] ?? [];
            $colors = $_POST['variant_color'] ?? [];
            $vstocks = $_POST['variant_stock'] ?? [];
            for ($i = 0; $i < count($sizes); $i++) {
                $sz = trim($sizes[$i]);
                $cl = trim($colors[$i] ?? '');
                $st = (int) ($vstocks[$i] ?? 0);
                if ($sz === '' && $cl === '') continue;
                $vstmt = db()->prepare("INSERT INTO product_variants (product_id, size, color, stock) VALUES (?,?,?,?)");
                $vstmt->execute([$productId, $sz ?: null, $cl ?: null, $st]);
            }
        }

        // ---------- Tags: editable by any admin at any time ----------
        $selectedTagIds = array_map('intval', $_POST['tag_ids'] ?? []);
        $newTagsRaw = trim($_POST['new_tags'] ?? '');
        syncProductTags($productId, $selectedTagIds, $newTagsRaw);

        redirect('products.php');
    }

    // Preserve the current tag selection when re-rendering the form after validation errors.
    $productTagIds = array_map('intval', $_POST['tag_ids'] ?? []);

    // Preserve the generated or submitted SKU when re-rendering the form after validation errors.
    if ($product) { $product['sku'] = $sku; } else { $product = ['sku' => $sku]; }
    $hasVariantsInitial = $hasVariants;
}

/**
 * Validate and securely store an uploaded file.
 */
function handleProductImageUpload(array $file): array
{
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['ok' => false, 'error' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.'];
    }

    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowedMimes[$mime])) {
        return ['ok' => false, 'error' => 'فقط تصاویر JPG، PNG یا WEBP مجاز هستند.'];
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = bin2hex(random_bytes(12)) . '.' . $allowedMimes[$mime];
    $destination = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['ok' => false, 'error' => 'خطا در ذخیره فایل روی سرور.'];
    }

    return ['ok' => true, 'filename' => $filename];
}

renderView('admin/product_edit', compact('pageTitle', 'product', 'categories', 'variants', 'errors', 'hasVariantsInitial', 'allTags', 'productTagIds'));
