<?php
/**
 * Bulk Pricing Workstation View — Display and UI interactions only.
 * High-density desktop dual-pane workbench with instant client-side calculation.
 */
require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="admin-container" style="max-width: 1440px; margin: 0 auto; padding: 20px 24px;">

    <!-- 1. Top KPI Metrics Deck -->
    <section class="pricing-kpi-deck" aria-label="شاخص‌های کلی قیمت‌گذاری کاتالوگ">
        <div class="pricing-kpi-card">
            <div class="pricing-kpi-info">
                <span class="pricing-kpi-label">تعداد کل کالاهای فعال</span>
                <span class="pricing-kpi-val"><?= toPersianDigits((string)$metrics['total_products']) ?></span>
                <span class="pricing-kpi-sub">محصولات کاتالوگ فروشگاه</span>
            </div>
            <div class="pricing-kpi-icon primary">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
        </div>

        <div class="pricing-kpi-card">
            <div class="pricing-kpi-info">
                <span class="pricing-kpi-label">میانگین قیمت فروش</span>
                <span class="pricing-kpi-val"><?= formatPrice($metrics['avg_sale_price']) ?></span>
                <span class="pricing-kpi-sub">نرخ جاری فروش به مشتری</span>
            </div>
            <div class="pricing-kpi-icon purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><line x1="12" y1="6" x2="12" y2="8"/><line x1="12" y1="16" x2="12" y2="18"/></svg>
            </div>
        </div>

        <div class="pricing-kpi-card">
            <div class="pricing-kpi-info">
                <span class="pricing-kpi-label">میانگین بهای تمام‌شده (خرید)</span>
                <span class="pricing-kpi-val"><?= formatPrice($metrics['avg_cost_price']) ?></span>
                <span class="pricing-kpi-sub">کالاهای دارای قیمت خرید</span>
            </div>
            <div class="pricing-kpi-icon warning">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
        </div>

        <div class="pricing-kpi-card">
            <div class="pricing-kpi-info">
                <span class="pricing-kpi-label">حاشیه سود میانگین کاتالوگ</span>
                <span class="pricing-kpi-val" style="color: <?= $metrics['avg_margin_percentage'] > 0 ? 'var(--color-success, #059669)' : 'var(--color-danger, #dc2626)' ?>;">
                    <?= toPersianDigits((string)$metrics['avg_margin_percentage']) ?>٪
                </span>
                <span class="pricing-kpi-sub">
                    <?= toPersianDigits((string)$metrics['missing_cost_count']) ?> کالا فاقد قیمت خرید
                </span>
            </div>
            <div class="pricing-kpi-icon success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
        </div>
    </section>

    <!-- 2. Dual-Pane Workbench Grid -->
    <div class="pricing-workstation">

        <!-- Main Left Column: Filter Bar, Presets, and Products Matrix Table -->
        <div class="pricing-matrix-card">

            <!-- Filter Toolbar -->
            <form method="get" action="pricing.php" class="pricing-toolbar">
                <div class="pricing-filters-left">
                    <select name="category_id" id="pricingCategoryFilter" class="pricing-filter-select" onchange="this.form.submit()">
                        <option value="">همه دسته‌بندی‌ها</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ($filters['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= str_repeat('— ', (int)($cat['depth'] ?? 0)) . e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="cost_status" class="pricing-filter-select" onchange="this.form.submit()">
                        <option value="">همه وضعیت‌های بهای خرید</option>
                        <option value="has_cost" <?= ($filters['cost_status'] === 'has_cost') ? 'selected' : '' ?>>دارای بهای تمام‌شده</option>
                        <option value="missing" <?= ($filters['cost_status'] === 'missing') ? 'selected' : '' ?>>فاقد بهای تمام‌شده</option>
                    </select>

                    <input type="text" name="q" value="<?= e($filters['search']) ?>" class="pricing-search-input" placeholder="جستجوی نام یا SKU کالا...">
                    <button type="submit" class="btn btn-outline" style="padding: 7px 14px;">فیلتر</button>
                    <?php if ($filters['search'] !== '' || !empty($filters['category_id']) || $filters['cost_status'] !== ''): ?>
                        <a href="pricing.php" class="btn btn-sm btn-outline" style="padding: 6px 10px; display: inline-flex; align-items: center;">حذف فیلترها</a>
                    <?php endif; ?>
                </div>

                <div class="pricing-counter-badge" id="selectionCounterBadge">
                    ۰ محصول انتخاب‌شده
                </div>
            </form>

            <!-- Selection Presets Deck -->
            <div class="pricing-presets-deck">
                <div class="pricing-preset-chips">
                    <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-left: 4px;">انتخاب سریع:</span>
                    <button type="button" class="pricing-preset-btn" id="presetSelectAll">انتخاب تمام کاتالوگ جاری (<?= toPersianDigits((string)count($products)) ?>)</button>
                    <button type="button" class="pricing-preset-btn" id="presetSelectNoCost">کالاهای بدون بهای خرید</button>
                    <button type="button" class="pricing-preset-btn" id="presetSelectCategory">کالاهای دسته انتخابی</button>
                    <button type="button" class="pricing-preset-btn" id="presetClearSelection">پاکسازی انتخاب‌ها</button>
                </div>
                <div style="font-size: 0.78rem; color: #94a3b8;">
                    تغییرات به صورت آنی در ستون «قیمت جدید پیشنهادی» محاسبه می‌شوند.
                </div>
            </div>

            <!-- Products Matrix Table -->
            <div class="pricing-table-container">
                <table class="pricing-table" id="pricingProductsTable">
                    <thead>
                        <tr>
                            <th style="width: 44px; text-align: center;">
                                <input type="checkbox" id="pricingMasterCheckbox" style="cursor: pointer; width: 16px; height: 16px;">
                            </th>
                            <th>محصول و کد شناسایی</th>
                            <th style="width: 140px;">دسته‌بندی</th>
                            <th style="width: 130px;">قیمت فروش فعلی</th>
                            <th style="width: 130px;">بهای تمام‌شده</th>
                            <th style="width: 150px;">قیمت جدید پیشنهادی</th>
                            <th style="width: 150px;">تغییر و وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($products as $p):
                        $costVal = $p['cost_price'] !== null ? (int)$p['cost_price'] : 0;
                        $imgUrl = !empty($p['image']) ? UPLOAD_URL . e($p['image']) : '';
                    ?>
                        <tr class="pricing-item-row"
                            data-id="<?= (int)$p['id'] ?>"
                            data-price="<?= (int)$p['price'] ?>"
                            data-cost="<?= $costVal ?>"
                            data-category="<?= (int)($p['category_id'] ?? 0) ?>">
                            <td style="text-align: center;">
                                <input type="checkbox" class="pricing-row-check" value="<?= (int)$p['id'] ?>" style="cursor: pointer; width: 16px; height: 16px;">
                            </td>
                            <td>
                                <div class="pricing-prod-cell">
                                    <?php if ($imgUrl): ?>
                                        <img src="<?= $imgUrl ?>" alt="<?= e($p['name']) ?>" class="pricing-thumb" loading="lazy">
                                    <?php else: ?>
                                        <div class="pricing-thumb-fallback">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        </div>
                                    <?php endif; ?>
                                    <div class="pricing-prod-info">
                                        <span class="pricing-prod-name" title="<?= e($p['name']) ?>"><?= e($p['name']) ?></span>
                                        <span class="pricing-prod-sku"><?= e($p['sku'] ?: 'بدون SKU') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="color: var(--color-muted); font-size: 0.84rem;">
                                <?= e($p['category_name'] ?: '—') ?>
                            </td>
                            <td>
                                <span class="pricing-cur-num"><?= formatPrice((int)$p['price']) ?></span>
                            </td>
                            <td>
                                <?php if ($costVal > 0): ?>
                                    <span class="pricing-cur-num"><?= formatPrice($costVal) ?></span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 0.8rem;">ثبت‌نشده</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: baseline; gap: 4px;">
                                    <span class="pricing-prop-num"><?= number_format((int)$p['price']) ?></span>
                                    <span style="font-size: 0.78rem; color: #94a3b8;">تومان</span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <span class="pricing-delta-tag neutral">۰</span>
                                    <div class="pricing-row-alert"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px 20px; color: var(--color-muted);">
                                هیچ محصولی با شرایط جستجو و فیلترهای انتخابی یافت نشد.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Right Column: Sticky Formula Workbench -->
        <aside class="pricing-sidebar-card">
            <div class="pricing-sidebar-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <h3>موتور فرمول‌ساز قیمت</h3>
            </div>

            <div class="pricing-sidebar-body">

                <!-- 1. Target Selector -->
                <div>
                    <label class="pricing-field-label">قیمت هدف جهت تغییر</label>
                    <div class="pricing-segmented-control">
                        <button type="button" class="pricing-seg-btn active" data-target="sale_price">قیمت فروش به مشتری</button>
                        <button type="button" class="pricing-seg-btn" data-target="cost_price">بهای تمام‌شده (خرید)</button>
                    </div>
                </div>

                <!-- 2. Method Selector -->
                <div>
                    <label class="pricing-field-label">روش محاسبه فرمول</label>
                    <div class="pricing-method-grid">
                        <button type="button" class="pricing-method-btn active" data-method="percentage">درصدی (٪)</button>
                        <button type="button" class="pricing-method-btn" data-method="fixed_amount">مبلغ ثابت (+/-)</button>
                        <button type="button" class="pricing-method-btn" data-method="direct_value">نرخ مستقیم (=)</button>
                    </div>
                </div>

                <!-- 3. Value Input & Quick Chips -->
                <div>
                    <div class="pricing-field-label">
                        <span>مقدار عددی تغییر</span>
                        <span style="font-size: 0.76rem; font-weight: normal; color: #64748b;">(اعداد منفی نیز مجاز است)</span>
                    </div>
                    <div class="pricing-input-wrap">
                        <input type="text" id="pricingValueInput" inputmode="decimal" placeholder="مثلاً: 15 یا 10-" value="10">
                        <span class="pricing-input-unit" id="pricingUnitLabel">٪</span>
                    </div>

                    <div class="pricing-quick-chips">
                        <span class="pricing-chip" data-val="+5%">+۵٪</span>
                        <span class="pricing-chip" data-val="+10%">+۱۰٪</span>
                        <span class="pricing-chip" data-val="+15%">+۱۵٪</span>
                        <span class="pricing-chip" data-val="+20%">+۲۰٪</span>
                        <span class="pricing-chip" data-val="+25%">+۲۵٪</span>
                        <span class="pricing-chip" data-val="-10%">-۱۰٪</span>
                    </div>
                </div>

                <!-- 4. Smart Rounding Row -->
                <div>
                    <label class="pricing-field-label">گرد کردن هوشمند (روانشناسی قیمت)</label>
                    <div class="pricing-rounding-row">
                        <select id="pricingRoundingSelect">
                            <option value="0">بدون گرد کردن (محاسبه دقیق)</option>
                            <option value="1000">رند به نزدیک‌ترین ۱,۰۰۰ تومان</option>
                            <option value="2000">رند به نزدیک‌ترین ۲,۰۰۰ تومان</option>
                            <option value="5000">رند به نزدیک‌ترین ۵,۰۰۰ تومان</option>
                            <option value="10000">رند به نزدیک‌ترین ۱۰,۰۰۰ تومان</option>
                            <option value="custom">گام سفارشی (دستی)...</option>
                        </select>
                        <div class="pricing-custom-rounding-wrap" id="pricingCustomRoundingWrap">
                            <input type="text" id="pricingCustomRoundingInput" inputmode="numeric" placeholder="مبلغ گام">
                        </div>
                    </div>
                </div>

                <!-- 5. Variant Sync Checkbox -->
                <label class="pricing-checkbox-row">
                    <input type="checkbox" id="pricingApplyVariants" checked>
                    <span>اعمال همزمان روی قیمت تنوع‌ها (سایز و رنگ)</span>
                </label>

                <!-- 6. Reason Input -->
                <div>
                    <label class="pricing-field-label">دلیل ثبت در تاریخچه حسابداری</label>
                    <input type="text" id="pricingReasonInput" class="form-control" style="font-size: 0.85rem;" placeholder="مثلاً: نوسان قیمت تولیدکننده یا تخفیف پایان فصل">
                </div>

                <!-- 7. Live Financial Impact Summary Box -->
                <div class="pricing-impact-box">
                    <div class="pricing-impact-row">
                        <span class="impact-lbl">کالاهای تحت تاثیر:</span>
                        <span class="impact-val" id="impactSelectedCount">۰ کالا</span>
                    </div>
                    <div class="pricing-impact-row">
                        <span class="impact-lbl">میانگین تغییر هر محصول:</span>
                        <span class="impact-val" id="impactAvgDelta">۰ تومان</span>
                    </div>

                    <div class="pricing-impact-alert" id="impactAlertBox">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span>تعداد <strong id="impactAlertCount">۰</strong> کالا با حاشیه سود منفی (زیر قیمت خرید) مواجه می‌شوند!</span>
                    </div>
                </div>

                <!-- 8. Execute Action Button -->
                <button type="button" id="openPricingModalBtn" class="pricing-apply-btn" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    مشاهده و تایید نهایی تغییرات
                </button>

            </div>
        </aside>

    </div>

    <!-- 3. Audit Log: Recent Bulk Operations -->
    <?php if (!empty($recentOps)): ?>
    <section class="pricing-audit-card" aria-label="لاگ عملیات‌های قبلی تغییر قیمت گروهی">
        <h3 class="pricing-audit-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            تاریخچه آخرین عملیات‌های گروهی قیمت
        </h3>
        <div style="overflow-x: auto;">
            <table class="admin-table" style="margin: 0; width: 100%;">
                <thead>
                    <tr style="background: var(--color-bg-subtle, #f8fafc); border-bottom: 1px solid var(--color-border, #e5e7eb);">
                        <th style="width: 70px;">شناسه</th>
                        <th style="width: 140px;">تاریخ و ساعت</th>
                        <th style="width: 140px;">فیلد تغییریافته</th>
                        <th>فرمول و تنظیمات ثبت‌شده</th>
                        <th>دلیل حسابداری</th>
                        <th style="width: 110px; text-align: center;">تعداد اقلام</th>
                        <th style="width: 110px;">مدیر ثبت‌کننده</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentOps as $op): ?>
                    <tr style="border-bottom: 1px solid var(--color-border, #f1f5f9);">
                        <td style="font-weight: 700; color: #64748b;">#<?= (int)$op['id'] ?></td>
                        <td style="font-size: 0.82rem; color: var(--color-muted);"><?= toPersianDigits(date('Y/m/d H:i', strtotime($op['created_at']))) ?></td>
                        <td>
                            <span class="status-pill <?= $op['field_changed'] === 'cost_price' ? 'status-pending' : 'status-shipped' ?>" style="font-size: 0.78rem;">
                                <?= $op['field_changed'] === 'cost_price' ? 'بهای خرید' : 'قیمت فروش' ?>
                            </span>
                        </td>
                        <td dir="ltr" style="font-family: monospace; font-size: 0.85rem; font-weight: 700; color: var(--color-text);">
                            <?= e($op['requested_change']) ?>
                        </td>
                        <td style="font-size: 0.84rem; color: #475569;"><?= e($op['reason'] ?: '—') ?></td>
                        <td style="text-align: center;">
                            <span class="status-pill status-delivered" style="font-size: 0.78rem;">
                                <?= toPersianDigits((string)$op['product_count']) ?> کالا
                            </span>
                        </td>
                        <td style="font-size: 0.84rem;"><?= e($op['admin_username'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

</div>

<!-- 4. Hidden Form for Safe POST Submission -->
<form id="bulkPricingForm" method="post" action="pricing.php" style="display: none;">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="apply">
    <input type="hidden" name="field" id="formField" value="sale_price">
    <input type="hidden" name="method" id="formMethod" value="percentage">
    <input type="hidden" name="value" id="formValue" value="0">
    <input type="hidden" name="rounding_step" id="formRoundingStep" value="0">
    <input type="hidden" name="apply_to_variants" id="formApplyVariants" value="1">
    <input type="hidden" name="allow_negative_margin" id="formAllowNegative" value="0">
    <input type="hidden" name="reason" id="formReason" value="">
    <div id="hiddenProductIdsContainer"></div>
</form>

<!-- 5. Confirmation & Safety Guard Modal -->
<div class="pricing-modal-backdrop" id="pricingModalBackdrop">
    <div class="pricing-modal" role="dialog" aria-modal="true" aria-labelledby="pricingModalTitle">
        <div class="pricing-modal-head">
            <h3 id="pricingModalTitle">تایید نهایی تغییر گروهی قیمت‌ها</h3>
            <button type="button" class="pricing-modal-close" id="pricingModalCloseBtn" aria-label="بستن پنجره">&times;</button>
        </div>

        <div class="pricing-modal-body">
            <p style="margin: 0; font-size: 0.9rem; color: #475569; line-height: 1.5;">
                شما در حال اعمال تغییر قیمت بر روی محصولات انتخاب‌شده هستید. اطلاعات فرمول و خلاصه اثر مالی را بازبینی نمایید:
            </p>

            <div class="pricing-modal-stat-grid">
                <div class="pricing-modal-stat">
                    <span class="m-lbl">تعداد اقلام انتخابی</span>
                    <span class="m-val" id="modalStatCount">۰ محصول</span>
                </div>
                <div class="pricing-modal-stat">
                    <span class="m-lbl">فیلد هدف</span>
                    <span class="m-val" id="modalStatTarget">قیمت فروش</span>
                </div>
                <div class="pricing-modal-stat" style="grid-column: span 2;">
                    <span class="m-lbl">فرمول و گام گرد کردن</span>
                    <span class="m-val" id="modalStatFormula" style="font-size: 1.05rem; color: var(--color-primary);">+۱۰٪</span>
                </div>
            </div>

            <!-- Danger Alert Callout (Shows ONLY if negative margin items exist) -->
            <div class="pricing-modal-danger-box" id="modalDangerBox">
                <div class="pricing-modal-danger-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    هشدار ایمنی: خطر ضرردهی (حاشیه سود منفی)
                </div>
                <p class="pricing-modal-danger-text">
                    توجه: تعداد <strong id="modalDangerCount">۰</strong> کالا پس از این تغییر با قیمتی کمتر از بهای تمام‌شده (خرید) ذخیره خواهند شد. فروش زیر قیمت خرید مستلزم تایید صریح مدیر است.
                </p>
                <label class="pricing-modal-danger-check">
                    <input type="checkbox" id="modalAllowNegativeCheck" style="width: 17px; height: 17px; cursor: pointer;">
                    <span>تایید می‌کنم که از فروش زیر قیمت تمام‌شده آگاه بوده و تمایل به ذخیره‌سازی دارم.</span>
                </label>
            </div>
        </div>

        <div class="pricing-modal-foot">
            <button type="button" class="btn btn-outline" id="pricingModalCancelBtn" style="padding: 9px 18px;">انصراف</button>
            <button type="button" class="btn btn-primary" id="pricingModalConfirmBtn" style="padding: 9px 24px; font-weight: 700;">تأیید و اجرای تراکنش</button>
        </div>
    </div>
</div>

<script src="/assets/js/admin-pricing.js?v=<?= APP_VERSION . '.' . (@filemtime(APP_ROOT . '/assets/js/admin-pricing.js') ?: 1) ?>"></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
