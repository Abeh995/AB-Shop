<?php
/**
 * کارت محصول — انتظار می‌رود متغیر $p (ردیف محصول از دیتابیس) در دسترس باشد
 */
$discount = discountPercent($p);
$finalPrice = effectivePrice($p);
$imgSrc = $p['image'] ? UPLOAD_URL . e($p['image']) : '/assets/img/placeholder-sock.svg';
?>
<a href="/product/<?= e($p['slug']) ?>" class="product-card">
    <?php if ($discount > 0): ?>
        <span class="badge-discount"><?= toPersianDigits($discount) ?>%-</span>
    <?php elseif ((int)$p['stock'] <= 0): ?>
        <span class="badge-outofstock">ناموجود</span>
    <?php endif; ?>
    <div class="thumb">
        <img src="<?= $imgSrc ?>" alt="<?= e($p['name']) ?>" loading="lazy">
    </div>
    <div class="body">
        <div class="name"><?= e($p['name']) ?></div>
        <div class="price-row">
            <span class="price-current"><?= formatPrice($finalPrice) ?></span>
            <?php if ($discount > 0): ?>
                <span class="price-old"><?= formatPrice($p['price']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</a>
