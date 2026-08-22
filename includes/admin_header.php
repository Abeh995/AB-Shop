<?php
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
            <a href="products.php" class="<?= $currentPage === 'products.php' || $currentPage === 'product_edit.php' ? 'active' : '' ?>">محصولات</a>
            <a href="categories.php" class="<?= $currentPage === 'categories.php' ? 'active' : '' ?>">دسته‌بندی‌ها</a>
            <a href="orders.php" class="<?= $currentPage === 'orders.php' || $currentPage === 'order_detail.php' ? 'active' : '' ?>">سفارش‌ها</a>
            <a href="/" target="_blank">مشاهده فروشگاه ↗</a>
            <a href="logout.php" class="logout-link">خروج</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1><?= e($pageTitle) ?></h1>
            <span class="admin-user">👤 <?= e($_SESSION['admin_username'] ?? '') ?></span>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
