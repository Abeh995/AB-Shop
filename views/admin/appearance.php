<?php
require APP_ROOT . '/views/admin/layout/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <div>
        <p style="color:var(--color-muted); font-size:.9rem; margin:0;">
            کنترل چیدمان بصری و بخش‌های نمایشی سمت مشتری: صفحه اصلی، پالت رنگ، لوگو، نوار اعلان و فوتر.
        </p>
    </div>
</div>

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap:20px; align-items:start;">

    <!-- Home Sections Card -->
    <div class="admin-card" style="grid-column: 1 / -1;">
        <h3 style="margin-bottom:12px;">🏠 مدیریت بخش‌های صفحه اصلی (Landing Page)</h3>
        <p style="color:var(--color-muted); font-size:.88rem; margin-bottom:20px;">
            هر کدام از بخش‌های صفحه اصلی را می‌توانید به دلخواه روشن یا خاموش کنید و عنوان نمایشی آن‌ها را تغییر دهید.
        </p>

        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="home_sections">

            <div style="display:flex; flex-direction:column; gap:16px;">

                <!-- 1. Intro Section -->
                <div style="padding:14px 16px; border:1px solid var(--color-border); border-radius:10px; background:#FAF8F5;">
                    <label style="display:flex; align-items:center; gap:10px; font-weight:700; font-size:.95rem; cursor:pointer; margin-bottom:10px;">
                        <input type="checkbox" name="home_intro_enabled" value="1" <?= $homeIntroEnabled ? 'checked' : '' ?>>
                        <span>۱. متن و پیام معرفی بالای صفحه (Hero Intro)</span>
                        <?php if (!$homeIntroEnabled): ?>
                            <span class="status-pill status-cancelled" style="font-size:.72rem;">غیرفعال</span>
                        <?php else: ?>
                            <span class="status-pill status-delivered" style="font-size:.72rem;">فعال</span>
                        <?php endif; ?>
                    </label>
                    <div class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label style="font-size:.8rem;">تیتر اصلی</label>
                            <input class="form-control" type="text" name="home_intro_title" value="<?= e($homeIntroTitle) ?>" placeholder="جوراب‌هایی که هر روزتان را راحت‌تر می‌کنند">
                        </div>
                        <div class="form-group" style="flex:2;">
                            <label style="font-size:.8rem;">توضیحات کوتاه</label>
                            <input class="form-control" type="text" name="home_intro_subtitle" value="<?= e($homeIntroSubtitle) ?>" placeholder="کیفیت پارچه، دوخت مقاوم و...">
                        </div>
                    </div>
                </div>

                <!-- 2. Big Categories Grid -->
                <div style="padding:14px 16px; border:1px solid var(--color-border); border-radius:10px; background:#FAF8F5;">
                    <label style="display:flex; align-items:center; gap:10px; font-weight:700; font-size:.95rem; cursor:pointer; margin-bottom:10px;">
                        <input type="checkbox" name="home_categories_enabled" value="1" <?= $homeCategoriesEnabled ? 'checked' : '' ?>>
                        <span>۲. کارت‌های بزرگ دسته‌بندی‌ها (بالای صفحه)</span>
                        <?php if (!$homeCategoriesEnabled): ?>
                            <span class="status-pill status-cancelled" style="font-size:.72rem;">غیرفعال</span>
                        <?php else: ?>
                            <span class="status-pill status-delivered" style="font-size:.72rem;">فعال</span>
                        <?php endif; ?>
                    </label>
                    <div class="form-group" style="max-width:320px; margin:0;">
                        <label style="font-size:.8rem;">عنوان بخش</label>
                        <input class="form-control" type="text" name="home_categories_title" value="<?= e($homeCategoriesTitle) ?>" placeholder="دسته‌بندی‌ها">
                    </div>
                </div>

                <!-- 3. Featured Carousel -->
                <div style="padding:14px 16px; border:1px solid var(--color-border); border-radius:10px; background:#FAF8F5;">
                    <label style="display:flex; align-items:center; gap:10px; font-weight:700; font-size:.95rem; cursor:pointer; margin-bottom:10px;">
                        <input type="checkbox" name="home_featured_enabled" value="1" <?= $homeFeaturedEnabled ? 'checked' : '' ?>>
                        <span>۳. اسلایدر پیشنهاد ویژه (Featured Products)</span>
                        <?php if (!$homeFeaturedEnabled): ?>
                            <span class="status-pill status-cancelled" style="font-size:.72rem;">غیرفعال</span>
                        <?php else: ?>
                            <span class="status-pill status-delivered" style="font-size:.72rem;">فعال</span>
                        <?php endif; ?>
                    </label>
                    <div class="form-group" style="max-width:320px; margin:0;">
                        <label style="font-size:.8rem;">عنوان بخش</label>
                        <input class="form-control" type="text" name="home_featured_title" value="<?= e($homeFeaturedTitle) ?>" placeholder="پیشنهاد ویژه">
                    </div>
                </div>

                <!-- 4. Newest Carousel -->
                <div style="padding:14px 16px; border:1px solid var(--color-border); border-radius:10px; background:#FAF8F5;">
                    <label style="display:flex; align-items:center; gap:10px; font-weight:700; font-size:.95rem; cursor:pointer; margin-bottom:10px;">
                        <input type="checkbox" name="home_newest_enabled" value="1" <?= $homeNewestEnabled ? 'checked' : '' ?>>
                        <span>۴. اسلایدر آخرین محصولات (Newest Products)</span>
                        <?php if (!$homeNewestEnabled): ?>
                            <span class="status-pill status-cancelled" style="font-size:.72rem;">غیرفعال</span>
                        <?php else: ?>
                            <span class="status-pill status-delivered" style="font-size:.72rem;">فعال</span>
                        <?php endif; ?>
                    </label>
                    <div class="form-group" style="max-width:320px; margin:0;">
                        <label style="font-size:.8rem;">عنوان بخش</label>
                        <input class="form-control" type="text" name="home_newest_title" value="<?= e($homeNewestTitle) ?>" placeholder="آخرین محصولات">
                    </div>
                </div>

                <!-- 5. Bottom Category Strip -->
                <div style="padding:14px 16px; border:1px solid var(--color-border); border-radius:10px; background:#FAF8F5;">
                    <label style="display:flex; align-items:center; gap:10px; font-weight:700; font-size:.95rem; cursor:pointer; margin-bottom:10px;">
                        <input type="checkbox" name="home_category_strip_enabled" value="1" <?= $homeCategoryStripEnabled ? 'checked' : '' ?>>
                        <span>۵. نوار افقی دسته‌بندی‌ها در پایین صفحه (Category Strip)</span>
                        <?php if (!$homeCategoryStripEnabled): ?>
                            <span class="status-pill status-cancelled" style="font-size:.72rem;">غیرفعال</span>
                        <?php else: ?>
                            <span class="status-pill status-delivered" style="font-size:.72rem;">فعال</span>
                        <?php endif; ?>
                    </label>
                    <div class="form-group" style="max-width:360px; margin:0;">
                        <label style="font-size:.8rem;">عنوان بخش</label>
                        <input class="form-control" type="text" name="home_category_strip_title" value="<?= e($homeCategoryStripTitle) ?>" placeholder="دسته‌بندی‌ها را از همین‌جا هم می‌بینید">
                    </div>
                </div>

            </div>

            <div style="margin-top:20px;">
                <button type="submit" class="btn btn-primary" style="padding:10px 24px;">ذخیره تنظیمات صفحه اصلی</button>
            </div>
        </form>
    </div>

    <!-- Active Theme Card -->
    <div class="admin-card">
        <h3 style="margin-bottom:12px;">🎨 قالب و پالت رنگ سایت</h3>
        <p style="color:var(--color-muted); font-size:.88rem; margin-bottom:16px;">
            پالت رنگی سراسری فروشگاه از طریق بخش تم‌ها مدیریت می‌شود.
        </p>
        <div style="background:#FAF8F5; padding:16px; border-radius:10px; border:1px solid var(--color-border); margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <span style="font-size:.85rem; color:var(--color-muted); display:block;">تم فعال فعلی:</span>
                <strong style="font-size:1.1rem; color:var(--color-primary);"><?= e($activeTheme['name'] ?? 'پیش‌فرض') ?></strong>
            </div>
            <a href="themes.php" class="btn btn-outline btn-sm">مدیریت و تغییر تم‌ها ↗</a>
        </div>
    </div>

    <!-- Branding / Logo Card -->
    <div class="admin-card">
        <h3 style="margin-bottom:12px;">🖼️ لوگوی فروشگاه</h3>
        <p style="color:var(--color-muted); font-size:.88rem; margin-bottom:16px;">
            لوگو در هدر و فوتر نمایش داده می‌شود. (فرمت‌های مجاز: JPG، PNG، WEBP یا SVG).
        </p>
        <?php if ($siteLogo && siteLogoUrl()): ?>
            <div style="margin-bottom:14px; text-align:center; background:var(--color-primary-light); padding:12px; border-radius:8px;">
                <img src="<?= e(siteLogoUrl()) ?>" alt="لوگوی فعلی" style="max-height:56px; max-width:200px; object-fit:contain;">
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="branding">
            <div class="form-group">
                <input class="form-control" type="file" name="site_logo" accept="image/*,.heic,.heif,.svg" data-optimize-image="logo" data-max-dimension="1000" data-default-quality="0.30" data-allow-svg="true">
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary btn-sm">بارگذاری لوگو</button>
                <?php if ($siteLogo): ?>
                    <button type="submit" name="remove_logo" value="1" class="btn btn-outline btn-sm" onclick="return confirm('لوگو حذف شود؟');">حذف لوگو</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Announcement Bar Card -->
    <div class="admin-card">
        <h3 style="margin-bottom:12px;">📢 نوار اعلان بالای سایت</h3>
        <p style="color:var(--color-muted); font-size:.88rem; margin-bottom:16px;">
            اطلاع‌رسانی مهم، تخفیف عمومی یا شرایط ارسال زیر هدر سایت.
        </p>
        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="announcement">
            <label style="display:flex; align-items:center; gap:8px; margin-bottom:14px; cursor:pointer;">
                <input type="checkbox" name="announcement_bar_enabled" <?= $announcementBarEnabled ? 'checked' : '' ?>>
                <strong>نمایش نوار اعلان</strong>
            </label>
            <div class="form-group">
                <label style="font-size:.82rem;">متن اعلان</label>
                <input class="form-control" type="text" name="announcement_bar_text" value="<?= e($announcementBarText) ?>" placeholder="مثلاً: ارسال رایگان خریدهای بالای ۵۰۰ هزار تومان">
            </div>
            <div class="form-group">
                <label style="font-size:.82rem;">لینک اعلان (اختیاری)</label>
                <input class="form-control" type="text" name="announcement_bar_link" dir="ltr" value="<?= e($announcementBarLink) ?>" placeholder="https://...">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">ذخیره اعلان</button>
        </form>
    </div>

    <!-- Footer Content Card -->
    <div class="admin-card">
        <h3 style="margin-bottom:12px;">📄 متن‌های فوتر</h3>
        <p style="color:var(--color-muted); font-size:.88rem; margin-bottom:16px;">
            توضیحات کوتاه برند و نماد ارسال در فوتر سایت.
        </p>
        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="footer">
            <div class="form-group">
                <label style="font-size:.82rem;">متن دعوت به درباره ما</label>
                <input class="form-control" type="text" name="footer_about_teaser_text" value="<?= e($footerAboutTeaserText) ?>">
            </div>
            <div class="form-group">
                <label style="font-size:.82rem;">توضیح برند در فوتر</label>
                <textarea class="form-control" name="footer_tagline" rows="2"><?= e($footerTagline) ?></textarea>
            </div>
            <div class="form-group">
                <label style="font-size:.82rem;">متن نماد ارسال</label>
                <input class="form-control" type="text" name="footer_shipping_badge_text" value="<?= e($footerShippingBadgeText) ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">ذخیره فوتر</button>
        </form>
    </div>

</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
