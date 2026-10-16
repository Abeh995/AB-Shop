<?php
/**
 * Modern High-Density Admin Coupons & Promotions Management Workstation (views/admin/coupons.php)
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 * Archetype: List (ADMIN-UI-PLAN §3.8)
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="ab-pattern-list">

    <!-- 1. Page Header -->
    <?php
    component('page_header', [
        'title'       => $pageTitle ?? 'کدهای تخفیف و پروموشن‌ها',
        'subtitle'    => 'مدیریت کمپین‌ها، کدهای تخفیف درصدی و نقدی و رهگیری عملکرد فروش',
        'icon'        => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H2v7l6.29 6.29c.94.94 2.48.94 3.42 0l5.58-5.58c.94-.94.94-2.48 0-3.42L11 3H9Z"/><circle cx="6" cy="8" r="1.5" fill="currentColor"/></svg>',
        'breadcrumbs' => [
            ['label' => 'داشبورد', 'url' => 'index.php'],
            ['label' => 'محصولات', 'url' => 'products.php'],
            ['label' => 'کدهای تخفیف'],
        ],
        'actions'     => [
            [
                'label'   => 'افزودن کد تخفیف',
                'variant' => 'primary',
                'icon'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
                'attrs'   => ['data-ab-modal-open' => 'couponModal', 'type' => 'button'],
            ],
        ],
    ]);
    ?>

    <!-- 2. Alert Messages -->
    <?php if (isset($_GET['saved'])): ?>
        <?php component('alert', ['state' => 'success', 'message' => 'کد تخفیف با موفقیت ذخیره و به‌روزرسانی شد.', 'dismissible' => true]); ?>
    <?php endif; ?>
    <?php if (isset($_GET['toggled'])): ?>
        <?php component('alert', ['state' => 'success', 'message' => 'وضعیت فعال‌بودن کد تخفیف با موفقیت تغییر یافت.', 'dismissible' => true]); ?>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <?php component('alert', ['state' => 'success', 'message' => 'کد تخفیف با موفقیت حذف گردید.', 'dismissible' => true]); ?>
    <?php endif; ?>
    <?php if ($error): ?>
        <?php component('alert', ['state' => 'danger', 'message' => e($error), 'dismissible' => true]); ?>
    <?php endif; ?>

    <!-- 3. Bento KPI Metrics -->
    <?php require __DIR__ . '/coupons_partials/_kpis.php'; ?>

    <!-- 4. Interactive Master Coupons Card (Toolbar + Data Table) -->
    <?php ob_start(); ?>
    <div class="ab-stack ab-stack--xs">
        <?php require __DIR__ . '/coupons_partials/_toolbar.php'; ?>
        <?php require __DIR__ . '/coupons_partials/_table.php'; ?>
    </div>
    <?php $couponsTableCard = ob_get_clean(); ?>

    <?php component('card', [
        'title'    => 'فهرست کدهای تخفیف و پروموشن‌ها',
        'subtitle' => 'مدیریت کوپن‌ها، شرایط سبد خرید و پایش نرخ مصرف خریداران (' . toPersianDigits((string)count($coupons)) . ' کد)',
        'icon'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H2v7l6.29 6.29c.94.94 2.48.94 3.42 0l5.58-5.58c.94-.94.94-2.48 0-3.42L11 3H9Z"/><circle cx="6" cy="8" r="1.5" fill="currentColor"/></svg>',
        'content'  => $couponsTableCard,
        'flush'    => true,
    ]); ?>

    <!-- 5. Create / Edit Coupon Modal -->
    <?php require __DIR__ . '/coupons_partials/_modal.php'; ?>

    <!-- 6. Slide-over Analytics Drawer -->
    <?php require __DIR__ . '/coupons_partials/_drawer_stats.php'; ?>

</div>

<script src="<?= e(asset('/assets/js/admin-coupons.js')) ?>" defer></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
