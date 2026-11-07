<?php
/**
 * Product-listing page for a category (including its subcategories, if any)
 */

$slug = $_GET['slug'] ?? '';

$stmt = db()->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    require __DIR__ . '/not_found.php';
    return;
}

$pageTitle = !empty($category['seo_title']) ? $category['seo_title'] : $category['name'];
$metaDescription = !empty($category['seo_description']) 
    ? $category['seo_description'] 
    : ($category['description'] ? seoCleanText($category['description'], 160) : ('خرید انواع ' . $category['name'] . ' در ' . SITE_NAME . ' با ارسال سریع و ضمانت کیفیت.'));
$canonicalUrl = rtrim(SITE_URL, '/') . '/category/' . $category['slug'];

// If this category has subcategories, their products are shown on this page too
$subCategories = db()->prepare("SELECT id, name, slug, image FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order ASC");
$subCategories->execute([$category['id']]);
$subCategories = $subCategories->fetchAll();

$categoryIds = getCategoryAndChildIds($category['id']);
$placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

// ---------- Filtering and sorting ----------
$sort = $_GET['sort'] ?? 'newest';
$onlyAvailable = isset($_GET['available']);

$orderMap = [
    'newest'     => 'created_at DESC',
    'price_asc'  => 'COALESCE(discount_price, price) ASC',
    'price_desc' => 'COALESCE(discount_price, price) DESC',
];
$orderBy = $orderMap[$sort] ?? $orderMap['newest'];

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = "category_id IN ($placeholders) AND is_active = 1";
$params = $categoryIds;
$stockSql = effectiveStockSqlFragment('products');
if ($onlyAvailable) {
    $where .= " AND $stockSql > 0";
}

$countStmt = db()->prepare("SELECT COUNT(*) FROM products WHERE $where");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

$stmt = db()->prepare("SELECT *, $stockSql AS effective_stock FROM products WHERE $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Breadcrumbs & Multi-schema Structured Data (CollectionPage + BreadcrumbList + FAQPage)
$breadcrumbs = [
    ['name' => 'خانه', 'url' => '/'],
    ['name' => 'دسته‌بندی‌ها', 'url' => '/categories'],
    ['name' => $category['name'], 'url' => '/category/' . $category['slug']],
];
$faqItems = seoParseJsonList($category['seo_faq'] ?? null);
$jsonLd = array_filter([
    seoBuildCategoryJsonLd($category, $products),
    seoBuildBreadcrumbsJsonLd($breadcrumbs),
    seoBuildFaqJsonLd($faqItems),
]);

renderView('site/category', compact(
    'pageTitle', 'metaDescription', 'canonicalUrl', 'category', 'slug', 'sort', 'onlyAvailable',
    'products', 'totalPages', 'page', 'subCategories', 'breadcrumbs', 'faqItems', 'jsonLd'
));
