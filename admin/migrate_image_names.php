<?php
/**
 * One-time / on-demand migration & batch compressor:
 * 1. Standardizes all existing filenames ({entity}-{id}-{role}-{hash4}.webp).
 * 2. Compresses all non-WebP or oversize images to WebP (quality 82, max 1600px).
 * 3. Safely cleans up memory and old files on shared PHP hosting.
 *
 * Access:  /admin/migrate_image_names.php  (super_admin only)
 * Safe to re-run: Images already optimized as standard WebP are safely skipped.
 */

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/core/auth.php';

requireAdmin();
if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
    http_response_code(403);
    echo 'دسترسی غیرمجاز – فقط مدیر ارشد (super_admin) مجاز به اجرای این اسکریپت است.';
    exit;
}

// Ensure sufficient resources for shared hosting
@ini_set('memory_limit', '256M');
@set_time_limit(300);

// Disable buffering so results stream live to the browser
if (ob_get_level()) {
    @ob_end_flush();
}
ob_implicit_flush(true);

header('Content-Type: text/html; charset=utf-8');

/**
 * Format bytes to readable human string
 */
function formatSizeUnits(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' مگابایت';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' کیلوبایت';
    }
    return $bytes . ' بایت';
}

/**
 * Safely compress and convert an image to WebP with shared-hosting memory safeguards.
 *
 * @param string $sourceDir Directory containing the file (e.g. UPLOAD_DIR)
 * @param string $currentFilename Current filename in database
 * @param string $entityType 'product', 'giftitem', or 'logo'
 * @param int $entityId Primary key
 * @param string $role 'main', 'g1', 'site', etc.
 * @param int $maxDim Maximum width/height dimension (default 1600px)
 * @param int $quality WebP lossy quality (default 82)
 * @return array Status and metrics
 */
