<?php
/**
 * Admin horizontal topic sub-navigation bar.
 * Renders topic pills for the current section to make navigation instant and clutter-free.
 */

$currentPage = basename($_SERVER['SCRIPT_NAME']);
$currentQuery = $_SERVER['QUERY_STRING'] ?? '';

require_once APP_ROOT . '/views/admin/layout/nav_config.php';
$adminNav = getAdminNavConfig($currentPage, $pendingOrdersCount ?? 0);

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
<div class="admin-topic-bar-wrap">
    <nav class="admin-topic-bar" aria-label="زیرمجموعه‌های این بخش">
        <?php foreach ($activeSubItems as $topic): ?>
            <a href="<?= e($topic['url']) ?>" class="topic-pill <?= $topic['active'] ? 'active' : '' ?>">
                <?= e($topic['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>
<?php endif; ?>
