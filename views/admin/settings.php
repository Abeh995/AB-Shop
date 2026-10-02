<?php
/**
 * Admin Store Settings Hub — Master Workspace View
 * Decomposed into modular partials with Desktop Master-Tabs and Hash state sync.
 * Strict Layer Boundaries: Pure presentation, 0 SQL, 0 direct form processing (Rule 7).
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="settings-workspace">

    <!-- 1. System Pulse & Vital KPIs -->
    <?php require __DIR__ . '/settings_partials/_header_stats.php'; ?>

    <!-- 2. Master Navigation Tabs -->
    <?php require __DIR__ . '/settings_partials/_nav_tabs.php'; ?>

    <!-- 3. Tab Panes -->
    <?php require __DIR__ . '/settings_partials/_tab_orders.php'; ?>
    <?php require __DIR__ . '/settings_partials/_tab_payments.php'; ?>
    <?php require __DIR__ . '/settings_partials/_tab_general.php'; ?>
    <?php require __DIR__ . '/settings_partials/_tab_catalog_search.php'; ?>
    <?php require __DIR__ . '/settings_partials/_tab_seo_social.php'; ?>
    <?php require __DIR__ . '/settings_partials/_tab_legal_cms.php'; ?>
    <?php require __DIR__ . '/settings_partials/_tab_workstations.php'; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Master Tab Switcher with Hash Sync
    const tabBtns = document.querySelectorAll('.settings-tab-btn');
    const tabPanes = document.querySelectorAll('.settings-tab-pane');

    function activateTab(tabKey) {
        if (!tabKey) tabKey = 'orders';
        const targetBtn = document.querySelector(`.settings-tab-btn[data-tab="${tabKey}"]`);
        const targetPane = document.getElementById(`pane-${tabKey}`);

        if (targetBtn && targetPane) {
            tabBtns.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));

            targetBtn.classList.add('active');
            targetPane.classList.add('active');
        }
    }

    // Initial load from URL Hash
    const initialHash = window.location.hash.replace('#', '');
    if (initialHash) {
        activateTab(initialHash);
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const tabKey = this.getAttribute('data-tab');
            activateTab(tabKey);
            history.replaceState(null, '', '#' + tabKey);
        });
    });

    // 2. Legal CMS Sub-tab Switcher
    const cmsBtns = document.querySelectorAll('.cms-subtab-btn');
    const cmsPanes = document.querySelectorAll('.cms-pane');

    cmsBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            cmsBtns.forEach(b => {
                b.style.background = 'transparent';
                b.style.color = 'var(--set-text-muted)';
            });
            cmsPanes.forEach(p => p.style.display = 'none');

            this.style.background = 'var(--set-primary)';
            this.style.color = '#fff';

            const targetPane = document.querySelector(this.getAttribute('data-target'));
            if (targetPane) targetPane.style.display = 'block';
        });
    });
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
