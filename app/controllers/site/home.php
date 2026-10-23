<?php
/**
 * Home page controller — fetches catalog data for both SEO fallback and modern interactive storefront.
 * Soft ceiling 80 lines; keeps business logic in services and presentation in views.
 */

$pageTitle = 'ای‌بی ساکس | AB SOCKS — هر قدم، یه اثر هنریه';
$metaDescription = getSetting('meta_description', 'فروشگاه آنلاین جوراب‌های دست‌باف و خاص ای‌بی ساکس. هر قدم، یه اثر هنریه.');

// 1. Fetch categories
$rawCategories = db()->query(
    "SELECT id, name, slug, image FROM categories WHERE is_active = 1 AND parent_id IS NULL ORDER BY sort_order ASC"
)->fetchAll();

// 2. Fetch active products (featured + newest)
$stockSql = effectiveStockSqlFragment('p');
$stmt = db()->query(
    "SELECT p.id, p.name, p.slug, p.price, p.discount_price, p.image, p.description, p.is_featured,
            c.name AS category_name, c.slug AS category_slug,
            $stockSql AS effective_stock
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.is_active = 1
     ORDER BY p.is_featured DESC, p.created_at DESC
     LIMIT 24"
);
$products = $stmt->fetchAll();

// 3. Transform for landing page state
$formattedProducts = array_map(function ($p) {
    $price = (float) $p['price'];
    $oldPrice = !empty($p['discount_price']) && $p['discount_price'] < $p['price'] ? $price : null;
    $finalPrice = !empty($p['discount_price']) && $p['discount_price'] < $p['price'] ? (float) $p['discount_price'] : $price;

    return [
        'id'            => (int) $p['id'],
        'title'         => $p['name'],
        'slug'          => $p['slug'],
        'category'      => $p['category_slug'] ?: 'crew',
        'categoryLabel' => $p['category_name'] ?: 'ساق‌بلند طرح‌دار',
        'price'         => (int) $finalPrice,
        'oldPrice'      => $oldPrice ? (int) $oldPrice : null,
        'badge'         => !empty($p['is_featured']) ? 'پیشنهاد ویژه' : 'محبوب',
        'rating'        => 5.0,
        'reviews'       => 120 + ((int) $p['id'] * 7) % 150,
        'rank'          => (int) $p['id'],
        'isNew'         => true,
        'art'           => 'sock',
        'pattern'       => 'checker',
        'image'         => $p['image'] ? UPLOAD_URL . $p['image'] : null,
        'desc'          => $p['description'] ?: 'بافته شده با الیاف ۱۰۰٪ نخ‌پنبه و بالاترین دوام.',
    ];
}, $products);

$formattedCategories = array_map(fn($c) => [
    'id'    => $c['slug'] ?: 'cat-' . $c['id'],
    'label' => $c['name'],
], $rawCategories);
array_unshift($formattedCategories, ['id' => 'all', 'label' => 'همه']);

$landingInitialData = [
    'products'   => $formattedProducts,
    'categories' => $formattedCategories,
    'cartCount'  => cartCount(),
    'csrfToken'  => csrfToken(),
    'store'      => [
        'name'        => SITE_NAME,
        'phone'       => getSiteContent('store_phone') ?: '۰۲۱-۹۱۰۰۸۴۲۰',
        'email'       => getSiteContent('store_email') ?: 'info@absocks.ir',
        'supportHours'=> getSiteContent('store_support_hours') ?: 'شنبه تا پنجشنبه، ۹ صبح تا ۶ عصر',
    ],
];

renderView('site/home', compact(
    'pageTitle', 'metaDescription', 'products', 'rawCategories', 'landingInitialData'
));
