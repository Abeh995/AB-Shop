<?php
/**
 * Modern Admin Orders View
 * Connected with live store database, bento KPI metrics, filterable table,
 * adaptive mobile order cards stack, bottom sheet dossier, and postal tracking.
 */
require APP_ROOT . '/views/admin/layout/header.php';

// Prepare lightweight JSON map for instant client-side dossier drawer & modal interactions
$ordersJsonMap = [];
foreach ($ordersData['orders'] as $ord) {
    $receiptUrl = !empty($ord['card_to_card_receipt'])
        ? 'order_receipt.php?id=' . (int)$ord['id']
        : '';

    $cogs = 0;
    foreach ($ord['items'] ?? [] as $it) {
        $cogs += ((int)($it['unit_cost_price'] ?? $it['unit_price'])) * (int)$it['quantity'];
    }
    $grossProfit = max(0, (int)$ord['total'] - $cogs - (int)$ord['shipping_cost']);

    $ordersJsonMap[(int)$ord['id']] = [
        'id'                  => (int)$ord['id'],
        'order_code'          => $ord['order_code'],
        'customer_name'       => $ord['customer_name'],
        'phone'               => $ord['phone'],
        'email'               => $ord['email'] ?? '',
        'province'            => $ord['province'],
        'city'                => $ord['city'],
        'address'             => $ord['address'],
        'postal_code'         => $ord['postal_code'] ?? '',
        'notes'               => $ord['notes'] ?? '',
        'subtotal'            => (int)$ord['subtotal'],
        'discount_total'      => (int)$ord['discount_total'],
        'shipping_cost'       => (int)$ord['shipping_cost'],
        'shipping_method'     => $ord['shipping_method_name'] ?: 'پست پیشتاز',
        'total'               => (int)$ord['total'],
        'coupon_code'         => $ord['coupon_code'] ?? '',
        'payment_method'      => $ord['payment_method'],
        'payment_status'      => $ord['payment_status'],
        'status'              => $ord['status'],
        'tracking_code'       => $ord['tracking_code'] ?? '',
        'receipt_img'         => $receiptUrl,
        'receipt_time'        => !empty($ord['card_to_card_submitted_at']) ? appDateTime($ord['card_to_card_submitted_at'], 'full_shamsi') : appDateTime($ord['created_at'], 'full_shamsi'),
        'created_at'          => $ord['created_at'],
        'created_at_persian'  => appDateTime($ord['created_at'], 'full_shamsi'),
        'cost_price'          => $cogs,
        'gross_profit'        => $grossProfit,
        'items'               => array_map(function($it) {
            return [
                'name'    => $it['product_name'] . ($it['variant_label'] ? ' (' . $it['variant_label'] . ')' : ''),
                'qty'     => (int)$it['quantity'],
                'price'   => (int)$it['unit_price'],
                'total'   => (int)$it['line_total'],
                'is_gift' => false,
            ];
        }, $ord['items'] ?? []),
        'has_gift'            => !empty($ord['gifts']),
        'gifts'               => array_map(function($g) {
            return [
                'name'    => $g['name'],
                'qty'     => (int)$g['quantity'],
                'role'    => $g['role'],
                'is_gift' => true,
            ];
        }, $ord['gifts'] ?? []),
    ];
}
?>

