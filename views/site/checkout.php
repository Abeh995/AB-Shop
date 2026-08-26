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
                    <input class="form-control" type="text" name="province" value="<?= e($_POST['province'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>شهر</label>
                    <input class="form-control" type="text" name="city" value="<?= e($_POST['city'] ?? '') ?>" required>
                </div>
            </div>

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
                <div class="variant-options">
                    <label class="variant-chip selected">
                        <input type="radio" name="payment_method" value="online" checked> پرداخت آنلاین (زرین‌پال)
                    </label>
                    <label class="variant-chip">
                        <input type="radio" name="payment_method" value="cod"> پرداخت در محل / هماهنگی تلفنی
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">ثبت و پرداخت سفارش</button>
        </form>

        <div class="order-review">
            <h3 style="margin-bottom:14px;">خلاصه سفارش</h3>
            <?php foreach ($cart['items'] as $item): ?>
                <div class="item-line">
                    <span><?= e($item['product']['name']) ?> × <?= toPersianDigits((string)$item['qty']) ?></span>
                    <span><?= formatPrice($item['line_total']) ?></span>
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
            <div class="row total-row">
                <span>مبلغ قابل پرداخت</span>
                <span><?= formatPrice($cart['subtotal'] - $discount) ?></span>
            </div>
            <p style="font-size:.82rem; color:var(--color-muted); margin-top:14px;">
                در صورت انتخاب پرداخت آنلاین، بعد از ثبت فرم به درگاه امن زرین‌پال منتقل می‌شوید.
            </p>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
