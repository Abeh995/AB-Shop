<?php

function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function formatPrice($amount): string
{
    $amount = (float) $amount;
    $formatted = number_format($amount, 0);
    return toPersianDigits($formatted) . ' ' . CURRENCY_LABEL;
}

function toPersianDigits(string $str): string
{
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    return str_replace($en, $fa, $str);
}

function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/\s+/u', '-', $text);
    $text = preg_replace('/[^\p{L}\p{N}\-]/u', '', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-') ?: 'item-' . substr(md5(uniqid('', true)), 0, 8);
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function generateOrderCode(): string
{
    return 'SK-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

function effectivePrice(array $product): float
{
    if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']) {
        return (float) $product['discount_price'];
    }
    return (float) $product['price'];
}

function discountPercent(array $product): int
{
    if (!empty($product['discount_price']) && $product['discount_price'] < $product['price'] && $product['price'] > 0) {
        return (int) round((1 - ($product['discount_price'] / $product['price'])) * 100);
    }
    return 0;
}

function isValidIranPhone(string $phone): bool
{
    return (bool) preg_match('/^09[0-9]{9}$/', $phone);
}