<main class="dash-workspace">

    <!-- =================================================================== -->
    <!-- 4 Bento Metric Cards (Key Store Stream Insights)                     -->
    <!-- =================================================================== -->
    <section class="orders-kpi-grid">
        <!-- 1. Pending Orders (Urgent Action) -->
        <div class="orders-kpi-card <?= $orderStats['pending_count'] > 0 ? 'highlight-amber' : '' ?>" onclick="location.href='?status=pending'">
            <div class="orders-kpi-header">
                <span class="orders-kpi-title">نیازمند بررسی / فیش</span>
                <span class="orders-kpi-icon-pill" style="background:#FEF3C7; color:#B45309;">⏳</span>
            </div>
            <div class="orders-kpi-val-row">
                <span class="orders-kpi-val" style="color:<?= $orderStats['pending_count'] > 0 ? '#B45309' : 'inherit' ?>;"><?= toPersianDigits((string)$orderStats['pending_count']) ?></span>
                <span class="orders-kpi-unit">سفارش</span>
            </div>
            <div class="orders-kpi-footer">
                <?php if ($orderStats['pending_count'] > 0): ?>
                    <span class="pulse-badge">اقدام فوری</span>
                    <span>رسیدگی به سفارش‌های معلق</span>
                <?php else: ?>
                    <span style="color:#059669; font-weight:700;">به‌روز</span>
                    <span>سفارش معوقی وجود ندارد</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. Today / Total Sales -->
        <div class="orders-kpi-card highlight-green">
            <div class="orders-kpi-header">
                <span class="orders-kpi-title">خالص فروش موفق</span>
                <span class="orders-kpi-icon-pill" style="background:#ECFDF5; color:#047857;">💰</span>
            </div>
            <div class="orders-kpi-val-row">
                <span class="orders-kpi-val"><?= formatPrice($orderStats['total_sales']) ?></span>
            </div>
            <div class="orders-kpi-footer">
                <span style="color:#059669; font-weight:700;">تسویه‌شده</span>
                <span>فروشگاه AB Socks</span>
            </div>
        </div>

        <!-- 3. Total Orders -->
        <div class="orders-kpi-card highlight-blue" onclick="location.href='orders.php'">
            <div class="orders-kpi-header">
                <span class="orders-kpi-title">کل سفارش‌های ثبت‌شده</span>
                <span class="orders-kpi-icon-pill" style="background:#EFF6FF; color:#1D4ED8;">📦</span>
            </div>
            <div class="orders-kpi-val-row">
                <span class="orders-kpi-val"><?= toPersianDigits((string)$orderStats['total_orders']) ?></span>
                <span class="orders-kpi-unit">سفارش</span>
            </div>
            <div class="orders-kpi-footer">
                <span>از ابتدای راه‌اندازی فروشگاه</span>
            </div>
        </div>

        <!-- 4. Packaging / Processing -->
        <div class="orders-kpi-card" onclick="location.href='?status=processing'">
            <div class="orders-kpi-header">
                <span class="orders-kpi-title">در حال بسته‌بندی</span>
                <span class="orders-kpi-icon-pill" style="background:#F5F3FF; color:#6D28D9;">🎁</span>
            </div>
            <div class="orders-kpi-val-row">
                <span class="orders-kpi-val"><?= toPersianDigits((string)$orderStats['processing_count']) ?></span>
                <span class="orders-kpi-unit">بسته</span>
            </div>
            <div class="orders-kpi-footer">
                <span>آماده صدور بارکد و ارسال</span>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- Filter, Search and Controls Hub                                      -->
    <!-- =================================================================== -->
    <div class="orders-control-card">
        <!-- Status Tabs Bar -->
        <nav class="orders-status-nav" aria-label="فیلتر وضعیت سفارشات">
            <a href="?<?= http_build_query(array_merge($filters, ['status' => '', 'page' => 1])) ?>" class="status-nav-tab <?= $filters['status'] === '' ? 'active' : '' ?>">
                <span>همه سفارش‌ها</span>
                <span class="status-nav-count"><?= toPersianDigits((string)$statusCounts['all']) ?></span>
            </a>
            <a href="?<?= http_build_query(array_merge($filters, ['status' => 'pending', 'page' => 1])) ?>" class="status-nav-tab <?= $filters['status'] === 'pending' ? 'active' : '' ?>">
                <span>در انتظار بررسی / فیش</span>
                <span class="status-nav-count <?= $statusCounts['pending'] > 0 ? 'alert' : '' ?>"><?= toPersianDigits((string)$statusCounts['pending']) ?></span>
            </a>
            <a href="?<?= http_build_query(array_merge($filters, ['status' => 'processing', 'page' => 1])) ?>" class="status-nav-tab <?= $filters['status'] === 'processing' ? 'active' : '' ?>">
                <span>در حال بسته‌بندی</span>
                <span class="status-nav-count"><?= toPersianDigits((string)$statusCounts['processing']) ?></span>
            </a>
            <a href="?<?= http_build_query(array_merge($filters, ['status' => 'shipped', 'page' => 1])) ?>" class="status-nav-tab <?= $filters['status'] === 'shipped' ? 'active' : '' ?>">
                <span>ارسال شده با پست</span>
                <span class="status-nav-count"><?= toPersianDigits((string)$statusCounts['shipped']) ?></span>
            </a>
            <a href="?<?= http_build_query(array_merge($filters, ['status' => 'delivered', 'page' => 1])) ?>" class="status-nav-tab <?= $filters['status'] === 'delivered' ? 'active' : '' ?>">
                <span>تحویل داده شده</span>
                <span class="status-nav-count"><?= toPersianDigits((string)$statusCounts['delivered']) ?></span>
            </a>
            <a href="?<?= http_build_query(array_merge($filters, ['status' => 'cancelled', 'page' => 1])) ?>" class="status-nav-tab <?= $filters['status'] === 'cancelled' ? 'active' : '' ?>">
                <span>لغو شده</span>
                <span class="status-nav-count"><?= toPersianDigits((string)$statusCounts['cancelled']) ?></span>
            </a>
        </nav>

        <!-- Search & Dropdown Filters Bar -->
        <form method="get" action="orders.php" class="orders-filter-bar">
            <?php if ($filters['status']): ?>
                <input type="hidden" name="status" value="<?= e($filters['status']) ?>">
            <?php endif; ?>

            <div class="filter-group-right">
                <div class="search-input-wrap">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" id="orderSearchInput" name="search" class="orders-search-input" value="<?= e($filters['search']) ?>" placeholder="جستجو بر اساس کد سفارش، نام مشتری، موبایل، شهر، یا کد رهگیری پستی...">
                </div>

                <select class="select-filter" name="payment_method" onchange="this.form.submit()">
                    <option value="">روش پرداخت: همه</option>
                    <option value="card_to_card" <?= $filters['payment_method'] === 'card_to_card' ? 'selected' : '' ?>>کارت‌به‌کارت</option>
                    <option value="zarinpal" <?= $filters['payment_method'] === 'zarinpal' ? 'selected' : '' ?>>درگاه آنلاین شتاب</option>
                </select>

                <select class="select-filter" name="payment_status" onchange="this.form.submit()">
                    <option value="">وضعیت پرداخت: همه</option>
                    <option value="paid" <?= $filters['payment_status'] === 'paid' ? 'selected' : '' ?>>پرداخت شده</option>
                    <option value="unpaid" <?= $filters['payment_status'] === 'unpaid' ? 'selected' : '' ?>>در انتظار پرداخت</option>
                    <option value="failed" <?= $filters['payment_status'] === 'failed' ? 'selected' : '' ?>>ناموفق</option>
                </select>
            </div>

            <div class="filter-group-left">
                <select class="select-filter" name="date_range" onchange="this.form.submit()">
                    <option value="">بازه زمانی: همه</option>
                    <option value="today" <?= $filters['date_range'] === 'today' ? 'selected' : '' ?>>امروز</option>
                    <option value="3days" <?= $filters['date_range'] === '3days' ? 'selected' : '' ?>>۳ روز اخیر</option>
                    <option value="this_week" <?= $filters['date_range'] === 'this_week' ? 'selected' : '' ?>>هفته جاری</option>
                    <option value="this_month" <?= $filters['date_range'] === 'this_month' ? 'selected' : '' ?>>ماه جاری</option>
                </select>

                <a href="orders.php" class="btn-secondary" title="بازنشانی فیلترها">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    <span>ریست</span>
                </a>
            </div>
        </form>
    </div>

    <!-- =================================================================== -->
    <!-- Orders Master Data Card (Table for Desktop + Stack for Mobile)      -->
    <!-- =================================================================== -->
    <div class="orders-table-card">

        <?php if (empty($ordersData['orders'])): ?>
            <div class="orders-empty-state-card" style="background:#FFF; border:1px solid var(--border-card); border-radius:var(--radius-lg); padding:50px 20px; text-align:center;">
                <div style="font-size:2.2rem; margin-bottom:10px;">🔍</div>
                <div style="font-weight:700; font-size:1rem; color:var(--text-primary); margin-bottom:6px;">سفارشی با این مشخصات یافت نشد</div>
                <div style="font-size:0.78rem; color:var(--text-muted); margin-bottom:14px;">می‌توانید فیلترها را ریست کنید یا عبارت جستجو را تغییر دهید.</div>
                <a href="orders.php" class="btn-row-action" style="display:inline-block; padding:7px 16px;">نمایش همه سفارش‌ها</a>
            </div>
        <?php else: ?>

            <!-- 1. Desktop Orders Grid Table (Hidden < 860px) -->
            <div class="orders-table-wrapper">
                <table class="orders-grid-table">
                    <thead>
                        <tr>
                            <th style="width: 38px; text-align: center;">
                                <div class="custom-checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                                    <input type="checkbox">
                                </div>
                            </th>
                            <th>کد سفارش</th>
                            <th>مشتری و مقصد</th>
                            <th>اقلام سفارش</th>
                            <th>مبلغ کل</th>
                            <th>روش و وضعیت پرداخت</th>
                            <th>وضعیت سفارش</th>
                            <th>کد رهگیری پستی</th>
                            <th style="text-align: left;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        <?php foreach ($ordersData['orders'] as $o):
                            $customerName = trim($o['customer_name'] ?: 'مشتری بدون نام');
                            $initials = function_exists('mb_substr') ? mb_substr($customerName, 0, 2, 'UTF-8') : substr($customerName, 0, 4);
                            
                            $statusKey = $o['status'] ?? 'pending';
                            $statusTitle = $statusLabels[$statusKey] ?? $statusKey;
                            $statusClass = 'amber';
                            if ($statusKey === 'processing') $statusClass = 'purple';
                            elseif ($statusKey === 'shipped') $statusClass = 'blue';
                            elseif ($statusKey === 'delivered') $statusClass = 'emerald';
                            elseif ($statusKey === 'cancelled') $statusClass = 'rose';

                            $isC2c = ($o['payment_method'] === 'card_to_card');
                            $isPaid = ($o['payment_status'] === 'paid');
                        ?>
                        <tr id="row-<?= (int)$o['id'] ?>" onclick="openOrderDrawer(<?= (int)$o['id'] ?>)">
                            <td style="text-align: center;" onclick="event.stopPropagation()">
                                <div class="custom-checkbox" data-order-id="<?= (int)$o['id'] ?>" onclick="toggleSelectRow(<?= (int)$o['id'] ?>)">
                                    <input type="checkbox">
                                </div>
                            </td>
                            <td>
                                <span class="order-code-badge" onclick="event.stopPropagation(); copyCode('<?= e($o['order_code']) ?>', 'کد سفارش')" title="کپی کد سفارش">
                                    <span><?= e($o['order_code']) ?></span>
                                    <span>📋</span>
                                </span>
                                <span class="order-date-sub"><?= toPersianDigits(date('Y/m/d H:i', strtotime($o['created_at']))) ?></span>
                            </td>
                            <td>
                                <div class="cust-cell-wrap">
                                    <div class="cust-avatar"><?= e($initials) ?></div>
                                    <div class="cust-info-col">
                                        <span class="cust-name"><?= e($customerName) ?></span>
                                        <div class="cust-meta">
                                            <span class="cust-phone" dir="ltr"><?= e($o['phone']) ?></span>
                                            <span class="cust-city-tag"><?= e($o['city']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="order-items-preview">
                                    <?php 
                                    $itemsCount = count($o['items'] ?? []);
                                    $firstTwo = array_slice($o['items'] ?? [], 0, 2);
                                    foreach ($firstTwo as $it): 
                                    ?>
                                        <div class="item-chip">
                                            <span class="item-qty-tag">×<?= toPersianDigits((string)$it['quantity']) ?></span>
                                            <span><?= e($it['product_name']) ?><?= $it['variant_label'] ? ' (' . e($it['variant_label']) . ')' : '' ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if ($itemsCount > 2): ?>
                                        <span style="font-size:0.68rem; color:var(--brand-primary); font-weight:700;">+ <?= toPersianDigits((string)($itemsCount - 2)) ?> قلم دیگر...</span>
                                    <?php endif; ?>
                                    <?php if (!empty($o['has_gift'])): ?>
                                        <span class="item-gift-badge">🎁 هدیه بعد از سبد</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:800; font-size:0.86rem;"><?= formatPrice($o['total']) ?></div>
                                <?php if ($o['discount_total'] > 0): ?>
                                    <div style="font-size:0.68rem; color:#059669;">تخفیف: <?= formatPrice($o['discount_total']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isC2c): ?>
                                    <?php if ($isPaid): ?>
                                        <span class="pay-pill-c2c-approved">✓ کارت‌به‌کارت تاییدشده</span>
                                    <?php else: ?>
                                        <span class="pay-pill-c2c-pending" onclick="event.stopPropagation(); openOrderDrawer(<?= (int)$o['id'] ?>); openReceiptModal();" title="مشاهده و بررسی فیش">🧾 فیش نیازمند تایید</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="pay-pill-gateway">✓ درگاه آنلاین</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="dash-status-pill <?= $statusClass ?>">
                                    <span class="dash-status-dot"></span>
                                    <span><?= e($statusTitle) ?></span>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($o['tracking_code'])): ?>
                                    <span class="tracking-badge" onclick="event.stopPropagation(); copyCode('<?= e($o['tracking_code']) ?>', 'کد رهگیری پست')" title="کلیک برای کپی کد رهگیری">
                                        <span dir="ltr"><?= e(mb_substr($o['tracking_code'], 0, 10)) ?>...</span>
                                        <span>📋</span>
                                    </span>
                                <?php elseif ($statusKey === 'processing'): ?>
                                    <button type="button" class="btn-row-action" style="font-size:0.68rem; padding:2px 6px;" onclick="event.stopPropagation(); openOrderDrawer(<?= (int)$o['id'] ?>);">+ درج بارکد</button>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:0.75rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td onclick="event.stopPropagation()">
                                <div class="actions-cell">
                                    <button type="button" class="btn-row-action" onclick="openOrderDrawer(<?= (int)$o['id'] ?>)">بررسی پرونده</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- 2. Adaptive Mobile Cards Stack (< 860px) -->
            <div class="orders-cards-stack" id="ordersCardsStack">
                <?php foreach ($ordersData['orders'] as $o):
                    $customerName = trim($o['customer_name'] ?: 'مشتری بدون نام');
                    $initials = function_exists('mb_substr') ? mb_substr($customerName, 0, 2, 'UTF-8') : substr($customerName, 0, 4);
                    
                    $statusKey = $o['status'] ?? 'pending';
                    $statusTitle = $statusLabels[$statusKey] ?? $statusKey;
                    $statusClass = 'amber';
                    if ($statusKey === 'processing') $statusClass = 'purple';
                    elseif ($statusKey === 'shipped') $statusClass = 'blue';
                    elseif ($statusKey === 'delivered') $statusClass = 'emerald';
                    elseif ($statusKey === 'cancelled') $statusClass = 'rose';

                    $isC2c = ($o['payment_method'] === 'card_to_card');
                    $isPaid = ($o['payment_status'] === 'paid');
                ?>
                <div class="orders-mobile-card" id="mcard-<?= (int)$o['id'] ?>" onclick="openOrderDrawer(<?= (int)$o['id'] ?>)">
                    <!-- Top Row -->
                    <div class="m-card-top-row">
                        <div class="m-card-top-right">
                            <div class="custom-checkbox" data-order-id="<?= (int)$o['id'] ?>" onclick="event.stopPropagation(); toggleSelectRow(<?= (int)$o['id'] ?>)">
                                <input type="checkbox">
                            </div>
                            <span class="order-code-badge" onclick="event.stopPropagation(); copyCode('<?= e($o['order_code']) ?>', 'کد سفارش')">
                                <span><?= e($o['order_code']) ?></span>
                                <span style="font-size:0.72rem;">📋</span>
                            </span>
                            <span class="m-card-rel-time"><?= toPersianDigits(date('m/d H:i', strtotime($o['created_at']))) ?></span>
                        </div>
                        <div class="m-card-top-left">
                            <span class="dash-status-pill <?= $statusClass ?>">
                                <span class="dash-status-dot"></span>
                                <span><?= e($statusTitle) ?></span>
                            </span>
                        </div>
                    </div>

                    <!-- Customer & Direct Contact Row -->
                    <div class="m-card-customer-row">
                        <div class="m-customer-meta">
                            <div class="cust-avatar"><?= e($initials) ?></div>
                            <div class="m-customer-text">
                                <div class="m-customer-name"><?= e($customerName) ?></div>
                                <div class="m-customer-location">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <span><?= e($o['city']) ?> (<?= e($o['province']) ?>)</span>
                                </div>
                            </div>
                        </div>
                        <div class="m-customer-actions" onclick="event.stopPropagation()">
                            <a href="tel:<?= e($o['phone']) ?>" class="btn-m-contact" title="تماس مستقیم با مشتری">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </a>
                            <a href="sms:<?= e($o['phone']) ?>" class="btn-m-contact" title="ارسال پیامک">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Items Summary Chips -->
                    <div class="m-card-items-wrap">
                        <?php foreach ($o['items'] as $it): ?>
                            <div class="m-order-item-chip">
                                <span class="m-item-qty">×<?= toPersianDigits((string)$it['quantity']) ?></span>
                                <span><?= e($it['product_name']) ?><?= $it['variant_label'] ? ' (' . e($it['variant_label']) . ')' : '' ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!empty($o['has_gift'])): ?>
                            <span class="m-order-gift-chip">🎁 اشانتیون بعد از سبد</span>
                        <?php endif; ?>
                    </div>

                    <!-- Postal Tracking Row -->
                    <?php if (!empty($o['tracking_code'])): ?>
                        <div class="m-card-tracking-row" onclick="event.stopPropagation(); copyCode('<?= e($o['tracking_code']) ?>', 'کد رهگیری پست')">
                            <span class="m-track-label">📦 کد رهگیری پستی:</span>
                            <span class="m-track-val" dir="ltr"><?= e($o['tracking_code']) ?></span>
                            <span class="m-track-copy">📋 کپی</span>
                        </div>
                    <?php elseif ($statusKey === 'processing'): ?>
                        <div class="m-card-tracking-add" onclick="event.stopPropagation(); openOrderDrawer(<?= (int)$o['id'] ?>);">
                            <span>+ ثبت بارکد رهگیری پست برای این مرسوله</span>
                        </div>
                    <?php endif; ?>

                    <!-- Price & Action Row -->
                    <div class="m-card-bottom-row">
                        <div class="m-card-finance">
                            <div class="m-card-price"><?= formatPrice($o['total']) ?></div>
                            <div class="m-card-pay-badge">
                                <?php if ($isC2c): ?>
                                    <?php if ($isPaid): ?>
                                        <span class="m-pay-pill pay-c2c-approved">✓ کارت‌به‌کارت تاییدشده</span>
                                    <?php else: ?>
                                        <span class="m-pay-pill pay-c2c-pending" onclick="event.stopPropagation(); openOrderDrawer(<?= (int)$o['id'] ?>); openReceiptModal();">🧾 فیش نیازمند بررسی</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="m-pay-pill pay-gateway">✓ درگاه آنلاین</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button type="button" class="btn-m-dossier" onclick="openOrderDrawer(<?= (int)$o['id'] ?>)">
                            <span>مشاهده پرونده</span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Table Footer / Server-side Pagination -->
            <div class="orders-table-footer">
                <div>
                    نمایش <?= toPersianDigits((string)count($ordersData['orders'])) ?> سفارش از مجموع <?= toPersianDigits((string)$ordersData['total']) ?>
                </div>

                <?php if ($ordersData['pages'] > 1): ?>
                    <div class="pagination-controls">
                        <?php if ($ordersData['page'] > 1): ?>
                            <a href="?<?= http_build_query(array_merge($filters, ['page' => $ordersData['page'] - 1])) ?>" class="page-btn">«</a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $ordersData['pages']; $p++): ?>
                            <?php if ($p === $ordersData['page']): ?>
                                <span class="page-btn active"><?= toPersianDigits((string)$p) ?></span>
                            <?php elseif ($p <= 3 || $p >= $ordersData['pages'] - 1 || abs($p - $ordersData['page']) <= 1): ?>
                                <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>" class="page-btn"><?= toPersianDigits((string)$p) ?></a>
                            <?php elseif ($p === 4 || $p === $ordersData['pages'] - 2): ?>
                                <span class="page-btn" style="border:none; background:transparent;">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($ordersData['page'] < $ordersData['pages']): ?>
                            <a href="?<?= http_build_query(array_merge($filters, ['page' => $ordersData['page'] + 1])) ?>" class="page-btn">»</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>

