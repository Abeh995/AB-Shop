<?php
/**
 * Admin Appearance & Storefront Hub — Master Workspace View
 * Decomposed into modular partials with Desktop Master-Tabs and Hash state sync.
 * Strict Layer Boundaries: Pure presentation, 0 SQL, 0 direct form processing (Rule 7).
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="appearance-workspace">

    <!-- 1. System Pulse & Vital Status Badges -->
    <?php require __DIR__ . '/appearance_partials/_header_stats.php'; ?>

    <!-- 2. Master Navigation Tabs -->
    <?php require __DIR__ . '/appearance_partials/_nav_tabs.php'; ?>

    <!-- 3. Tab Panes -->
    <?php require __DIR__ . '/appearance_partials/_tab_sections.php'; ?>
    <?php require __DIR__ . '/appearance_partials/_tab_hero.php'; ?>
    <?php require __DIR__ . '/appearance_partials/_tab_trust.php'; ?>
    <?php require __DIR__ . '/appearance_partials/_tab_branding.php'; ?>
    <?php require __DIR__ . '/appearance_partials/_tab_announcement.php'; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Master Tab Switcher with Hash Sync
    const tabBtns = document.querySelectorAll('.appearance-tab-btn');
    const tabPanes = document.querySelectorAll('.appearance-tab-pane');

    function activateTab(tabKey) {
        if (!tabKey) tabKey = 'sections';
        const targetBtn = document.querySelector(`.appearance-tab-btn[data-tab="${tabKey}"]`);
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
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
