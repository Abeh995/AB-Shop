<?php
/**
 * Storefront product search — used by the header search form.
 */

$query = trim($_GET['q'] ?? '');
$pageTitle = $query !== '' ? 'نتایج جستجو برای «' . $query . '»' : 'جستجو';
$metaDescription = 'جستجوی محصولات در ' . SITE_NAME;

$products = [];
$total = 0;
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;

if ($query !== '') {
    $stockSql = effectiveStockSqlFragment('p');
    $where = 'p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ?)';
    $params = ['%' . $query . '%', '%' . $query . '%'];

    $countStmt = db()->prepare("SELECT COUNT(*) FROM products p WHERE $where");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $offset = ($page - 1) * $perPage;
    $stmt = db()->prepare("SELECT p.*, $stockSql AS effective_stock FROM products p
                            WHERE $where ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
}

$totalPages = max(1, (int) ceil($total / $perPage));

renderView('site/search', compact('pageTitle', 'query', 'products', 'total', 'page', 'totalPages'));
