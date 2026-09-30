<?php
/**
 * Modern High-Density Admin Products Catalog View
 * Desktop-first SaaS table, Bento KPIs, Variant Popovers, Floating Bulk Actions & Side Dossier Drawer.
 */
require APP_ROOT . '/views/admin/layout/header.php';

// Prepare lightweight JSON dataset for client-side Drawer & instant inspection
$productsJsonMap = [];
foreach ($catalog['items'] as $item) {
    $salePrice = !empty($item['discount_price']) && $item['discount_price'] < $item['price'] ? (int)$item['discount_price'] : (int)$item['price'];
    $costPrice = !empty($item['cost_price']) ? (int)$item['cost_price'] : null;
    $marginPercent = null;
    if ($salePrice > 0 && $costPrice !== null && $costPrice > 0) {
        $marginPercent = (int) round((($salePrice - $costPrice) / $salePrice) * 100);
    }

    $galleryUrls = [];
    foreach ($item['gallery_images'] ?? [] as $gImg) {
        $galleryUrls[] = !empty($gImg) ? UPLOAD_URL . e($gImg) : '';
    }

    $productsJsonMap[(int)$item['id']] = [
        'id'             => (int)$item['id'],
        'name'           => $item['name'],
        'sku'            => $item['sku'] ?? '',
        'category_name'  => $item['category_name'] ?? '',
        'price'          => (int)$item['price'],
        'price_fmt'      => formatPrice((int)$item['price']),
        'discount_price' => $item['discount_price'] ? (int)$item['discount_price'] : null,
        'discount_fmt'   => $item['discount_price'] ? formatPrice((int)$item['discount_price']) : '',
        'cost_price'     => $costPrice,
        'cost_fmt'       => $costPrice ? formatPrice($costPrice) : '',
        'margin_percent' => $marginPercent,
        'stock'          => (int)$item['effective_stock'],
        'is_active'      => (int)$item['is_active'],
        'is_featured'    => (int)$item['is_featured'],
        'image_url'      => !empty($item['image']) ? UPLOAD_URL . e($item['image']) : '/assets/img/placeholder-sock.svg',
        'variants'       => $item['variants'] ?? [],
        'gallery_urls'   => array_filter($galleryUrls),
    ];
}
$returnUrl = 'products.php?' . http_build_query($_GET);
?>

<meta name="csrf-token" content="<?= e(csrfToken()) ?>">

