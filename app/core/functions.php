<?php
/**
 * General helper functions.
 */

// Escape output to prevent XSS.
function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Format prices in Toman with thousands separators and Persian numerals.
function formatPrice($amount): string
{
    $amount = (float) $amount;
    $formatted = number_format($amount, 0);
    return toPersianDigits($formatted) . ' ' . CURRENCY_LABEL;
}

// Convert Latin digits to Persian numerals for localized display.
function toPersianDigits(string $str): string
{
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    return str_replace($en, $fa, $str);
}

// Generate a slug from Persian or Latin text.
function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/\s+/u', '-', $text);
    $text = preg_replace('/[^\p{L}\p{N}\-]/u', '', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-') ?: 'item-' . substr(md5(uniqid('', true)), 0, 8);
}

// Perform a simple redirect.
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// Flash messages persisted across redirects using the session.
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
 * Check the database before returning to ensure the SKU has not already been used.
 * The collision probability is low, but multiple attempts are made as a final safeguard.
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
    // Extremely unlikely fallback: add microseconds as an extra uniqueness component.
    return 'SOCK-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
}

// Get the effective product price, including discounts.
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
 * Build a hierarchical category list (parent categories followed by indented subcategories).
 * Used by the admin <select>. Each item includes a 'depth' field (0 = top-level category).
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
 * This allows a parent category page to include products from its subcategories.
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
