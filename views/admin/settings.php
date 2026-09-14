<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="max-width:760px;">
    <h3 style="margin-bottom:14px;">اطلاعات کسب‌وکار و تماس</h3>
    <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:18px;">
        اطلاعات این بخش در صفحات «درباره ما» و «تماس با ما» نمایش داده می‌شوند. این مقادیر در دیتابیس فروشگاه نگهداری می‌شوند و نباید برای اطلاعات حساس مانند رمزها یا کلیدهای API استفاده شوند.
    </p>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="section" value="business">
        <div class="form-group">
            <label>ایمیل پشتیبانی</label>
            <input class="form-control" type="email" name="store_email" dir="ltr" value="<?= e($storeEmail) ?>" placeholder="support@example.com">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>تلفن ثابت</label>
                <input class="form-control" type="text" name="store_phone" dir="ltr" value="<?= e($storePhone) ?>" placeholder="051-12345678">
            </div>
            <div class="form-group">
                <label>شماره همراه</label>
                <input class="form-control" type="text" name="store_mobile" dir="ltr" value="<?= e($storeMobile) ?>" placeholder="09123456789">
            </div>
        </div>
        <div class="form-group">
            <label>نشانی کسب‌وکار</label>
            <input class="form-control" type="text" name="store_address" value="<?= e($storeAddress) ?>" placeholder="نشانی کامل کسب‌وکار">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>کدپستی</label>
                <input class="form-control" type="text" name="store_postal_code" dir="ltr" value="<?= e($storePostalCode) ?>" placeholder="۱۰ رقمی">
            </div>
            <div class="form-group">
                <label>تاریخ شروع فعالیت</label>
                <input class="form-control" type="text" name="store_start_date" value="<?= e($storeStartDate) ?>" placeholder="مرداد ۱۴۰۵">
            </div>
        </div>
        <div class="form-group">
            <label>ساعات پاسخ‌گویی</label>
            <input class="form-control" type="text" name="store_support_hours" value="<?= e($storeSupportHours) ?>" placeholder="شنبه تا پنج‌شنبه، ۹ تا ۱۸">
        </div>
        <div class="form-group">
            <label>متن معرفی صفحه تماس با ما</label>
            <textarea class="form-control" name="contact_intro" rows="3"><?= e($contactIntro) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">ذخیره اطلاعات تماس</button>
    </form>
</div>

<div class="admin-card" style="max-width:760px;">
    <h3 style="margin-bottom:14px;">محتوای صفحات عمومی</h3>
    <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:18px;">
        متن «درباره ما»، «قوانین و مقررات» و «حریم خصوصی و امنیت» از اینجا قابل ویرایش است. HTML خام پشتیبانی نمی‌شود؛ از قالب ساده زیر استفاده کنید: خط خالی = پاراگراف جدید، <code>## عنوان</code> = تیتر بخش، و <code>- مورد</code> = آیتم فهرست.
    </p>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="section" value="legal_content">

        <div class="form-group">
            <label>متن «درباره ما»</label>
            <textarea class="form-control" name="about_content" rows="14"><?= e($aboutContent) ?></textarea>
        </div>

        <div class="form-group">
            <label>متن «قوانین و مقررات»</label>
            <textarea class="form-control" name="terms_content" rows="30"><?= e($termsContent) ?></textarea>
        </div>

        <div class="form-group">
            <label>متن «حریم خصوصی و امنیت»</label>
            <textarea class="form-control" name="privacy_content" rows="30"><?= e($privacyContent) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">ذخیره محتوای صفحات</button>
    </form>
</div>

<div class="admin-card" style="max-width:560px;">
    <h3 style="margin-bottom:14px;">لوگوی فروشگاه</h3>
    <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:18px;">
        لوگو در هدر سایت (به‌جای نام متنی فروشگاه) و در فوتر نمایش داده می‌شود. فرمت‌های مجاز: JPG، PNG، WEBP یا SVG — حداکثر ۲ مگابایت.
        برای بهترین نتیجه از تصویر با پس‌زمینه شفاف (PNG یا SVG) استفاده کنید.
    </p>
    <?php if ($siteLogo && siteLogoUrl()): ?>
        <div style="margin-bottom:16px;">
            <img src="<?= e(siteLogoUrl()) ?>" alt="لوگوی فعلی" style="max-height:64px; max-width:220px; background:var(--color-primary-light); border-radius:8px; padding:8px;">
        </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="section" value="branding">
        <div class="form-group">
            <input class="form-control" type="file" name="site_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml">
        </div>
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary">بارگذاری لوگو</button>
            <?php if ($siteLogo): ?>
                <button type="submit" name="remove_logo" value="1" class="btn btn-outline" onclick="return confirm('لوگو حذف شود و به‌جای آن نام فروشگاه نمایش داده شود؟');">حذف لوگو</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="admin-card" style="max-width:560px;">
    <h3 style="margin-bottom:14px;">نوار اعلان بالای سایت</h3>
    <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:18px;">
        یک نوار باریک زیر هدر، بالای محصولات صفحه اصلی — مناسب اطلاع‌رسانی روزهای ارسال، کد تخفیف عمومی و مانند آن.
    </p>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="section" value="announcement">
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
            <input type="checkbox" name="announcement_bar_enabled" <?= $announcementBarEnabled ? 'checked' : '' ?>>
            نمایش نوار اعلان
        </label>
        <div class="form-group">
            <label>متن اعلان</label>
            <input class="form-control" type="text" name="announcement_bar_text" value="<?= e($announcementBarText) ?>" placeholder="متن اعلان فروشگاه">
        </div>
        <div class="form-group">
            <label>لینک اعلان (اختیاری)</label>
            <input class="form-control" type="text" name="announcement_bar_link" dir="ltr" value="<?= e($announcementBarLink) ?>" placeholder="https://example.com">
        </div>
        <button type="submit" class="btn btn-primary">ذخیره تنظیمات</button>
    </form>
