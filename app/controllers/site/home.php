<?php
/**
 * Home page controller — assembles the storefront landing payload.
 *
 * Soft ceiling 80 lines. All queries/transformations live in
 * StorefrontService and LandingService (Rule 7); this file only wires data into
 * the view.
 */

// 0. Landing content: the Studio draft is served inside the Studio preview
//    iframe only; every visitor gets the published snapshot.
$landingPreviewMode = (($_GET['lb_preview'] ?? '') === '1') && isAdminLoggedIn();
$landingConfig = $landingPreviewMode ? getLandingDraftConfig() : getLandingPublishedConfig();

$hero = $landingConfig['hero'] ?? [];
$seo = $landingConfig['seo'] ?? [];

$pageTitle = trim((string) ($seo['title'] ?? ''));
if ($pageTitle === '') {
    $pageTitle = 'ای‌بی ساکس | AB SOCKS — هر قدم، یه اثر هنریه';
}
$metaDescription = trim((string) ($seo['description'] ?? ''));
if ($metaDescription === '') {
    $metaDescription = getSetting('meta_description', 'فروشگاه آنلاین جوراب‌های دست‌باف و خاص ای‌بی ساکس. هر قدم، یه اثر هنریه.');
}

// 1. Catalog data for both the SEO fallback markup and the React island.
$catalog = getLandingCatalogPayload();
$products = $catalog['raw'];
$rawCategories = $catalog['categories'];

$landingInitialData = [
    'products'      => $catalog['cards'],
    'landingConfig' => $landingConfig,
    'previewMode'   => $landingPreviewMode,
    'categories'    => $catalog['categories'],
    'cartCount'     => cartCount(),
    'csrfToken'     => csrfToken(),
    'store'         => [
        'name'         => SITE_NAME,
        'phone'        => getSiteContent('store_phone') ?: '۰۲۱-۹۱۰۰۸۴۲۰',
        'email'        => getSiteContent('store_email') ?: 'info@absocks.ir',
        'supportHours' => getSiteContent('store_support_hours') ?: 'شنبه تا پنجشنبه، ۹ صبح تا ۶ عصر',
    ],
];

renderView('site/home', compact(
    'pageTitle', 'metaDescription', 'products', 'rawCategories', 'landingInitialData', 'landingConfig', 'hero', 'seo'
));
