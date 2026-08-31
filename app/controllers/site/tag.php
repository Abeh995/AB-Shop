<?php
/**
 * Product-listing page for a single tag (topical grouping + SEO landing page).
 */

$slug = $_GET['slug'] ?? '';

$stmt = db()->prepare("SELECT * FROM tags WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$tag = $stmt->fetch();

if (!$tag) {
    http_response_code(404);
    require __DIR__ . '/not_found.php';
    return;
}

$pageTitle = $tag['name'];
$stockSql = effectiveStockSqlFragment('p');

$stmt = db()->prepare("SELECT p.*, $stockSql AS effective_stock FROM products p
                        JOIN product_tags pt ON pt.product_id = p.id
                        WHERE pt.tag_id = ? AND p.is_active = 1
                        ORDER BY p.created_at DESC");
$stmt->execute([$tag['id']]);
$products = $stmt->fetchAll();

// A tag-specific meta description (instead of the generic site-wide default)
// gives each tag page unique, keyword-relevant content for search engines.
$metaDescription = 'خرید ' . $tag['name'] . ' — ' . count($products) . ' محصول متنوع با ارسال سریع در ' . SITE_NAME . '.';

renderView('site/tag', compact('pageTitle', 'tag', 'products'));