</main>

<!-- ======================================================================= -->
<!-- Floating Bulk Actions Dock (Triggered when 1+ checkboxes selected)       -->
<!-- ======================================================================= -->
<div class="bulk-actions-dock" id="bulkActionsDock">
    <div class="bulk-count"><span id="selectedCountText">۰</span> سفارش انتخاب شد</div>
    <div class="bulk-divider"></div>

    <form method="post" action="orders.php" id="bulkActionForm" style="display:flex; align-items:center; gap:8px;">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="bulk_status">
        <input type="hidden" name="order_ids" id="bulkOrderIds" value="">
        <input type="hidden" name="bulk_new_status" id="bulkNewStatus" value="">

        <button type="button" class="btn-bulk-action btn-bulk-primary" onclick="submitBulkStatus('processing')">
            <span>تغییر وضعیت به بسته‌بندی</span>
        </button>
        <button type="button" class="btn-bulk-action" onclick="submitBulkStatus('shipped')">
            <span>علامت‌گذاری ارسال‌شده</span>
        </button>
    </form>

    <button type="button" class="btn-bulk-action" onclick="clearSelection()" style="background:transparent; border-color:transparent; opacity:0.8;">
        <span>انصراف ✕</span>
    </button>
</div>

<!-- ======================================================================= -->
<!-- Order Detail Dossier (Slide Drawer on Desktop / Bottom Sheet on Mobile) -->
<!-- ======================================================================= -->
<div class="drawer-backdrop" id="orderDrawerBackdrop" onclick="closeOrderDrawer()">
    <div class="order-drawer" onclick="event.stopPropagation()">
        
        <!-- Mobile Bottom Sheet Handle -->
        <div class="bottom-sheet-handle-bar" onclick="closeOrderDrawer()" title="برای بستن لمس کنید یا به پایین بکشید">
            <div class="bottom-sheet-handle"></div>
        </div>

        <!-- Drawer Header -->
        <div class="drawer-header">
            <div class="drawer-header-title">
                <span class="order-code-badge" id="dOrderCode">ORD-0000</span>
                <span class="dash-status-pill amber" id="dStatusPill">
                    <span class="dash-status-dot"></span>
                    <span id="dStatusText">در انتظار بررسی</span>
                </span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <button type="button" class="btn-dash-action" onclick="window.print()" title="چاپ فاکتور این سفارش">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span>چاپ فاکتور</span>
                </button>
                <button type="button" class="drawer-close-btn" onclick="closeOrderDrawer()" title="بستن (Esc)">✕</button>
            </div>
        </div>

        <!-- Drawer Body -->
        <div class="drawer-body">
            
            <!-- Order Progress Stepper -->
            <div class="order-stepper" id="dStepper">
                <div class="step-item completed">
                    <div class="step-dot">✓</div>
                    <span class="step-label">ثبت سفارش</span>
                </div>
                <div class="step-item current" id="stepPay">
                    <div class="step-dot">۲</div>
                    <span class="step-label">تایید پرداخت</span>
                </div>
                <div class="step-item" id="stepPack">
                    <div class="step-dot">۳</div>
                    <span class="step-label">بسته‌بندی</span>
                </div>
                <div class="step-item" id="stepShip">
                    <div class="step-dot">۴</div>
                    <span class="step-label">تحویل به پست</span>
                </div>
                <div class="step-item" id="stepDeliver">
                    <div class="step-dot">۵</div>
                    <span class="step-label">تحویل مشتری</span>
                </div>
            </div>

            <!-- Card to Card Verification Widget (Shown if payment is C2C) -->
            <div class="drawer-section-card" id="dC2cSection" style="display:none; border-color:#FCD34D; background:#FFFDF8;">
                <div class="drawer-section-title" style="color:#B45309;">
                    <span>🧾 بررسی و تایید فیش کارت‌به‌کارت</span>
                    <span style="font-size:0.72rem; font-weight:600; color:#B45309;">واریز به حساب فروشگاه</span>
                </div>
                <div class="drawer-receipt-preview">
                    <div class="receipt-thumb-wrap" onclick="openReceiptModal()">
                        <img id="dReceiptImg" src="" alt="فیش واریزی">
                        <div class="receipt-zoom-overlay">🔍 بزرگنمایی</div>
                    </div>
                    <div class="receipt-info-meta">
                        <div style="font-weight:700; font-size:0.84rem;" id="dReceiptAmount">۰ تومان</div>
                        <div style="font-size:0.7rem; color:var(--text-muted);" id="dReceiptDate">—</div>
                        <div style="display:flex; gap:8px; margin-top:6px;">
                            <form method="post" action="orders.php" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="verify_c2c">
                                <input type="hidden" name="id" class="drawer-hidden-order-id" value="">
                                <input type="hidden" name="decision" value="approved">
                                <button type="submit" class="btn-verify-receipt">تایید پرداخت و فیش ✓</button>
                            </form>
                            <form method="post" action="orders.php" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="verify_c2c">
                                <input type="hidden" name="id" class="drawer-hidden-order-id" value="">
                                <input type="hidden" name="decision" value="rejected">
                                <button type="submit" class="btn-reject-receipt" onclick="return confirm('آیا از رد فیش اطمینان دارید؟');">رد فیش ✕</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer & Shipping Information -->
            <div class="drawer-section-card">
                <div class="drawer-section-title">
                    <span>👤 اطلاعات خریدار و آدرس گیرنده</span>
                    <span style="font-size:0.72rem; color:var(--text-muted);" id="dOrderTime">—</span>
                </div>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                    <div>
                        <div style="font-size:0.7rem; color:var(--text-muted);">نام تحویل‌گیرنده:</div>
                        <div style="font-weight:700;" id="dCustomerName">—</div>
                    </div>
                    <div>
                        <div style="font-size:0.7rem; color:var(--text-muted);">شماره تماس همراه:</div>
                        <div style="font-weight:700; font-family:monospace; direction:ltr; text-align:right;" id="dCustomerPhone">—</div>
                    </div>
                </div>

                <!-- Postal Label Box with 1-Click Copy -->
                <div class="postal-address-box">
                    <button type="button" class="btn-copy-address" onclick="copyPostalLabel()" title="کپی کل متن آدرس جهت برچسب پستی">
                        <span>📋</span>
                        <span>کپی برای برچسب پستی</span>
                    </button>
                    <div style="font-weight:600; margin-bottom:4px;" id="dProvinceCity">—</div>
                    <div id="dFullAddress">—</div>
                    <div style="margin-top:4px; font-family:monospace; color:var(--text-muted);">کد پستی: <span id="dPostalCode">—</span></div>
                </div>

                <!-- Customer Order Notes (If exists) -->
                <div id="dCustomerNoteBox" style="margin-top:10px; font-size:0.74rem; background:#FFF; border:1px solid var(--border-card); padding:8px 10px; border-radius:6px; display:none;">
                    <span style="font-weight:700; color:var(--brand-primary);">💬 یادداشت مشتری:</span>
                    <span id="dCustomerNoteText" style="color:var(--text-secondary);">—</span>
                </div>
            </div>

            <!-- Items Breakdown -->
            <div class="drawer-section-card">
                <div class="drawer-section-title">
                    <span>🛍️ اقلام سفارش و فاکتور</span>
                    <span id="dItemsCount" style="font-size:0.72rem; color:var(--text-muted);">۰ قلم کالا</span>
                </div>

                <table class="drawer-items-table">
                    <thead>
                        <tr>
                            <th style="text-align:right;">نام کالا و ویژگی</th>
                            <th style="text-align:center;">تعداد</th>
                            <th style="text-align:left;">قیمت</th>
                            <th style="text-align:left;">مجموع</th>
                        </tr>
                    </thead>
                    <tbody id="dItemsTableBody">
                        <!-- Populated dynamically -->
                    </tbody>
                </table>

                <div style="margin-top:12px; padding-top:8px; border-top:1px solid var(--border-card);">
                    <div class="financial-summary-row">
                        <span>مجموع قیمت کالاها:</span>
                        <span id="dSubtotal">۰ تومان</span>
                    </div>
                    <div class="financial-summary-row" id="dDiscountRow" style="color:#059669; display:none;">
                        <span>تخفیف (<span id="dCouponCode">—</span>):</span>
                        <span id="dDiscount">۰ تومان</span>
                    </div>
                    <div class="financial-summary-row">
                        <span>هزینه ارسال پستی (<span id="dShippingName">پست پیشتاز</span>):</span>
                        <span id="dShippingCost">۰ تومان</span>
                    </div>
                    <div class="financial-summary-row total-row">
                        <span>مبلغ نهایی فاکتور:</span>
                        <span id="dTotalAmount" style="color:var(--brand-primary);">۰ تومان</span>
                    </div>
                </div>
            </div>

            <!-- Postal Barcode Dispatch Field -->
            <div class="drawer-section-card">
                <div class="drawer-section-title">
                    <span>🚚 کد رهگیری مرسوله پستی</span>
                    <span style="font-size:0.72rem; color:var(--text-muted);">سامانه رهگیری مرسولات پست پیشتاز</span>
                </div>
                <form method="post" action="orders.php" style="display:flex; gap:8px;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_tracking">
                    <input type="hidden" name="id" class="drawer-hidden-order-id" value="">
                    <input type="text" name="tracking_code" id="dTrackingInput" class="orders-search-input" style="background:#FAF8F5; border:1px solid var(--border-card); border-radius:6px; padding:7px 10px; font-family:monospace; direction:ltr;" placeholder="کد رهگیری ۲۴ رقمی پست پیشتاز...">
                    <button type="submit" class="btn-verify-receipt" style="background:#1D4ED8; white-space:nowrap;">ثبت و ذخیره کد</button>
                </form>
            </div>

            <!-- Profitability Snapshot (Business Intel) -->
            <div class="drawer-section-card">
                <div class="drawer-section-title">
                    <span>📈 تحلیل سودآوری این سفارش</span>
                    <span style="font-size:0.7rem; color:var(--text-muted);">اسنپ‌شات بهای تمام‌شده در لحظه خرید</span>
                </div>
                <div class="profit-badge-box">
                    <div>
                        <div style="font-size:0.7rem; opacity:0.85;">سود ناخالص سفارش:</div>
                        <div style="font-size:1.05rem; font-weight:800;" id="dGrossProfit">۰ تومان</div>
                    </div>
                    <div style="text-align:left; font-size:0.72rem;">
                        <div>حاشیه سود: <strong id="dMarginPct">۰٪</strong></div>
                        <div style="opacity:0.75;">هزینه تمام‌شده کالا: <span id="dCostPrice">۰ ت</span></div>
                    </div>
                </div>
            </div>

            <!-- Deep Link to Full Order Detail Page -->
            <div style="margin-top:4px; text-align:center;">
                <a id="dDeepLinkBtn" href="order_detail.php" class="btn-dash-action" style="justify-content:center; padding:9px 16px; font-weight:700;">
                    <span>مشاهده صفحه کامل فاکتور و تخصیص هدایا ↗</span>
                </a>
            </div>

        </div>

        <!-- Drawer Footer Actions -->
        <div class="drawer-footer">
            <form method="post" action="orders.php" id="dStatusChangeForm" style="display:flex; align-items:center; gap:8px; width:100%;">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" class="drawer-hidden-order-id" value="">
                <label style="font-size:0.76rem; font-weight:700; white-space:nowrap;">تغییر وضعیت:</label>
                <select name="status" class="select-filter" id="dStatusChangeSelect" style="flex:1;">
                    <option value="pending">در انتظار بررسی</option>
                    <option value="confirmed">تأیید شده</option>
                    <option value="processing">در حال آماده‌سازی و بسته‌بندی</option>
                    <option value="shipped">ارسال شده با پست پیشتاز</option>
                    <option value="delivered">تحویل داده شده</option>
                    <option value="cancelled">لغو شده</option>
                </select>
                <button type="submit" class="btn-drawer-primary">به‌روزرسانی وضعیت سفارش</button>
            </form>
        </div>

    </div>
