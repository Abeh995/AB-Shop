<?php
/**
 * Storefront front controller — every site request (other than admin, ajax,
 * payment, uploads) is routed through here.
 * .htaccess forwards requests to this file as index.php?route=path.
 * This file only dispatches the route to the right controller under
 * app/controllers/site; no business logic or HTML belongs in this file.
 */

require_once __DIR__ . '/app/bootstrap.php';

// --- 301/302 URL Redirect Resolver (SEO preservation for slug changes) ---
$redirectRule = seoResolveRedirect($_SERVER['REQUEST_URI'] ?? '/');
if ($redirectRule) {
    http_response_code($redirectRule['status_code'] ?? 301);
    header('Location: ' . $redirectRule['target_url']);
    exit;
}

$route = trim($_GET['route'] ?? '', '/');
$segments = $route === '' ? [] : explode('/', $route);
$page = $segments[0] ?? 'home';

$controllersDir = __DIR__ . '/app/controllers/site';

switch ($page) {
    case '':
    case 'home':
        require $controllersDir . '/home.php';
        break;

    case 'categories':
        require $controllersDir . '/categories.php';
        break;

    case 'category':
        $_GET['slug'] = $segments[1] ?? '';
        require $controllersDir . '/category.php';
        break;

    case 'category.php':
        $legacyCatSlug = trim($_GET['slug'] ?? '');
        if ($legacyCatSlug !== '') {
            header('Location: /category/' . rawurlencode($legacyCatSlug), true, 301);
            exit;
        }
        header('Location: /categories', true, 301);
        exit;

    case 'product':
        $_GET['slug'] = $segments[1] ?? '';
        require $controllersDir . '/product.php';
        break;

    case 'product.php':
        $legacyProdSlug = trim($_GET['slug'] ?? '');
        if ($legacyProdSlug !== '') {
            header('Location: /product/' . rawurlencode($legacyProdSlug), true, 301);
            exit;
        }
        header('Location: /', true, 301);
        exit;

    case 'cart':
        require $controllersDir . '/cart.php';
        break;

    case 'checkout':
        require $controllersDir . '/checkout.php';
        break;

    case 'payment':
        if (($segments[1] ?? '') === 'card-to-card') {
            require $controllersDir . '/card_to_card.php';
        } else {
            require $controllersDir . '/not_found.php';
        }
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

    case 'privacy':
        require $controllersDir . '/privacy.php';
        break;

    case 'signup':
        require $controllersDir . '/signup.php';
        break;

    case 'verify-phone':
        require $controllersDir . '/verify_phone.php';
        break;

    case 'verify-email':
        require $controllersDir . '/verify_email.php';
        break;

    case 'login':
        require $controllersDir . '/login.php';
        break;

    case 'logout':
        require $controllersDir . '/logout.php';
        break;

    case 'account':
        if (($segments[1] ?? '') === 'order') {
            $_GET['code'] = $segments[2] ?? '';
            require $controllersDir . '/account_order.php';
        } else {
            require $controllersDir . '/account.php';
        }
        break;

    case 'tag':
        $_GET['slug'] = $segments[1] ?? '';
        require $controllersDir . '/tag.php';
        break;

    case 'search':
        require $controllersDir . '/search.php';
        break;

    case 'llms.txt':
    case 'llms-full.txt':
        $_GET['format'] = ($page === 'llms-full.txt') ? 'full' : 'standard';
        require $controllersDir . '/llms.php';
        break;

    default:
        http_response_code(404);
        require $controllersDir . '/not_found.php';
        break;
}