<main class="prod-workspace">

    <!-- =================================================================== -->
    <!-- 1. Bento KPI Stats Header (Clickable Direct Filter Anchors)         -->
    <!-- =================================================================== -->
    <section class="prod-kpi-grid">
        <!-- 1. Total Catalog Products -->
        <div class="prod-kpi-card highlight-blue" onclick="location.href='products.php?status=all'">
            <div class="prod-kpi-header">
                <span class="prod-kpi-title">کل محصولات کاتالوگ</span>
                <div class="prod-kpi-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
            </div>
            <div class="prod-kpi-val-row">
                <span class="prod-kpi-val"><?= toPersianDigits((string)$stats['total_products']) ?></span>
                <span class="prod-kpi-unit">قلم کالا</span>
            </div>
            <div class="prod-kpi-sub">
                <span style="color: var(--prod-success); font-weight: 700;"><?= toPersianDigits((string)$stats['active_products']) ?> فعال</span>
                <span>•</span>
                <span><?= toPersianDigits((string)($stats['total_products'] - $stats['active_products'])) ?> غیرفعال</span>
            </div>
        </div>

        <!-- 2. Low Stock Alert -->
        <div class="prod-kpi-card highlight-amber" onclick="location.href='products.php?status=low_stock'">
            <div class="prod-kpi-header">
                <span class="prod-kpi-title">موجودی رو به اتمام (≤ ۳)</span>
                <div class="prod-kpi-icon" style="color: var(--prod-warning); background: var(--prod-warning-light);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
            </div>
            <div class="prod-kpi-val-row">
                <span class="prod-kpi-val" style="color: #D97706;"><?= toPersianDigits((string)$stats['low_stock_count']) ?></span>
                <span class="prod-kpi-unit">محصول</span>
            </div>
            <div class="prod-kpi-sub">کلیک جهت مشاهده و شارژ انبار</div>
        </div>

        <!-- 3. Out of Stock -->
        <div class="prod-kpi-card highlight-red" onclick="location.href='products.php?status=out_of_stock'">
            <div class="prod-kpi-header">
                <span class="prod-kpi-title">کالاهای ناموجود (صفر)</span>
                <div class="prod-kpi-icon" style="color: var(--prod-danger); background: var(--prod-danger-light);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                </div>
            </div>
            <div class="prod-kpi-val-row">
                <span class="prod-kpi-val" style="color: var(--prod-danger);"><?= toPersianDigits((string)$stats['out_of_stock_count']) ?></span>
                <span class="prod-kpi-unit">محصول</span>
            </div>
            <div class="prod-kpi-sub">توقف فروش به علت کسری موجودی</div>
        </div>

        <!-- 4. Inventory Capital Valuation -->
        <div class="prod-kpi-card highlight-green">
            <div class="prod-kpi-header">
                <span class="prod-kpi-title">ارزش سرمایه انبار</span>
                <div class="prod-kpi-icon" style="color: var(--prod-success); background: var(--prod-success-light);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <div class="prod-kpi-val-row">
                <span class="prod-kpi-val" style="color: var(--prod-success); font-size: 1.32rem;"><?= formatPrice($stats['inventory_valuation']) ?></span>
            </div>
            <div class="prod-kpi-sub">محاسبه بر مبنای بهای خرید / قیمت کالا</div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 2. Control Toolbar & Segmented Tabs                                 -->
    <!-- =================================================================== -->
    <section class="prod-toolbar-card">
        <!-- Status Tabs Row -->
        <div class="prod-tabs-row">
            <nav class="prod-status-tabs" aria-label="فیلتر بر مبنای وضعیت محصول">
                <?php
                $tabParams = $_GET;
                unset($tabParams['status'], $tabParams['page']);
                $qStr = !empty($tabParams) ? '&' . http_build_query($tabParams) : '';
                ?>
                <a href="products.php?status=all<?= $qStr ?>" class="prod-tab-pill <?= ($status === 'all') ? 'active' : '' ?>">
                    <span>همه محصولات</span>
                    <span class="prod-tab-badge"><?= toPersianDigits((string)$stats['total_products']) ?></span>
                </a>
                <a href="products.php?status=active<?= $qStr ?>" class="prod-tab-pill <?= ($status === 'active') ? 'active' : '' ?>">
                    <span>فعال</span>
                    <span class="prod-tab-badge"><?= toPersianDigits((string)$stats['active_products']) ?></span>
                </a>
                <a href="products.php?status=featured<?= $qStr ?>" class="prod-tab-pill <?= ($status === 'featured') ? 'active' : '' ?>">
                    <span>پیشنهاد ویژه</span>
                    <span class="prod-tab-badge"><?= toPersianDigits((string)$stats['featured_products']) ?></span>
                </a>
                <a href="products.php?status=low_stock<?= $qStr ?>" class="prod-tab-pill <?= ($status === 'low_stock') ? 'active' : '' ?>" style="<?= $status !== 'low_stock' ? 'color:#D97706;' : '' ?>">
                    <span>رو به اتمام</span>
                    <span class="prod-tab-badge"><?= toPersianDigits((string)$stats['low_stock_count']) ?></span>
                </a>
                <a href="products.php?status=out_of_stock<?= $qStr ?>" class="prod-tab-pill <?= ($status === 'out_of_stock') ? 'active' : '' ?>" style="<?= $status !== 'out_of_stock' ? 'color:var(--prod-danger);' : '' ?>">
                    <span>ناموجود</span>
                    <span class="prod-tab-badge"><?= toPersianDigits((string)$stats['out_of_stock_count']) ?></span>
                </a>
                <a href="products.php?status=discounted<?= $qStr ?>" class="prod-tab-pill <?= ($status === 'discounted') ? 'active' : '' ?>">
                    <span>دارای تخفیف</span>
                </a>
            </nav>

            <a href="product_edit.php" class="prod-btn-new">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>محصول جدید</span>
            </a>
        </div>

        <!-- Filter Form Row -->
        <form method="get" action="products.php" class="prod-filters-row">
            <input type="hidden" name="status" value="<?= e($status) ?>">

            <!-- Search -->
            <div class="prod-search-wrap">
                <svg class="prod-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input class="prod-search-input" type="search" name="q" placeholder="جستجوی عنوان محصول، مشخصات یا کد SKU..." value="<?= e($search) ?>" autocomplete="off">
            </div>

            <!-- Category -->
            <select class="prod-select prod-select-category" name="category_id">
                <option value="">همه دسته‌بندی‌ها</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= ($categoryId === (int)$c['id']) ? 'selected' : '' ?>>
                        <?= $c['depth'] > 0 ? str_repeat('— ', $c['depth']) : '' ?><?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Sort -->
            <select class="prod-select prod-select-sort" name="sort">
                <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>جدیدترین</option>
                <option value="oldest" <?= ($sort === 'oldest') ? 'selected' : '' ?>>قدیمی‌ترین</option>
                <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>ارزان‌ترین</option>
                <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>گران‌ترین</option>
                <option value="stock_desc" <?= ($sort === 'stock_desc') ? 'selected' : '' ?>>بیشترین موجودی</option>
                <option value="stock_asc" <?= ($sort === 'stock_asc') ? 'selected' : '' ?>>کمترین موجودی</option>
                <option value="name_asc" <?= ($sort === 'name_asc') ? 'selected' : '' ?>>نام کالا (الفبا)</option>
            </select>

            <!-- Per Page -->
            <select class="prod-select prod-select-perpage" name="per_page">
                <option value="20" <?= ($perPage === 20) ? 'selected' : '' ?>>۲۰ در صفحه</option>
                <option value="50" <?= ($perPage === 50) ? 'selected' : '' ?>>۵۰ در صفحه</option>
                <option value="100" <?= ($perPage === 100) ? 'selected' : '' ?>>۱۰۰ در صفحه</option>
            </select>

            <button class="prod-btn-filter" type="submit">اعمال فیلتر</button>

            <?php if ($search !== '' || $categoryId > 0 || $sort !== 'newest' || $status !== 'all' || $perPage !== 20): ?>
                <a href="products.php" class="prod-btn-clear" title="حذف تمام فیلترها">✕ پاک‌سازی</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- =================================================================== -->
    <!-- 3. High-Density Products Data Table                                 -->
    <!-- =================================================================== -->
    <div class="prod-table-card">
        <div class="prod-table-wrap">
            <table class="prod-table">
                <thead class="prod-thead">
                    <tr>
                        <th class="prod-th prod-th-center" style="width: 40px;">
                            <input type="checkbox" id="prodCheckAll" class="prod-checkbox" title="انتخاب همه">
                        </th>
                        <th class="prod-th prod-th-center" style="width: 58px;">تصویر</th>
                        <th class="prod-th">مشخصات و عنوان کالا</th>
                        <th class="prod-th" style="width: 110px;">کد SKU</th>
                        <th class="prod-th" style="width: 120px;">دسته‌بندی</th>
                        <th class="prod-th" style="width: 125px;">قیمت فروش</th>
                        <th class="prod-th" style="width: 130px;">بهای خرید و سود</th>
                        <th class="prod-th" style="width: 150px;">وضعیت انبار</th>
                        <th class="prod-th prod-th-center" style="width: 75px;">ویترین</th>
                        <th class="prod-th prod-th-center" style="width: 75px;">وضعیت</th>
                        <th class="prod-th prod-th-center" style="width: 135px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($catalog['items'] as $p): 
                    $pid = (int)$p['id'];
                    $img = !empty($p['image']) ? UPLOAD_URL . e($p['image']) : '/assets/img/placeholder-sock.svg';
                    $effStock = (int) $p['effective_stock'];
                    $hasDiscount = !empty($p['discount_price']) && $p['discount_price'] < $p['price'];
                    $variantCount = (int)$p['variant_count'];
                    $costVal = !empty($p['cost_price']) ? (int)$p['cost_price'] : 0;
                    $actualSalePrice = $hasDiscount ? (int)$p['discount_price'] : (int)$p['price'];
                    $marginPct = ($actualSalePrice > 0 && $costVal > 0) ? (int)round((($actualSalePrice - $costVal) / $actualSalePrice) * 100) : null;
                ?>
                <tr class="prod-row" id="prod-row-<?= $pid ?>">
                    <!-- Checkbox -->
                    <td class="prod-td prod-td-center">
                        <input type="checkbox" class="prod-checkbox prod-check-row" value="<?= $pid ?>" data-id="<?= $pid ?>">
                    </td>

                    <!-- Thumbnail -->
                    <td class="prod-td prod-td-center">
                        <div class="prod-thumb-box btn-inspect" data-id="<?= $pid ?>" title="مشاهده پیش‌نمایش در کشو">
                            <img src="<?= $img ?>" class="prod-thumb-img" alt="" loading="lazy">
                        </div>
                    </td>

                    <!-- Title & Badges -->
                    <td class="prod-td">
                        <div class="prod-title-group">
                            <a href="#" class="prod-title-link" data-id="<?= $pid ?>" title="بررسی سریع مشخصات کالا">
                                <?= e($p['name']) ?>
                            </a>
                            <div class="prod-badges-row">
                                <?php if (!empty($p['is_featured'])): ?>
                                    <span class="prod-badge prod-badge-gold">ویژه صفحه اصلی</span>
                                <?php endif; ?>
                                <?php if ($hasDiscount): ?>
                                    <span class="prod-badge prod-badge-sale">تخفیف‌دار</span>
                                <?php endif; ?>
                                <?php if ($variantCount > 0): ?>
                                    <span class="prod-badge prod-badge-variant">
                                        <?= toPersianDigits((string)$variantCount) ?> واریانت
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>

                    <!-- SKU -->
                    <td class="prod-td" dir="ltr">
                        <?php if (!empty($p['sku'])): ?>
                            <span class="prod-sku-chip" data-sku="<?= e($p['sku']) ?>" title="کلیک برای کپی SKU">
                                <?= e($p['sku']) ?>
                            </span>
                        <?php else: ?>
                            <span style="color: var(--prod-text-muted); font-size: 0.8rem;">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Category -->
                    <td class="prod-td">
                        <span class="prod-cat-tag">
                            <?= e($p['category_name']) ?>
                        </span>
                    </td>

                    <!-- Sale Price -->
                    <td class="prod-td">
                        <div class="prod-price-box">
                            <?php if ($hasDiscount): ?>
                                <span class="prod-price-current prod-price-sale"><?= formatPrice((int)$p['discount_price']) ?></span>
                                <span class="prod-price-old"><?= formatPrice((int)$p['price']) ?></span>
                            <?php else: ?>
                                <span class="prod-price-current"><?= formatPrice((int)$p['price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Cost Price & Margin (User Request) -->
                    <td class="prod-td">
                        <div class="prod-cost-box">
                            <?php if ($costVal > 0): ?>
                                <span class="prod-cost-val"><?= formatPrice($costVal) ?></span>
                                <?php if ($marginPct !== null): ?>
                                    <span class="prod-margin-pill <?= $marginPct > 0 ? 'margin-positive' : 'margin-neutral' ?>">
                                        <?= toPersianDigits((string)$marginPct) ?>٪ سود
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: var(--prod-text-muted); font-size: 0.78rem;">ثبت نشده</span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Stock & Popover -->
                    <td class="prod-td prod-stock-cell">
                        <?php if ($variantCount > 0): ?>
                            <div class="prod-stock-pill <?= $effStock === 0 ? 'stock-red' : ($effStock <= 3 ? 'stock-amber' : 'stock-green') ?> prod-variant-trigger" id="stock-badge-<?= $pid ?>" title="کلیک جهت مشاهده جزئیات سایزها">
                                <span>مجموع: <?= toPersianDigits((string)$effStock) ?></span>
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                            <!-- Variant Popover -->
                            <div class="prod-variant-popover">
                                <div class="prod-vpop-title">موجودی واریانت‌ها:</div>
                                <?php foreach ($p['variants'] as $v): 
                                    $vLabel = trim(($v['size'] ?? '') . ' ' . ($v['color'] ?? '')) ?: 'پیش‌فرض';
                                    $vStock = (int)$v['stock'];
                                ?>
                                    <div class="prod-vpop-row">
                                        <span style="color: var(--prod-text-secondary);"><?= e($vLabel) ?>:</span>
                                        <strong style="color: <?= $vStock === 0 ? 'var(--prod-danger)' : ($vStock <= 3 ? 'var(--prod-warning)' : 'var(--prod-success)') ?>;">
                                            <?= toPersianDigits((string)$vStock) ?> عدد
                                        </strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="prod-stock-pill <?= $effStock === 0 ? 'stock-red' : ($effStock <= 3 ? 'stock-amber' : 'stock-green') ?>" id="stock-badge-<?= $pid ?>">
                                <?= toPersianDigits((string)$effStock) ?> عدد
                            </div>
                        <?php endif; ?>
                    </td>

                    <!-- Featured Toggle Switch -->
                    <td class="prod-td prod-td-center">
                        <label class="prod-switch switch-featured" title="سوییچ پیشنهاد ویژه (صفحه اصلی)">
                            <input type="checkbox" data-id="<?= $pid ?>" data-field="is_featured" <?= !empty($p['is_featured']) ? 'checked' : '' ?>>
                            <span class="prod-slider"></span>
                        </label>
                    </td>

                    <!-- Active Toggle Switch -->
                    <td class="prod-td prod-td-center">
                        <label class="prod-switch" title="سوییچ انتشار محصول (فعال / غیرفعال)">
                            <input type="checkbox" data-id="<?= $pid ?>" data-field="is_active" <?= !empty($p['is_active']) ? 'checked' : '' ?>>
                            <span class="prod-slider"></span>
                        </label>
                    </td>

                    <!-- Actions -->
                    <td class="prod-td prod-td-center">
                        <div class="prod-actions-group">
                            <!-- Quick Inspect (Drawer) -->
                            <button type="button" class="prod-act-btn btn-inspect" data-id="<?= $pid ?>" title="بررسی سریع و تنظیم انبار">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>

                            <!-- Full Edit -->
                            <a href="product_edit.php?id=<?= $pid ?>" class="prod-act-btn" title="ویرایش کامل کالا">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>

                            <!-- View Storefront -->
                            <a href="/product.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" class="prod-act-btn" title="مشاهده در فروشگاه">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>

                            <!-- Delete -->
                            <form method="post" action="products.php" onsubmit="return confirm('آیا از حذف محصول «<?= e(addslashes($p['name'])) ?>» اطمینان دارید؟');" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $pid ?>">
                                <input type="hidden" name="return_url" value="<?= e($returnUrl) ?>">
                                <button type="submit" class="prod-act-btn btn-del" title="حذف محصول">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($catalog['items'])): ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 48px 20px; color: var(--prod-text-muted);">
                            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 10px; opacity: 0.4;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <div style="font-size: 0.95rem; font-weight: 600;">هیچ محصولی با معیارهای انتخاب‌شده پیدا نشد.</div>
                            <a href="products.php" style="display: inline-block; margin-top: 10px; font-size: 0.84rem; color: var(--prod-brand-primary); font-weight: 700;">مشاهده همه محصولات کاتالوگ</a>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- =============================================================== -->
        <!-- 4. Modern Sticky Pagination Bar                                 -->
        <!-- =============================================================== -->
        <?php if ($catalog['total_pages'] > 1): 
            $currPage = $catalog['current_page'];
            $totPages = $catalog['total_pages'];
            $baseQuery = $_GET;
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-top: 1px solid var(--prod-border-card); background: #FAF8F5; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 0.82rem; color: var(--prod-text-muted);">
                نمایش <strong><?= toPersianDigits((string)((($currPage - 1) * $catalog['per_page']) + 1)) ?></strong> تا <strong><?= toPersianDigits((string)min($currPage * $catalog['per_page'], $catalog['total_count'])) ?></strong> از <strong><?= toPersianDigits((string)$catalog['total_count']) ?></strong> کالا
            </div>
            <div style="display: flex; gap: 5px; align-items: center;">
                <?php if ($currPage > 1): 
                    $baseQuery['page'] = $currPage - 1;
                ?>
                    <a href="products.php?<?= http_build_query($baseQuery) ?>" class="btn btn-sm btn-outline" style="background:#FFF;">قبلی</a>
                <?php endif; ?>

                <?php for ($i = max(1, $currPage - 2); $i <= min($totPages, $currPage + 2); $i++): 
                    $baseQuery['page'] = $i;
                ?>
                    <a href="products.php?<?= http_build_query($baseQuery) ?>" class="btn btn-sm <?= ($i === $currPage) ? 'btn-primary' : 'btn-outline' ?>" style="min-width: 32px; text-align: center; <?= $i !== $currPage ? 'background:#FFF;' : '' ?>">
                        <?= toPersianDigits((string)$i) ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currPage < $totPages): 
                    $baseQuery['page'] = $currPage + 1;
                ?>
                    <a href="products.php?<?= http_build_query($baseQuery) ?>" class="btn btn-sm btn-outline" style="background:#FFF;">بعدی</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- =================================================================== -->
    <!-- 5. Floating Bulk Action Bar                                         -->
    <!-- =================================================================== -->
    <div class="prod-bulk-bar" id="prodBulkBar">
        <span class="prod-bulk-count" id="prodBulkCount">۰ کالا انتخاب شد</span>
        <div class="prod-bulk-btns">
            <button type="button" class="prod-bulk-btn" id="bulkActivate">فعال‌سازی</button>
            <button type="button" class="prod-bulk-btn" id="bulkDeactivate">غیرفعال‌سازی</button>
            <div style="display: flex; align-items: center; gap: 4px;">
                <select id="bulkCategorySelect" style="height: 28px; background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; font-size: 0.74rem; padding: 0 8px;">
                    <option value="" style="color:#000;">انتقال به دسته...</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" style="color:#000;"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="prod-bulk-btn" id="bulkCategory">انتقال</button>
            </div>
            <button type="button" class="prod-bulk-btn btn-bulk-danger" id="bulkDelete">حذف گروهی</button>
        </div>
        <button type="button" class="prod-bulk-close" id="bulkCancel" title="لغو انتخاب">✕</button>
    </div>

    <!-- =================================================================== -->
    <!-- 6. Side Dossier Drawer (Quick Inspect & Stock Editor)               -->
    <!-- =================================================================== -->
    <div class="prod-drawer-backdrop" id="prodDrawerBackdrop"></div>
    <aside class="prod-drawer" id="prodDrawer" aria-label="کشوی جزئیات و انبار کالا">
        <div class="prod-drawer-header">
            <div class="prod-drawer-title-box">
                <img src="" id="drawerThumb" class="prod-drawer-thumb" alt="">
                <div>
                    <div id="drawerName" class="prod-drawer-name">نام کالا</div>
                    <div id="drawerSku" class="prod-drawer-sku">کد SKU</div>
                </div>
            </div>
            <button type="button" class="prod-drawer-close" id="drawerClose" title="بستن کشو (Esc)">✕</button>
        </div>

        <div class="prod-drawer-body">
            <!-- Financial Snapshot -->
            <div class="prod-drawer-kpis">
                <div class="prod-dkpi-item">
                    <span class="prod-dkpi-label">قیمت فروش:</span>
                    <strong class="prod-dkpi-val" id="drawerPrice">—</strong>
                </div>
                <div class="prod-dkpi-item">
                    <span class="prod-dkpi-label">بهای تمام‌شده:</span>
                    <strong class="prod-dkpi-val" id="drawerCost">—</strong>
                </div>
                <div class="prod-dkpi-item">
                    <span class="prod-dkpi-label">مارجین ناخالص:</span>
                    <strong class="prod-dkpi-val" id="drawerMargin">—</strong>
                </div>
            </div>

            <!-- Quick Stock Editor -->
            <div>
                <div class="prod-drawer-section-title">
                    <span>مدیریت سریع موجودی انبار</span>
                    <span style="font-size: 0.72rem; color: var(--prod-text-muted); font-weight: normal;">ویرایش و ذخیره آنی</span>
                </div>
                <div id="drawerStockEditor"></div>
            </div>

            <!-- Gallery Strip -->
            <div id="drawerGallerySection">
                <div class="prod-drawer-section-title">گالری تصاویر کالا</div>
                <div class="prod-gallery-strip" id="drawerGallery"></div>
            </div>
        </div>

        <div class="prod-drawer-footer">
            <button type="button" class="prod-btn-save-stock" id="drawerSaveStock">ذخیره تغییرات موجودی</button>
            <a href="#" class="prod-btn-full-edit" id="drawerFullEdit">
                <span>ویرایش کامل کالا</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
        </div>
    </aside>

    <!-- Toast Notification -->
    <div class="prod-toast" id="prodToast"></div>

</main>

<script>
    // Hydrate client-side product dataset for instant drawer & interactions
    window.productsDataMap = <?= json_encode($productsJsonMap, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/assets/js/admin-products.js?v=<?= APP_VERSION . '.' . (@filemtime(APP_ROOT . '/assets/js/admin-products.js') ?: 1) ?>"></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