</div>

<!-- ======================================================================= -->
<!-- Receipt Fullscreen Modal                                                -->
<!-- ======================================================================= -->
<div class="receipt-modal-backdrop" id="receiptModal" onclick="closeReceiptModal()">
    <div class="receipt-modal-content" onclick="event.stopPropagation()">
        <img src="" id="receiptModalImg" class="receipt-modal-img" alt="تصویر کامل فیش کارت‌به‌کارت">
        <div class="receipt-modal-footer">
            <div>
                <strong style="font-size:0.84rem;">تصویر فیش واریزی کارت‌به‌کارت</strong>
                <div style="font-size:0.7rem; color:var(--text-muted);">شماره پیگیری و نام واریزکننده را با فاکتور مطابقت دهید.</div>
            </div>
            <button type="button" class="btn-secondary" onclick="closeReceiptModal()">بستن تصویر</button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="dash-toast" id="ordersToast">
    <span>✓</span>
    <span id="toastMsg">عملیات انجام شد.</span>
</div>

<!-- ======================================================================= -->
<!-- Interactive Logic & Event Handlers                                      -->
<!-- ======================================================================= -->
<script>
window.LIVE_ORDERS = <?= json_encode($ordersJsonMap, JSON_UNESCAPED_UNICODE) ?>;
let selectedOrders = new Set();
let currentOpenOrderId = null;

