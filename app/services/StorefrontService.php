<?php
/**
 * Storefront presentation data service.
 *
 * Keeps site controllers thin (Rule 7): storefront queries live here so
 * controllers only assemble view data and templates remain pure presentation.
 */

/**
 * Active top-level categories for storefront home grid and category strip.
 *
 * @return array<int, array<string, mixed>>
 */
function getHomeCategories(): array
{
    return db()->query(
        'SELECT id, name, slug, image
           FROM categories
          WHERE is_active = 1 AND parent_id IS NULL
          ORDER BY sort_order ASC'
    )->fetchAll();
}

/**
 * Active featured products with effective stock for the homepage carousel.
 *
 * @return array<int, array<string, mixed>>
 */
function getHomeFeaturedProducts(int $limit = 6): array
{
    $limit = max(2, min(24, $limit));
    $stockSql = effectiveStockSqlFragment('products');

    $stmt = db()->prepare(
        "SELECT *, $stockSql AS effective_stock
           FROM products
          WHERE is_active = 1 AND is_featured = 1
          ORDER BY created_at DESC
          LIMIT ?"
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Active newest products with effective stock for the homepage carousel.
 *
 * @return array<int, array<string, mixed>>
 */
function getHomeNewestProducts(int $limit = 6): array
{
    $limit = max(2, min(24, $limit));
    $stockSql = effectiveStockSqlFragment('products');

    $stmt = db()->prepare(
        "SELECT *, $stockSql AS effective_stock
           FROM products
          WHERE is_active = 1
          ORDER BY created_at DESC
          LIMIT ?"
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}
