<?php
/**
 * Admin Horizontal Sub-Navigation Strip (The Global Underline Tab Strip).
 * Renders sleek, minimal, borderless tabs for the active functional hub.
 * Sits flush below the topbar, providing frictionless sub-page navigation.
 */

$currentPage = basename($_SERVER['SCRIPT_NAME']);
$currentQuery = $_SERVER['QUERY_STRING'] ?? '';

require_once APP_ROOT . '/views/admin/layout/nav_config.php';
$adminNav = getAdminNavConfig($currentPage, $pendingOrdersCount ?? 0, $pendingC2CCount ?? 0);

$activeGroupKey = null;
$activeSubItems = [];

foreach ($adminNav as $groupKey => $group) {
    if (!empty($group['sub_items']) && $group['active']) {
        $activeGroupKey = $groupKey;
        $activeSubItems = $group['sub_items'];
        break;
    }
}
?>

<?php if ($activeGroupKey && !empty($activeSubItems)): ?>
<div class="admin-subnav-container">
    <nav class="admin-subnav-strip" aria-label="ناوبری فرعی بخش <?= e($adminNav[$activeGroupKey]['label'] ?? '') ?>">
        <?php foreach ($activeSubItems as $sub): ?>
            <a href="<?= e($sub['url']) ?>" class="subnav-tab-item <?= $sub['active'] ? 'active' : '' ?>">
                <span class="subnav-tab-text"><?= e($sub['label']) ?></span>
                <?php if (!empty($sub['badge']) && $sub['badge'] > 0): ?>
                    <span class="subnav-tab-badge"><?= toPersianDigits((string)$sub['badge']) ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>
<?php endif; ?>