// Format Price in Tomans
function formatToman(amount) {
    return new Intl.NumberFormat('fa-IR').format(amount) + ' تومان';
}

// Open Order Dossier Drawer (Desktop) / Bottom Sheet (Mobile)
function openOrderDrawer(orderId) {
    const o = window.LIVE_ORDERS[orderId];
    if (!o) return;
    currentOpenOrderId = orderId;

    // Header info
    document.getElementById('dOrderCode').textContent = o.order_code;
    
    // Status
    const statusPill = document.getElementById('dStatusPill');
    const statusText = document.getElementById('dStatusText');
    const statusLabels = {
        'pending': 'در انتظار بررسی',
        'confirmed': 'تأیید شده',
        'processing': 'در حال بسته‌بندی',
        'shipped': 'ارسال شده با پست',
        'delivered': 'تحویل داده شده',
        'cancelled': 'لغو شده'
    };
    let pillClass = 'amber';
    if (o.status === 'processing') pillClass = 'purple';
    else if (o.status === 'shipped') pillClass = 'blue';
    else if (o.status === 'delivered') pillClass = 'emerald';
    else if (o.status === 'cancelled') pillClass = 'rose';

    statusPill.className = `dash-status-pill ${pillClass}`;
    statusText.textContent = statusLabels[o.status] || o.status;
    const statusSelect = document.getElementById('dStatusChangeSelect');
    if (statusSelect) statusSelect.value = o.status;

    // Stepper updates
    updateStepperUI(o.status, o.payment_status);

    // C2C Card section
    const c2cSection = document.getElementById('dC2cSection');
    if (o.payment_method === 'card_to_card' && o.receipt_img) {
        c2cSection.style.display = 'block';
        document.getElementById('dReceiptImg').src = o.receipt_img;
        document.getElementById('dReceiptAmount').textContent = formatToman(o.total);
        document.getElementById('dReceiptDate').textContent = 'ارسال شده: ' + (o.receipt_time || o.created_at_persian || o.created_at);
    } else {
        c2cSection.style.display = 'none';
    }

    // Customer
    document.getElementById('dOrderTime').textContent = o.created_at_persian || o.created_at;
    document.getElementById('dCustomerName').textContent = o.customer_name;
    document.getElementById('dCustomerPhone').textContent = o.phone;
    document.getElementById('dProvinceCity').textContent = `استان ${o.province}، ${o.city}`;
    document.getElementById('dFullAddress').textContent = o.address;
    document.getElementById('dPostalCode').textContent = o.postal_code || '—';

    // Note
    const noteBox = document.getElementById('dCustomerNoteBox');
    if (o.notes && o.notes.trim()) {
        noteBox.style.display = 'block';
        document.getElementById('dCustomerNoteText').textContent = o.notes;
    } else {
        noteBox.style.display = 'none';
    }

    // Items Table
    const tbody = document.getElementById('dItemsTableBody');
    let itemsHtml = '';
    (o.items || []).forEach(it => {
        itemsHtml += `
            <tr>
                <td>
                    <div style="font-weight:700;">${it.name} ${it.is_gift ? '<span class="item-gift-badge">🎁 هدیه</span>' : ''}</div>
                    <div style="font-size:0.68rem; color:var(--text-muted);">${it.variant || ''}</div>
                </td>
                <td style="text-align:center; font-weight:700;">×${it.qty}</td>
                <td style="text-align:left;">${formatToman(it.price)}</td>
                <td style="text-align:left; font-weight:700;">${formatToman(it.total)}</td>
            </tr>
        `;
    });
    (o.gifts || []).forEach(g => {
        itemsHtml += `
            <tr style="background:#F0FDF4;">
                <td>
                    <div style="font-weight:700; color:#047857;">🎁 ${g.name} <span class="item-gift-badge">اشانتیون</span></div>
                    <div style="font-size:0.68rem; color:#059669;">هدیه وفاداری / مناسبتی</div>
                </td>
                <td style="text-align:center; font-weight:700; color:#047857;">×${g.qty}</td>
                <td style="text-align:left; color:#059669;">رایگان</td>
                <td style="text-align:left; font-weight:700; color:#047857;">۰ تومان</td>
            </tr>
        `;
    });
    tbody.innerHTML = itemsHtml;
    document.getElementById('dItemsCount').textContent = ((o.items ? o.items.length : 0) + (o.gifts ? o.gifts.length : 0)) + ' قلم کالا';

    // Financial breakdown
    document.getElementById('dSubtotal').textContent = formatToman(o.subtotal);
    document.getElementById('dShippingName').textContent = o.shipping_method || 'پست پیشتاز';
    document.getElementById('dShippingCost').textContent = o.shipping_cost > 0 ? formatToman(o.shipping_cost) : 'رایگان';
    document.getElementById('dTotalAmount').textContent = formatToman(o.total);

    const discRow = document.getElementById('dDiscountRow');
    if (o.discount_total > 0) {
        discRow.style.display = 'flex';
        document.getElementById('dCouponCode').textContent = o.coupon_code || 'کوپن';
        document.getElementById('dDiscount').textContent = '−' + formatToman(o.discount_total);
    } else {
        discRow.style.display = 'none';
    }

    // Tracking code
    document.getElementById('dTrackingInput').value = o.tracking_code || '';

    // Profit
    document.getElementById('dGrossProfit').textContent = formatToman(o.gross_profit || 0);
    document.getElementById('dCostPrice').textContent = formatToman(o.cost_price || 0) + ' ت';
    const margin = o.total > 0 ? Math.round(((o.gross_profit || 0) / o.total) * 100) : 0;
    document.getElementById('dMarginPct').textContent = margin + '٪';

    // Hidden Order IDs for forms
    document.querySelectorAll('.drawer-hidden-order-id').forEach(el => el.value = o.id);
    document.getElementById('dDeepLinkBtn').href = 'order_detail.php?id=' + o.id;

    // Show Drawer
    document.getElementById('orderDrawerBackdrop').classList.add('open');
}

