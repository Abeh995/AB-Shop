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
$categories = db()->query("SELECT id, name FROM categories ORDER BY sort_order ASC")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (int) preg_replace('/\D/', '', $_POST['price'] ?? '0');
    $discountPrice = trim($_POST['discount_price'] ?? '');
    $discountPrice = $discountPrice === '' ? null : (int) preg_replace('/\D/', '', $discountPrice);
    $stock = (int) ($_POST['stock'] ?? 0);
    $sku = trim($_POST['sku'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    if ($name === '') $errors[] = 'نام محصول الزامی است.';
    if ($categoryId < 1) $errors[] = 'دسته‌بندی را انتخاب کنید.';
    if ($price < 1) $errors[] = 'قیمت معتبر وارد کنید.';
    if ($discountPrice !== null && $discountPrice >= $price) $errors[] = 'قیمت با تخفیف باید کمتر از قیمت اصلی باشد.';

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
            $stmt->execute([$categoryId, $name, $slug, $description, $price, $discountPrice, $sku ?: null, $stock, $newImageName, $isActive, $isFeatured, $id]);
            $productId = $id;
            setFlash('success', 'محصول به‌روزرسانی شد.');
        } else {
            $stmt = db()->prepare("INSERT INTO products (category_id, name, slug, description, price, discount_price, sku, stock, image, is_active, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$categoryId, $name, $slug, $description, $price, $discountPrice, $sku ?: null, $stock, $newImageName, $isActive, $isFeatured]);
            $productId = db()->lastInsertId();
            setFlash('success', 'محصول با موفقیت اضافه شد.');
        }

        db()->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$productId]);
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

        redirect('products.php');
    }
}


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

renderView('admin/product_edit', compact('pageTitle', 'product', 'categories', 'variants', 'errors'));
