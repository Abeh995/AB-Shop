<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap;">
    <form method="get" style="display:flex; gap:8px;">
        <input class="form-control" type="text" name="q" placeholder="جستجوی نام آیتم..." value="<?= e($search) ?>">
        <button class="btn btn-outline" type="submit">جستجو</button>
    </form>
    <a href="gift_item_edit.php" class="btn btn-primary">+ آیتم جدید</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th></th><th>نام</th><th>قابل اهدا</th><th>قابل فروش (پیشنهاد بعد از سبد)</th><th>قیمت تمام‌شده</th><th>قیمت پیشنهادی</th><th>موجودی</th><th>وضعیت</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it):
            $img = $it['image'] ? UPLOAD_URL . e($it['image']) : '/assets/img/placeholder-sock.svg';
        ?>
        <tr>
            <td><img class="thumb-sm" src="<?= $img ?>" alt=""></td>
            <td><?= e($it['name']) ?></td>
            <td><?= $it['is_giftable'] ? '✓' : '—' ?></td>
            <td><?= $it['is_post_orderable'] ? '✓' : '—' ?></td>
            <td><?= formatPrice((int)$it['cost_price']) ?></td>
            <td><?= $it['post_order_price'] !== null ? formatPrice((int)$it['post_order_price']) : '—' ?></td>
            <td><?= toPersianDigits((string)$it['stock']) ?></td>
            <td><?= $it['is_active'] ? '<span class="status-pill status-delivered">فعال</span>' : '<span class="status-pill status-cancelled">غیرفعال</span>' ?></td>
            <td>
                <div class="admin-actions">
                    <a href="gift_item_edit.php?id=<?= (int)$it['id'] ?>" class="btn btn-sm btn-outline">ویرایش</a>
                    <form method="post" onsubmit="return confirm('حذف این آیتم؟ سفارش‌های قبلی که این آیتم برایشان ثبت شده، تاریخچه‌شان حفظ می‌ماند.');" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$items): ?><tr><td colspan="9" style="text-align:center; color:var(--color-muted);">آیتمی یافت نشد.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
