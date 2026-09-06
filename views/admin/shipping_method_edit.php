<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="max-width:600px;">
    <?php if ($errors): ?>
        <div class="alert alert-error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <form method="post">
        <?= csrfField() ?>

        <div class="form-group">
            <label>نام روش ارسال</label>
            <input class="form-control" type="text" name="name" value="<?= e($method['name'] ?? '') ?>" required placeholder="مثلا: پیک تهران">
        </div>

        <div class="form-group">
            <label>توضیح کوتاه (اختیاری، فقط برای خودتان)</label>
            <input class="form-control" type="text" name="description" value="<?= e($method['description'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>نوع تطبیق</label>
            <select class="form-control" name="match_type" id="matchType">
                <option value="province_contains" <?= (($method['match_type'] ?? '') === 'province_contains') ? 'selected' : '' ?>>فقط وقتی استان مشتری شامل متن خاصی باشد</option>
                <option value="default" <?= (($method['match_type'] ?? 'default') === 'default') ? 'selected' : '' ?>>سایر موارد (پیش‌فرض، وقتی هیچ روش دیگری تطبیق نداشت)</option>
            </select>
        </div>

        <div class="form-group" id="matchValueGroup" style="<?= (($method['match_type'] ?? '') !== 'province_contains') ? 'display:none;' : '' ?>">
            <label>متنی که باید در نام استان وجود داشته باشد</label>
            <input class="form-control" type="text" name="match_value" value="<?= e($method['match_value'] ?? '') ?>" placeholder="مثلا: تهران">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>هزینه ارسال (تومان)</label>
                <input class="form-control" type="text" inputmode="numeric" name="cost" value="<?= e((string)($method['cost'] ?? '0')) ?>" required>
            </div>
            <div class="form-group">
                <label>ارسال رایگان از این مبلغ به بالا (اختیاری)</label>
                <input class="form-control" type="text" inputmode="numeric" name="free_above_amount" value="<?= e((string)($method['free_above_amount'] ?? '')) ?>" placeholder="مثلا: 1000000">
            </div>
        </div>

        <label style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
            <input type="checkbox" name="is_active" <?= (!$method || $method['is_active']) ? 'checked' : '' ?>> فعال
        </label>

        <button type="submit" class="btn btn-primary"><?= $method && !empty($method['id']) ? 'ذخیره تغییرات' : 'افزودن روش ارسال' ?></button>
        <a href="shipping_methods.php" class="btn btn-outline">انصراف</a>
    </form>
</div>

<script>
document.getElementById('matchType').addEventListener('change', function () {
    document.getElementById('matchValueGroup').style.display = this.value === 'province_contains' ? 'block' : 'none';
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
