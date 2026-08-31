<?php
/**
 * Shared application bootstrap.
 * Every entry point (site index.php, admin/*.php, ajax/*.php, payment/*.php) requires this file.
 *
 * Load order:
 * 1) Base constants (version, root path)
 * 2) Secure session settings and session start
 * 3) Load config
 * 4) Load application core (app/core)
 * 5) Load services (app/services) — payment gateway, SMS, coupons
 */

define('APP_VERSION', '1.3.0');
define('APP_ROOT', dirname(__DIR__));

// ---------- Secure session settings, applied before the session starts ----------
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', 1);
}

session_start();

// ---------- Config ----------
require_once __DIR__ . '/../config/config.php';

// Storage for site-branding assets (logo). Derived from the existing product
// upload path so it works on already-deployed sites without requiring a new
// constant to be added to config.php on the live server.
if (!defined('BRANDING_UPLOAD_DIR')) {
    define('BRANDING_UPLOAD_DIR', dirname(rtrim(UPLOAD_DIR, '/')) . '/branding/');
}
if (!defined('BRANDING_UPLOAD_URL')) {
    define('BRANDING_UPLOAD_URL', dirname(rtrim(UPLOAD_URL, '/')) . '/branding/');
}

// ---------- Core ----------
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/core/csrf.php';
require_once __DIR__ . '/core/settings.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/customer_auth.php';
require_once __DIR__ . '/core/cart.php';

// ---------- Services ----------
require_once __DIR__ . '/services/ZarinpalService.php';
require_once __DIR__ . '/services/SmsService.php';
require_once __DIR__ . '/services/CouponService.php';
require_once __DIR__ . '/services/FarazSmsService.php';
require_once __DIR__ . '/services/EmailService.php';
require_once __DIR__ . '/services/VerificationService.php';

/**
 * Render a view with a given set of variables (keeps logic separate from presentation).
 * @param string $__view Relative path inside views/ without extension, e.g. 'site/home' or 'admin/products'
 * @param array  $__data Associative array of variables made available to the view
 */
function renderView(string $__view, array $__data = []): void
{
    extract($__data, EXTR_SKIP);
    require APP_ROOT . '/views/' . $__view . '.php';
}
