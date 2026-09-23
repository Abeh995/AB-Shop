    </main>
</div>

<!-- Admin Mobile/Tablet Bottom Navigation Bar (FEAT-A003) -->
<nav class="admin-bottom-nav" aria-label="ناوبری اصلی پنل ادمین">
    <?php
    $bPage = basename($_SERVER['SCRIPT_NAME']);
    $isDashActive = ($bPage === 'index.php');
    $isOrdersActive = in_array($bPage, ['orders.php', 'order_detail.php', 'card_to_card_payments.php'], true);
    $isProductsActive = in_array($bPage, ['products.php', 'product_edit.php', 'categories.php', 'pricing.php', 'gift_items.php', 'gift_item_edit.php'], true);
    $isFinanceActive = in_array($bPage, ['finance_dashboard.php', 'expenses.php', 'expense_edit.php'], true);
    $isSettingsActive = in_array($bPage, ['settings.php', 'appearance.php', 'sms_patterns.php', 'sms_pattern_edit.php', 'themes.php', 'theme_edit.php', 'email_accounts.php', 'emails.php', 'shipping_methods.php', 'shipping_method_edit.php', 'users.php', 'diagnostics.php', 'notifications_log.php'], true);
    ?>

    <!-- 1. Dashboard -->
    <a href="index.php" class="admin-bottom-nav-item <?= $isDashActive ? 'active' : '' ?>">
        <div class="nav-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="9" rx="1"/>
                <rect x="14" y="3" width="7" height="5" rx="1"/>
                <rect x="14" y="12" width="7" height="9" rx="1"/>
                <rect x="3" y="16" width="7" height="5" rx="1"/>
            </svg>
        </div>
        <span class="nav-label">داشبورد</span>
    </a>

    <!-- 2. Orders -->
    <a href="orders.php" class="admin-bottom-nav-item <?= $isOrdersActive ? 'active' : '' ?>">
        <div class="nav-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m7.5 4.27 9 5.15"/>
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                <path d="m3.3 7 8.7 5 8.7-5"/>
                <path d="M12 22V12"/>
            </svg>
            <?php if (!empty($pendingOrdersCount) && $pendingOrdersCount > 0): ?>
                <span class="nav-badge"><?= (int) $pendingOrdersCount ?></span>
            <?php endif; ?>
        </div>
        <span class="nav-label">سفارش‌ها</span>
    </a>

    <!-- 3. Products -->
    <a href="products.php" class="admin-bottom-nav-item <?= $isProductsActive ? 'active' : '' ?>">
        <div class="nav-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                <path d="M3 6h18"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
        </div>
        <span class="nav-label">محصولات</span>
    </a>

    <!-- 4. Finance -->
    <a href="finance_dashboard.php" class="admin-bottom-nav-item <?= $isFinanceActive ? 'active' : '' ?>">
        <div class="nav-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v20"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <span class="nav-label">مالی</span>
    </a>

    <!-- 5. Settings -->
    <a href="settings.php" class="admin-bottom-nav-item <?= $isSettingsActive ? 'active' : '' ?>">
        <div class="nav-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </div>
        <span class="nav-label">تنظیمات</span>
    </a>
</nav>

<script src="/assets/js/main.js"></script>
<script src="/assets/js/admin-image-optimizer.js"></script>
<script src="/assets/js/admin.js?v=<?= APP_VERSION ?>"></script>
</body>
</html>
