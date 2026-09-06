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
                <?php if (!empty($order['gift_items_total']) && $order['gift_items_total'] > 0): ?>
                <div class="row"><span>پیشنهاد بعد از سبد</span><span><?= formatPrice($order['gift_items_total']) ?></span></div>
                <?php endif; ?>
                <div class="row">
                    <span>هزینه ارسال<?= !empty($order['shipping_method_name']) ? ' (' . e($order['shipping_method_name']) . ')' : '' ?></span>
                    <span><?= $order['shipping_cost'] > 0 ? formatPrice($order['shipping_cost']) : 'رایگان' ?></span>
                </div>
                <div class="row total-row"><span>مبلغ نهایی</span><span><?= formatPrice($order['total']) ?></span></div>
            </div>
        </div>

        <?php if ($orderGiftItems): ?>
        <div class="admin-card">
            <h3 style="margin-bottom:14px;">هدایا و پیشنهادهای بعد از سبد</h3>
            <table class="admin-table">
                <thead><tr><th>نام</th><th>نوع</th><th>تعداد</th><th>قیمت واحد</th><th>یادداشت</th></tr></thead>
                <tbody>
                <?php foreach ($orderGiftItems as $gi): ?>
                <tr>
                    <td><?= e($gi['name']) ?></td>
                    <td>
                        <?php if ($gi['role'] === 'gift'): ?>
                            <span class="status-pill status-delivered">🎁 هدیه<?= $gi['admin_username'] ? ' — ' . e($gi['admin_username']) : '' ?></span>
                        <?php else: ?>
                            <span class="status-pill status-confirmed">پیشنهاد بعد از سبد</span>
                        <?php endif; ?>
                    </td>
                    <td><?= toPersianDigits((string)$gi['quantity']) ?></td>
                    <td><?= $gi['unit_selling_price'] > 0 ? formatPrice((int)$gi['unit_selling_price']) : 'رایگان' ?></td>
                    <td><?= e($gi['note'] ?: '—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if ($giftableItems): ?>
        <div class="admin-card">
            <h3 style="margin-bottom:6px;">اهدای یک هدیه به این سفارش</h3>
            <p style="color:var(--color-muted); font-size:.85rem; margin-bottom:14px;">این آیتم رایگان به مشتری تعلق می‌گیرد و از موجودی کم می‌شود؛ مبلغی به فاکتور مشتری اضافه نمی‌شود.</p>
            <form method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="assign_gift">
                <div class="form-group" style="flex:1; min-width:180px;">
                    <label>آیتم هدیه</label>
                    <select class="form-control" name="gift_item_id" required>
                        <?php foreach ($giftableItems as $gi): ?>
                            <option value="<?= (int)$gi['id'] ?>">
                                <?= e($gi['name']) ?> (موجودی: <?= toPersianDigits((string)$gi['stock']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="width:100px;">
                    <label>تعداد</label>
                    <input class="form-control" type="number" name="quantity" value="1" min="1" required>
                </div>
                <div class="form-group" style="flex:1; min-width:180px;">
                    <label>یادداشت (اختیاری)</label>
                    <input class="form-control" type="text" name="note" placeholder="مثلا: جبران تأخیر ارسال">
                </div>
                <button type="submit" class="btn btn-primary">اهدا کن</button>
            </form>
        </div>
        <?php endif; ?>

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

<?php if (isSuperAdmin()): ?>
<div class="admin-card" style="border-color:var(--color-danger);">
    <h3 style="margin-bottom:10px; color:var(--color-danger);">منطقه خطر</h3>
    <p style="font-size:.85rem; color:var(--color-muted); margin-bottom:14px;">
        حذف سفارش غیرقابل بازگشت است و تمام اقلام مرتبط با آن نیز حذف می‌شود. این عملیات فقط برای مدیر کل در دسترس است.
    </p>
    <form method="post" onsubmit="return confirm('آیا از حذف کامل این سفارش مطمئن هستید؟ این عملیات قابل بازگشت نیست.');">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="btn btn-danger">حذف کامل سفارش</button>
    </form>
</div>
<?php endif; ?>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
