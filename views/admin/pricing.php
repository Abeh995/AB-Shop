<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<?php if ($preview): ?>
<!-- 1. Preview Mode: Diff Review Table -->
<div class="admin-card" style="border-right: 4px solid var(--color-primary);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="margin: 0; font-size: 1.15rem; color: var(--color-primary);">پیش‌نمایش تغییر قیمت (تایید نهایی)</h3>
            <p style="color: var(--color-muted); font-size: 0.88rem; margin: 4px 0 0 0;">
                این تغییرات هنوز در پایگاه‌داده ذخیره نشده‌اند. جدول زیر را بررسی کنید و پس از اطمینان روی «اعمال تغییرات» کلیک نمایید.
            </p>
        </div>
        <div class="status-pill status-shipped" style="font-size: 0.85rem; padding: 4px 12px;">
            تعداد اقلام انتخابی: <?= toPersianDigits((string)count($preview['rows'])) ?> کالا
        </div>
    </div>

    <div style="overflow-x: auto; margin: 16px 0; border: 1px solid var(--color-border); border-radius: var(--radius-md);">
        <table class="admin-table" style="margin: 0; width: 100%;">
            <thead>
                <tr style="background: var(--color-bg-subtle, rgba(0,0,0,0.02)); border-bottom: 1px solid var(--color-border);">
                    <th>نام کالا</th>
                    <th style="width: 120px;">کد SKU</th>
                    <th style="width: 140px;">مقدار فعلی</th>
                    <th style="width: 140px;">مقدار جدید پیشنهادی</th>
                    <th style="width: 150px;">میزان و درصد تغییر</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($preview['rows'] as $row): ?>
            <tr style="border-bottom: 1px solid var(--color-border);">
                <td style="font-weight: 600;"><?= e($row['name']) ?></td>
                <td dir="ltr" style="font-family: monospace; font-size: 0.85rem; color: var(--color-muted);"><?= e($row['sku'] ?: '—') ?></td>
                <td><?= $row['current_value'] !== null ? formatPrice($row['current_value']) : '—' ?></td>
                <td style="font-weight: 700; color: var(--color-text);"><?= formatPrice($row['new_value']) ?></td>
                <td>
                    <span style="font-weight: 700; color: <?= $row['change_amount'] >= 0 ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
                        <?= $row['change_amount'] >= 0 ? '+' : '' ?><?= toPersianDigits(number_format($row['change_amount'])) ?> تومان
                    </span>
                    <?php if ($row['change_percentage'] !== null): ?>
                        <span style="font-size: 0.8rem; color: var(--color-muted); margin-right: 4px;">
                            (<?= toPersianDigits(number_format($row['change_percentage'], 1)) ?>٪)
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <form method="post" action="pricing.php" style="display: flex; gap: 10px; align-items: center; margin-top: 18px;">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="apply">
        <input type="hidden" name="field" value="<?= e($preview['field']) ?>">
        <input type="hidden" name="method" value="<?= e($preview['method']) ?>">
        <input type="hidden" name="value" value="<?= e((string)$preview['value']) ?>">
        <input type="hidden" name="reason" value="<?= e((string)$preview['reason']) ?>">
        <?php foreach ($preview['product_ids'] as $pid): ?>
            <input type="hidden" name="product_ids[]" value="<?= (int)$pid ?>">
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary" style="padding: 9px 20px; font-weight: 700;">
            تأیید و اعمال روی <?= toPersianDigits((string)count($preview['rows'])) ?> محصول
        </button>
        <a href="pricing.php" class="btn btn-outline">انصراف و تغییر مقادیر</a>
    </form>
</div>

<?php else: ?>

