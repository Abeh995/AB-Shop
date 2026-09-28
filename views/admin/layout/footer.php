    </main>
</div>

<!-- Admin Mobile/Tablet Bottom Navigation Bar (BNB) — Rendered strictly on 5 primary hubs -->
<?php
require_once APP_ROOT . '/views/admin/layout/nav_config.php';
$showBottomNav = function_exists('shouldShowAdminBottomNav') ? shouldShowAdminBottomNav($currentPage) : true;
?>
<?php if ($showBottomNav): ?>
    <?php $bottomNavConfig = getAdminNavConfig($currentPage, $pendingOrdersCount ?? 0); ?>
    <nav class="admin-bottom-nav" aria-label="ناوبری اصلی پنل ادمین">
        <?php foreach ($bottomNavConfig as $item): ?>
            <a href="<?= e($item['url']) ?>" class="admin-bottom-nav-item <?= $item['active'] ? 'active' : '' ?>">
                <div class="nav-icon-wrap">
                    <?= $item['icon'] ?>
                    <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
                        <span class="nav-badge"><?= (int) $item['badge'] ?></span>
                    <?php endif; ?>
                </div>
                <span class="nav-label"><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
<?php endif; ?>

<?php
$mainJsVer  = APP_VERSION . '.' . (@filemtime(APP_ROOT . '/assets/js/main.js') ?: 1);
$aioJsVer   = APP_VERSION . '.' . (@filemtime(APP_ROOT . '/assets/js/admin-image-optimizer.js') ?: 1);
$adminJsVer = APP_VERSION . '.' . (@filemtime(APP_ROOT . '/assets/js/admin.js') ?: 1);
?>
<script src="/assets/js/main.js?v=<?= $mainJsVer ?>"></script>
<script src="/assets/js/admin-image-optimizer.js?v=<?= $aioJsVer ?>"></script>
<script src="/assets/js/admin.js?v=<?= $adminJsVer ?>"></script>
</body>
</html>
