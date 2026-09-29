<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<!-- 1. Bento KPI Stats Header -->
<div class="c2c-kpi-grid" style="margin-bottom: 20px;">
    <!-- Total Products -->
    <div class="c2c-kpi-card" style="border-right: 4px solid var(--color-primary);">
        <div class="c2c-kpi-header">
            <span class="c2c-kpi-title">کل محصولات کاتالوگ</span>
            <div class="c2c-kpi-icon" style="background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.12); color: var(--color-primary);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
        </div>
        <div class="c2c-kpi-value"><?= toPersianDigits((string)$stats['total_products']) ?> <span style="font-size: 0.85rem; font-weight: 500; color: var(--color-muted);">قلم کالا</span></div>
        <div class="c2c-kpi-sub">
            <span style="color: var(--color-success); font-weight: 600;"><?= toPersianDigits((string)$stats['active_products']) ?> فعال</span>
            <span style="color: var(--color-muted); margin: 0 4px;">•</span>
            <span style="color: var(--color-muted);"><?= toPersianDigits((string)($stats['total_products'] - $stats['active_products'])) ?> غیرفعال</span>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="c2c-kpi-card" style="border-right: 4px solid #f59e0b;">
        <div class="c2c-kpi-header">
            <span class="c2c-kpi-title">موجودی رو به اتمام (≤ ۳)</span>
            <div class="c2c-kpi-icon" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
        </div>
        <div class="c2c-kpi-value" style="color: #d97706;"><?= toPersianDigits((string)$stats['low_stock_count']) ?> <span style="font-size: 0.85rem; font-weight: 500; color: var(--color-muted);">محصول</span></div>
        <div class="c2c-kpi-sub">نیاز به شارژ مجدد و سفارش خرید</div>
    </div>

    <!-- Out of Stock -->
    <div class="c2c-kpi-card" style="border-right: 4px solid var(--color-danger);">
        <div class="c2c-kpi-header">
            <span class="c2c-kpi-title">کالاهای ناموجود (صفر)</span>
            <div class="c2c-kpi-icon" style="background: rgba(239, 68, 68, 0.12); color: var(--color-danger);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </div>
        </div>
        <div class="c2c-kpi-value" style="color: var(--color-danger);"><?= toPersianDigits((string)$stats['out_of_stock_count']) ?> <span style="font-size: 0.85rem; font-weight: 500; color: var(--color-muted);">محصول</span></div>
        <div class="c2c-kpi-sub">توقف فروش به دلیل اتمام موجودی</div>
    </div>

    <!-- Inventory Capital Valuation -->
    <div class="c2c-kpi-card" style="border-right: 4px solid var(--color-success);">
        <div class="c2c-kpi-header">
            <span class="c2c-kpi-title">ارزش سرمایه انبار</span>
            <div class="c2c-kpi-icon" style="background: rgba(16, 185, 129, 0.12); color: var(--color-success);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
        </div>
        <div class="c2c-kpi-value" style="font-size: 1.35rem; color: var(--color-success);"><?= formatPrice($stats['inventory_valuation']) ?></div>
        <div class="c2c-kpi-sub">محاسبه بر مبنای بهای تمام‌شده/قیمت کالا</div>
    </div>
</div>

