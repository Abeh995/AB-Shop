<?php
/**
 * Modern Admin Dashboard View (Redesigned)
 * Connected with live store metrics, dynamic SVG revenue chart, filterable orders table,
 * adaptive mobile cards stack, quick action tiles, and real-time inventory & receipt widgets.
 */
require APP_ROOT . '/views/admin/layout/header.php';

// Prepare SVG chart dynamic coordinates based on live 7-day revenue
$maxVal = 1;
foreach ($chartPoints as $pt) {
    if ($pt['total'] > $maxVal) {
        $maxVal = $pt['total'];
    }
}
$svgPoints = [];
$pathCoords = [];
$totalPoints = count($chartPoints ?? []);
if ($totalPoints > 0) {
    foreach ($chartPoints as $idx => $pt) {
        $x = ($totalPoints > 1) ? (int) round(($idx / ($totalPoints - 1)) * 700) : 350;
        // Scale y between 25 (highest) and 125 (lowest)
        $y = (int) round(125 - (($pt['total'] / $maxVal) * 95));
        $svgPoints[] = ['x' => $x, 'y' => $y, 'pt' => $pt];
        $pathCoords[] = "$x $y";
    }
}

if (!empty($pathCoords)) {
    $linePathD = 'M ' . implode(' L ', $pathCoords);
    $areaPathD = $linePathD . ' L 700 150 L 0 150 Z';
} else {
    $linePathD = 'M 0 125 L 700 125';
    $areaPathD = 'M 0 125 L 700 125 L 700 150 L 0 150 Z';
}
?>

<!-- ======================================================================= -->
<!-- Compact Dashboard Header (Brand + Search Trigger + Store Link)           -->
<!-- ======================================================================= -->
<div class="dash-topbar">
    <div class="dash-topbar-right">
        <div class="dash-brand-badge">AB</div>
        <div class="dash-title-wrap">
            <span class="dash-main-title">داشبورد مدیریت <?= e(SITE_NAME) ?></span>
            <span class="dash-title-sub">نمای تحلیلی و عملیات فروشگاه</span>
        </div>
    </div>

    <div class="dash-topbar-left">
        <!-- Compact Search Button (Opens Command Palette / Ctrl+K) -->
        <button type="button" class="btn-dash-search" onclick="openAdminCmdPalette()" title="جستجوی سریع در پنل (Ctrl+K)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <span>جستجو در سیستم...</span>
            <kbd class="dash-keycap">Ctrl K</kbd>
        </button>

        <!-- Direct Storefront Link -->
        <a href="/" target="_blank" class="btn-dash-store" title="مشاهده سایت سمت مشتری">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            <span>مشاهده فروشگاه</span>
        </a>
    </div>
</div>

