<?php
/**
 * Modern High-Density Admin Inventory Valuation & Capital Health Dashboard
 * Part of AB-Socks Financial Hub.
 */
require APP_ROOT . '/views/admin/layout/header.php';

$catStats = $report['category_stats'] ?? [];
$topInvested = $report['top_invested'] ?? [];
$deadStock = $report['dead_stock_items'] ?? [];
?>

<main class="fin-workspace">

    <!-- =================================================================== -->
    <!-- 1. Top Header & Action Strip                                        -->
    <!-- =================================================================== -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
            <h2 style="margin: 0 0 6px 0; font-size: 1.35rem; font-weight: 800; color: var(--fin-text-primary); display: flex; align-items: center; gap: 8px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                ارزش‌گذاری و سلامت سرمایه انبار
            </h2>
            <div style="font-size: 0.85rem; color: var(--fin-text-muted);">
                پایش سرمایه در گردش قفل‌شده در موجودی کالا، پتانسیل سودآوری و ردیابی کالاهای راکد
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <button type="button" onclick="window.print()" class="fin-action-btn fin-btn-outline" title="چاپ کارنامه ارزش‌گذاری انبار">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>چاپ کارنامه انبار</span>
            </button>
            <a href="pricing.php" class="fin-action-btn fin-btn-outline" title="اصلاح قیمت خرید و فروش کاتالوگ">
                <span>مدیریت قیمت‌ها و بهای خرید ←</span>
            </a>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 2. Cost Health Alert (Missing Cost Notice)                          -->
    <!-- =================================================================== -->
    <?php if ($report['missing_cost_count'] > 0): ?>
    <section class="fin-alert-banner" style="margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                ⚠️
            </div>
            <div style="font-size: 0.85rem; line-height: 1.6; color: #92400E;">
                <strong>کسری بهای خرید در انبار:</strong>
                تعداد <strong><?= toPersianDigits((string)$report['missing_cost_count']) ?> محصول</strong> دارای موجودی فعال در انبار هستند اما بهای تمام‌شده خرید آن‌ها در سیستم ثبت نشده است (صفر محاسبه شده است).
            </div>
        </div>
        <a href="pricing.php" class="fin-action-btn fin-btn-outline" style="background:#FFF; color:#B45309; border-color:#FCD34D;">
            ثبت قیمت خرید در مدیریت قیمت‌ها ←
        </a>
    </section>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- 3. Hero Bento KPI Grid (4 Pillars of Inventory Capital)             -->
    <!-- =================================================================== -->
    <section class="fin-kpi-grid">
        <!-- 1. Total Capital Invested in Stock -->
        <div class="fin-kpi-card accent-blue">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title">سرمایه قفل‌شده در انبار</span>
                <div class="fin-kpi-icon-box" style="background: var(--fin-primary-light); color: var(--fin-primary);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span class="fin-kpi-value" style="color: var(--fin-primary);"><?= formatPrice($report['total_cost_value']) ?></span>
            </div>
            <div class="fin-kpi-footer">
                <span>ارزش بهای تمام‌شده خرید کالاها</span>
            </div>
        </div>

        <!-- 2. Retail Potential Value -->
        <div class="fin-kpi-card accent-emerald">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title">ارزش فروش بالقوه کاتالوگ</span>
                <div class="fin-kpi-icon-box" style="background: var(--fin-emerald-light); color: var(--fin-emerald);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span class="fin-kpi-value" style="color: var(--fin-emerald);"><?= formatPrice($report['total_retail_value']) ?></span>
            </div>
            <div class="fin-kpi-footer">
                <span>گردش ناخالص در صورت فروش ۱۰۰٪ انبار</span>
            </div>
        </div>

        <!-- 3. Unrealized Potential Profit -->
        <div class="fin-kpi-card accent-purple">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title">سود ناخالص بالقوه انبار</span>
                <div class="fin-kpi-icon-box" style="background: var(--fin-purple-light); color: var(--fin-purple);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span class="fin-kpi-value" style="color: var(--fin-purple);"><?= formatPrice($report['potential_profit']) ?></span>
            </div>
            <div class="fin-kpi-footer">
                <span>مارجین انتظاری: <strong><?= toPersianDigits(number_format($report['potential_margin_percent'], 1)) ?>٪</strong></span>
            </div>
        </div>

        <!-- 4. Total Stock Units & Dead Stock Alert -->
        <div class="fin-kpi-card accent-amber">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title">کل موجودی فیزیکی انبار</span>
                <div class="fin-kpi-icon-box" style="background: var(--fin-amber-light); color: var(--fin-amber);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span class="fin-kpi-value"><?= toPersianDigits((string)$report['total_units']) ?></span>
                <span style="font-size: 0.85rem; font-weight: 500; color: var(--fin-text-muted); margin-right: 4px;">جفت / عدد</span>
            </div>
            <div class="fin-kpi-footer" style="color: #B45309;">
                <span>سرمایه در کالای راکد: <strong><?= formatPrice($report['dead_stock_capital']) ?></strong></span>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 4. Category Capital Breakdown & Dead Stock Dual Grid                -->
    <!-- =================================================================== -->
    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 20px; margin-bottom: 24px;">
        
        <!-- Category Distribution Card -->
        <div class="admin-card" style="margin: 0; padding: 0; overflow: hidden; border-radius: var(--fin-radius-md); border: 1px solid var(--fin-border); box-shadow: var(--fin-shadow-sm);">
            <div style="padding: 14px 20px; background: #F8FAFC; border-bottom: 1px solid var(--fin-border); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 700; font-size: 0.9rem; color: var(--fin-text-primary);">توزیع سرمایه بر اساس دسته‌بندی کالاها</span>
                <span style="font-size: 0.78rem; color: var(--fin-text-muted);"><?= toPersianDigits((string)count($catStats)) ?> دسته‌بندی</span>
            </div>

            <div style="overflow-x: auto;">
                <table class="admin-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr style="background: #FFF; border-bottom: 1px solid var(--fin-border);">
                            <th style="padding: 10px 16px; font-size: 0.78rem; color: var(--fin-text-muted);">دسته‌بندی</th>
                            <th style="padding: 10px 14px; font-size: 0.78rem; color: var(--fin-text-muted); text-align: center;">موجودی</th>
                            <th style="padding: 10px 14px; font-size: 0.78rem; color: var(--fin-text-muted);">سرمایه خرید</th>
                            <th style="padding: 10px 14px; font-size: 0.78rem; color: var(--fin-text-muted);">ارزش فروش</th>
                            <th style="padding: 10px 16px; font-size: 0.78rem; color: var(--fin-text-muted); text-align: left;">سهم از انبار</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($catStats as $cat): ?>
                        <tr style="border-bottom: 1px solid var(--fin-border); vertical-align: middle;">
                            <td style="padding: 10px 16px; font-weight: 600; font-size: 0.84rem; color: var(--fin-text-primary);">
                                <?= e($cat['name']) ?>
                                <span style="font-size: 0.72rem; color: var(--fin-text-muted); display: block;">(<?= toPersianDigits((string)$cat['product_count']) ?> کالا)</span>
                            </td>
                            <td style="padding: 10px 14px; text-align: center; font-size: 0.84rem;">
                                <?= toPersianDigits((string)$cat['total_units']) ?>
                            </td>
                            <td style="padding: 10px 14px; font-weight: 700; font-size: 0.85rem; color: var(--fin-primary);">
                                <?= formatPrice($cat['cost_value']) ?>
                            </td>
                            <td style="padding: 10px 14px; font-size: 0.82rem; color: var(--fin-text-secondary);">
                                <?= formatPrice($cat['retail_value']) ?>
                            </td>
                            <td style="padding: 10px 16px; text-align: left; font-size: 0.82rem; font-weight: 700;">
                                <?= toPersianDigits(number_format($cat['share_percent'], 1)) ?>٪
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dead Stock Alert Card -->
        <div class="admin-card" style="margin: 0; padding: 0; overflow: hidden; border-radius: var(--fin-radius-md); border: 1px solid #FDE68A; box-shadow: var(--fin-shadow-sm); background: #FFFDF5;">
            <div style="padding: 14px 20px; background: #FEF3C7; border-bottom: 1px solid #FDE68A; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.1rem;">⚠️</span>
                    <span style="font-weight: 700; font-size: 0.9rem; color: #92400E;">هشدار کالاهای راکد (بدون فروش در ۶۰ روز اخیر)</span>
                </div>
                <span style="font-size: 0.76rem; font-weight: 700; color: #B45309;"><?= toPersianDigits((string)count($deadStock)) ?> قلم کالا</span>
            </div>

            <div style="padding: 12px 18px; font-size: 0.8rem; color: #78350F; line-height: 1.5; border-bottom: 1px dashed #FDE68A;">
                این اقلام دارای موجودی هستند اما در دو ماه اخیر فروشی نداشته‌اند. تخفیف‌گذاری یا باندل کردن این اقلام می‌تواند سرمایه خوابیده را آزاد کند.
            </div>

            <div style="overflow-y: auto; max-height: 380px;">
                <?php if (empty($deadStock)): ?>
                    <div style="padding: 36px 20px; text-align: center; color: #059669;">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom: 6px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <div style="font-weight: 700; font-size: 0.9rem;">تمام کالاهای انبار دارای گردش فروش مطلوب هستند!</div>
                    </div>
                <?php else: ?>
                    <table class="admin-table" style="margin: 0; width: 100%;">
                        <tbody>
                        <?php foreach (array_slice($deadStock, 0, 8) as $ds): ?>
                            <tr style="border-bottom: 1px solid rgba(245, 158, 11, 0.15); vertical-align: middle;">
                                <td style="padding: 10px 14px; width: 44px;">
                                    <img src="<?= e($ds['image_url']) ?>" alt="<?= e($ds['name']) ?>" style="width: 38px; height: 38px; border-radius: 6px; object-fit: cover; border: 1px solid #FCD34D;">
                                </td>
                                <td style="padding: 10px 8px;">
                                    <div style="font-size: 0.84rem; font-weight: 700; color: #78350F; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;">
                                        <?= e($ds['name']) ?>
                                    </div>
                                    <div style="font-size: 0.72rem; color: #B45309;">
                                        موجودی: <strong><?= toPersianDigits((string)$ds['units']) ?></strong> عدد
                                    </div>
                                </td>
                                <td style="padding: 10px 12px; text-align: left; white-space: nowrap;">
                                    <div style="font-size: 0.84rem; font-weight: 700; color: #B45309;">
                                        <?= formatPrice($ds['cost_value']) ?>
                                    </div>
                                    <a href="product_edit.php?id=<?= (int)$ds['id'] ?>" style="font-size: 0.72rem; color: var(--fin-primary); text-decoration: none; font-weight: 600;">
                                        ویرایش یا تخفیف ←
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 5. Top Capital-Concentrated Products Table                          -->
    <!-- =================================================================== -->
    <div class="admin-card" style="padding: 0; overflow: hidden; border-radius: var(--fin-radius-md); border: 1px solid var(--fin-border); box-shadow: var(--fin-shadow-sm);">
        <div style="padding: 14px 20px; background: #F8FAFC; border-bottom: 1px solid var(--fin-border); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span style="font-weight: 700; font-size: 0.92rem; color: var(--fin-text-primary);">تمرکز سرمایه انبار (محصولات با بالاترین ارزش ریالی خوابیده)</span>
                <span style="font-size: 0.78rem; color: var(--fin-text-muted); display: block; margin-top: 2px;">کالاهایی که بیشترین حجم پول نقد شما را در رگال‌ها یا انبار به خود اختصاص داده‌اند</span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table" style="margin: 0; width: 100%;">
                <thead>
                    <tr style="background: #FFF; border-bottom: 1px solid var(--fin-border);">
                        <th style="padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted); width: 60px;">تصویر</th>
                        <th style="padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted);">عنوان کالا</th>
                        <th style="padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted);">دسته‌بندی</th>
                        <th style="padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted); text-align: center;">موجودی</th>
                        <th style="padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted);">سرمایه خوابیده (بهای خرید)</th>
                        <th style="padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted);">ارزش فروش کل</th>
                        <th style="padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted); text-align: center;">مارجین انتظاری</th>
                        <th style="padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted); text-align: center;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($topInvested as $ti): ?>
                    <tr style="border-bottom: 1px solid var(--fin-border); vertical-align: middle;">
                        <td style="padding: 12px 16px;">
                            <img src="<?= e($ti['image_url']) ?>" alt="<?= e($ti['name']) ?>" style="width: 44px; height: 44px; border-radius: 6px; object-fit: cover; border: 1px solid var(--fin-border);">
                        </td>
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: 700; font-size: 0.88rem; color: var(--fin-text-primary);">
                                <?= e($ti['name']) ?>
                            </div>
                            <?php if ($ti['has_missing_cost']): ?>
                                <span style="font-size: 0.72rem; color: var(--fin-rose); font-weight: 600;">(دارای واریانت فاقد بهای خرید)</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px 16px; font-size: 0.82rem; color: var(--fin-text-secondary);">
                            <?= e($ti['category_name']) ?>
                        </td>
                        <td style="padding: 12px 16px; text-align: center; font-size: 0.9rem; font-weight: 700;">
                            <?= toPersianDigits((string)$ti['units']) ?>
                        </td>
                        <td style="padding: 12px 16px; font-size: 0.95rem; font-weight: 800; color: var(--fin-primary);">
                            <?= formatPrice($ti['cost_value']) ?>
                        </td>
                        <td style="padding: 12px 16px; font-size: 0.88rem; color: var(--fin-text-secondary);">
                            <?= formatPrice($ti['retail_value']) ?>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <span class="fin-nature-tag nature-capital">
                                <?= toPersianDigits(number_format($ti['margin_percent'], 1)) ?>٪
                            </span>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <a href="product_edit.php?id=<?= (int)$ti['id'] ?>" class="btn btn-sm btn-outline" style="font-size: 0.78rem; padding: 4px 10px;">
                                مدیریت کالا
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
