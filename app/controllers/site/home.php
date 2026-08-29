<?php
/**
 * Home page controller; data fetching only. HTML belongs in the view layer.
 */

$pageTitle = 'خانه';

$categories = db()->query("SELECT id, name, slug, image FROM categories WHERE is_active = 1 AND parent_id IS NULL ORDER BY sort_order ASC")->fetchAll();

$stockSql = effectiveStockSqlFragment('products');

$featured = db()->query("
    SELECT *, $stockSql AS effective_stock FROM products
    WHERE is_active = 1 AND is_featured = 1
    ORDER BY created_at DESC LIMIT 8
")->fetchAll();

$newest = db()->query("
    SELECT *, $stockSql AS effective_stock FROM products
    WHERE is_active = 1
    ORDER BY created_at DESC LIMIT 8
")->fetchAll();

renderView('site/home', compact('pageTitle', 'categories', 'featured', 'newest'));
