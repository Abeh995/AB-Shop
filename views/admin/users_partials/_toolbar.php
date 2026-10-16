<?php
/**
 * Admin Users Partial: Action & Filter Toolbar
 * Rendered via component('toolbar', ...)
 */

ob_start();
?>
<div class="ab-cluster" role="group" aria-label="فیلتر نقش و وضعیت">
    <button type="button" class="ab-chip is-active" data-filter="all" aria-pressed="true">همه (<?= toPersianDigits((string)count($admins)) ?>)</button>
    <button type="button" class="ab-chip" data-filter="super_admin" aria-pressed="false">مدیران ارشد</button>
    <button type="button" class="ab-chip" data-filter="admin" aria-pressed="false">مدیران عادی</button>
    <button type="button" class="ab-chip" data-filter="active" aria-pressed="false">فعال</button>
    <button type="button" class="ab-chip" data-filter="inactive" aria-pressed="false">غیرفعال</button>
</div>
<?php
$filterChipsHtml = ob_get_clean();

component('toolbar', [
    'search' => [
        'name'        => 'q',
        'placeholder' => 'جست‌وجو بر اساس نام، نام کاربری، ایمیل یا موبایل...',
        'target'      => '#adminUsersTable',
        'id'          => 'adminSearchInput',
    ],
    'filters' => [
        $filterChipsHtml,
    ],
    'actions' => [
        [
            'label'   => 'افزودن مدیر جدید',
            'variant' => 'primary',
            'size'    => 'sm',
            'icon'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
            'attrs'   => ['data-ab-modal-open' => 'modalCreateAdmin'],
        ],
    ],
]);
