<?php
/**
 * Shared site footer.
 * Reads branding/social settings configured under Admin > Store settings.
 */
$footerAboutTeaser = getSetting('footer_about_teaser_text', '');
$footerShippingBadge = getSetting('footer_shipping_badge_text', '');
$storePhone = getSetting('store_phone', '');
$footerLogoUrl = siteLogoUrl();

$socialLabels = ['instagram' => 'اینستاگرام', 'telegram' => 'تلگرام', 'bale' => 'بله', 'torob' => 'ترب'];
$socialIcons = [
    'instagram' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>',
    'telegram'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7Z"/></svg>',
    'bale'      => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-8.5 8.5 8.5 8.5 0 0 1-4-1L3 20l1-5.5A8.38 8.38 0 0 1 3 11.5 8.5 8.5 0 0 1 12.5 3 8.38 8.38 0 0 1 21 11.5Z"/></svg>',
    'torob'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41 11 3.83A2 2 0 0 0 9.59 3.2L4 3v5.59a2 2 0 0 0 .59 1.41l9.58 9.59a2 2 0 0 0 2.83 0l3.59-3.59a2 2 0 0 0 0-2.59Z"/><circle cx="8.5" cy="8.5" r="1.5"/></svg>',
];

$activeSocialLinks = [];
foreach (array_keys($socialLabels) as $key) {
    $enabled = getSetting("social_{$key}_enabled", '0') === '1';
    $url = getSetting("social_{$key}_url", '');
    if ($enabled && $url !== '') {
        $activeSocialLinks[$key] = $url;
    }
}

$enamadEnabled = getSetting('enamad_enabled', '0') === '1';
$enamadEmbedCode = getSetting('enamad_embed_code', '');
?>
</main>

<footer class="site-footer">
    <?php if ($footerAboutTeaser !== ''): ?>
    <div class="footer-teaser">
        <div class="container">
            <a href="/about"><?= e($footerAboutTeaser) ?></a>
        </div>
    </div>
    <?php endif; ?>

    <div class="container footer-inner">
        <div class="footer-col footer-col-brand">
            <a href="/" class="footer-logo">
                <?php if ($footerLogoUrl): ?>
                    <img src="<?= e($footerLogoUrl) ?>" alt="<?= e(SITE_NAME) ?>">
                <?php else: ?>
                    <?= e(SITE_NAME) ?>
                <?php endif; ?>
            </a>
            <p>خرید آنلاین انواع جوراب با کیفیت بالا و ارسال سریع به سراسر ایران.</p>
            <?php if ($storePhone !== ''): ?>
                <a href="tel:<?= e($storePhone) ?>" class="footer-phone" dir="ltr"><?= e($storePhone) ?></a>
            <?php endif; ?>
            <?php if ($footerShippingBadge !== ''): ?>
                <p class="footer-shipping-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <?= e($footerShippingBadge) ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="footer-col">
            <h4>دسترسی سریع</h4>
            <a href="/about">درباره ما</a>
            <a href="/contact">تماس با ما</a>
            <a href="/terms">قوانین و مقررات</a>
        </div>

        <div class="footer-col">
            <h4>پشتیبانی</h4>
            <p>شنبه تا پنج‌شنبه، ۹ تا ۱۸</p>
        </div>

        <?php if ($activeSocialLinks || ($enamadEnabled && $enamadEmbedCode !== '')): ?>
        <div class="footer-col">
            <h4>ما را دنبال کنید</h4>
            <?php if ($activeSocialLinks): ?>
            <div class="footer-social">
                <?php foreach ($activeSocialLinks as $key => $url): ?>
                    <a href="<?= e($url) ?>" target="_blank" rel="noopener nofollow" title="<?= e($socialLabels[$key]) ?>"><?= $socialIcons[$key] ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if ($enamadEnabled && $enamadEmbedCode !== ''): ?>
                <div class="footer-enamad"><?= $enamadEmbedCode /* Trusted admin-only embed code from enamad.ir, not user input */ ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="footer-bottom">
        <div class="container">
            &copy; <?= date('Y') ?> <?= e(SITE_NAME) ?> — تمامی حقوق محفوظ است.
        </div>
    </div>
</footer>

<script src="/assets/js/main.js"></script>
</body>
</html>
