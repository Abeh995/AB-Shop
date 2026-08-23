<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap;">
    <form method="get" style="display:flex; gap:8px;">
        <input class="form-control" type="text" name="q" placeholder="جستجوی نام محصول..." value="<?= e($search) ?>">
        <button class="btn btn-outline" type="submit">جستجو</button>
    </form>
    <a href="product_edit.php" class="btn btn-primary">+ محصول جدید</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th></th><th>نام</th><th>دسته‌بندی</th><th>قیمت</th><th>موجودی</th><th>وضعیت</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p):
            $img = $p['image'] ? UPLOAD_URL . e($p['image']) : '/assets/img/placeholder-sock.svg';
        ?>
        <tr>
            <td><img class="thumb-sm" src="<?= $img ?>" alt=""></td>
            <td><?= e($p['name']) ?></td>
            <td><?= e($p['category_name']) ?></td>
            <td><?= formatPrice(effectivePrice($p)) ?></td>
            <td><?= toPersianDigits((string)$p['stock']) ?></td>
            <td><?= $p['is_active'] ? '<span class="status-pill status-delivered">فعال</span>' : '<span class="status-pill status-cancelled">غیرفعال</span>' ?></td>
            <td class="admin-actions">
                <a href="product_edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline">ویرایش</a>
                <form method="post" onsubmit="return confirm('حذف این محصول؟');" style="display:inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?><tr><td colspan="7" style="text-align:center; color:var(--color-muted);">محصولی یافت نشد.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
