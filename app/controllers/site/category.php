<?php

$slug = $_GET['slug'] ?? '';

$stmt = db()->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    require __DIR__ . '/not_found.php';
    return;
}

$pageTitle = $category['name'];

// ---------- Filter & Sorting ----------
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

$where = 'category_id = ? AND is_active = 1';
$params = [$category['id']];
if ($onlyAvailable) {
    $where .= ' AND stock > 0';
}

$countStmt = db()->prepare("SELECT COUNT(*) FROM products WHERE $where");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

$stmt = db()->prepare("SELECT * FROM products WHERE $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

renderView('site/category', compact('pageTitle', 'category', 'slug', 'sort', 'onlyAvailable', 'products', 'totalPages', 'page'));
