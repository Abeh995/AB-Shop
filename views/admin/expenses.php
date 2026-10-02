<?php
/**
 * Modern High-Density Admin Expenses Ledger & Workstation
 * Part of AB-Socks Financial Hub. Bento KPI Grid, Visual Category Distribution, and Invoice Attachments.
 */
require APP_ROOT . '/views/admin/layout/header.php';

$activeCount = (int) ($expensesData['active_count'] ?? 0);
$archivedCount = (int) ($expensesData['archived_count'] ?? 0);
$catBreakdown = $summaryMetrics['category_breakdown'] ?? [];
$natureBreakdown = $summaryMetrics['nature_breakdown'] ?? [];

// Color palette for the multi-segment category bar
$palette = ['#3B82F6', '#8B5CF6', '#10B981', '#F59E0B', '#EC4899', '#06B6D4', '#6366F1', '#84CC16', '#64748B'];

$baseQueryParams = [
    'status'         => $status,
    'category'       => $category,
    'payment_source' => $paymentSource,
    'expense_nature' => $expenseNature,
    'q'              => $search,
    'sort'           => $sort,
    'start_date'     => $startDate,
    'end_date'       => $endDate,
    'range'          => $range,
];
$returnUrl = 'expenses.php?' . http_build_query($_GET);
$natureBadgeClasses = [
    'variable' => 'nature-variable',
    'fixed'    => 'nature-fixed',
    'capital'  => 'nature-capital',
];
$natureLabels = [
    'variable' => 'متغیر',
    'fixed'    => 'ثابت',
    'capital'  => 'سرمایه‌ای',
];

$activeFiltersCount = 0;
if ($search !== '') $activeFiltersCount++;
if ($category !== '') $activeFiltersCount++;
if ($paymentSource !== '') $activeFiltersCount++;
if ($expenseNature !== '') $activeFiltersCount++;
if ($startDate !== '' || $endDate !== '') $activeFiltersCount++;
if ($sort !== 'date_desc') $activeFiltersCount++;
?>

