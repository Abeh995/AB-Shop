<?php
/**
 * CSRF protection for all storefront and admin forms.
 */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Return a ready-to-render hidden input for HTML forms.
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

// Validate the submitted form token and abort invalid requests.
function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('درخواست نامعتبر است (CSRF). لطفاً صفحه را رفرش کرده و دوباره تلاش کنید.');
    }
}
