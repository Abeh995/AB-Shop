<?php

$pageTitle = $pageTitle ?? SITE_NAME;
$flash = getFlash();

$navCategories = db()->query("SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>
<meta name="description" content="فروشگاه اینترنتی جوراب؛ خرید آنلاین جوراب مردانه، زنانه و بچگانه با ارسال سریع">
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="/assets/css/style.css">
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
    </div>
</header>

<?php if ($flash): ?>
    <div class="container">
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    </div>
<?php endif; ?>

<main>
