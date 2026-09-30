<?php
/**
 * Modern High-Density Admin Gifts & Post-Order Add-ons Workstation
 * Desktop Dual-Pane Master-Detail Workspace (Sticky Smart Studio + Matrix Table & Bento KPIs).
 */
require APP_ROOT . '/views/admin/layout/header.php';

// Prepare lightweight JSON dataset for client-side Studio and instant inspection
$giftItemsJsonMap = [];
foreach ($items as $it) {
    $giftItemsJsonMap[(int)$it['id']] = [
        'id'                => (int)$it['id'],
        'name'              => $it['name'],
        'image_url'         => !empty($it['image']) ? UPLOAD_URL . e($it['image']) : '/assets/img/placeholder-sock.svg',
        'is_active'         => (int)$it['is_active'],
        'is_giftable'       => (int)$it['is_giftable'],
        'is_post_orderable' => (int)$it['is_post_orderable'],
        'cost_price'        => (int)$it['cost_price'],
        'post_order_price'  => $it['post_order_price'] !== null ? (int)$it['post_order_price'] : '',
        'stock'             => (int)$it['stock'],
        'profit_per_unit'   => $it['profit_per_unit'],
        'margin_percent'    => $it['margin_percent'],
        'gifted_units'      => (int)$it['gifted_units'],
        'sold_units'        => (int)$it['sold_units'],
        'gross_revenue'     => (int)$it['gross_revenue'],
    ];
}
$activeEditItem = $editItem ?? null;
?>

<meta name="csrf-token" content="<?= e(csrfToken()) ?>">

