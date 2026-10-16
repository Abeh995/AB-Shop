<?php
/**
 * Admin Users & Security Audit Workspace View
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 * Archetype: List (ADMIN-UI-PLAN §3.8)
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="ab-pattern-list">

    <!-- 1. Page Header -->
    <?php
    component('page_header', [
        'title' => $pageTitle ?? 'مدیران سایت و سطوح دسترسی',
        'subtitle' => 'مدیریت حساب‌های مدیران، سطوح دسترسی و ردپای رویدادهای امنیتی',
        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'breadcrumbs' => [
            ['label' => 'داشبورد', 'url' => 'index.php'],
            ['label' => 'تنظیمات', 'url' => 'settings.php'],
            ['label' => 'مدیران سایت'],
        ],
        'actions' => [
            [
                'label'   => 'افزودن مدیر جدید',
                'variant' => 'primary',
                'icon'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
                'attrs'   => ['data-ab-modal-open' => 'modalCreateAdmin'],
            ],
        ],
    ]);
    ?>

    <!-- 2. Bento KPI Grid -->
    <?php require __DIR__ . '/users_partials/_kpis.php'; ?>

    <!-- 3. Interactive Admins Card (Toolbar + Data Table) -->
    <?php ob_start(); ?>
    <div class="ab-stack ab-stack--xs">
        <?php require __DIR__ . '/users_partials/_toolbar.php'; ?>
        <?php require __DIR__ . '/users_partials/_table.php'; ?>
    </div>
    <?php $usersTableCard = ob_get_clean(); ?>
    <?php component('card', [
        'title'    => 'فهرست مدیران سیستم',
        'subtitle' => 'مدیریت و پایش حساب‌های کاربری فعال در پنل مدیریت (' . toPersianDigits((string)count($admins)) . ' حساب)',
        'icon'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'content'  => $usersTableCard,
        'flush'    => true,
    ]); ?>

    <!-- 4. Security Audit Trail Card -->
    <?php ob_start(); ?>
    <?php require __DIR__ . '/users_partials/_audit_trail.php'; ?>
    <?php $auditCardContent = ob_get_clean(); ?>
    <?php component('card', [
        'title'    => 'ردپای امنیتی و لاگ رویدادهای مدیران (Audit Trail)',
        'subtitle' => 'پایش و ثبت بلادرنگ آخرین اقدامات امنیتی و نشست‌های مدیران در سیستم',
        'icon'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'actions'  => [
            render_component('badge', [
                'label' => 'پایش بلادرنگ',
                'state' => 'success',
                'dot'   => true,
            ]),
        ],
        'content'  => $auditCardContent,
        'flush'    => true,
    ]); ?>

</div>

<!-- 5. Interactive Modals -->
<?php require __DIR__ . '/users_partials/_modals.php'; ?>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
