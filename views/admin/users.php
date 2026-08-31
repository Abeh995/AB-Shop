<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card">
    <h3 style="margin-bottom:14px;">افزودن ادمین جدید</h3>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="create">
        <div class="form-row">
            <div class="form-group">
                <label>نام کاربری</label>
                <input class="form-control" type="text" name="username" dir="ltr" required minlength="3">
            </div>
            <div class="form-group">
                <label>نام کامل (اختیاری)</label>
                <input class="form-control" type="text" name="full_name">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>رمز عبور (حداقل ۸ کاراکتر)</label>
                <input class="form-control" type="password" name="password" required minlength="8">
            </div>
            <div class="form-group">
                <label>سطح دسترسی</label>
                <select class="form-control" name="role">
                    <option value="admin">ادمین عادی (بدون دسترسی به مدیریت ادمین‌ها)</option>
                    <option value="super_admin">مدیر کل (دسترسی کامل)</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">افزودن ادمین</button>
    </form>
</div>

<div class="admin-card">
    <h3 style="margin-bottom:14px;">لیست ادمین‌ها</h3>
    <table class="admin-table">
        <thead><tr><th>نام کاربری</th><th>نام کامل</th><th>سطح دسترسی</th><th>وضعیت</th><th>تاریخ عضویت</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($admins as $a): $isMe = (int)$a['id'] === (int)$_SESSION['admin_id']; ?>
        <tr>
            <td dir="ltr"><?= e($a['username']) ?> <?= $isMe ? '<span style="color:var(--color-muted); font-size:.8rem;">(شما)</span>' : '' ?></td>
            <td><?= e($a['full_name'] ?: '—') ?></td>
            <td><?= $a['role'] === 'super_admin' ? '<span class="status-pill status-shipped">مدیر کل</span>' : '<span class="status-pill status-processing">ادمین</span>' ?></td>
            <td><?= $a['is_active'] ? '<span class="status-pill status-delivered">فعال</span>' : '<span class="status-pill status-cancelled">غیرفعال</span>' ?></td>
            <td><?= toPersianDigits(date('Y/m/d', strtotime($a['created_at']))) ?></td>
            <td>
                <div class="admin-actions">
                    <button type="button" class="btn btn-sm btn-outline" onclick="document.getElementById('pw-<?= (int)$a['id'] ?>').style.display='flex'">تغییر رمز</button>
                    <?php if (!$isMe): ?>
                    <form method="post" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="toggle_active">
                        <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline"><?= $a['is_active'] ? 'غیرفعال کردن' : 'فعال کردن' ?></button>
                    </form>
                    <form method="post" onsubmit="return confirm('حذف این حساب ادمین؟');" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                    </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <tr id="pw-<?= (int)$a['id'] ?>" style="display:none;">
            <td colspan="6">
                <form method="post" style="display:flex; gap:8px; align-items:center; max-width:420px;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                    <input class="form-control" type="password" name="password" placeholder="رمز عبور جدید (حداقل ۸ کاراکتر)" minlength="8" required>
                    <button type="submit" class="btn btn-sm btn-primary">ذخیره رمز</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
