<?php
/**
 * Diagnostics Partial: Master Tab Navigation
 * SSoT: Rendered via component('nav_tabs', ...)
 */
$currentTab = $activeTab ?? 'health';

$diagTabs = [
    [
        'tab'   => 'health',
        'label' => 'پایش منابع و سرور',
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
    ],
    [
        'tab'   => 'tests',
        'label' => 'تست زنده اتصال سرویس‌ها',
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>',
    ],
    [
        'tab'   => 'notifications',
        'label' => 'لاگ پیامک و ایمیل',
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    ],
    [
        'tab'         => 'errors',
        'label'       => 'خطاهای سیستمی PHP',
        'icon'        => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        'badge'       => !empty($systemErrors['lines_count']) ? toPersianDigits((string)$systemErrors['lines_count']) : null,
        'badge_class' => 'diag-badge danger',
    ],
    [
        'tab'   => 'audit',
        'label' => 'ردپای امنیتی مدیران',
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    ],
];

component('nav_tabs', [
    'tabs'      => $diagTabs,
    'activeTab' => $currentTab,
    'id'        => 'diagTabsNav',
    'class'     => 'diag-tabs-nav',
    'ariaLabel' => 'تب‌های عیب‌یابی و لاگ',
]);
