<?php
/**
 * 5-Hub Floating Liquid Glass Navigation Dock for Mobile & Tablet (FEAT-C004)
 * Items: 1- خانه 2- دسته‌بندی 3- سبد خرید 4- جستجو 5- حساب کاربری
 */
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

$cartQty = cartCount();
?>
<nav class="bottom-dock-nav" aria-label="ناوبری اصلی کلاینت">
    <div class="bottom-dock-inner">
        <!-- 1. خانه -->
        <a href="/" class="dock-item <?= $activeHub === 'home' ? 'is-active' : '' ?>">
            <div class="dock-icon-wrap">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <span class="dock-label">خانه</span>
        </a>

        <!-- 2. دسته‌بندی -->
        <a href="/categories" class="dock-item <?= $activeHub === 'categories' ? 'is-active' : '' ?>">
            <div class="dock-icon-wrap">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
            </div>
            <span class="dock-label">دسته‌ها</span>
        </a>

        <!-- 3. سبد خرید (مرکز) -->
        <a href="/cart" class="dock-item dock-item-cart <?= $activeHub === 'cart' ? 'is-active' : '' ?>">
            <div class="dock-icon-wrap">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <span class="dock-badge <?= $cartQty > 0 ? 'has-items' : '' ?>" id="dockCartBadge"><?= toPersianDigits((string)$cartQty) ?></span>
            </div>
            <span class="dock-label">سبد خرید</span>
        </a>

        <!-- 4. جستجو -->
        <a href="/search" class="dock-item <?= $activeHub === 'search' ? 'is-active' : '' ?>" id="dockSearchBtn">
            <div class="dock-icon-wrap">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <span class="dock-label">جستجو</span>
        </a>

        <!-- 5. حساب کاربری -->
        <a href="<?= isCustomerLoggedIn() ? '/account' : '/login' ?>" class="dock-item <?= $activeHub === 'account' ? 'is-active' : '' ?>">
            <div class="dock-icon-wrap">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <span class="dock-label"><?= isCustomerLoggedIn() ? 'حساب من' : 'ورود' ?></span>
        </a>
    </div>
</nav>
