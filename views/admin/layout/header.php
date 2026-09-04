<?php
/**
 * Shared admin panel header — expects $pageTitle to be set and bootstrap to
 * already be loaded
 */
$pageTitle = $pageTitle ?? 'پنل مدیریت';
$flash = getFlash();
$currentPage = basename($_SERVER['SCRIPT_NAME']);
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
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-wrap">
    <aside class="admin-sidebar">
        <div class="admin-logo"><?= e(SITE_NAME) ?><br><small>پنل مدیریت</small></div>
        <nav>
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">داشبورد</a>

            <div class="nav-group-label">محصولات</div>
            <a href="products.php" class="<?= $currentPage === 'products.php' && empty($_GET['featured']) || $currentPage === 'product_edit.php' ? 'active' : '' ?>">محصولات</a>
            <a href="products.php?featured=1" class="<?= $currentPage === 'products.php' && !empty($_GET['featured']) ? 'active' : '' ?>">⭐ پیشنهاد ویژه</a>
            <a href="categories.php" class="<?= $currentPage === 'categories.php' ? 'active' : '' ?>">دسته‌بندی‌ها</a>
            <a href="pricing.php" class="<?= $currentPage === 'pricing.php' ? 'active' : '' ?>">💰 تغییر قیمت گروهی</a>

            <div class="nav-group-label">سفارش‌ها</div>
            <a href="orders.php" class="<?= $currentPage === 'orders.php' || $currentPage === 'order_detail.php' ? 'active' : '' ?>">سفارش‌ها</a>

            <div class="nav-group-label">تنظیمات</div>
            <a href="settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">تنظیمات فروشگاه</a>
            <a href="themes.php" class="<?= $currentPage === 'themes.php' || $currentPage === 'theme_edit.php' ? 'active' : '' ?>">🎨 قالب و رنگ سایت</a>

            <?php if (isSuperAdmin()): ?>
            <div class="nav-group-label">مدیریت</div>
            <a href="users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">مدیریت ادمین‌ها</a>
            <a href="diagnostics.php" class="<?= $currentPage === 'diagnostics.php' || $currentPage === 'notifications_log.php' ? 'active' : '' ?>">🔧 عیب‌یابی پیامک/ایمیل</a>
            <?php endif; ?>

            <a href="/" target="_blank" style="margin-top:16px; border-top:1px solid rgba(255,255,255,.08); padding-top:16px;">مشاهده فروشگاه ↗</a>
            <a href="logout.php" class="logout-link">خروج</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1><?= e($pageTitle) ?></h1>
            <span class="admin-user">👤 <?= e($_SESSION['admin_username'] ?? '') ?> <small style="opacity:.7;">(<?= isSuperAdmin() ? 'مدیر کل' : 'ادمین' ?>)</small></span>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
