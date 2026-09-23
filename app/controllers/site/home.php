<?php
/**
 * Home page controller — data fetching only; no HTML is written here.
 * Respects section visibility settings configured in the admin panel.
 */

$pageTitle = 'خانه';

$homeIntroEnabled = getSetting('home_intro_enabled', '0') === '1';
$homeIntroTitle = getSetting('home_intro_title', 'جوراب‌هایی که هر روزتان را راحت‌تر می‌کنند');
$homeIntroSubtitle = getSetting('home_intro_subtitle', 'کیفیت پارچه، دوخت مقاوم و طرح‌های به‌روز؛ مستقیم درِ خانه شما.');

$homeCategoriesEnabled = getSetting('home_categories_enabled', '0') === '1';
$homeCategoriesTitle = getSetting('home_categories_title', 'دسته‌بندی‌ها');

$homeFeaturedEnabled = getSetting('home_featured_enabled', '1') === '1';
$homeFeaturedTitle = getSetting('home_featured_title', 'پیشنهاد ویژه');

$homeNewestEnabled = getSetting('home_newest_enabled', '1') === '1';
$homeNewestTitle = getSetting('home_newest_title', 'آخرین محصولات');

$homeCategoryStripEnabled = getSetting('home_category_strip_enabled', '1') === '1';
$homeCategoryStripTitle = getSetting('home_category_strip_title', 'دسته‌بندی‌ها را از همین‌جا هم می‌بینید');

$categories = [];
if ($homeCategoriesEnabled || $homeCategoryStripEnabled) {
    $categories = db()->query("SELECT id, name, slug, image FROM categories WHERE is_active = 1 AND parent_id IS NULL ORDER BY sort_order ASC")->fetchAll();
}

$stockSql = effectiveStockSqlFragment('products');

$featured = [];
if ($homeFeaturedEnabled) {
    $featured = db()->query("
        SELECT *, $stockSql AS effective_stock FROM products
        WHERE is_active = 1 AND is_featured = 1
        ORDER BY created_at DESC LIMIT 6
    ")->fetchAll();
}

$newest = [];
if ($homeNewestEnabled) {
    $newest = db()->query("
        SELECT *, $stockSql AS effective_stock FROM products
        WHERE is_active = 1
        ORDER BY created_at DESC LIMIT 6
    ")->fetchAll();
}

renderView('site/home', compact(
    'pageTitle',
    'categories',
    'featured',
    'newest',
    'homeIntroEnabled',
    'homeIntroTitle',
    'homeIntroSubtitle',
    'homeCategoriesEnabled',
    'homeCategoriesTitle',
    'homeFeaturedEnabled',
    'homeFeaturedTitle',
    'homeNewestEnabled',
    'homeNewestTitle',
    'homeCategoryStripEnabled',
    'homeCategoryStripTitle'
));
