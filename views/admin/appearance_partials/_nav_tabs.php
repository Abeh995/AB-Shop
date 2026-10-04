<?php
/**
 * Appearance Hub — Master Navigation Tabs
 * SSoT: Rendered via component('nav_tabs', ...)
 */

$appearanceTabs = [
    [
        'tab'   => 'sections',
        'label' => '۱. چیدمان و سکشن‌های صفحه اصلی',
        'icon'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/></svg>',
    ],
    [
        'tab'   => 'hero',
        'label' => '۲. بنر پروموشن ویژه (Hero Banner)',
        'icon'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    ],
    [
        'tab'   => 'trust',
        'label' => '۳. مزایای خرید و اعتمادسازی (Trust Bar)',
        'icon'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    ],
    [
        'tab'   => 'branding',
        'label' => '۴. هویت بصری، لوگو و تم',
        'icon'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>',
    ],
    [
        'tab'   => 'announcement',
        'label' => '۵. نوار اعلان و فوتر',
        'icon'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>',
    ],
];

component('nav_tabs', [
    'tabs'      => $appearanceTabs,
    'activeTab' => 'sections',
    'class'     => 'appearance-tabs-nav',
    'btnClass'  => 'appearance-tab-btn',
    'ariaLabel' => 'تب‌های تنظیمات ظاهر',
]);
