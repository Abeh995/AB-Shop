<?php
/**
 * One-time migration: rename all existing uploaded images to the
 * standardized naming convention (product-{ID}-main-{hash4}.{ext}).
 *
 * Access:  /admin/migrate_image_names.php  (super_admin only)
 * After:   Delete this file from the server once the migration is confirmed.
 *
 * Safe to re-run: files that already match the standard pattern are skipped.
 */

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/core/auth.php';

requireAdmin();
if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
    http_response_code(403);
    echo 'Access denied – super_admin only.';
    exit;
}

// Standard-name detection regex: entityType-id-role-hash4.ext  or  entityType-role-hash4.ext
$standardPattern = '/^(product|giftitem|logo)-(\d+-)?[a-z0-9]+-[a-f0-9]{4}\.\w+$/';

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html dir="rtl" lang="fa"><head><meta charset="utf-8"><title>Image Name Migration</title>';
echo '<style>body{font-family:Tahoma,sans-serif;margin:2em;direction:ltr;text-align:left}';
echo '.ok{color:green}.skip{color:#999}.err{color:red}pre{background:#f5f5f5;padding:1em;border-radius:8px;overflow-x:auto}</style></head><body>';
echo '<h1>Image Name Migration — FEAT-A001</h1><pre>';

$totalRenamed = 0;
$totalSkipped = 0;
$totalErrors = 0;

// ──────────────────────────────────────────────────────────────
// 1. Product main images (products.image → UPLOAD_DIR)
// ──────────────────────────────────────────────────────────────
echo "\n=== Product main images ===\n";
$rows = db()->query("SELECT id, image FROM products WHERE image IS NOT NULL AND image != ''")->fetchAll();
foreach ($rows as $row) {
    $old = $row['image'];
    if (preg_match($standardPattern, $old)) {
        echo "<span class='skip'>SKIP  product #{$row['id']}  {$old}  (already standard)</span>\n";
        $totalSkipped++;
        continue;
    }
    $renamed = renameUploadedImage($old, 'product', (int) $row['id'], 'main');
    if ($renamed) {
        db()->prepare("UPDATE products SET image = ? WHERE id = ?")->execute([$renamed, $row['id']]);
        echo "<span class='ok'>OK    product #{$row['id']}  {$old} → {$renamed}</span>\n";
        $totalRenamed++;
    } else {
        echo "<span class='err'>ERR   product #{$row['id']}  {$old}  (file not found or rename failed)</span>\n";
        $totalErrors++;
    }
}

// ──────────────────────────────────────────────────────────────
// 2. Product gallery images (product_images.image_path → UPLOAD_DIR)
// ──────────────────────────────────────────────────────────────
echo "\n=== Product gallery images ===\n";
$rows = db()->query("SELECT pi.id, pi.product_id, pi.image_path, pi.sort_order FROM product_images pi ORDER BY pi.product_id, pi.sort_order")->fetchAll();
foreach ($rows as $row) {
    $old = $row['image_path'];
    if (preg_match($standardPattern, $old)) {
        echo "<span class='skip'>SKIP  gallery #{$row['id']} (product #{$row['product_id']})  {$old}  (already standard)</span>\n";
        $totalSkipped++;
        continue;
    }
    $role = 'g' . (int) $row['sort_order'];
    $renamed = renameUploadedImage($old, 'product', (int) $row['product_id'], $role);
    if ($renamed) {
        db()->prepare("UPDATE product_images SET image_path = ? WHERE id = ?")->execute([$renamed, $row['id']]);
        echo "<span class='ok'>OK    gallery #{$row['id']} (product #{$row['product_id']})  {$old} → {$renamed}</span>\n";
        $totalRenamed++;
    } else {
        echo "<span class='err'>ERR   gallery #{$row['id']} (product #{$row['product_id']})  {$old}  (file not found or rename failed)</span>\n";
        $totalErrors++;
    }
}

// ──────────────────────────────────────────────────────────────
// 3. Gift item images (gift_items.image → UPLOAD_DIR)
// ──────────────────────────────────────────────────────────────
echo "\n=== Gift item images ===\n";
$rows = db()->query("SELECT id, image FROM gift_items WHERE image IS NOT NULL AND image != ''")->fetchAll();
foreach ($rows as $row) {
    $old = $row['image'];
    if (preg_match($standardPattern, $old)) {
        echo "<span class='skip'>SKIP  giftitem #{$row['id']}  {$old}  (already standard)</span>\n";
        $totalSkipped++;
        continue;
    }
    $renamed = renameUploadedImage($old, 'giftitem', (int) $row['id'], 'main');
    if ($renamed) {
        db()->prepare("UPDATE gift_items SET image = ? WHERE id = ?")->execute([$renamed, $row['id']]);
        echo "<span class='ok'>OK    giftitem #{$row['id']}  {$old} → {$renamed}</span>\n";
        $totalRenamed++;
    } else {
        echo "<span class='err'>ERR   giftitem #{$row['id']}  {$old}  (file not found or rename failed)</span>\n";
        $totalErrors++;
    }
}

// ──────────────────────────────────────────────────────────────
// 4. Site logo (settings key 'site_logo' → BRANDING_UPLOAD_DIR)
// ──────────────────────────────────────────────────────────────
echo "\n=== Site logo ===\n";
$logoFile = getSetting('site_logo', '');
if ($logoFile !== '') {
    if (preg_match($standardPattern, $logoFile)) {
        echo "<span class='skip'>SKIP  logo  {$logoFile}  (already standard)</span>\n";
        $totalSkipped++;
    } else {
        $oldPath = BRANDING_UPLOAD_DIR . $logoFile;
        if (file_exists($oldPath)) {
            $ext = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
            $newName = generateStandardFilename('logo', 0, 'site', $ext);
            $newPath = BRANDING_UPLOAD_DIR . $newName;
            if (rename($oldPath, $newPath)) {
                setSetting('site_logo', $newName);
                echo "<span class='ok'>OK    logo  {$logoFile} → {$newName}</span>\n";
                $totalRenamed++;
            } else {
                echo "<span class='err'>ERR   logo  {$logoFile}  (rename failed)</span>\n";
                $totalErrors++;
            }
        } else {
            echo "<span class='err'>ERR   logo  {$logoFile}  (file not found at {$oldPath})</span>\n";
            $totalErrors++;
        }
    }
} else {
    echo "<span class='skip'>SKIP  no logo configured</span>\n";
}

// ──────────────────────────────────────────────────────────────
// Summary
// ──────────────────────────────────────────────────────────────
echo "\n============================\n";
echo "Renamed: {$totalRenamed}  |  Skipped: {$totalSkipped}  |  Errors: {$totalErrors}\n";
echo "</pre>";

if ($totalErrors === 0) {
    echo '<p style="color:green;font-weight:bold">✅ Migration completed successfully. You can delete this file from the server.</p>';
} else {
    echo '<p style="color:red;font-weight:bold">⚠️ Some files could not be renamed. Check the errors above — the most common cause is a file that was already deleted from disk but still referenced in the database.</p>';
}

echo '</body></html>';
