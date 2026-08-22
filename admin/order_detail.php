<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) redirect('orders.php');

$statusLabels = [
    'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $newStatus = $_POST['status'] ?? '';
    if (isset($statusLabels[$newStatus])) {
        db()->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
        setFlash('success', 'وضعیت سفارش به‌روزرسانی شد.');
        redirect('order_detail.php?id=' . $id);
    }
}

$itemsStmt = db()->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

$pageTitle = 'سفارش ' . $order['order_code'];
require __DIR__ . '/../includes/admin_header.php';
?>

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
            <div style="margin-top:16px; text-align:left; font-weight:700;">
                جمع کل: <?= formatPrice($order['total']) ?>
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
    </div>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