function updateStepperUI(status, paymentStatus) {
    const sPay = document.getElementById('stepPay');
    const sPack = document.getElementById('stepPack');
    const sShip = document.getElementById('stepShip');
    const sDeliver = document.getElementById('stepDeliver');
    if (!sPay) return;

    [sPay, sPack, sShip, sDeliver].forEach(el => el.className = 'step-item');

    if (paymentStatus === 'paid') sPay.classList.add('completed');
    else sPay.classList.add('current');

    if (status === 'processing') {
        sPay.classList.add('completed');
        sPack.classList.add('current');
    } else if (status === 'shipped') {
        sPay.classList.add('completed');
        sPack.classList.add('completed');
        sShip.classList.add('current');
    } else if (status === 'delivered') {
        sPay.classList.add('completed');
        sPack.classList.add('completed');
        sShip.classList.add('completed');
        sDeliver.classList.add('completed');
    }
}

function closeOrderDrawer() {
    document.getElementById('orderDrawerBackdrop').classList.remove('open');
    currentOpenOrderId = null;
}

// 1-Click Copy for Postal Shipping Labels
function copyPostalLabel() {
    if (!currentOpenOrderId) return;
    const o = window.LIVE_ORDERS[currentOpenOrderId];
    if (!o) return;
    const label = `گیرنده: ${o.customer_name}\nتلفن: ${o.phone}\nنشانی: استان ${o.province}، ${o.city}، ${o.address}\nکد پستی: ${o.postal_code || '—'}`;
    if (!navigator.clipboard) return;
    navigator.clipboard.writeText(label).then(() => {
        showToast('مشخصات کامل نشانی پستی جهت چاپ برچسب کپی شد ✓');
    });
}

