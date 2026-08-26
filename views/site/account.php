<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section">
    <h1 style="margin-bottom:8px;">حساب کاربری</h1>
    <p style="color:var(--color-muted); margin-bottom:24px;">
        <?= e($customer['full_name'] ?: 'کاربر') ?> — <span dir="ltr"><?= e($customer['phone']) ?></span>
        &nbsp;•&nbsp; <a href="/logout" style="color:var(--color-danger);">خروج از حساب</a>
    </p>

    <h3 style="margin-bottom:14px;">سفارش‌های من</h3>

    <?php if (!$orders): ?>
        <div class="empty-state">هنوز سفارشی ثبت نکرده‌اید. <a href="/">مشاهده محصولات</a></div>
    <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>کد سفارش</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td dir="ltr"><?= e($o['order_code']) ?></td>
                <td><?= formatPrice($o['total']) ?></td>
                <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e($statusLabels[$o['status']] ?? $o['status']) ?></span></td>
                <td><?= toPersianDigits(date('Y/m/d', strtotime($o['created_at']))) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