<div class="dash-workspace">

    <!-- =================================================================== -->
    <!-- 5 Bento Metric Cards (Directly at the Top)                          -->
    <!-- =================================================================== -->
    <section class="dash-kpi-grid">
        
        <!-- 1. Total Sales / Revenue -->
        <div class="dash-kpi-card">
            <div class="dash-kpi-header">
                <span class="dash-kpi-title">خالص فروش کل</span>
                <span class="dash-kpi-icon-pill">💰</span>
            </div>
            <div class="dash-kpi-val-row">
                <span class="dash-kpi-val"><?= formatPrice($revenue) ?></span>
            </div>
            <div class="dash-kpi-footer">
                <span class="dash-tag-green">تسویه‌شده</span>
                <span>سفارش‌های موفق فروشگاه</span>
            </div>
        </div>

        <!-- 2. Pending Orders (Urgent) -->
        <div class="dash-kpi-card <?= $pendingCount > 0 ? 'highlight-amber' : '' ?>">
            <div class="dash-kpi-header">
                <span class="dash-kpi-title">سفارش‌های معلق</span>
                <span class="dash-kpi-icon-pill" style="background:#FEF3C7; color:#B45309;">⏳</span>
            </div>
            <div class="dash-kpi-val-row">
                <span class="dash-kpi-val" style="color:<?= $pendingCount > 0 ? '#B45309' : 'inherit' ?>;"><?= toPersianDigits((string)$pendingCount) ?></span>
                <span class="dash-kpi-unit">سفارش</span>
            </div>
            <div class="dash-kpi-footer">
                <?php if ($pendingCount > 0): ?>
                    <span class="dash-tag-amber">اقدام فوری</span>
                    <span>نیازمند رسیدگی امروز</span>
                <?php else: ?>
                    <span class="dash-tag-green">به‌روز</span>
                    <span>سفارش معوقی وجود ندارد</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. Total Orders -->
        <div class="dash-kpi-card">
            <div class="dash-kpi-header">
                <span class="dash-kpi-title">کل سفارش‌ها</span>
                <span class="dash-kpi-icon-pill">📦</span>
            </div>
            <div class="dash-kpi-val-row">
                <span class="dash-kpi-val"><?= toPersianDigits((string)$orderCount) ?></span>
                <span class="dash-kpi-unit">سفارش</span>
            </div>
            <div class="dash-kpi-footer">
                <span class="dash-tag-blue"><?= toPersianDigits((string)$chartPeriodOrders) ?> سفارش</span>
                <span>در ۷ روز اخیر</span>
            </div>
        </div>

        <!-- 4. Active Products -->
        <div class="dash-kpi-card">
            <div class="dash-kpi-header">
                <span class="dash-kpi-title">محصولات فعال</span>
                <span class="dash-kpi-icon-pill">🛍️</span>
            </div>
            <div class="dash-kpi-val-row">
                <span class="dash-kpi-val"><?= toPersianDigits((string)$productCount) ?></span>
                <span class="dash-kpi-unit">مدل جوراب</span>
            </div>
            <div class="dash-kpi-footer">
                <a href="products.php" style="color:inherit; text-decoration:underline;">مدیریت کاتالوگ و تنوع‌ها ←</a>
            </div>
        </div>

        <!-- 5. Low Stock Alert -->
        <div class="dash-kpi-card <?= $lowStock > 0 ? 'highlight-rose' : '' ?>">
            <div class="dash-kpi-header">
                <span class="dash-kpi-title">کسری انبار</span>
                <span class="dash-kpi-icon-pill" style="background:#FEE2E2; color:#B91C1C;">⚠️</span>
            </div>
            <div class="dash-kpi-val-row">
                <span class="dash-kpi-val" style="color:<?= $lowStock > 0 ? '#DC2626' : 'inherit' ?>;"><?= toPersianDigits((string)$lowStock) ?></span>
                <span class="dash-kpi-unit">قلم بحرانی</span>
            </div>
            <div class="dash-kpi-footer">
                <?php if ($lowStock > 0): ?>
                    <span class="dash-tag-rose">زیر ۵ جفت</span>
                    <span>نیاز به شارژ موجودی</span>
                <?php else: ?>
                    <span class="dash-tag-green">موجودی کافی</span>
                    <span>انبار در وضعیت مطلوب</span>
                <?php endif; ?>
            </div>
        </div>

    </section>

    <!-- =================================================================== -->
    <!-- Two-Column Operational Layout                                        -->
    <!-- =================================================================== -->
    <div class="dash-main-grid">

        <!-- ================= Left Main Column (Analytics & Orders) ================= -->
        <div class="dash-col-main">

            <!-- Interactive Revenue Trend Chart -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">
                        <span>📈</span>
                        <span>روند فروش و سفارش‌ها</span>
                    </div>
                    <div class="dash-chart-controls">
                        <span style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">۷ روز اخیر</span>
                    </div>
                </div>
                <div class="dash-chart-body">
                    <div class="dash-chart-summary">
                        <div class="dash-stat-block">
                            <span class="dash-stat-lbl">مجموع فروش این دوره:</span>
                            <span class="dash-stat-num" id="chartTotalDisplay"><?= formatPrice($chartPeriodTotal) ?></span>
                        </div>
                        <div class="dash-stat-block">
                            <span class="dash-stat-lbl">تعداد سفارش‌ها:</span>
                            <span class="dash-stat-num" id="chartOrdersDisplay"><?= toPersianDigits((string)$chartPeriodOrders) ?> سفارش</span>
                        </div>
                    </div>

                    <!-- Live Dynamic SVG Trend Chart -->
                    <div class="dash-svg-wrap" id="dashChartWrap">
                        <div class="dash-chart-tooltip" id="dashChartTooltip"></div>
                        <svg viewBox="0 0 700 150" width="100%" height="100%" preserveAspectRatio="none" style="overflow: visible;">
                            <defs>
                                <linearGradient id="dashAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#C46C46" stop-opacity="0.32" />
                                    <stop offset="100%" stop-color="#C46C46" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>
                            <!-- Grid lines -->
                            <line x1="0" y1="30" x2="700" y2="30" stroke="#EFE9DF" stroke-dasharray="3,3" />
                            <line x1="0" y1="75" x2="700" y2="75" stroke="#EFE9DF" stroke-dasharray="3,3" />
                            <line x1="0" y1="120" x2="700" y2="120" stroke="#EFE9DF" stroke-dasharray="3,3" />

                            <!-- Gradient Fill Area -->
                            <path d="<?= $areaPathD ?>" fill="url(#dashAreaGrad)" />

                            <!-- Spline Stroke -->
                            <path d="<?= $linePathD ?>" fill="none" stroke="#C46C46" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />

                            <!-- Interactive Points -->
                            <?php foreach ($svgPoints as $p): ?>
                                <circle class="dash-chart-node" cx="<?= $p['x'] ?>" cy="<?= $p['y'] ?>" r="4.5" 
                                    fill="#FFF" stroke="#C46C46" stroke-width="2.5" 
                                    data-date="<?= e($p['pt']['date']) ?>" 
                                    data-val="<?= formatPrice($p['pt']['total']) ?>" 
                                    data-orders="<?= toPersianDigits((string)$p['pt']['count']) ?> سفارش" 
                                    style="cursor:pointer;" />
                            <?php endforeach; ?>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">
                        <span>📦</span>
                        <span>آخرین سفارش‌های دریافتی</span>
                    </div>
                    <a href="orders.php" class="dash-header-link">مشاهده همه سفارش‌ها ←</a>
                </div>

                <!-- Status Filter Pills -->
                <div class="dash-filter-row">
                    <div class="dash-filter-pills">
                        <button type="button" class="dash-pill-btn active" onclick="filterDashOrders('all', this)">همه (<?= count($recentOrders) ?>)</button>
                        <button type="button" class="dash-pill-btn" onclick="filterDashOrders('pending', this)">در انتظار بررسی</button>
                        <button type="button" class="dash-pill-btn" onclick="filterDashOrders('shipped', this)">ارسال شده</button>
                        <button type="button" class="dash-pill-btn" onclick="filterDashOrders('delivered', this)">تحویل شده</button>
                    </div>
                </div>

                <!-- 1. Desktop Modern Table (Visible >= 860px) -->
                <div class="dash-table-wrapper">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>کد سفارش</th>
                                <th>مشتری</th>
                                <th>روش پرداخت</th>
                                <th>مبلغ کل</th>
                                <th>وضعیت</th>
                                <th>زمان ثبت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="dashOrdersBody">
                            <?php foreach ($recentOrders as $o): 
                                $isC2c = ($o['payment_method'] ?? '') === 'card_to_card';
                                $customerName = trim($o['customer_name'] ?? 'مشتری');
                                $initials = function_exists('mb_substr') ? mb_substr($customerName, 0, 2, 'UTF-8') : substr($customerName, 0, 4);
                                $statusKey = $o['status'] ?? 'pending';
                                $statusText = $statusLabels[$statusKey] ?? $statusKey;
                                
                                $pillClass = 'amber';
                                if (in_array($statusKey, ['shipped', 'processing'], true)) $pillClass = 'blue';
                                elseif ($statusKey === 'delivered') $pillClass = 'emerald';
                                elseif ($statusKey === 'cancelled') $pillClass = 'rose';
                            ?>
                                <tr data-status="<?= e($statusKey) ?>">
                                    <td>
                                        <span class="dash-code-badge" onclick="copyDashCode('<?= e($o['order_code']) ?>', this)" title="کلیک برای کپی کد سفارش">
                                            <span><?= e($o['order_code']) ?></span>
                                            <span>📋</span>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dash-cust-chip">
                                            <div class="dash-cust-avatar"><?= e($initials) ?></div>
                                            <div class="dash-cust-meta">
                                                <span class="dash-cust-name"><?= e($customerName) ?></span>
                                                <span class="dash-cust-phone"><?= e($o['phone'] ?? '') ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($isC2c): ?>
                                            <span class="dash-pay-c2c">کارت‌به‌کارت</span>
                                        <?php else: ?>
                                            <span class="dash-pay-gateway">درگاه آنلاین</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= formatPrice($o['total']) ?></strong></td>
                                    <td>
                                        <span class="dash-status-pill <?= $pillClass ?>">
                                            <span class="dash-status-dot"></span>
                                            <span><?= e($statusText) ?></span>
                                        </span>
                                    </td>
                                    <td class="dash-date-cell"><?= toPersianDigits(date('Y/m/d H:i', strtotime($o['created_at']))) ?></td>
                                    <td>
                                        <a href="order_detail.php?id=<?= (int)$o['id'] ?>" class="dash-action-btn">بررسی فاکتور</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:28px; color:var(--text-muted);">
                                        هنوز سفارشی در سیستم ثبت نشده است.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 2. Adaptive Mobile Cards Stack (Visible < 860px) -->
                <div class="dash-cards-stack">
                    <?php foreach ($recentOrders as $o): 
                        $isC2c = ($o['payment_method'] ?? '') === 'card_to_card';
                        $customerName = trim($o['customer_name'] ?? 'مشتری');
                        $initials = function_exists('mb_substr') ? mb_substr($customerName, 0, 2, 'UTF-8') : substr($customerName, 0, 4);
                        $statusKey = $o['status'] ?? 'pending';
                        $statusText = $statusLabels[$statusKey] ?? $statusKey;
                        
                        $pillClass = 'amber';
                        if (in_array($statusKey, ['shipped', 'processing'], true)) $pillClass = 'blue';
                        elseif ($statusKey === 'delivered') $pillClass = 'emerald';
                        elseif ($statusKey === 'cancelled') $pillClass = 'rose';
                    ?>
                        <div class="dash-order-m-card" data-status="<?= e($statusKey) ?>">
                            <div class="dash-m-row-top">
                                <span class="dash-code-badge" onclick="copyDashCode('<?= e($o['order_code']) ?>', this)">
                                    <span><?= e($o['order_code']) ?></span> 📋
                                </span>
                                <span class="dash-status-pill <?= $pillClass ?>">
                                    <span class="dash-status-dot"></span>
                                    <span><?= e($statusText) ?></span>
                                </span>
                            </div>
                            <div class="dash-m-row-middle">
                                <div class="dash-cust-chip">
                                    <div class="dash-cust-avatar"><?= e($initials) ?></div>
                                    <div class="dash-cust-meta">
                                        <span class="dash-cust-name"><?= e($customerName) ?></span>
                                        <span class="dash-cust-phone"><?= e($o['phone'] ?? '') ?></span>
                                    </div>
                                </div>
                                <div style="text-align:left;">
                                    <div style="font-weight:800; font-size:0.86rem;"><?= formatPrice($o['total']) ?></div>
                                    <?php if ($isC2c): ?>
                                        <span class="dash-pay-c2c">کارت‌به‌کارت</span>
                                    <?php else: ?>
                                        <span class="dash-pay-gateway">درگاه</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="dash-m-row-bottom">
                                <span class="dash-date-cell"><?= toPersianDigits(date('Y/m/d H:i', strtotime($o['created_at']))) ?></span>
                                <a href="order_detail.php?id=<?= (int)$o['id'] ?>" class="dash-action-btn">مشاهده جزئیات ←</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

        </div>

        <!-- ================= Right Operational Column (Widgets) ================= -->
        <div class="dash-col-side">

            <!-- 1. Quick Admin Actions Hub -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">
                        <span>⚡</span>
                        <span>دسترسی‌های سریع ادمین</span>
                    </div>
                </div>
                <div class="dash-quick-grid">
                    <a href="product_edit.php" class="dash-tile">
                        <span class="dash-tile-icon">➕</span>
                        <span>محصول جدید</span>
                    </a>
                    <a href="pricing.php" class="dash-tile">
                        <span class="dash-tile-icon">🏷️</span>
                        <span>مدیریت قیمت‌ها</span>
                    </a>
                    <a href="appearance.php" class="dash-tile">
                        <span class="dash-tile-icon">🎨</span>
                        <span>ظاهر فروشگاه</span>
                    </a>
                    <a href="sms_patterns.php" class="dash-tile">
                        <span class="dash-tile-icon">💬</span>
                        <span>الگوهای پیامک</span>
                    </a>
                </div>
            </div>

            <!-- 2. Card to Card Instant Review Box -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">
                        <span>💳</span>
                        <span>بررسی فیش کارت‌به‌کارت</span>
                    </div>
                    <?php if ($latestC2cOrder): ?>
                        <span class="dash-tag-amber">نیازمند بررسی</span>
                    <?php endif; ?>
                </div>
                <div class="dash-c2c-body">
                    <?php if ($latestC2cOrder): ?>
                        <a href="order_detail.php?id=<?= (int)$latestC2cOrder['id'] ?>" class="dash-c2c-box" title="مشاهده فیش در سفارش">
                            <div class="dash-c2c-thumb">🧾</div>
                            <div class="dash-c2c-meta">
                                <div class="dash-c2c-amount"><?= formatPrice($latestC2cOrder['total']) ?></div>
                                <div class="dash-c2c-sub">سفارش <?= e($latestC2cOrder['order_code']) ?> • <?= e($latestC2cOrder['customer_name']) ?></div>
                            </div>
                            <span style="font-size:0.75rem; color:var(--brand-primary); font-weight:700;">بررسی 🔍</span>
                        </a>
                        <div class="dash-c2c-actions">
                            <a href="order_detail.php?id=<?= (int)$latestC2cOrder['id'] ?>" class="dash-btn-approve">ورود و تایید فیش ✓</a>
                            <a href="card_to_card_payments.php" class="dash-btn-inspect">همه فیش‌ها</a>
                        </div>
                    <?php else: ?>
                        <div style="text-align:center; padding:16px 8px; color:var(--text-muted); font-size:0.78rem;">
                            <span>✓</span> تمامی فیش‌های کارت‌به‌کارت بررسی شده‌اند.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 3. Critical Low Stock Inventory Widget -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">
                        <span>⚠️</span>
                        <span>هشدار موجودی انبار</span>
                    </div>
                    <?php if ($lowStock > 0): ?>
                        <span class="dash-tag-rose"><?= toPersianDigits((string)$lowStock) ?> بحرانی</span>
                    <?php endif; ?>
                </div>
                <div class="dash-stock-list">
                    <?php if (!empty($criticalStockItems)): ?>
                        <?php foreach ($criticalStockItems as $item): 
                            $pct = max(10, min(100, (int)$item['stock'] * 20));
                            $barColor = ($item['stock'] <= 2) ? '#EF4444' : '#F59E0B';
                        ?>
                            <div class="dash-stock-row">
                                <div class="dash-stock-info">
                                    <div class="dash-stock-name"><?= e($item['name'] ?? $item['title'] ?? '') ?></div>
                                    <div class="dash-stock-track">
                                        <div class="dash-stock-fill" style="width: <?= $pct ?>%; background: <?= $barColor ?>;"></div>
                                    </div>
                                </div>
                                <span class="dash-stock-badge" style="background:<?= $item['stock'] <= 2 ? '#FEE2E2' : '#FEF3C7' ?>; color:<?= $item['stock'] <= 2 ? '#B91C1C' : '#B45309' ?>;">
                                    <?= toPersianDigits((string)$item['stock']) ?> جفت
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align:center; padding:16px 8px; color:var(--text-muted); font-size:0.78rem;">
                            موجودی تمام محصولات فعال بالاتر از ۵ جفت است.
                        </div>
                    <?php endif; ?>
                </div>
                <div style="padding:10px 14px; background:#FAF8F5; border-top:1px solid var(--border-subtle); text-align:center;">
                    <a href="products.php" class="dash-action-btn" style="display:block; text-align:center;">ورود به انبارداری و کاتالوگ ←</a>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- ======================================================================= -->
