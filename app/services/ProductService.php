<?php
/**
 * Product & Category Service
 *
 * Core service layer handling the products catalog, CRUD operations,
 * inventory valuation, variant upserts, category hierarchy, and safe deletions.
 *
 * Architectural Invariants:
 * - Pricing & cost updates MUST route through PricingService (Rule 1).
 * - All SQL queries & mutations for products and categories are encapsulated here (Rule 7).
 * - Controllers only parse input, call these service methods, and pass data to views.
 */

// ==========================================
// 1. Catalog & Statistics
// ==========================================

/**
 * Fetch high-level Bento KPIs for the products catalog.
 * Returns total products, active products, low stock count, out-of-stock count, and total inventory value.
 */
function getProductCatalogStats(): array
{
    $pdo = db();

    $statsQuery = $pdo->query("
        SELECT 
            COUNT(*) AS total_products,
            COUNT(CASE WHEN p.is_active = 1 THEN 1 END) AS active_products,
            COUNT(CASE WHEN p.is_featured = 1 THEN 1 END) AS featured_products
        FROM products p
    ")->fetch();

    // Query low-stock and out-of-stock considering variant stocks or parent stock
    $stockBreakdown = $pdo->query("
        SELECT 
            p.id,
            COALESCE((SELECT SUM(v.stock) FROM product_variants v WHERE v.product_id = p.id), p.stock) AS effective_stock,
            COALESCE(p.cost_price, p.price) AS valuation_unit_price
        FROM products p
    ")->fetchAll();

    $lowStockCount = 0;
    $outOfStockCount = 0;
    $totalInventoryValue = 0;

    foreach ($stockBreakdown as $row) {
        $effStock = (int) $row['effective_stock'];
        $valPrice = (int) $row['valuation_unit_price'];

        if ($effStock === 0) {
            $outOfStockCount++;
        } elseif ($effStock <= 3) {
            $lowStockCount++;
        }

        if ($effStock > 0) {
            $totalInventoryValue += ($effStock * $valPrice);
        }
    }

    return [
        'total_products' => (int) ($statsQuery['total_products'] ?? 0),
        'active_products' => (int) ($statsQuery['active_products'] ?? 0),
        'featured_products' => (int) ($statsQuery['featured_products'] ?? 0),
        'low_stock_count' => $lowStockCount,
        'out_of_stock_count' => $outOfStockCount,
        'inventory_valuation' => $totalInventoryValue,
    ];
}

/**
 * Fetch paginated and filtered product catalog list for admin panel.
 *
 * @param array $filters ['q' => string, 'category_id' => int, 'status' => string, 'sort' => string]
 * @param int $page
 * @param int $perPage
 * @return array{items: array, total_count: int, total_pages: int, current_page: int, per_page: int}
 */
function getProductsCatalog(array $filters = [], int $page = 1, int $perPage = 20): array
{
    $pdo = db();
    $page = max(1, $page);
    $perPage = max(1, min(100, $perPage));
    $offset = ($page - 1) * $perPage;

    $where = ['1=1'];
    $params = [];

    // Search query: name, sku, or connected product tags
    $search = trim($filters['q'] ?? '');
    if ($search !== '') {
        $where[] = '(p.name LIKE ? OR p.sku LIKE ? OR EXISTS (
            SELECT 1 FROM product_tags pt_s 
            JOIN tags t_s ON t_s.id = pt_s.tag_id 
            WHERE pt_s.product_id = p.id AND (t_s.name LIKE ? OR t_s.slug LIKE ?)
        ))';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    // Tag filter
    $tagId = (int) ($filters['tag_id'] ?? 0);
    if ($tagId > 0) {
        $where[] = 'EXISTS (SELECT 1 FROM product_tags pt_f WHERE pt_f.product_id = p.id AND pt_f.tag_id = ?)';
        $params[] = $tagId;
    }

    // Category filter
    $categoryId = (int) ($filters['category_id'] ?? 0);
    if ($categoryId > 0) {
        $childIds = getCategoryAndChildIds($categoryId);
        $placeholders = implode(',', array_fill(0, count($childIds), '?'));
        $where[] = "p.category_id IN ($placeholders)";
        $params = array_merge($params, $childIds);
    }

    // Status filter
    $status = $filters['status'] ?? 'all';
    if ($status === 'active') {
        $where[] = 'p.is_active = 1';
    } elseif ($status === 'inactive') {
        $where[] = 'p.is_active = 0';
    } elseif ($status === 'featured') {
        $where[] = 'p.is_featured = 1';
    } elseif ($status === 'discounted') {
        $where[] = 'p.discount_price IS NOT NULL AND p.discount_price > 0 AND p.discount_price < p.price';
    } elseif ($status === 'out_of_stock') {
        $where[] = 'COALESCE((SELECT SUM(v.stock) FROM product_variants v WHERE v.product_id = p.id), p.stock) = 0';
    } elseif ($status === 'low_stock') {
        $where[] = 'COALESCE((SELECT SUM(v.stock) FROM product_variants v WHERE v.product_id = p.id), p.stock) BETWEEN 1 AND 3';
    }

    $whereSql = implode(' AND ', $where);

    // Sorting
    $sort = $filters['sort'] ?? 'newest';
    $orderSql = match ($sort) {
        'oldest' => 'p.id ASC',
        'price_asc' => 'effective_price ASC, p.id DESC',
        'price_desc' => 'effective_price DESC, p.id DESC',
        'stock_asc' => 'effective_stock ASC, p.id DESC',
        'stock_desc' => 'effective_stock DESC, p.id DESC',
        'name_asc' => 'p.name ASC',
        default => 'p.id DESC',
    };

    // Count query
    $countStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM products p
        WHERE $whereSql
    ");
    $countStmt->execute($params);
    $totalCount = (int) $countStmt->fetchColumn();
    $totalPages = (int) ceil($totalCount / $perPage);

    // Main listing query
    $listSql = "
        SELECT 
            p.*,
            c.name AS category_name,
            COALESCE(p.discount_price, p.price) AS effective_price,
            COALESCE((SELECT SUM(v.stock) FROM product_variants v WHERE v.product_id = p.id), p.stock) AS effective_stock,
            (SELECT COUNT(*) FROM product_variants v WHERE v.product_id = p.id) AS variant_count,
            (SELECT GROUP_CONCAT(
                CONCAT(TRIM(CONCAT(COALESCE(v.size,''), ' ', COALESCE(v.color,''))), ': ', v.stock)
                SEPARATOR ' | ')
             FROM product_variants v WHERE v.product_id = p.id) AS variant_stock_summary
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE $whereSql
        ORDER BY $orderSql
        LIMIT $perPage OFFSET $offset
    ";

    $stmt = $pdo->prepare($listSql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    if (!empty($items)) {
        $productIds = array_column($items, 'id');
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        // 1. Fetch structured variants for each item on page
        $vStmt = $pdo->prepare("
            SELECT id, product_id, size, color, stock, price_override, cost_price, is_default
            FROM product_variants
            WHERE product_id IN ($placeholders)
            ORDER BY id ASC
        ");
        $vStmt->execute($productIds);
        $variantsByProd = [];
        foreach ($vStmt->fetchAll() as $v) {
            $variantsByProd[(int)$v['product_id']][] = [
                'id' => (int)$v['id'],
                'size' => $v['size'],
                'color' => $v['color'],
                'stock' => (int)$v['stock'],
                'price_override' => $v['price_override'] !== null ? (int)$v['price_override'] : null,
                'cost_price' => $v['cost_price'] !== null ? (int)$v['cost_price'] : null,
                'is_default' => (bool)$v['is_default'],
            ];
        }

        // 2. Fetch gallery images for quick preview
        $imgStmt = $pdo->prepare("
            SELECT product_id, image_path, sort_order
            FROM product_images
            WHERE product_id IN ($placeholders)
            ORDER BY sort_order ASC, id ASC
        ");
        $imgStmt->execute($productIds);
        $galleryByProd = [];
        foreach ($imgStmt->fetchAll() as $img) {
            $galleryByProd[(int)$img['product_id']][] = $img['image_path'];
        }

        foreach ($items as &$it) {
            $pid = (int)$it['id'];
            $it['variants'] = $variantsByProd[$pid] ?? [];
            $it['gallery_images'] = $galleryByProd[$pid] ?? [];
        }
        unset($it);
    }

    return [
        'items' => $items,
        'total_count' => $totalCount,
        'total_pages' => $totalPages,
        'current_page' => $page,
        'per_page' => $perPage,
    ];
}

// ==========================================
// 2. Product Detail & Mutation
// ==========================================

/**
 * Fetch all data required for the product editor workstation.
 */
function getProductForEdit(int $id): ?array
{
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        return null;
    }

    $vstmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
    $vstmt->execute([$id]);
    $variants = $vstmt->fetchAll();

    $galleryStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
    $galleryStmt->execute([$id]);
    $galleryImages = $galleryStmt->fetchAll();

    $productTagIds = array_column(getProductTags($id), 'id');
    $priceHistory = getProductPriceHistory($id);

    return [
        'product' => $product,
        'variants' => $variants,
        'gallery_images' => $galleryImages,
        'product_tag_ids' => $productTagIds,
        'price_history' => $priceHistory,
    ];
}

/**
 * Save (insert or update) a product and all related entities transactionally.
 * Honors Rule 1 (PricingService for price & cost changes) and Rule 3 (safe updates).
 *
 * @param array $data Form payload
 * @param array $files Uploaded files ($_FILES)
 * @param int $adminId Current admin id
 * @return array{ok: bool, product_id?: int, errors?: array}
 */
function saveProduct(array $data, array $files, int $adminId): array
{
    $pdo = db();
    $id = (int) ($data['id'] ?? 0);
    $name = trim($data['name'] ?? '');
    $categoryId = (int) ($data['category_id'] ?? 0);
    $description = trim($data['description'] ?? '');
    $price = (int) preg_replace('/\D/', '', $data['price'] ?? '0');
    $discountPriceRaw = trim($data['discount_price'] ?? '');
    $discountPrice = $discountPriceRaw === '' ? null : (int) preg_replace('/\D/', '', $discountPriceRaw);
    $costPriceRaw = trim($data['cost_price'] ?? '');
    $costPrice = $costPriceRaw === '' ? null : (int) preg_replace('/\D/', '', $costPriceRaw);
    $sku = trim($data['sku'] ?? '');
    $isActive = isset($data['is_active']) ? 1 : 0;
    $isFeatured = isset($data['is_featured']) ? 1 : 0;
    $hasVariants = isset($data['has_variants']);
    $stock = $hasVariants ? 0 : (int) ($data['stock'] ?? 0);

    $errors = [];
    if ($name === '') $errors[] = 'نام محصول الزامی است.';
    if ($categoryId < 1) $errors[] = 'دسته‌بندی را انتخاب کنید.';
    if ($price < 1) $errors[] = 'قیمت معتبر وارد کنید.';
    if ($discountPrice !== null && $discountPrice >= $price) {
        $errors[] = 'قیمت با تخفیف باید کمتر از قیمت اصلی باشد.';
    }

    // SKU verification / generation
    if ($sku === '') {
        $sku = generateUniqueSku();
    } else {
        $skuCheck = $pdo->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
        $skuCheck->execute([$sku, $id]);
        if ($skuCheck->fetch()) {
            $errors[] = 'این کد محصول (SKU) قبلاً برای محصول دیگری استفاده شده است.';
        }
    }

    // Fetch existing product if updating
    $existingProduct = null;
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $existingProduct = $stmt->fetch();
        if (!$existingProduct) {
            return ['ok' => false, 'errors' => ['محصول مورد نظر یافت نشد.']];
        }
    }

    // Handle primary image upload
    $newImageName = $existingProduct['image'] ?? null;
    if (!empty($files['image']['name']) && ($files['image']['error'] ?? 1) === UPLOAD_ERR_OK) {
        $uploadResult = $existingProduct
            ? handleProductImageUpload($files['image'], 'product', $id, 'main')
            : handleProductImageUpload($files['image']);
        if ($uploadResult['ok']) {
            if ($newImageName && file_exists(UPLOAD_DIR . $newImageName)) {
                @unlink(UPLOAD_DIR . $newImageName);
            }
            $newImageName = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }

    if (!empty($errors)) {
        return ['ok' => false, 'errors' => $errors];
    }

    // Generate unique slug
    $slug = slugify($name);
    $slugCheck = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
    $slugCheck->execute([$slug, $id]);
    if ($slugCheck->fetch()) {
        $slug .= '-' . substr(md5(uniqid('', true)), 0, 4);
    }

    $productId = $id;
    $useGlobalVariantStrategy = isset($data['use_global_variant_strategy']) ? 1 : 0;

    if ($existingProduct) {
        // Update product metadata (price & cost are recorded via PricingService below)
        $stmt = $pdo->prepare("
            UPDATE products 
            SET category_id=?, name=?, slug=?, description=?, discount_price=?, sku=?, stock=?, use_global_variant_strategy=?, image=?, is_active=?, is_featured=? 
            WHERE id=?
        ");
        $stmt->execute([$categoryId, $name, $slug, $description, $discountPrice, $sku, $stock, $useGlobalVariantStrategy, $newImageName, $isActive, $isFeatured, $id]);

        // Audit price changes if changed
        $oldPrice = (int) $existingProduct['price'];
        if ($price !== $oldPrice) {
            recordPriceChange($productId, null, 'sale_price', 'direct_value', (float) $price, $adminId);
        }

        $oldCostPrice = $existingProduct['cost_price'] !== null ? (int) $existingProduct['cost_price'] : null;
        if ($costPrice !== $oldCostPrice) {
            if ($costPrice === null) {
                $pdo->prepare("UPDATE products SET cost_price = NULL WHERE id = ?")->execute([$productId]);
            } else {
                recordPriceChange($productId, null, 'cost_price', 'direct_value', (float) $costPrice, $adminId);
            }
        }
    } else {
        // Insert new product
        $stmt = $pdo->prepare("
            INSERT INTO products (category_id, name, slug, description, price, discount_price, cost_price, sku, stock, use_global_variant_strategy, image, is_active, is_featured) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([$categoryId, $name, $slug, $description, $price, $discountPrice, $costPrice, $sku, $stock, $useGlobalVariantStrategy, $newImageName, $isActive, $isFeatured]);
        $productId = (int) $pdo->lastInsertId();

        // Rename temporary uploaded image if it was a new creation
        if ($newImageName) {
            $renamed = renameUploadedImage($newImageName, 'product', $productId, 'main');
            if ($renamed) {
                $newImageName = $renamed;
                $pdo->prepare("UPDATE products SET image = ? WHERE id = ?")->execute([$renamed, $productId]);
            }
        }

        // Record initial price creation in price_history
        recordPriceChange($productId, null, 'sale_price', 'direct_value', (float) $price, $adminId, 'قیمت‌گذاری اولیه محصول');
        if ($costPrice !== null) {
            recordPriceChange($productId, null, 'cost_price', 'direct_value', (float) $costPrice, $adminId, 'ثبت اولیه قیمت تمام‌شده');
        }
    }

    // ------------------------------------------
    // Variants: Upsert in place (Rule 2 invariant)
    // ------------------------------------------
    $existingVariantsStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ?");
    $existingVariantsStmt->execute([$productId]);
    $oldVariantsById = [];
    foreach ($existingVariantsStmt->fetchAll() as $v) {
        $oldVariantsById[(int) $v['id']] = $v;
    }

    $submittedVariantIds = [];
    if ($hasVariants) {
        $variantIdsIn = $data['variant_id'] ?? [];
        $sizes = $data['variant_size'] ?? [];
        $colors = $data['variant_color'] ?? [];
        $vstocks = $data['variant_stock'] ?? [];
        $vcostPrices = $data['variant_cost_price'] ?? [];
        $defaultVariantIdx = isset($data['default_variant_index']) ? (int) $data['default_variant_index'] : 0;

        for ($i = 0; $i < count($sizes); $i++) {
            $sz = trim($sizes[$i] ?? '');
            $cl = trim($colors[$i] ?? '');
            $st = (int) ($vstocks[$i] ?? 0);
            $vcpRaw = trim($vcostPrices[$i] ?? '');
            $vcp = $vcpRaw === '' ? null : (int) preg_replace('/\D/', '', $vcpRaw);
            $existingId = (int) ($variantIdsIn[$i] ?? 0);
            $isDefault = ($defaultVariantIdx === $i) ? 1 : 0;

            if ($sz === '' && $cl === '') continue;

            if ($existingId && isset($oldVariantsById[$existingId])) {
                $pdo->prepare("UPDATE product_variants SET size=?, color=?, stock=?, is_default=? WHERE id=? AND product_id=?")
                    ->execute([$sz ?: null, $cl ?: null, $st, $isDefault, $existingId, $productId]);

                $oldVariant = $oldVariantsById[$existingId];
                $oldVcp = $oldVariant['cost_price'] !== null ? (int) $oldVariant['cost_price'] : null;
                if ($vcp !== $oldVcp) {
                    if ($vcp === null) {
                        $pdo->prepare("UPDATE product_variants SET cost_price = NULL WHERE id = ?")->execute([$existingId]);
                    } else {
                        recordPriceChange($productId, $existingId, 'cost_price', 'direct_value', (float) $vcp, $adminId);
                    }
                }
                $submittedVariantIds[] = $existingId;
            } else {
                $vstmt = $pdo->prepare("INSERT INTO product_variants (product_id, size, color, stock, cost_price, is_default) VALUES (?,?,?,?,?,?)");
                $vstmt->execute([$productId, $sz ?: null, $cl ?: null, $st, $vcp, $isDefault]);
                $newVId = (int) $pdo->lastInsertId();
                if ($vcp !== null) {
                    recordPriceChange($productId, $newVId, 'cost_price', 'direct_value', (float) $vcp, $adminId, 'ثبت اولیه قیمت تمام‌شده واریانت');
                }
                $submittedVariantIds[] = $newVId;
            }
        }
    }

    // Delete removed variants
    $idsToDelete = array_diff(array_keys($oldVariantsById), $submittedVariantIds);
    if ($idsToDelete) {
        $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
        $pdo->prepare("DELETE FROM product_variants WHERE id IN ($placeholders) AND product_id = ?")
            ->execute([...$idsToDelete, $productId]);
    }

    // ------------------------------------------
    // Tags Synchronization
    // ------------------------------------------
    $selectedTagIds = array_map('intval', $data['tag_ids'] ?? []);
    $newTagsRaw = trim($data['new_tags'] ?? '');
    syncProductTags($productId, $selectedTagIds, $newTagsRaw);

    // ------------------------------------------
    // Gallery Images Deletion & Additions
    // ------------------------------------------
    $deleteImageIds = array_map('intval', $data['delete_image_ids'] ?? []);
    if ($deleteImageIds) {
        $placeholders = implode(',', array_fill(0, count($deleteImageIds), '?'));
        $imgStmt = $pdo->prepare("SELECT id, image_path FROM product_images WHERE id IN ($placeholders) AND product_id = ?");
        $imgStmt->execute([...$deleteImageIds, $productId]);
        foreach ($imgStmt->fetchAll() as $img) {
            if (file_exists(UPLOAD_DIR . $img['image_path'])) {
                @unlink(UPLOAD_DIR . $img['image_path']);
            }
        }
        $pdo->prepare("DELETE FROM product_images WHERE id IN ($placeholders) AND product_id = ?")
            ->execute([...$deleteImageIds, $productId]);
    }

    if (!empty($files['gallery_images']['name'][0])) {
        $maxSortStmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) FROM product_images WHERE product_id = ?");
        $maxSortStmt->execute([$productId]);
        $maxSort = (int) $maxSortStmt->fetchColumn();
        $galleryFiles = $files['gallery_images'];
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

            $uploadResult = handleProductImageUpload($singleFile, 'product', $productId, 'g' . ($maxSort + 1));
            if ($uploadResult['ok']) {
                $maxSort++;
                $pdo->prepare("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?, ?, ?)")
                    ->execute([$productId, $uploadResult['filename'], $maxSort]);
            }
        }
    }

    return ['ok' => true, 'product_id' => $productId];
}

/**
 * Safely delete a product and associated files from the catalog.
 */
function deleteProduct(int $id): array
{
    $pdo = db();

    // Check if product exists
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();

    if ($img && file_exists(UPLOAD_DIR . $img)) {
        @unlink(UPLOAD_DIR . $img);
    }

    // Delete gallery images from disk
    $galleryStmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
    $galleryStmt->execute([$id]);
    foreach ($galleryStmt->fetchAll() as $g) {
        if (!empty($g['image_path']) && file_exists(UPLOAD_DIR . $g['image_path'])) {
            @unlink(UPLOAD_DIR . $g['image_path']);
        }
    }

    // Deletion cascades product_variants, product_images, product_tags
    $del = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $del->execute([$id]);

    return ['ok' => true];
}

/**
 * Fast boolean toggle for is_active or is_featured.
 */
function quickToggleProductField(int $id, string $field): array
{
    if (!in_array($field, ['is_active', 'is_featured'], true)) {
        return ['ok' => false, 'error' => 'فیلد نامعتبر است.'];
    }

    $pdo = db();
    $stmt = $pdo->prepare("SELECT $field FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetchColumn();

    if ($current === false) {
        return ['ok' => false, 'error' => 'محصول یافت نشد.'];
    }

    $newVal = $current ? 0 : 1;
    $pdo->prepare("UPDATE products SET $field = ?, updated_at = NOW() WHERE id = ?")->execute([$newVal, $id]);

    return ['ok' => true, 'new_value' => $newVal];
}

/**
 * Fast transactional inventory adjustment for product and/or its variants.
 * Respects Rule 3 (concurrency locking).
 */
function quickUpdateProductStock(int $productId, array $variantStocks = [], ?int $parentStock = null): array
{
    if ($productId <= 0) {
        return ['ok' => false, 'error' => 'شناسه محصول نامعتبر است.'];
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT id, stock FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$productId]);
        $prod = $stmt->fetch();
        if (!$prod) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'محصول یافت نشد.'];
        }

        $vStmt = $pdo->prepare("SELECT id, stock FROM product_variants WHERE product_id = ? FOR UPDATE");
        $vStmt->execute([$productId]);
        $existingVariants = $vStmt->fetchAll();

        $totalEffectiveStock = 0;
        if (!empty($existingVariants)) {
            $updateVStmt = $pdo->prepare("UPDATE product_variants SET stock = ? WHERE id = ? AND product_id = ?");
            foreach ($existingVariants as $ev) {
                $vid = (int)$ev['id'];
                if (isset($variantStocks[$vid])) {
                    $newStock = max(0, (int)$variantStocks[$vid]);
                    $updateVStmt->execute([$newStock, $vid, $productId]);
                    $totalEffectiveStock += $newStock;
                } else {
                    $totalEffectiveStock += (int)$ev['stock'];
                }
            }
            $pdo->prepare("UPDATE products SET stock = 0, updated_at = NOW() WHERE id = ?")->execute([$productId]);
        } else {
            $newStock = max(0, (int)($parentStock ?? 0));
            $pdo->prepare("UPDATE products SET stock = ?, updated_at = NOW() WHERE id = ?")->execute([$newStock, $productId]);
            $totalEffectiveStock = $newStock;
        }

        $pdo->commit();
        return [
            'ok' => true,
            'effective_stock' => $totalEffectiveStock,
        ];
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Quick stock update error: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'خطا در به‌روزرسانی موجودی انبار.'];
    }
}

