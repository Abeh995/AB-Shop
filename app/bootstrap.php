<?php

define('APP_VERSION', '1.1.0');
define('APP_ROOT', dirname(__DIR__));

ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', 1);
}

session_start();

require_once __DIR__ . '/../config/config.php';

require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/core/csrf.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/cart.php';

require_once __DIR__ . '/services/ZarinpalService.php';
require_once __DIR__ . '/services/SmsService.php';
require_once __DIR__ . '/services/CouponService.php';


function renderView(string $__view, array $__data = []): void
{
    extract($__data, EXTR_SKIP);
    require APP_ROOT . '/views/' . $__view . '.php';
}
