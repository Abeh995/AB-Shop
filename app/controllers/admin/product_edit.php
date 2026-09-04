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
$galleryImages = $id ? db()->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC") : null;
if ($galleryImages) { $galleryImages->execute([$id]); $galleryImages = $galleryImages->fetchAll(); } else { $galleryImages = []; }
$errors = [];
// Current "has variants" state, used to pre-check the checkbox on first render (based on existing variant rows)
$hasVariantsInitial = count($variants) > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (int) preg_replace('/\D/', '', $_POST['price'] ?? '0');
    $discountPrice = trim($_POST['discount_price'] ?? '');
    $discountPrice = $discountPrice === '' ? null : (int) preg_replace('/\D/', '', $discountPrice);
    $costPriceRaw = trim($_POST['cost_price'] ?? '');
    $costPrice = $costPriceRaw === '' ? null : (int) preg_replace('/\D/', '', $costPriceRaw);
    $sku = trim($_POST['sku'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $hasVariants = isset($_POST['has_variants']);
    $adminId = (int) ($_SESSION['admin_id'] ?? 0);

    // When a product has variants, overall stock is meaningless (each variant
    // has its own stock), so 0 is stored to avoid misleading reports.
    $stock = $hasVariants ? 0 : (int) ($_POST['stock'] ?? 0);

    if ($name === '') $errors[] = 'نام محصول الزامی است.';
    if ($categoryId < 1) $errors[] = 'دسته‌بندی را انتخاب کنید.';
    if ($price < 1) $errors[] = 'قیمت معتبر وارد کنید.';
    if ($discountPrice !== null && $discountPrice >= $price) $errors[] = 'قیمت با تخفیف باید کمتر از قیمت اصلی باشد.';

    // ---------- SKU: auto-generated if left empty; otherwise its uniqueness is checked ----------
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
            // price and cost_price are deliberately NOT written here — they go
            // exclusively through recordPriceChange() below, so every change to
            // them lands in price_history. See app/services/PricingService.php.
            $stmt = db()->prepare("UPDATE products SET category_id=?, name=?, slug=?, description=?, discount_price=?, sku=?, stock=?, image=?, is_active=?, is_featured=? WHERE id=?");
            $stmt->execute([$categoryId, $name, $slug, $description, $discountPrice, $sku, $stock, $newImageName, $isActive, $isFeatured, $id]);
            $productId = $id;

            $oldPrice = (int) $product['price'];
            if ($price !== $oldPrice) {
                recordPriceChange($productId, null, 'sale_price', 'direct_value', (float) $price, $adminId);
            }
            $oldCostPrice = $product['cost_price'] !== null ? (int) $product['cost_price'] : null;
            if ($costPrice !== $oldCostPrice) {
                if ($costPrice === null) {
                    db()->prepare("UPDATE products SET cost_price = NULL WHERE id = ?")->execute([$productId]);
                } else {
                    recordPriceChange($productId, null, 'cost_price', 'direct_value', (float) $costPrice, $adminId);
                }
            }

            setFlash('success', 'محصول به‌روزرسانی شد.');
        } else {
            $stmt = db()->prepare("INSERT INTO products (category_id, name, slug, description, price, discount_price, cost_price, sku, stock, image, is_active, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$categoryId, $name, $slug, $description, $price, $discountPrice, $costPrice, $sku, $stock, $newImageName, $isActive, $isFeatured]);
            $productId = db()->lastInsertId();
            setFlash('success', 'محصول با موفقیت اضافه شد.');
        }

        // ---------- Variants: upsert in place so an existing row's id (and
        // anything referencing it — price_history, order_items) stays stable
        // across edits, instead of being deleted and recreated every save. ----------
        $oldVariantsById = [];
        foreach ($variants as $v) {
            $oldVariantsById[(int) $v['id']] = $v;
        }

        $submittedVariantIds = [];
        if ($hasVariants) {
            $variantIdsIn = $_POST['variant_id'] ?? [];
            $sizes = $_POST['variant_size'] ?? [];
            $colors = $_POST['variant_color'] ?? [];
            $vstocks = $_POST['variant_stock'] ?? [];
            $vcostPrices = $_POST['variant_cost_price'] ?? [];

            for ($i = 0; $i < count($sizes); $i++) {
                $sz = trim($sizes[$i]);
                $cl = trim($colors[$i] ?? '');
                $st = (int) ($vstocks[$i] ?? 0);
                $vcpRaw = trim($vcostPrices[$i] ?? '');
                $vcp = $vcpRaw === '' ? null : (int) preg_replace('/\D/', '', $vcpRaw);
                $existingId = (int) ($variantIdsIn[$i] ?? 0);

                if ($sz === '' && $cl === '') continue;

                if ($existingId && isset($oldVariantsById[$existingId])) {
                    db()->prepare("UPDATE product_variants SET size=?, color=?, stock=? WHERE id=? AND product_id=?")
                        ->execute([$sz ?: null, $cl ?: null, $st, $existingId, $productId]);

                    $oldVariant = $oldVariantsById[$existingId];
                    $oldVcp = $oldVariant['cost_price'] !== null ? (int) $oldVariant['cost_price'] : null;
                    if ($vcp !== $oldVcp) {
                        if ($vcp === null) {
                            db()->prepare("UPDATE product_variants SET cost_price = NULL WHERE id = ?")->execute([$existingId]);
                        } else {
                            recordPriceChange($productId, $existingId, 'cost_price', 'direct_value', (float) $vcp, $adminId);
                        }
                    }
                    $submittedVariantIds[] = $existingId;
                } else {
                    $vstmt = db()->prepare("INSERT INTO product_variants (product_id, size, color, stock, cost_price) VALUES (?,?,?,?,?)");
                    $vstmt->execute([$productId, $sz ?: null, $cl ?: null, $st, $vcp]);
                    $submittedVariantIds[] = (int) db()->lastInsertId();
                }
            }
        }

        // Any previously-existing variant not present in this submission was
        // removed by the admin (or "has variants" was turned off entirely).
        $idsToDelete = array_diff(array_keys($oldVariantsById), $submittedVariantIds);
        if ($idsToDelete) {
            $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
            db()->prepare("DELETE FROM product_variants WHERE id IN ($placeholders) AND product_id = ?")
                ->execute([...$idsToDelete, $productId]);
        }

        // ---------- Tags: editable by any admin, at any time ----------
        $selectedTagIds = array_map('intval', $_POST['tag_ids'] ?? []);
        $newTagsRaw = trim($_POST['new_tags'] ?? '');
        syncProductTags($productId, $selectedTagIds, $newTagsRaw);

        // ---------- Gallery: delete the selected images ----------
        $deleteImageIds = array_map('intval', $_POST['delete_image_ids'] ?? []);
        if ($deleteImageIds) {
            $placeholders = implode(',', array_fill(0, count($deleteImageIds), '?'));
            $imgStmt = db()->prepare("SELECT id, image_path FROM product_images WHERE id IN ($placeholders) AND product_id = ?");
            $imgStmt->execute([...$deleteImageIds, $productId]);
            foreach ($imgStmt->fetchAll() as $img) {
                if (file_exists(UPLOAD_DIR . $img['image_path'])) {
                    @unlink(UPLOAD_DIR . $img['image_path']);
                }
            }
            db()->prepare("DELETE FROM product_images WHERE id IN ($placeholders) AND product_id = ?")
                ->execute([...$deleteImageIds, $productId]);
        }

        // ---------- Gallery: add however many new images the admin selected ----------
        if (!empty($_FILES['gallery_images']['name'][0])) {
            $maxSortStmt = db()->prepare("SELECT COALESCE(MAX(sort_order), -1) FROM product_images WHERE product_id = ?");
            $maxSortStmt->execute([$productId]);
            $maxSort = (int) $maxSortStmt->fetchColumn();
            $galleryFiles = $_FILES['gallery_images'];
            $fileCount = count($galleryFiles['name']);

            for ($i = 0; $i < $fileCount; $i++) {
                if ($galleryFiles['error'][$i] !== UPLOAD_ERR_OK) continue;

                $singleFile = [
                    'name' => $galleryFiles['name'][$i],
                    'type' => $galleryFiles['type'][$i],
                    'tmp_name' => $galleryFiles['tmp_name'][$i],
                    'error' => $galleryFiles['error'][$i],
                    'size' => $galleryFiles['size'][$i],
                ];

                $uploadResult = handleProductImageUpload($singleFile);
                if ($uploadResult['ok']) {
                    $maxSort++;
                    db()->prepare("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?, ?, ?)")
                        ->execute([$productId, $uploadResult['filename'], $maxSort]);
                }
                // An error on one gallery image doesn't stop the rest of the save (only that image is skipped)
            }
        }

        redirect('products.php');
    }

    // If there were errors, keep the current tag selection so the form re-renders it
    $productTagIds = array_map('intval', $_POST['tag_ids'] ?? []);

    // If there were errors, keep the generated/entered SKU so the form re-renders it
    if ($product) { $product['sku'] = $sku; } else { $product = ['sku' => $sku]; }
    $hasVariantsInitial = $hasVariants;
}

/**
 * Validate and safely store the uploaded file
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

$priceHistory = $id ? getProductPriceHistory($id) : [];

renderView('admin/product_edit', compact('pageTitle', 'product', 'categories', 'variants', 'errors', 'hasVariantsInitial', 'allTags', 'productTagIds', 'galleryImages', 'priceHistory'));
