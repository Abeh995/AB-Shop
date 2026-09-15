<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card">
    <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:18px;">اینجا رسیدهای کارت‌به‌کارت را بازبینی کنید. برای تایید یا رد پرداخت و تغییر وضعیت سفارش، وارد جزئیات همان سفارش شوید.</p>
    <div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>کد سفارش</th><th>مشتری</th><th>مبلغ</th><th>رسید</th><th>پرداخت</th><th>وضعیت سفارش</th><th>تاریخ</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
            <td dir="ltr"><?= e($o['order_code']) ?></td>
            <td><?= e($o['customer_name']) ?><br><span dir="ltr" style="font-size:.78rem; color:var(--color-muted);"><?= e($o['phone']) ?></span></td>
            <td><?= formatPrice($o['total']) ?></td>
            <td><?= $o['card_to_card_receipt'] ? '<span class="status-pill status-delivered">دریافت شده</span>' : '<span class="status-pill status-cancelled">بدون رسید</span>' ?></td>
            <td><span class="status-pill <?= $paymentClasses[$o['payment_status']] ?? '' ?>"><?= e($paymentLabels[$o['payment_status']] ?? $o['payment_status']) ?></span></td>
            <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e($statusLabels[$o['status']] ?? $o['status']) ?></span></td>
            <td><?= toPersianDigits(date('Y/m/d H:i', strtotime($o['created_at']))) ?></td>
            <td><a href="order_detail.php?id=<?= (int)$o['id'] ?>" class="btn btn-sm btn-outline">بازبینی</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?><tr><td colspan="8" style="text-align:center; color:var(--color-muted);">پرداخت کارت‌به‌کارتی ثبت نشده است.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
