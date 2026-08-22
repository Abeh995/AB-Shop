<?php
/**
 * صفحه اصلی
 */
$pageTitle = 'خانه';

$categories = db()->query("SELECT id, name, slug, image FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

$featured = db()->query("
    SELECT * FROM products
    WHERE is_active = 1 AND is_featured = 1
    ORDER BY created_at DESC LIMIT 8
")->fetchAll();

$newest = db()->query("
    SELECT * FROM products
    WHERE is_active = 1
    ORDER BY created_at DESC LIMIT 8
")->fetchAll();

require __DIR__ . '/../includes/header.php';
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
            <?php foreach ($featured as $p): include __DIR__ . '/partials/product_card.php'; endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($newest): ?>
    <section class="section">
        <div class="section-title"><h2>جدیدترین محصولات</h2></div>
        <div class="product-grid">
            <?php foreach ($newest as $p): include __DIR__ . '/partials/product_card.php'; endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
