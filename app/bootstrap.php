<?php
/**
 * Shared application bootstrap.
 * All entry points (index.php, admin/*.php, ajax/*.php, payment/*.php) require this file.
 *
 * Initialization order:
 * 1) Define base constants such as version and root path.
 * 2) Configure and start the session securely.
 * 3) Load configuration.
 * 4) Load core application logic.
 * 5) Load services for payments, SMS, coupons, and related features.
 */

define('APP_VERSION', '1.2.1');
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

// ---------- Core application logic ----------
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
 * Render a view with a defined set of variables to keep application logic separate from presentation.
 * @param string $__view Relative path under views/ without the extension, e.g. 'site/home' or 'admin/products'.
 * @param array  $__data Associative array of variables exposed to the view.
 */
function renderView(string $__view, array $__data = []): void
{
    extract($__data, EXTR_SKIP);
    require APP_ROOT . '/views/' . $__view . '.php';
}
