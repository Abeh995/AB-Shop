<?php
/**
 * Controller for the product details page.
 */

$slug = $_GET['slug'] ?? '';

$stmt = db()->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                        FROM products p JOIN categories c ON c.id = p.category_id
                        WHERE p.slug = ? AND p.is_active = 1 LIMIT 1");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    require __DIR__ . '/not_found.php';
    return;
}

$pageTitle = $product['name'];

$imgStmt = db()->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$imgStmt->execute([$product['id']]);
$gallery = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
$mainImage = $product['image'] ? UPLOAD_URL . $product['image'] : '/assets/img/placeholder-sock.svg';
if (empty($gallery) && $product['image']) $gallery = [$product['image']];

$varStmt = db()->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
$varStmt->execute([$product['id']]);
$variants = $varStmt->fetchAll();

$hasVariants = count($variants) > 0;
$totalStock = $hasVariants ? array_sum(array_column($variants, 'stock')) : (int) $product['stock'];

$discount = discountPercent($product);
$finalPrice = effectivePrice($product);

renderView('site/product', compact(
    'pageTitle', 'product', 'gallery', 'mainImage', 'variants', 'hasVariants', 'totalStock', 'discount', 'finalPrice'
));
