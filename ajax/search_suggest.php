<?php
/**
 * Live search and autocomplete suggestion endpoint (FEAT-C001).
 *
 * Lightweight, fast JSON endpoint designed for storefront search autocomplete.
 * Reads search scope and suggestion limits from the database settings table,
 * allowing full customization from the admin panel without code modification.
 */

require_once __DIR__ . '/../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

// Ensure GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'روش درخواست نامعتبر است']);
    exit;
}

// Check if storefront live search is enabled in admin settings
$liveEnabled = getSetting('search_live_enabled', '1') === '1';
if (!$liveEnabled) {
    echo json_encode([
        'ok' => true,
        'enabled' => false,
        'query' => '',
        'total' => 0,
        'categories' => [],
        'results' => []
    ]);
    exit;
}

$rawQuery = trim($_GET['q'] ?? '');
$minChars = max(1, min(5, (int) getSetting('search_min_chars', '2')));

// Require minimum length (UTF-8 character count)
if (mb_strlen($rawQuery) < $minChars) {
    echo json_encode([
        'ok' => true,
        'enabled' => true,
        'query' => $rawQuery,
        'total' => 0,
        'categories' => [],
        'results' => []
    ]);
    exit;
}

// Sanitize query string (max 60 chars)
$query = mb_substr(strip_tags($rawQuery), 0, 60);
$limit = max(2, min(20, (int) getSetting('search_suggest_limit', '6')));

// Search scope settings
$scopeName = getSetting('search_scope_name', '1') === '1';
$scopeDesc = getSetting('search_scope_description', '1') === '1';
$includeCategories = getSetting('search_include_categories', '1') === '1';

// Build search WHERE clause for products
$searchClauses = [];
$searchParams = [];

if ($scopeName) {
    $searchClauses[] = 'p.name LIKE ?';
    $searchParams[] = '%' . $query . '%';
}
if ($scopeDesc) {
    $searchClauses[] = 'p.description LIKE ?';
    $searchParams[] = '%' . $query . '%';
}

// Fallback to name search if all scopes were disabled
if (empty($searchClauses)) {
    $searchClauses[] = 'p.name LIKE ?';
    $searchParams[] = '%' . $query . '%';
}

$whereSql = '(' . implode(' OR ', $searchClauses) . ')';

// 1. Matching categories (if enabled in settings)
$matchedCategories = [];
if ($includeCategories) {
    $catStmt = db()->prepare("SELECT id, name, slug FROM categories 
                              WHERE is_active = 1 AND name LIKE ? 
                              ORDER BY sort_order ASC, name ASC LIMIT 3");
    $catStmt->execute(['%' . $query . '%']);
    while ($cat = $catStmt->fetch()) {
        $matchedCategories[] = [
            'id' => (int) $cat['id'],
            'name' => $cat['name'],
            'slug' => $cat['slug'],
            'url' => '/category/' . $cat['slug'],
        ];
    }
}

// 2. Count total matching products for "View all X results"
$countParams = $searchParams;
$countStmt = db()->prepare("SELECT COUNT(*) FROM products p WHERE p.is_active = 1 AND $whereSql");
$countStmt->execute($countParams);
$totalProducts = (int) $countStmt->fetchColumn();

// 3. Fetch top matching products
$stockSql = effectiveStockSqlFragment('p');

// Order by exact name prefix match first, then newest
$selectParams = array_merge($searchParams, ['%' . $query . '%']);
$querySql = "SELECT p.id, p.name, p.slug, p.price, p.discount_price, p.image,
                    c.name AS category_name, c.slug AS category_slug,
                    $stockSql AS effective_stock
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.is_active = 1 AND $whereSql
             ORDER BY (p.name LIKE ?) DESC, p.created_at DESC
             LIMIT $limit";

$stmt = db()->prepare($querySql);
$stmt->execute($selectParams);
$products = $stmt->fetchAll();

$formattedResults = [];
foreach ($products as $p) {
    $finalPrice = effectivePrice($p);
    $discount = discountPercent($p);
    $stock = (int) ($p['effective_stock'] ?? 0);

    // Format image URL
    $imgUrl = $p['image'] ? UPLOAD_URL . $p['image'] : '/assets/img/placeholder-sock.svg';

    $formattedResults[] = [
        'id' => (int) $p['id'],
        'name' => $p['name'],
        'slug' => $p['slug'],
        'url' => '/product/' . $p['slug'],
        'image' => $imgUrl,
        'category_name' => $p['category_name'] ?: 'دسته‌بندی نشده',
        'category_slug' => $p['category_slug'] ?: '',
        'price' => (float) $finalPrice,
        'price_formatted' => formatPrice($finalPrice),
        'old_price_formatted' => $discount > 0 ? formatPrice($p['price']) : null,
        'discount_percent' => $discount,
        'in_stock' => $stock > 0,
        'stock' => $stock,
    ];
}

echo json_encode([
    'ok' => true,
    'enabled' => true,
    'query' => $query,
    'total' => $totalProducts,
    'categories' => $matchedCategories,
    'results' => $formattedResults,
], JSON_UNESCAPED_UNICODE);
