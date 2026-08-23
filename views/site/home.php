<?php
/**
 * View صفحه اصلی — فقط نمایش. متغیرها ($categories, $featured, $newest, $pageTitle)
 * توسط app/controllers/site/home.php آماده و از طریق renderView() تزریق شده‌اند.
 */
require APP_ROOT . '/views/layout/header.php';
?>

<section class="hero">
    <div class="container">
        <h1>جوراب‌هایی که هر روزتان را راحت‌تر می‌کنند</h1>
        <p>کیفیت پارچه، دوخت مقاوم و طرح‌های به‌روز؛ مستقیم درِ خانه شما.</p>
        <a href="/category/<?= e($categories[0]['slug'] ?? '') ?>" class="btn btn-primary">مشاهده محصولات</a>
    </div>
</section>

<div class="container">

    <?php if ($categories): ?>
    <section class="section">
        <div class="section-title"><h2>دسته‌بندی‌ها</h2></div>
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="/category/<?= e($cat['slug']) ?>" class="category-card"><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($featured): ?>
    <section class="section">
        <div class="section-title">
            <h2>پیشنهاد ویژه</h2>
        </div>
        <div class="product-grid">
            <?php foreach ($featured as $p): require APP_ROOT . '/views/site/partials/product_card.php'; endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($newest): ?>
    <section class="section">
        <div class="section-title"><h2>جدیدترین محصولات</h2></div>
        <div class="product-grid">
            <?php foreach ($newest as $p): require APP_ROOT . '/views/site/partials/product_card.php'; endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
