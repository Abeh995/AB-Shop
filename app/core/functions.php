<?php
/**
 * General-purpose helper functions.
 */

// Escape output to prevent XSS.
function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Format prices in toman with thousands separators and Persian numerals.
function formatPrice($amount): string
{
    $amount = (float) $amount;
    $formatted = number_format($amount, 0);
    return toPersianDigits($formatted) . ' ' . CURRENCY_LABEL;
}

// Convert Latin digits to Persian numerals for local display.
function toPersianDigits(string $str): string
{
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    return str_replace($en, $fa, $str);
}

// Generate a slug from Persian or English text.
function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/\s+/u', '-', $text);
    $text = preg_replace('/[^\p{L}\p{N}\-]/u', '', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-') ?: 'item-' . substr(md5(uniqid('', true)), 0, 8);
}

// Simple redirect helper.
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// Flash messages persisted across redirects through the session.
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// Generate a unique order code, e.g. SK-4F82A1.
function generateOrderCode(): string
{
    return 'SK-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

/**
 * Generate a unique SKU for a new product, e.g. SOCK-042817.
 * The database is checked before returning the value; on the unlikely chance of a collision,
 * the generator retries to guarantee uniqueness.
 */
function generateUniqueSku(): string
{
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $candidate = 'SOCK-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $stmt = db()->prepare("SELECT id FROM products WHERE sku = ?");
        $stmt->execute([$candidate]);
        if (!$stmt->fetch()) {
            return $candidate;
        }
    }
    // Very unlikely fallback: include microseconds to preserve uniqueness.
    return 'SOCK-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
}

// Calculate the effective product price after discounts.
function effectivePrice(array $product): float
{
    if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']) {
        return (float) $product['discount_price'];
    }
    return (float) $product['price'];
}

// Calculate the discount percentage for display.
function discountPercent(array $product): int
{
    if (!empty($product['discount_price']) && $product['discount_price'] < $product['price'] && $product['price'] > 0) {
        return (int) round((1 - ($product['discount_price'] / $product['price'])) * 100);
    }
    return 0;
}

// Basic validation for Iranian mobile numbers.
function isValidIranPhone(string $phone): bool
{
    return (bool) preg_match('/^09[0-9]{9}$/', $phone);
}

/**
 * Return categories in hierarchical order for admin <select> elements.
 * Each item includes a 'depth' value (0 = top-level category).
 */
function getCategoriesForDropdown(): array
{
    $all = db()->query("SELECT id, parent_id, name FROM categories ORDER BY parent_id IS NULL DESC, sort_order ASC")->fetchAll();

    $byParent = [];
    foreach ($all as $cat) {
        $byParent[$cat['parent_id'] ?? 0][] = $cat;
    }

    $result = [];
    $walk = function ($parentId, $depth) use (&$walk, &$byParent, &$result) {
        foreach ($byParent[$parentId] ?? [] as $cat) {
            $cat['depth'] = $depth;
            $result[] = $cat;
            $walk($cat['id'], $depth + 1);
        }
    };
    $walk(0, 0);

    return $result;
}

/**
 * Return a category ID together with the IDs of its direct children.
 * Used so parent category pages can include products from their immediate subcategories.
 */
function getCategoryAndChildIds(int $categoryId): array
{
    $ids = [$categoryId];
    $stmt = db()->prepare("SELECT id FROM categories WHERE parent_id = ? AND is_active = 1");
    $stmt->execute([$categoryId]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $childId) {
        $ids[] = (int) $childId;
    }
    return $ids;
}

/**
 * SQL fragment for calculating a product's effective stock in list queries (product cards).
 * Variant products use the sum of variant stock; products without variants use their stock column.
 * Fixes the previous behavior where variant products were incorrectly shown as out of stock
 * because p.stock is intentionally stored as zero for such products.
 */
function effectiveStockSqlFragment(string $productAlias = 'p'): string
{
    return "COALESCE((SELECT SUM(v.stock) FROM product_variants v WHERE v.product_id = $productAlias.id), $productAlias.stock)";
}

// ---------- Product tags ----------

// Load all tags used by the site, including those attached to inactive products, for admin forms.
function getAllTags(): array
{
    return db()->query("SELECT id, name, slug FROM tags ORDER BY name ASC")->fetchAll();
}

// Load tags assigned to a specific product.
function getProductTags(int $productId): array
{
    $stmt = db()->prepare("SELECT t.id, t.name, t.slug FROM tags t
                            JOIN product_tags pt ON pt.tag_id = t.id
                            WHERE pt.product_id = ? ORDER BY t.name ASC");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

/**
 * Synchronize a product's tags with a new selection and any newly entered tag names.
 * Missing tags are created automatically.
 * @param int[] $existingTagIds IDs of existing selected tags.
 * @param string $newTagsRaw Comma-separated new tag names, e.g. "cotton, autumn".
 */
function syncProductTags(int $productId, array $existingTagIds, string $newTagsRaw): void
{
    $tagIds = array_map('intval', $existingTagIds);

    foreach (explode(',', $newTagsRaw) as $rawName) {
        $name = trim($rawName);
        if ($name === '') continue;

        $slug = slugify($name);
        $stmt = db()->prepare("SELECT id FROM tags WHERE name = ? OR slug = ?");
        $stmt->execute([$name, $slug]);
        $existing = $stmt->fetch();

        if ($existing) {
            $tagIds[] = (int) $existing['id'];
        } else {
            $ins = db()->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
            $ins->execute([$name, $slug]);
            $tagIds[] = (int) db()->lastInsertId();
        }
    }

    $tagIds = array_unique(array_filter($tagIds));

    db()->prepare("DELETE FROM product_tags WHERE product_id = ?")->execute([$productId]);
    if ($tagIds) {
        $stmt = db()->prepare("INSERT IGNORE INTO product_tags (product_id, tag_id) VALUES (?, ?)");
        foreach ($tagIds as $tagId) {
            $stmt->execute([$productId, $tagId]);
        }
    }
}