<main class="gift-workspace">

    <!-- =================================================================== -->
    <!-- 1. Bento KPI Stats Header (Catalog Health & Utilization Metrics)    -->
    <!-- =================================================================== -->
    <section class="gift-kpi-grid">
        <!-- 1. Total Catalog Items -->
        <div class="gift-kpi-card highlight-blue is-clickable" onclick="location.href='gift_items.php?role=all'" title="مشاهده تمام اقلام کاتالوگ">
            <div class="gift-kpi-header">
                <span class="gift-kpi-title">کاتالوگ هدایا و اقلام جانبی</span>
                <div class="gift-kpi-icon" style="color: var(--gift-blue); background: var(--gift-blue-light);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </div>
            </div>
            <div class="gift-kpi-val-row">
                <span class="gift-kpi-val"><?= toPersianDigits((string)$metrics['total_items']) ?></span>
                <span class="gift-kpi-unit">قلم کالا</span>
            </div>
            <div class="gift-kpi-sub">
                <span style="font-weight: 700; color: var(--gift-success);"><?= toPersianDigits((string)$metrics['active_items']) ?> فعال</span>
                <span>•</span>
                <span><?= toPersianDigits((string)$metrics['inactive_items']) ?> غیرفعال</span>
            </div>
        </div>

        <!-- 2. Admin Free Gifts -->
        <div class="gift-kpi-card highlight-teal is-clickable" onclick="location.href='gift_items.php?role=giftable'" title="فیلتر اقلام قابل اهدا توسط ادمین">
            <div class="gift-kpi-header">
                <span class="gift-kpi-title">هدایای رایگان ادمین</span>
                <div class="gift-kpi-icon" style="color: #0D9488; background: rgba(13, 148, 136, 0.1);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                </div>
            </div>
            <div class="gift-kpi-val-row">
                <span class="gift-kpi-val"><?= toPersianDigits((string)$metrics['lifetime_gifted_units']) ?></span>
                <span class="gift-kpi-unit">بار اهدا در سفارشات</span>
            </div>
            <div class="gift-kpi-sub">
                <span style="font-weight: 700; color: #0D9488;"><?= toPersianDigits((string)$metrics['giftable_count']) ?> قلم در دسترس جهت اهدا</span>
            </div>
        </div>

        <!-- 3. Post-Order Upsell Revenue -->
        <div class="gift-kpi-card highlight-purple is-clickable" onclick="location.href='gift_items.php?role=post_orderable'" title="فیلتر اقلام قابل فروش بعد از سبد">
            <div class="gift-kpi-header">
                <span class="gift-kpi-title">درآمد پیشنهادات سبد خرید</span>
                <div class="gift-kpi-icon" style="color: var(--gift-purple); background: var(--gift-purple-light);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                </div>
            </div>
            <div class="gift-kpi-val-row">
                <span class="gift-kpi-val" style="font-size: 1.25rem; color: var(--gift-purple);"><?= formatPrice($metrics['lifetime_post_order_revenue']) ?></span>
            </div>
            <div class="gift-kpi-sub">
                <span><?= toPersianDigits((string)$metrics['lifetime_sold_units']) ?> واحد فروش مکمل</span>
                <span>•</span>
                <span><?= toPersianDigits((string)$metrics['post_orderable_count']) ?> قلم در سبد</span>
            </div>
        </div>

        <!-- 4. Inventory Valuation & Low Stock Alert -->
        <div class="gift-kpi-card highlight-amber is-clickable" onclick="location.href='gift_items.php?role=low_stock'" title="فیلتر اقلام با موجودی کم یا ناموجود">
            <div class="gift-kpi-header">
                <span class="gift-kpi-title">ارزش موجودی ملزومات انبار</span>
                <div class="gift-kpi-icon" style="color: var(--gift-warning); background: var(--gift-warning-light);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
            </div>
            <div class="gift-kpi-val-row">
                <span class="gift-kpi-val"><?= formatPrice($metrics['total_inventory_valuation']) ?></span>
            </div>
            <div class="gift-kpi-sub">
                <?php if ($metrics['low_stock_count'] > 0): ?>
                    <span style="color: var(--gift-warning); font-weight: 700;">⚠️ <?= toPersianDigits((string)$metrics['low_stock_count']) ?> قلم رو به اتمام (موجودی ≤ ۵)</span>
                <?php else: ?>
                    <span style="color: var(--gift-success); font-weight: 700;">موجودی تمامی اقلام تکمیل است</span>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 2. Dual-Pane Master-Detail Workbench Grid                           -->
    <!-- =================================================================== -->
    <div class="gift-split-grid">

        <!-- =============================================================== -->
        <!-- COLUMN 1: Master Matrix Table                                   -->
        <!-- =============================================================== -->
        <div class="gift-master-card">

            <!-- Filter Toolbar -->
            <div class="gift-toolbar">
                <div class="gift-toolbar-top">
                    <!-- Live Search -->
                    <div class="gift-search-wrap">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" class="gift-search-input" id="giftLiveSearch" placeholder="جستجوی بلادرنگ نام قلم هدیه یا جانبی..." value="<?= e($search) ?>" autocomplete="off">
                    </div>

                    <!-- Role Filter Chips -->
                    <div class="gift-filter-chips">
                        <a href="gift_items.php?role=all" class="gift-chip-btn <?= $roleFilter === 'all' ? 'is-active' : '' ?>">
                            همه اقلام
                            <span class="chip-count"><?= toPersianDigits((string)$metrics['total_items']) ?></span>
                        </a>
                        <a href="gift_items.php?role=giftable" class="gift-chip-btn <?= $roleFilter === 'giftable' ? 'is-active' : '' ?>">
                            هدایای ادمین
                            <span class="chip-count"><?= toPersianDigits((string)$metrics['giftable_count']) ?></span>
                        </a>
                        <a href="gift_items.php?role=post_orderable" class="gift-chip-btn <?= $roleFilter === 'post_orderable' ? 'is-active' : '' ?>">
                            پیشنهاد سبد
                            <span class="chip-count"><?= toPersianDigits((string)$metrics['post_orderable_count']) ?></span>
                        </a>
                        <a href="gift_items.php?role=hybrid" class="gift-chip-btn <?= $roleFilter === 'hybrid' ? 'is-active' : '' ?>">
                            دوکاربره
                            <span class="chip-count"><?= toPersianDigits((string)$metrics['hybrid_count']) ?></span>
                        </a>
                        <a href="gift_items.php?role=low_stock" class="gift-chip-btn <?= $roleFilter === 'low_stock' ? 'is-active' : '' ?>">
                            کم‌موجودی
                            <span class="chip-count"><?= toPersianDigits((string)$metrics['low_stock_count']) ?></span>
                        </a>
                        <a href="gift_items.php?role=inactive" class="gift-chip-btn <?= $roleFilter === 'inactive' ? 'is-active' : '' ?>">
                            غیرفعال‌ها
                            <span class="chip-count"><?= toPersianDigits((string)$metrics['inactive_items']) ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Master Table -->
            <div class="gift-table-container">
                <table class="gift-table" id="giftItemsTable">
                    <thead>
                        <tr>
                            <th style="width: 54px; text-align: center;">تصویر</th>
                            <th>نام و شناسه قلم</th>
                            <th style="width: 140px;">نقش‌های فعال</th>
                            <th style="width: 110px;">بهای تمام‌شده</th>
                            <th style="width: 120px;">قیمت فروش سبد</th>
                            <th style="width: 100px;">حاشیه سود</th>
                            <th style="width: 90px; text-align: center;">موجودی</th>
                            <th style="width: 110px;">آمار عملکرد</th>
                            <th style="width: 70px; text-align: center;">وضعیت</th>
                            <th style="width: 110px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it):
                            $img = !empty($it['image']) ? UPLOAD_URL . e($it['image']) : '/assets/img/placeholder-sock.svg';
                            $stockVal = (int) $it['stock'];
                            $salePrice = $it['post_order_price'] !== null ? (int)$it['post_order_price'] : null;
                            $margin = $it['margin_percent'];
                            $isSelected = ($activeEditItem && (int)$activeEditItem['id'] === (int)$it['id']);
                        ?>
                        <tr id="row-<?= (int)$it['id'] ?>" class="<?= $isSelected ? 'is-selected' : '' ?>" data-name="<?= e(mb_strtolower($it['name'])) ?>">
                            <!-- Thumbnail -->
                            <td style="text-align: center;">
                                <div class="gift-thumb-wrap">
                                    <img src="<?= $img ?>" alt="<?= e($it['name']) ?>" class="gift-thumb-img" loading="lazy">
                                </div>
                            </td>

                            <!-- Title & ID -->
                            <td>
                                <div class="gift-item-title"><?= e($it['name']) ?></div>
                                <div class="gift-item-id-badge">شناسه: #<?= (int)$it['id'] ?></div>
                            </td>

                            <!-- Roles -->
                            <td>
                                <div class="gift-role-tags">
                                    <?php if (!empty($it['is_giftable'])): ?>
                                        <span class="gift-role-pill gift-role-giftable" title="قابل اهدا به سفارشات توسط ادمین">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/></svg>
                                            هدیه ادمین
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($it['is_post_orderable'])): ?>
                                        <span class="gift-role-pill gift-role-postorder" title="پیشنهاد مکمل در سبد خرید">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                            فروش در سبد
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Cost Price -->
                            <td>
                                <span class="gift-price-cost"><?= formatPrice((int)$it['cost_price']) ?></span>
                            </td>

                            <!-- Post-Order Sale Price -->
                            <td>
                                <?php if ($salePrice !== null): ?>
                                    <span class="gift-price-sale"><?= formatPrice($salePrice) ?></span>
                                <?php else: ?>
                                    <span style="color: var(--gift-text-muted);">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- Margin % -->
                            <td>
                                <?php if ($margin !== null):
                                    $marginClass = ($margin >= 40) ? 'gift-margin-high' : (($margin >= 20) ? 'gift-margin-mid' : 'gift-margin-low');
                                ?>
                                    <span class="gift-margin-badge <?= $marginClass ?>">
                                        <?= toPersianDigits((string)$margin) ?>٪
                                        (<?= toPersianDigits((string)round($it['profit_per_unit'] / 1000)) ?>ه)
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--gift-text-muted); font-size: 0.8rem;">رایگان</span>
                                <?php endif; ?>
                            </td>

                            <!-- Stock -->
                            <td style="text-align: center;">
                                <?php
                                $stockClass = ($stockVal === 0) ? 'gift-stock-out' : (($stockVal <= 5) ? 'gift-stock-low' : 'gift-stock-ok');
                                ?>
                                <span class="gift-stock-badge <?= $stockClass ?>" onclick="quickEditStock(<?= (int)$it['id'] ?>, <?= $stockVal ?>)" title="جهت تنظیم سریع موجودی کلیک کنید">
                                    <?= ($stockVal === 0) ? 'ناموجود' : toPersianDigits((string)$stockVal) ?>
                                </span>
                            </td>

                            <!-- Performance -->
                            <td>
                                <div class="gift-perf-wrap">
                                    <?php if (!empty($it['gifted_units'])): ?>
                                        <div class="gift-perf-line">
                                            <span>اهدا:</span>
                                            <span class="gift-perf-val"><?= toPersianDigits((string)$it['gifted_units']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($it['sold_units'])): ?>
                                        <div class="gift-perf-line">
                                            <span>فروش:</span>
                                            <span class="gift-perf-val"><?= toPersianDigits((string)$it['sold_units']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (empty($it['gifted_units']) && empty($it['sold_units'])): ?>
                                        <span style="color: var(--gift-text-muted); font-size: 0.72rem;">بدون سفارش</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Status Toggle Switch -->
                            <td style="text-align: center;">
                                <label class="gift-switch" title="فعال یا غیرفعال کردن آیتم">
                                    <input type="checkbox" onchange="toggleItemActive(<?= (int)$it['id'] ?>, this)" <?= !empty($it['is_active']) ? 'checked' : '' ?>>
                                    <span class="gift-slider"></span>
                                </label>
                            </td>

                            <!-- Actions -->
                            <td style="text-align: center;">
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    <button type="button" class="gift-action-btn" onclick="loadItemIntoStudio(<?= (int)$it['id'] ?>)" title="ویرایش قلم در استودیو">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        ویرایش
                                    </button>
                                    <form method="post" action="gift_items.php" onsubmit="return confirm('حذف قلم «<?= e($it['name']) ?>»؟ (سفارش‌های قبلی تاریخچه‌شان حفظ می‌ماند)');" style="display:inline; margin:0;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                                        <button type="submit" class="gift-action-btn gift-action-danger" title="حذف قلم">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($items)): ?>
                            <tr id="noResultsRow">
                                <td colspan="10" style="text-align: center; padding: 40px; color: var(--gift-text-muted);">
                                    هیچ آیتمی با شرایط انتخابی یافت نشد.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- =============================================================== -->
        <!-- COLUMN 2: Sticky Smart Studio & Live Editor                     -->
        <!-- =============================================================== -->
        <aside class="gift-studio-card <?= $activeEditItem ? 'mode-editing' : '' ?>" id="giftStudioCard">
            <!-- Studio Header -->
            <div class="gift-studio-header">
                <div class="gift-studio-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    <span id="studioTitleText"><?= $activeEditItem ? 'ویرایش قلم: ' . e($activeEditItem['name']) : 'افزودن قلم هدیه یا جانبی جدید' ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span class="gift-mode-badge" id="studioModeBadge"><?= $activeEditItem ? 'حالت ویرایش' : 'قلم جدید' ?></span>
                    <button type="button" class="gift-btn-reset-mode" id="studioResetBtn" onclick="resetStudioToCreate()" title="بازگشت به حالت ثبت قلم جدید">
                        ✕ انصراف
                    </button>
                </div>
            </div>

            <!-- Studio Form -->
            <form method="post" action="gift_items.php" enctype="multipart/form-data" id="giftStudioForm" class="gift-studio-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="studioItemId" value="<?= (int)($activeEditItem['id'] ?? 0) ?>">

                <!-- Image Dropzone & Preview -->
                <div class="gift-form-group">
                    <label class="gift-form-label">
                        <span>تصویر قلم کالا</span>
                        <span style="font-size: 0.72rem; color: var(--gift-text-muted);">WebP فشرده خودکار</span>
                    </label>
                    <div class="gift-dropzone" onclick="document.getElementById('studioImageInput').click()">
                        <div class="gift-preview-container">
                            <img src="<?= !empty($activeEditItem['image']) ? UPLOAD_URL . e($activeEditItem['image']) : '/assets/img/placeholder-sock.svg' ?>" id="studioPreviewImg" class="gift-preview-img" alt="پیش‌نمایش تصویر">
                            <div class="gift-dropzone-info">
                                <div style="font-weight: 700; color: var(--gift-brand-primary); margin-bottom: 2px;">کلیک جهت انتخاب یا تغییر تصویر</div>
                                <div>JPG، PNG، WEBP (حداکثر ۲ مگابایت)</div>
                            </div>
                        </div>
                        <input type="file" name="image" id="studioImageInput" accept="image/*,.heic,.heif" data-optimize-image="giftitem" data-max-dimension="1600" data-default-quality="0.30" style="display: none;" onchange="handleStudioImagePreview(this)">
                    </div>
                </div>

                <!-- Name Input -->
                <div class="gift-form-group">
                    <label class="gift-form-label">
                        <span>نام قلم / عنوان نمایشی <span class="req">*</span></span>
                    </label>
                    <input type="text" name="name" id="studioNameInput" class="gift-form-input" placeholder="مثلاً: جعبه کادویی هاردباکس طرح رز" value="<?= e($activeEditItem['name'] ?? '') ?>" required>
                </div>

                <!-- Role Selection Cards -->
                <div class="gift-form-group">
                    <label class="gift-form-label">نقش‌ها و دسترسی‌های قلم <span class="req">*</span></label>
                    <div class="gift-role-selector">
                        <label class="gift-role-option">
                            <input type="checkbox" name="is_giftable" id="studioIsGiftable" <?= (!$activeEditItem || !empty($activeEditItem['is_giftable'])) ? 'checked' : '' ?>>
                            <div>
                                <div class="gift-role-option-title">قابل اهدای رایگان به سفارش توسط مدیر</div>
                                <div class="gift-role-option-desc">در جزئیات سفارش، مدیر می‌تواند این قلم را به عنوان اشانتیون یا کادو بیفزاید.</div>
                            </div>
                        </label>
                        <label class="gift-role-option">
                            <input type="checkbox" name="is_post_orderable" id="studioIsPostOrderable" onchange="togglePostOrderPriceGroup(this.checked)" <?= (!empty($activeEditItem['is_post_orderable'])) ? 'checked' : '' ?>>
                            <div>
                                <div class="gift-role-option-title">قابل فروش به‌عنوان «پیشنهاد بعد از سبد خرید»</div>
                                <div class="gift-role-option-desc">در صفحه سبد خرید به عنوان محصول مکمل و افزایشی به مشتری پیشنهاد داده می‌شود.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Financials: Cost & Post-Order Selling Price -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="gift-form-group">
                        <label class="gift-form-label">
                            <span>بهای تمام‌شده (تومان) <span class="req">*</span></span>
                        </label>
                        <input type="text" inputmode="numeric" name="cost_price" id="studioCostPrice" class="gift-form-input" placeholder="مثلاً: ۱۲,۰۰۰" value="<?= e((string)($activeEditItem['cost_price'] ?? '')) ?>" oninput="calculateStudioMargin()" required>
                    </div>

                    <div class="gift-form-group" id="studioPostOrderPriceGroup" style="<?= empty($activeEditItem['is_post_orderable']) ? 'opacity: 0.5; pointer-events: none;' : '' ?>">
                        <label class="gift-form-label">
                            <span>قیمت فروش در سبد (تومان)</span>
                        </label>
                        <input type="text" inputmode="numeric" name="post_order_price" id="studioPostOrderPrice" class="gift-form-input" placeholder="مثلاً: ۲۵,۰۰۰" value="<?= e((string)($activeEditItem['post_order_price'] ?? '')) ?>" oninput="calculateStudioMargin()">
                    </div>
                </div>

                <!-- Real-time Live Margin Calculator Box -->
                <div class="gift-margin-calculator" id="studioMarginBox">
                    <div class="gift-calc-item">
                        <span class="gift-calc-label">سود ناخالص هر واحد:</span>
                        <span class="gift-calc-val" id="studioUnitProfit">—</span>
                    </div>
                    <div class="gift-calc-item" style="text-align: left;">
                        <span class="gift-calc-label">حاشیه سود فروش:</span>
                        <span class="gift-calc-pill" id="studioMarginPercent">—</span>
                    </div>
                </div>

                <!-- Stock & Active Switch -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; align-items: end;">
                    <div class="gift-form-group">
                        <label class="gift-form-label">
                            <span>موجودی انبار <span class="req">*</span></span>
                        </label>
                        <input type="number" name="stock" id="studioStock" class="gift-form-input" min="0" value="<?= e((string)($activeEditItem['stock'] ?? 0)) ?>" required>
                    </div>

                    <div class="gift-form-group">
                        <label class="gift-role-option" style="padding-bottom: 8px;">
                            <input type="checkbox" name="is_active" id="studioIsActive" <?= (!$activeEditItem || !empty($activeEditItem['is_active'])) ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                            <span style="font-weight: 700; font-size: 0.85rem; color: var(--gift-text-primary);">وضعیت قلم فعال</span>
                        </label>
                    </div>
                </div>

                <!-- Studio Footer Actions -->
                <div class="gift-studio-footer">
                    <button type="submit" class="gift-btn-submit" id="studioSubmitBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span id="studioSubmitText"><?= $activeEditItem ? 'ذخیره تغییرات قلم' : 'ثبت قلم جدید در کاتالوگ' ?></span>
                    </button>
                </div>
            </form>
        </aside>

    </div>

</main>

<script>
// Lightweight Client Dataset
const giftItemsMap = <?= json_encode($giftItemsJsonMap, JSON_UNESCAPED_UNICODE) ?>;
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Helper: Format Persian numbers
function toFaDigits(str) {
    const f = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    return String(str).replace(/[0-9]/g, w => f[+w]);
}

// Helper: Format Price
function formatPriceJs(num) {
    if (isNaN(num) || num === null) return '—';
    return toFaDigits(Number(num).toLocaleString('en-US')) + ' ت';
}

// Live Search Filter
document.getElementById('giftLiveSearch').addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    const rows = document.querySelectorAll('#giftItemsTable tbody tr[data-name]');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        if (query === '' || name.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    let noRes = document.getElementById('noResultsRow');
    if (!noRes && visibleCount === 0) {
        const tbody = document.querySelector('#giftItemsTable tbody');
        const tr = document.createElement('tr');
        tr.id = 'noResultsRow';
        tr.innerHTML = '<td colspan="10" style="text-align: center; padding: 40px; color: var(--gift-text-muted);">هیچ آیتمی با عنوان مورد نظر یافت نشد.</td>';
        tbody.appendChild(tr);
    } else if (noRes && visibleCount > 0) {
        noRes.remove();
    }
});

