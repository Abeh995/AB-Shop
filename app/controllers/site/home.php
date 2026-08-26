<?php
/**
 * Home page controller — data fetching only; no HTML is rendered here.
 */

$pageTitle = 'خانه';

$categories = db()->query("SELECT id, name, slug, image FROM categories WHERE is_active = 1 AND parent_id IS NULL ORDER BY sort_order ASC")->fetchAll();

$featured = db()->query("
    SELECT * FROM products
    WHERE is_active = 1 AND is_featured = 1
    ORDER BY created_at DESC LIMIT 8
")->fetchAll();

$newest = db()->query("
    SELECT * FROM products
    WHERE is_active = 1
    ORDER BY created_at DESC LIMIT 8
")->fetchAll();

renderView('site/home', compact('pageTitle', 'categories', 'featured', 'newest'));
