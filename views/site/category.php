<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <section class="section">
        <div class="section-title">
            <h2><?= e($category['name']) ?></h2>
        </div>

        <?php if ($category['description']): ?>
            <p style="color:var(--color-muted); margin-top:-14px; margin-bottom:24px;"><?= e($category['description']) ?></p>
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

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?sort=<?= e($sort) ?><?= $onlyAvailable ? '&available=1' : '' ?>&page=<?= $i ?>"
                       class="<?= $i === $page ? 'active' : '' ?>"><?= toPersianDigits((string)$i) ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">در حال حاضر محصولی در این دسته‌بندی موجود نیست.</div>
        <?php endif; ?>
    </section>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