<!-- 2. Configuration & Product Selection Mode -->
<div class="admin-card">
    <h3 style="margin-top: 0; margin-bottom: 14px; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        موتور تغییر گروهی قیمت کالاها
    </h3>

    <form method="get" action="pricing.php" style="display: flex; gap: 10px; margin-bottom: 18px;">
        <input class="form-control" type="text" name="q" value="<?= e($search) ?>" placeholder="فیلتر و جستجوی نام یا SKU کالاها در لیست..." style="max-width: 400px;">
        <button type="submit" class="btn btn-outline">جستجو</button>
        <?php if ($search !== ''): ?>
            <a href="pricing.php" class="btn btn-sm btn-outline" style="display: flex; align-items: center;">حذف جستجو</a>
        <?php endif; ?>
    </form>

    <form method="post" action="pricing.php">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="preview">

        <div class="form-row" style="background: var(--color-bg-subtle, rgba(0,0,0,0.02)); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 18px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-weight: 600;">کدام قیمت تغییر کند؟</label>
                <select class="form-control" name="field">
                    <option value="sale_price">قیمت فروش به مشتری</option>
                    <option value="cost_price">قیمت خرید / تمام‌شده کالا</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-weight: 600;">فرمول تغییر</label>
                <select class="form-control" name="method" id="methodSelect">
                    <option value="fixed_amount">مبلغ ثابت (تومان) — مثلاً 15000+ یا 10000-</option>
                    <option value="percentage">درصدی (٪) — مثلاً 10+ یا 5-</option>
                    <option value="direct_value">تعیین مبلغ ثابت جدید — مثلاً 95000</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-weight: 600;" id="valueLabel">مقدار عددی</label>
                <input class="form-control" type="text" inputmode="decimal" name="value" required placeholder="مثلاً: 25000 یا 15">
            </div>
        </div>

        <div class="form-group">
            <label style="font-weight: 600;">دلیل تغییر قیمت (جهت ثبت در تاریخچه حسابداری)</label>
            <input class="form-control" type="text" name="reason" placeholder="مثلاً: نوسان قیمت مواد اولیه توسط کارخانه تولیدکننده">
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin: 16px 0 10px 0;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; font-weight: 600;">
                <input type="checkbox" id="selectAllProducts" style="width: 16px; height: 16px;">
                انتخاب همه محصولات نمایش‌داده‌شده در جدول (<?= toPersianDigits((string)count($products)) ?> کالا)
            </label>
            <span id="selectedCountDisplay" style="font-size: 0.85rem; color: var(--color-muted);">۰ محصول انتخاب شده است</span>
        </div>

        <!-- Scrollable Products Selection Table -->
        <div style="max-height: 420px; overflow-y: auto; border: 1px solid var(--color-border); border-radius: var(--radius-md); margin-bottom: 16px;">
            <table class="admin-table" style="margin: 0; width: 100%;">
                <thead>
                    <tr style="background: var(--color-bg-subtle, rgba(0,0,0,0.03)); position: sticky; top: 0; z-index: 2; border-bottom: 1px solid var(--color-border);">
                        <th style="width: 45px; text-align: center;"></th>
                        <th>نام محصول</th>
                        <th style="width: 120px;">کد SKU</th>
                        <th style="width: 130px;">دسته‌بندی</th>
                        <th style="width: 130px;">قیمت فروش</th>
                        <th style="width: 130px;">قیمت خرید (تمام‌شده)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                <tr style="border-bottom: 1px solid var(--color-border);">
                    <td style="text-align: center;">
                        <input type="checkbox" class="product-item-check" name="product_ids[]" value="<?= (int)$p['id'] ?>" style="width: 16px; height: 16px;">
                    </td>
                    <td style="font-weight: 600;"><?= e($p['name']) ?></td>
                    <td dir="ltr" style="font-family: monospace; font-size: 0.85rem; color: var(--color-muted);"><?= e($p['sku'] ?: '—') ?></td>
                    <td><?= e($p['category_name']) ?></td>
                    <td style="font-weight: 700;"><?= formatPrice((int)$p['price']) ?></td>
                    <td><?= $p['cost_price'] !== null ? formatPrice((int)$p['cost_price']) : '—' ?></td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($products)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 20px; color: var(--color-muted);">محصولی یافت نشد.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 700;">
            محاسبه و مشاهده پیش‌نمایش تغییرات
        </button>
    </form>
</div>

<!-- 3. Recent Bulk Operations Audit Log -->
<?php if (!empty($recentOps)): ?>
<div class="admin-card" style="margin-top: 24px;">
    <h3 style="margin-top: 0; margin-bottom: 14px; font-size: 1.05rem;">تاریخچه آخرین عملیات‌های گروهی قیمت</h3>
    <div style="overflow-x: auto;">
        <table class="admin-table" style="margin: 0; width: 100%;">
            <thead>
                <tr style="background: var(--color-bg-subtle, rgba(0,0,0,0.02)); border-bottom: 1px solid var(--color-border);">
                    <th style="width: 60px;">شناسه</th>
                    <th>تاریخ و ساعت</th>
                    <th>نوع قیمت</th>
                    <th>فرمول درخواست‌شده</th>
                    <th>دلیل ثبت‌شده</th>
                    <th style="width: 90px; text-align: center;">تعداد اقلام</th>
                    <th>ثبت‌کننده</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentOps as $op): ?>
            <tr style="border-bottom: 1px solid var(--color-border);">
                <td>#<?= (int)$op['id'] ?></td>
                <td style="font-size: 0.82rem; color: var(--color-muted);"><?= toPersianDigits(date('Y/m/d H:i', strtotime($op['created_at']))) ?></td>
                <td><?= $op['field_changed'] === 'cost_price' ? 'قیمت تمام‌شده' : 'قیمت فروش' ?></td>
                <td dir="ltr" style="font-family: monospace; font-size: 0.85rem; font-weight: 600;"><?= e($op['requested_change']) ?></td>
                <td style="font-size: 0.85rem; color: var(--color-text-secondary, #4b5563);"><?= e($op['reason'] ?: '—') ?></td>
                <td style="text-align: center;">
                    <span class="status-pill status-delivered" style="font-size: 0.78rem;">
                        <?= toPersianDigits((string)$op['product_count']) ?> کالا
                    </span>
                </td>
                <td style="font-size: 0.85rem;"><?= e($op['admin_username'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
var selectAll = document.getElementById('selectAllProducts');
var itemChecks = document.querySelectorAll('.product-item-check');
var countDisplay = document.getElementById('selectedCountDisplay');

function updateCount() {
    var count = 0;
    itemChecks.forEach(function(c) { if (c.checked) count++; });
    countDisplay.innerText = count.toLocaleString('fa-IR') + ' محصول انتخاب شده است';
}

if (selectAll) {
    selectAll.addEventListener('change', function() {
        itemChecks.forEach(function(c) { c.checked = selectAll.checked; });
        updateCount();
    });
}

itemChecks.forEach(function(c) {
    c.addEventListener('change', updateCount);
});
</script>

<?php endif; ?>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