/**
 * Bulk toggle active or featured status for multiple products.
 */
function bulkUpdateProductsStatus(array $ids, string $field, int $value): array
{
    if (!in_array($field, ['is_active', 'is_featured'], true)) {
        return ['ok' => false, 'error' => 'فیلد نامعتبر است.'];
    }
    $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
    if (empty($ids)) {
        return ['ok' => false, 'error' => 'هیچ محصولی انتخاب نشده است.'];
    }

    $val = $value ? 1 : 0;
    $pdo = db();
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $params = array_merge([$val], $ids);
    $stmt = $pdo->prepare("UPDATE products SET $field = ?, updated_at = NOW() WHERE id IN ($placeholders)");
    $stmt->execute($params);

    return ['ok' => true, 'affected' => count($ids)];
}

/**
 * Bulk category reassignment for multiple products.
 */
function bulkUpdateProductsCategory(array $ids, int $categoryId): array
{
    $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
    if (empty($ids)) {
        return ['ok' => false, 'error' => 'هیچ محصولی انتخاب نشده است.'];
    }
    if ($categoryId <= 0) {
        return ['ok' => false, 'error' => 'دسته‌بندی نامعتبر است.'];
    }

    $pdo = db();
    $cStmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
    $cStmt->execute([$categoryId]);
    if (!$cStmt->fetchColumn()) {
        return ['ok' => false, 'error' => 'دسته‌بندی مورد نظر یافت نشد.'];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $params = array_merge([$categoryId], $ids);
    $stmt = $pdo->prepare("UPDATE products SET category_id = ?, updated_at = NOW() WHERE id IN ($placeholders)");
    $stmt->execute($params);

    return ['ok' => true, 'affected' => count($ids)];
}

/**
 * Bulk safe deletion of products.
 */
function bulkDeleteProducts(array $ids): array
{
    $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
    if (empty($ids)) {
        return ['ok' => false, 'error' => 'هیچ محصولی انتخاب نشده است.'];
    }

    $successCount = 0;
    $errors = [];
    foreach ($ids as $id) {
        $res = deleteProduct($id);
        if ($res['ok']) {
            $successCount++;
        } else {
            $errors[] = "محصول #$id: " . ($res['error'] ?? 'خطا در حذف');
        }
    }

    return [
        'ok' => $successCount > 0,
        'affected' => $successCount,
        'errors' => $errors,
    ];
}

// ==========================================
// 3. Category Management
// ==========================================

/**
 * Fetch all categories with hierarchical depth and product count.
 */
function getAllCategoriesWithHierarchy(): array
{
    $categoriesFlat = db()->query("
        SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
        FROM categories c 
        ORDER BY c.sort_order ASC, c.id ASC
    ")->fetchAll();

    $byParent = [];
    foreach ($categoriesFlat as $cat) {
        $byParent[$cat['parent_id'] ?? 0][] = $cat;
    }

    $result = [];
    $walk = function ($parentId, $depth) use (&$walk, &$byParent, &$result) {
        foreach ($byParent[$parentId] ?? [] as $cat) {
            $cat['depth'] = $depth;
            $result[] = $cat;
            $walk($cat['id'], $depth + 1);
        }
    };
    $walk(0, 0);

    return $result;
}

/**
 * Create or update a category record.
 */
function saveCategory(array $data, ?array $imageFile = null): array
{
    $pdo = db();
    $id = (int) ($data['id'] ?? 0);
    $name = trim($data['name'] ?? '');
    $slugRaw = trim($data['slug'] ?? '');
    $description = trim($data['description'] ?? '');
    $sortOrder = (int) ($data['sort_order'] ?? 0);
    $isActive = !empty($data['is_active']) ? 1 : 0;
    $parentId = (int) ($data['parent_id'] ?? 0);
    $parentId = $parentId > 0 ? $parentId : null;
    $removeImage = !empty($data['remove_image']);

    if ($name === '') {
        return ['ok' => false, 'error' => 'نام دسته‌بندی الزامی است.'];
    }

    if ($parentId !== null && $parentId === $id) {
        $parentId = null;
    }

    // Slug: use provided slug or derive from name
    $slug = $slugRaw !== '' ? slugify($slugRaw) : slugify($name);
    if ($slug === '') {
        $slug = 'category-' . time();
    }
    $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
    $check->execute([$slug, $id]);
    if ($check->fetch()) {
        $slug .= '-' . substr(md5(uniqid('', true)), 0, 4);
    }

    // Existing image lookup
    $existingImage = null;
    if ($id > 0) {
        $imgStmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
        $imgStmt->execute([$id]);
        $existingImage = $imgStmt->fetchColumn() ?: null;
    }

    $image = $existingImage;

    // Handle image removal if requested
    if ($removeImage && $existingImage) {
        if (file_exists(UPLOAD_DIR . $existingImage)) {
            @unlink(UPLOAD_DIR . $existingImage);
        }
        $image = null;
    }

    // Handle new image upload
    $newImageUploaded = false;
    if ($imageFile && !empty($imageFile['name']) && $imageFile['error'] === UPLOAD_ERR_OK) {
        $uploadRes = handleProductImageUpload($imageFile, 'category', $id > 0 ? $id : 0, 'icon');
        if (!$uploadRes['ok']) {
            return ['ok' => false, 'error' => $uploadRes['error']];
        }

        // Clean up previous image if exists
        if ($existingImage && file_exists(UPLOAD_DIR . $existingImage)) {
            @unlink(UPLOAD_DIR . $existingImage);
        }

        $image = $uploadRes['filename'];
        $newImageUploaded = true;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE categories SET parent_id=?, name=?, slug=?, description=?, image=?, sort_order=?, is_active=? WHERE id=?");
        $stmt->execute([$parentId, $name, $slug, $description, $image, $sortOrder, $isActive, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (parent_id, name, slug, description, image, sort_order, is_active) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$parentId, $name, $slug, $description, $image, $sortOrder, $isActive]);
        $id = (int) $pdo->lastInsertId();

        // If new category with temporary upload, rename to standard name
        if ($newImageUploaded && $image) {
            $renamed = renameUploadedImage($image, 'category', $id, 'icon');
            if ($renamed) {
                $image = $renamed;
                $pdo->prepare("UPDATE categories SET image = ? WHERE id = ?")->execute([$image, $id]);
            }
        }
    }

    return ['ok' => true, 'id' => $id, 'image' => $image];
}

/**
 * Safely delete a category if it has no products and no subcategories.
 */
function deleteCategory(int $id): array
{
    $pdo = db();

    $hasProducts = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $hasProducts->execute([$id]);
    if ($hasProducts->fetchColumn() > 0) {
        return ['ok' => false, 'error' => 'این دسته‌بندی دارای محصول است و قابل حذف نیست. ابتدا محصولات را جابه‌جا کنید.'];
    }

    $hasChildren = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
    $hasChildren->execute([$id]);
    if ($hasChildren->fetchColumn() > 0) {
        return ['ok' => false, 'error' => 'این دسته‌بندی دارای زیردسته است و قابل حذف نیست. ابتدا زیردسته‌ها را حذف یا جابه‌جا کنید.'];
    }

    // Clean up category image file if exists
    $imgStmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
    $imgStmt->execute([$id]);
    $catImg = $imgStmt->fetchColumn();
    if ($catImg && file_exists(UPLOAD_DIR . $catImg)) {
        @unlink(UPLOAD_DIR . $catImg);
    }

    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    return ['ok' => true];
}

/**
 * Compute high-level Bento KPIs for categories catalog.
 */
function getCategoryCatalogStats(): array
{
    $pdo = db();
    $rows = $pdo->query("
        SELECT 
            c.id,
            c.name,
            c.parent_id,
            c.is_active,
            (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
        FROM categories c
    ")->fetchAll();

    $total = count($rows);
    $rootCount = 0;
    $subCount = 0;
    $activeCount = 0;
    $emptyCount = 0;
    $totalAssignedProducts = 0;
    $topCategory = null;
    $maxProducts = -1;

    foreach ($rows as $r) {
        $pCount = (int)$r['product_count'];
        $totalAssignedProducts += $pCount;

        if ($r['parent_id'] === null) {
            $rootCount++;
        } else {
            $subCount++;
        }

        if (!empty($r['is_active'])) {
            $activeCount++;
        }

        if ($pCount === 0) {
            $emptyCount++;
        }

        if ($pCount > $maxProducts && $pCount > 0) {
            $maxProducts = $pCount;
            $topCategory = [
                'name' => $r['name'],
                'count' => $pCount,
            ];
        }
    }

    return [
        'total' => $total,
        'root_count' => $rootCount,
        'sub_count' => $subCount,
        'active_count' => $activeCount,
        'empty_count' => $emptyCount,
        'total_products' => $totalAssignedProducts,
        'top_category' => $topCategory ?? ['name' => '—', 'count' => 0],
    ];
}

/**
 * Fast boolean toggle for category is_active status.
 */
function quickToggleCategoryActive(int $id): array
{
    if ($id <= 0) {
        return ['ok' => false, 'error' => 'شناسه دسته‌بندی نامعتبر است.'];
    }
    $pdo = db();
    $stmt = $pdo->prepare("SELECT is_active FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetchColumn();
    if ($current === false) {
        return ['ok' => false, 'error' => 'دسته‌بندی یافت نشد.'];
    }

    $newVal = $current ? 0 : 1;
    $pdo->prepare("UPDATE categories SET is_active = ? WHERE id = ?")->execute([$newVal, $id]);
    return ['ok' => true, 'new_value' => $newVal];
}

/**
 * Resolve the default product variant to display based on product and global store strategy.
 *
 * Invariants:
 * - If product follows global strategy, pick variant based on store strategy setting (default: 'highest_stock').
 * - If product uses manual override, pick the variant with is_default = 1.
 * - Always prioritize variants in-stock (stock > 0). If the preferred variant is out of stock,
 *   fall back gracefully to an in-stock variant.
 * - If all variants are out of stock, fall back to the preferred or first variant.
 */
function resolveDefaultProductVariant(array $variants, bool $useGlobalStrategy = true, ?string $strategy = null): ?array
{
    if (empty($variants)) {
        return null;
    }

    $inStock = array_values(array_filter($variants, fn($v) => (int)($v['stock'] ?? 0) > 0));

    if (!$useGlobalStrategy) {
        // Manual override per product
        $defaultCandidates = array_values(array_filter($variants, fn($v) => !empty($v['is_default'])));
        $manualDefault = $defaultCandidates[0] ?? null;

        if ($manualDefault && (int)($manualDefault['stock'] ?? 0) > 0) {
            return $manualDefault;
        }

        // If manual choice is out of stock, fall back to highest in-stock variant
        if (!empty($inStock)) {
            usort($inStock, fn($a, $b) => ((int)$b['stock'] <=> (int)$a['stock']) ?: ((int)$a['id'] <=> (int)$b['id']));
            return $inStock[0];
        }

        return $manualDefault ?: $variants[0];
    }

    // Global Store Strategy
    if ($strategy === null) {
        try {
            $strategy = getSetting('default_variant_strategy', 'highest_stock');
        } catch (Throwable $e) {
            $strategy = 'highest_stock';
        }
    }

    if (!empty($inStock)) {
        if ($strategy === 'highest_stock') {
            usort($inStock, fn($a, $b) => ((int)$b['stock'] <=> (int)$a['stock']) ?: ((int)$a['id'] <=> (int)$b['id']));
            return $inStock[0];
        }
        if ($strategy === 'lowest_stock') {
            usort($inStock, fn($a, $b) => ((int)$a['stock'] <=> (int)$b['stock']) ?: ((int)$a['id'] <=> (int)$b['id']));
            return $inStock[0];
        }
        // 'first_created' or default
        return $inStock[0];
    }

    // All out of stock
    return $variants[0];
}
