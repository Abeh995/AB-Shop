<?php
/**
 * Modern High-Density Admin Financial Dashboard & Profitability Workstation
 * Desktop Bento-Grid Layout with Interactive SVG Trends, Unit Economics & Printable P&L.
 */
require APP_ROOT . '/views/admin/layout/header.php';

// Prepare SVG chart dynamic coordinates
$chartCount = count($dailyTrends ?? []);
$maxTrendVal = 1;
if ($chartCount > 0) {
    foreach ($dailyTrends as $pt) {
        if ($pt['revenue'] > $maxTrendVal) $maxTrendVal = $pt['revenue'];
        if ($pt['cogs'] > $maxTrendVal) $maxTrendVal = $pt['cogs'];
        if ($pt['net_profit'] > $maxTrendVal) $maxTrendVal = $pt['net_profit'];
    }
}

$revPoints = [];
$profitPoints = [];
$revCoords = [];
$profitCoords = [];

if ($chartCount > 0) {
    foreach ($dailyTrends as $idx => $pt) {
        $x = ($chartCount > 1) ? (int) round(($idx / ($chartCount - 1)) * 680) + 10 : 350;
        $yRev = (int) round(140 - (($pt['revenue'] / $maxTrendVal) * 115));
        $yProfit = (int) round(140 - ((max(0, $pt['net_profit']) / $maxTrendVal) * 115));

        $revPoints[] = ['x' => $x, 'y' => $yRev, 'pt' => $pt];
        $profitPoints[] = ['x' => $x, 'y' => $yProfit, 'pt' => $pt];
        $revCoords[] = "$x $yRev";
        $profitCoords[] = "$x $yProfit";
    }
}

$revPathD = !empty($revCoords) ? 'M ' . implode(' L ', $revCoords) : 'M 10 140 L 690 140';
$revAreaD = !empty($revCoords) ? $revPathD . ' L ' . end($revPoints)['x'] . ' 150 L 10 150 Z' : '';
$profitPathD = !empty($profitCoords) ? 'M ' . implode(' L ', $profitCoords) : '';

$isProfitable = ($summary['net_profit'] >= 0);
$netStatusClass = $isProfitable ? 'accent-emerald' : 'accent-rose';
$netColor = $isProfitable ? 'var(--fin-emerald)' : 'var(--fin-rose)';
?>