// Load Item into Studio for Edit
function loadItemIntoStudio(id) {
    const item = giftItemsMap[id];
    if (!item) return;

    // Highlight row
    document.querySelectorAll('#giftItemsTable tr').forEach(r => r.classList.remove('is-selected'));
    const selectedRow = document.getElementById('row-' + id);
    if (selectedRow) selectedRow.classList.add('is-selected');

    // Populate Studio
    const studio = document.getElementById('giftStudioCard');
    studio.classList.add('mode-editing');

    document.getElementById('studioTitleText').textContent = 'ویرایش قلم: ' + item.name;
    document.getElementById('studioModeBadge').textContent = 'حالت ویرایش';
    document.getElementById('studioSubmitText').textContent = 'ذخیره تغییرات قلم';
    document.getElementById('studioResetBtn').style.display = 'inline-block';

    document.getElementById('studioItemId').value = item.id;
    document.getElementById('studioNameInput').value = item.name;
    document.getElementById('studioPreviewImg').src = item.image_url;
    document.getElementById('studioIsGiftable').checked = (item.is_giftable === 1);
    document.getElementById('studioIsPostOrderable').checked = (item.is_post_orderable === 1);
    togglePostOrderPriceGroup(item.is_post_orderable === 1);

    document.getElementById('studioCostPrice').value = item.cost_price ? Number(item.cost_price).toLocaleString('en-US') : '';
    document.getElementById('studioPostOrderPrice').value = item.post_order_price ? Number(item.post_order_price).toLocaleString('en-US') : '';
    document.getElementById('studioStock').value = item.stock;
    document.getElementById('studioIsActive').checked = (item.is_active === 1);

    calculateStudioMargin();

    // Scroll to studio smoothly if needed on smaller displays
    if (window.innerWidth < 1200) {
        studio.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Reset Studio to Create Mode
function resetStudioToCreate() {
    document.querySelectorAll('#giftItemsTable tr').forEach(r => r.classList.remove('is-selected'));

    const studio = document.getElementById('giftStudioCard');
    studio.classList.remove('mode-editing');

    document.getElementById('studioTitleText').textContent = 'افزودن قلم هدیه یا جانبی جدید';
    document.getElementById('studioModeBadge').textContent = 'قلم جدید';
    document.getElementById('studioSubmitText').textContent = 'ثبت قلم جدید در کاتالوگ';
    document.getElementById('studioResetBtn').style.display = 'none';

    document.getElementById('studioItemId').value = '0';
    document.getElementById('studioNameInput').value = '';
    document.getElementById('studioPreviewImg').src = '/assets/img/placeholder-sock.svg';
    document.getElementById('studioImageInput').value = '';
    document.getElementById('studioIsGiftable').checked = true;
    document.getElementById('studioIsPostOrderable').checked = false;
    togglePostOrderPriceGroup(false);

    document.getElementById('studioCostPrice').value = '';
    document.getElementById('studioPostOrderPrice').value = '';
    document.getElementById('studioStock').value = '0';
    document.getElementById('studioIsActive').checked = true;

    calculateStudioMargin();
}

// Post-Order Price Group Toggle
function togglePostOrderPriceGroup(enabled) {
    const grp = document.getElementById('studioPostOrderPriceGroup');
    if (enabled) {
        grp.style.opacity = '1';
        grp.style.pointerEvents = 'auto';
    } else {
        grp.style.opacity = '0.5';
        grp.style.pointerEvents = 'none';
    }
    calculateStudioMargin();
}

// Real-time Live Margin Calculation
function calculateStudioMargin() {
    const isPostOrder = document.getElementById('studioIsPostOrderable').checked;
    const costRaw = document.getElementById('studioCostPrice').value.replace(/\D/g, '');
    const postRaw = document.getElementById('studioPostOrderPrice').value.replace(/\D/g, '');

    const profitEl = document.getElementById('studioUnitProfit');
    const marginEl = document.getElementById('studioMarginPercent');

    if (!isPostOrder || postRaw === '' || costRaw === '') {
        profitEl.textContent = '—';
        marginEl.textContent = '—';
        marginEl.style.background = 'var(--gift-border-subtle)';
        marginEl.style.color = 'var(--gift-text-muted)';
        return;
    }

    const cost = parseInt(costRaw, 10);
    const sale = parseInt(postRaw, 10);

    if (sale <= 0) {
        profitEl.textContent = '—';
        marginEl.textContent = '—';
        return;
    }

    const profit = sale - cost;
    const marginPercent = Math.round((profit / sale) * 100);

    profitEl.textContent = formatPriceJs(profit);
    marginEl.textContent = toFaDigits(marginPercent) + '٪';

    if (marginPercent >= 40) {
        marginEl.style.background = 'var(--gift-success-light)';
        marginEl.style.color = 'var(--gift-success)';
    } else if (marginPercent >= 20) {
        marginEl.style.background = 'var(--gift-warning-light)';
        marginEl.style.color = '#D97706';
    } else {
        marginEl.style.background = 'var(--gift-danger-light)';
        marginEl.style.color = 'var(--gift-danger)';
    }
}

// Instant Image Preview from File Input
function handleStudioImagePreview(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('studioPreviewImg').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// AJAX: Toggle Item Active Status
async function toggleItemActive(id, checkbox) {
    const originalChecked = checkbox.checked;
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('action', 'toggle_active');
    formData.append('id', id);

    try {
        const response = await fetch('gift_items.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (!data.ok) {
            alert(data.error || 'خطا در تغییر وضعیت آیتم.');
            checkbox.checked = !originalChecked;
        } else if (giftItemsMap[id]) {
            giftItemsMap[id].is_active = data.is_active;
        }
    } catch (e) {
        alert('خطای ارتباط با سرور.');
        checkbox.checked = !originalChecked;
    }
}

// AJAX: Quick Stock Adjustment
async function quickEditStock(id, currentStock) {
    const item = giftItemsMap[id];
    const name = item ? item.name : 'قلم';
    const input = prompt('تنظیم سریع موجودی انبار برای «' + name + '»:', currentStock);
    if (input === null) return;

    const newStock = parseInt(input.trim(), 10);
    if (isNaN(newStock) || newStock < 0) {
        alert('لطفاً یک عدد معتبر و مثبت وارد کنید.');
        return;
    }

    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('action', 'quick_stock');
    formData.append('id', id);
    formData.append('stock', newStock);

    try {
        const response = await fetch('gift_items.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (data.ok) {
            location.reload();
        } else {
            alert(data.error || 'خطا در ثبت موجودی جدید.');
        }
    } catch (e) {
        alert('خطای ارتباط با سرور.');
    }
}

// Initial calculation on page load
calculateStudioMargin();
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
