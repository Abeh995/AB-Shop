<?php
/**
 * Appearance Hub — Header Overview & Vital Badges
 */
$stats = $stats ?? [
    'activeSectionsCount' => 0,
    'heroBannerActive'    => false,
    'hasHeroBannerImage'  => false,
    'trustBarActive'      => false,
    'hasLogo'             => false,
    'hasFavicon'          => false,
    'announcementActive'  => false,
];
?>

<div class="appearance-pulse-card">
    <div class="appearance-pulse-lead">
        <div class="appearance-pulse-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.9a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
                <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/>
                <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>
            </svg>
        </div>
        <div>
            <h1 class="appearance-pulse-title">ظاهر و صفحه اصلی فروشگاه</h1>
            <p class="appearance-pulse-desc">کنترل بصری سکشن‌ها، بنر پروموشن، ارزش‌های برند، لوگو و پالت رنگ</p>
        </div>
    </div>

    <div class="appearance-pulse-badges">
        <span class="appr-pill <?= $stats['activeSectionsCount'] > 0 ? 'appr-pill-active' : 'appr-pill-muted' ?>">
            <span>سکشن‌های فعال:</span>
            <strong><?= toPersianDigits((string) $stats['activeSectionsCount']) ?> از ۵</strong>
        </span>

        <span class="appr-pill <?= $stats['heroBannerActive'] ? 'appr-pill-active' : 'appr-pill-muted' ?>">
            <span>بنر پروموشن:</span>
            <strong><?= $stats['heroBannerActive'] ? 'فعال' : 'غیرفعال' ?></strong>
        </span>

        <span class="appr-pill <?= $stats['trustBarActive'] ? 'appr-pill-active' : 'appr-pill-muted' ?>">
            <span>مزایای خرید:</span>
            <strong><?= $stats['trustBarActive'] ? 'فعال' : 'غیرفعال' ?></strong>
        </span>

        <span class="appr-pill <?= $stats['hasLogo'] ? 'appr-pill-active' : 'appr-pill-muted' ?>">
            <span>لوگو:</span>
            <strong><?= $stats['hasLogo'] ? 'ثبت‌شده' : 'پیش‌فرض' ?></strong>
        </span>

        <span class="appr-pill <?= $stats['hasFavicon'] ? 'appr-pill-active' : 'appr-pill-muted' ?>">
            <span>فاویکون:</span>
            <strong><?= $stats['hasFavicon'] ? 'اختصاصی' : 'پیش‌فرض' ?></strong>
        </span>

        <a href="themes.php" class="appr-pill appr-pill-theme" title="مدیریت تم‌ها و تغییر پالت رنگ">
            <span>تم فعال:</span>
            <strong><?= e($activeTheme['name'] ?? 'پیش‌فرض') ?> ↗</strong>
        </a>
    </div>
</div>
