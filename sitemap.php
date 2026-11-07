<?php
/**
 * Dynamic sitemap.xml with Google Image Sitemap Namespace.
 * Indexes core pages, category hubs, active products with primary images, and tag clusters.
 */

require_once __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = rtrim(SITE_URL, '/');

$categories = db()->query("SELECT slug, image, name, created_at FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$products = db()->query("SELECT name, slug, image, updated_at FROM products WHERE is_active = 1 ORDER BY updated_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$tags = db()->query("SELECT slug, name, created_at FROM tags ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

// 1. Core Pages
echo "  <url>\n    <loc>{$baseUrl}/</loc>\n    <changefreq>daily</changefreq>\n    <priority>1.0</priority>\n  </url>\n";
echo "  <url>\n    <loc>{$baseUrl}/categories</loc>\n    <changefreq>weekly</changefreq>\n    <priority>0.8</priority>\n  </url>\n";
echo "  <url>\n    <loc>{$baseUrl}/about</loc>\n    <changefreq>monthly</changefreq>\n    <priority>0.4</priority>\n  </url>\n";
echo "  <url>\n    <loc>{$baseUrl}/contact</loc>\n    <changefreq>monthly</changefreq>\n    <priority>0.4</priority>\n  </url>\n";

// 2. Categories
foreach ($categories as $c) {
    $lastmod = !empty($c['created_at']) ? date('Y-m-d', strtotime($c['created_at'])) : date('Y-m-d');
    $catUrl = $baseUrl . '/category/' . rawurlencode($c['slug']);
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($catUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    if (!empty($c['image'])) {
        $imgUrl = $baseUrl . UPLOAD_URL . $c['image'];
        echo "    <image:image>\n";
        echo "      <image:loc>" . htmlspecialchars($imgUrl, ENT_XML1, 'UTF-8') . "</image:loc>\n";
        echo "      <image:title>" . htmlspecialchars($c['name'], ENT_XML1, 'UTF-8') . "</image:title>\n";
        echo "    </image:image>\n";
    }
    echo "  </url>\n";
}

// 3. Products (with Image tags)
foreach ($products as $p) {
    $lastmod = !empty($p['updated_at']) ? date('Y-m-d', strtotime($p['updated_at'])) : date('Y-m-d');
    $prodUrl = $baseUrl . '/product/' . rawurlencode($p['slug']);
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($prodUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.9</priority>\n";
    if (!empty($p['image'])) {
        $imgUrl = $baseUrl . UPLOAD_URL . $p['image'];
        echo "    <image:image>\n";
        echo "      <image:loc>" . htmlspecialchars($imgUrl, ENT_XML1, 'UTF-8') . "</image:loc>\n";
        echo "      <image:title>" . htmlspecialchars($p['name'], ENT_XML1, 'UTF-8') . "</image:title>\n";
        echo "    </image:image>\n";
    }
    echo "  </url>\n";
}

// 4. Tags
foreach ($tags as $t) {
    $tagUrl = $baseUrl . '/tag/' . rawurlencode($t['slug']);
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($tagUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.6</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>';
