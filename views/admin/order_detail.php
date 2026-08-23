<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="checkout-layout">
    <div>
        <div class="admin-card">
            <h3 style="margin-bottom:14px;">اقلام سفارش</h3>
            <table class="admin-table">
                <thead><tr><th>محصول</th><th>ویژگی</th><th>تعداد</th><th>قیمت واحد</th><th>جمع</th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= e($it['product_name']) ?></td>
                    <td><?= e($it['variant_label'] ?: '—') ?></td>
                    <td><?= toPersianDigits((string)$it['quantity']) ?></td>
                    <td><?= formatPrice($it['unit_price']) ?></td>
                    <td><?= formatPrice($it['line_total']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:16px;">
                <div class="row"><span>جمع کل کالاها</span><span><?= formatPrice($order['subtotal']) ?></span></div>
                <?php if ($order['discount_total'] > 0): ?>
                <div class="row" style="color:var(--color-success);">
                    <span>تخفیف <?= $order['coupon_code'] ? '(' . e($order['coupon_code']) . ')' : '' ?></span>
                    <span>−<?= formatPrice($order['discount_total']) ?></span>
                </div>
                <?php endif; ?>
                <div class="row total-row"><span>مبلغ نهایی</span><span><?= formatPrice($order['total']) ?></span></div>
            </div>
        </div>

        <div class="admin-card">
            <h3 style="margin-bottom:14px;">اطلاعات مشتری و ارسال</h3>
            <p><strong>نام:</strong> <?= e($order['customer_name']) ?></p>
            <p><strong>موبایل:</strong> <span dir="ltr"><?= e($order['phone']) ?></span></p>
            <?php if ($order['email']): ?><p><strong>ایمیل:</strong> <span dir="ltr"><?= e($order['email']) ?></span></p><?php endif; ?>
            <p><strong>آدرس:</strong> <?= e($order['province']) ?>، <?= e($order['city']) ?>، <?= e($order['address']) ?></p>
            <?php if ($order['postal_code']): ?><p><strong>کد پستی:</strong> <span dir="ltr"><?= e($order['postal_code']) ?></span></p><?php endif; ?>
            <?php if ($order['notes']): ?><p><strong>توضیحات:</strong> <?= e($order['notes']) ?></p><?php endif; ?>
        </div>

        <div class="admin-card">
            <h3 style="margin-bottom:14px;">اطلاعات پرداخت</h3>
            <?php
            $payLabels = ['unpaid' => 'پرداخت‌نشده', 'paid' => 'پرداخت‌شده', 'failed' => 'ناموفق'];
            $payClass = ['unpaid' => 'status-pending', 'paid' => 'status-delivered', 'failed' => 'status-cancelled'];
            ?>
            <p><strong>وضعیت پرداخت:</strong> <span class="status-pill <?= $payClass[$order['payment_status']] ?? '' ?>"><?= e($payLabels[$order['payment_status']] ?? $order['payment_status']) ?></span></p>
            <?php if ($order['payment_ref_id']): ?><p><strong>کد پیگیری زرین‌پال:</strong> <span dir="ltr"><?= e($order['payment_ref_id']) ?></span></p><?php endif; ?>
        </div>
    </div>

    <div class="admin-card">
        <h3 style="margin-bottom:14px;">وضعیت سفارش</h3>
        <p style="margin-bottom:14px;"><span class="status-pill status-<?= e($order['status']) ?>"><?= e($statusLabels[$order['status']]) ?></span></p>
        <form method="post">
            <?= csrfField() ?>
            <div class="form-group">
                <select class="form-control" name="status">
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">به‌روزرسانی وضعیت</button>
        </form>
        <p style="font-size:.78rem; color:var(--color-muted); margin-top:10px;">
            با تغییر وضعیت، پیامک اطلاع‌رسانی به مشتری ارسال می‌شود (در صورت فعال بودن سرویس پیامک).
        </p>
    </div>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
