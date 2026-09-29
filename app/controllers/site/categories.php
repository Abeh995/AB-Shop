<?php
/**
 * Categories Hub controller — lists all parent categories with their subcategories and stats.
 */

$pageTitle = 'دسته‌بندی محصولات';
$metaDescription = 'مشاهده تمامی دسته‌بندی‌های جوراب و پاپوش در ' . SITE_NAME;

$parentsStmt = db()->query("
    SELECT c.id, c.name, c.slug, c.description, c.image,
           (SELECT COUNT(*) FROM products p WHERE p.category_id IN (
               SELECT id FROM categories sub WHERE sub.id = c.id OR sub.parent_id = c.id
           ) AND p.is_active = 1) AS product_count
    FROM categories c
    WHERE c.is_active = 1 AND c.parent_id IS NULL
    ORDER BY c.sort_order ASC
");
$categories = $parentsStmt->fetchAll();

$childStmt = db()->query("
    SELECT c.id, c.parent_id, c.name, c.slug,
           (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS product_count
    FROM categories c
    WHERE c.is_active = 1 AND c.parent_id IS NOT NULL
    ORDER BY c.sort_order ASC
");
$allChildren = $childStmt->fetchAll();

$childrenByParent = [];
foreach ($allChildren as $child) {
    $childrenByParent[(int) $child['parent_id']][] = $child;
}

renderView('site/categories', compact('pageTitle', 'metaDescription', 'categories', 'childrenByParent'));
