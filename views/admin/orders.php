<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="display:flex; gap:8px; flex-wrap:wrap;">
    <a href="orders.php" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-outline' ?>">همه</a>
    <?php foreach ($statusLabels as $key => $label): ?>
        <a href="?status=<?= e($key) ?>" class="btn btn-sm <?= $statusFilter === $key ? 'btn-primary' : 'btn-outline' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th>کد سفارش</th><th>مشتری</th><th>موبایل</th><th>اقلام سفارش</th><th>مبلغ</th><th>پرداخت</th><th>وضعیت</th><th>تاریخ</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o):
            $payLabels = ['unpaid' => 'پرداخت‌نشده', 'paid' => 'پرداخت‌شده', 'failed' => 'ناموفق'];
            $payClass = ['unpaid' => 'status-pending', 'paid' => 'status-delivered', 'failed' => 'status-cancelled'];
        ?>
        <tr>
            <td dir="ltr"><?= e($o['order_code']) ?></td>
            <td><?= e($o['customer_name']) ?></td>
            <td dir="ltr"><?= e($o['phone']) ?></td>
            <td class="cell-multiline">
                <?php foreach ($itemsByOrder[$o['id']] ?? [] as $it): ?>
                    <div>
                        <?= e($it['product_name']) ?><?= $it['variant_label'] ? ' <span style="color:var(--color-muted);">(' . e($it['variant_label']) . ')</span>' : '' ?>
                        × <?= toPersianDigits((string)$it['quantity']) ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($itemsByOrder[$o['id']])): ?>—<?php endif; ?>
            </td>
            <td><?= formatPrice($o['total']) ?></td>
            <td><span class="status-pill <?= $payClass[$o['payment_status']] ?? '' ?>"><?= e($payLabels[$o['payment_status']] ?? $o['payment_status']) ?></span></td>
            <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e($statusLabels[$o['status']] ?? $o['status']) ?></span></td>
            <td><?= toPersianDigits(date('Y/m/d H:i', strtotime($o['created_at']))) ?></td>
            <td>
                <div class="admin-actions">
                    <a href="order_detail.php?id=<?= (int)$o['id'] ?>" class="btn btn-sm btn-outline">مشاهده</a>
                    <?php if (isSuperAdmin()): ?>
                    <form method="post" onsubmit="return confirm('حذف کامل این سفارش؟ این عملیات قابل بازگشت نیست.');" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                    </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?><tr><td colspan="9" style="text-align:center; color:var(--color-muted);">سفارشی یافت نشد.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
