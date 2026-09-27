<?php
/**
 * Shared admin panel header — expects $pageTitle to be set and bootstrap to
 * already be loaded.
 * Restructured with 5 core functional groups and Global Live Search (FEAT-A003, FEAT-A004).
 */
$pageTitle = $pageTitle ?? 'پنل مدیریت';
$flash = getFlash();
$currentPage = basename($_SERVER['SCRIPT_NAME']);

$pendingOrdersCount = class_exists('OrderService') ? OrderService::getPendingCount() : 0;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | مدیریت <?= e(SITE_NAME) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="/assets/css/style.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>">
<?php if (in_array($currentPage, ['orders.php', 'order_detail.php'], true)): ?>
<link rel="stylesheet" href="/assets/css/admin-orders.css?v=<?= APP_VERSION ?>">
<?php endif; ?>
</head>
<body class="admin-body <?= $currentPage === 'index.php' ? 'admin-page-dashboard' : (in_array($currentPage, ['orders.php', 'order_detail.php'], true) ? 'admin-page-orders' : '') ?>">

<div class="admin-wrap">
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <a href="index.php" style="color:inherit; text-decoration:none;">
                <?= e(SITE_NAME) ?><br><small>پنل مدیریت</small>
            </a>
        </div>
        <nav>
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span> داشبورد
            </a>

            <div class="nav-group-label">سفارش‌ها</div>
            <a href="orders.php" class="<?= in_array($currentPage, ['orders.php', 'order_detail.php'], true) ? 'active' : '' ?>">
                <span class="nav-icon">📦</span> همه سفارش‌ها
                <?php if ($pendingOrdersCount > 0): ?>
                    <span class="admin-badge-count"><?= $pendingOrdersCount ?></span>
                <?php endif; ?>
            </a>

            <div class="nav-group-label">محصولات</div>
            <a href="products.php" class="<?= ($currentPage === 'products.php' && empty($_GET['featured'])) || $currentPage === 'product_edit.php' ? 'active' : '' ?>">
                <span class="nav-icon">🛍️</span> همه محصولات
            </a>
            <a href="products.php?featured=1" class="<?= $currentPage === 'products.php' && !empty($_GET['featured']) ? 'active' : '' ?>">
                <span class="nav-icon">⭐</span> پیشنهاد ویژه
            </a>
            <a href="categories.php" class="<?= $currentPage === 'categories.php' ? 'active' : '' ?>">
                <span class="nav-icon">📁</span> دسته‌بندی‌ها
            </a>
            <a href="pricing.php" class="<?= $currentPage === 'pricing.php' ? 'active' : '' ?>">
                <span class="nav-icon">💰</span> تغییر قیمت گروهی
            </a>
            <a href="gift_items.php" class="<?= in_array($currentPage, ['gift_items.php', 'gift_item_edit.php'], true) ? 'active' : '' ?>">
                <span class="nav-icon">🎁</span> هدیه و آفر بعد از سبد
            </a>

            <div class="nav-group-label">مالی</div>
            <a href="finance_dashboard.php" class="<?= $currentPage === 'finance_dashboard.php' ? 'active' : '' ?>">
                <span class="nav-icon">📈</span> داشبورد مالی
            </a>
            <a href="card_to_card_payments.php" class="<?= $currentPage === 'card_to_card_payments.php' ? 'active' : '' ?>">
                <span class="nav-icon">💳</span> فیش‌های کارت‌به‌کارت
            </a>
            <a href="expenses.php" class="<?= in_array($currentPage, ['expenses.php', 'expense_edit.php'], true) ? 'active' : '' ?>">
                <span class="nav-icon">🧾</span> هزینه‌ها
            </a>

            <div class="nav-group-label">تنظیمات</div>
            <a href="settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">
                <span class="nav-icon">⚙️</span> تنظیمات عمومی
            </a>
            <a href="appearance.php" class="<?= in_array($currentPage, ['appearance.php', 'themes.php', 'theme_edit.php'], true) ? 'active' : '' ?>">
                <span class="nav-icon">🎨</span> ظاهر و صفحه اصلی
            </a>
            <a href="sms_patterns.php" class="<?= in_array($currentPage, ['sms_patterns.php', 'sms_pattern_edit.php'], true) ? 'active' : '' ?>">
                <span class="nav-icon">📱</span> الگوهای پیامک
            </a>
            <a href="email_accounts.php" class="<?= in_array($currentPage, ['email_accounts.php', 'emails.php', 'email_read.php', 'email_compose.php'], true) ? 'active' : '' ?>">
                <span class="nav-icon">📧</span> ایمیل‌ها
            </a>
            <a href="shipping_methods.php" class="<?= in_array($currentPage, ['shipping_methods.php', 'shipping_method_edit.php'], true) ? 'active' : '' ?>">
                <span class="nav-icon">🚚</span> روش‌های ارسال
            </a>

            <?php if (isSuperAdmin()): ?>
            <a href="users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
                <span class="nav-icon">👥</span> مدیران سایت
            </a>
            <a href="diagnostics.php" class="<?= in_array($currentPage, ['diagnostics.php', 'notifications_log.php'], true) ? 'active' : '' ?>">
                <span class="nav-icon">🔧</span> عیب‌یابی و لاگ
            </a>
            <?php endif; ?>

            <a href="/" target="_blank" style="margin-top:16px; border-top:1px solid rgba(255,255,255,.08); padding-top:16px;">مشاهده فروشگاه ↗</a>
            <a href="logout.php" class="logout-link">خروج</a>
        </nav>
    </aside>

    <main class="admin-main">
        <!-- Unified Modern Admin Topbar (Global across all tabs) -->
        <header class="dash-topbar" id="adminGlobalTopbar">
            <div class="dash-topbar-right">
                <!-- Global Responsive Pill Search Bar -->
                <div class="admin-search-box">
                    <div class="admin-search-input-wrap">
                        <svg class="admin-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="search" id="adminGlobalSearch" class="admin-search-input" placeholder="جستجو در پنل..." autocomplete="off" spellcheck="false" dir="rtl">
                        <kbd class="admin-search-kbd">Ctrl K</kbd>
                    </div>
                    <div id="adminSearchResults" class="admin-search-results" style="display:none;"></div>
                </div>

                <!-- Compact Storefront Link -->
                <a href="/" target="_blank" class="btn-dash-store-compact" title="مشاهده فروشگاه آنلاین (تب جدید)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    <span class="store-btn-text">فروشگاه</span>
                </a>
            </div>

            <div class="dash-topbar-left">
                <!-- Stacked 2-Line Real-Time Live Date & Clock Widget (Zero Emojis, No Width Waste, Strict Alignment) -->
                <div class="dash-live-datetime" id="dashLiveDateTime" title="ساعت و تاریخ جاری سیستم">
                    <div class="live-clock-time" id="liveClockTime" dir="ltr"><?= appDateTime(null, 'time_full') ?></div>
                    <div class="live-clock-date" id="liveClockDate" dir="rtl"><?= appDateTime(null, 'shamsi_text') ?></div>
                </div>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <?php if (!in_array($currentPage, ['index.php', 'orders.php', 'order_detail.php'], true)): ?>
            <?php require APP_ROOT . '/views/admin/layout/sub_nav.php'; ?>
        <?php endif; ?>

