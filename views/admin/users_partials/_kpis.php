<?php
/**
 * Admin Users Partial: Bento KPIs
 * SSoT: Rendered via component('kpi_card', ...)
 */
$lastLoginSub = 'ثبت ورود جدیدی نیست';
$lastLoginVal = '—';
if (!empty($metrics['last_login'])) {
    $lastLoginVal = $metrics['last_login']['username'];
    $lastLoginSub = toPersianDigits(date('Y/m/d H:i', strtotime($metrics['last_login']['last_login_at']))) . ' (' . e($metrics['last_login']['last_login_ip'] ?? '') . ')';
}
?>
<div class="usr-kpi-grid ab-kpi-grid">
    <?php
    component('kpi_card', [
        'title' => 'کل مدیران سیستم',
        'value' => toPersianDigits((string)($metrics['total'] ?? 0)),
        'sub' => toPersianDigits((string)($metrics['active'] ?? 0)) . ' حساب فعال',
        'color' => 'primary',
        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    ]);

    component('kpi_card', [
        'title' => 'مدیران ارشد (Super Admin)',
        'value' => toPersianDigits((string)($metrics['super_admins'] ?? 0)),
        'sub' => 'دسترسی تام و بدون محدودیت',
        'color' => 'amber',
        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    ]);

    component('kpi_card', [
        'title' => 'مدیران عملیاتی',
        'value' => toPersianDigits((string)($metrics['standard_admins'] ?? 0)),
        'sub' => 'مدیریت سفارشات، محصولات، مالی',
        'color' => 'emerald',
        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>',
    ]);

    component('kpi_card', [
        'title' => 'آخرین ورود به سیستم',
        'value' => $lastLoginVal,
        'sub' => $lastLoginSub,
        'color' => 'sky',
        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    ]);
    ?>
</div>