// Receipt Modal Zoom
function openReceiptModal() {
    if (!currentOpenOrderId) return;
    const o = window.LIVE_ORDERS[currentOpenOrderId];
    if (!o || !o.receipt_img) return;
    document.getElementById('receiptModalImg').src = o.receipt_img;
    document.getElementById('receiptModal').classList.add('open');
}

function closeReceiptModal() {
    document.getElementById('receiptModal').classList.remove('open');
}

// Checkbox and Bulk Actions
function toggleSelectRow(orderId) {
    if (selectedOrders.has(orderId)) {
        selectedOrders.delete(orderId);
    } else {
        selectedOrders.add(orderId);
    }
    updateBulkDockState();
}

function toggleSelectAll(el) {
    const isAll = el.classList.contains('checked');
    const checkboxes = document.querySelectorAll('.custom-checkbox[data-order-id]');
    if (isAll) {
        selectedOrders.clear();
        el.classList.remove('checked');
        checkboxes.forEach(c => c.classList.remove('checked'));
    } else {
        selectedOrders.clear();
        checkboxes.forEach(c => {
            const oid = parseInt(c.getAttribute('data-order-id'), 10);
            if (oid) selectedOrders.add(oid);
            c.classList.add('checked');
        });
        el.classList.add('checked');
    }
    updateBulkDockState();
}

