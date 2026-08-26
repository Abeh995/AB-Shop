<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card">
    <h3 style="margin-bottom:14px;">افزودن دسته‌بندی جدید</h3>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="create">
        <div class="form-row">
            <div class="form-group">
                <label>نام دسته‌بندی</label>
                <input class="form-control" type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>دسته والد (اختیاری — برای ساخت زیردسته)</label>
                <select class="form-control" name="parent_id">
                    <option value="">بدون والد (دسته اصلی)</option>
                    <?php foreach ($topLevelCategories as $tc): ?>
                        <option value="<?= (int)$tc['id'] ?>"><?= e($tc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>ترتیب نمایش</label>
                <input class="form-control" type="number" name="sort_order" value="0">
            </div>
        </div>
        <div class="form-group">
            <label>توضیحات (اختیاری)</label>
            <textarea class="form-control" name="description"></textarea>
        </div>
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
            <input type="checkbox" name="is_active" checked> فعال باشد
        </label>
        <button type="submit" class="btn btn-primary">افزودن</button>
    </form>
</div>

<div class="admin-card">
    <h3 style="margin-bottom:14px;">لیست دسته‌بندی‌ها</h3>
    <table class="admin-table">
        <thead><tr><th>نام</th><th>دسته والد</th><th>تعداد محصول</th><th>وضعیت</th><th>ترتیب</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($categories as $c): ?>
        <tr>
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                <td>
                    <?= $c['depth'] > 0 ? '<span style="color:var(--color-muted);">└ </span>' : '' ?>
                    <input class="form-control" type="text" name="name" value="<?= e($c['name']) ?>" style="min-width:160px; display:inline-block; width:auto;">
                </td>
                <td>
                    <select class="form-control" name="parent_id" style="min-width:150px;">
                        <option value="">بدون والد (دسته اصلی)</option>
                        <?php foreach ($topLevelCategories as $tc): if ($tc['id'] == $c['id']) continue; ?>
                            <option value="<?= (int)$tc['id'] ?>" <?= ($c['parent_id'] == $tc['id']) ? 'selected' : '' ?>><?= e($tc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><?= toPersianDigits((string)$c['product_count']) ?></td>
                <td><label style="display:flex; align-items:center; gap:6px;"><input type="checkbox" name="is_active" <?= $c['is_active'] ? 'checked' : '' ?>> فعال</label></td>
                <td><input class="form-control" type="number" name="sort_order" value="<?= (int)$c['sort_order'] ?>" style="width:70px;"></td>
                <td class="admin-actions">
                    <input type="hidden" name="description" value="<?= e($c['description']) ?>">
                    <button type="submit" class="btn btn-sm btn-outline">ذخیره</button>
            </form>
                    <form method="post" onsubmit="return confirm('حذف این دسته‌بندی؟');" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                    </form>
                </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p style="font-size:.8rem; color:var(--color-muted); margin-top:12px;">
        نکته: فقط دسته‌های اصلی (بدون والد) می‌توانند به‌عنوان «دسته والد» برای زیردسته انتخاب شوند تا تودرتویی بیش از یک سطح ایجاد نشود.
    </p>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
