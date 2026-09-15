<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section">
    <a href="/account" style="color:var(--color-muted); font-size:.9rem;">← بازگشت به حساب کاربری</a>
    <h1 style="margin:14px 0 6px;">سفارش <span dir="ltr"><?= e($order['order_code']) ?></span></h1>
    <p style="margin-bottom:20px;">
        <span class="status-pill status-<?= e($order['status']) ?>"><?= e($statusLabels[$order['status']] ?? $order['status']) ?></span>
        <span style="color:var(--color-muted); font-size:.85rem; margin-right:8px;"><?= toPersianDigits(date('Y/m/d H:i', strtotime($order['created_at']))) ?></span>
    </p>

    <div class="order-payment-summary">
        <div><span>روش پرداخت</span><strong><?= e($paymentMethodLabels[$order['payment_method']] ?? $order['payment_method']) ?></strong></div>
        <div><span>وضعیت پرداخت</span><strong><?= e($paymentLabels[$order['payment_status']] ?? $order['payment_status']) ?></strong></div>
    </div>

    <div class="order-review" style="max-width:600px;">
        <?php foreach ($items as $it): ?>
            <div class="item-line">
                <span><?= e($it['product_name']) ?><?= $it['variant_label'] ? ' (' . e($it['variant_label']) . ')' : '' ?> × <?= toPersianDigits((string)$it['quantity']) ?></span>
                <span><?= formatPrice($it['line_total']) ?></span>
            </div>
        <?php endforeach; ?>
        <?php foreach ($orderGiftItems as $gi): ?>
            <div class="item-line">
                <span><?= e($gi['name']) ?> × <?= toPersianDigits((string)$gi['quantity']) ?></span>
                <span><?= formatPrice($gi['unit_selling_price'] * $gi['quantity']) ?></span>
            </div>
        <?php endforeach; ?>
        <div class="row" style="margin-top:14px;"><span>جمع کل کالاها</span><span><?= formatPrice($order['subtotal']) ?></span></div>
        <?php if ($order['discount_total'] > 0): ?>
        <div class="row" style="color:var(--color-success);"><span>تخفیف</span><span>−<?= formatPrice($order['discount_total']) ?></span></div>
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

    <div class="admin-card" style="max-width:600px; margin-top:20px;">
        <h3 style="margin-bottom:10px;">اطلاعات ارسال</h3>
        <p><?= e($order['customer_name']) ?> — <span dir="ltr"><?= e($order['phone']) ?></span></p>
        <p><?= e($order['province']) ?>، <?= e($order['city']) ?>، <?= e($order['address']) ?></p>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
