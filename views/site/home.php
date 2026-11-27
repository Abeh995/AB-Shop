<?php
/**
 * Home page view — pure presentation.
 * Data is assembled by app/controllers/site/home.php via StorefrontService and SettingService.
 * Maintains a strict single H1 heading for SEO hierarchy.
 */
require APP_ROOT . '/views/layout/header.php';

// Generic grid/category icon reused for category tiles without custom images
$catIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>';

$hasHeroBanner = !empty($heroBannerEnabled);
$hasPageIntro = !$hasHeroBanner && !empty($homeIntroEnabled) && ($homeIntroTitle !== '' || $homeIntroSubtitle !== '');
$h1Text = $hasHeroBanner
    ? ($heroBannerTitle ?: SITE_NAME)
    : (($hasPageIntro && $homeIntroTitle !== '') ? $homeIntroTitle : SITE_NAME);
?>

<div class="container">
    <!-- 1. Hero Promo Banner or Text Intro (Single H1) -->
    <?php if ($hasHeroBanner): ?>
        <section class="hero-promo-banner" aria-label="پیشنهاد ویژه فروشگاه">
            <?php if (!empty($heroBannerImage)): ?>
                <img src="<?= e($heroBannerImage) ?>" alt="<?= e($h1Text) ?>" class="hero-promo-bg" loading="eager">
            <?php endif; ?>
            <div class="hero-promo-content">
                <?php if (!empty($heroBannerBadge)): ?>
                    <span class="hero-promo-badge"><?= e($heroBannerBadge) ?></span>
                <?php endif; ?>
    <?php elseif ($hasPageIntro): ?>
        <div class="page-intro">
    <?php endif; ?>
                <h1<?= (!$hasHeroBanner && !$hasPageIntro) ? ' style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;"' : '' ?>><?= e($h1Text) ?></h1>
    <?php if ($hasHeroBanner): ?>
                <?php if (!empty($heroBannerSubtitle)): ?>
                    <p class="hero-promo-sub"><?= e($heroBannerSubtitle) ?></p>
                <?php endif; ?>
                <?php if (!empty($heroBannerCtaText) && !empty($heroBannerCtaUrl)): ?>
                    <a href="<?= e($heroBannerCtaUrl) ?>" class="hero-promo-cta">
                        <span><?= e($heroBannerCtaText) ?></span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </section>
    <?php elseif ($hasPageIntro): ?>
            <?php if ($homeIntroSubtitle !== ''): ?><p><?= e($homeIntroSubtitle) ?></p><?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- 2. Trust Bar / Value Propositions -->
    <?php if (!empty($trustBarEnabled) && !empty($trustItems)): ?>
        <?php
        $trustIcons = [
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"/><path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>',
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/></svg>',
        ];
        ?>
        <section class="trust-bar-section" aria-label="مزایای خرید از فروشگاه">
            <div class="trust-bar-grid">
                <?php foreach ($trustItems as $idx => $tItem): ?>
                    <?php if (empty($tItem['title'])) continue; ?>
                    <div class="trust-bar-item">
                        <div class="trust-bar-icon">
                            <?= $trustIcons[$idx % 4] ?>
                        </div>
                        <div class="trust-bar-text">
                            <h3><?= e($tItem['title']) ?></h3>
                            <?php if (!empty($tItem['desc'])): ?>
                                <p><?= e($tItem['desc']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- 3. Category Grid -->
    <?php if ($homeCategoriesEnabled && $categories): ?>
    <section class="section" style="padding-top:12px;">
        <div class="section-title"><h2><?= e($homeCategoriesTitle ?: 'دسته‌بندی‌ها') ?></h2></div>
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="/category/<?= e($cat['slug']) ?>" class="category-card">
                    <span class="cat-icon">
                        <?php if (!empty($cat['image'])): ?>
                            <img src="<?= UPLOAD_URL . e($cat['image']) ?>" alt="<?= e($cat['name']) ?>" class="cat-icon-img" loading="lazy">
                        <?php else: ?>
                            <?= $catIcon ?>
                        <?php endif; ?>
                    </span>
                    <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- 4. Featured Carousel -->
    <?php if ($homeFeaturedEnabled && $featured): ?>
    <section class="section">
        <div class="section-title"><h2><?= e($homeFeaturedTitle ?: 'پیشنهاد ویژه') ?></h2></div>
        <div class="carousel-wrap">
            <div class="carousel-track">
                <?php foreach ($featured as $p): require APP_ROOT . '/views/site/partials/product_card.php'; endforeach; ?>
            </div>
            <?php if (count($featured) > 3): ?>
                <button type="button" class="carousel-nav prev" aria-label="محصولات قبلی">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <button type="button" class="carousel-nav next" aria-label="محصولات بعدی">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- 5. Newest Carousel -->
    <?php if ($homeNewestEnabled && $newest): ?>
    <section class="section">
        <div class="section-title"><h2><?= e($homeNewestTitle ?: 'آخرین محصولات') ?></h2></div>
        <div class="carousel-wrap">
            <div class="carousel-track">
                <?php foreach ($newest as $p): require APP_ROOT . '/views/site/partials/product_card.php'; endforeach; ?>
            </div>
            <?php if (count($newest) > 3): ?>
                <button type="button" class="carousel-nav prev" aria-label="محصولات قبلی">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <button type="button" class="carousel-nav next" aria-label="محصولات بعدی">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- 6. Bottom Category Strip -->
<?php if ($homeCategoryStripEnabled && $categories): ?>
<section class="category-strip-section">
    <div class="container">
        <h2><?= e($homeCategoryStripTitle ?: 'دسته‌بندی‌ها را از همین‌جا هم می‌بینید') ?></h2>
        <div class="category-strip">
            <?php foreach ($categories as $cat): ?>
                <a href="/category/<?= e($cat['slug']) ?>" class="category-pill">
                    <span class="cat-icon">
                        <?php if (!empty($cat['image'])): ?>
                            <img src="<?= UPLOAD_URL . e($cat['image']) ?>" alt="<?= e($cat['name']) ?>" class="cat-icon-img" loading="lazy">
                        <?php else: ?>
                            <?= $catIcon ?>
                        <?php endif; ?>
                    </span>
                    <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
