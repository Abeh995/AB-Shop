<?php
/**
 * Admin horizontal topic sub-navigation bar.
 * Renders topic pills for the current section to make navigation instant and clutter-free.
 */

$currentPage = basename($_SERVER['SCRIPT_NAME']);
$currentQuery = $_SERVER['QUERY_STRING'] ?? '';

// Determine active group based on current page
$groups = [
    'orders' => [
        ['label' => 'همه سفارش‌ها', 'url' => 'orders.php', 'active' => in_array($currentPage, ['orders.php', 'order_detail.php'], true)],
        ['label' => '💳 بررسی کارت‌به‌کارت', 'url' => 'card_to_card_payments.php', 'active' => $currentPage === 'card_to_card_payments.php'],
    ],
    'products' => [
        ['label' => '📦 همه محصولات', 'url' => 'products.php', 'active' => $currentPage === 'products.php' && empty($_GET['featured']) || $currentPage === 'product_edit.php'],
        ['label' => '⭐ پیشنهاد ویژه', 'url' => 'products.php?featured=1', 'active' => $currentPage === 'products.php' && !empty($_GET['featured'])],
        ['label' => '📁 دسته‌بندی‌ها', 'url' => 'categories.php', 'active' => $currentPage === 'categories.php'],
        ['label' => '💰 تغییر قیمت گروهی', 'url' => 'pricing.php', 'active' => $currentPage === 'pricing.php'],
        ['label' => '🎁 هدیه و پیشنهاد بعد از سبد', 'url' => 'gift_items.php', 'active' => in_array($currentPage, ['gift_items.php', 'gift_item_edit.php'], true)],
    ],
    'finance' => [
        ['label' => '📊 داشبورد مالی', 'url' => 'finance_dashboard.php', 'active' => $currentPage === 'finance_dashboard.php'],
        ['label' => '🧾 هزینه‌ها', 'url' => 'expenses.php', 'active' => in_array($currentPage, ['expenses.php', 'expense_edit.php'], true)],
    ],
    'settings' => [
        ['label' => '⚙️ تنظیمات عمومی', 'url' => 'settings.php', 'active' => $currentPage === 'settings.php'],
        ['label' => '🎨 ظاهر و صفحه اصلی', 'url' => 'appearance.php', 'active' => in_array($currentPage, ['appearance.php', 'themes.php', 'theme_edit.php'], true)],
        ['label' => '📱 الگوهای پیامک', 'url' => 'sms_patterns.php', 'active' => in_array($currentPage, ['sms_patterns.php', 'sms_pattern_edit.php'], true)],
        ['label' => '📧 ایمیل‌ها', 'url' => 'email_accounts.php', 'active' => in_array($currentPage, ['email_accounts.php', 'emails.php', 'email_read.php', 'email_compose.php'], true)],
        ['label' => '🚚 روش‌های ارسال', 'url' => 'shipping_methods.php', 'active' => in_array($currentPage, ['shipping_methods.php', 'shipping_method_edit.php'], true)],
    ],
];

if (isSuperAdmin()) {
    $groups['settings'][] = ['label' => '👥 مدیران سایت', 'url' => 'users.php', 'active' => $currentPage === 'users.php'];
    $groups['settings'][] = ['label' => '🔧 عیب‌یابی و لاگ', 'url' => 'diagnostics.php', 'active' => in_array($currentPage, ['diagnostics.php', 'notifications_log.php'], true)];
}

$activeGroupKey = null;
foreach ($groups as $groupKey => $items) {
    foreach ($items as $item) {
        if ($item['active']) {
            $activeGroupKey = $groupKey;
            break 2;
        }
    }
}
?>

<?php if ($activeGroupKey && !empty($groups[$activeGroupKey])): ?>
<div class="admin-topic-bar-wrap">
    <nav class="admin-topic-bar" aria-label="زیرمجموعه‌های این بخش">
        <?php foreach ($groups[$activeGroupKey] as $topic): ?>
            <a href="<?= e($topic['url']) ?>" class="topic-pill <?= $topic['active'] ? 'active' : '' ?>">
                <?= e($topic['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>
<?php endif; ?>
