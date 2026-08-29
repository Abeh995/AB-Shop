<?php
/**
 * Dynamically generates sitemap.xml for primary pages, categories, and active products.
 * Sitemap generation is independent of Google indexing settings; it does not force indexing.
 * It is only useful when crawlers discover and process the sitemap; robots.txt remains the primary control.
 */

require_once __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = rtrim(SITE_URL, '/');

$categories = db()->query("SELECT slug, created_at FROM categories WHERE is_active = 1")->fetchAll();
$products = db()->query("SELECT slug, updated_at FROM products WHERE is_active = 1")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

echo "  <url><loc>{$baseUrl}/</loc><changefreq>daily</changefreq><priority>1.0</priority></url>\n";
echo "  <url><loc>{$baseUrl}/about</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>\n";
echo "  <url><loc>{$baseUrl}/contact</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>\n";

foreach ($categories as $c) {
    echo "  <url><loc>{$baseUrl}/category/" . e($c['slug']) . "</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>\n";
}

foreach ($products as $p) {
    $lastmod = date('Y-m-d', strtotime($p['updated_at']));
    echo "  <url><loc>{$baseUrl}/product/" . e($p['slug']) . "</loc><lastmod>{$lastmod}</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>\n";
}

echo '</urlset>';
