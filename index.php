<?php

require_once __DIR__ . '/includes/bootstrap.php';

$route = trim($_GET['route'] ?? '', '/');
$segments = $route === '' ? [] : explode('/', $route);

$page = $segments[0] ?? 'home';

switch ($page) {
    case '':
    case 'home':
        require __DIR__ . '/pages/home.php';
        break;

    case 'category':
        $_GET['slug'] = $segments[1] ?? '';
        require __DIR__ . '/pages/category.php';
        break;

    case 'product':
        $_GET['slug'] = $segments[1] ?? '';
        require __DIR__ . '/pages/product.php';
        break;

    case 'cart':
        require __DIR__ . '/pages/cart.php';
        break;

    case 'checkout':
        require __DIR__ . '/pages/checkout.php';
        break;

    case 'order':
        if (($segments[1] ?? '') === 'success') {
            $_GET['code'] = $segments[2] ?? '';
            require __DIR__ . '/pages/order_success.php';
        } else {
            require __DIR__ . '/pages/404.php';
        }
        break;

    case 'about':
        require __DIR__ . '/pages/about.php';
        break;

    case 'contact':
        require __DIR__ . '/pages/contact.php';
        break;

    case 'terms':
        require __DIR__ . '/pages/terms.php';
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/pages/404.php';
        break;
}
