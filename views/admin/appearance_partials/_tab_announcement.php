<?php
/**
 * Appearance Tab 5 — Announcement Bar & Footer Copy
 */
?>

<div class="appearance-tab-pane" id="pane-announcement">
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap:20px;">

        <!-- 1. Announcement Bar Card -->
        <div class="appearance-card">
            <div class="appearance-card-header">
                <div>
                    <h3 class="appearance-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                        نوار اعلان بالای سایت (Announcement Bar)
                    </h3>
                    <p class="appearance-card-subtitle">پیام اطلاع‌رسانی مهم، جشنواره تخفیف یا شرایط ارسال رایگان در بالاترین بخش سایت.</p>
                </div>
                <span class="appr-pill <?= $announcementBarEnabled ? 'appr-pill-active' : 'appr-pill-muted' ?>">
                    <?= $announcementBarEnabled ? 'اعلان فعال است' : 'اعلان غیرفعال است' ?>
                </span>
            </div>

            <form method="post" action="appearance.php">
                <?= csrfField() ?>
                <input type="hidden" name="section" value="announcement">
                <input type="hidden" name="tab" value="announcement">

                <div class="appearance-card-body">
                    <div style="margin-bottom:14px;">
                        <label class="section-item-label">
                            <input type="checkbox" name="announcement_bar_enabled" <?= $announcementBarEnabled ? 'checked' : '' ?>>
                            <span>نمایش نوار اعلان در بالای تمامی صفحات</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label style="font-size:0.82rem; font-weight:700;">متن اعلان</label>
                        <input class="form-control" type="text" name="announcement_bar_text" value="<?= e($announcementBarText) ?>" placeholder="مثلاً: ارسال رایگان خریدهای بالای ۵۰۰ هزار تومان">
                    </div>

                    <div class="form-group">
                        <label style="font-size:0.82rem; font-weight:700;">لینک اعلان (اختیاری)</label>
                        <input class="form-control" type="text" name="announcement_bar_link" dir="ltr" value="<?= e($announcementBarLink) ?>" placeholder="https://... یا /categories.php">
                    </div>
                </div>

                <div class="form-actions-bar">
                    <button type="submit" class="btn-appr-save">
                        ذخیره تنظیمات اعلان
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. Footer Copy Card -->
        <div class="appearance-card">
            <div class="appearance-card-header">
                <div>
                    <h3 class="appearance-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        متن‌های نمایشی فوتر (Footer)
                    </h3>
                    <p class="appearance-card-subtitle">توضیحات معرفی برند، تگ‌لاین و نمادهای متنی پایین سایت.</p>
                </div>
            </div>

            <form method="post" action="appearance.php">
                <?= csrfField() ?>
                <input type="hidden" name="section" value="footer">
                <input type="hidden" name="tab" value="announcement">

                <div class="appearance-card-body">
                    <div class="form-group">
                        <label style="font-size:0.82rem; font-weight:700;">متن دعوت به درباره ما (Teaser)</label>
                        <input class="form-control" type="text" name="footer_about_teaser_text" value="<?= e($footerAboutTeaserText) ?>" placeholder="درباره فروشگاه جوراب AB">
                    </div>

                    <div class="form-group">
                        <label style="font-size:0.82rem; font-weight:700;">توضیح کوتاه و تگ‌لاین برند در فوتر</label>
                        <textarea class="form-control" name="footer_tagline" rows="2" placeholder="توضیح کوتاه درباره کیفیت، تنوع و رسالت برند..."><?= e($footerTagline) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label style="font-size:0.82rem; font-weight:700;">متن نماد ارسال در فوتر</label>
                        <input class="form-control" type="text" name="footer_shipping_badge_text" value="<?= e($footerShippingBadgeText) ?>" placeholder="ارسال سریع پستی به سراسر کشور">
                    </div>
                </div>

                <div class="form-actions-bar">
                    <button type="submit" class="btn-appr-save">
                        ذخیره متن‌های فوتر
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
