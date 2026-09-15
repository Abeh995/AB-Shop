<?php
/**
 * Secure storage for customer-uploaded card-to-card payment receipts.
 * Temporary files are bound to the customer's PHP session and permanent
 * files are served only through an authenticated admin endpoint.
 */

class CardToCardReceiptService
{
    private const MAX_SIZE = 2097152; // 2 MiB
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function upload(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'بارگذاری تصویر رسید انجام نشد.'];
        }
        if (($file['size'] ?? 0) < 1 || $file['size'] > self::MAX_SIZE) {
            return ['ok' => false, 'error' => 'حجم تصویر رسید باید حداکثر ۲ مگابایت باشد.'];
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['ok' => false, 'error' => 'فایل بارگذاری‌شده معتبر نیست.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!isset(self::ALLOWED_MIMES[$mime])) {
            return ['ok' => false, 'error' => 'فقط تصاویر JPG، PNG یا WEBP برای رسید مجاز هستند.'];
        }
        if (@getimagesize($file['tmp_name']) === false) {
            return ['ok' => false, 'error' => 'فایل انتخاب‌شده یک تصویر معتبر نیست.'];
        }

        self::ensureDirectories();
        self::discardPending();

        $token = bin2hex(random_bytes(24));
        $filename = $token . '.' . self::ALLOWED_MIMES[$mime];
        $destination = CARD_TO_CARD_TMP_DIR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['ok' => false, 'error' => 'ذخیره تصویر روی سرور انجام نشد.'];
        }

        $_SESSION['card_to_card_receipt'] = [
            'token' => $token,
            'filename' => $filename,
            'uploaded_at' => time(),
        ];

        return ['ok' => true, 'token' => $token, 'filename' => $filename];
    }

    public static function hasPending(): bool
    {
        $pending = $_SESSION['card_to_card_receipt'] ?? null;
        if (!is_array($pending) || empty($pending['token']) || empty($pending['filename'])) {
            return false;
        }
        return is_file(self::pendingPath($pending['filename']))
            && hash_equals($pending['token'], pathinfo($pending['filename'], PATHINFO_FILENAME));
    }

    public static function finalizePending(): ?string
    {
        if (!self::hasPending()) {
            return null;
        }
        $pending = $_SESSION['card_to_card_receipt'];
        self::ensureDirectories();
        $source = self::pendingPath($pending['filename']);
        $finalFilename = 'receipt-' . $pending['filename'];
        $destination = CARD_TO_CARD_UPLOAD_DIR . $finalFilename;

        if (!rename($source, $destination)) {
            return null;
        }
        unset($_SESSION['card_to_card_receipt']);
        return $finalFilename;
    }

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

    public static function pathForStoredFile(string $filename): ?string
    {
        if (!preg_match('/^receipt-[a-f0-9]{48}\.(jpg|png|webp)$/', $filename)) {
            return null;
        }
        $path = CARD_TO_CARD_UPLOAD_DIR . $filename;
        return is_file($path) ? $path : null;
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
