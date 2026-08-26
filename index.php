<?php
/**
 * Store front controller. All site requests (except admin, ajax, payment, and uploads) are routed here.
 * .htaccess forwards requests here with the requested path in the route parameter.
 * This file only dispatches the request to the appropriate controller under app/controllers/site.
 * Business logic and HTML rendering do not belong in this file.
 */

require_once __DIR__ . '/app/bootstrap.php';

$route = trim($_GET['route'] ?? '', '/');
$segments = $route === '' ? [] : explode('/', $route);
$page = $segments[0] ?? 'home';

$controllersDir = __DIR__ . '/app/controllers/site';

switch ($page) {
    case '':
    case 'home':
        require $controllersDir . '/home.php';
        break;

    case 'category':
        $_GET['slug'] = $segments[1] ?? '';
        require $controllersDir . '/category.php';
        break;

    case 'product':
        $_GET['slug'] = $segments[1] ?? '';
        require $controllersDir . '/product.php';
        break;

    case 'cart':
        require $controllersDir . '/cart.php';
        break;

    case 'checkout':
        require $controllersDir . '/checkout.php';
        break;

    case 'order':
        if (($segments[1] ?? '') === 'success') {
            $_GET['code'] = $segments[2] ?? '';
            require $controllersDir . '/order_success.php';
        } elseif (($segments[1] ?? '') === 'failed') {
            $_GET['code'] = $segments[2] ?? '';
            require $controllersDir . '/order_failed.php';
        } else {
            require $controllersDir . '/not_found.php';
        }
        break;

    case 'about':
        require $controllersDir . '/about.php';
        break;

    case 'contact':
        require $controllersDir . '/contact.php';
        break;

    case 'terms':
        require $controllersDir . '/terms.php';
        break;

    case 'signup':
        require $controllersDir . '/signup.php';
        break;

    case 'login':
        require $controllersDir . '/login.php';
        break;

    case 'logout':
        require $controllersDir . '/logout.php';
        break;

    case 'account':
        require $controllersDir . '/account.php';
        break;

    default:
        http_response_code(404);
        require $controllersDir . '/not_found.php';
        break;
}
