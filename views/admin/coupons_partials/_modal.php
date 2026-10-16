<?php
/**
 * Create / Edit Coupon Promotion Modal (views/admin/coupons_partials/_modal.php)
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 */
?>
<dialog class="ab-modal ab-modal--lg" id="couponModal" data-ab-modal <?= $editCoupon ? 'open' : '' ?>>
    <header class="ab-modal__header">
        <h3 class="ab-modal__title">
            <?= $editCoupon ? 'ویرایش کد تخفیف' : 'تعریف کد تخفیف جدید' ?>
        </h3>
        <button type="button" class="ab-icon-btn ab-icon-btn--sm ab-modal__close" data-ab-modal-close aria-label="بستن"></button>
    </header>

    <form method="post" action="/admin/coupons.php" id="couponForm">
        <div class="ab-modal__body">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int)($editCoupon['id'] ?? 0) ?>">

            <div class="ab-stack ab-stack--md">
                <!-- Row 1: Code and Title -->
                <div class="ab-form-grid">
                    <div>
                        <label class="ab-field__label" for="couponCodeInput">کد تخفیف (انگلیسی و بزرگ) *</label>
                        <div class="ab-cluster ab-cluster--xs ab-cluster--nowrap">
                            <input class="ab-input ab-font-bold ab-num" type="text" name="code" id="couponCodeInput" 
                                   dir="ltr" required value="<?= e($editCoupon['code'] ?? '') ?>" 
                                   placeholder="YALDA1403">
                            <button type="button" class="ab-btn ab-btn--outline ab-btn--sm ab-nowrap" data-action="generate-random-code" title="تولید کد تصادفی">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.64 3.64-1.28-1.28a1.21 1.21 0 0 0-1.72 0L2.36 18.64a1.21 1.21 0 0 0 0 1.72l1.28 1.28a1.2 1.2 0 0 0 1.72 0L21.64 5.36a1.2 1.2 0 0 0 0-1.72Z"/><path d="m14 7 3 3"/></svg>
                                کد رندوم
                            </button>
                        </div>
                    </div>

                    <?php
                    component('form_field', [
                        'name'     => 'title',
                        'label'    => 'عنوان یا مناسبت کمپین (یادداشت داخلی)',
                        'value'    => $editCoupon['title'] ?? '',
                        'attrs'    => ['placeholder' => 'مثلاً: استوری اینستاگرام / تخفیف افتتاحیه'],
                    ]);
                    ?>
                </div>

                <!-- Row 2: Type, Value, and Max Cap -->
                <div class="ab-form-grid">
                    <?php
                    component('form_field', [
                        'name'     => 'type',
                        'id'       => 'couponTypeSelect',
                        'label'    => 'نوع تخفیف *',
                        'type'     => 'select',
                        'value'    => $editCoupon['type'] ?? 'percent',
                        'options'  => [
                            'percent'       => 'درصدی (٪)',
                            'fixed'         => 'مبلغ ثابت (تومان)',
                            'free_shipping' => 'ارسال رایگان سبد',
                        ],
                    ]);
                    ?>

                    <div id="couponValueGroup" class="ab-field">
                        <label class="ab-field__label" id="couponValueLabel" for="couponValueInput">درصد تخفیف (۱ تا ۱۰۰) *</label>
                        <input class="ab-input ab-num" type="number" step="any" name="value" id="couponValueInput" 
                               value="<?= (float)($editCoupon['value'] ?? 0) ?>" placeholder="۲۰">
                    </div>
                </div>

                <!-- Row 3: Max Cap and Min Order -->
                <div class="ab-form-grid" id="couponCapMinRow">
                    <div id="couponMaxCapGroup" class="ab-field">
                        <label class="ab-field__label" for="couponMaxDiscountInput">حداکثر سقف تخفیف (تومان)</label>
                        <input class="ab-input ab-num" type="number" name="max_discount_amount" id="couponMaxDiscountInput" 
                               value="<?= (float)($editCoupon['max_discount_amount'] ?? '') ?>" placeholder="خالی برای بدون سقف">
                    </div>

                    <?php
                    component('form_field', [
                        'name'     => 'min_order_amount',
                        'label'    => 'حداقل مبلغ سبد خرید (تومان)',
                        'type'     => 'number',
                        'value'    => (float)($editCoupon['min_order_amount'] ?? 0),
                        'attrs'    => ['placeholder' => '۰ برای بدون حداقل'],
                    ]);
                    ?>
                </div>

                <!-- Row 4: Usage Limits & Customer Limit -->
                <div class="ab-form-grid">
                    <?php
                    component('form_field', [
                        'name'     => 'max_uses',
                        'label'    => 'کل دفعات استفاده مجاز',
                        'type'     => 'number',
                        'value'    => (int)($editCoupon['max_uses'] ?? ''),
                        'attrs'    => ['placeholder' => 'خالی برای نامحدود'],
                    ]);

                    component('form_field', [
                        'name'     => 'max_uses_per_customer',
                        'label'    => 'سقف استفاده به ازای هر مشتری (موبایل)',
                        'type'     => 'number',
                        'value'    => (int)($editCoupon['max_uses_per_customer'] ?? 1),
                        'attrs'    => ['min' => 1, 'placeholder' => 'پیش‌فرض: ۱ بار'],
                    ]);
                    ?>
                </div>

                <!-- Row 5: Category Scoping & Expiry -->
                <div class="ab-form-grid">
                    <?php
                    $catOptions = ['' => 'همه دسته‌بندی‌ها (سراسری)'];
                    foreach ($categories as $cat) {
                        $catOptions[(string)$cat['id']] = $cat['name'];
                    }
                    component('form_field', [
                        'name'     => 'category_id',
                        'label'    => 'محدود به دسته‌بندی خاص',
                        'type'     => 'select',
                        'value'    => (string)($editCoupon['category_id'] ?? ''),
                        'options'  => $catOptions,
                    ]);

                    component('form_field', [
                        'name'     => 'expires_at',
                        'label'    => 'تاریخ انقضا (YYYY-MM-DD)',
                        'type'     => 'text',
                        'dir'      => 'ltr',
                        'value'    => $editCoupon['expires_at'] ?? '',
                        'attrs'    => ['placeholder' => '1405-12-29'],
                    ]);
                    ?>
                </div>

                <!-- Row 6: Active Status -->
                <div>
                    <label class="ab-check">
                        <input type="checkbox" name="is_active" value="1" <?= ($editCoupon['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <span class="ab-font-bold ab-text-sm">کد تخفیف فعال و آماده اعمال توسط خریداران در تسویه حساب باشد</span>
                    </label>
                </div>
            </div>
        </div>

        <footer class="ab-modal__footer">
            <button type="button" class="ab-btn ab-btn--outline" data-ab-modal-close>انصراف</button>
            <button type="submit" class="ab-btn ab-btn--primary">
                <?= $editCoupon ? 'بروزرسانی تغییرات کد' : 'ذخیره و انتشار کد تخفیف' ?>
            </button>
        </footer>
    </form>
</dialog>