function clearSelection() {
    selectedOrders.clear();
    const selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll) selectAll.classList.remove('checked');
    document.querySelectorAll('.custom-checkbox[data-order-id]').forEach(c => c.classList.remove('checked'));
    updateBulkDockState();
}

function updateBulkDockState() {
    const dock = document.getElementById('bulkActionsDock');
    const countSpan = document.getElementById('selectedCountText');
    const hiddenInput = document.getElementById('bulkOrderIds');
    if (!dock) return;

    // Update active checkboxes in DOM
    document.querySelectorAll('.custom-checkbox[data-order-id]').forEach(c => {
        const oid = parseInt(c.getAttribute('data-order-id'), 10);
        c.classList.toggle('checked', selectedOrders.has(oid));
        const row = document.getElementById('row-' + oid);
        if (row) row.classList.toggle('row-selected', selectedOrders.has(oid));
        const mcard = document.getElementById('mcard-' + oid);
        if (mcard) mcard.classList.toggle('m-card-selected', selectedOrders.has(oid));
    });

    if (selectedOrders.size > 0) {
        dock.classList.add('active');
        countSpan.textContent = selectedOrders.size;
        hiddenInput.value = Array.from(selectedOrders).join(',');
    } else {
        dock.classList.remove('active');
        hiddenInput.value = '';
    }
}

function submitBulkStatus(status) {
    if (selectedOrders.size === 0) return;
    document.getElementById('bulkNewStatus').value = status;
    document.getElementById('bulkActionForm').submit();
}

// Copy to Clipboard
function copyCode(text, label) {
    if (!navigator.clipboard) return;
    navigator.clipboard.writeText(text).then(() => {
        showToast(`${label} «${text}» کپی شد ✓`);
    });
}

// Toast Notification
function showToast(msg) {
    const toast = document.getElementById('ordersToast');
    const span = document.getElementById('toastMsg');
    if (!toast || !span) return;
    span.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 2800);
}

// Touch swipe-down to dismiss bottom sheet
(function initBottomSheetGestures() {
    let startY = 0;
    let currentY = 0;
    const drawer = document.querySelector('.order-drawer');
    const handle = document.querySelector('.bottom-sheet-handle-bar');
    if (!drawer || !handle) return;

    handle.addEventListener('touchstart', (e) => {
        startY = e.touches[0].clientY;
    }, { passive: true });

    handle.addEventListener('touchmove', (e) => {
        currentY = e.touches[0].clientY;
        const diff = currentY - startY;
        if (diff > 0) {
            drawer.style.transform = `translateY(${diff}px)`;
        }
    }, { passive: true });

    handle.addEventListener('touchend', () => {
        const diff = currentY - startY;
        drawer.style.transform = '';
        if (diff > 70) {
            closeOrderDrawer();
        }
        startY = 0;
        currentY = 0;
    });
})();

// Keybinds (Esc to close drawers)
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeReceiptModal();
        closeOrderDrawer();
    }
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
