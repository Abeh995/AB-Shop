<?php
/**
 * Shared site header.
 * $pageTitle may be defined before including this file.
 * Optional page-level SEO variables: $metaDescription, $ogImage, $jsonLd.
 */
$pageTitle = $pageTitle ?? SITE_NAME;
$flash = getFlash();

// Load top-level categories for the main menu; child categories are shown on their parent page.
$navCategories = db()->query("SELECT id, name, slug FROM categories WHERE is_active = 1 AND parent_id IS NULL ORDER BY sort_order ASC LIMIT 8")->fetchAll();

// ---------- SEO: control Google indexing based on the admin setting ----------
$seoIndexingEnabled = getSetting('seo_indexing_enabled', '0') === '1';
$canonicalUrl = rtrim(SITE_URL, '/') . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$metaDescription = $metaDescription ?? 'فروشگاه اینترنتی جوراب؛ خرید آنلاین جوراب مردانه، زنانه و بچگانه با ارسال سریع';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
<?php if ($seoIndexingEnabled): ?>
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= e($canonicalUrl) ?>">
<?php else: ?>
<meta name="robots" content="noindex, nofollow">
<?php endif; ?>
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:type" content="<?= isset($ogImage) ? 'product' : 'website' ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">
<?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="/assets/css/style.css">
<?php if (!empty($jsonLd)): ?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <button class="nav-toggle" id="navToggle" aria-label="باز کردن منو">
            <span></span><span></span><span></span>
        </button>

        <a href="/" class="logo"><?= e(SITE_NAME) ?></a>

        <nav class="main-nav" id="mainNav">
            <a href="/">خانه</a>
            <?php foreach ($navCategories as $cat): ?>
                <a href="/category/<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
            <a href="/about">درباره ما</a>
            <a href="/contact">تماس با ما</a>
        </nav>

        <a href="/cart" class="cart-link">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <span class="cart-count" id="cartCount"><?= cartCount() ?></span>
        </a>

        <?php if (isCustomerLoggedIn()): ?>
            <a href="/account" class="account-link" title="حساب کاربری">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </a>
        <?php else: ?>
            <a href="/login" class="account-link" title="ورود / ثبت‌نام">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            </a>
        <?php endif; ?>
    </div>
</header>

<?php if ($flash): ?>
    <div class="container">
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    </div>
<?php endif; ?>

<main>
