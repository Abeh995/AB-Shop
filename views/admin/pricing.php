<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<?php if ($preview): ?>
<div class="admin-card">
    <h3 style="margin-bottom:6px;">پیش‌نمایش تغییر قیمت</h3>
    <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:18px;">
        این تغییر هنوز اعمال نشده است. جدول زیر را بررسی کنید و در صورت تأیید روی «اعمال تغییر» بزنید.
    </p>
    <table class="admin-table">
        <thead><tr><th>محصول</th><th>SKU</th><th>مقدار فعلی</th><th>مقدار جدید</th><th>تغییر</th></tr></thead>
        <tbody>
        <?php foreach ($preview['rows'] as $row): ?>
        <tr>
            <td><?= e($row['name']) ?></td>
            <td dir="ltr"><?= e($row['sku']) ?></td>
            <td><?= $row['current_value'] !== null ? formatPrice($row['current_value']) : '—' ?></td>
            <td><?= formatPrice($row['new_value']) ?></td>
            <td style="color: <?= $row['change_amount'] >= 0 ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
                <?= $row['change_amount'] >= 0 ? '+' : '' ?><?= toPersianDigits(number_format($row['change_amount'])) ?>
                <?php if ($row['change_percentage'] !== null): ?>
                    (<?= toPersianDigits(number_format($row['change_percentage'], 1)) ?>٪)
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <form method="post" style="margin-top:18px; display:flex; gap:10px;">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="apply">
        <input type="hidden" name="field" value="<?= e($preview['field']) ?>">
        <input type="hidden" name="method" value="<?= e($preview['method']) ?>">
        <input type="hidden" name="value" value="<?= e((string)$preview['value']) ?>">
        <input type="hidden" name="reason" value="<?= e((string)$preview['reason']) ?>">
        <?php foreach ($preview['product_ids'] as $pid): ?>
            <input type="hidden" name="product_ids[]" value="<?= (int)$pid ?>">
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">اعمال تغییر روی <?= toPersianDigits((string)count($preview['rows'])) ?> محصول</button>
        <a href="pricing.php" class="btn btn-outline">انصراف</a>
    </form>
</div>

<?php else: ?>

<div class="admin-card">
    <h3 style="margin-bottom:14px;">انتخاب محصولات و نوع تغییر</h3>
    <form method="get" style="margin-bottom:16px; display:flex; gap:10px;">
        <input class="form-control" type="text" name="q" value="<?= e($search) ?>" placeholder="جستجوی نام یا SKU محصول...">
        <button type="submit" class="btn btn-outline">جستجو</button>
    </form>

    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="preview">

        <div class="form-row">
            <div class="form-group">
                <label>نوع قیمت</label>
                <select class="form-control" name="field">
                    <option value="sale_price">قیمت فروش</option>
                    <option value="cost_price">قیمت تمام‌شده</option>
                </select>
            </div>
            <div class="form-group">
                <label>روش تغییر</label>
                <select class="form-control" name="method" id="methodSelect">
                    <option value="fixed_amount">افزودن مبلغ ثابت (تومان)</option>
                    <option value="percentage">درصدی</option>
                    <option value="direct_value">تعیین مستقیم قیمت نهایی</option>
                </select>
            </div>
            <div class="form-group">
                <label id="valueLabel">مقدار (تومان)</label>
                <input class="form-control" type="text" inputmode="decimal" name="value" required placeholder="مثلا 30000 یا 22.5">
            </div>
        </div>
        <div class="form-group">
            <label>دلیل تغییر (اختیاری، برای تاریخچه)</label>
            <input class="form-control" type="text" name="reason" placeholder="مثلا: افزایش قیمت خرید از تأمین‌کننده">
        </div>

        <label style="display:flex; align-items:center; gap:8px; margin: 14px 0;">
            <input type="checkbox" id="selectAll"> انتخاب همه محصولات نمایش‌داده‌شده در جدول زیر
        </label>

        <div style="max-height:420px; overflow-y:auto; border:1px solid var(--color-border); border-radius:var(--radius-md);">
        <table class="admin-table" style="margin:0;">
            <thead><tr><th></th><th>نام</th><th>SKU</th><th>دسته‌بندی</th><th>قیمت فروش</th><th>قیمت تمام‌شده</th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><input type="checkbox" class="product-check" name="product_ids[]" value="<?= (int)$p['id'] ?>"></td>
                <td><?= e($p['name']) ?></td>
                <td dir="ltr"><?= e($p['sku']) ?></td>
                <td><?= e($p['category_name']) ?></td>
                <td><?= formatPrice((int)$p['price']) ?></td>
                <td><?= $p['cost_price'] !== null ? formatPrice((int)$p['cost_price']) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$products): ?><tr><td colspan="6" style="text-align:center; color:var(--color-muted);">محصولی یافت نشد.</td></tr><?php endif; ?>
            </tbody>
        </table>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:16px;">پیش‌نمایش تغییر</button>
    </form>
</div>

<div class="admin-card">
    <h3 style="margin-bottom:14px;">عملیات‌های گروهی اخیر</h3>
    <?php if ($recentOps): ?>
    <table class="admin-table">
        <thead><tr><th>تاریخ</th><th>نوع</th><th>روش</th><th>تغییر درخواستی</th><th>تعداد محصول</th><th>ادمین</th></tr></thead>
        <tbody>
        <?php foreach ($recentOps as $op): ?>
        <tr>
            <td><?= toPersianDigits(date('Y/m/d H:i', strtotime($op['created_at']))) ?></td>
            <td><?= $op['field_changed'] === 'cost_price' ? 'قیمت تمام‌شده' : 'قیمت فروش' ?></td>
            <td><?= ['fixed_amount' => 'مبلغ ثابت', 'percentage' => 'درصدی', 'direct_value' => 'مستقیم'][$op['method']] ?? e($op['method']) ?></td>
            <td dir="ltr"><?= e($op['requested_change']) ?></td>
            <td><?= toPersianDigits((string)$op['product_count']) ?></td>
            <td><?= e($op['admin_username'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="color:var(--color-muted); font-size:.9rem;">هنوز عملیات گروهی‌ای ثبت نشده است.</p>
    <?php endif; ?>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.product-check').forEach(function (cb) { cb.checked = document.getElementById('selectAll').checked; });
});
document.getElementById('methodSelect').addEventListener('change', function () {
    var label = document.getElementById('valueLabel');
    if (this.value === 'percentage') label.textContent = 'مقدار (درصد)';
    else if (this.value === 'direct_value') label.textContent = 'مقدار (تومان — قیمت نهایی)';
    else label.textContent = 'مقدار (تومان)';
});
</script>

<?php endif; ?>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
