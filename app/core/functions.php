<?php
/**
 * General-purpose helper functions
 */

// Escape output to prevent XSS
function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Format a price in Toman with thousands separators and Persian digits
function formatPrice($amount): string
{
    $amount = (float) $amount;
    $formatted = number_format($amount, 0);
    return toPersianDigits($formatted) . ' ' . CURRENCY_LABEL;
}

// Convert Latin digits to Persian digits for a more native display
function toPersianDigits(string $str): string
{
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    return str_replace($en, $fa, $str);
}

// Build a URL slug from a Persian/English string
function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/\s+/u', '-', $text);
    $text = preg_replace('/[^\p{L}\p{N}\-]/u', '', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-') ?: 'item-' . substr(md5(uniqid('', true)), 0, 8);
}

// Simple redirect helper
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// Session-based flash messages (persist across a redirect)
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

// Generate a unique order code, e.g. SK-4F82A1
function generateOrderCode(): string
{
    return 'SK-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

/**
 * Generate a unique SKU for a new product, e.g. SOCK-042817.
 * Before returning, always checks the database to make sure the code hasn't
 * been used already (collisions are extremely unlikely, but it retries a
 * few times just to be safe).
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
    // Extremely unlikely fallback: mix in microtime uniqueness for a guaranteed-unique value
    return 'SOCK-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
}

// Effective product price (accounting for a discount)
function effectivePrice(array $product): float
{
    if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']) {
        return (float) $product['discount_price'];
    }
    return (float) $product['price'];
}

// Discount percentage, for the badge shown on product cards
function discountPercent(array $product): int
{
    if (!empty($product['discount_price']) && $product['discount_price'] < $product['price'] && $product['price'] > 0) {
        return (int) round((1 - ($product['discount_price'] / $product['price'])) * 100);
    }
    return 0;
}

// Simple validation for an Iranian mobile number
function isValidIranPhone(string $phone): bool
{
    return (bool) preg_match('/^09[0-9]{9}$/', $phone);
}

/**
 * Categories as a hierarchical list (a parent category followed by its indented
 * children) for use in the admin panel's <select>. Each item has a 'depth'
 * field (0 = top-level category).
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
 * A category's id plus the ids of all its direct children (one level deep).
 * Lets a parent category page also show products from its subcategories.
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
 * SQL fragment that computes a product's "effective stock" for listing
 * queries (product cards): the sum of its variants' stock when it has
 * variants, otherwise its own stock column.
 * Bug fix: product cards used to read only p.stock (which is always stored
 * as 0 for products with variants, per the 1.2.0 design) and so incorrectly
 * showed every product with variants as "out of stock", even when its
 * variants had stock.
 */
function effectiveStockSqlFragment(string $productAlias = 'p'): string
{
    return "COALESCE((SELECT SUM(v.stock) FROM product_variants v WHERE v.product_id = $productAlias.id), $productAlias.stock)";
}

/**
 * URL of the uploaded site logo, or null when no logo has been uploaded yet
 * (or the stored file is missing), so callers can fall back to the text logo.
 */
function siteLogoUrl(): ?string
{
    $filename = getSetting('site_logo', '');
    if (!$filename || !file_exists(BRANDING_UPLOAD_DIR . $filename)) {
        return null;
    }
    return BRANDING_UPLOAD_URL . $filename;
}

/**
 * Build a standardized filename for an uploaded asset.
 *
 * Pattern: {entityType}-{entityId}-{role}-{hash4}.{ext}
 * Examples: product-5-main-a3f4.jpg, giftitem-3-main-d4e5.webp
 *
 * The 4-char random suffix acts as a cache-buster so browsers fetch the new
 * version when an image is replaced without changing the URL structure.
 *
 * @param string $entityType  e.g. 'product', 'giftitem', 'logo'
 * @param int    $entityId    Row id (0 for singletons like the site logo)
 * @param string $role        e.g. 'main', 'g1', 'g2', 'site'
 * @param string $ext         File extension without the dot, e.g. 'jpg'
 */
function generateStandardFilename(string $entityType, int $entityId, string $role, string $ext): string
{
    $hash = substr(bin2hex(random_bytes(2)), 0, 4);
    if ($entityId > 0) {
        return "{$entityType}-{$entityId}-{$role}-{$hash}.{$ext}";
    }
    // Singleton assets (e.g. logo) omit the id segment
    return "{$entityType}-{$role}-{$hash}.{$ext}";
}

/**
 * Rename an already-uploaded file from its temporary random name to a
 * standardized name.  Used after INSERT when the entity id is first known.
 *
 * @return string|null The new filename on success, null on failure.
 */
function renameUploadedImage(string $oldFilename, string $entityType, int $entityId, string $role): ?string
{
    $oldPath = UPLOAD_DIR . $oldFilename;
    if (!file_exists($oldPath)) {
        return null;
    }
    $ext = strtolower(pathinfo($oldFilename, PATHINFO_EXTENSION));
    $newFilename = generateStandardFilename($entityType, $entityId, $role, $ext);
    $newPath = UPLOAD_DIR . $newFilename;

    if (rename($oldPath, $newPath)) {
        return $newFilename;
    }
    return null;
}

/**
 * Validate and safely store an uploaded product/gift-item image.
 * Shared by app/controllers/admin/product_edit.php and gift_item_edit.php.
 *
 * When $entityType and $entityId are provided the file receives a
 * standardized, human-readable name (e.g. product-5-main-a3f4.jpg).
 * Without them a temporary random name is generated; the caller should
 * call renameUploadedImage() once the entity id is known.
 *
 * @param string $entityType  Optional, e.g. 'product', 'giftitem'
 * @param int    $entityId    Optional, the entity's primary key
 * @param string $role        Optional, e.g. 'main', 'g1'
 */
function handleProductImageUpload(array $file, string $entityType = '', int $entityId = 0, string $role = 'main'): array
{
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['ok' => false, 'error' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.'];
    }

    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowedMimes[$mime])) {
        return ['ok' => false, 'error' => 'فقط تصاویر JPG، PNG یا WEBP مجاز هستند.'];
    }

    // Integrity check: verify that the file is actually a readable, valid image
    $imgSize = @getimagesize($file['tmp_name']);
    if ($imgSize === false) {
        return ['ok' => false, 'error' => 'فایل انتخابی یک تصویر معتبر نیست.'];
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if ($entityType !== '' && $entityId > 0) {
        $filename = generateStandardFilename($entityType, $entityId, $role, $allowedMimes[$mime]);
    } else {
        // Temporary random name — caller must rename after obtaining the id
        $filename = bin2hex(random_bytes(12)) . '.' . $allowedMimes[$mime];
    }
    $destination = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['ok' => false, 'error' => 'خطا در ذخیره فایل روی سرور.'];
    }

    // Defense-in-depth: if JPEG file still contains EXIF/GPS metadata, re-save to strip it
    if ($mime === 'image/jpeg' && function_exists('exif_read_data') && function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
        $exif = @exif_read_data($destination);
        if ($exif && (!empty($exif['GPS']) || !empty($exif['Make']) || !empty($exif['Model']))) {
            $gd = @imagecreatefromjpeg($destination);
            if ($gd) {
                imagejpeg($gd, $destination, 90);
                imagedestroy($gd);
            }
        }
    }

    return ['ok' => true, 'filename' => $filename];
}