<!-- Admin Command Palette Modal (Ctrl + K Live Search)                       -->
<!-- ======================================================================= -->
<div class="dash-cmd-backdrop" id="dashCmdModal" onclick="closeAdminCmdPalette(event)">
    <div class="dash-cmd-box" onclick="event.stopPropagation()">
        <div class="dash-cmd-input-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" class="dash-cmd-input" id="dashCmdInput" placeholder="جستجوی سفارش، کالا، تنظیمات یا دستورات..." oninput="handleDashCmdSearch(this.value)">
            <kbd class="dash-keycap" style="cursor:pointer;" onclick="closeAdminCmdPalette()">Esc</kbd>
        </div>
        <div class="dash-cmd-results" id="dashCmdResults">
            <!-- Default Quick Links -->
            <a href="orders.php" class="dash-cmd-item">
                <div class="dash-cmd-item-left"><span>📦</span><span>مدیریت همه سفارش‌ها</span></div>
                <span class="dash-cmd-tag">سفارش</span>
            </a>
            <a href="products.php" class="dash-cmd-item">
                <div class="dash-cmd-item-left"><span>🛍️</span><span>کاتالوگ و لیست محصولات</span></div>
                <span class="dash-cmd-tag">محصول</span>
            </a>
            <a href="appearance.php" class="dash-cmd-item">
                <div class="dash-cmd-item-left"><span>🎨</span><span>تنظیمات ظاهر، بنر و لندینگ پیج</span></div>
                <span class="dash-cmd-tag">تنظیمات</span>
            </a>
            <a href="sms_patterns.php" class="dash-cmd-item">
                <div class="dash-cmd-item-left"><span>💬</span><span>الگوهای پیامک کاوه‌نگار</span></div>
                <span class="dash-cmd-tag">پیامک</span>
            </a>
            <a href="finance_dashboard.php" class="dash-cmd-item">
                <div class="dash-cmd-item-left"><span>💰</span><span>داشبورد مالی و گزارش سود و زیان</span></div>
                <span class="dash-cmd-tag">مالی</span>
            </a>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- Toast Notification Container                                            -->
