<?php
/**
 * Admin Coupons & Promotions Management View
 * Pure presentation, zero direct SQL queries, zero form mutations (Rule 7).
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="coupons-workspace">

    <!-- 1. Top Bento KPIs -->
    <div class="coupon-kpi-grid">
        <div class="coupon-kpi-card">
            <div class="coupon-kpi-info">
                <h4>کدهای تخفیف فعال</h4>
                <div class="kpi-num" style="color:#059669;"><?= (int)$activeCount ?></div>
                <small style="color:#64748b; font-size:.76rem;">آماده اعمال در سبد خرید</small>
            </div>
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(16, 185, 129, 0.1); color:#059669; display:flex; align-items:center; justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H2v7l6.29 6.29c.94.94 2.48.94 3.42 0l5.58-5.58c.94-.94.94-2.48 0-3.42L11 3H9Z"/><circle cx="6" cy="8" r="1.5" fill="currentColor"/></svg>
            </div>
        </div>

        <div class="coupon-kpi-card">
            <div class="coupon-kpi-info">
                <h4>مجموع استفاده خریداران</h4>
                <div class="kpi-num"><?= (int)$totalUsed ?></div>
                <small style="color:#64748b; font-size:.76rem;">دفعات اعمال موفق در سفارش‌ها</small>
            </div>
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(59, 130, 246, 0.1); color:#2563EB; display:flex; align-items:center; justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
            </div>
        </div>

        <div class="coupon-kpi-card">
            <div class="coupon-kpi-info">
                <h4>اقدام سریع</h4>
                <button type="button" class="btn btn-primary" onclick="openCouponModal()" style="margin-top:6px; font-weight:700; font-size:.85rem; padding:8px 16px;">
                    + افزودن کد تخفیف جدید
                </button>
            </div>
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(234, 88, 12, 0.1); color:#EA580C; display:flex; align-items:center; justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            </div>
        </div>
    </div>

    <!-- Alert Notices -->
    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">کد تخفیف با موفقیت ذخیره گردید.</div>
    <?php endif; ?>
    <?php if (isset($_GET['toggled'])): ?>
        <div class="alert alert-success">وضعیت کد تخفیف تغییر یافت.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">کد تخفیف با موفقیت حذف گردید.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- 2. Coupons Table -->
    <div class="coupon-table-card">
        <div class="coupon-table-header">
            <div>
                <h3 style="margin:0 0 4px; font-size:1.05rem; font-weight:800; color:#0f172a;">فهرست کدهای تخفیف فروشگاه</h3>
                <p style="margin:0; font-size:.84rem; color:#64748b;">کدهای فعال توسط خریداران در صفحه سبد خرید و تسویه حساب قابل اعمال است.</p>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>کد تخفیف</th>
                        <th>نوع و مقدار</th>
                        <th>حداقل سبد خرید</th>
                        <th>سقف تخفیف</th>
                        <th>میزان مصرف</th>
                        <th>مهلت اعتبار</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($coupons)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:36px; color:#64748b;">
                                هنوز هیچ کد تخفیفی در فروشگاه تعریف نشده است.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($coupons as $c): ?>
                            <?php
                            $isExpired = !empty($c['expires_at']) && strtotime($c['expires_at']) < strtotime('today');
                            $isLimitReached = $c['max_uses'] !== null && (int)$c['used_count'] >= (int)$c['max_uses'];
                            $usagePercent = !empty($c['max_uses']) ? min(100, round(((int)$c['used_count'] / (int)$c['max_uses']) * 100)) : null;
                            ?>
                            <tr>
                                <td>
                                    <div class="coupon-code-badge">
                                        <span><?= e($c['code']) ?></span>
                                        <button type="button" class="btn-copy-code" title="کپی کردن کد" onclick="navigator.clipboard.writeText('<?= e($c['code']) ?>'); alert('کد تخفیف کپی شد');">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($c['type'] === 'percent'): ?>
                                        <span style="font-weight:700; color:#2563eb;"><?= (float)$c['value'] ?>٪ تخفیف</span>
                                    <?php else: ?>
                                        <span style="font-weight:700; color:#059669;"><?= formatPrice($c['value']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= !empty($c['min_order_amount']) && (float)$c['min_order_amount'] > 0 ? formatPrice($c['min_order_amount']) : '<span style="color:#94a3b8;">بدون کف</span>' ?>
                                </td>
                                <td>
                                    <?= !empty($c['max_discount_amount']) && (float)$c['max_discount_amount'] > 0 ? formatPrice($c['max_discount_amount']) : '<span style="color:#94a3b8;">نامحدود</span>' ?>
                                </td>
                                <td>
                                    <div style="font-size:.82rem; font-weight:600;">
                                        <?= (int)$c['used_count'] ?> <?= $c['max_uses'] ? 'از ' . (int)$c['max_uses'] : 'بار (نامحدود)' ?>
                                    </div>
                                    <?php if ($usagePercent !== null): ?>
                                        <div class="usage-meter">
                                            <div class="usage-meter-fill" style="width:<?= $usagePercent ?>%;"></div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td dir="ltr" style="font-size:.82rem; font-family:monospace;">
                                    <?php if (!empty($c['expires_at'])): ?>
                                        <span style="color:<?= $isExpired ? '#dc2626' : '#334155' ?>; font-weight:<?= $isExpired ? '700' : '500' ?>;">
                                            <?= e($c['expires_at']) ?>
                                            <?= $isExpired ? ' (منقضی)' : '' ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;">همیشگی</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$c['is_active']): ?>
                                        <span class="badge" style="background:#fee2e2; color:#b91c1c; padding:3px 8px; border-radius:6px; font-size:.76rem; font-weight:700;">غیرفعال</span>
                                    <?php elseif ($isExpired): ?>
                                        <span class="badge" style="background:#fef3c7; color:#b45309; padding:3px 8px; border-radius:6px; font-size:.76rem; font-weight:700;">منقضی</span>
                                    <?php elseif ($isLimitReached): ?>
                                        <span class="badge" style="background:#e2e8f0; color:#475569; padding:3px 8px; border-radius:6px; font-size:.76rem; font-weight:700;">تکمیل ظرفیت</span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#d1fae5; color:#065f46; padding:3px 8px; border-radius:6px; font-size:.76rem; font-weight:700;">فعال</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <a href="/admin/coupons.php?edit=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline">ویرایش</a>
                                        
                                        <form method="post" action="/admin/coupons.php" style="margin:0; display:inline;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline">
                                                <?= $c['is_active'] ? 'تعلیق' : 'فعال' ?>
                                            </button>
                                        </form>

                                        <form method="post" action="/admin/coupons.php" onsubmit="return confirm('آیا از حذف این کد تخفیف اطمینان دارید؟');" style="margin:0; display:inline;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                            <button type="submit" class="btn btn-sm" style="background:#fee2e2; color:#b91c1c; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Add / Edit Coupon Modal -->
    <div class="email-modal-overlay <?= $editCoupon ? 'active' : '' ?>" id="couponModal">
        <div class="email-modal-card" style="max-width:600px;">
            <div class="email-modal-header">
                <h3><?= $editCoupon ? 'ویرایش کد تخفیف' : 'تعریف کد تخفیف جدید' ?></h3>
                <button type="button" class="btn btn-sm btn-outline" onclick="closeCouponModal()" style="border:none; font-size:1.2rem; cursor:pointer;">✕</button>
            </div>

            <form method="post" action="/admin/coupons.php" class="email-modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int)($editCoupon['id'] ?? 0) ?>">

                <div class="mail-form-row">
                    <div class="mail-form-group">
                        <label>کد تخفیف (انگلیسی و بزرگ) *</label>
                        <input class="mail-form-control mono-num" type="text" name="code" dir="ltr" required 
                               value="<?= e($editCoupon['code'] ?? '') ?>" placeholder="مثلاً: YALDA1403" style="text-transform:uppercase;">
                    </div>

                    <div class="mail-form-group">
                        <label>نوع تخفیف *</label>
                        <select class="mail-form-control" name="type" id="couponTypeSelect" onchange="toggleCouponTypeFields(this.value)">
                            <option value="percent" <?= ($editCoupon['type'] ?? '') === 'percent' ? 'selected' : '' ?>>درصدی (٪)</option>
                            <option value="fixed" <?= ($editCoupon['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>مبلغ ثابت (تومان)</option>
                        </select>
                    </div>
                </div>

                <div class="mail-form-row" style="margin-top:10px;">
                    <div class="mail-form-group">
                        <label id="valueFieldLabel">درصد تخفیف (۱ تا ۱۰۰) *</label>
                        <input class="mail-form-control mono-num" type="number" step="any" name="value" required 
                               value="<?= (float)($editCoupon['value'] ?? 0) ?>" placeholder="مثلاً: ۲۰">
                    </div>

                    <div class="mail-form-group" id="maxCapGroup">
                        <label>حداکثر سقف تخفیف (تومان)</label>
                        <input class="mail-form-control mono-num" type="number" name="max_discount_amount" 
                               value="<?= (float)($editCoupon['max_discount_amount'] ?? '') ?>" placeholder="اختیاری">
                    </div>
                </div>

                <div class="mail-form-row" style="margin-top:10px;">
                    <div class="mail-form-group">
                        <label>حداقل مبلغ سفارش (تومان)</label>
                        <input class="mail-form-control mono-num" type="number" name="min_order_amount" 
                               value="<?= (float)($editCoupon['min_order_amount'] ?? 0) ?>" placeholder="۰ برای بدون حداقل">
                    </div>

                    <div class="mail-form-group">
                        <label>سقف دفعات استفاده مجاز</label>
                        <input class="mail-form-control mono-num" type="number" name="max_uses" 
                               value="<?= (int)($editCoupon['max_uses'] ?? '') ?>" placeholder="خالی برای نامحدود">
                    </div>
                </div>

                <div class="mail-form-row" style="margin-top:10px;">
                    <div class="mail-form-group">
                        <label>تاریخ انقضا (YYYY-MM-DD)</label>
                        <input class="mail-form-control mono-num" type="date" name="expires_at" dir="ltr" 
                               value="<?= e($editCoupon['expires_at'] ?? '') ?>">
                    </div>

                    <div class="mail-form-group" style="display:flex; flex-direction:column; justify-content:center;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:20px;">
                            <input type="checkbox" name="is_active" value="1" <?= ($editCoupon['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <span style="font-weight:700;">فعال و قابل استفاده در سایت</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between; margin-top:20px; padding-top:16px; border-top:1px solid #e2e8f0;">
                    <button type="button" class="btn btn-outline" onclick="closeCouponModal()">انصراف</button>
                    <button type="submit" class="btn btn-primary" style="padding:10px 28px; font-weight:700;">
                        <?= $editCoupon ? 'بروزرسانی کد تخفیف' : 'ایجاد و ذخیره کد تخفیف' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function openCouponModal() {
    document.getElementById('couponModal').classList.add('active');
}
function closeCouponModal() {
    document.getElementById('couponModal').classList.remove('active');
    if (window.location.search.includes('edit=')) {
        window.location.href = '/admin/coupons.php';
    }
}
function toggleCouponTypeFields(val) {
    const label = document.getElementById('valueFieldLabel');
    const capGroup = document.getElementById('maxCapGroup');
    if (val === 'fixed') {
        label.textContent = 'مبلغ تخفیف (تومان) *';
        capGroup.style.display = 'none';
    } else {
        label.textContent = 'درصد تخفیف (۱ تا ۱۰۰) *';
        capGroup.style.display = 'block';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('couponTypeSelect');
    if (sel) toggleCouponTypeFields(sel.value);
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