// ---------- Product tags ----------

// All tags on the site (across all products, active or not) — for the admin form's checkboxes
function getAllTags(): array
{
    return db()->query("SELECT id, name, slug FROM tags ORDER BY name ASC")->fetchAll();
}

// Tags belonging to a specific product
function getProductTags(int $productId): array
{
    $stmt = db()->prepare("SELECT t.id, t.name, t.slug FROM tags t
                            JOIN product_tags pt ON pt.tag_id = t.id
                            WHERE pt.product_id = ? ORDER BY t.name ASC");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

/**
 * Sync a product's tags with a new list (checked existing tags + any new
 * tags the admin typed in as free text). New tags are created if missing.
 * @param int[] $existingTagIds Ids of the existing tags that were checked
 * @param string $newTagsRaw Comma-separated string of new tags, e.g. "نخی, پاییزه"
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

// ---------- Storefront theme system ----------

/**
 * Canonical set of color tokens a theme must define, with a human label and
 * a safe fallback value. Used both to render the theme editor (so every
 * field always appears, even for a theme missing a token) and as the last
 * line of defense if a token is somehow absent from the database.
 */
function defaultThemeColorTokens(): array
{
    return [
        'bg'            => ['label' => 'پس‌زمینه صفحه', 'default' => '#F9E9DA'],
        'surface'       => ['label' => 'کارت‌ها و بخش‌ها', 'default' => '#F5E5D6'],
        'text'          => ['label' => 'متن اصلی', 'default' => '#7D5141'],
        'muted'         => ['label' => 'متن کم‌رنگ', 'default' => '#9C7C6C'],
        'border'        => ['label' => 'خط دور کادرها', 'default' => '#E7D2BF'],
        'primary'       => ['label' => 'دکمه‌ها / CTA', 'default' => '#582B1C'],
        'primary-dark'  => ['label' => 'هاور دکمه / جزئیات تیره', 'default' => '#3E1D12'],
        'primary-light' => ['label' => 'بج و هاورِ روشن', 'default' => '#EAD6C7'],
        'accent'        => ['label' => 'جزئیات ثانویه', 'default' => '#B89180'],
    ];
}

/**
 * The currently active theme, or null if the theme tables don't exist yet
 * (migration not run) or no theme is marked active.
 */
function getActiveTheme(): ?array
{
    try {
        $stmt = db()->query("SELECT * FROM themes WHERE is_active = 1 LIMIT 1");
        $theme = $stmt->fetch();
        return $theme ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * A theme's tokens as a flat [token_key => token_value] map.
 */
function getThemeTokens(int $themeId, string $group = 'color'): array
{
    $stmt = db()->prepare("SELECT token_key, token_value FROM theme_tokens WHERE theme_id = ? AND token_group = ?");
    $stmt->execute([$themeId, $group]);
    $tokens = [];
    foreach ($stmt->fetchAll() as $row) {
        $tokens[$row['token_key']] = $row['token_value'];
    }
    return $tokens;
}

/**
 * A `:root{...}` CSS block overriding the default palette in
 * assets/css/style.css with the active theme's tokens, for injection into
 * <head> right after the main stylesheet. Returns an empty string (never
 * throws) if the theme system isn't set up yet, so the page always falls
 * back cleanly to the hardcoded defaults already in style.css.
 */
function activeThemeCssVars(): string
{
    $theme = getActiveTheme();
    if (!$theme) {
        return '';
    }

    $tokens = getThemeTokens((int) $theme['id']);
    if (!$tokens) {
        return '';
    }

    $css = ':root{';
    foreach ($tokens as $key => $value) {
        $css .= '--color-' . preg_replace('/[^a-z0-9\-]/', '', $key) . ':' . preg_replace('/[^#a-zA-Z0-9(),.%\s]/', '', $value) . ';';
    }
    $css .= '}';
    return $css;
}

/**
 * Atomically make one theme the active one (clears every other theme's
 * is_active flag inside the same transaction, so exactly one row is ever
 * active even if two admins act at the same time).
 */
function setActiveTheme(int $themeId): void
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->exec("UPDATE themes SET is_active = 0");
        $stmt = $pdo->prepare("UPDATE themes SET is_active = 1 WHERE id = ?");
        $stmt->execute([$themeId]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
