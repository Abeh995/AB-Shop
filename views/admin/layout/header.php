<?php
/**
 * Shared admin panel header — expects $pageTitle to be set and bootstrap to
 * already be loaded.
 * Restructured with 5 core functional groups and Global Live Search (FEAT-A003, FEAT-A004).
 */
$pageTitle = $pageTitle ?? 'پنل مدیریت';
$flash = getFlash();
$currentPage = basename($_SERVER['SCRIPT_NAME']);

$pendingOrdersCount = 0;
try {
    $pendingOrdersCount = (int) db()->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'payment_pending')")->fetchColumn();
} catch (Exception $e) {}
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
</head>
<body class="admin-body">

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
            <a href="card_to_card_payments.php" class="<?= $currentPage === 'card_to_card_payments.php' ? 'active' : '' ?>">
                <span class="nav-icon">💳</span> بررسی کارت‌به‌کارت
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
        <div class="admin-topbar">
            <div class="admin-topbar-title-wrap">
                <h1><?= e($pageTitle) ?></h1>
            </div>

            <!-- Global Live Search (FEAT-A004) -->
            <div class="admin-search-box">
                <div class="admin-search-input-wrap">
                    <svg class="admin-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" id="adminGlobalSearch" class="admin-search-input" placeholder="جستجو در ادمین (صفحات، سفارش، کالا)..." autocomplete="off" spellcheck="false">
                    <kbd class="admin-search-kbd">Ctrl+K</kbd>
                </div>
                <div id="adminSearchResults" class="admin-search-results" style="display:none;"></div>
            </div>

            <div class="admin-topbar-user">
                <span class="admin-user">👤 <?= e($_SESSION['admin_username'] ?? '') ?> <small style="opacity:.7;">(<?= isSuperAdmin() ? 'مدیر کل' : 'ادمین' ?>)</small></span>
                <a href="/" target="_blank" class="admin-storefront-btn" title="مشاهده فروشگاه">🌐</a>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <?php require APP_ROOT . '/views/admin/layout/sub_nav.php'; ?>
