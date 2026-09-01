<?php
/**
 * Home page view — display only. The variables ($categories, $featured,
 * $newest, $pageTitle) are prepared by app/controllers/site/home.php and
 * injected via renderView().
 *
 * Layout (per the 1.4.0 UI redesign): categories first, then a "Featured"
 * carousel (3 cards per view, swipe for more), then a "Newest" carousel in
 * the same style, then the categories again in a different look (a
 * horizontal pill strip) so they stay reachable without scrolling back up.
 */
require APP_ROOT . '/views/layout/header.php';

// A generic grid/category icon — reused for every tile since categories have
// no per-category image/icon of their own yet.
$catIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>';
?>

<div class="container">
    <div class="page-intro">
        <h1>جوراب‌هایی که هر روزتان را راحت‌تر می‌کنند</h1>
        <p>کیفیت پارچه، دوخت مقاوم و طرح‌های به‌روز؛ مستقیم درِ خانه شما.</p>
    </div>

    <?php if ($categories): ?>
    <section class="section" style="padding-top:12px;">
        <div class="section-title"><h2>دسته‌بندی‌ها</h2></div>
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="/category/<?= e($cat['slug']) ?>" class="category-card">
                    <span class="cat-icon"><?= $catIcon ?></span>
                    <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($featured): ?>
    <section class="section">
        <div class="section-title"><h2>پیشنهاد ویژه</h2></div>
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

    <?php if ($newest): ?>
    <section class="section">
        <div class="section-title"><h2>آخرین محصولات</h2></div>
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

<?php if ($categories): ?>
<section class="category-strip-section">
    <div class="container">
        <h2>دسته‌بندی‌ها را از همین‌جا هم می‌بینید</h2>
        <div class="category-strip">
            <?php foreach ($categories as $cat): ?>
                <a href="/category/<?= e($cat['slug']) ?>" class="category-pill">
                    <span class="cat-icon"><?= $catIcon ?></span>
                    <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
