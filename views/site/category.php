<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <?php site_component('breadcrumb', ['items' => $breadcrumbs ?? []]); ?>

    <section class="c-section section">
        <div class="section-title category-header-wrap">
            <?php if (!empty($category['image'])): ?>
                <div class="category-header-icon">
                    <img src="<?= UPLOAD_URL . e($category['image']) ?>" alt="<?= e($category['name']) ?>" width="72" height="72" loading="eager" class="category-header-img">
                </div>
            <?php endif; ?>
            <div>
                <h1><?= e($category['name']) ?></h1>
            </div>
        </div>

        <?php if (!empty($category['seo_intro'])): ?>
            <div class="category-seo-intro"><?= nl2br(e($category['seo_intro'])) ?></div>
        <?php elseif (!empty($category['description'])): ?>
            <p class="category-seo-intro c-text-muted"><?= e($category['description']) ?></p>
        <?php endif; ?>

        <?php if ($subCategories): ?>
        <div class="category-sub-pills">
            <?php foreach ($subCategories as $sc): ?>
                <a href="/category/<?= e($sc['slug']) ?>" class="category-card category-sub-pill">
                    <?php if (!empty($sc['image'])): ?>
                        <span class="cat-icon">
                            <img src="<?= UPLOAD_URL . e($sc['image']) ?>" alt="<?= e($sc['name']) ?>" width="40" height="40" loading="lazy" class="cat-icon-img">
                        </span>
                    <?php endif; ?>
                    <?= e($sc['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form class="toolbar" method="get" action="/category/<?= e($slug) ?>">
            <label class="c-cluster c-cluster--xs c-text-sm">
                <input type="checkbox" name="available" value="1" data-ab-auto-submit <?= $onlyAvailable ? 'checked' : '' ?>>
                فقط کالاهای موجود
            </label>
            <select name="sort" data-ab-auto-submit aria-label="مرتب‌سازی محصولات">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>جدیدترین</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>ارزان‌ترین</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>گران‌ترین</option>
            </select>
        </form>

        <?php if ($products): ?>
            <div class="c-product-grid product-grid">
                <?php foreach ($products as $p): site_component('product_card', ['product' => $p]); endforeach; ?>
            </div>

            <?php
            $paginationParams = ['sort' => $sort];
            if ($onlyAvailable) $paginationParams['available'] = '1';
            site_component('pagination', [
                'page' => $page,
                'totalPages' => $totalPages,
                'params' => $paginationParams,
            ]);
            ?>
        <?php else: ?>
            <?php site_component('empty_state', ['message' => 'در حال حاضر محصولی در این دسته‌بندی موجود نیست.']); ?>
        <?php endif; ?>

        <?php if (!empty($category['seo_guide'])): ?>
            <div class="category-seo-guide">
                <h3>راهنمای جامع خرید و انتخاب <?= e($category['name']) ?></h3>
                <div class="c-text-sm"><?= nl2br(e($category['seo_guide'])) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($faqItems)): ?>
            <?php site_component('faq_accordion', [
                'title' => 'پرسش‌های متداول درباره ' . $category['name'],
                'items' => $faqItems,
            ]); ?>
        <?php endif; ?>
    </section>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
