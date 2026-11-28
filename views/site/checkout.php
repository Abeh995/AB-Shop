<?php
$pageScripts = ['/assets/js/client/checkout.js'];
require APP_ROOT . '/views/layout/header.php';
?>

<div class="container c-section section">
    <h1 class="c-mb-6">تکمیل خرید</h1>

    <?php site_component('alert', ['state' => 'error', 'messages' => $errors ?? []]); ?>

    <div class="checkout-layout">
        <form method="post" action="/checkout">
            <?= csrfField() ?>

            <div class="c-form-group">
                <label for="coName">نام و نام‌خانوادگی</label>
                <input class="c-form-control" id="coName" type="text" name="customer_name" value="<?= e($formData['customer_name'] ?? '') ?>" required>
            </div>

            <div class="c-form-row">
                <div class="c-form-group">
                    <label for="coPhone">شماره موبایل</label>
                    <input class="c-form-control" id="coPhone" type="tel" name="phone" dir="ltr" placeholder="09123456789" value="<?= e($formData['phone'] ?? '') ?>" required>
                </div>
                <div class="c-form-group">
                    <label for="coEmail">ایمیل (اختیاری)</label>
                    <input class="c-form-control" id="coEmail" type="email" name="email" dir="ltr" value="<?= e($formData['email'] ?? '') ?>">
                </div>
            </div>

            <div class="c-form-row">
                <div class="c-form-group">
                    <label for="provinceInput">استان</label>
                    <input class="c-form-control" type="text" name="province" id="provinceInput" data-ab-shipping-estimate value="<?= e($formData['province'] ?? '') ?>" required>
                </div>
                <div class="c-form-group">
                    <label for="coCity">شهر</label>
                    <input class="c-form-control" id="coCity" type="text" name="city" value="<?= e($formData['city'] ?? '') ?>" required>
                </div>
            </div>
            <p id="shippingEstimateNote" class="c-shipping-note">
                <?php if ($shippingPreview['method_name']): ?>
                    هزینه ارسال (<?= e($shippingPreview['method_name']) ?><?= !empty($shippingPreview['estimated_delivery']) ? ' • ' . e($shippingPreview['estimated_delivery']) : '' ?>): <?= $shippingPreview['is_free'] ? 'رایگان' : formatPrice($shippingPreview['cost']) ?>
                <?php else: ?>
                    هزینه ارسال بر اساس استانی که وارد می‌کنید محاسبه می‌شود.
                <?php endif; ?>
            </p>

            <div class="c-form-group">
                <label for="coAddress">آدرس کامل</label>
                <textarea class="c-form-control" id="coAddress" name="address" required><?= e($formData['address'] ?? '') ?></textarea>
            </div>

            <div class="c-form-group">
                <label for="coPostal">کد پستی (اختیاری)</label>
                <input class="c-form-control" id="coPostal" type="text" dir="ltr" name="postal_code" value="<?= e($formData['postal_code'] ?? '') ?>">
            </div>

            <div class="c-form-group">
                <label for="coNotes">توضیحات سفارش (اختیاری)</label>
                <textarea class="c-form-control" id="coNotes" name="notes"><?= e($formData['notes'] ?? '') ?></textarea>
            </div>

            <div class="c-form-group">
                <label class="group-label">روش پرداخت</label>
                <?php if ($paymentMethodsAvailable): ?>
                <div class="payment-method-options">
                    <?php if ($zarinpalEnabled): ?>
                    <label class="payment-method-option">
                        <input type="radio" name="payment_method" value="zarinpal" <?= (!$cardToCardConfigured) ? 'checked' : '' ?>>
                        <span class="payment-method-copy"><strong>زرین‌پال</strong><small>پرداخت آنلاین امن</small></span>
                    </label>
                    <?php endif; ?>
                    <?php if ($cardToCardConfigured): ?>
                    <label class="payment-method-option">
                        <input type="radio" name="payment_method" value="card_to_card" <?= (!$zarinpalEnabled) ? 'checked' : '' ?>>
                        <span class="payment-method-copy"><strong>کارت به کارت</strong><small>ارسال تصویر فیش برای بررسی ادمین</small></span>
                    </label>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <?php site_component('alert', [
                        'state' => 'error',
                        'message' => 'هیچ روش پرداخت فعالی برای فروشگاه تنظیم نشده است.',
                        'class' => 'c-mb-0',
                    ]); ?>
                <?php endif; ?>
            </div>

            <button type="submit" class="c-btn c-btn--primary c-btn--block" <?= !$paymentMethodsAvailable ? 'disabled' : '' ?>>ادامه و پرداخت سفارش</button>
        </form>

        <?php
        $shippingSummaryLabel = 'هزینه ارسال' . ($shippingPreview['method_name']
            ? ' (' . $shippingPreview['method_name'] . (!empty($shippingPreview['estimated_delivery']) ? ' • ' . $shippingPreview['estimated_delivery'] : '') . ')'
            : '');
        $checkoutFooterHtml = '<p class="c-text-xs c-text-muted c-mt-3 c-mb-0">'
            . 'با انتخاب کارت‌به‌کارت، بعد از ثبت اطلاعات به صفحه پرداخت کارت‌به‌کارت منتقل می‌شوید؛ با انتخاب زرین‌پال، به درگاه پرداخت منتقل خواهید شد.'
            . '</p>';

        site_component('order_summary', [
            'title' => 'خلاصه سفارش',
            'items' => $cart['items'],
            'giftLines' => $postOrderResult['lines'] ?? [],
            'subtotal' => $cart['subtotal'],
            'discount' => $appliedCoupon ? $discount : 0,
            'couponCode' => $appliedCoupon['code'] ?? '',
            'postOrderTotal' => $postOrderResult['total'] ?? 0,
            'shippingLabel' => $shippingSummaryLabel,
            'shippingCost' => (float) ($shippingPreview['cost'] ?? 0),
            'shippingText' => !empty($shippingPreview['is_free']) ? 'رایگان' : '',
            'grandTotal' => max(0, (float) $cart['subtotal'] - (float) $discount + (float) ($postOrderResult['total'] ?? 0) + (float) ($shippingPreview['cost'] ?? 0)),
            'shippingRowId' => 'shippingRow',
            'shippingCostId' => 'shippingCostValue',
            'grandTotalId' => 'grandTotalValue',
            'footerHtml' => $checkoutFooterHtml,
        ]);
        ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
