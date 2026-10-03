<?php
/**
 * Shared Admin Navigation Configuration
 * Single Source of Truth for Desktop Sidebar, Mobile Bottom Nav (BNB), and Sub-nav Pills.
 *
 * All 5 core hubs:
 * 1) dashboard (داشبورد)
 * 2) orders (سفارش‌ها)
 * 3) products (محصولات)
 * 4) finance (مالی)
 * 5) settings (تنظیمات)
 */

if (!function_exists('getAdminNavConfig')) {
    function getAdminNavConfig(string $currentPage, int $pendingOrdersCount = 0, int $pendingC2CCount = 0): array
    {
        $isFeatured = !empty($_GET['featured']);

        $config = [
            'dashboard' => [
                'key' => 'dashboard',
                'label' => 'داشبورد',
                'url' => 'index.php',
                'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>',
                'active' => ($currentPage === 'index.php'),
                'badge' => 0,
                'sub_items' => []
            ],
            'orders' => [
                'key' => 'orders',
                'label' => 'سفارش‌ها',
                'url' => 'orders.php',
                'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
                'active' => in_array($currentPage, ['orders.php', 'order_detail.php', 'card_to_card_payments.php'], true),
                'badge' => $pendingOrdersCount,
                'sub_items' => [
                    [
                        'label' => 'همه سفارش‌ها',
                        'url' => 'orders.php',
                        'active' => in_array($currentPage, ['orders.php', 'order_detail.php'], true)
                    ],
                    [
                        'label' => 'فیش‌های کارت‌به‌کارت',
                        'url' => 'card_to_card_payments.php',
                        'active' => ($currentPage === 'card_to_card_payments.php'),
                        'badge' => $pendingC2CCount,
                    ],
                ]
            ],
            'products' => [
                'key' => 'products',
                'label' => 'محصولات',
                'url' => 'products.php',
                'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
                'active' => in_array($currentPage, ['products.php', 'product_edit.php', 'categories.php', 'pricing.php', 'gift_items.php', 'gift_item_edit.php', 'coupons.php', 'tags.php'], true),
                'badge' => 0,
                'sub_items' => [
                    [
                        'label' => 'همه محصولات',
                        'url' => 'products.php',
                        'active' => in_array($currentPage, ['products.php', 'product_edit.php'], true)
                    ],
                    [
                        'label' => 'دسته‌بندی‌ها',
                        'url' => 'categories.php',
                        'active' => ($currentPage === 'categories.php')
                    ],
                    [
                        'label' => 'برچسب‌ها و مشخصات',
                        'url' => 'tags.php',
                        'active' => ($currentPage === 'tags.php')
                    ],
                    [
                        'label' => 'تغییر قیمت گروهی',
                        'url' => 'pricing.php',
                        'active' => ($currentPage === 'pricing.php')
                    ],
                    [
                        'label' => 'کدهای تخفیف',
                        'url' => 'coupons.php',
                        'active' => ($currentPage === 'coupons.php')
                    ],
                    [
                        'label' => 'هدایای سبد و جانبی',
                        'url' => 'gift_items.php',
                        'active' => in_array($currentPage, ['gift_items.php', 'gift_item_edit.php'], true)
                    ],
                ]
            ],
            'finance' => [
                'key' => 'finance',
                'label' => 'مالی',
                'url' => 'finance_dashboard.php',
                'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
                'active' => in_array($currentPage, ['finance_dashboard.php', 'expenses.php', 'expense_edit.php', 'inventory_valuation.php'], true),
                'badge' => 0,
                'sub_items' => [
                    [
                        'label' => 'داشبورد مالی',
                        'url' => 'finance_dashboard.php',
                        'active' => ($currentPage === 'finance_dashboard.php')
                    ],
                    [
                        'label' => 'هزینه‌ها',
                        'url' => 'expenses.php',
                        'active' => in_array($currentPage, ['expenses.php', 'expense_edit.php'], true)
                    ],
                    [
                        'label' => 'ارزش‌گذاری انبار',
                        'url' => 'inventory_valuation.php',
                        'active' => ($currentPage === 'inventory_valuation.php')
                    ],
                ]
            ],
            'settings' => [
                'key' => 'settings',
                'label' => 'تنظیمات',
                'url' => 'settings.php',
                'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
                'active' => in_array($currentPage, ['settings.php', 'appearance.php', 'themes.php', 'theme_edit.php', 'sms_patterns.php', 'sms_pattern_edit.php', 'email_accounts.php', 'emails.php', 'email_read.php', 'email_compose.php', 'shipping_methods.php', 'shipping_method_edit.php', 'users.php', 'diagnostics.php', 'notifications_log.php'], true),
                'badge' => 0,
                'sub_items' => [
                    [
                        'label' => 'تنظیمات عمومی',
                        'url' => 'settings.php',
                        'active' => ($currentPage === 'settings.php')
                    ],
                    [
                        'label' => 'ظاهر و صفحه اصلی',
                        'url' => 'appearance.php',
                        'active' => in_array($currentPage, ['appearance.php', 'themes.php', 'theme_edit.php'], true)
                    ],
                    [
                        'label' => 'روش‌های ارسال',
                        'url' => 'shipping_methods.php',
                        'active' => in_array($currentPage, ['shipping_methods.php', 'shipping_method_edit.php'], true)
                    ],
                    [
                        'label' => 'الگوهای پیامک',
                        'url' => 'sms_patterns.php',
                        'active' => in_array($currentPage, ['sms_patterns.php', 'sms_pattern_edit.php'], true)
                    ],
                    [
                        'label' => 'ایمیل‌ها و وب‌میل',
                        'url' => 'emails.php',
                        'active' => in_array($currentPage, ['emails.php', 'email_accounts.php', 'email_read.php', 'email_compose.php'], true)
                    ],
                ]
            ],
        ];

        if (function_exists('isSuperAdmin') && isSuperAdmin()) {
            $config['settings']['sub_items'][] = [
                'label' => 'مدیران سایت',
                'url' => 'users.php',
                'active' => ($currentPage === 'users.php')
            ];
            $config['settings']['sub_items'][] = [
                'label' => 'عیب‌یابی و لاگ',
                'url' => 'diagnostics.php',
                'active' => in_array($currentPage, ['diagnostics.php', 'notifications_log.php'], true)
            ];
        }

        return $config;
    }
}

if (!function_exists('shouldShowAdminBottomNav')) {
    /**
     * Determines whether the Mobile Bottom Navigation Bar (BNB) should render.
     * BNB is strictly displayed on the 5 primary hubs on mobile/tablet viewports,
     * and omitted on sub-pages/editors to prevent layer collisions with sticky action bars.
     */
    function shouldShowAdminBottomNav(string $currentPage): bool
    {
        $primaryHubs = [
            'index.php',
            'dashboard.php',
            'orders.php',
            'products.php',
            'finance_dashboard.php',
            'settings.php',
        ];

        return in_array($currentPage, $primaryHubs, true);
    }
}
