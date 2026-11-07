<?php
/**
 * Product detail page controller
 */
$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE p.slug = ? AND p.is_active = 1 LIMIT 1");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    require __DIR__ . '/not_found.php';
    return;
}

$pageTitle = !empty($product['seo_title']) ? $product['seo_title'] : $product['name'];
$canonicalUrl = !empty($product['canonical_url']) ? $product['canonical_url'] : (rtrim(SITE_URL, '/') . '/product/' . $product['slug']);

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

$useGlobalStrategy = (int) ($product['use_global_variant_strategy'] ?? 1) === 1;
$selectedVariant = $hasVariants ? resolveDefaultProductVariant($variants, $useGlobalStrategy) : null;
$defaultVariantId = $selectedVariant ? (int) $selectedVariant['id'] : null;
$selectedVariantLabel = $selectedVariant ? (trim(($selectedVariant['size'] ?? '') . ' ' . ($selectedVariant['color'] ?? '')) ?: 'استاندارد') : '';
$initialVariantStock = $selectedVariant ? (int)$selectedVariant['stock'] : $totalStock;
$initialMaxQty = min(max(1, $initialVariantStock), 20);
$initialPrice = ($selectedVariant && $selectedVariant['price_override'] !== null) ? (float)$selectedVariant['price_override'] : (float)$finalPrice;

$tags = (getSetting('show_product_tags', '1') === '1') ? getProductTags($product['id']) : [];
$metaDescription = !empty($product['seo_description']) 
    ? $product['seo_description'] 
    : ($product['description'] ? seoCleanText($product['description'], 160) : ($product['name'] . ' — ' . SITE_NAME));
$ogImage = rtrim(SITE_URL, '/') . $mainImage;

// Structured Data: BreadcrumbList + Full Merchant Product Schema + FAQ Schema
$materialSpecs = seoParseJsonList($product['material_specs'] ?? null);
$breadcrumbs = [
    ['name' => 'خانه', 'url' => '/'],
    ['name' => $product['category_name'], 'url' => '/category/' . $product['category_slug']],
    ['name' => $product['name'], 'url' => '/product/' . $product['slug']],
];
$productFaq = seoBuildProductFaqJsonLd($product, $materialSpecs);
$jsonLd = [
    seoBuildProductJsonLd($product, $variants, $finalPrice, $totalStock),
    seoBuildBreadcrumbsJsonLd($breadcrumbs),
];
if ($productFaq) {
    $jsonLd[] = $productFaq;
}

$preloadHeroImage = $mainImage;

renderView('site/product', compact(
    'pageTitle', 'product', 'gallery', 'mainImage', 'variants', 'hasVariants', 'totalStock', 'discount', 'finalPrice', 'tags',
    'defaultVariantId', 'selectedVariant', 'selectedVariantLabel', 'initialVariantStock', 'initialMaxQty', 'initialPrice',
    'metaDescription', 'ogImage', 'canonicalUrl', 'breadcrumbs', 'materialSpecs', 'jsonLd', 'preloadHeroImage', 'productFaq'
));
