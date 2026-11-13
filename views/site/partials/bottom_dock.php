<?php
/**
 * 5-Hub Floating Liquid Glass Navigation Dock for Mobile & Tablet (FEAT-C004)
 * Based on deckmobile_liquid.html specification with Warm Craft brand tokens.
 * Items: 1- خانه (0) 2- دسته‌بندی‌ها (1) 3- سبد خرید (2) 4- جستجو (3) 5- حساب کاربری (4)
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
<div class="dock-container">
    <nav class="dock" id="liquidDock" aria-label="ناوبری اصلی فروشگاه">
        <div class="liquid-drop" id="liquidDrop"></div>

        <!-- 1. خانه -->
        <a href="/" class="dock-item <?= $activeHub === 'home' ? 'active' : '' ?>" data-index="0">
            <svg width="22" height="22" style="width:22px;height:22px;max-width:22px;max-height:22px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"></path>
            </svg>
            <span class="dock-label">خانه</span>
        </a>

        <!-- 2. دسته‌بندی‌ها -->
        <a href="/categories" class="dock-item <?= $activeHub === 'categories' ? 'active' : '' ?>" data-index="1">
            <svg width="22" height="22" style="width:22px;height:22px;max-width:22px;max-height:22px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
            </svg>
            <span class="dock-label">دسته‌ها</span>
        </a>

        <!-- 3. سبد خرید (مرکز با بج تعداد) -->
        <a href="/cart" class="dock-item <?= $activeHub === 'cart' ? 'active' : '' ?>" data-index="2">
            <?php if ($cartQty > 0): ?>
                <span class="dock-cart-badge" id="dockCartBadge"><?= toPersianDigits((string)$cartQty) ?></span>
            <?php endif; ?>
            <svg width="22" height="22" style="width:22px;height:22px;max-width:22px;max-height:22px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span class="dock-label">سبد خرید</span>
        </a>

        <!-- 4. جستجو -->
        <a href="/search" class="dock-item <?= $activeHub === 'search' ? 'active' : '' ?>" data-index="3">
            <svg width="22" height="22" style="width:22px;height:22px;max-width:22px;max-height:22px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <span class="dock-label">جستجو</span>
        </a>

        <!-- 5. حساب کاربری -->
        <a href="<?= isCustomerLoggedIn() ? '/account' : '/login' ?>" class="dock-item <?= $activeHub === 'account' ? 'active' : '' ?>" data-index="4">
            <svg width="22" height="22" style="width:22px;height:22px;max-width:22px;max-height:22px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="dock-label"><?= isCustomerLoggedIn() ? 'حساب من' : 'ورود' ?></span>
        </a>
    </nav>
</div>
