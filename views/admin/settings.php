<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="max-width:560px;">
    <h3 style="margin-bottom:14px;">تضمین قیمت سبد خرید</h3>
    <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:18px;">
        وقتی فعال باشد، قیمت کالاهای سبد خرید کاربران لاگین‌شده تا مدت مشخص‌شده از تاریخ افزودن اولین کالا
        به سبد (خالی قبلی) ثابت می‌ماند، حتی اگر قیمت محصول در این فاصله تغییر کند. بعد از پایان این مهلت،
        قیمت لحظه‌ای جایگزین می‌شود.
    </p>
    <form method="post">
        <?= csrfField() ?>
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
            <input type="checkbox" name="price_guarantee_enabled" <?= $priceGuaranteeEnabled ? 'checked' : '' ?>>
            فعال بودن تضمین قیمت
        </label>
        <div class="form-group">
            <label>تعداد روزهای تضمین قیمت</label>
            <input class="form-control" type="number" name="price_guarantee_days" value="<?= (int) $priceGuaranteeDays ?>" min="1" style="max-width:160px;">
        </div>
        <button type="submit" class="btn btn-primary">ذخیره تنظیمات</button>
    </form>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