</div>

<div class="admin-card" style="max-width:560px;">
    <h3 style="margin-bottom:14px;">فوتر سایت</h3>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="section" value="footer">
        <div class="form-group">
            <label>متن دعوت به مطالعه «درباره ما»</label>
            <input class="form-control" type="text" name="footer_about_teaser_text" value="<?= e($footerAboutTeaserText) ?>" placeholder="متن کوتاه لینک درباره ما">
        </div>
        <div class="form-group">
            <label>توضیح برند در فوتر</label>
            <textarea class="form-control" name="footer_tagline" rows="3"><?= e($footerTagline) ?></textarea>
        </div>
        <div class="form-group">
            <label>متن نماد ارسال</label>
            <input class="form-control" type="text" name="footer_shipping_badge_text" value="<?= e($footerShippingBadgeText) ?>" placeholder="متن توضیح ارسال">
        </div>
        <button type="submit" class="btn btn-primary">ذخیره تنظیمات</button>
    </form>
</div>

<div class="admin-card" style="max-width:560px;">
    <h3 style="margin-bottom:14px;">شبکه‌های اجتماعی و نماد اعتماد</h3>
    <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:18px;">
        هر شبکه را جدا فعال/غیرفعال کنید و لینک آن را وارد کنید. فقط شبکه‌های فعال و دارای لینک در فوتر نمایش داده می‌شوند.
    </p>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="section" value="social">
        <?php foreach ($socialNetworks as $key => $label): $s = $socialSettings[$key]; ?>
        <div class="form-row" style="align-items:center; margin-bottom:10px;">
            <label style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="social_<?= e($key) ?>_enabled" <?= $s['enabled'] ? 'checked' : '' ?>>
                <?= e($label) ?>
            </label>
            <input class="form-control" type="text" name="social_<?= e($key) ?>_url" dir="ltr" value="<?= e($s['url']) ?>" placeholder="https://...">
        </div>
        <?php endforeach; ?>

        <hr style="border:none; border-top:1px solid var(--color-border); margin:18px 0;">

        <label style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
            <input type="checkbox" name="enamad_enabled" <?= $enamadEnabled ? 'checked' : '' ?>>
            نمایش نماد اعتماد الکترونیکی (اینماد) در فوتر
        </label>
        <div class="form-group">
            <label>کد بج اینماد</label>
            <textarea class="form-control" name="enamad_embed_code" dir="ltr" rows="4" placeholder="کد Badge دریافتی از پنل enamad.ir را اینجا Paste کنید"><?= e($enamadEmbedCode) ?></textarea>
            <p style="font-size:.78rem; color:var(--color-muted); margin-top:4px;">این کد پس از دریافت نماد از enamad.ir در پنل کاربری‌تان قابل کپی است؛ تا قبل از آن این بخش را خالی بگذارید.</p>
        </div>
        <button type="submit" class="btn btn-primary">ذخیره تنظیمات</button>
    </form>
</div>

<div class="admin-card" style="max-width:560px;">
    <h3 style="margin-bottom:14px;">تضمین قیمت سبد خرید</h3>
    <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:18px;">
        وقتی فعال باشد، قیمت کالاهای سبد خرید کاربران لاگین‌شده تا مدت مشخص‌شده از تاریخ افزودن اولین کالا
        به سبد (خالی قبلی) ثابت می‌ماند، حتی اگر قیمت محصول در این فاصله تغییر کند. بعد از پایان این مهلت،
        قیمت لحظه‌ای جایگزین می‌شود.
    </p>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="section" value="price_guarantee">
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
        <input type="hidden" name="section" value="tags">
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
        <input type="hidden" name="section" value="seo">
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
            <input type="checkbox" name="seo_indexing_enabled" <?= $seoIndexingEnabled ? 'checked' : '' ?>>
            اجازه ایندکس شدن سایت توسط گوگل (Google/Bing و...)
        </label>
        <button type="submit" class="btn btn-primary">ذخیره تنظیمات</button>
    </form>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