<!-- 2. Status Segment Tabs -->
<div class="admin-card" style="padding: 12px 16px; margin-bottom: 16px;">
    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
            <a href="products.php?status=all<?= $search ? '&q=' . urlencode($search) : '' ?><?= $categoryId ? '&category_id=' . $categoryId : '' ?>" 
               class="btn btn-sm <?= ($status === 'all') ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px;">
                همه محصولات (<?= toPersianDigits((string)$stats['total_products']) ?>)
            </a>
            <a href="products.php?status=active<?= $search ? '&q=' . urlencode($search) : '' ?><?= $categoryId ? '&category_id=' . $categoryId : '' ?>" 
               class="btn btn-sm <?= ($status === 'active') ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px;">
                فعال (<?= toPersianDigits((string)$stats['active_products']) ?>)
            </a>
            <a href="products.php?status=featured<?= $search ? '&q=' . urlencode($search) : '' ?><?= $categoryId ? '&category_id=' . $categoryId : '' ?>" 
               class="btn btn-sm <?= ($status === 'featured') ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px;">
                پیشنهاد ویژه (<?= toPersianDigits((string)$stats['featured_products']) ?>)
            </a>
            <a href="products.php?status=low_stock<?= $search ? '&q=' . urlencode($search) : '' ?><?= $categoryId ? '&category_id=' . $categoryId : '' ?>" 
               class="btn btn-sm <?= ($status === 'low_stock') ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px; color: <?= $status !== 'low_stock' ? '#d97706' : '#fff' ?>;">
                رو به اتمام (<?= toPersianDigits((string)$stats['low_stock_count']) ?>)
            </a>
            <a href="products.php?status=out_of_stock<?= $search ? '&q=' . urlencode($search) : '' ?><?= $categoryId ? '&category_id=' . $categoryId : '' ?>" 
               class="btn btn-sm <?= ($status === 'out_of_stock') ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px; color: <?= $status !== 'out_of_stock' ? 'var(--color-danger)' : '#fff' ?>;">
                ناموجود (<?= toPersianDigits((string)$stats['out_of_stock_count']) ?>)
            </a>
            <a href="products.php?status=discounted<?= $search ? '&q=' . urlencode($search) : '' ?><?= $categoryId ? '&category_id=' . $categoryId : '' ?>" 
               class="btn btn-sm <?= ($status === 'discounted') ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px;">
                دارای تخفیف
            </a>
        </div>
        <a href="product_edit.php" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: var(--radius-md);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            محصول جدید
        </a>
    </div>
</div>

<!-- 3. Filter & Search Toolbar -->
<div class="admin-card" style="padding: 14px 18px; margin-bottom: 20px;">
    <form method="get" action="products.php" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <input type="hidden" name="status" value="<?= e($status) ?>">

        <div style="flex: 2; min-width: 220px;">
            <input class="form-control" type="text" name="q" placeholder="جستجوی نام محصول یا کد SKU..." value="<?= e($search) ?>" style="height: 38px;">
        </div>

        <div style="flex: 1.5; min-width: 180px;">
            <select class="form-control" name="category_id" style="height: 38px;">
                <option value="">همه دسته‌بندی‌ها</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= ($categoryId === (int)$c['id']) ? 'selected' : '' ?>>
                        <?= $c['depth'] > 0 ? str_repeat('— ', $c['depth']) : '' ?><?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="flex: 1.2; min-width: 150px;">
            <select class="form-control" name="sort" style="height: 38px;">
                <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>جدیدترین</option>
                <option value="oldest" <?= ($sort === 'oldest') ? 'selected' : '' ?>>قدیمی‌ترین</option>
                <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>ارزان‌ترین</option>
                <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>گران‌ترین</option>
                <option value="stock_desc" <?= ($sort === 'stock_desc') ? 'selected' : '' ?>>بیشترین موجودی</option>
                <option value="stock_asc" <?= ($sort === 'stock_asc') ? 'selected' : '' ?>>کمترین موجودی</option>
                <option value="name_asc" <?= ($sort === 'name_asc') ? 'selected' : '' ?>>نام کالا (الفبا)</option>
            </select>
        </div>

        <div style="display: flex; gap: 8px;">
            <button class="btn btn-outline" type="submit" style="height: 38px; padding: 0 16px;">اعمال فیلتر</button>
            <?php if ($search !== '' || $categoryId > 0 || $sort !== 'newest' || $status !== 'all'): ?>
                <a href="products.php" class="btn btn-sm btn-outline" style="height: 38px; display: inline-flex; align-items: center; color: var(--color-muted);" title="حذف تمام فیلترها">✕ پاک‌سازی</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- 4. Products High-Density Table Card -->
