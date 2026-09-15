<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section">
    <h1 style="margin-bottom:24px;">تکمیل خرید</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="checkout-layout">
        <form method="post" action="/checkout">
            <?= csrfField() ?>

            <div class="form-group">
                <label>نام و نام‌خانوادگی</label>
                <input class="form-control" type="text" name="customer_name" value="<?= e($_POST['customer_name'] ?? ($prefillCustomer['full_name'] ?? '')) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>شماره موبایل</label>
                    <input class="form-control" type="tel" name="phone" dir="ltr" placeholder="09123456789" value="<?= e($_POST['phone'] ?? ($prefillCustomer['phone'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label>ایمیل (اختیاری)</label>
                    <input class="form-control" type="email" name="email" dir="ltr" value="<?= e($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>استان</label>
                    <input class="form-control" type="text" name="province" id="provinceInput" value="<?= e($_POST['province'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>شهر</label>
                    <input class="form-control" type="text" name="city" value="<?= e($_POST['city'] ?? '') ?>" required>
                </div>
            </div>
            <p id="shippingEstimateNote" style="font-size:.85rem; color:var(--color-muted); margin-top:-10px; margin-bottom:16px;">
                <?php if ($shippingPreview['method_name']): ?>
                    هزینه ارسال (<?= e($shippingPreview['method_name']) ?>): <?= $shippingPreview['is_free'] ? 'رایگان' : formatPrice($shippingPreview['cost']) ?>
                <?php else: ?>
                    هزینه ارسال بر اساس استانی که وارد می‌کنید محاسبه می‌شود.
                <?php endif; ?>
            </p>

            <div class="form-group">
                <label>آدرس کامل</label>
                <textarea class="form-control" name="address" required><?= e($_POST['address'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>کد پستی (اختیاری)</label>
                <input class="form-control" type="text" dir="ltr" name="postal_code" value="<?= e($_POST['postal_code'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>توضیحات سفارش (اختیاری)</label>
                <textarea class="form-control" name="notes"><?= e($_POST['notes'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
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
                    <div class="alert alert-error" style="margin:0;">هیچ روش پرداخت فعالی برای فروشگاه تنظیم نشده است.</div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block" <?= !$paymentMethodsAvailable ? 'disabled' : '' ?>>ادامه و پرداخت سفارش</button>
        </form>

        <div class="order-review">
            <h3 style="margin-bottom:14px;">خلاصه سفارش</h3>
            <?php foreach ($cart['items'] as $item): ?>
                <div class="item-line">
                    <span><?= e($item['product']['name']) ?> × <?= toPersianDigits((string)$item['qty']) ?></span>
                    <span><?= formatPrice($item['line_total']) ?></span>
                </div>
            <?php endforeach; ?>
            <?php foreach ($postOrderResult['lines'] as $line): ?>
                <div class="item-line">
                    <span><?= e($line['name']) ?> × <?= toPersianDigits((string)$line['quantity']) ?></span>
                    <span><?= formatPrice($line['line_total']) ?></span>
                </div>
            <?php endforeach; ?>

            <div class="row" style="margin-top:14px;">
                <span>جمع کل کالاها</span>
                <span><?= formatPrice($cart['subtotal']) ?></span>
            </div>
            <?php if ($appliedCoupon && $discount > 0): ?>
            <div class="row" style="color:var(--color-success);">
                <span>تخفیف (<?= e($appliedCoupon['code']) ?>)</span>
                <span>−<?= formatPrice($discount) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($postOrderResult['total'] > 0): ?>
            <div class="row">
                <span>پیشنهاد بعد از سبد</span>
                <span><?= formatPrice($postOrderResult['total']) ?></span>
            </div>
            <?php endif; ?>
            <div class="row" id="shippingRow">
                <span>هزینه ارسال<?= $shippingPreview['method_name'] ? ' (' . e($shippingPreview['method_name']) . ')' : '' ?></span>
                <span id="shippingCostValue"><?= $shippingPreview['is_free'] ? 'رایگان' : formatPrice($shippingPreview['cost']) ?></span>
            </div>
            <div class="row total-row">
                <span>مبلغ قابل پرداخت</span>
                <span id="grandTotalValue"><?= formatPrice($cart['subtotal'] - $discount + $postOrderResult['total'] + $shippingPreview['cost']) ?></span>
            </div>
            <p style="font-size:.82rem; color:var(--color-muted); margin-top:14px;">
                با انتخاب کارت‌به‌کارت، بعد از ثبت اطلاعات به صفحه پرداخت کارت‌به‌کارت منتقل می‌شوید؛ با انتخاب زرین‌پال، به درگاه پرداخت منتقل خواهید شد.
            </p>
        </div>
    </div>
</div>

<script>
(function () {
    var provinceInput = document.getElementById('provinceInput');
    var note = document.getElementById('shippingEstimateNote');
    var shippingRow = document.getElementById('shippingRow');
    var shippingCostValue = document.getElementById('shippingCostValue');
    var grandTotalValue = document.getElementById('grandTotalValue');
    if (!provinceInput) return;

    var timer = null;

    function refreshShipping() {
        var fd = new FormData();
        fd.append('province', provinceInput.value);
        fetch('/ajax/shipping_estimate.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) return;
                var label = data.method_name ? ('هزینه ارسال (' + data.method_name + ')') : 'هزینه ارسال';
                shippingRow.querySelector('span').textContent = label;
                shippingCostValue.textContent = data.is_free ? 'رایگان' : data.cost_formatted;
                note.textContent = data.method_name
                    ? (label + ': ' + (data.is_free ? 'رایگان' : data.cost_formatted))
                    : 'هزینه ارسال بر اساس استانی که وارد می‌کنید محاسبه می‌شود.';
                if (grandTotalValue) grandTotalValue.textContent = data.grand_total_formatted;
            })
            .catch(function () { /* keep the last known estimate on a network error */ });
    }

    provinceInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(refreshShipping, 500);
    });
    provinceInput.addEventListener('blur', refreshShipping);
})();
</script>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
