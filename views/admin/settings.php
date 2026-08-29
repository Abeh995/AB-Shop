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

<div class="admin-card" style="max-width:560px;">
    <h3 style="margin-bottom:14px;">تگ‌های محصول</h3>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="price_guarantee_days" value="<?= (int) $priceGuaranteeDays ?>">
        <?php if ($priceGuaranteeEnabled): ?><input type="hidden" name="price_guarantee_enabled" value="1"><?php endif; ?>
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
            <input type="checkbox" name="show_product_tags" <?= $showProductTags ? 'checked' : '' ?>>
            نمایش تگ‌های محصول در صفحه هر محصول
        </label>
        <button type="submit" class="btn btn-primary">ذخیره تنظیمات</button>
    </form>
</div>

<div class="admin-card" style="max-width:560px;">
    <h3 style="margin-bottom:14px;">سئو و ایندکس گوگل</h3>
    <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:18px;">
        تا زمانی که محصولات واقعی و محتوای نهایی روی سایت بارگذاری نشده، بهتر است اجازه ایندکس شدن توسط گوگل داده نشود؛
        در غیر این‌صورت صفحات ناقص/تستی ممکن است در نتایج جستجو ظاهر شوند. وقتی سایت آماده شد، این گزینه را فعال کنید.
    </p>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="price_guarantee_days" value="<?= (int) $priceGuaranteeDays ?>">
        <?php if ($priceGuaranteeEnabled): ?><input type="hidden" name="price_guarantee_enabled" value="1"><?php endif; ?>
        <?php if ($showProductTags): ?><input type="hidden" name="show_product_tags" value="1"><?php endif; ?>
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
            <input type="checkbox" name="seo_indexing_enabled" <?= $seoIndexingEnabled ? 'checked' : '' ?>>
            اجازه ایندکس شدن سایت توسط گوگل (Google/Bing و...)
        </label>
        <button type="submit" class="btn btn-primary">ذخیره تنظیمات</button>
    </form>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