<main class="fin-workspace">

    <!-- =================================================================== -->
    <!-- 0. Printable P&L Header (Strictly for @media print)                 -->
    <!-- =================================================================== -->
    <div class="fin-print-header">
        <h1>صورت سود و زیان رسمی — فروشگاه اینترنتی <?= e(SITE_NAME) ?></h1>
        <p style="margin: 6px 0 0 0; font-size: 10pt; color: #444;">
            دوره گزارش: از تاریخ <?= e($startDate) ?> تا <?= e($endDate) ?> | تاریخ صدور: <?= e(appDateTime(null, 'full_shamsi')) ?>
        </p>
    </div>

    <!-- =================================================================== -->
    <!-- 1. Date Range Toolbar, Quick Chips & Action Center                  -->
    <!-- =================================================================== -->
    <section class="fin-toolbar-card">
        <!-- Quick Preset Chips -->
        <div class="fin-presets-group">
            <span class="fin-preset-label">بازه سریع:</span>
            <a href="finance_dashboard.php?range=today" class="fin-chip <?= ($range === 'today') ? 'active' : '' ?>">امروز</a>
            <a href="finance_dashboard.php?range=yesterday" class="fin-chip <?= ($range === 'yesterday') ? 'active' : '' ?>">دیروز</a>
            <a href="finance_dashboard.php?range=7days" class="fin-chip <?= ($range === '7days') ? 'active' : '' ?>">۷ روز</a>
            <a href="finance_dashboard.php?range=30days" class="fin-chip <?= ($range === '30days') ? 'active' : '' ?>">۳۰ روز</a>
            <a href="finance_dashboard.php?range=this_month" class="fin-chip <?= ($range === 'this_month' || (empty($range) && $startDate === date('Y-m-01'))) ? 'active' : '' ?>">ماه جاری</a>
            <a href="finance_dashboard.php?range=last_month" class="fin-chip <?= ($range === 'last_month') ? 'active' : '' ?>">ماه قبل</a>
            <a href="finance_dashboard.php?range=this_year" class="fin-chip <?= ($range === 'this_year') ? 'active' : '' ?>">امسال</a>
        </div>

        <!-- Custom Date Range Form -->
        <form method="get" action="finance_dashboard.php" class="fin-custom-date-form">
            <div class="fin-date-field">
                <span>از:</span>
                <input class="fin-date-input" type="date" name="start_date" value="<?= e($startDate) ?>" required>
            </div>
            <div class="fin-date-field">
                <span>تا:</span>
                <input class="fin-date-input" type="date" name="end_date" value="<?= e($endDate) ?>" required>
            </div>
            <button type="submit" class="fin-action-btn fin-btn-outline">اعمال تاریخ</button>
        </form>

        <!-- Command Action Buttons -->
        <div class="fin-actions-group">
            <button type="button" onclick="window.print()" class="fin-action-btn fin-btn-outline" title="چاپ رسمی صورت سود و زیان (A4)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>چاپ صورت مالی</span>
            </button>
            <a href="finance_dashboard.php?export=csv&start_date=<?= e($startDate) ?>&end_date=<?= e($endDate) ?>" class="fin-action-btn fin-btn-outline" title="دریافت فایل اکسل / CSV">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>خروجی CSV</span>
            </a>
            <a href="expense_edit.php" class="fin-action-btn fin-btn-primary" title="ثبت سند هزینه عملیاتی جدید">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>ثبت سند هزینه</span>
            </a>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 2. Cost Health Alert (Orders with Missing Cost Data)                -->
    <!-- =================================================================== -->
    <?php if (!empty($summary['orders_with_incomplete_cost_data'])): ?>
    <section class="fin-alert-banner" style="flex-direction: column; align-items: stretch;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                    ⚠️
                </div>
                <div style="font-size: 0.86rem; line-height: 1.6; color: #92400E;">
                    <strong>هشدار عدم ثبت بهای تمام‌شده:</strong>
                    تعداد <strong><?= toPersianDigits((string)$summary['orders_with_incomplete_cost_data']) ?> سفارش</strong> در این بازه، شامل اقلامی بوده‌اند که در زمان ثبت فاقد بهای خرید بوده‌اند. 
                    (سلامت بهای کاتالوگ: <strong><?= toPersianDigits(number_format($summary['cost_health_percent'], 1)) ?>٪</strong>)
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <?php if (!empty($incompleteProducts)): ?>
                    <button type="button" onclick="document.getElementById('finIncompleteDrawer').classList.toggle('is-open')" class="fin-action-btn fin-btn-outline" style="background:#FFF; color:#B45309; border-color:#FCD34D;">
                        مشاهده لیست کالاها (<?= toPersianDigits((string)count($incompleteProducts)) ?> قلم) ▾
                    </button>
                <?php endif; ?>
                <a href="pricing.php" class="fin-action-btn fin-btn-outline" style="background:#FFF; color:#B45309; border-color:#FCD34D;">
                    اصلاح در مدیریت قیمت‌ها ←
                </a>
            </div>
        </div>

        <?php if (!empty($incompleteProducts)): ?>
        <div id="finIncompleteDrawer" style="display: none; margin-top: 14px; padding-top: 12px; border-top: 1px dashed rgba(217, 119, 6, 0.3);">
            <div style="font-size: 0.8rem; font-weight: 700; color: #92400E; margin-bottom: 8px;">کالاهای دارای کسری قیمت خرید در سفارشات این دوره:</div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px;">
                <?php foreach ($incompleteProducts as $inc): ?>
                <div style="background: #FFF; border: 1px solid #FDE68A; border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
                        <img src="<?= e($inc['image_url']) ?>" alt="<?= e($inc['product_name']) ?>" style="width: 32px; height: 32px; border-radius: 4px; object-fit: cover; flex-shrink: 0;">
                        <div style="overflow: hidden;">
                            <div style="font-size: 0.8rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #78350F;"><?= e($inc['product_name']) ?></div>
                            <div style="font-size: 0.7rem; color: #B45309;"><?= toPersianDigits((string)$inc['impacted_orders']) ?> سفارش (<?= toPersianDigits((string)$inc['sold_qty']) ?> عدد)</div>
                        </div>
                    </div>
                    <a href="product_edit.php?id=<?= (int)$inc['product_id'] ?>" style="font-size: 0.72rem; color: var(--fin-primary); text-decoration: none; font-weight: 700; white-space: nowrap; margin-right: 6px;">
                        ثبت قیمت خرید ←
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- 3. Hero Bento KPI Grid (4 Pillars of Store Profitability)           -->
    <!-- =================================================================== -->
    <section class="fin-kpi-grid">
        
        <!-- 1. Gross Revenue -->
        <div class="fin-kpi-card accent-blue">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title">مجموع فروش ناخالص</span>
                <div class="fin-kpi-icon-box" style="background: var(--fin-primary-light); color: var(--fin-primary);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span class="fin-kpi-value" style="color: var(--fin-primary);"><?= formatPrice($summary['total_revenue']) ?></span>
            </div>
            <div class="fin-kpi-footer">
                <span><strong><?= toPersianDigits((string)$summary['order_count']) ?></strong> سفارش موفق</span>
                <span>•</span>
                <span>AOV: <strong><?= formatPrice($summary['aov']) ?></strong></span>
            </div>
        </div>

        <!-- 2. COGS & Fulfillment -->
        <div class="fin-kpi-card accent-purple">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title">بهای تمام‌شده کالا و لجستیک</span>
                <div class="fin-kpi-icon-box" style="background: var(--fin-purple-light); color: var(--fin-purple);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span class="fin-kpi-value" style="color: var(--fin-purple);"><?= formatPrice($summary['total_cogs']) ?></span>
            </div>
            <div class="fin-kpi-footer">
                <span>کالا: <strong><?= formatPrice($summary['product_cost']) ?></strong></span>
                <span>•</span>
                <span>پست: <strong><?= formatPrice($summary['shipping_cost']) ?></strong></span>
            </div>
        </div>

        <!-- 3. Operating Expenses -->
        <div class="fin-kpi-card accent-amber">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title">هزینه‌های جاری عملیاتی</span>
                <div class="fin-kpi-icon-box" style="background: var(--fin-amber-light); color: var(--fin-amber);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span class="fin-kpi-value" style="color: #D97706;"><?= formatPrice($summary['total_expenses']) ?></span>
            </div>
            <div class="fin-kpi-footer">
                <span>سهم از درآمد: <strong><?= toPersianDigits(number_format(($summary['total_revenue'] > 0 ? ($summary['total_expenses'] / $summary['total_revenue']) * 100 : 0), 1)) ?>٪</strong></span>
                <span>•</span>
                <a href="expenses.php" style="color: var(--fin-primary); text-decoration: none;">مشاهده دفتر کل</a>
            </div>
        </div>

        <!-- 4. Net Profit & Margin -->
        <div class="fin-kpi-card <?= $netStatusClass ?>">
            <div class="fin-kpi-header">
                <span class="fin-kpi-title" style="font-weight: 700;">سود خالص نهایی دوره</span>
                <div class="fin-kpi-icon-box" style="background: <?= $isProfitable ? 'var(--fin-emerald-light)' : 'var(--fin-rose-light)' ?>; color: <?= $netColor ?>;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <div class="fin-kpi-value-row">
                <span class="fin-kpi-value" style="color: <?= $netColor ?>;"><?= formatPrice($summary['net_profit']) ?></span>
            </div>
            <div class="fin-kpi-footer">
                <span style="font-weight: 700; color: <?= $netColor ?>;">
                    حاشیه سود: <?= toPersianDigits(number_format($summary['net_margin_percent'], 1)) ?>٪
                </span>
                <span>•</span>
                <span>سود ناخالص: <?= formatPrice($summary['gross_profit']) ?></span>
            </div>
        </div>

    </section>

    <!-- =================================================================== -->
    <!-- 4. Unit Economics & Micro-Metrics Strip                             -->
    <!-- =================================================================== -->
    <section class="fin-micro-grid">
        <!-- A. Profit per Order -->
        <div class="fin-micro-card">
            <div class="fin-micro-lead">
                <span class="fin-micro-dot" style="background: var(--fin-emerald);"></span>
                <span class="fin-micro-title">میانگین سود هر سبد</span>
            </div>
            <span class="fin-micro-val" style="color: <?= $summary['net_profit_per_order'] >= 0 ? 'var(--fin-emerald)' : 'var(--fin-rose)' ?>;">
                <?= formatPrice($summary['net_profit_per_order']) ?>
            </span>
        </div>

        <!-- B. Discount Drain Rate -->
        <div class="fin-micro-card">
            <div class="fin-micro-lead">
                <span class="fin-micro-dot" style="background: var(--fin-rose);"></span>
                <span class="fin-micro-title">نرخ فرسایش تخفیف</span>
            </div>
            <span class="fin-micro-val">
                <?= toPersianDigits(number_format($summary['discount_rate'], 1)) ?>٪
                <span style="font-size: 0.72rem; font-weight: normal; color: var(--fin-text-muted);">(<?= formatPrice($summary['discount_total']) ?>)</span>
            </span>
        </div>

        <!-- C. Logistics Balance (Shipping Profit/Subsidy) -->
        <div class="fin-micro-card">
            <div class="fin-micro-lead">
                <span class="fin-micro-dot" style="background: <?= $summary['shipping_balance'] >= 0 ? 'var(--fin-teal)' : 'var(--fin-amber)' ?>;"></span>
                <span class="fin-micro-title">تراز مالی کرایه پست</span>
            </div>
            <span class="fin-micro-val" style="color: <?= $summary['shipping_balance'] >= 0 ? 'var(--fin-teal)' : '#D97706' ?>;">
                <?= ($summary['shipping_balance'] >= 0 ? '+' : '') . formatPrice($summary['shipping_balance']) ?>
                <span style="font-size: 0.7rem; font-weight: normal; color: var(--fin-text-muted);">
                    (<?= $summary['shipping_balance'] >= 0 ? 'مازاد کرایه' : 'سوبسید فروشگاه' ?>)
                </span>
            </span>
        </div>

        <!-- D. Catalog Cost Health -->
        <div class="fin-micro-card">
            <div class="fin-micro-lead">
                <span class="fin-micro-dot" style="background: var(--fin-primary);"></span>
                <span class="fin-micro-title">سلامت بهای تمام‌شده</span>
            </div>
            <span class="fin-micro-val" style="color: <?= $summary['cost_health_percent'] >= 90 ? 'var(--fin-emerald)' : '#D97706' ?>;">
                <?= toPersianDigits(number_format($summary['cost_health_percent'], 1)) ?>٪
                <span style="font-size: 0.72rem; font-weight: normal; color: var(--fin-text-muted);">داده‌های معتبر</span>
            </span>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 5. Visual Timeline (SVG Trends) & Cash Flow Waterfall               -->
    <!-- =================================================================== -->
    <section class="fin-visual-grid">
        
        <!-- Left Visual: Interactive Daily Trendline Chart -->
        <div class="fin-visual-card">
            <div class="fin-card-head">
                <div>
                    <h3 class="fin-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        <span>روند درآمد و سود روزانه در طول دوره</span>
                    </h3>
                    <p class="fin-card-subtitle">بررسی دینامیک درآمد فروش در برابر سود خالص نهایی روز به روز</p>
                </div>
                <div style="display: flex; gap: 14px; font-size: 0.78rem;">
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: var(--fin-primary); display: inline-block;"></span>
                        <span>فروش روز</span>
                    </span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: var(--fin-emerald); display: inline-block;"></span>
                        <span>سود خالص روز</span>
                    </span>
                </div>
            </div>

            <div class="fin-chart-container" id="finChartBox">
                <div class="fin-chart-tooltip" id="finChartTooltip"></div>
                <svg viewBox="0 0 700 160" preserveAspectRatio="none" class="fin-chart-svg" id="finChartSvg">
                    <defs>
                        <linearGradient id="finRevAreaGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="var(--fin-primary)" stop-opacity="0.25"/>
                            <stop offset="100%" stop-color="var(--fin-primary)" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>

                    <!-- Subtle Horizontal Gridlines -->
                    <line x1="10" y1="25" x2="690" y2="25" stroke="#F1F5F9" stroke-width="1" stroke-dasharray="4"/>
                    <line x1="10" y1="80" x2="690" y2="80" stroke="#F1F5F9" stroke-width="1" stroke-dasharray="4"/>
                    <line x1="10" y1="140" x2="690" y2="140" stroke="#E2E8F0" stroke-width="1.2"/>

                    <!-- Area fill for Revenue -->
                    <?php if (!empty($revAreaD)): ?>
                        <path d="<?= $revAreaD ?>" fill="url(#finRevAreaGrad)"/>
                    <?php endif; ?>

                    <!-- Revenue Trendline -->
                    <path d="<?= $revPathD ?>" fill="none" stroke="var(--fin-primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>

                    <!-- Net Profit Trendline -->
                    <?php if (!empty($profitPathD)): ?>
                        <path d="<?= $profitPathD ?>" fill="none" stroke="var(--fin-emerald)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="3 3"/>
                    <?php endif; ?>

                    <!-- Interactive Data Points -->
                    <?php foreach ($revPoints as $p): ?>
                        <circle cx="<?= $p['x'] ?>" cy="<?= $p['y'] ?>" r="4.5" fill="#FFFFFF" stroke="var(--fin-primary)" stroke-width="2.5" class="fin-chart-node"
                            data-date="<?= e($p['pt']['label_shamsi']) ?>"
                            data-rev="<?= formatPrice($p['pt']['revenue']) ?>"
                            data-profit="<?= formatPrice($p['pt']['net_profit']) ?>"
                            data-exp="<?= formatPrice($p['pt']['expenses']) ?>"
                            data-orders="<?= toPersianDigits((string)$p['pt']['order_count']) ?>"
                            style="cursor: pointer; transition: transform 0.15s ease;"
                        />
                    <?php endforeach; ?>

                    <!-- X-Axis Date Labels -->
                    <?php if (!empty($revPoints)): ?>
                        <?php 
                        $firstPt = reset($revPoints);
                        $lastPt = end($revPoints);
                        $midPt = $revPoints[(int)(count($revPoints) / 2)] ?? null;
                        ?>
                        <text x="<?= $firstPt['x'] ?>" y="156" font-size="10" fill="#94A3B8" font-family="inherit" text-anchor="start"><?= e($firstPt['pt']['label_shamsi']) ?></text>
                        <?php if ($midPt && count($revPoints) > 4): ?>
                            <text x="<?= $midPt['x'] ?>" y="156" font-size="10" fill="#94A3B8" font-family="inherit" text-anchor="middle"><?= e($midPt['pt']['label_shamsi']) ?></text>
                        <?php endif; ?>
                        <text x="<?= $lastPt['x'] ?>" y="156" font-size="10" fill="#94A3B8" font-family="inherit" text-anchor="end"><?= e($lastPt['pt']['label_shamsi']) ?></text>
                    <?php endif; ?>
                </svg>
            </div>
        </div>

        <!-- Right Visual: Waterfall Cash Flow Breakdown -->
        <?php 
        $cogsPct = ($summary['total_revenue'] > 0) ? round(($summary['total_cogs'] / $summary['total_revenue']) * 100, 1) : 0;
        $expPct  = ($summary['total_revenue'] > 0) ? round(($summary['total_expenses'] / $summary['total_revenue']) * 100, 1) : 0;
        $netPct  = max(0, round(100 - $cogsPct - $expPct, 1));
        ?>
        <div class="fin-visual-card">
            <div class="fin-card-head">
                <div>
                    <h3 class="fin-card-title">جریان تفکیک ورودی صندوق</h3>
                    <p class="fin-card-subtitle">توزیع ۱۰۰٪ درآمدهای وصولی فروشگاه</p>
                </div>
                <span style="font-size: 0.8rem; font-weight: 700; color: var(--fin-primary);">
                    <?= formatPrice($summary['total_revenue']) ?>
                </span>
            </div>

            <!-- Segmented Flow Bar -->
            <div class="fin-waterfall-bar">
                <div class="fin-waterfall-seg" style="width: <?= min(100, $cogsPct) ?>%; background: var(--fin-purple);" title="بهای کالا و ارسال: <?= $cogsPct ?>٪"></div>
                <div class="fin-waterfall-seg" style="width: <?= min(100 - $cogsPct, $expPct) ?>%; background: var(--fin-amber);" title="هزینه‌های جاری: <?= $expPct ?>٪"></div>
                <?php if ($summary['net_profit'] > 0): ?>
                    <div class="fin-waterfall-seg" style="width: <?= $netPct ?>%; background: var(--fin-emerald);" title="سود خالص باقی‌مانده: <?= $netPct ?>٪"></div>
                <?php endif; ?>
            </div>

            <!-- Detailed Legend -->
            <div class="fin-waterfall-legend">
                <div class="fin-legend-row">
                    <div class="fin-legend-lead">
                        <span class="fin-legend-dot" style="background: var(--fin-purple);"></span>
                        <span>بهای کالا و ارسال (COGS)</span>
                    </div>
                    <div>
                        <strong><?= formatPrice($summary['total_cogs']) ?></strong>
                        <span style="color: var(--fin-text-muted); font-size: 0.75rem;">(<?= toPersianDigits((string)$cogsPct) ?>٪)</span>
                    </div>
                </div>

                <div class="fin-legend-row">
                    <div class="fin-legend-lead">
                        <span class="fin-legend-dot" style="background: var(--fin-amber);"></span>
                        <span>مخارج جاری عملیاتی (OPEX)</span>
                    </div>
                    <div>
                        <strong><?= formatPrice($summary['total_expenses']) ?></strong>
                        <span style="color: var(--fin-text-muted); font-size: 0.75rem;">(<?= toPersianDigits((string)$expPct) ?>٪)</span>
                    </div>
                </div>

                <div class="fin-legend-row" style="border-bottom: none;">
                    <div class="fin-legend-lead">
                        <span class="fin-legend-dot" style="background: var(--fin-emerald);"></span>
                        <span>سود خالص باقی‌مانده</span>
                    </div>
                    <div>
                        <strong style="color: <?= $netColor ?>;"><?= formatPrice($summary['net_profit']) ?></strong>
                        <span style="color: var(--fin-text-muted); font-size: 0.75rem;">(<?= toPersianDigits(number_format($summary['net_margin_percent'], 1)) ?>٪)</span>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- =================================================================== -->
    <!-- 6. Dual-Column Deep Analytics Workspace                             -->
    <!-- =================================================================== -->
    <section class="fin-dual-grid">
        
        <!-- Right Column (60%): Top Profit Drivers, Logistics, & Payment Split -->
        <div class="fin-col-block">
            
            <!-- A. Top 5 Profit Drivers Table -->
            <div class="fin-table-card">
                <div class="fin-table-head-strip">
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700;">محصولات پیشران سود دوره (Top Profit Drivers)</h4>
                        <span style="font-size: 0.75rem; color: var(--fin-text-muted);">کالاهایی که بیشترین سود ریالی را تولید کرده‌اند</span>
                    </div>
                    <a href="pricing.php" style="font-size: 0.8rem; color: var(--fin-primary); text-decoration: none; font-weight: 600;">
                        مدیریت قیمت‌ها ←
                    </a>
                </div>

                <?php if (!empty($topProducts)): ?>
                <table class="fin-data-table">
                    <thead>
                        <tr>
                            <th>محصول</th>
                            <th style="width: 80px; text-align: center;">فروش</th>
                            <th style="width: 110px;">درآمد کل</th>
                            <th style="width: 120px;">سود ناخالص</th>
                            <th style="width: 80px; text-align: center;">مارجین</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProducts as $prod): ?>
                        <tr>
                            <td>
                                <div class="fin-prod-cell">
                                    <img src="<?= e($prod['image_url']) ?>" alt="<?= e($prod['product_name']) ?>" class="fin-prod-thumb" loading="lazy">
                                    <div>
                                        <div class="fin-prod-name" title="<?= e($prod['product_name']) ?>"><?= e($prod['product_name']) ?></div>
                                        <div class="fin-prod-sub">قیمت فعلی: <?= formatPrice($prod['current_price']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: 700;">
                                <?= toPersianDigits((string)$prod['total_sold_qty']) ?>
                            </td>
                            <td><?= formatPrice($prod['total_revenue']) ?></td>
                            <td style="font-weight: 700; color: var(--fin-emerald);">
                                <?= formatPrice($prod['total_profit']) ?>
                            </td>
                            <td style="text-align: center;">
                                <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; background: var(--fin-emerald-light); color: var(--fin-emerald);">
                                    <?= toPersianDigits(number_format($prod['margin_percent'], 1)) ?>٪
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="padding: 30px; text-align: center; color: var(--fin-text-muted); font-size: 0.85rem;">
                        در این بازه زمانی هیچ فروش محصولی ثبت نشده است.
                    </div>
                <?php endif; ?>
            </div>

            <!-- B. Shipping & Logistics P&L Table -->
            <div class="fin-table-card">
                <div class="fin-table-head-strip">
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700;">ترازنامه مالی روش‌های ارسال (Shipping Logistics)</h4>
                        <span style="font-size: 0.75rem; color: var(--fin-text-muted);">مقایسه کرایه دریافتی از مشتری با هزینه پرداختی به پست</span>
                    </div>
                    <a href="shipping_methods.php" style="font-size: 0.8rem; color: var(--fin-primary); text-decoration: none; font-weight: 600;">
                        تنظیم روش‌ها ←
                    </a>
                </div>

                <?php if (!empty($shippingBreakdown)): ?>
                <table class="fin-data-table">
                    <thead>
                        <tr>
                            <th>روش ارسال</th>
                            <th style="width: 70px; text-align: center;">بسته‌ها</th>
                            <th style="width: 110px;">کرایه دریافتی</th>
                            <th style="width: 110px;">هزینه واقعی پست</th>
                            <th style="width: 120px;">تراز نهایی</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shippingBreakdown as $sh): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= e($sh['method_name']) ?></td>
                            <td style="text-align: center;"><?= toPersianDigits((string)$sh['order_count']) ?></td>
                            <td><?= formatPrice($sh['shipping_revenue']) ?></td>
                            <td><?= formatPrice($sh['shipping_cost']) ?></td>
                            <td>
                                <?php if ($sh['balance'] >= 0): ?>
                                    <span style="color: var(--fin-teal); font-weight: 700;">+<?= formatPrice($sh['balance']) ?> (مازاد)</span>
                                <?php else: ?>
                                    <span style="color: var(--fin-rose); font-weight: 700;">-<?= formatPrice(abs($sh['balance'])) ?> (سوبسید)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- C. Payment Channels Split -->
            <div class="fin-table-card" style="padding: 16px 20px;">
                <h4 style="margin: 0 0 12px 0; font-size: 0.95rem; font-weight: 700;">کانال‌های وصول درآمد (Payment Channels)</h4>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($paymentBreakdown as $pay): ?>
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; font-size: 0.84rem;">
                            <span style="font-weight: 600;"><?= e($pay['title']) ?> (<?= toPersianDigits((string)$pay['order_count']) ?> سفارش)</span>
                            <span><strong><?= formatPrice($pay['total_amount']) ?></strong> (<?= toPersianDigits((string)$pay['share_percent']) ?>٪)</span>
                        </div>
                        <div style="height: 8px; background: #E2E8F0; border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $pay['share_percent'] ?>%; background: <?= $pay['method'] === 'zarinpal' ? 'var(--fin-primary)' : 'var(--fin-purple)' ?>; border-radius: 4px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Left Column (40%): Break-Even Gauge, Operational Expenses by Category -->
        <div class="fin-col-block">
            
            <!-- A. Break-Even Analysis High-Contrast Card -->
            <div class="fin-be-card">
                <div class="fin-be-header">
                    <span class="fin-be-title">تحلیل نقطه سر‌به‌سر (Break-Even)</span>
                    <span class="fin-be-status <?= $summary['is_breakeven_reached'] ? 'reached' : 'pending' ?>">
                        <?= $summary['is_breakeven_reached'] ? '✓ عبور از مرز زیان' : '⏳ در مسیر تحقق' ?>
                    </span>
                </div>
                
                <div style="font-size: 0.82rem; color: #CBD5E1; line-height: 1.5;">
                    فروش مورد نیاز برای جبران ۱۰۰٪ مخارج دوره:
                    <strong style="color: #FFFFFF; font-size: 1rem; margin-right: 4px;"><?= formatPrice($summary['breakeven_revenue']) ?></strong>
                </div>

                <!-- Progress Track -->
                <div class="fin-gauge-track">
                    <div class="fin-gauge-fill" style="width: <?= min(100, $summary['breakeven_progress_percent']) ?>%; background: <?= $summary['is_breakeven_reached'] ? '#34D399' : '#FBBF24' ?>;"></div>
                </div>

                <div class="fin-be-meta-grid">
                    <div class="fin-be-meta-item">
                        <div class="fin-be-meta-label">سفارش لازم تا سر‌به‌سر</div>
                        <div class="fin-be-meta-val"><?= toPersianDigits((string)$summary['breakeven_orders']) ?> سفارش</div>
                    </div>
                    <div class="fin-be-meta-item">
                        <div class="fin-be-meta-label">درصد تحقق هدف</div>
                        <div class="fin-be-meta-val"><?= toPersianDigits(number_format($summary['breakeven_progress_percent'], 1)) ?>٪</div>
                    </div>
                </div>
            </div>

            <!-- B. Operating Expenses by Category -->
            <div class="fin-table-card">
                <div class="fin-table-head-strip">
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700;">سرفصل‌های هزینه‌های جاری</h4>
                        <span style="font-size: 0.75rem; color: var(--fin-text-muted);">توزیع مخارج عملیاتی ثبت‌شده</span>
                    </div>
                    <a href="expenses.php" style="font-size: 0.8rem; color: var(--fin-primary); text-decoration: none; font-weight: 600;">
                        ریز اسناد ←
                    </a>
                </div>

                <?php if (!empty($summary['expenses_by_category'])): ?>
                <div class="fin-cat-list">
                    <?php foreach ($summary['expenses_by_category'] as $row): ?>
                    <div>
                        <div class="fin-cat-row-head">
                            <span style="font-weight: 600;"><?= e($row['category']) ?></span>
                            <div>
                                <strong><?= formatPrice((int)$row['total']) ?></strong>
                                <span style="color: var(--fin-text-muted); font-size: 0.75rem; margin-right: 4px;">(<?= toPersianDigits(number_format($row['share_percent'], 1)) ?>٪)</span>
                            </div>
                        </div>
                        <div class="fin-cat-bar-bg">
                            <div class="fin-cat-bar-fill" style="width: <?= min(100, $row['share_percent']) ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <div style="padding: 24px; text-align: center; color: var(--fin-text-muted); font-size: 0.85rem;">
                        در این بازه هیچ سند هزینه‌ای ثبت نشده است.
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </section>

    <!-- =================================================================== -->
    <!-- 7. Printable P&L Footer (Strictly for @media print)                 -->
    <!-- =================================================================== -->
    <div class="fin-print-footer">
        <div>
            <strong>تنظیم‌کننده گزارش:</strong> سیستم حسابداری خودکار <?= e(SITE_NAME) ?>
        </div>
        <div>
            <strong>امضا و تایید مدیریت فروشگاه:</strong> ...................................
        </div>
    </div>

