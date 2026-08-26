<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap;">
    <form method="get" style="display:flex; gap:8px;">
        <?php if ($featuredOnly): ?><input type="hidden" name="featured" value="1"><?php endif; ?>
        <input class="form-control" type="text" name="q" placeholder="جستجوی نام محصول..." value="<?= e($search) ?>">
        <button class="btn btn-outline" type="submit">جستجو</button>
    </form>
    <div style="display:flex; gap:8px;">
        <?php if ($featuredOnly): ?>
            <a href="products.php" class="btn btn-sm btn-outline">✕ حذف فیلتر ویژه</a>
        <?php endif; ?>
        <a href="product_edit.php" class="btn btn-primary">+ محصول جدید</a>
    </div>
</div>

<?php if ($featuredOnly): ?>
    <div class="alert alert-info">فقط محصولاتی که «نمایش در پیشنهاد ویژه صفحه اصلی» برایشان فعال است نمایش داده می‌شوند.</div>
<?php endif; ?>

<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th></th><th>نام</th><th>SKU</th><th>دسته‌بندی</th><th>قیمت</th><th>موجودی</th><th>وضعیت</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p):
            $img = $p['image'] ? UPLOAD_URL . e($p['image']) : '/assets/img/placeholder-sock.svg';
        ?>
        <tr>
            <td><img class="thumb-sm" src="<?= $img ?>" alt=""></td>
            <td>
                <?= e($p['name']) ?>
                <?php if ($p['is_featured']): ?><span class="status-pill status-shipped" style="margin-right:6px;">ویژه</span><?php endif; ?>
            </td>
            <td dir="ltr" style="font-size:.82rem; color:var(--color-muted);"><?= e($p['sku'] ?: '—') ?></td>
            <td><?= e($p['category_name']) ?></td>
            <td><?= formatPrice(effectivePrice($p)) ?></td>
            <td>
                <?php if (!empty($p['variant_stock_summary'])): ?>
                    <div style="font-size:.78rem; line-height:1.9;">
                        <?php foreach (explode(' | ', $p['variant_stock_summary']) as $line): ?>
                            <div><?= e(trim($line)) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?= toPersianDigits((string)$p['stock']) ?>
                <?php endif; ?>
            </td>
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
        <?php if (!$products): ?><tr><td colspan="8" style="text-align:center; color:var(--color-muted);">محصولی یافت نشد.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
