<?php
/**
 * Storefront Order Summary Panel Component
 *
 * Unified invoice breakdown across cart, checkout, card-to-card, and account order detail.
 * Props:
 * - title (string): heading text
 * - items (array): product/order line items
 * - giftLines (array): post-order or gift line items
 * - subtotal (float|int|null): merchandise subtotal
 * - discount (float|int): applied discount total
 * - couponCode (string|null): applied coupon label
 * - postOrderTotal (float|int): sum of post-order add-ons
 * - shippingLabel (string|null): shipping row caption
 * - shippingCost (float|int|null): numeric shipping fee (0 renders as رایگان; null omits unless shippingText is set)
 * - shippingText (string|null): optional override text for shipping cost cell
 * - grandTotal (float|int): final payable total
 * - totalLabel (string): final total caption
 * - showItems (bool): whether to list line items above subtotal
 * - shippingRowId (string|null): optional DOM id on shipping row
 * - shippingCostId (string|null): optional DOM id on shipping cost span
 * - grandTotalId (string|null): optional DOM id on grand total span
 * - couponFormHtml (string|null): TRUSTED pre-rendered coupon slot markup (caller must escape dynamic values with e())
 * - footerHtml (string|null): TRUSTED pre-rendered footer CTA / recipient slot markup (caller must escape dynamic values with e())
 * - class (string): optional container classes
 */
$title = isset($title) && (string) $title !== '' ? (string) $title : 'خلاصه سفارش';
$items = !empty($items) && is_array($items) ? $items : [];
$giftLines = !empty($giftLines) && is_array($giftLines) ? $giftLines : [];
$showItems = $showItems ?? true;
$subtotal = $subtotal ?? null;
$discount = (float) ($discount ?? 0);
$couponCode = isset($couponCode) ? trim((string) $couponCode) : '';
$postOrderTotal = (float) ($postOrderTotal ?? 0);
$shippingLabel = isset($shippingLabel) && (string) $shippingLabel !== '' ? (string) $shippingLabel : 'هزینه ارسال';
$shippingCost = $shippingCost ?? null;
$shippingText = isset($shippingText) ? (string) $shippingText : null;
$grandTotal = (float) ($grandTotal ?? 0);
$totalLabel = isset($totalLabel) && (string) $totalLabel !== '' ? (string) $totalLabel : 'مبلغ قابل پرداخت';
$shippingRowId = isset($shippingRowId) ? trim((string) $shippingRowId) : '';
$shippingCostId = isset($shippingCostId) ? trim((string) $shippingCostId) : '';
$grandTotalId = isset($grandTotalId) ? trim((string) $grandTotalId) : '';
$couponFormHtml = isset($couponFormHtml) ? (string) $couponFormHtml : '';
$footerHtml = isset($footerHtml) ? (string) $footerHtml : '';
$extraClass = isset($class) ? trim((string) $class) : '';
?>
<aside class="c-order-summary order-review<?= $extraClass !== '' ? ' ' . e($extraClass) : '' ?>">
    <h3 class="c-order-summary__title"><?= e($title) ?></h3>

    <?php if ($showItems): ?>
        <?php foreach ($items as $item):
            $itemName = (string) ($item['product']['name'] ?? ($item['product_name'] ?? ($item['name'] ?? '')));
            $variantLabel = (string) ($item['variant_label'] ?? '');
            if ($variantLabel === '' && !empty($item['variant']) && is_array($item['variant'])) {
                $variantLabel = trim((string) ($item['variant']['size'] ?? '') . ' ' . (string) ($item['variant']['color'] ?? ''));
            }
            $qty = (int) ($item['qty'] ?? ($item['quantity'] ?? 1));
            $lineTotal = (float) ($item['line_total'] ?? 0);
        ?>
            <div class="c-order-summary__item item-line">
                <span><?= e($itemName) ?><?= $variantLabel !== '' ? ' (' . e($variantLabel) . ')' : '' ?> × <?= toPersianDigits((string) $qty) ?></span>
                <span><?= formatPrice($lineTotal) ?></span>
            </div>
        <?php endforeach; ?>

        <?php foreach ($giftLines as $gl):
            $gName = (string) ($gl['name'] ?? '');
            $gQty = (int) ($gl['quantity'] ?? ($gl['qty'] ?? 1));
            $gTotal = isset($gl['line_total'])
                ? (float) $gl['line_total']
                : ((float) ($gl['unit_selling_price'] ?? 0) * $gQty);
        ?>
            <div class="c-order-summary__item item-line">
                <span><?= e($gName) ?> × <?= toPersianDigits((string) $gQty) ?></span>
                <span><?= formatPrice($gTotal) ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($subtotal !== null): ?>
        <div class="c-order-summary__row row">
            <span>جمع کل کالاها</span>
            <span><?= formatPrice((float) $subtotal) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($discount > 0): ?>
        <div class="c-order-summary__row c-text-success c-fw-bold row">
            <span>تخفیف<?= $couponCode !== '' ? ' (' . e($couponCode) . ')' : '' ?></span>
            <span>−<?= formatPrice($discount) ?></span>
        </div>
    <?php elseif ($couponCode !== ''): ?>
        <div class="c-order-summary__row c-text-success c-fw-bold row">
            <span>کد تخفیف (<?= e($couponCode) ?>)</span>
            <span>ارسال رایگان</span>
        </div>
    <?php endif; ?>

    <?php if ($couponFormHtml !== ''): ?>
        <div class="c-mt-3 c-mb-2">
            <?= $couponFormHtml ?>
        </div>
    <?php endif; ?>

    <?php if ($postOrderTotal > 0): ?>
        <div class="c-order-summary__row row">
            <span>پیشنهاد بعد از سبد</span>
            <span><?= formatPrice($postOrderTotal) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($shippingCost !== null || ($shippingText !== null && $shippingText !== '')): ?>
        <div class="c-order-summary__row row"<?= $shippingRowId !== '' ? ' id="' . e($shippingRowId) . '"' : '' ?>>
            <span><?= e($shippingLabel) ?></span>
            <span<?= $shippingCostId !== '' ? ' id="' . e($shippingCostId) . '"' : '' ?>>
                <?= $shippingText !== null && $shippingText !== ''
                    ? e($shippingText)
                    : ((float) $shippingCost > 0 ? formatPrice((float) $shippingCost) : 'رایگان') ?>
            </span>
        </div>
    <?php endif; ?>

    <div class="c-order-summary__total total-row">
        <span><?= e($totalLabel) ?></span>
        <span<?= $grandTotalId !== '' ? ' id="' . e($grandTotalId) . '"' : '' ?>><?= formatPrice($grandTotal) ?></span>
    </div>

    <?php if ($footerHtml !== ''): ?>
        <div class="c-mt-3">
            <?= $footerHtml ?>
        </div>
    <?php endif; ?>
</aside>
