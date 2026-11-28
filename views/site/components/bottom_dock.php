<?php
/**
 * 5-Hub Liquid Glass Mobile Bottom Dock Component
 *
 * Pure presentation 5-hub navigation bar without inline SVG styles.
 * Props:
 * - activeHub (string|null): 'home' | 'categories' | 'cart' | 'search' | 'account'
 * - cartCount (int|null): current item quantity badge count
 * - isLoggedIn (bool|null): customer auth state for account link/label
 */
if (!isset($activeHub) || $activeHub === null || $activeHub === '') {
    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $activeHub = 'home';
    if (str_starts_with($reqPath, '/categories') || str_starts_with($reqPath, '/category')) {
        $activeHub = 'categories';
    } elseif (str_starts_with($reqPath, '/cart') || str_starts_with($reqPath, '/checkout')) {
        $activeHub = 'cart';
    } elseif (str_starts_with($reqPath, '/search')) {
        $activeHub = 'search';
    } elseif (
        str_starts_with($reqPath, '/account') ||
        str_starts_with($reqPath, '/login') ||
        str_starts_with($reqPath, '/signup') ||
        str_starts_with($reqPath, '/verify')
    ) {
        $activeHub = 'account';
    }
}

$cartQty = isset($cartCount) && $cartCount !== null ? (int) $cartCount : cartCount();
$loggedIn = isset($isLoggedIn) && $isLoggedIn !== null ? (bool) $isLoggedIn : isCustomerLoggedIn();
?>
<div class="dock-container">
    <nav class="dock" id="liquidDock" data-ab-dock aria-label="ناوبری اصلی فروشگاه">
        <div class="liquid-drop" id="liquidDrop"></div>

        <a href="/" class="dock-item<?= $activeHub === 'home' ? ' active' : '' ?>" data-index="0">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"></path>
            </svg>
            <span class="dock-label">خانه</span>
        </a>

        <a href="/categories" class="dock-item<?= $activeHub === 'categories' ? ' active' : '' ?>" data-index="1">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
            </svg>
            <span class="dock-label">دسته‌ها</span>
        </a>

        <a href="/cart" class="dock-item<?= $activeHub === 'cart' ? ' active' : '' ?>" data-index="2">
            <span class="dock-cart-badge<?= $cartQty > 0 ? ' has-items' : '' ?>"
                  id="dockCartBadge"<?= $cartQty <= 0 ? ' hidden' : '' ?>><?= toPersianDigits((string) max(0, $cartQty)) ?></span>
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span class="dock-label">سبد خرید</span>
        </a>

        <a href="/search" class="dock-item<?= $activeHub === 'search' ? ' active' : '' ?>" data-index="3" data-ab-dock-search>
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <span class="dock-label">جستجو</span>
        </a>

        <a href="<?= $loggedIn ? '/account' : '/login' ?>" class="dock-item<?= $activeHub === 'account' ? ' active' : '' ?>" data-index="4">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="dock-label"><?= $loggedIn ? 'حساب من' : 'ورود' ?></span>
        </a>
    </nav>
</div>
