<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container c-section section">
    <div class="section-title"><h1>محصولات با تگ «<?= e($tag['name']) ?>»</h1></div>

    <?php if ($products): ?>
        <div class="c-product-grid product-grid">
            <?php foreach ($products as $p): site_component('product_card', ['product' => $p]); endforeach; ?>
        </div>
    <?php else: ?>
        <?php site_component('empty_state', ['message' => 'محصولی با این تگ یافت نشد.']); ?>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
