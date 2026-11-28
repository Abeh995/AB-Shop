<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container c-section section">
    <a href="/account" class="c-text-muted c-text-sm">← بازگشت به حساب کاربری</a>
    <h1 class="c-mt-3 c-mb-2">سفارش <span dir="ltr"><?= e($order['order_code']) ?></span></h1>
    <p class="c-mb-4 c-cluster c-cluster--sm">
        <span class="status-pill status-<?= e($order['status']) ?>"><?= e($statusLabels[$order['status']] ?? $order['status']) ?></span>
        <span class="c-text-muted c-text-sm"><?= toPersianDigits(date('Y/m/d H:i', strtotime($order['created_at']))) ?></span>
    </p>

    <div class="order-payment-summary">
        <div><span>روش پرداخت</span><strong><?= e($paymentMethodLabels[$order['payment_method']] ?? $order['payment_method']) ?></strong></div>
        <div><span>وضعیت پرداخت</span><strong><?= e($paymentLabels[$order['payment_status']] ?? $order['payment_status']) ?></strong></div>
    </div>

    <?php
    $orderShippingLabel = 'هزینه ارسال' . (!empty($order['shipping_method_name']) ? ' (' . $order['shipping_method_name'] . ')' : '');
    site_component('order_summary', [
        'title' => 'جزئیات اقلام و فاکتور',
        'class' => 'c-max-w-md',
        'items' => $items,
        'giftLines' => $orderGiftItems,
        'subtotal' => $order['subtotal'],
        'discount' => $order['discount_total'],
        'postOrderTotal' => $order['gift_items_total'] ?? 0,
        'shippingLabel' => $orderShippingLabel,
        'shippingCost' => (float) $order['shipping_cost'],
        'grandTotal' => $order['total'],
        'totalLabel' => 'مبلغ نهایی',
    ]);
    ?>

    <div class="customer-card c-max-w-md c-mt-4">
        <h3 class="c-mb-2">اطلاعات ارسال</h3>
        <p class="c-mb-1"><?= e($order['customer_name']) ?> — <span dir="ltr"><?= e($order['phone']) ?></span></p>
        <p class="c-mb-0"><?= e($order['province']) ?>، <?= e($order['city']) ?>، <?= e($order['address']) ?></p>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