<main class="fin-workspace">

    <!-- =================================================================== -->
    <!-- 1. Top Header, Command Actions & Status Tabs                        -->
    <!-- =================================================================== -->
    <div class="fin-page-header-row" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div class="fin-header-title-group">
            <h2 style="margin: 0 0 6px 0; font-size: 1.35rem; font-weight: 800; color: var(--fin-text-primary); display: flex; align-items: center; gap: 8px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                دفتر هزینه‌های عملیاتی
            </h2>
            <div class="fin-header-subtitle" style="font-size: 0.85rem; color: var(--fin-text-muted);">
                ثبت دقیق مخارج، تفکیک سرفصل‌ها، مدیریت فاکتورها، و مغایرت‌گیری تراز مالی فروشگاه
            </div>
        </div>

        <div class="fin-header-actions" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Active / Archived Filter Tabs -->
            <div class="fin-tab-nav">
                <?php 
                $tabActiveParams = array_merge($baseQueryParams, ['status' => 'active', 'page' => 1]);
                $tabArchivedParams = array_merge($baseQueryParams, ['status' => 'archived', 'page' => 1]);
                ?>
                <a href="expenses.php?<?= http_build_query($tabActiveParams) ?>" class="fin-tab-btn <?= ($status === 'active') ? 'active' : '' ?>">
                    اسناد فعال (<?= toPersianDigits((string)$activeCount) ?>)
                </a>
                <a href="expenses.php?<?= http_build_query($tabArchivedParams) ?>" class="fin-tab-btn <?= ($status === 'archived') ? 'active' : '' ?>">
                    بایگانی‌شده (<?= toPersianDigits((string)$archivedCount) ?>)
                </a>
            </div>

            <!-- CSV Export Button -->
            <a href="expenses.php?<?= http_build_query(array_merge($baseQueryParams, ['export' => 'csv'])) ?>" class="fin-action-btn fin-btn-outline fin-export-btn" title="دریافت خروجی اکسل و CSV از هزینه‌های فیلترشده">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>خروجی اکسل</span>
            </a>

            <!-- New Expense Button -->
            <a href="expense_edit.php" class="fin-action-btn fin-btn-primary fin-new-expense-btn" style="font-weight: 700;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>ثبت سند جدید</span>
            </a>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 2. Bento KPI Grid (4 Pillars of Operating Expenses)                 -->
    <!-- =================================================================== -->
    <section class="fin-kpi-grid">
        <!-- 1. Filtered Period Total -->
        <div class="fin-kpi-card accent-blue">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title">مجموع مخارج دوره</span>
                <div class="fin-kpi-icon-box" style="background: var(--fin-primary-light); color: var(--fin-primary);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span class="fin-kpi-value" style="color: var(--fin-primary);"><?= formatPrice($summaryMetrics['total_amount']) ?></span>
            </div>
            <div class="fin-kpi-footer" style="font-size: 0.76rem; color: var(--fin-text-muted);">
                <span>متغیر: <strong><?= formatPrice($natureBreakdown['variable'] ?? 0) ?></strong></span>
                <span>•</span>
                <span>ثابت: <strong><?= formatPrice($natureBreakdown['fixed'] ?? 0) ?></strong></span>
            </div>
        </div>

        <!-- 2. Filtered Count -->
        <div class="fin-kpi-card accent-emerald">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title">تعداد اسناد فیلترشده</span>
                <div class="fin-kpi-icon-box" style="background: var(--fin-emerald-light); color: var(--fin-emerald);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span class="fin-kpi-value"><?= toPersianDigits((string)$summaryMetrics['total_count']) ?></span>
                <span style="font-size: 0.85rem; font-weight: 500; color: var(--fin-text-muted); margin-right: 4px;">سند</span>
            </div>
            <div class="fin-kpi-footer">
                <span>وضعیت: <strong><?= ($status === 'archived') ? 'بایگانی‌شده' : 'فعال و معتبر' ?></strong></span>
            </div>
        </div>

        <!-- 3. Average Expense Value -->
        <div class="fin-kpi-card accent-purple">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title">میانگین هر سند هزینه</span>
                <div class="fin-kpi-icon-box" style="background: var(--fin-purple-light); color: var(--fin-purple);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span class="fin-kpi-value"><?= formatPrice($summaryMetrics['avg_amount']) ?></span>
            </div>
            <div class="fin-kpi-footer">
                <span>ارزش میانگین هر بار خرج‌کرد</span>
            </div>
        </div>

        <!-- 4. Top Cost Center -->
        <div class="fin-kpi-card accent-amber">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title">بزرگترین سرفصل مخارج</span>
                <div class="fin-kpi-icon-box" style="background: var(--fin-amber-light); color: var(--fin-amber);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span style="font-size: 1.05rem; font-weight: 700; color: #B45309; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">
                    <?= e($summaryMetrics['top_category']) ?>
                </span>
            </div>
            <div class="fin-kpi-footer">
                <span><strong><?= formatPrice($summaryMetrics['top_category_amount']) ?></strong></span>
                <span>•</span>
                <span style="color: var(--fin-primary); font-weight: 700;"><?= toPersianDigits(number_format($summaryMetrics['top_category_share'], 1)) ?>٪ از کل</span>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 3. Category Distribution Stacked Bar (Visual Cost Breakdown)        -->
    <!-- =================================================================== -->
    <?php if (!empty($catBreakdown)): ?>
    <section class="fin-dist-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--fin-text-primary);">توزیع درصدی هزینه‌ها بر اساس سرفصل:</span>
            <span style="font-size: 0.78rem; color: var(--fin-text-muted);"><?= toPersianDigits((string)count($catBreakdown)) ?> دسته‌بندی فعال</span>
        </div>

        <!-- Stacked Progress Bar -->
        <div class="fin-dist-bar">
            <?php foreach ($catBreakdown as $idx => $cat): 
                $color = $palette[$idx % count($palette)];
            ?>
                <div class="fin-dist-segment" style="width: <?= $cat['share_percent'] ?>%; background: <?= $color ?>;" title="<?= e($cat['category']) ?>: <?= toPersianDigits(number_format($cat['share_percent'], 1)) ?>٪ (<?= formatPrice($cat['amount']) ?>)"></div>
            <?php endforeach; ?>
        </div>

        <!-- Legend Chips -->
        <div class="fin-dist-legend">
            <?php foreach ($catBreakdown as $idx => $cat): 
                $color = $palette[$idx % count($palette)];
            ?>
                <div class="fin-dist-legend-item">
                    <span class="fin-dist-dot" style="background: <?= $color ?>;"></span>
                    <span><?= e($cat['category']) ?>:</span>
                    <strong><?= formatPrice($cat['amount']) ?></strong>
                    <span style="color: var(--fin-text-muted); font-size: 0.72rem;">(<?= toPersianDigits(number_format($cat['share_percent'], 1)) ?>٪)</span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- 4. Date Range Toolbar & Filter Form                                 -->
    <!-- =================================================================== -->
    <section class="fin-toolbar-card" style="margin-bottom: 16px;">
        <!-- Quick Preset Chips -->
        <div class="fin-presets-group">
            <span class="fin-preset-label">بازه سریع:</span>
            <div class="fin-presets-scroll">
                <?php 
                $presets = [
                    ''           => 'همه تاریخ‌ها',
                    'today'      => 'امروز',
                    '7days'      => '۷ روز',
                    '30days'     => '۳۰ روز',
                    'this_month' => 'ماه جاری',
                    'last_month' => 'ماه قبل',
                    'this_year'  => 'امسال',
                ];
                foreach ($presets as $pKey => $pLabel): 
                    $pParams = array_merge($baseQueryParams, ['range' => $pKey, 'start_date' => '', 'end_date' => '', 'page' => 1]);
                    $isActive = ($range === $pKey);
                ?>
                    <a href="expenses.php?<?= http_build_query($pParams) ?>" class="fin-chip <?= $isActive ? 'active' : '' ?>">
                        <?= $pLabel ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Custom Date Range & Dropdown Filters Form -->
        <form method="get" action="expenses.php" class="fin-filters-form" id="finFiltersForm">
            <input type="hidden" name="status" value="<?= e($status) ?>">

            <!-- Search Bar & Mobile Filter Drawer Toggle -->
            <div class="fin-filter-primary-row">
                <div class="fin-filter-search-box">
                    <input class="fin-filter-input" type="text" name="q" value="<?= e($search) ?>" placeholder="جستجوی عنوان، طرف حساب یا یادداشت...">
                </div>
                <button type="button" class="fin-filter-toggle-btn" onclick="toggleMobileFilters()" title="فیلترهای پیشرفته">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>فیلترها</span>
                    <?php if ($activeFiltersCount > 0): ?>
                        <span class="fin-filter-badge"><?= toPersianDigits((string)$activeFiltersCount) ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Filter Controls Group (Inline on Desktop, Collapsible Drawer on Mobile) -->
            <div class="fin-filter-drawer <?= ($activeFiltersCount > 0) ? 'is-open' : '' ?>" id="finFilterDrawer">
                <!-- Category Filter -->
                <div class="fin-filter-field fin-filter-field-cat">
                    <select class="fin-filter-select" name="category">
                        <option value="">همه سرفصل‌ها</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= e($c) ?>" <?= ($category === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Payment Source Filter -->
                <div class="fin-filter-field fin-filter-field-source">
                    <select class="fin-filter-select" name="payment_source">
                        <option value="">همه منابع پرداخت</option>
                        <?php foreach ($paymentSources as $ps): ?>
                            <option value="<?= e($ps) ?>" <?= ($paymentSource === $ps) ? 'selected' : '' ?>><?= e($ps) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Expense Nature Filter -->
                <div class="fin-filter-field fin-filter-field-nature">
                    <select class="fin-filter-select" name="expense_nature">
                        <option value="">همه ماهیت‌ها</option>
                        <option value="variable" <?= ($expenseNature === 'variable') ? 'selected' : '' ?>>متغیر عملیاتی</option>
                        <option value="fixed" <?= ($expenseNature === 'fixed') ? 'selected' : '' ?>>ثابت بالاسری</option>
                        <option value="capital" <?= ($expenseNature === 'capital') ? 'selected' : '' ?>>سرمایه‌ای / تجهیزات</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div class="fin-filter-field fin-filter-field-sort">
                    <select class="fin-filter-select" name="sort">
                        <option value="date_desc" <?= ($sort === 'date_desc') ? 'selected' : '' ?>>جدیدترین تاریخ</option>
                        <option value="date_asc" <?= ($sort === 'date_asc') ? 'selected' : '' ?>>قدیمی‌ترین تاریخ</option>
                        <option value="amount_desc" <?= ($sort === 'amount_desc') ? 'selected' : '' ?>>بیشترین مبلغ</option>
                        <option value="amount_asc" <?= ($sort === 'amount_asc') ? 'selected' : '' ?>>کمترین مبلغ</option>
                    </select>
                </div>

                <!-- Date Fields -->
                <div class="fin-filter-date-group">
                    <div class="fin-filter-date-item">
                        <span class="fin-filter-date-label">از:</span>
                        <input class="fin-filter-date" type="date" name="start_date" value="<?= e($startDate) ?>" style="width: 130px;">
                    </div>
                    <div class="fin-filter-date-item">
                        <span class="fin-filter-date-label">تا:</span>
                        <input class="fin-filter-date" type="date" name="end_date" value="<?= e($endDate) ?>" style="width: 130px;">
                    </div>
                </div>

                <!-- Actions -->
                <div class="fin-filter-actions">
                    <button type="submit" class="fin-action-btn fin-btn-primary" style="height: 38px; padding: 0 16px;">اعمال فیلتر</button>
                    <?php if ($activeFiltersCount > 0 || $range !== ''): ?>
                        <a href="expenses.php?status=<?= e($status) ?>" class="fin-action-btn fin-btn-outline" style="height: 38px;" title="حذف تمام فیلترها">✕</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </section>

    <!-- =================================================================== -->
    <!-- 5. High-Density Expenses Ledger Table Card (Desktop View)           -->
    <!-- =================================================================== -->
    <div class="admin-card fin-table-desktop" style="padding: 0; overflow: hidden; border-radius: var(--fin-radius-md); box-shadow: var(--fin-shadow-sm); border: 1px solid var(--fin-border);">
        <div style="overflow-x: auto;">
            <table class="admin-table" style="margin: 0; width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #F8FAFC; border-bottom: 1px solid var(--fin-border);">
                        <th style="width: 105px; text-align: right; padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted);">تاریخ سند</th>
                        <th style="text-align: right; padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted);">شرح هزینه و طرف حساب</th>
                        <th style="width: 180px; text-align: right; padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted);">سرفصل و ماهیت</th>
                        <th style="width: 140px; text-align: right; padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted);">منبع پرداخت</th>
                        <th style="width: 130px; text-align: right; padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted);">مبلغ (تومان)</th>
                        <th style="width: 60px; text-align: center; padding: 12px 8px; font-size: 0.8rem; color: var(--fin-text-muted);">فاکتور</th>
                        <th style="width: 100px; text-align: right; padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted);">ثبت‌کننده</th>
                        <th style="width: 110px; text-align: center; padding: 12px 16px; font-size: 0.8rem; color: var(--fin-text-muted);">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                foreach ($expensesData['items'] as $ex): 
                    $natClass = $natureBadgeClasses[$ex['expense_nature'] ?? 'variable'] ?? 'nature-variable';
                    $natText = $natureLabels[$ex['expense_nature'] ?? 'variable'] ?? 'متغیر';
                    $hasReceipt = !empty($ex['receipt_image']);
                    $receiptUrl = $hasReceipt ? (EXPENSE_UPLOAD_URL . e($ex['receipt_image'])) : '';
                ?>
                <tr style="border-bottom: 1px solid var(--fin-border); vertical-align: middle; transition: background 0.15s ease;">
                    <!-- Date -->
                    <td style="padding: 12px 16px; white-space: nowrap;">
                        <div style="font-weight: 600; font-size: 0.85rem; color: var(--fin-text-primary);">
                            <?= toPersianDigits(appDateTime($ex['expense_date'], 'shamsi_date')) ?>
                        </div>
                        <div style="font-size: 0.72rem; color: var(--fin-text-muted);">
                            <?= toPersianDigits(date('Y/m/d', strtotime($ex['expense_date']))) ?>
                        </div>
                    </td>

                    <!-- Title & Payee -->
                    <td style="padding: 12px 16px;">
                        <div style="font-weight: 700; font-size: 0.9rem; color: var(--fin-text-primary); margin-bottom: 2px;">
                            <?= e($ex['title']) ?>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <?php if (!empty($ex['payee'])): ?>
                                <span style="font-size: 0.78rem; color: #475569; display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    طرف‌حساب: <strong><?= e($ex['payee']) ?></strong>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($ex['description'])): ?>
                                <span style="font-size: 0.76rem; color: var(--fin-text-muted);" title="<?= e($ex['description']) ?>">
                                    <?= e(mb_strimwidth($ex['description'], 0, 45, '...')) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Category & Nature Tag -->
                    <td style="padding: 12px 16px;">
                        <div style="font-size: 0.82rem; font-weight: 600; color: var(--fin-text-primary); margin-bottom: 4px;">
                            <?= e($ex['category']) ?>
                        </div>
                        <span class="fin-nature-tag <?= $natClass ?>">
                            <?= $natText ?>
                        </span>
                    </td>

                    <!-- Payment Source -->
                    <td style="padding: 12px 16px;">
                        <span class="fin-source-pill" title="منبع تأمین هزینه">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            <?= e($ex['payment_source'] ?? 'کارت اصلی') ?>
                        </span>
                    </td>

                    <!-- Amount -->
                    <td style="padding: 12px 16px; white-space: nowrap;">
                        <strong style="font-size: 0.95rem; color: var(--fin-text-primary);">
                            <?= formatPrice((int)$ex['amount']) ?>
                        </strong>
                    </td>

                    <!-- Receipt Thumbnail / Trigger -->
                    <td style="padding: 12px 8px; text-align: center;">
                        <?php if ($hasReceipt): ?>
                            <img src="<?= $receiptUrl ?>" alt="رسید فاکتور" class="fin-receipt-thumb" onclick="openReceiptModal('<?= $receiptUrl ?>', '<?= e(addslashes($ex['title'])) ?>')" title="مشاهده تصویر فاکتور">
                        <?php else: ?>
                            <span style="font-size: 0.72rem; color: #CBD5E1;">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Created By -->
                    <td style="padding: 12px 16px; font-size: 0.82rem; color: var(--fin-text-secondary);">
                        <?= e($ex['admin_username'] ?? 'مدیر') ?>
                    </td>

                    <!-- Actions -->
                    <td style="padding: 12px 16px; text-align: center;">
                        <div class="admin-actions" style="justify-content: center; gap: 4px;">
                            <a href="expense_edit.php?id=<?= (int)$ex['id'] ?>" class="btn btn-sm btn-outline" style="padding: 4px 8px;" title="ویرایش سند">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>

                            <?php if ($ex['status'] === 'active'): ?>
                                <form method="post" action="expenses.php" onsubmit="return confirm('آیا از انتقال این سند به بایگانی اطمینان دارید؟');" style="display:inline;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="archive">
                                    <input type="hidden" name="id" value="<?= (int)$ex['id'] ?>">
                                    <input type="hidden" name="return_url" value="<?= e($returnUrl) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline" style="padding: 4px 8px; color: #DC2626;" title="بایگانی سند">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="expenses.php" onsubmit="return confirm('آیا می‌خواهید این سند هزینه را مجدداً فعال کنید؟');" style="display:inline;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="restore">
                                    <input type="hidden" name="id" value="<?= (int)$ex['id'] ?>">
                                    <input type="hidden" name="return_url" value="<?= e($returnUrl) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline" style="padding: 4px 8px; color: #059669;" title="بازیابی و فعال‌سازی">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($expensesData['items'])): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 48px 20px; color: var(--fin-text-muted);">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 8px; opacity: 0.4;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <div style="font-size: 0.95rem; font-weight: 600;">هیچ سند هزینه‌ای با مشخصات انتخابی یافت نشد.</div>
                            <div style="font-size: 0.8rem; margin-top: 4px;">فیلترها را تغییر دهید یا سند جدید ثبت کنید.</div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 6. Adaptive Mobile Expense Cards Stack (Mobile <= 768px)            -->
    <!-- =================================================================== -->
    <?php require APP_ROOT . '/views/admin/expenses_partials/_cards_stack.php'; ?>

    <!-- =================================================================== -->
    <!-- 7. Shared Pagination Controls                                       -->
    <!-- =================================================================== -->
    <?php if ($expensesData['total_pages'] > 1): 
        $currPage = $expensesData['current_page'];
        $totPages = $expensesData['total_pages'];
        $paginationQuery = $_GET;
    ?>
    <div class="fin-pagination-container">
        <div class="fin-pagination-info">
            نمایش <?= toPersianDigits((string)((($currPage - 1) * $expensesData['per_page']) + 1)) ?> تا <?= toPersianDigits((string)min($currPage * $expensesData['per_page'], $expensesData['total_count'])) ?> از <?= toPersianDigits((string)$expensesData['total_count']) ?> سند هزینه
        </div>
        <div class="fin-pagination-pages">
            <?php if ($currPage > 1): 
                $paginationQuery['page'] = $currPage - 1;
            ?>
                <a href="expenses.php?<?= http_build_query($paginationQuery) ?>" class="fin-action-btn fin-btn-outline" style="height: 32px; padding: 0 10px;">قبلی</a>
            <?php endif; ?>

            <?php for ($i = max(1, $currPage - 2); $i <= min($totPages, $currPage + 2); $i++): 
                $paginationQuery['page'] = $i;
            ?>
                <a href="expenses.php?<?= http_build_query($paginationQuery) ?>" class="fin-action-btn <?= ($i === $currPage) ? 'fin-btn-primary' : 'fin-btn-outline' ?>" style="height: 32px; min-width: 32px; padding: 0; justify-content: center;">
                    <?= toPersianDigits((string)$i) ?>
                </a>
            <?php endfor; ?>

            <?php if ($currPage < $totPages): 
                $paginationQuery['page'] = $currPage + 1;
            ?>
                <a href="expenses.php?<?= http_build_query($paginationQuery) ?>" class="fin-action-btn fin-btn-outline" style="height: 32px; padding: 0 10px;">بعدی</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</main>

