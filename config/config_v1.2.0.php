<?php
/**
 * Main site configuration. Direct browser access is blocked by .htaccess.
 * After deployment, populate this file with the real environment credentials and settings.
 */

// ---------- Database configuration (obtain credentials from DirectAdmin) ----------
define('DB_HOST', 'localhost');
define('DB_NAME', 'absocksi_mvp');
define('DB_USER', 'absocksi_Abeh');
define('DB_PASS', '@A95175398b');
define('DB_CHARSET', 'utf8mb4');

// ---------- Site settings ----------
define('SITE_NAME', 'AB Socks-Shop');
define('SITE_URL', 'https://absocks.ir');   // No trailing slash.
define('CURRENCY_LABEL', 'تومان');

// ---------- Environment ----------
// Keep this false in production so internal errors are not exposed to users.
define('APP_DEBUG', false);

// ---------- Zarinpal payment gateway ----------
// In Sandbox mode, test merchant IDs can be used and no real money is transferred.
// For production, replace ZARINPAL_MERCHANT_ID with the real merchant ID and set ZARINPAL_SANDBOX to false.
define('ZARINPAL_MERCHANT_ID', '00000000-0000-0000-0000-000000000000'); // Default Zarinpal test merchant ID.
define('ZARINPAL_SANDBOX', true);

// ---------- SMS provider ----------
// When SMS_ENABLED is false or the API key is empty, no real SMS is sent
// and the message is recorded in sms_log instead, preventing site failures.
define('SMS_ENABLED', false);
define('SMS_PROVIDER_API_KEY', ''); // Kavenegar API key.
define('SMS_SENDER_LINE', '');      // Kavenegar sender line number.

// ---------- Security ----------
// Generate a long, random secret for CSRF/session signing and store it here.
define('APP_SECRET', '-jEdk$RvFcg<Zr_LJa*aVuQ7%39QMUt]');

// ---------- Upload path ----------
define('UPLOAD_DIR', __DIR__ . '/../uploads/products/');
define('UPLOAD_URL', '/uploads/products/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB

// ---------- Environment-based error reporting ----------
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage_errors.log');
}

date_default_timezone_set('Asia/Tehran');