<!-- ======================================================================= -->
<div class="dash-toast" id="dashToast">
    <span id="dashToastIcon">✓</span>
    <span id="dashToastMsg">عملیات با موفقیت انجام شد.</span>
</div>

<script>
    // Copy Order Code to Clipboard
    function copyDashCode(code, element) {
        if (!navigator.clipboard) return;
        navigator.clipboard.writeText(code).then(() => {
            showDashToast('کد سفارش ' + code + ' در حافظه کپی شد!');
            const orig = element.innerHTML;
            element.innerHTML = 'کپی شد ✓';
            element.style.color = '#059669';
            setTimeout(() => {
                element.innerHTML = orig;
                element.style.color = '';
            }, 1400);
        });
    }

    // Toast Helper
    function showDashToast(message) {
        const toast = document.getElementById('dashToast');
        const msgSpan = document.getElementById('dashToastMsg');
        if (!toast || !msgSpan) return;
        msgSpan.textContent = message;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2400);
    }

    // Command Palette Modal
    function openAdminCmdPalette() {
        const modal = document.getElementById('dashCmdModal');
        if (!modal) return;
        modal.classList.add('open');
        setTimeout(() => {
            const input = document.getElementById('dashCmdInput');
            if (input) input.focus();
        }, 50);
    }

    function closeAdminCmdPalette() {
        const modal = document.getElementById('dashCmdModal');
        if (modal) modal.classList.remove('open');
    }

    // Keyboard Shortcut (Ctrl+K or Cmd+K)
    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            openAdminCmdPalette();
        }
        if (e.key === 'Escape') {
            closeAdminCmdPalette();
        }
    });

    // Live search via ajax/admin_search.php
    let cmdDebounceTimer = null;
    function handleDashCmdSearch(query) {
        if (cmdDebounceTimer) clearTimeout(cmdDebounceTimer);
        const resultsEl = document.getElementById('dashCmdResults');
        if (!query.trim()) {
            return;
        }
        cmdDebounceTimer = setTimeout(() => {
            fetch('/ajax/admin_search.php?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    if (!data || !data.ok) return;
                    let html = '';
                    (data.orders || []).forEach(o => {
                        html += `<a href="${o.url}" class="dash-cmd-item">
                            <div class="dash-cmd-item-left"><span>📦</span><span>${o.title} (${o.sub || ''})</span></div>
                            <span class="dash-cmd-tag">سفارش</span>
                        </a>`;
                    });
                    (data.products || []).forEach(p => {
                        html += `<a href="${p.url}" class="dash-cmd-item">
                            <div class="dash-cmd-item-left"><span>🧦</span><span>${p.title}</span></div>
                            <span class="dash-cmd-tag">محصول</span>
                        </a>`;
                    });
                    (data.pages || []).forEach(pg => {
                        html += `<a href="${pg.url}" class="dash-cmd-item">
                            <div class="dash-cmd-item-left"><span>⚙️</span><span>${pg.title}</span></div>
                            <span class="dash-cmd-tag">صفحه</span>
                        </a>`;
                    });
                    if (html) {
                        resultsEl.innerHTML = html;
                    }
                })
                .catch(() => {});
        }, 180);
    }

    // Filter Table Rows
    function filterDashOrders(status, btn) {
        document.querySelectorAll('.dash-pill-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const rows = document.querySelectorAll('#dashOrdersBody tr');
        rows.forEach(r => {
            if (status === 'all' || r.getAttribute('data-status') === status) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });

        const mCards = document.querySelectorAll('.dash-order-m-card');
        mCards.forEach(c => {
            if (status === 'all' || c.getAttribute('data-status') === status) {
                c.style.display = '';
            } else {
                c.style.display = 'none';
            }
        });
    }

    // Chart Node Tooltip Interactivity
    document.addEventListener('DOMContentLoaded', () => {
        const wrap = document.getElementById('dashChartWrap');
        const tooltip = document.getElementById('dashChartTooltip');
        const nodes = document.querySelectorAll('.dash-chart-node');

        if (!wrap || !tooltip) return;

        nodes.forEach(node => {
            node.addEventListener('mouseenter', () => {
                const date = node.getAttribute('data-date');
                const val = node.getAttribute('data-val');
                const orders = node.getAttribute('data-orders');
                tooltip.innerHTML = `<strong>${date}</strong>: ${val} <span style="opacity:0.75; font-size:0.7rem;">(${orders})</span>`;

                const rect = node.getBoundingClientRect();
                const wrapRect = wrap.getBoundingClientRect();
                const left = rect.left - wrapRect.left + (rect.width / 2);
                const top = rect.top - wrapRect.top;

                tooltip.style.left = `${left}px`;
                tooltip.style.top = `${top}px`;
                tooltip.style.opacity = '1';
            });

            node.addEventListener('mouseleave', () => {
                tooltip.style.opacity = '0';
            });
        });
    });
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
