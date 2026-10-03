<?php
/**
 * Appearance Tab 4 — Branding Identity (Logo, Favicon, Theme)
 */
$logoUrl = siteLogoUrl();
$faviconUrl = siteFaviconUrl();
?>

<div class="appearance-tab-pane" id="pane-branding">
    <div class="branding-grid">

        <!-- 1. Logo Card -->
        <div class="appearance-card">
            <div class="appearance-card-header">
                <div>
                    <h3 class="appearance-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        لوگوی فروشگاه (Logo)
                    </h3>
                    <p class="appearance-card-subtitle">در هدر، فوتر و فاکتورها نمایش داده می‌شود.</p>
                </div>
                <span class="appr-pill <?= $logoUrl ? 'appr-pill-active' : 'appr-pill-muted' ?>">
                    <?= $logoUrl ? 'لوگو ثبت شده' : 'متن پیش‌فرض' ?>
                </span>
            </div>

            <form method="post" action="appearance.php" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="section" value="branding">
                <input type="hidden" name="tab" value="branding">

                <div class="appearance-card-body">
                    <div class="asset-preview-canvas" title="پیش‌نمایش لوگو در پس‌زمینه شطرنجی شفاف">
                        <?php if ($logoUrl): ?>
                            <img src="<?= e($logoUrl) ?>" alt="لوگوی فعلی فروشگاه">
                        <?php else: ?>
                            <span style="font-size:0.85rem; color:var(--appr-text-muted); font-weight:700;"><?= e(SITE_NAME) ?> (متن خام)</span>
                        <?php endif; ?>
                    </div>

                    <div class="asset-dropzone">
                        <input class="form-control" type="file" name="site_logo" accept="image/jpeg,image/png,image/webp,image/svg+xml">
                        <small style="display:block; color:var(--appr-text-muted); margin-top:6px; font-size:0.75rem;">
                            فرمت‌های مجاز: PNG شفاف، WEBP، JPG یا SVG (حداکثر ۲ مگابایت).
                        </small>
                    </div>
                </div>

                <div class="form-actions-bar">
                    <?php if ($logoUrl): ?>
                        <button type="submit" name="remove_logo" value="1" class="btn-appr-danger" onclick="return confirm('لوگو حذف شود و نام متنی فروشگاه جایگزین گردد؟');">
                            حذف لوگو
                        </button>
                    <?php endif; ?>
                    <button type="submit" class="btn-appr-save">
                        بارگذاری لوگو
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. Favicon Card -->
        <div class="appearance-card">
            <div class="appearance-card-header">
                <div>
                    <h3 class="appearance-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                        آیکون تب مرورگر (Favicon)
                    </h3>
                    <p class="appearance-card-subtitle">آیکون کوچک کنار نام سایت در تب مرورگر و بوکمارک‌ها.</p>
                </div>
                <span class="appr-pill <?= $faviconUrl ? 'appr-pill-active' : 'appr-pill-muted' ?>">
                    <?= $faviconUrl ? 'فاویکون اختصاصی' : 'پیش‌فرض' ?>
                </span>
            </div>

            <form method="post" action="appearance.php" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="section" value="branding">
                <input type="hidden" name="tab" value="branding">

                <div class="appearance-card-body">
                    <!-- Browser Tab Mockup -->
                    <div style="text-align:center; margin-bottom:12px;">
                        <div class="browser-tab-preview">
                            <div class="browser-tab-bubble">
                                <?php if ($faviconUrl): ?>
                                    <img src="<?= e($faviconUrl) ?>" alt="Favicon" class="browser-tab-favicon">
                                <?php else: ?>
                                    <span style="font-size:12px;">🧦</span>
                                <?php endif; ?>
                                <span><?= e(SITE_NAME) ?> | خانه</span>
                                <span style="color:#94A3B8; font-size:11px; margin-right:4px;">✕</span>
                            </div>
                        </div>
                    </div>

                    <div class="asset-dropzone">
                        <input class="form-control" type="file" name="site_favicon" accept="image/x-icon,image/png,image/svg+xml">
                        <small style="display:block; color:var(--appr-text-muted); margin-top:6px; font-size:0.75rem;">
                            فرمت‌های پیشنهادی: فایل ICO یا PNG با ابعاد 32x32 یا SVG مربع (حداکثر ۱ مگابایت).
                        </small>
                    </div>
                </div>

                <div class="form-actions-bar">
                    <?php if ($faviconUrl): ?>
                        <button type="submit" name="remove_favicon" value="1" class="btn-appr-danger" onclick="return confirm('فاویکون حذف شود و آیکون پیش‌فرض اعمال گردد؟');">
                            حذف فاویکون
                        </button>
                    <?php endif; ?>
                    <button type="submit" class="btn-appr-save">
                        بارگذاری فاویکون
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. Active Theme Card -->
        <div class="appearance-card" style="grid-column: 1 / -1;">
            <div class="appearance-card-header">
                <div>
                    <h3 class="appearance-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                        قالب فعال و پالت رنگ سراسری فروشگاه
                    </h3>
                    <p class="appearance-card-subtitle">تم انتخابی تمامی رنگ‌های دکمه‌ها، سربرگ‌ها، بج‌ها و پس‌زمینه فروشگاه را مشخص می‌کند.</p>
                </div>
                <a href="themes.php" class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
                    <span>مدیریت و ویرایش تم‌ها</span>
                    <span>↗</span>
                </a>
            </div>

            <div class="appearance-card-body">
                <div style="background:var(--appr-bg-subtle); padding:16px 20px; border-radius:10px; border:1px solid var(--appr-border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                    <div>
                        <span style="font-size:0.8rem; color:var(--appr-text-muted); display:block; margin-bottom:4px;">نام تم فعال:</span>
                        <strong style="font-size:1.15rem; color:var(--appr-primary);"><?= e($activeTheme['name'] ?? 'پیش‌فرض') ?></strong>
                    </div>

                    <?php if (!empty($activeTheme['tokens'])): ?>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:0.8rem; color:var(--appr-text-muted);">پالت رنگی تم:</span>
                            <div style="display:flex; gap:6px;">
                                <?php foreach (['bg', 'surface', 'primary', 'primary-dark', 'accent'] as $colorKey): ?>
                                    <?php if (!empty($activeTheme['tokens'][$colorKey])): ?>
                                        <span style="width:24px; height:24px; border-radius:50%; display:inline-block; border:1px solid #CBD5E1; background:<?= e($activeTheme['tokens'][$colorKey]) ?>;" title="<?= e($colorKey) ?>: <?= e($activeTheme['tokens'][$colorKey]) ?>"></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
