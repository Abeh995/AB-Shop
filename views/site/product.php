<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <?php if (!empty($breadcrumbs)): ?>
    <nav class="breadcrumb-trail" aria-label="مسیر صفحه" style="margin: 1rem 0; font-size: 0.85rem; color: var(--color-muted);">
        <?php foreach ($breadcrumbs as $bi => $bc): ?>
            <?php if ($bi > 0): ?><span style="margin: 0 6px; opacity: 0.6;">/</span><?php endif; ?>
            <?php if ($bi === count($breadcrumbs) - 1): ?>
                <span style="color: var(--color-text); font-weight: 600;"><?= e($bc['name']) ?></span>
            <?php else: ?>
                <a href="<?= e($bc['url']) ?>" style="color: inherit; text-decoration: none;"><?= e($bc['name']) ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <div class="product-detail">

        <?php $mainImgAttrs = seoBuildImageAttributes($product, 1, true); ?>
        <div class="product-gallery">
            <img id="mainProductImage" src="<?= $mainImage ?>" alt="<?= e($mainImgAttrs['alt']) ?>" title="<?= e($mainImgAttrs['title']) ?>">
            <?php if (count($gallery) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($gallery as $i => $img): 
                    $thumbAttrs = seoBuildImageAttributes($product, $i + 1, false);
                ?>
                    <img src="<?= UPLOAD_URL . e($img) ?>" data-full="<?= UPLOAD_URL . e($img) ?>" class="<?= $i === 0 ? 'active' : '' ?>" alt="<?= e($thumbAttrs['alt']) ?>" title="<?= e($thumbAttrs['title']) ?>">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <div class="cat-label"><a href="/category/<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a></div>
            <h1><?= e($product['name']) ?></h1>

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

            <?php if (!empty($materialSpecs)): ?>
            <div class="product-specs" style="margin-top: 1.25rem; border: 1px solid var(--color-border); border-radius: 8px; padding: 12px 16px; background: rgba(0,0,0,0.01);">
                <h3 style="font-size: 0.92rem; font-weight: 700; margin-bottom: 8px;">مشخصات الیاف و بافت</h3>
                <ul style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px; font-size: 0.85rem;">
                    <?php foreach ($materialSpecs as $specKey => $specVal): ?>
                        <li><span style="color: var(--color-muted);"><?= e($specKey) ?>:</span> <strong><?= e($specVal) ?></strong></li>
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
    <div class="product-faq-section" style="margin-top: 2.5rem; border-top: 1px solid var(--color-border); padding-top: 1.5rem;">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            پرسش‌های متداول مشتریان درباره <?= e($product['name']) ?>
        </h2>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($productFaq['mainEntity'] as $faq): ?>
                <details style="background: rgba(0,0,0,0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: 12px 16px; cursor: pointer;">
                    <summary style="font-weight: 600; font-size: 0.95rem; color: var(--color-text); outline: none;">
                        <?= e($faq['name']) ?>
                    </summary>
                    <div style="margin-top: 8px; font-size: 0.88rem; line-height: 1.7; color: var(--color-muted);">
                        <?= nl2br(e($faq['acceptedAnswer']['text'])) ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
