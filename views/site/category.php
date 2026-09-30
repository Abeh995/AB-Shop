<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <section class="section">
        <div class="section-title category-header-wrap">
            <?php if (!empty($category['image'])): ?>
                <div class="category-header-icon">
                    <img src="<?= UPLOAD_URL . e($category['image']) ?>" alt="<?= e($category['name']) ?>" class="category-header-img">
                </div>
            <?php endif; ?>
            <div>
                <h2><?= e($category['name']) ?></h2>
            </div>
        </div>

        <?php if ($category['description']): ?>
            <p style="color:var(--color-muted); margin-top:-14px; margin-bottom:24px;"><?= e($category['description']) ?></p>
        <?php endif; ?>

        <?php if ($subCategories): ?>
        <div class="category-sub-pills" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px;">
            <?php foreach ($subCategories as $sc): ?>
                <a href="/category/<?= e($sc['slug']) ?>" class="category-card" style="padding:8px 16px; font-size:.88rem; display:inline-flex; align-items:center; gap:8px;">
                    <?php if (!empty($sc['image'])): ?>
                        <span class="cat-icon" style="width:24px; height:24px; margin:0; overflow:hidden; border-radius:50%; flex-shrink:0;">
                            <img src="<?= UPLOAD_URL . e($sc['image']) ?>" alt="<?= e($sc['name']) ?>" class="cat-icon-img">
                        </span>
                    <?php endif; ?>
                    <?= e($sc['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form class="toolbar" method="get" action="/category/<?= e($slug) ?>">
            <label style="display:flex; align-items:center; gap:6px; font-size:.9rem;">
                <input type="checkbox" name="available" value="1" onchange="this.form.submit()" <?= $onlyAvailable ? 'checked' : '' ?>>
                فقط کالاهای موجود
            </label>
            <select name="sort" onchange="this.form.submit()">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>جدیدترین</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>ارزان‌ترین</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>گران‌ترین</option>
            </select>
        </form>

        <?php if ($products): ?>
            <div class="product-grid">
                <?php foreach ($products as $p): require APP_ROOT . '/views/site/partials/product_card.php'; endforeach; ?>
            </div>

            <?php
            $paginationParams = ['sort' => $sort];
            if ($onlyAvailable) $paginationParams['available'] = '1';
            require APP_ROOT . '/views/site/partials/pagination.php';
            ?>
        <?php else: ?>
            <div class="empty-state">در حال حاضر محصولی در این دسته‌بندی موجود نیست.</div>
        <?php endif; ?>
    </section>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
