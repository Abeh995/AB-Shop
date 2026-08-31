<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <section class="section">
        <div class="section-title">
            <h1 style="font-size:1.4rem; margin:0;"><?= $query !== '' ? 'نتایج جستجو برای «' . e($query) . '»' : 'جستجوی محصولات' ?></h1>
        </div>

        <?php if ($query === ''): ?>
            <div class="empty-state">عبارتی برای جستجو وارد نشده است.</div>
        <?php elseif ($products): ?>
            <p style="color:var(--color-muted); margin-top:-14px; margin-bottom:20px; font-size:.9rem;"><?= toPersianDigits((string)$total) ?> محصول یافت شد.</p>
            <div class="product-grid">
                <?php foreach ($products as $p): require APP_ROOT . '/views/site/partials/product_card.php'; endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?q=<?= urlencode($query) ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= toPersianDigits((string)$i) ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">محصولی مطابق با «<?= e($query) ?>» پیدا نشد.</div>
        <?php endif; ?>
    </section>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
