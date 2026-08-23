<?php

$pageTitle = 'خانه';

$categories = db()->query("SELECT id, name, slug, image FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

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
