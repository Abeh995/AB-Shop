<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap;">
    <p style="color:var(--color-muted); font-size:.88rem; margin:0;">
        روش‌ها به‌ترتیب از بالا به پایین بررسی می‌شوند؛ اولین موردی که با استان مشتری مطابقت داشته باشد اعمال می‌شود. روش «سایر موارد» را همیشه آخر نگه دارید.
    </p>
    <a href="shipping_method_edit.php" class="btn btn-primary">+ روش جدید</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th>ترتیب</th><th>نام</th><th>نوع تطبیق</th><th>هزینه</th><th>ارسال رایگان از</th><th>وضعیت</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($methods as $i => $m): ?>
        <tr>
            <td>
                <div class="admin-actions">
                    <form method="post" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="move">
                        <input type="hidden" name="direction" value="up">
                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline" <?= $i === 0 ? 'disabled' : '' ?>>▲</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="move">
                        <input type="hidden" name="direction" value="down">
                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline" <?= $i === count($methods) - 1 ? 'disabled' : '' ?>>▼</button>
                    </form>
                </div>
            </td>
            <td>
                <?= e($m['name']) ?>
                <?php if ($m['description']): ?><div style="font-size:.78rem; color:var(--color-muted);"><?= e($m['description']) ?></div><?php endif; ?>
            </td>
            <td><?= $m['match_type'] === 'province_contains' ? 'استان شامل «' . e($m['match_value']) . '»' : 'سایر موارد (پیش‌فرض)' ?></td>
            <td><?= formatPrice((int)$m['cost']) ?></td>
            <td><?= $m['free_above_amount'] !== null ? formatPrice((int)$m['free_above_amount']) : '—' ?></td>
            <td><?= $m['is_active'] ? '<span class="status-pill status-delivered">فعال</span>' : '<span class="status-pill status-cancelled">غیرفعال</span>' ?></td>
            <td>
                <div class="admin-actions">
                    <a href="shipping_method_edit.php?id=<?= (int)$m['id'] ?>" class="btn btn-sm btn-outline">ویرایش</a>
                    <form method="post" onsubmit="return confirm('حذف این روش ارسال؟');" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$methods): ?><tr><td colspan="7" style="text-align:center; color:var(--color-muted);">هیچ روش ارسالی تعریف نشده — بدون آن، هزینه ارسال همه سفارش‌ها صفر خواهد بود.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