<div class="admin-card" style="padding: 0; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table class="admin-table" style="margin: 0; width: 100%;">
            <thead>
                <tr style="background: var(--color-bg-subtle, rgba(0,0,0,0.02)); border-bottom: 1px solid var(--color-border);">
                    <th style="width: 60px; text-align: center;">تصویر</th>
                    <th>مشخصات کالا</th>
                    <th style="width: 120px;">کد SKU</th>
                    <th style="width: 130px;">دسته‌بندی</th>
                    <th style="width: 130px;">قیمت فروش</th>
                    <th style="width: 160px;">وضعیت انبار</th>
                    <th style="width: 90px; text-align: center;">ویترین</th>
                    <th style="width: 90px; text-align: center;">وضعیت</th>
                    <th style="width: 140px; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($catalog['items'] as $p): 
                $img = !empty($p['image']) ? UPLOAD_URL . e($p['image']) : '/assets/img/placeholder-sock.svg';
                $effStock = (int) $p['effective_stock'];
                $hasDiscount = !empty($p['discount_price']) && $p['discount_price'] < $p['price'];
                $returnUrl = 'products.php?' . http_build_query($_GET);
            ?>
            <tr style="border-bottom: 1px solid var(--color-border); vertical-align: middle;">
                <!-- Thumbnail -->
                <td style="text-align: center; padding: 10px;">
                    <div style="width: 48px; height: 48px; border-radius: 8px; overflow: hidden; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--color-border);">
                        <img src="<?= $img ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                    </div>
                </td>

                <!-- Product Name & Badges -->
                <td>
                    <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 4px;">
                        <a href="product_edit.php?id=<?= (int)$p['id'] ?>" style="color: var(--color-text); text-decoration: none;">
                            <?= e($p['name']) ?>
                        </a>
                    </div>
                    <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                        <?php if (!empty($p['is_featured'])): ?>
                            <span class="status-pill status-shipped" style="font-size: 0.72rem; padding: 2px 8px;">ویژه صفحه اصلی</span>
                        <?php endif; ?>
                        <?php if ($hasDiscount): ?>
                            <span class="status-pill" style="background: rgba(239, 68, 68, 0.12); color: var(--color-danger); font-size: 0.72rem; padding: 2px 8px;">
                                تخفیف‌دار
                            </span>
                        <?php endif; ?>
                        <?php if ((int)$p['variant_count'] > 0): ?>
                            <span style="font-size: 0.72rem; color: var(--color-muted); background: var(--color-bg-subtle, rgba(0,0,0,0.04)); padding: 2px 6px; border-radius: 4px;">
                                <?= toPersianDigits((string)$p['variant_count']) ?> واریانت
                            </span>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- SKU -->
                <td dir="ltr" style="font-family: monospace; font-size: 0.85rem; color: var(--color-muted);">
                    <?= e($p['sku'] ?: '—') ?>
                </td>

                <!-- Category -->
                <td>
                    <span style="font-size: 0.88rem; color: var(--color-text-secondary, #4b5563);">
                        <?= e($p['category_name']) ?>
                    </span>
                </td>

                <!-- Price -->
                <td>
                    <?php if ($hasDiscount): ?>
                        <div style="font-weight: 700; color: var(--color-danger); font-size: 0.92rem;">
                            <?= formatPrice((int)$p['discount_price']) ?>
                        </div>
                        <div style="text-decoration: line-through; color: var(--color-muted); font-size: 0.78rem;">
                            <?= formatPrice((int)$p['price']) ?>
                        </div>
                    <?php else: ?>
                        <div style="font-weight: 700; font-size: 0.92rem;">
                            <?= formatPrice((int)$p['price']) ?>
                        </div>
                    <?php endif; ?>
                </td>

                <!-- Stock & Variants Breakdown -->
                <td>
                    <?php if (!empty($p['variant_stock_summary'])): ?>
                        <div style="font-size: 0.78rem; line-height: 1.8;">
                            <?php foreach (explode(' | ', $p['variant_stock_summary']) as $vLine): 
                                $vParts = explode(': ', $vLine);
                                $vStockVal = (int) ($vParts[1] ?? 0);
                            ?>
                                <div style="display: flex; justify-content: space-between; gap: 8px; border-bottom: 1px dashed var(--color-border); padding: 1px 0;">
                                    <span style="color: var(--color-muted);"><?= e($vParts[0] ?? '') ?>:</span>
                                    <strong style="color: <?= $vStockVal === 0 ? 'var(--color-danger)' : ($vStockVal <= 3 ? '#d97706' : 'var(--color-success)') ?>;">
                                        <?= toPersianDigits((string)$vStockVal) ?>
                                    </strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <?php if ($effStock === 0): ?>
                            <span class="status-pill status-cancelled" style="font-size: 0.78rem;">ناموجود (۰)</span>
                        <?php elseif ($effStock <= 3): ?>
                            <span class="status-pill" style="background: rgba(245, 158, 11, 0.12); color: #d97706; font-size: 0.78rem;">
                                اندک: <?= toPersianDigits((string)$effStock) ?> عدد
                            </span>
                        <?php else: ?>
                            <span class="status-pill status-delivered" style="font-size: 0.78rem;">
                                <?= toPersianDigits((string)$effStock) ?> عدد
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>

                <!-- Featured Toggle Switch -->
                <td style="text-align: center;">
                    <form method="post" action="products.php" style="display: inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="field" value="is_featured">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <input type="hidden" name="return_url" value="<?= e($returnUrl) ?>">
                        <button type="submit" style="background: none; border: none; cursor: pointer; padding: 4px;" title="تغییر وضعیت پیشنهاد ویژه">
                            <?php if (!empty($p['is_featured'])): ?>
                                <span style="display: inline-block; width: 14px; height: 14px; border-radius: 50%; background: #f59e0b; box-shadow: 0 0 6px #f59e0b;"></span>
                            <?php else: ?>
                                <span style="display: inline-block; width: 14px; height: 14px; border-radius: 50%; background: #d1d5db;"></span>
                            <?php endif; ?>
                        </button>
                    </form>
                </td>

                <!-- Active Toggle Switch -->
                <td style="text-align: center;">
                    <form method="post" action="products.php" style="display: inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="field" value="is_active">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <input type="hidden" name="return_url" value="<?= e($returnUrl) ?>">
                        <button type="submit" class="btn btn-sm" style="padding: 2px 10px; font-size: 0.78rem; border-radius: 12px; background: <?= !empty($p['is_active']) ? 'rgba(16, 185, 129, 0.12)' : 'rgba(239, 68, 68, 0.12)' ?>; color: <?= !empty($p['is_active']) ? 'var(--color-success)' : 'var(--color-danger)' ?>; border: none; cursor: pointer;">
                            <?= !empty($p['is_active']) ? 'فعال' : 'غیرفعال' ?>
                        </button>
                    </form>
                </td>

                <!-- Actions -->
                <td style="text-align: center;">
                    <div class="admin-actions" style="justify-content: center; gap: 4px;">
                        <a href="product_edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline" title="ویرایش محصول" style="padding: 4px 8px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <a href="/product.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" class="btn btn-sm btn-outline" title="مشاهده در فروشگاه" style="padding: 4px 8px; color: var(--color-muted);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                        <form method="post" action="products.php" onsubmit="return confirm('آیا از حذف این محصول و تصاویر آن اطمینان دارید؟');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <input type="hidden" name="return_url" value="<?= e($returnUrl) ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="حذف کالا" style="padding: 4px 8px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($catalog['items'])): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px 20px; color: var(--color-muted);">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 8px; opacity: 0.5;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <div style="font-size: 0.95rem;">هیچ محصولی با معیارهای انتخاب‌شده پیدا نشد.</div>
                        <a href="products.php" style="display: inline-block; margin-top: 10px; font-size: 0.85rem; color: var(--color-primary);">مشاهده همه محصولات</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 5. Modern Pagination Bar -->
    <?php if ($catalog['total_pages'] > 1): 
        $currPage = $catalog['current_page'];
        $totPages = $catalog['total_pages'];
        $baseQuery = $_GET;
    ?>
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-top: 1px solid var(--color-border); background: var(--color-bg-subtle, rgba(0,0,0,0.01)); flex-wrap: wrap; gap: 12px;">
        <div style="font-size: 0.85rem; color: var(--color-muted);">
            نمایش <?= toPersianDigits((string)((($currPage - 1) * $catalog['per_page']) + 1)) ?> تا <?= toPersianDigits((string)min($currPage * $catalog['per_page'], $catalog['total_count'])) ?> از <?= toPersianDigits((string)$catalog['total_count']) ?> کالا
        </div>
        <div style="display: flex; gap: 6px; align-items: center;">
            <?php if ($currPage > 1): 
                $baseQuery['page'] = $currPage - 1;
            ?>
                <a href="products.php?<?= http_build_query($baseQuery) ?>" class="btn btn-sm btn-outline">قبلی</a>
            <?php endif; ?>

            <?php for ($i = max(1, $currPage - 2); $i <= min($totPages, $currPage + 2); $i++): 
                $baseQuery['page'] = $i;
            ?>
                <a href="products.php?<?= http_build_query($baseQuery) ?>" class="btn btn-sm <?= ($i === $currPage) ? 'btn-primary' : 'btn-outline' ?>" style="min-width: 32px; text-align: center;">
                    <?= toPersianDigits((string)$i) ?>
                </a>
            <?php endfor; ?>

            <?php if ($currPage < $totPages): 
                $baseQuery['page'] = $currPage + 1;
            ?>
                <a href="products.php?<?= http_build_query($baseQuery) ?>" class="btn btn-sm btn-outline">بعدی</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
