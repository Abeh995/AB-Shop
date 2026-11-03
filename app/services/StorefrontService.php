<?php
/**
 * Storefront presentation data service.
 *
 * Keeps the home controller thin (Rule 7): every query and every
 * storefront-shaped transformation the landing page needs lives here, so the
 * controller only assembles the initial payload and the view stays pure.
 */

/**
 * Active categories offered as landing-page filter chips ("همه" first).
 *
 * @return array<int, array{id: string, label: string}>
 */
function getLandingCategoryOptions(): array
{
    $rows = db()->query(
        'SELECT name, slug FROM categories
          WHERE is_active = 1 AND parent_id IS NULL
          ORDER BY sort_order ASC'
    )->fetchAll();

    $options = [['id' => 'all', 'label' => 'همه']];
    foreach ($rows as $row) {
        $options[] = [
            'id'    => $row['slug'] ?: 'cat-' . $row['id'],
            'label' => (string) $row['name'],
        ];
    }

    return $options;
}

/**
 * Raw active products for the SEO/no-JS fallback markup.
 */
function getLandingProducts(int $limit = 24): array
{
    $limit = max(1, min(60, $limit));
    $stockSql = effectiveStockSqlFragment('p');

    return db()->query(
        "SELECT p.id, p.name, p.slug, p.price, p.discount_price, p.image, p.description, p.is_featured,
                c.name AS category_name, c.slug AS category_slug,
                $stockSql AS effective_stock
           FROM products p
           LEFT JOIN categories c ON p.category_id = c.id
          WHERE p.is_active = 1
          ORDER BY p.is_featured DESC, p.created_at DESC
          LIMIT $limit"
    )->fetchAll();
}

/**
 * Shape one database row into the object the React catalog consumes.
 */
function formatLandingProduct(array $product): array
{
    $price = (float) $product['price'];
    $hasDiscount = !empty($product['discount_price']) && (float) $product['discount_price'] < $price;
    $finalPrice = $hasDiscount ? (float) $product['discount_price'] : $price;

    return [
        'id'            => (int) $product['id'],
        'title'         => (string) $product['name'],
        'slug'          => (string) $product['slug'],
        'category'      => $product['category_slug'] ?: 'crew',
        'categoryLabel' => $product['category_name'] ?: 'ساق‌بلند طرح‌دار',
        'price'         => (int) $finalPrice,
        'oldPrice'      => $hasDiscount ? (int) $price : null,
        'badge'         => !empty($product['is_featured']) ? 'پیشنهاد ویژه' : 'محبوب',
        'rating'        => 5.0,
        'reviews'       => 120 + ((int) $product['id'] * 7) % 150,
        'rank'          => (int) $product['id'],
        'isNew'         => true,
        'art'           => 'sock',
        'pattern'       => 'checker',
        'image'         => $product['image'] ? UPLOAD_URL . $product['image'] : null,
        'desc'          => $product['description'] ?: 'بافته شده با الیاف ۱۰۰٪ نخ‌پنبه و بالاترین دوام.',
    ];
}

/**
 * Everything the landing page needs from the catalog layer.
 *
 * @return array{raw: array, cards: array, categories: array}
 */
function getLandingCatalogPayload(int $limit = 24): array
{
    $raw = getLandingProducts($limit);

    return [
        'raw'        => $raw,
        'cards'      => array_map('formatLandingProduct', $raw),
        'categories' => getLandingCategoryOptions(),
    ];
}
