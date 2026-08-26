<?php
/**
 * Shared application bootstrap.
 * All entry points (site index.php, admin/*.php, ajax/*.php, payment/*.php) require this file.
 *
 * Bootstrap order:
 * 1) Define base constants (version and project root).
 * 2) Configure secure session settings and start the session.
 * 3) Load application configuration.
 * 4) Load the application core (app/core).
 * 5) Load application services (app/services), including payment, SMS, and coupon services.
 */

define('APP_VERSION', '1.2.0');
define('APP_ROOT', dirname(__DIR__));

// ---------- Secure session configuration before session_start() ----------
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', 1);
}

session_start();

// ---------- Config ----------
require_once __DIR__ . '/../config/config.php';

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

/**
 * Render a view with the provided variables to keep business logic separate from presentation.
 * @param string $__view Relative path under views/ without the file extension, e.g. 'site/home' or 'admin/products'
 * @param array  $__data Associative array of variables exposed to the view
 */
function renderView(string $__view, array $__data = []): void
{
    extract($__data, EXTR_SKIP);
    require APP_ROOT . '/views/' . $__view . '.php';
}