function processAndOptimizeImage(
    string $sourceDir,
    string $currentFilename,
    string $entityType,
    int $entityId,
    string $role,
    int $maxDim = 1600,
    int $quality = 82
): array {
    $sourcePath = $sourceDir . $currentFilename;
    if (!file_exists($sourcePath)) {
        return ['status' => 'error', 'message' => "فایل فیزیکی در مسیر {$sourcePath} یافت نشد."];
    }

    $ext = strtolower(pathinfo($currentFilename, PATHINFO_EXTENSION));

    // Vector SVG: Never convert to WebP, preserve vector fidelity
    if ($ext === 'svg') {
        $standardPattern = '/^logo-site-[a-f0-9]{4}\.svg$/';
        if (preg_match($standardPattern, $currentFilename)) {
            return ['status' => 'skip', 'message' => 'فایل وکتور SVG استاندارد است (بدون نیاز به تغییر).'];
        }
        $newFilename = generateStandardFilename($entityType, $entityId, $role, 'svg');
        $finalPath = $sourceDir . $newFilename;
        if (rename($sourcePath, $finalPath)) {
            @chmod($finalPath, 0644);
            return [
                'status' => 'renamed',
                'newFilename' => $newFilename,
                'message' => "تغییر نام وکتور SVG به استاندارد: {$newFilename}"
            ];
        }
        return ['status' => 'error', 'message' => 'خطا در تغییر نام فایل وکتور SVG.'];
    }

    $fileSize = filesize($sourcePath);
    $imageInfo = @getimagesize($sourcePath);
    if (!$imageInfo) {
        return ['status' => 'error', 'message' => 'فرمت فایل معتبر نیست یا تصویر خراب است.'];
    }

    $origW = $imageInfo[0];
    $origH = $imageInfo[1];

    // If already standard named WebP with acceptable dimensions (<= maxDim), skip
    $isStandardWebp = ($ext === 'webp') && preg_match('/^(product|giftitem|logo)-(\d+-)?[a-z0-9]+-[a-f0-9]{4}\.webp$/', $currentFilename);
    if ($isStandardWebp && max($origW, $origH) <= $maxDim) {
        return [
            'status' => 'skip',
            'message' => "تصویر از قبل WebP استاندارد و بهینه‌سازی‌شده است ({$origW}×{$origH} - " . formatSizeUnits($fileSize) . ")."
        ];
    }

    // Load source image into GD
    $rawContent = @file_get_contents($sourcePath);
    if ($rawContent === false) {
        return ['status' => 'error', 'message' => 'عدم دسترسی برای خواندن محتوای فایل تصویر.'];
    }
    $srcIm = @imagecreatefromstring($rawContent);
    unset($rawContent); // Free memory immediately

    if (!$srcIm) {
        return ['status' => 'error', 'message' => 'کتابخانه GD نتوانست تصویر را پردازش کند.'];
    }

    // Compute proportional dimensions down to maxDim
    $targetW = $origW;
    $targetH = $origH;
    if ($origW > $maxDim || $origH > $maxDim) {
        if ($origW >= $origH) {
            $targetW = $maxDim;
            $targetH = (int) round(($origH * $maxDim) / $origW);
        } else {
            $targetH = $maxDim;
            $targetW = (int) round(($origW * $maxDim) / $origH);
        }
    }

    // Create target canvas and preserve transparency
    $dstIm = imagecreatetruecolor($targetW, $targetH);
    imagealphablending($dstIm, false);
    imagesavealpha($dstIm, true);
    $transparent = imagecolorallocatealpha($dstIm, 0, 0, 0, 127);
    imagefilledrectangle($dstIm, 0, 0, $targetW, $targetH, $transparent);

    imagecopyresampled($dstIm, $srcIm, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);
    imagedestroy($srcIm); // Free source image memory immediately

    $newFilename = generateStandardFilename($entityType, $entityId, $role, 'webp');
    $tmpPath = $sourceDir . 'tmp_mig_' . uniqid() . '.webp';
    $finalPath = $sourceDir . $newFilename;

    $success = @imagewebp($dstIm, $tmpPath, $quality);
    imagedestroy($dstIm); // Free destination canvas memory immediately

    if (!$success || !file_exists($tmpPath) || filesize($tmpPath) === 0) {
        @unlink($tmpPath);
        return ['status' => 'error', 'message' => 'خطا در فشرده‌سازی و ذخیره به فرمت WebP.'];
    }

    @chmod($tmpPath, 0644);

    if (!@rename($tmpPath, $finalPath)) {
        @unlink($tmpPath);
        return ['status' => 'error', 'message' => 'خطا در جابجایی فایل فشرده‌شده به نام استاندارد.'];
    }

    $newSize = filesize($finalPath);

    // Delete old file if different path
    if (realpath($sourcePath) !== realpath($finalPath) && file_exists($sourcePath)) {
        @unlink($sourcePath);
    }

    // Force garbage collection on shared host
    if (function_exists('gc_collect_cycles')) {
        gc_collect_cycles();
    }

    $savedBytes = max(0, $fileSize - $newSize);
    $savedPercent = $fileSize > 0 ? round(($savedBytes / $fileSize) * 100) : 0;

    return [
        'status' => 'converted',
        'newFilename' => $newFilename,
        'oldSize' => $fileSize,
        'newSize' => $newSize,
        'savedBytes' => $savedBytes,
        'savedPercent' => $savedPercent,
        'origW' => $origW,
        'origH' => $origH,
        'targetW' => $targetW,
        'targetH' => $targetH,
    ];
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بهینه‌سازی و تبدیل هوشمند تصاویر به WebP</title>
    <style>
        body {
            font-family: system-ui, -apple-system, Tahoma, sans-serif;
            background: #0F172A;
            color: #F8FAFC;
            margin: 0;
            padding: 24px;
            direction: rtl;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
        }
        h1 {
            font-size: 1.5rem;
            color: #38BDF8;
            margin-bottom: 8px;
        }
        p.subtitle {
            color: #94A3B8;
            font-size: 0.9rem;
            margin-top: 0;
            margin-bottom: 24px;
        }
        .log-box {
            background: #1E293B;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 16px;
            font-family: monospace;
            font-size: 0.85rem;
            line-height: 1.6;
            max-height: 65vh;
            overflow-y: auto;
            direction: ltr;
            text-align: left;
            margin-bottom: 24px;
        }
        .row-ok { color: #34D399; }
        .row-skip { color: #94A3B8; }
        .row-err { color: #F87171; font-weight: bold; }
        .row-head { color: #38BDF8; font-weight: bold; margin-top: 14px; border-bottom: 1px solid #334155; padding-bottom: 4px; }
        .summary-card {
            background: #1E293B;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-val {
            font-size: 1.6rem;
            font-weight: 700;
            color: #38BDF8;
        }
        .stat-label {
            font-size: 0.8rem;
            color: #94A3B8;
            margin-top: 4px;
        }
        .btn-return {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #2563EB;
            color: #FFF;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-return:hover { background: #1D4ED8; }
    </style>
</head>
<body>
<div class="container">
    <h1>🚀 بهینه‌سازی و تبدیل دسته‌جمعی تصاویر به WebP</h1>
    <p class="subtitle">تبدیل استاندارد نام فایل‌ها، کاهش ابعاد به حداکثر ۱۶۰۰ پیکسل و فشرده‌سازی هوشمند بدون افت کیفیت محسوس</p>
    
    <div class="log-box">
<?php

$totalProcessed = 0;
$totalConverted = 0;
$totalSkipped = 0;
$totalErrors = 0;
$totalOriginalBytes = 0;
$totalOptimizedBytes = 0;

// ──────────────────────────────────────────────────────────────
// 1. Product Main Images
// ──────────────────────────────────────────────────────────────
echo "<div class='row-head'>=== 1. تصاویر اصلی محصولات (products.image) ===</div>";
$products = db()->query("SELECT id, name, image FROM products WHERE image IS NOT NULL AND image != ''")->fetchAll();
foreach ($products as $p) {
    $totalProcessed++;
    $res = processAndOptimizeImage(UPLOAD_DIR, $p['image'], 'product', (int) $p['id'], 'main');

    if ($res['status'] === 'converted') {
        db()->prepare("UPDATE products SET image = ? WHERE id = ?")->execute([$res['newFilename'], $p['id']]);
        $totalConverted++;
        $totalOriginalBytes += $res['oldSize'];
        $totalOptimizedBytes += $res['newSize'];
        echo "<div class='row-ok'>[CONVERTED] Product #{$p['id']} ({$p['name']}): {$p['image']} ({$res['origW']}x{$res['origH']}, " . formatSizeUnits($res['oldSize']) . ") &rarr; {$res['newFilename']} ({$res['targetW']}x{$res['targetH']}, " . formatSizeUnits($res['newSize']) . ") [{$res['savedPercent']}% SAVED]</div>";
    } elseif ($res['status'] === 'renamed') {
        db()->prepare("UPDATE products SET image = ? WHERE id = ?")->execute([$res['newFilename'], $p['id']]);
        $totalConverted++;
        echo "<div class='row-ok'>[RENAMED] Product #{$p['id']}: {$p['image']} &rarr; {$res['newFilename']}</div>";
    } elseif ($res['status'] === 'skip') {
        $totalSkipped++;
        echo "<div class='row-skip'>[SKIP] Product #{$p['id']}: {$p['image']} - {$res['message']}</div>";
    } else {
        $totalErrors++;
        echo "<div class='row-err'>[ERROR] Product #{$p['id']}: {$p['image']} - {$res['message']}</div>";
    }
}

// ──────────────────────────────────────────────────────────────
// 2. Product Gallery Images
// ──────────────────────────────────────────────────────────────
echo "<div class='row-head'>=== 2. تصاویر گالری محصولات (product_images) ===</div>";
$gallery = db()->query("SELECT id, product_id, image_path, sort_order FROM product_images ORDER BY product_id, sort_order")->fetchAll();
foreach ($gallery as $g) {
    $totalProcessed++;
    $role = 'g' . (int) $g['sort_order'];
    $res = processAndOptimizeImage(UPLOAD_DIR, $g['image_path'], 'product', (int) $g['product_id'], $role);

    if ($res['status'] === 'converted') {
        db()->prepare("UPDATE product_images SET image_path = ? WHERE id = ?")->execute([$res['newFilename'], $g['id']]);
        $totalConverted++;
        $totalOriginalBytes += $res['oldSize'];
        $totalOptimizedBytes += $res['newSize'];
        echo "<div class='row-ok'>[CONVERTED] Gallery #{$g['id']} (Product #{$g['product_id']}): {$g['image_path']} &rarr; {$res['newFilename']} [{$res['savedPercent']}% SAVED]</div>";
    } elseif ($res['status'] === 'renamed') {
        db()->prepare("UPDATE product_images SET image_path = ? WHERE id = ?")->execute([$res['newFilename'], $g['id']]);
        $totalConverted++;
        echo "<div class='row-ok'>[RENAMED] Gallery #{$g['id']}: {$g['image_path']} &rarr; {$res['newFilename']}</div>";
    } elseif ($res['status'] === 'skip') {
        $totalSkipped++;
        echo "<div class='row-skip'>[SKIP] Gallery #{$g['id']} (Product #{$g['product_id']}): {$g['image_path']} - {$res['message']}</div>";
    } else {
        $totalErrors++;
        echo "<div class='row-err'>[ERROR] Gallery #{$g['id']} (Product #{$g['product_id']}): {$g['image_path']} - {$res['message']}</div>";
    }
}

// ──────────────────────────────────────────────────────────────
// 3. Gift Items Images
// ──────────────────────────────────────────────────────────────
$hasGiftTable = (bool) db()->query("SHOW TABLES LIKE 'gift_items'")->fetch();
if ($hasGiftTable) {
    echo "<div class='row-head'>=== 3. تصاویر آیتم‌های گیفت‌باکس (gift_items) ===</div>";
    $gifts = db()->query("SELECT id, name, image FROM gift_items WHERE image IS NOT NULL AND image != ''")->fetchAll();
    foreach ($gifts as $gift) {
        $totalProcessed++;
        $res = processAndOptimizeImage(UPLOAD_DIR, $gift['image'], 'giftitem', (int) $gift['id'], 'main');

        if ($res['status'] === 'converted') {
            db()->prepare("UPDATE gift_items SET image = ? WHERE id = ?")->execute([$res['newFilename'], $gift['id']]);
            $totalConverted++;
            $totalOriginalBytes += $res['oldSize'];
            $totalOptimizedBytes += $res['newSize'];
            echo "<div class='row-ok'>[CONVERTED] Gift #{$gift['id']} ({$gift['name']}): {$gift['image']} &rarr; {$res['newFilename']} [{$res['savedPercent']}% SAVED]</div>";
        } elseif ($res['status'] === 'renamed') {
            db()->prepare("UPDATE gift_items SET image = ? WHERE id = ?")->execute([$res['newFilename'], $gift['id']]);
            $totalConverted++;
            echo "<div class='row-ok'>[RENAMED] Gift #{$gift['id']}: {$gift['image']} &rarr; {$res['newFilename']}</div>";
        } elseif ($res['status'] === 'skip') {
            $totalSkipped++;
            echo "<div class='row-skip'>[SKIP] Gift #{$gift['id']}: {$gift['image']} - {$res['message']}</div>";
        } else {
            $totalErrors++;
            echo "<div class='row-err'>[ERROR] Gift #{$gift['id']}: {$gift['image']} - {$res['message']}</div>";
        }
    }
}

// ──────────────────────────────────────────────────────────────
// 4. Site Logo
// ──────────────────────────────────────────────────────────────
echo "<div class='row-head'>=== 4. لوگوی سایت (Settings: site_logo) ===</div>";
$logoFile = getSetting('site_logo', '');
if ($logoFile !== '') {
    $totalProcessed++;
    $res = processAndOptimizeImage(BRANDING_UPLOAD_DIR, $logoFile, 'logo', 0, 'site');

    if ($res['status'] === 'converted' || $res['status'] === 'renamed') {
        setSetting('site_logo', $res['newFilename']);
        $totalConverted++;
        if (isset($res['oldSize'])) {
            $totalOriginalBytes += $res['oldSize'];
            $totalOptimizedBytes += $res['newSize'];
        }
        echo "<div class='row-ok'>[CONVERTED] Logo: {$logoFile} &rarr; {$res['newFilename']}</div>";
    } elseif ($res['status'] === 'skip') {
        $totalSkipped++;
        echo "<div class='row-skip'>[SKIP] Logo: {$logoFile} - {$res['message']}</div>";
    } else {
        $totalErrors++;
        echo "<div class='row-err'>[ERROR] Logo: {$logoFile} - {$res['message']}</div>";
    }
} else {
    echo "<div class='row-skip'>[SKIP] هیچ لوگویی در تنظیمات سایت تنظیم نشده است.</div>";
}

// ──────────────────────────────────────────────────────────────
// 5. Card-to-Card Receipts (orders.card_to_card_receipt)
// ──────────────────────────────────────────────────────────────
echo "<div class='row-head'>=== 5. فیش‌های واریزی کارت‌به‌کارت (orders.card_to_card_receipt) ===</div>";
$receiptOrders = db()->query("SELECT id, order_code, card_to_card_receipt, card_to_card_submitted_at FROM orders WHERE card_to_card_receipt IS NOT NULL AND card_to_card_receipt != ''")->fetchAll();
if (empty($receiptOrders)) {
    echo "<div class='row-skip'>[SKIP] هیچ رسید کارت‌به‌کارتی در دیتابیس ثبت نشده است.</div>";
} else {
    foreach ($receiptOrders as $ro) {
        $totalProcessed++;
        $receiptFile = $ro['card_to_card_receipt'];
        $receiptPath = CARD_TO_CARD_UPLOAD_DIR . $receiptFile;

        if (!file_exists($receiptPath)) {
            $totalErrors++;
            echo "<div class='row-err'>[ERROR] Order #{$ro['id']} ({$ro['order_code']}): فایل رسید {$receiptFile} روی هاست یافت نشد.</div>";
            continue;
        }

        // Check if already standard receipt pattern
        if (preg_match('/^receipt-order-\d+-[a-zA-Z0-9\-]+-[0-9]{8}-[0-9]{6}-[a-f0-9]{4}\.webp$/', $receiptFile)) {
            $totalSkipped++;
            echo "<div class='row-skip'>[SKIP] Order #{$ro['id']} ({$ro['order_code']}): رسید از قبل دارای نام استاندارد WebP است ({$receiptFile}).</div>";
            continue;
        }

        // Convert / recompress to WebP
        $cleanCode = preg_replace('/[^a-zA-Z0-9]/', '', $ro['order_code']);
        $subDate = !empty($ro['card_to_card_submitted_at']) ? date('Ymd-His', strtotime($ro['card_to_card_submitted_at'])) : date('Ymd-His');
        $hash4 = substr(bin2hex(random_bytes(2)), 0, 4);
        $newReceiptName = "receipt-order-{$ro['id']}-{$cleanCode}-{$subDate}-{$hash4}.webp";
        $newReceiptPath = CARD_TO_CARD_UPLOAD_DIR . $newReceiptName;

        $res = processAndOptimizeImage(CARD_TO_CARD_UPLOAD_DIR, $receiptFile, 'receipt', (int) $ro['id'], $cleanCode, 1200, 78);
        if ($res['status'] === 'converted' || $res['status'] === 'renamed') {
            $actualCreatedPath = CARD_TO_CARD_UPLOAD_DIR . $res['newFilename'];
            if ($actualCreatedPath !== $newReceiptPath && file_exists($actualCreatedPath)) {
                @rename($actualCreatedPath, $newReceiptPath);
            }
            db()->prepare("UPDATE orders SET card_to_card_receipt = ? WHERE id = ?")->execute([$newReceiptName, $ro['id']]);
            $totalConverted++;
            if (isset($res['oldSize'], $res['newSize'])) {
                $totalOriginalBytes += $res['oldSize'];
                $totalOptimizedBytes += $res['newSize'];
            }
            echo "<div class='row-ok'>[CONVERTED] Order #{$ro['id']} ({$ro['order_code']}): {$receiptFile} &rarr; {$newReceiptName}</div>";
        } elseif ($res['status'] === 'skip') {
            $totalSkipped++;
            echo "<div class='row-skip'>[SKIP] Order #{$ro['id']}: {$receiptFile} - {$res['message']}</div>";
        } else {
            $totalErrors++;
            echo "<div class='row-err'>[ERROR] Order #{$ro['id']}: {$receiptFile} - {$res['message']}</div>";
        }
    }
}

$totalSavedBytes = max(0, $totalOriginalBytes - $totalOptimizedBytes);
$overallReduction = $totalOriginalBytes > 0 ? round(($totalSavedBytes / $totalOriginalBytes) * 100) : 0;
?>
    </div>

    <div class="summary-card">
        <div class="stat-item">
            <div class="stat-val"><?= $totalProcessed ?></div>
            <div class="stat-label">کل فایل‌های بررسی‌شده</div>
        </div>
        <div class="stat-item">
            <div class="stat-val" style="color: #34D399;"><?= $totalConverted ?></div>
            <div class="stat-label">تبدیل و بهینه‌سازی‌شده به WebP</div>
        </div>
        <div class="stat-item">
            <div class="stat-val" style="color: #94A3B8;"><?= $totalSkipped ?></div>
            <div class="stat-label">از قبل استاندارد و بهینه</div>
        </div>
        <div class="stat-item">
            <div class="stat-val" style="color: <?= $totalErrors > 0 ? '#F87171' : '#34D399' ?>;"><?= $totalErrors ?></div>
            <div class="stat-label">خطاها</div>
        </div>
        <div class="stat-item">
            <div class="stat-val" style="color: #38BDF8;"><?= formatSizeUnits($totalSavedBytes) ?></div>
            <div class="stat-label">کاهش مصرف حجم هاست (<?= $overallReduction ?>٪ صرفه‌جویی)</div>
        </div>
    </div>

    <div style="margin-top: 24px; text-align: center;">
        <a href="<?= BASE_URL ?>/admin/products.php" class="btn-return">&larr; بازگشت به پنل مدیریت محصولات</a>
    </div>
</div>
</body>
</html>
