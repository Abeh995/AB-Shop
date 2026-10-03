<?php
/**
 * AB-Socks Modern SMS Patterns Workstation
 * v1.27.0 - Bento KPIs, Filter Toolbar, Dynamic Variable Cards & Live Mobile Mockup
 *
 * @var string $pageTitle
 * @var array $patterns
 * @var array $availableEvents
 * @var array $metrics
 */
require APP_ROOT . '/views/admin/layout/header.php';
?>

<meta name="csrf-token" content="<?= e(csrfToken()) ?>">

<main class="sms-workspace">

    <!-- 1. Bento KPI Telephony Statistics -->
    <?php require APP_ROOT . '/views/admin/sms_patterns_partials/_kpis.php'; ?>

    <!-- 2. Category Filter & Search Toolbar -->
    <?php require APP_ROOT . '/views/admin/sms_patterns_partials/_toolbar.php'; ?>

    <!-- 3. Pattern Matrix Table -->
    <?php require APP_ROOT . '/views/admin/sms_patterns_partials/_table.php'; ?>

</main>

<?php
$adminSmsJsVer = APP_VERSION . '.' . (@filemtime(APP_ROOT . '/assets/js/admin-sms.js') ?: 1);
?>
<script src="/assets/js/admin-sms.js?v=<?= $adminSmsJsVer ?>"></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
