<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'AB Socks');
define('SITE_URL', 'https://example.com');

define('CURRENCY_LABEL', 'تومان');

define('APP_DEBUG', false);

define('APP_SECRET', 'CHANGE-THIS-TO-A-LONG-RANDOM-SECRET');

define('UPLOAD_DIR', __DIR__ . '/../uploads/products/');
define('UPLOAD_URL', '/uploads/products/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);

date_default_timezone_set('Asia/Tehran');