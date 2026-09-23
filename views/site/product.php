<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <div class="product-detail">

        <div class="product-gallery">
            <img id="mainProductImage" src="<?= $mainImage ?>" alt="<?= e($product['name']) ?>">
            <?php if (count($gallery) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($gallery as $i => $img): ?>
                    <img src="<?= UPLOAD_URL . e($img) ?>" data-full="<?= UPLOAD_URL . e($img) ?>" class="<?= $i === 0 ? 'active' : '' ?>">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <div class="cat-label"><a href="/category/<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a></div>
            <h1><?= e($product['name']) ?></h1>

            <?php
            $selectedVariant = null;
            $selectedVariantLabel = '';
            if ($hasVariants) {
                foreach ($variants as $v) {
                    if ($defaultVariantId !== null && (int)$v['id'] === (int)$defaultVariantId) {
                        $selectedVariant = $v;
                        break;
                    }
                }
                if (!$selectedVariant && !empty($variants)) {
                    $selectedVariant = $variants[0];
                }
                if ($selectedVariant) {
                    $selectedVariantLabel = trim(($selectedVariant['size'] ?? '') . ' ' . ($selectedVariant['color'] ?? '')) ?: 'استاندارد';
                }
            }
            $initialVariantStock = $selectedVariant ? (int)$selectedVariant['stock'] : $totalStock;
            $initialMaxQty = min(max(1, $initialVariantStock), 20);
            $initialPrice = ($selectedVariant && $selectedVariant['price_override'] !== null) ? (float)$selectedVariant['price_override'] : (float)$finalPrice;
            ?>

            <div class="price-box" id="productPriceBox">
                <span class="price-current" id="productPriceCurrent"><?= formatPrice($initialPrice) ?></span>
                <?php if ($discount > 0): ?>
                    <span class="price-old" id="productPriceOld"><?= formatPrice($product['price']) ?></span>
                    <span class="badge-discount" id="productBadgeDiscount" style="position:static;"><?= toPersianDigits($discount) ?>%-</span>
                <?php endif; ?>
            </div>

            <div class="stock-info" id="productStockInfo">
                <?php if ($initialVariantStock <= 0): ?>
                    <span class="stock-out">ناموجود</span>
                <?php elseif ($initialVariantStock <= 5): ?>
                    <span class="stock-low">فقط <?= toPersianDigits((string)$initialVariantStock) ?> عدد باقی مانده</span>
                <?php else: ?>
                    <span class="stock-ok">موجود در انبار</span>
                <?php endif; ?>
            </div>

            <?php if ($totalStock > 0): ?>
            <form id="addToCartForm">
                <?= csrfField() ?>
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                <?php if ($hasVariants): ?>
                <div class="variant-group">
                    <label class="group-label">
                        سایز / رنگ:
                        <span id="selectedVariantLabel" class="selected-variant-label"><?= e($selectedVariantLabel) ?></span>
                    </label>
                    <div class="variant-options" id="variantOptions">
                        <?php foreach ($variants as $v):
                            $label = trim(($v['size'] ?? '') . ' ' . ($v['color'] ?? ''));
                            $disabled = (int)$v['stock'] <= 0;
                            $isSelected = ($selectedVariant && (int)$v['id'] === (int)$selectedVariant['id']);
                            $vPrice = $v['price_override'] !== null ? (float)$v['price_override'] : (float)$finalPrice;
                        ?>
                        <label class="variant-chip <?= $disabled ? 'disabled' : '' ?> <?= $isSelected ? 'selected' : '' ?>">
                            <input type="radio" name="variant_id" value="<?= (int)$v['id'] ?>"
                                   <?= $disabled ? 'disabled' : '' ?>
                                   <?= $isSelected ? 'checked' : '' ?>
                                   data-stock="<?= (int)$v['stock'] ?>"
                                   data-price="<?= (float)$vPrice ?>"
                                   data-price-formatted="<?= formatPrice($vPrice) ?>"
                                   data-label="<?= e($label ?: 'استاندارد') ?>">
                            <span class="variant-chip-text"><?= e($label ?: 'استاندارد') ?></span>
                            <?php if ($disabled): ?>
                                <span class="variant-chip-badge">ناموجود</span>
                            <?php endif; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="variant-group">
                    <label class="group-label">تعداد</label>
                    <div class="qty-selector">
                        <button type="button" class="qty-minus">−</button>
                        <input type="number" id="productQtyInput" name="qty" value="1" min="1" max="<?= $initialMaxQty ?>">
                        <button type="button" class="qty-plus">+</button>
                    </div>
                </div>

                <button type="submit" id="addToCartBtn" class="btn btn-primary btn-block" <?= ($selectedVariant && (int)$selectedVariant['stock'] <= 0) ? 'disabled' : '' ?>>
                    <?= ($selectedVariant && (int)$selectedVariant['stock'] <= 0) ? 'این گزینه ناموجود است' : 'افزودن به سبد خرید' ?>
                </button>
            </form>
            <?php else: ?>
                <button class="btn btn-outline btn-block" disabled>ناموجود</button>
            <?php endif; ?>

            <?php if ($product['description']): ?>
            <div class="product-description">
                <?= nl2br(e($product['description'])) ?>
            </div>
            <?php endif; ?>

            <?php if ($tags): ?>
            <div class="product-tags">
                <?php foreach ($tags as $tag): ?>
                    <a href="/tag/<?= e($tag['slug']) ?>" class="tag-pill" rel="tag"><?= e($tag['name']) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
