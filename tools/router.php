<?php
/**
 * Developer local PHP server router (for php -S 127.0.0.1:8085 tools/router.php)
 * Serves existing static files directly, and dispatches dynamic clean routes to index.php.
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = realpath(__DIR__ . '/..') . $uri;

if ($uri !== '/' && file_exists($path) && !is_dir($path)) {
    return false; // let built-in web server serve the static file
}

$_GET['route'] = ltrim($uri, '/');
require realpath(__DIR__ . '/..') . '/index.php';
