<?php
/**
 * CSRF protection for all forms (storefront and admin)
 */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Ready-to-use markup for inside an HTML form
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

// Check the token submitted by the form; halts the request if it's invalid
function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        if (function_exists('isStorefrontAjaxRequest') && isStorefrontAjaxRequest()) {
            $msg = 'نشست شما منقضی شده است؛ لطفاً صفحه را تازه‌سازی کنید.';
            jsonResponse(['ok' => false, 'message' => $msg, 'error' => $msg], 403);
        }
        http_response_code(403);
        die('درخواست نامعتبر است (CSRF). لطفاً صفحه را رفرش کرده و دوباره تلاش کنید.');
    }
}
