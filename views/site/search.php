<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <section class="c-section section">
        <div class="section-title">
            <h1><?= $query !== '' ? 'نتایج جستجو برای «' . e($query) . '»' : 'جستجوی محصولات' ?></h1>
        </div>

        <?php if ($query === ''): ?>
            <?php site_component('empty_state', ['message' => 'عبارتی برای جستجو وارد نشده است.']); ?>
        <?php elseif ($products): ?>
            <p class="c-text-muted c-text-sm c-mb-4"><?= toPersianDigits((string) $total) ?> محصول یافت شد.</p>
            <div class="c-product-grid product-grid">
                <?php foreach ($products as $p): site_component('product_card', ['product' => $p]); endforeach; ?>
            </div>

            <?php site_component('pagination', [
                'page' => $page,
                'totalPages' => $totalPages,
                'params' => ['q' => $query],
            ]); ?>
        <?php else: ?>
            <?php site_component('empty_state', ['message' => 'محصولی مطابق با «' . $query . '» پیدا نشد.']); ?>
        <?php endif; ?>
    </section>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
