<?php
/**
 * Storefront Product Card Component
 *
 * Pure presentation card supporting catalog links and async post-order upsell actions.
 * Props:
 * - product (array): product or gift item row (also supports fallback $p)
 * - variant (string): 'default' | 'compact'
 * - badgeText (string|null): optional custom badge label
 * - badgeTone (string): 'primary' | 'discount' | 'out' | 'info'
 * - actionForm (array|null): ['action' => string, 'inputs' => array, 'submitLabel' => string, 'asyncMode' => string]
 * - eager (bool): disable lazy loading when true
 */
$product = $product ?? ($p ?? null);
if (empty($product) || !is_array($product)) {
    return;
}

$variant = $variant ?? 'default';
$isCompact = ($variant === 'compact');
$actionForm = !empty($actionForm) && is_array($actionForm) ? $actionForm : null;
$eager = !empty($eager);

$discount = !empty($product['price']) ? discountPercent($product) : 0;
$finalPrice = isset($product['post_order_price'])
    ? (float) $product['post_order_price']
    : effectivePrice($product);
$imgSrc = !empty($product['image']) ? UPLOAD_URL . e($product['image']) : '/assets/img/placeholder-sock.svg';
$stockForBadge = array_key_exists('effective_stock', $product)
    ? (int) $product['effective_stock']
    : (int) ($product['stock'] ?? 1);

$badgeLabel = $badgeText ?? null;
$badgeClass = 'c-badge--discount';
if ($badgeLabel !== null && $badgeLabel !== '') {
    $tone = $badgeTone ?? 'info';
    $badgeClass = $tone === 'out' ? 'c-badge--out' : ($tone === 'discount' ? 'c-badge--discount' : 'c-badge--info');
} elseif ($discount > 0) {
    $badgeLabel = toPersianDigits((string) $discount) . '%-';
    $badgeClass = 'c-badge--discount badge-discount';
} elseif ($stockForBadge <= 0) {
    $badgeLabel = 'ناموجود';
    $badgeClass = 'c-badge--out badge-outofstock';
}

$cardClasses = 'c-product-card product-card';
if ($isCompact) {
    $cardClasses .= ' c-product-card--compact';
}
if (!$actionForm) {
    $cardClasses .= ' catalog-product-item';
}

$slug = (string) ($product['slug'] ?? '');
$catSlug = (string) ($product['category_slug'] ?? '');
?>
<?php if (!$actionForm && $slug !== ''): ?>
<a href="/product/<?= e($slug) ?>" class="<?= e($cardClasses) ?>" data-cat-slug="<?= e($catSlug) ?>">
<?php else: ?>
<article class="<?= e($cardClasses) ?>" data-cat-slug="<?= e($catSlug) ?>">
<?php endif; ?>
    <?php if ($badgeLabel !== null && $badgeLabel !== ''): ?>
        <span class="c-badge c-product-card__badge <?= e($badgeClass) ?>"><?= e($badgeLabel) ?></span>
    <?php endif; ?>

    <div class="c-product-card__thumb thumb">
        <img src="<?= $imgSrc ?>" alt="<?= e($product['name'] ?? '') ?>" width="300" height="300" loading="<?= $eager ? 'eager' : 'lazy' ?>">
    </div>

    <div class="c-product-card__body body">
        <?php if (!empty($product['category_name'])): ?>
            <span class="c-product-card__cat category-pill-label"><?= e($product['category_name']) ?></span>
        <?php endif; ?>

        <div class="c-product-card__title name"><?= e($product['name'] ?? '') ?></div>

        <?php if (!empty($product['tagline'])): ?>
            <div class="c-product-card__tagline"><?= e($product['tagline']) ?></div>
        <?php endif; ?>

        <div class="c-product-card__prices price-row">
            <span class="c-product-card__price price-current"><?= formatPrice($finalPrice) ?></span>
            <?php if ($discount > 0 && isset($product['price'])): ?>
                <span class="c-product-card__old-price price-old"><?= formatPrice($product['price']) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($actionForm && !empty($actionForm['action'])): ?>
            <form method="post"
                  action="<?= e($actionForm['action']) ?>"
                  class="c-mt-2"
                  <?= !empty($actionForm['asyncMode']) ? 'data-ab-async-form="' . e($actionForm['asyncMode']) . '"' : '' ?>>
                <?= csrfField() ?>
                <?php foreach (($actionForm['inputs'] ?? []) as $inputName => $inputValue): ?>
                    <input type="hidden" name="<?= e((string) $inputName) ?>" value="<?= e((string) $inputValue) ?>">
                <?php endforeach; ?>
                <button type="submit" class="c-btn c-btn--sm c-btn--outline c-btn--block">
                    <?= e($actionForm['submitLabel'] ?? 'افزودن') ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
<?php if (!$actionForm && $slug !== ''): ?>
</a>
<?php else: ?>
</article>
<?php endif; ?>
