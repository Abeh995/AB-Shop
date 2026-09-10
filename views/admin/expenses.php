<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card">
    <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="min-width:160px;">
            <label>جستجو</label>
            <input class="form-control" type="text" name="q" value="<?= e($search) ?>" placeholder="عنوان یا توضیحات...">
        </div>
        <div class="form-group" style="min-width:140px;">
            <label>دسته‌بندی</label>
            <select class="form-control" name="category">
                <option value="">همه</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>از تاریخ</label>
            <input class="form-control" type="date" name="start_date" value="<?= e($startDate) ?>">
        </div>
        <div class="form-group">
            <label>تا تاریخ</label>
            <input class="form-control" type="date" name="end_date" value="<?= e($endDate) ?>">
        </div>
        <button type="submit" class="btn btn-outline">اعمال فیلتر</button>
        <a href="expenses.php" class="btn btn-outline">پاک کردن</a>
        <a href="expense_edit.php" class="btn btn-primary" style="margin-inline-start:auto;">+ ثبت هزینه جدید</a>
    </form>
</div>

<div class="admin-card">
    <p style="font-size:.95rem; margin-bottom:14px;">جمع هزینه‌های فیلترشده: <strong><?= formatPrice($totalAmount) ?></strong></p>
    <table class="admin-table">
        <thead><tr><th>تاریخ</th><th>عنوان</th><th>دسته‌بندی</th><th>مبلغ</th><th>ثبت‌کننده</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($expenses as $ex): ?>
        <tr>
            <td><?= toPersianDigits(date('Y/m/d', strtotime($ex['expense_date']))) ?></td>
            <td>
                <?= e($ex['title']) ?>
                <?php if ($ex['description']): ?><div style="font-size:.78rem; color:var(--color-muted);"><?= e($ex['description']) ?></div><?php endif; ?>
            </td>
            <td><?= e($ex['category']) ?></td>
            <td><?= formatPrice((int)$ex['amount']) ?></td>
            <td><?= e($ex['admin_username'] ?? '—') ?></td>
            <td>
                <div class="admin-actions">
                    <a href="expense_edit.php?id=<?= (int)$ex['id'] ?>" class="btn btn-sm btn-outline">ویرایش</a>
                    <form method="post" onsubmit="return confirm('این هزینه بایگانی شود؟ از گزارش‌های مالی بعدی حذف می‌شود اما رکوردش برای Audit باقی می‌ماند.');" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="archive">
                        <input type="hidden" name="id" value="<?= (int)$ex['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">بایگانی</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$expenses): ?><tr><td colspan="6" style="text-align:center; color:var(--color-muted);">هزینه‌ای یافت نشد.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
