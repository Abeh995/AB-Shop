<?php
/**
 * Admin System Health & Diagnostic Studio Master View
 *
 * Coordinates Host Quotas, Connectivity Suite, Notifications Log,
 * System PHP Error Viewer, and Audit Trail.
 * View Purity: 0 SQL, 0 $_POST, pure presentation (Rule 7).
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="diag-workspace">

    <!-- 1. System Pulse & Vital Gauges -->
    <?php require __DIR__ . '/diagnostics_partials/_header_pulse.php'; ?>

    <!-- 2. Master Navigation Tabs -->
    <?php require __DIR__ . '/diagnostics_partials/_nav_tabs.php'; ?>

    <!-- 3. Tab Panes -->
    <?php require __DIR__ . '/diagnostics_partials/_tab_server_health.php'; ?>
    <?php require __DIR__ . '/diagnostics_partials/_tab_connectivity.php'; ?>
    <?php require __DIR__ . '/diagnostics_partials/_tab_notifications_log.php'; ?>
    <?php require __DIR__ . '/diagnostics_partials/_tab_system_errors.php'; ?>
    <?php require __DIR__ . '/diagnostics_partials/_tab_audit_trail.php'; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabBtns = document.querySelectorAll('.diag-tab-btn');
    const tabPanes = document.querySelectorAll('.diag-tab-pane');

    function activateTab(tabKey) {
        if (!tabKey) tabKey = 'health';
        const targetBtn = document.querySelector(`.diag-tab-btn[data-tab="${tabKey}"]`);
        const targetPane = document.getElementById(`pane-${tabKey}`);

        if (targetBtn && targetPane) {
            tabBtns.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));

            targetBtn.classList.add('active');
            targetPane.classList.add('active');
        }
    }

    // Initial load from URL Hash or server-side activeTab
    const initialHash = window.location.hash.replace('#', '');
    if (initialHash) {
        activateTab(initialHash);
    } else {
        const serverTab = <?= json_encode($activeTab ?? 'health') ?>;
        activateTab(serverTab);
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const tabKey = this.getAttribute('data-tab');
            activateTab(tabKey);
            history.replaceState(null, '', '#' + tabKey);
        });
    });
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