<!-- =================================================================== -->
<!-- Receipt Lightbox Modal                                              -->
<!-- =================================================================== -->
<div id="receiptModal" class="fin-modal-overlay" onclick="if(event.target === this) closeReceiptModal();">
    <div class="fin-modal-content">
        <div class="fin-modal-head">
            <span id="receiptModalTitle" style="font-size: 0.9rem; font-weight: 700; color: var(--fin-text-primary);">تصویر فاکتور / رسید پرداختی</span>
            <button type="button" onclick="closeReceiptModal()" class="fin-action-btn fin-btn-outline" style="padding: 4px 10px; height: 30px;">✕ بستن</button>
        </div>
        <div class="fin-modal-body">
            <img id="receiptModalImg" src="" alt="رسید">
        </div>
    </div>
</div>

<script>
function toggleMobileFilters() {
    var drawer = document.getElementById('finFilterDrawer');
    if (drawer) {
        drawer.classList.toggle('is-open');
    }
}
function openReceiptModal(url, title) {
    document.getElementById('receiptModalImg').src = url;
    document.getElementById('receiptModalTitle').innerText = 'فاکتور: ' + title;
    document.getElementById('receiptModal').classList.add('is-active');
}
function closeReceiptModal() {
    document.getElementById('receiptModal').classList.remove('is-active');
    document.getElementById('receiptModalImg').src = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeReceiptModal();
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
