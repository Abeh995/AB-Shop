<?php
/**
 * Secure storage and optimization for customer-uploaded card-to-card payment receipts.
 * - Standardized naming convention with Order ID, tracking code, timestamp, and hash.
 * - Auto-compresses receipt images to WebP (< 300KB, max 1200px) and scrubs EXIF/metadata.
 * - Temporary files are bound to the customer's PHP session.
 * - Permanent files are served only through an authenticated admin endpoint.
 */

class CardToCardReceiptService
{
    private const MAX_SIZE = 31457280; // 30 MiB (allows direct high-res phone uploads before compression)
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Upload and optimize receipt image into temporary session storage.
     */
    public static function upload(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'بارگذاری تصویر رسید انجام نشد.'];
        }
        if (($file['size'] ?? 0) < 1 || $file['size'] > self::MAX_SIZE) {
            return ['ok' => false, 'error' => 'حجم فایل انتخاب‌شده بیش از سقف مجاز (۳۰ مگابایت) است.'];
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['ok' => false, 'error' => 'فایل بارگذاری‌شده معتبر نیست.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::ALLOWED_MIMES[$mime])) {
            return ['ok' => false, 'error' => 'فقط تصاویر با فرمت JPG، PNG یا WEBP مجاز هستند.'];
        }
        if (@getimagesize($file['tmp_name']) === false) {
            return ['ok' => false, 'error' => 'فایل انتخاب‌شده یک تصویر معتبر نیست.'];
        }

        self::ensureDirectories();
        self::discardPending();

        $token = bin2hex(random_bytes(24));
        $tmpFilename = $token . '.webp';
        $destination = CARD_TO_CARD_TMP_DIR . $tmpFilename;

        // Auto-compress to WebP (max 1200px, < 300KB) and scrub metadata
        $optimized = self::optimizeToWebp($file['tmp_name'], $destination, 1200, 307200);
        if (!$optimized || !file_exists($destination)) {
            return ['ok' => false, 'error' => 'پردازش و فشرده‌سازی تصویر رسید با خطا مواجه شد.'];
        }

        $_SESSION['card_to_card_receipt'] = [
            'token' => $token,
            'filename' => $tmpFilename,
            'uploaded_at' => time(),
        ];

        $finalSize = filesize($destination);

        return [
            'ok' => true,
            'token' => $token,
            'filename' => $tmpFilename,
            'size' => $finalSize,
            'formatted_size' => round($finalSize / 1024, 1) . ' کیلوبایت',
        ];
    }

    /**
     * Check if the current session has an uploaded, verified pending receipt.
     */
    public static function hasPending(): bool
    {
        $pending = $_SESSION['card_to_card_receipt'] ?? null;
        if (!is_array($pending) || empty($pending['token']) || empty($pending['filename'])) {
            return false;
        }
        return is_file(self::pendingPath($pending['filename']))
            && hash_equals($pending['token'], pathinfo($pending['filename'], PATHINFO_FILENAME));
    }

    /**
     * Finalize the pending receipt file, moving it to permanent storage with a standardized name:
     * receipt-order-{orderId}-{orderCode}-{datetime}-{hash4}.webp
     *
     * @param int $orderId Primary key ID of the created order
     * @param string $orderCode Unique public tracking code (e.g. SK-4F82A1)
     * @return string|null Standardized final filename, or null on failure
     */
    public static function finalizePending(int $orderId, string $orderCode): ?string
    {
        if (!self::hasPending()) {
            return null;
        }
        $pending = $_SESSION['card_to_card_receipt'];
        self::ensureDirectories();
        $source = self::pendingPath($pending['filename']);
        if (!is_file($source)) {
            return null;
        }

        $cleanCode = preg_replace('/[^a-zA-Z0-9]/', '', $orderCode);
        $dateTime = date('Ymd-His');
        $hash4 = substr(bin2hex(random_bytes(2)), 0, 4);

        $finalFilename = "receipt-order-{$orderId}-{$cleanCode}-{$dateTime}-{$hash4}.webp";
        $destination = CARD_TO_CARD_UPLOAD_DIR . $finalFilename;

        if (!rename($source, $destination)) {
            return null;
        }
        @chmod($destination, 0644);
        unset($_SESSION['card_to_card_receipt']);

        return $finalFilename;
    }

    /**
     * Restore a finalized receipt back to pending status if the checkout transaction failed.
     */
    public static function restorePending(string $finalFilename): void
    {
        $finalPath = CARD_TO_CARD_UPLOAD_DIR . $finalFilename;
        if (is_file($finalPath)) {
            $token = bin2hex(random_bytes(24));
            $tmpFilename = $token . '.webp';
            $tmpPath = CARD_TO_CARD_TMP_DIR . $tmpFilename;
            if (@rename($finalPath, $tmpPath)) {
                @chmod($tmpPath, 0644);
                $_SESSION['card_to_card_receipt'] = [
                    'token' => $token,
                    'filename' => $tmpFilename,
                    'uploaded_at' => time(),
                ];
            }
        }
    }

    /**
     * Discard any pending uploaded receipt from the current session.
     */
    public static function discardPending(): void
    {
        $pending = $_SESSION['card_to_card_receipt'] ?? null;
        if (is_array($pending) && !empty($pending['filename'])) {
            $path = self::pendingPath($pending['filename']);
            if (is_file($path)) {
                @unlink($path);
            }
        }
        unset($_SESSION['card_to_card_receipt']);
    }

    /**
     * Resolve the absolute path for a stored receipt file.
     * Guards strictly against directory traversal and accepts both new standard names and legacy names.
     */
    public static function pathForStoredFile(string $filename): ?string
    {
        if (basename($filename) !== $filename) {
            return null;
        }
        // Match standard format (receipt-order-{id}-{code}-{datetime}-{hash}.webp) or legacy (receipt-{48 hex}.(jpg|png|webp))
        if (!preg_match('/^receipt-(order-\d+-[a-zA-Z0-9\-]+|[a-f0-9]{48})\.(jpg|png|webp)$/', $filename)) {
            return null;
        }
        $path = CARD_TO_CARD_UPLOAD_DIR . $filename;
        return is_file($path) ? $path : null;
    }

    /**
     * Safely optimize an image to WebP, scaling down to max 1200px and keeping size under 300KB.
     */
    private static function optimizeToWebp(string $sourcePath, string $destinationPath, int $maxDim = 1200, int $targetMaxBytes = 307200): bool
    {
        $info = @getimagesize($sourcePath);
        if (!$info) {
            return false;
        }
        $origW = $info[0];
        $origH = $info[1];
        $mime = $info['mime'] ?? '';
        $fileSize = filesize($sourcePath);

        // If already WebP, within dimensions and under 300KB, copy directly
        if ($mime === 'image/webp' && max($origW, $origH) <= $maxDim && $fileSize <= $targetMaxBytes) {
            if ($sourcePath === $destinationPath) {
                @chmod($destinationPath, 0644);
                return true;
            }
            if (@copy($sourcePath, $destinationPath)) {
                @chmod($destinationPath, 0644);
                return true;
            }
        }

        $raw = @file_get_contents($sourcePath);
        if ($raw === false) {
            return false;
        }
        $srcIm = @imagecreatefromstring($raw);
        unset($raw);
        if (!$srcIm) {
            return false;
        }

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

        $dstIm = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($dstIm, false);
        imagesavealpha($dstIm, true);
        $transparent = imagecolorallocatealpha($dstIm, 0, 0, 0, 127);
        imagefilledrectangle($dstIm, 0, 0, $targetW, $targetH, $transparent);

        imagecopyresampled($dstIm, $srcIm, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);
        imagedestroy($srcIm);

        // Encode to WebP (quality 78)
        $quality = 78;
        $tmpOut = $destinationPath . '.tmp';
        @imagewebp($dstIm, $tmpOut, $quality);

        // If still > 300KB, iteratively lower quality
        if (file_exists($tmpOut) && filesize($tmpOut) > $targetMaxBytes) {
            @unlink($tmpOut);
            @imagewebp($dstIm, $tmpOut, 65);
        }
        if (file_exists($tmpOut) && filesize($tmpOut) > $targetMaxBytes) {
            @unlink($tmpOut);
            @imagewebp($dstIm, $tmpOut, 52);
        }

        imagedestroy($dstIm);

        if (!file_exists($tmpOut) || filesize($tmpOut) === 0) {
            @unlink($tmpOut);
            return false;
        }

        @chmod($tmpOut, 0644);
        if (!@rename($tmpOut, $destinationPath)) {
            @unlink($tmpOut);
            return false;
        }

        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        return true;
    }

    private static function pendingPath(string $filename): string
    {
        return CARD_TO_CARD_TMP_DIR . basename($filename);
    }

    private static function ensureDirectories(): void
    {
        if (!is_dir(CARD_TO_CARD_UPLOAD_DIR)) {
            mkdir(CARD_TO_CARD_UPLOAD_DIR, 0755, true);
        }
        if (!is_dir(CARD_TO_CARD_TMP_DIR)) {
            mkdir(CARD_TO_CARD_TMP_DIR, 0755, true);
        }
        $htaccess = CARD_TO_CARD_UPLOAD_DIR . '.htaccess';
        if (!is_file($htaccess)) {
            @file_put_contents($htaccess, "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n");
        }
        $tmpHtaccess = CARD_TO_CARD_TMP_DIR . '.htaccess';
        if (!is_file($tmpHtaccess)) {
            @file_put_contents($tmpHtaccess, "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n");
        }
    }
}
