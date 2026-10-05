<?php
/**
 * Shared admin panel header — expects $pageTitle to be set and bootstrap to
 * already be loaded.
 * Restructured with 5 core functional groups and Global Live Search (FEAT-A003, FEAT-A004).
 */
$pageTitle = $pageTitle ?? 'پنل مدیریت';
$flash = getFlash();
$currentPage = basename($_SERVER['SCRIPT_NAME']);

$pendingOrdersCount = class_exists('OrderService') ? OrderService::getPendingCount() : 0;
$pendingC2CCount = class_exists('OrderService') ? OrderService::getPendingCardToCardCount() : 0;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?= e($pageTitle) ?> | مدیریت <?= e(SITE_NAME) ?></title>
<meta name="robots" content="noindex, nofollow">
<?php $adminFavicon = siteFaviconUrl(); ?>
<?php if ($adminFavicon): ?>
<link rel="icon" href="<?= e($adminFavicon) ?>">
<link rel="apple-touch-icon" href="<?= e($adminFavicon) ?>">
<?php else: ?>
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<?php endif; ?>
<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?= e(asset('/assets/css/admin-tokens.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('/assets/css/admin-base.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('/assets/css/admin-shell.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('/assets/css/admin-components.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('/assets/css/admin-patterns.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('/assets/css/admin-utilities.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('/assets/css/admin.css')) ?>">
<?php
$pageCssMap = [
    'orders.php'                => '/assets/css/admin-orders.css',
    'order_detail.php'          => '/assets/css/admin-orders.css',
    'card_to_card_payments.php' => '/assets/css/admin-orders.css',
    'products.php'              => '/assets/css/admin-products.css',
    'product_edit.php'          => '/assets/css/admin-products.css',
    'categories.php'            => '/assets/css/admin-categories.css',
    'pricing.php'               => '/assets/css/admin-pricing.css',
    'gift_items.php'            => '/assets/css/admin-gift-items.css',
    'gift_item_edit.php'        => '/assets/css/admin-gift-items.css',
    'finance_dashboard.php'     => '/assets/css/admin-finance.css',
    'expenses.php'              => '/assets/css/admin-finance.css',
    'expense_edit.php'          => '/assets/css/admin-finance.css',
    'inventory_valuation.php'   => '/assets/css/admin-finance.css',
    'settings.php'              => '/assets/css/admin-settings.css',
    'appearance.php'            => '/assets/css/admin-appearance.css',
    'shipping_methods.php'      => '/assets/css/admin-shipping.css',
    'shipping_method_edit.php'  => '/assets/css/admin-shipping.css',
    'sms_patterns.php'          => '/assets/css/admin-sms.css',
    'sms_pattern_edit.php'      => '/assets/css/admin-sms.css',
    'emails.php'                => '/assets/css/admin-emails.css',
    'email_accounts.php'        => '/assets/css/admin-emails.css',
    'email_read.php'            => '/assets/css/admin-emails.css',
    'email_compose.php'         => '/assets/css/admin-emails.css',
    'coupons.php'               => '/assets/css/admin-coupons.css',
    'tags.php'                  => '/assets/css/admin-coupons.css',
    'users.php'                 => '/assets/css/admin-users.css',
    'diagnostics.php'           => '/assets/css/admin-diagnostics.css',
    'notifications_log.php'     => '/assets/css/admin-diagnostics.css',
];

$activePageCss = $pageStylesheets ?? $pageCssMap[$currentPage] ?? null;
if ($activePageCss) {
    foreach ((array)$activePageCss as $cssFile) {
        echo '<link rel="stylesheet" href="' . e(asset($cssFile)) . '">' . "\n";
    }
}
?>
<script>
(function(){
    try {
        if (localStorage.getItem('admin_sidebar_collapsed') === 'true' && window.innerWidth >= 1024) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    } catch(e){}
})();
</script>
<script src="<?= e(asset('/assets/js/ab-kit.js')) ?>"></script>
</head>
<body class="admin-body">

<?php
require_once APP_ROOT . '/views/admin/layout/nav_config.php';
$adminNav = getAdminNavConfig($currentPage, $pendingOrdersCount, $pendingC2CCount);

$pageTone = $pageTone ?? null;
if (!$pageTone) {
    foreach ($adminNav as $group) {
        if (!empty($group['sub_items'])) {
            foreach ($group['sub_items'] as $sub) {
                if (!empty($sub['active']) && !empty($sub['tone'])) {
                    $pageTone = $sub['tone'];
                    break 2;
                }
            }
        }
        if (!empty($group['active']) && !empty($group['tone'])) {
            $pageTone = $group['tone'];
            break;
        }
    }
}
$pageTone = $pageTone ?? 'brand';
?>

<div class="admin-wrap">
    <aside class="admin-sidebar" id="adminSidebar" aria-label="ناوبری اصلی پنل مدیریت">
        <div class="admin-sidebar-header">
            <a href="index.php" class="admin-logo" id="adminSidebarLogo" title="پیشخوان مدیریت <?= e(SITE_NAME) ?>" data-nav-tooltip="باز کردن منو ( [ )">
                <span class="logo-mark">AB</span>
                <div class="logo-text">
                    <span class="logo-title"><?= e(SITE_NAME) ?></span>
                    <span class="logo-subtitle">پنل مدیریت</span>
                </div>
            </a>
            <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" title="تغییر وضعیت سایدبار (کلید میانبر: [ )" aria-label="جمع کردن یا باز کردن سایدبار">
                <svg class="toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>
        </div>

        <nav class="admin-sidebar-nav" id="adminSidebarNav">
            <?php foreach ($adminNav as $groupKey => $group): ?>
                <?php if (empty($group['sub_items'])): ?>
                    <div class="nav-item-wrap">
                        <a href="<?= e($group['url']) ?>" class="nav-item-single <?= $group['active'] ? 'active' : '' ?>" data-nav-tooltip="<?= e($group['label']) ?>">
                            <span class="nav-icon-box"><?= $group['icon'] ?></span>
                            <span class="nav-item-label"><?= e($group['label']) ?></span>
                            <?php if (!empty($group['badge']) && $group['badge'] > 0): ?>
                                <span class="admin-badge-count"><?= (int) $group['badge'] ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="nav-group <?= $group['active'] ? 'active-group open' : '' ?>" data-group="<?= e($groupKey) ?>">
                        <button type="button" class="nav-group-toggle <?= $group['active'] ? 'active' : '' ?>" aria-expanded="<?= $group['active'] ? 'true' : 'false' ?>" data-nav-tooltip="<?= e($group['label']) ?>">
                            <div class="nav-group-lead">
                                <span class="nav-icon-box"><?= $group['icon'] ?></span>
                                <span class="nav-item-label"><?= e($group['label']) ?></span>
                            </div>
                            <div class="nav-group-meta">
                                <?php if (!empty($group['badge']) && $group['badge'] > 0): ?>
                                    <span class="admin-badge-count"><?= (int) $group['badge'] ?></span>
                                <?php endif; ?>
                                <svg class="nav-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </button>
                        <div class="nav-sub-list">
                            <div class="nav-flyout-header"><?= e($group['label']) ?></div>
                            <?php foreach ($group['sub_items'] as $sub): ?>
                                <a href="<?= e($sub['url']) ?>" class="nav-sub-item <?= $sub['active'] ? 'active' : '' ?>">
                                    <span class="sub-indicator"></span>
                                    <span class="sub-label"><?= e($sub['label']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="/" target="_blank" class="sidebar-footer-link" data-nav-tooltip="مشاهده فروشگاه ↗">
                <span class="nav-icon-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </span>
                <span class="footer-link-text">مشاهده فروشگاه</span>
            </a>
            <a href="logout.php" class="sidebar-footer-link logout-link" data-nav-tooltip="خروج از حساب">
                <span class="nav-icon-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </span>
                <span class="footer-link-text">خروج</span>
            </a>
        </div>
    </aside>

    <main class="admin-main">
        <!-- Unified Modern Admin Topbar (Global across all tabs) -->
        <header class="dash-topbar" id="adminGlobalTopbar">
            <div class="dash-topbar-right">
                <!-- Global Responsive Pill Search Bar -->
                <div class="admin-search-box">
                    <div class="admin-search-input-wrap">
                        <svg class="admin-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="search" id="adminGlobalSearch" class="admin-search-input" placeholder="جستجو در پنل..." autocomplete="off" spellcheck="false" dir="rtl">
                        <kbd class="admin-search-kbd">Ctrl K</kbd>
                    </div>
                    <div id="adminSearchResults" class="admin-search-results" style="display:none;"></div>
                </div>

                <!-- Compact Storefront Link -->
                <a href="/" target="_blank" class="btn-dash-store-compact" title="مشاهده فروشگاه آنلاین (تب جدید)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    <span class="store-btn-text">فروشگاه</span>
                </a>
            </div>

            <div class="dash-topbar-left">
                <?php
                $nowDateInfo = appDateTime(null, 'array');
                $weekdayName = $nowDateInfo['jalali']['weekday_name'] ?? '';
                $shamsiDateNum = $nowDateInfo['jalali']['formatted'] ?? '';
                $gregorianDateNum = str_replace('-', '/', $nowDateInfo['gregorian']['formatted'] ?? '');
                $timeFull = appDateTime(null, 'time_full');
                ?>
                <div class="dash-live-datetime" id="dashLiveDateTime" title="<?= e($nowDateInfo['jalali']['formatted_text'] ?? '') ?> | <?= e($gregorianDateNum) ?>">
                    <!-- Desktop View (>= 769px): Live Clock + Full Numeric Dates -->
                    <div class="dash-datetime-desktop">
                        <span class="live-clock-time" id="liveClockTime" dir="ltr"><?= e($timeFull) ?></span>
                        <span class="live-datetime-divider">|</span>
                        <span class="live-weekday"><?= e($weekdayName) ?></span>
                        <span class="live-date-shamsi" id="liveDateShamsi"><?= e($shamsiDateNum) ?></span>
                        <span class="live-datetime-divider">/</span>
                        <span class="live-date-gregorian" dir="ltr"><?= e($gregorianDateNum) ?></span>
                    </div>
                    <!-- Mobile View (<= 768px): Weekday + Numeric Dates (No Clock) -->
                    <div class="dash-datetime-mobile">
                        <div class="mobile-weekday"><?= e($weekdayName) ?></div>
                        <div class="mobile-dates">
                            <span class="mobile-shamsi"><?= e($shamsiDateNum) ?></span>
                            <span class="mobile-date-sep">·</span>
                            <span class="mobile-gregorian" dir="ltr"><?= e($gregorianDateNum) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <?php if (!in_array($currentPage, ['index.php', 'order_detail.php'], true)): ?>
            <?php require APP_ROOT . '/views/admin/layout/sub_nav.php'; ?>
        <?php endif; ?>

        <div class="ab-page" data-tone="<?= e($pageTone) ?>">
            <?php if ($flash): ?>
                <div class="ab-flash alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

