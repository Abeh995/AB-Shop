<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section">
    <div class="section-title"><h1 style="font-size:1.4rem; margin:0;">محصولات با تگ «<?= e($tag['name']) ?>»</h1></div>

    <?php if ($products): ?>
        <div class="product-grid">
            <?php foreach ($products as $p): require APP_ROOT . '/views/site/partials/product_card.php'; endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">محصولی با این تگ یافت نشد.</div>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
