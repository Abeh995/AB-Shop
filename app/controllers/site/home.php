<?php
/**
 * Home page controller — data fetching and SEO wiring only; no HTML here.
 * Respects section visibility settings, hero promo banner, and trust items configured in admin/appearance.php.
 */

$pageTitle = 'خانه';
$canonicalUrl = rtrim(SITE_URL, '/') . '/';
$metaDescription = getSetting(
    'meta_description',
    'فروشگاه آنلاین جوراب‌های دست‌باف و خاص ای‌بی ساکس؛ خرید اینترنتی جوراب مردانه، زنانه و بچگانه با ارسال سریع.'
);
$jsonLd = [seoBuildOrganizationJsonLd()];

$homeIntroEnabled = getSetting('home_intro_enabled', '0') === '1';
$homeIntroTitle = getSetting('home_intro_title', 'جوراب‌هایی که هر روزتان را راحت‌تر می‌کنند');
$homeIntroSubtitle = getSetting('home_intro_subtitle', 'کیفیت پارچه، دوخت مقاوم و طرح‌های به‌روز؛ مستقیم درِ خانه شما.');

$heroBannerEnabled = getSetting('hero_banner_enabled', '0') === '1';
$heroBannerImage = heroBannerImageUrl();
$heroBannerBadge = getSetting('hero_banner_badge', 'پیشنهاد ویژه این فصل');
$heroBannerTitle = getSetting('hero_banner_title', 'کالکشن جدید و خاص جوراب‌های AB');
$heroBannerSubtitle = getSetting('hero_banner_subtitle', 'تنوع بی‌نظیر طرح‌ها و رنگ‌ها با الیاف ۱۰۰٪ نخ‌پنبه و بالاترین دوام');
$heroBannerCtaText = getSetting('hero_banner_cta_text', 'مشاهده همه محصولات');
$heroBannerCtaUrl = getSetting('hero_banner_cta_url', '/categories');

$trustBarEnabled = getSetting('trust_bar_enabled', '1') === '1';
$trustItems = [];
if ($trustBarEnabled) {
    for ($i = 1; $i <= 4; $i++) {
        $trustItems[] = [
            'title' => getSetting("trust_item_{$i}_title", ''),
            'desc'  => getSetting("trust_item_{$i}_desc", ''),
        ];
    }
}

$homeCategoriesEnabled = getSetting('home_categories_enabled', '0') === '1';
$homeCategoriesTitle = getSetting('home_categories_title', 'دسته‌بندی‌ها');
$homeFeaturedEnabled = getSetting('home_featured_enabled', '1') === '1';
$homeFeaturedTitle = getSetting('home_featured_title', 'پیشنهاد ویژه');
$homeFeaturedLimit = max(2, min(24, (int) getSetting('home_featured_limit', '6')));
$homeNewestEnabled = getSetting('home_newest_enabled', '1') === '1';
$homeNewestTitle = getSetting('home_newest_title', 'آخرین محصولات');
$homeNewestLimit = max(2, min(24, (int) getSetting('home_newest_limit', '6')));
$homeCategoryStripEnabled = getSetting('home_category_strip_enabled', '1') === '1';
$homeCategoryStripTitle = getSetting('home_category_strip_title', 'دسته‌بندی‌ها را از همین‌جا هم می‌بینید');

$categories = ($homeCategoriesEnabled || $homeCategoryStripEnabled) ? getHomeCategories() : [];
$featured = $homeFeaturedEnabled ? getHomeFeaturedProducts($homeFeaturedLimit) : [];
$newest = $homeNewestEnabled ? getHomeNewestProducts($homeNewestLimit) : [];

renderView('site/home', compact(
    'pageTitle', 'canonicalUrl', 'metaDescription', 'jsonLd',
    'categories', 'featured', 'newest',
    'homeIntroEnabled', 'homeIntroTitle', 'homeIntroSubtitle',
    'heroBannerEnabled', 'heroBannerImage', 'heroBannerBadge',
    'heroBannerTitle', 'heroBannerSubtitle', 'heroBannerCtaText', 'heroBannerCtaUrl',
    'trustBarEnabled', 'trustItems',
    'homeCategoriesEnabled', 'homeCategoriesTitle',
    'homeFeaturedEnabled', 'homeFeaturedTitle',
    'homeNewestEnabled', 'homeNewestTitle',
    'homeCategoryStripEnabled', 'homeCategoryStripTitle'
));
