<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$pageTitle = 'داشبورد';

$productCount = db()->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$orderCount = db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingCount = db()->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$revenue = db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$lowStock = db()->query("SELECT COUNT(*) FROM products WHERE is_active = 1 AND stock <= 5")->fetchColumn();

$recentOrders = db()->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 8")->fetchAll();

$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

require __DIR__ . '/../includes/admin_header.php';
?>

<div class="stats-grid">
    <div class="stat-box"><div class="num"><?= toPersianDigits((string)$productCount) ?></div><div class="lbl">محصول فعال</div></div>
    <div class="stat-box"><div class="num"><?= toPersianDigits((string)$orderCount) ?></div><div class="lbl">کل سفارش‌ها</div></div>
    <div class="stat-box"><div class="num"><?= toPersianDigits((string)$pendingCount) ?></div><div class="lbl">در انتظار بررسی</div></div>
    <div class="stat-box"><div class="num"><?= formatPrice($revenue) ?></div><div class="lbl">مجموع فروش</div></div>
    <div class="stat-box"><div class="num"><?= toPersianDigits((string)$lowStock) ?></div><div class="lbl">موجودی کم (≤۵)</div></div>
</div>

<div class="admin-card">
    <h3 style="margin-bottom:14px;">آخرین سفارش‌ها</h3>
    <table class="admin-table">
        <thead><tr><th>کد سفارش</th><th>مشتری</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recentOrders as $o): ?>
            <tr>
                <td dir="ltr"><?= e($o['order_code']) ?></td>
                <td><?= e($o['customer_name']) ?></td>
                <td><?= formatPrice($o['total']) ?></td>
                <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e($statusLabels[$o['status']] ?? $o['status']) ?></span></td>
                <td><?= toPersianDigits(date('Y/m/d', strtotime($o['created_at']))) ?></td>
                <td><a href="order_detail.php?id=<?= (int)$o['id'] ?>" class="btn btn-sm btn-outline">مشاهده</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$recentOrders): ?><tr><td colspan="6" style="text-align:center; color:var(--color-muted);">هنوز سفارشی ثبت نشده است.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
