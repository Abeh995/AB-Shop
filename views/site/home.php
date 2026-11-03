<?php
/**
 * Modern Storefront Landing Page View.
 * Renders SEO-optimized HTML with server fallback, injected data, and client bundle mounting.
 * Zero database queries or form processing allowed in view per architecture rules.
 */
$siteFavicon = siteFaviconUrl();
$canonicalUrl = rtrim(SITE_URL, '/') . '/';
$storeName = SITE_NAME;

// Server-rendered fallback copy — mirrors the published landing config so bots
// (and no-JS visitors) always see the same headline the Studio published.
$hero = $hero ?? ($landingConfig['hero'] ?? []);
$seo = $seo ?? ($landingConfig['seo'] ?? []);
$heroMain = trim((string) ($hero['headlineMain'] ?? '')) ?: 'هر قدم،';
$heroHighlight = trim((string) ($hero['headlineHighlight'] ?? '')) ?: 'یه اثر هنریه';
$heroSubtitle = trim((string) ($hero['subtitle'] ?? '')) ?: 'فروشگاه آنلاین جوراب‌های دست‌باف و خاص؛ بافته‌شده با نخ‌پنبه سوپر شانه‌شده و طرح‌های اصیل کارگاهی.';
$ogImage = trim((string) ($seo['ogImage'] ?? '')) ?: trim((string) ($hero['productImage'] ?? ''));
if ($ogImage !== '' && !preg_match('~^https?://~i', $ogImage)) {
    $ogImage = rtrim(SITE_URL, '/') . '/' . ltrim($ogImage, '/');
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Social Media -->
    <meta property="og:site_name" content="<?= e($storeName) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <?php if ($ogImage !== ''): ?>
        <meta property="og:image" content="<?= e($ogImage) ?>">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <?php endif; ?>

    <!-- Favicon -->
    <?php if ($siteFavicon): ?>
        <link rel="icon" href="<?= e($siteFavicon) ?>">
        <link rel="apple-touch-icon" href="<?= e($siteFavicon) ?>">
    <?php else: ?>
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <?php endif; ?>
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#221510">

    <!-- Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Modern Landing Bundle Stylesheet -->
    <link rel="stylesheet" href="/assets/dist/landing/landing.bundle.css">

    <!-- Schema.org Store JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ClothingStore",
        "name": <?= json_encode($storeName, JSON_UNESCAPED_UNICODE) ?>,
        "description": <?= json_encode($metaDescription, JSON_UNESCAPED_UNICODE) ?>,
        "url": <?= json_encode($canonicalUrl, JSON_UNESCAPED_UNICODE) ?>,
        "telephone": <?= json_encode($landingInitialData['store']['phone'] ?? '', JSON_UNESCAPED_UNICODE) ?>
    }
    </script>
</head>
<body class="bg-[#fbf7ee] text-[#221510] font-sans antialiased">
    <!-- React Storefront Root Container -->
    <div id="root">
        <!-- SEO Semantic Server Fallback: indexed by crawlers before JS bundle mounts -->
        <header style="padding: 1.5rem; text-align: center; border-bottom: 1px solid rgba(34,21,16,0.1);">
            <a href="/" style="font-weight: 900; font-size: 1.5rem; color: #221510; text-decoration: none;">ای‌بی ساکس | AB SOCKS</a>
        </header>

        <main style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
            <section style="text-align: center; margin-bottom: 3rem;">
                <h1 style="font-size: 2.5rem; font-weight: 900; color: #221510; margin-bottom: 1rem;">
                    <?= e($heroMain) ?> <span style="color: <?= e(trim((string) ($hero['underlineColor'] ?? '')) ?: '#e85d1f') ?>;"><?= e($heroHighlight) ?></span>
                </h1>
                <p style="font-size: 1.1rem; color: #563c28; max-width: 600px; margin: 0 auto;">
                    <?= e($heroSubtitle) ?>
                </p>
            </section>

            <?php if (!empty($products)): ?>
            <section aria-label="محصولات منتخب کارگاه">
                <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem; text-align: center;">محصولات منتخب</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($products as $p): ?>
                        <article style="border: 1px solid rgba(34,21,16,0.1); border-radius: 1.25rem; padding: 1rem; background: #fff;">
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= e(UPLOAD_URL . $p['image']) ?>" alt="<?= e($p['name']) ?>" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 0.75rem;" loading="lazy">
                            <?php endif; ?>
                            <h3 style="font-size: 1rem; font-weight: 800; margin: 0.75rem 0 0.25rem;">
                                <a href="/product/<?= e($p['slug']) ?>" style="color: #221510; text-decoration: none;"><?= e($p['name']) ?></a>
                            </h3>
                            <p style="font-size: 0.85rem; color: #71543a; margin-bottom: 0.5rem;"><?= e(mb_substr($p['description'] ?? '', 0, 80)) ?></p>
                            <p style="font-size: 1.1rem; font-weight: 900; color: #e85d1f;"><?= number_format((float)($p['discount_price'] ?: $p['price'])) ?> تومان</p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </main>
    </div>

    <!-- Injected Live Data from Server Session & Database -->
    <script id="ab-landing-data">
        window.__AB_INITIAL_DATA__ = <?= json_encode($landingInitialData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>

    <!-- Modern Landing Bundle Script -->
    <script type="module" src="/assets/dist/landing/landing.bundle.js"></script>
</body>
</html>
