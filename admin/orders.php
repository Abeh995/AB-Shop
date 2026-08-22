<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$pageTitle = 'سفارش‌ها';

$statusFilter = $_GET['status'] ?? '';
$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

$where = '1=1';
$params = [];
if ($statusFilter && isset($statusLabels[$statusFilter])) {
    $where = 'status = ?';
    $params[] = $statusFilter;
}

$stmt = db()->prepare("SELECT * FROM orders WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

require __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-card" style="display:flex; gap:8px; flex-wrap:wrap;">
    <a href="orders.php" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-outline' ?>">همه</a>
    <?php foreach ($statusLabels as $key => $label): ?>
        <a href="?status=<?= e($key) ?>" class="btn btn-sm <?= $statusFilter === $key ? 'btn-primary' : 'btn-outline' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th>کد سفارش</th><th>مشتری</th><th>موبایل</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
            <td dir="ltr"><?= e($o['order_code']) ?></td>
            <td><?= e($o['customer_name']) ?></td>
            <td dir="ltr"><?= e($o['phone']) ?></td>
            <td><?= formatPrice($o['total']) ?></td>
            <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e($statusLabels[$o['status']] ?? $o['status']) ?></span></td>
            <td><?= toPersianDigits(date('Y/m/d H:i', strtotime($o['created_at']))) ?></td>
            <td><a href="order_detail.php?id=<?= (int)$o['id'] ?>" class="btn btn-sm btn-outline">مشاهده</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?><tr><td colspan="7" style="text-align:center; color:var(--color-muted);">سفارشی یافت نشد.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
