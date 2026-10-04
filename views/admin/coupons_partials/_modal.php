<?php
/**
 * Create / Edit Coupon Promotion Modal.
 */
?>
<div class="cpn-modal-overlay <?= $editCoupon ? 'active' : '' ?>" id="couponModal" data-ab-modal>
    <div class="cpn-modal-card">
        <div class="cpn-modal-header">
            <h3 style="margin:0; font-size:1.05rem; font-weight:800; color:#0f172a;">
                <?= $editCoupon ? 'ویرایش کد تخفیف' : 'تعریف کد تخفیف جدید' ?>
            </h3>
            <button type="button" class="btn-copy-code" data-ab-modal-close onclick="closeCouponModal()" style="font-size:1.2rem; cursor:pointer;">✕</button>
        </div>

        <form method="post" action="/admin/coupons.php" class="cpn-modal-body">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int)($editCoupon['id'] ?? 0) ?>">

            <!-- Row 1: Code and Title -->
            <div class="cpn-form-row">
                <div class="cpn-form-group">
                    <label>کد تخفیف (انگلیسی و بزرگ) *</label>
                    <div class="cpn-code-input-wrap">
                        <input class="cpn-form-control" type="text" name="code" id="couponCodeInput" dir="ltr" required 
                               value="<?= e($editCoupon['code'] ?? '') ?>" placeholder="مثلاً: YALDA1403" 
                               style="text-transform:uppercase; font-family:monospace; font-weight:700; flex:1;">
                        <button type="button" class="btn-magic-generate" onclick="generateRandomCouponCode()" title="تولید کد رندوم">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.64 3.64-1.28-1.28a1.21 1.21 0 0 0-1.72 0L2.36 18.64a1.21 1.21 0 0 0 0 1.72l1.28 1.28a1.2 1.2 0 0 0 1.72 0L21.64 5.36a1.2 1.2 0 0 0 0-1.72Z"/><path d="m14 7 3 3"/></svg>
                            کد رندوم
                        </button>
                    </div>
                </div>

                <div class="cpn-form-group">
                    <label>عنوان یا مناسبت کمپین (یادداشت داخلی)</label>
                    <input class="cpn-form-control" type="text" name="title" 
                           value="<?= e($editCoupon['title'] ?? '') ?>" placeholder="مثلاً: استوری اینستاگرام / تخفیف افتتاحیه">
                </div>
            </div>

            <!-- Row 2: Type, Value, and Max Cap -->
            <div class="cpn-form-row">
                <div class="cpn-form-group">
                    <label>نوع تخفیف *</label>
                    <select class="cpn-form-control" name="type" id="couponTypeSelect" onchange="handleCouponTypeChange(this.value)">
                        <option value="percent" <?= ($editCoupon['type'] ?? '') === 'percent' ? 'selected' : '' ?>>درصدی (٪)</option>
                        <option value="fixed" <?= ($editCoupon['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>مبلغ ثابت (تومان)</option>
                        <option value="free_shipping" <?= ($editCoupon['type'] ?? '') === 'free_shipping' ? 'selected' : '' ?>>ارسال رایگان سبد</option>
                    </select>
                </div>

                <div class="cpn-form-group" id="couponValueGroup">
                    <label id="couponValueLabel">درصد تخفیف (۱ تا ۱۰۰) *</label>
                    <input class="cpn-form-control" type="number" step="any" name="value" id="couponValueInput" 
                           value="<?= (float)($editCoupon['value'] ?? 0) ?>" placeholder="مثلاً: ۲۰">
                </div>
            </div>

            <!-- Row 3: Max Cap and Min Order -->
            <div class="cpn-form-row" id="couponCapMinRow">
                <div class="cpn-form-group" id="couponMaxCapGroup">
                    <label>حداکثر سقف تخفیف (تومان)</label>
                    <input class="cpn-form-control" type="number" name="max_discount_amount" 
                           value="<?= (float)($editCoupon['max_discount_amount'] ?? '') ?>" placeholder="خالی برای بدون سقف">
                </div>

                <div class="cpn-form-group">
                    <label>حداقل مبلغ سبد خرید (تومان)</label>
                    <input class="cpn-form-control" type="number" name="min_order_amount" 
                           value="<?= (float)($editCoupon['min_order_amount'] ?? 0) ?>" placeholder="۰ برای بدون حداقل">
                </div>
            </div>

            <!-- Row 4: Usage Limits & Customer Limit -->
            <div class="cpn-form-row">
                <div class="cpn-form-group">
                    <label>کل دفعات استفاده مجاز</label>
                    <input class="cpn-form-control" type="number" name="max_uses" 
                           value="<?= (int)($editCoupon['max_uses'] ?? '') ?>" placeholder="خالی برای نامحدود">
                </div>

                <div class="cpn-form-group">
                    <label>سقف استفاده به ازای هر مشتری (شماره موبایل)</label>
                    <input class="cpn-form-control" type="number" min="1" name="max_uses_per_customer" 
                           value="<?= (int)($editCoupon['max_uses_per_customer'] ?? 1) ?>" placeholder="پیش‌فرض: ۱ بار">
                </div>
            </div>

            <!-- Row 5: Category Scoping & Expiry -->
            <div class="cpn-form-row">
                <div class="cpn-form-group">
                    <label>محدود به دسته‌بندی خاص</label>
                    <select class="cpn-form-control" name="category_id">
                        <option value="">همه دسته‌بندی‌ها (سراسری)</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ((int)($editCoupon['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cpn-form-group">
                    <label>تاریخ انقضا (YYYY-MM-DD)</label>
                    <input class="cpn-form-control" type="date" name="expires_at" dir="ltr" 
                           value="<?= e($editCoupon['expires_at'] ?? '') ?>">
                </div>
            </div>

            <!-- Row 6: Active Status -->
            <div style="margin-top:6px;">
                <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= ($editCoupon['is_active'] ?? 1) ? 'checked' : '' ?> style="width:17px; height:17px; accent-color:var(--cpn-primary);">
                    <span style="font-weight:700; font-size:.85rem; color:#1e293b;">کد تخفیف فعال و آماده اعمال توسط خریداران باشد</span>
                </label>
            </div>

            <!-- Footer -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:14px; padding-top:16px; border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline" data-ab-modal-close onclick="closeCouponModal()">انصراف</button>
                <button type="submit" class="btn btn-primary" style="padding:9px 24px; font-weight:700;">
                    <?= $editCoupon ? 'بروزرسانی تغییرات کد' : 'ذخیره و انتشار کد تخفیف' ?>
                </button>
            </div>
        </form>
    </div>
</div>
