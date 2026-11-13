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
function formatLandingProduct(array $product, array $variants = []): array
{
    $price = (float) $product['price'];
    $hasDiscount = !empty($product['discount_price']) && (float) $product['discount_price'] < $price;
    $finalPrice = $hasDiscount ? (float) $product['discount_price'] : $price;

    $colors = [];
    $sizes = [];
    $formattedVariants = [];

    $colorMap = [
        'نارنجی' => '#e85d1f',
        'مشکی'   => '#221510',
        'سفید'   => '#f6efe0',
        'طوسی'   => '#71717a',
        'خاکستری'=> '#71717a',
        'سرمه‌ای' => '#1e3a8a',
        'آبی'    => '#0284c7',
        'قرمز'   => '#dc2626',
        'سبز'    => '#16a34a',
        'زرد'    => '#eab308',
        'کرم'    => '#f5ebe0',
        'قهوه‌ای'=> '#582b1c',
        'بنفش'   => '#7c3aed',
        'صورتی'  => '#db2777',
    ];

    $seenColors = [];
    foreach ($variants as $v) {
        $cName = trim((string)($v['color'] ?? ''));
        if ($cName !== '' && !isset($seenColors[$cName])) {
            $seenColors[$cName] = true;
            $hex = '#e85d1f';
            foreach ($colorMap as $namePart => $code) {
                if (str_contains($cName, $namePart)) {
                    $hex = $code;
                    break;
                }
            }
            $colors[] = ['name' => $cName, 'hex' => $hex, 'secondary' => '#f6efe0'];
        }

        $sName = trim((string)($v['size'] ?? ''));
        if ($sName !== '' && !in_array($sName, $sizes, true)) {
            $sizes[] = $sName;
        }

        $formattedVariants[] = [
            'id'       => (int) $v['id'],
            'size'     => $sName,
            'color'    => $cName,
            'stock'    => (int) $v['stock'],
            'price'    => $v['price_override'] !== null ? (int)$v['price_override'] : (int)$finalPrice,
        ];
    }

    if (empty($colors)) {
        $colors = [['name' => 'پیش‌فرض', 'hex' => '#e85d1f', 'secondary' => '#f6efe0']];
    }
    if (empty($sizes)) {
        $sizes = ['استاندارد'];
    }

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
        'variants'      => $formattedVariants,
        'colors'        => $colors,
        'sizes'         => $sizes,
    ];
}

/**
 * Active items currently inside session or logged-in cart.
 *
 * @return array<int, array>
 */
function getLandingCartPayload(): array
{
    $cart = cartDetails();
    $items = [];
    foreach ($cart['items'] as $item) {
        $p = $item['product'];
        $v = $item['variant'];
        $items[] = [
            'key'        => (string) $item['key'],
            'productId'  => (int) $p['id'],
            'variantId'  => $v ? (int) $v['id'] : null,
            'name'       => (string) $p['name'],
            'price'      => (int) $item['unit_price'],
            'qty'        => (int) $item['qty'],
            'color'      => $v ? (string) ($v['color'] ?? '') : '',
            'size'       => $v ? (string) ($v['size'] ?? '') : '',
            'image'      => $p['image'] ? UPLOAD_URL . $p['image'] : null,
        ];
    }
    return $items;
}

/**
 * Everything the landing page needs from the catalog layer.
 *
 * @return array{raw: array, cards: array, categories: array, cartItems: array}
 */
function getLandingCatalogPayload(int $limit = 24): array
{
    $raw = getLandingProducts($limit);
    $cards = [];

    if (!empty($raw)) {
        $pids = array_map(fn($p) => (int)$p['id'], $raw);
        $inClause = implode(',', $pids);

        $vRows = db()->query(
            "SELECT id, product_id, size, color, stock, price_override 
               FROM product_variants 
              WHERE product_id IN ($inClause)
              ORDER BY id ASC"
        )->fetchAll();

        $variantsByProduct = [];
        foreach ($vRows as $v) {
            $pid = (int) $v['product_id'];
            $variantsByProduct[$pid][] = $v;
        }

        foreach ($raw as $p) {
            $pid = (int) $p['id'];
            $cards[] = formatLandingProduct($p, $variantsByProduct[$pid] ?? []);
        }
    }

    return [
        'raw'        => $raw,
        'cards'      => $cards,
        'categories' => getLandingCategoryOptions(),
        'cartItems'  => getLandingCartPayload(),
    ];
}
