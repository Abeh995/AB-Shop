<?php
/**
 * Product card — expects the $p variable (a product row from the database)
 * to be available.
 * If the parent query computed the effective_stock column (the sum of
 * variants, when present), that's used; otherwise it falls back to the
 * plain stock column, just to be safe.
 */
$discount = discountPercent($p);
$finalPrice = effectivePrice($p);
$imgSrc = $p['image'] ? UPLOAD_URL . e($p['image']) : '/assets/img/placeholder-sock.svg';
$stockForBadge = array_key_exists('effective_stock', $p) ? (int) $p['effective_stock'] : (int) $p['stock'];
?>
<a href="/product/<?= e($p['slug']) ?>" class="product-card">
    <?php if ($discount > 0): ?>
        <span class="badge-discount"><?= toPersianDigits($discount) ?>%-</span>
    <?php elseif ($stockForBadge <= 0): ?>
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