</main>

<!-- Interactive SVG Tooltip Script -->
<script>
(function() {
    var chartBox = document.getElementById('finChartBox');
    var tooltip = document.getElementById('finChartTooltip');
    var nodes = document.querySelectorAll('.fin-chart-node');

    if (!chartBox || !tooltip || !nodes.length) return;

    nodes.forEach(function(node) {
        node.addEventListener('mouseenter', function(e) {
            var date = this.getAttribute('data-date');
            var rev = this.getAttribute('data-rev');
            var profit = this.getAttribute('data-profit');
            var exp = this.getAttribute('data-exp');
            var orders = this.getAttribute('data-orders');

            tooltip.innerHTML = '<div style="font-weight:700; margin-bottom:4px; border-bottom:1px solid rgba(255,255,255,0.2); padding-bottom:3px;">' + date + ' (' + orders + ' سفارش)</div>' +
                                '<div style="display:flex; justify-content:space-between; gap:10px;"><span>فروش:</span><strong>' + rev + '</strong></div>' +
                                '<div style="display:flex; justify-content:space-between; gap:10px; color:#34D399;"><span>سود خالص:</span><strong>' + profit + '</strong></div>' +
                                '<div style="display:flex; justify-content:space-between; gap:10px; color:#FBBF24;"><span>مخارج:</span><strong>' + exp + '</strong></div>';
            
            tooltip.style.display = 'block';
            this.setAttribute('r', '7');
        });

        node.addEventListener('mousemove', function(e) {
            var boxRect = chartBox.getBoundingClientRect();
            var x = e.clientX - boxRect.left;
            var y = e.clientY - boxRect.top;
            tooltip.style.left = x + 'px';
            tooltip.style.top = (y - 12) + 'px';
        });

        node.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
            this.setAttribute('r', '4.5');
        });
    });
})();
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
