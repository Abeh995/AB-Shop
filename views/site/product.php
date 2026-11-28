<?php
$pageScripts = ['/assets/js/client/product.js'];
require APP_ROOT . '/views/layout/header.php';
?>

<div class="container">
    <?php site_component('breadcrumb', ['items' => $breadcrumbs ?? []]); ?>

    <div class="product-detail">

        <?php $mainImgAttrs = seoBuildImageAttributes($product, 1, true); ?>
        <div class="product-gallery" data-ab-gallery>
            <img id="mainProductImage" src="<?= $mainImage ?>" width="600" height="600" fetchpriority="high" alt="<?= e($mainImgAttrs['alt']) ?>" title="<?= e($mainImgAttrs['title']) ?>">
            <?php if (count($gallery) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($gallery as $i => $img): 
                    $thumbAttrs = seoBuildImageAttributes($product, $i + 1, false);
                ?>
                    <button type="button" class="gallery-thumb<?= $i === 0 ? ' active' : '' ?>" data-full="<?= UPLOAD_URL . e($img) ?>" aria-label="نمایش تصویر <?= toPersianDigits((string)($i + 1)) ?>">
                        <img src="<?= UPLOAD_URL . e($img) ?>" width="64" height="64" loading="lazy" alt="<?= e($thumbAttrs['alt']) ?>" title="<?= e($thumbAttrs['title']) ?>">
                    </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <div class="cat-label"><a href="/category/<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a></div>
            <h1><?= e($product['name']) ?></h1>

            <?php
            $basePrice = (float) ($product['price'] ?? 0);
            $hasInitialDiscount = ($basePrice > 0 && $initialPrice < $basePrice);
            $initialDiscountPct = $hasInitialDiscount ? (int) round((($basePrice - $initialPrice) / $basePrice) * 100) : 0;
            ?>
            <div class="price-box" id="productPriceBox">
                <span class="price-current" id="productPriceCurrent"><?= formatPrice($initialPrice) ?></span>
                <span class="price-old" id="productPriceOld"<?= $hasInitialDiscount ? '' : ' hidden' ?>><?= $hasInitialDiscount ? formatPrice($basePrice) : '' ?></span>
                <span class="badge-discount" id="productBadgeDiscount"<?= $hasInitialDiscount ? '' : ' hidden' ?>><?= $hasInitialDiscount ? toPersianDigits((string) $initialDiscountPct) . '%-' : '' ?></span>
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
            <form id="addToCartForm" method="post" action="/ajax/cart_add.php" data-ab-async-form="cart-add">
                <?= csrfField() ?>
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                <?php if ($hasVariants): ?>
                <div class="variant-group">
                    <label class="group-label">
                        سایز / رنگ:
                        <span id="selectedVariantLabel" class="selected-variant-label"><?= e($selectedVariantLabel) ?></span>
                    </label>
                    <div class="variant-options" id="variantOptions" data-ab-variant-group>
                        <?php foreach ($variants as $v):
                            $label = trim(($v['size'] ?? '') . ' ' . ($v['color'] ?? ''));
                            $disabled = (int)$v['stock'] <= 0;
                            $isSelected = ($selectedVariant && (int)$v['id'] === (int)$selectedVariant['id']);
                            $vPrice = $v['price_override'] !== null ? (float)$v['price_override'] : (float)$finalPrice;
                            $vHasDiscount = ($basePrice > 0 && $vPrice < $basePrice);
                            $vOldFormatted = $vHasDiscount ? formatPrice($basePrice) : '';
                            $vDiscountPct = $vHasDiscount ? (int) round((($basePrice - $vPrice) / $basePrice) * 100) : 0;
                        ?>
                        <label class="variant-chip <?= $disabled ? 'disabled' : '' ?> <?= $isSelected ? 'selected' : '' ?>">
                            <input type="radio" name="variant_id" value="<?= (int)$v['id'] ?>"
                                   <?= $disabled ? 'disabled' : '' ?>
                                   <?= $isSelected ? 'checked' : '' ?>
                                   data-stock="<?= (int)$v['stock'] ?>"
                                   data-price="<?= (float)$vPrice ?>"
                                   data-price-formatted="<?= formatPrice($vPrice) ?>"
                                   data-old-price-formatted="<?= e($vOldFormatted) ?>"
                                   data-discount-percent="<?= (int)$vDiscountPct ?>"
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
                    <label class="group-label" for="productQtyInput">تعداد</label>
                    <?php site_component('qty_stepper', [
                        'id' => 'productQtyInput',
                        'name' => 'qty',
                        'value' => 1,
                        'min' => 1,
                        'max' => $initialMaxQty,
                    ]); ?>
                </div>

                <button type="submit" id="addToCartBtn" class="c-btn c-btn--primary c-btn--block" <?= ($selectedVariant && (int)$selectedVariant['stock'] <= 0) ? 'disabled' : '' ?>>
                    <?= ($selectedVariant && (int)$selectedVariant['stock'] <= 0) ? 'این گزینه ناموجود است' : 'افزودن به سبد خرید' ?>
                </button>
            </form>
            <?php else: ?>
                <button type="button" class="c-btn c-btn--outline c-btn--block" disabled>ناموجود</button>
            <?php endif; ?>

            <?php if ($product['description']): ?>
            <div class="product-description">
                <?= nl2br(e($product['description'])) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($materialSpecs)): ?>
            <div class="product-specs">
                <h3 class="product-specs__title">مشخصات الیاف و بافت</h3>
                <ul class="product-specs__list">
                    <?php foreach ($materialSpecs as $specKey => $specVal): ?>
                        <li><span class="c-text-muted"><?= e($specKey) ?>:</span> <strong><?= e($specVal) ?></strong></li>
                    <?php endforeach; ?>
                </ul>
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

    <?php if (!empty($productFaq['mainEntity'])): ?>
        <?php site_component('faq_accordion', [
            'title' => 'پرسش‌های متداول مشتریان درباره ' . ($product['name'] ?? ''),
            'items' => $productFaq['mainEntity'],
            'icon' => 'question',
        ]); ?>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
