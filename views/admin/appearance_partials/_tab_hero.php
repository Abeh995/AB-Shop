<?php
/**
 * Appearance Tab 2 — Hero Promo Banner
 */
$bannerUrl = heroBannerImageUrl();
?>

<div class="appearance-tab-pane" id="pane-hero">
    <div class="appearance-card">
        <div class="appearance-card-header">
            <div>
                <h3 class="appearance-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    بنر پروموشن ویژه صفحه اصلی (Hero Promo Banner)
                </h3>
                <p class="appearance-card-subtitle">
                    بنر تصویری شاخص بالای صفحه برای جلب توجه اولیه مشتری، معرفی جشنواره‌های فصلی و افزایش نرخ تبدیل خرید.
                </p>
            </div>
            <span class="appr-pill <?= $heroBannerEnabled ? 'appr-pill-active' : 'appr-pill-muted' ?>">
                <?= $heroBannerEnabled ? 'بنر فعال است' : 'بنر غیرفعال است' ?>
            </span>
        </div>

        <form method="post" action="appearance.php" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="hero_banner">
            <input type="hidden" name="tab" value="hero">

            <div class="appearance-card-body">
                <div class="hero-editor-grid">
                    <!-- Right/Inputs Column -->
                    <div>
                        <div style="margin-bottom:16px;">
                            <label class="section-item-label">
                                <input type="checkbox" name="hero_banner_enabled" value="1" <?= $heroBannerEnabled ? 'checked' : '' ?>>
                                <span>نمایش بنر پروموشن بالای صفحه اصلی</span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label style="font-size:0.82rem; font-weight:700;">برچسب کوچک بالای تیتر (Badge)</label>
                            <input class="form-control" type="text" name="hero_banner_badge" value="<?= e($heroBannerBadge) ?>" placeholder="مثلاً: پیشنهاد ویژه این فصل">
                        </div>

                        <div class="form-group">
                            <label style="font-size:0.82rem; font-weight:700;">تیتر اصلی بنر (H1)</label>
                            <input class="form-control" type="text" name="hero_banner_title" value="<?= e($heroBannerTitle) ?>" placeholder="کالکشن جدید و خاص جوراب‌های AB">
                        </div>

                        <div class="form-group">
                            <label style="font-size:0.82rem; font-weight:700;">توضیحات کوتاه زیر تیتر</label>
                            <textarea class="form-control" name="hero_banner_subtitle" rows="2" placeholder="تنوع بی‌نظیر طرح‌ها با الیاف طبیعی نخ‌پنبه و بالاترین دوام"><?= e($heroBannerSubtitle) ?></textarea>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="form-group">
                                <label style="font-size:0.82rem; font-weight:700;">متن دکمه خرید (CTA)</label>
                                <input class="form-control" type="text" name="hero_banner_cta_text" value="<?= e($heroBannerCtaText) ?>" placeholder="مشاهده همه محصولات">
                            </div>
                            <div class="form-group">
                                <label style="font-size:0.82rem; font-weight:700;">لینک دکمه مقصد</label>
                                <input class="form-control" type="text" name="hero_banner_cta_url" dir="ltr" value="<?= e($heroBannerCtaUrl) ?>" placeholder="/categories.php">
                            </div>
                        </div>

                        <div class="form-group" style="margin-top:14px;">
                            <label style="font-size:0.82rem; font-weight:700;">تصویر پس‌زمینه بنر</label>
                            <div class="asset-dropzone">
                                <input class="form-control" type="file" name="hero_banner_image" accept="image/jpeg,image/png,image/webp">
                                <small style="display:block; color:var(--appr-text-muted); margin-top:6px; font-size:0.75rem;">
                                    ابعاد پیشنهادی: حداقل ۱۲۰۰ در ۴۲۰ پیکسل (فرمت JPG، PNG یا WEBP، حداکثر ۳ مگابایت).
                                </small>
                            </div>
                        </div>

                        <?php if ($bannerUrl): ?>
                            <div style="margin-top:10px;">
                                <button type="submit" name="remove_hero_banner_image" value="1" class="btn-appr-danger" onclick="return confirm('تصویر بنر حذف شود؟');">
                                    حذف تصویر بنر
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Left/Preview Column -->
                    <div>
                        <label style="font-size:0.82rem; font-weight:800; color:var(--appr-text-secondary); display:block; margin-bottom:10px;">
                            پیش‌نمایش بصری زنده بنر (شماتیک):
                        </label>

                        <div class="hero-preview-box">
                            <?php if ($bannerUrl): ?>
                                <img src="<?= e($bannerUrl) ?>" alt="پیش‌نمایش بنر" class="hero-preview-bg">
                            <?php endif; ?>
                            <div class="hero-preview-content">
                                <?php if (!empty($heroBannerBadge)): ?>
                                    <span class="hero-preview-badge"><?= e($heroBannerBadge) ?></span>
                                <?php endif; ?>
                                <h4 class="hero-preview-title"><?= e($heroBannerTitle ?: 'کالکشن جدید جوراب‌های AB') ?></h4>
                                <p class="hero-preview-sub"><?= e($heroBannerSubtitle ?: 'تنوع بی‌نظیر طرح‌ها با الیاف ۱۰۰٪ نخ‌پنبه') ?></p>
                                <?php if (!empty($heroBannerCtaText)): ?>
                                    <span class="hero-preview-btn"><?= e($heroBannerCtaText) ?> ←</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <small style="display:block; color:var(--appr-text-muted); font-size:0.76rem; margin-top:10px; line-height:1.5;">
                            ⚡ در صورت فعال بودن، این بنر در بالاترین بخش صفحه اصلی فروشگاه نمایش داده شده و جایگزین Hero Intro متنی می‌گردد.
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-actions-bar">
                <button type="submit" class="btn-appr-save">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    ذخیره تنظیمات بنر پروموشن
                </button>
            </div>
        </form>
    </div>
</div>
