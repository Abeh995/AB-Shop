<?php
/**
 * UI Kit & Component Showcase Master View (views/admin/ui-kit.php)
 * Pure presentation template: 0 SQL, 0 $_POST (Rule 7).
 *
 * Demonstrates every component across all states, tones, breakpoints, and edge cases.
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="ab-pattern-list">

    <!-- 0. Header with Breadcrumbs & Actions -->
    <?php component('page_header', [
        'title'       => 'کتابخانه مؤلفه‌ها و کیت طراحی (UI Kit v2)',
        'subtitle'    => 'مرجع یکپارچه کامپوننت‌های پنل مدیریت، مقیاس واکنش‌گرایی، تنوع رنگ‌ها و حالات دسترسی‌پذیری',
        'icon'        => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>',
        'breadcrumbs' => [
            ['label' => 'پیشخوان', 'url' => 'index.php'],
            ['label' => 'عیب‌یابی', 'url' => 'diagnostics.php'],
            ['label' => 'کیت رابط کاربری', 'url' => null],
        ],
        'actions'     => [
            [
                'label'   => 'بازگشت به عیب‌یابی',
                'variant' => 'secondary',
                'href'    => 'diagnostics.php',
            ],
            [
                'label'   => 'تغییر به تن رنگی تصادفی',
                'variant' => 'outline',
                'href'    => '?tone=' . urlencode($tones[array_rand($tones)]),
            ],
        ],
    ]); ?>


    <!-- 1. Domain Tones & Color Palette System -->
    <?php ob_start(); ?>
    <div class="ab-stack">
        <p class="ab-text-sm ab-text-muted">
            هر بخش از پنل با استفاده از شناسه <code>data-tone</code> یک هویت بصری مشخص پیدا می‌کند؛ در حالی که رنگ‌های معنایی (موفقیت، هشدار، خطا، اطلاع) همیشه مستقل و ثابت باقی می‌مانند.
        </p>
        <div class="ab-cluster ab-cluster--sm">
            <?php foreach ($tones as $tName): ?>
                <a href="?tone=<?= e($tName) ?>" class="ab-chip <?= ($activeTone === $tName) ? 'is-active' : '' ?>" data-tone="<?= e($tName) ?>" aria-pressed="<?= ($activeTone === $tName) ? 'true' : 'false' ?>">
                    <span class="ab-badge-dot"></span>
                    تن <?= e($tName) ?><?= ($activeTone === $tName) ? ' (فعال)' : '' ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php $toneContent = ob_get_clean(); ?>

    <?php component('card', [
        'title'    => '۱. سامانه تن‌های رنگی دامنه (Domain Tones)',
        'subtitle' => 'تن فعال فعلی: ' . e($activeTone),
        'content'  => $toneContent,
    ]); ?>


    <!-- 2. Master Navigation Tabs -->
    <?php component('nav_tabs', [
        'id'        => 'uiKitDemoTabs',
        'tabs'      => [
            ['tab' => 'all', 'label' => 'همه اجزا', 'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>', 'active' => true],
            ['tab' => 'buttons', 'label' => 'دکمه‌ها و فرم‌ها', 'badge' => '۲۴'],
            ['tab' => 'tables', 'label' => 'جداول و کارت‌ها', 'badge' => '۴'],
            ['tab' => 'overlays', 'label' => 'دیالوگ و کشوها'],
        ],
        'ariaLabel' => 'دسته‌بندی‌های کیت طراحی',
    ]); ?>


    <!-- 3. KPI Cards & Bento Grid (All Tones & Trends) -->
    <?php component('kpi_grid', [
        'cards' => [
            [
                'title' => 'فروش امروز (تن فعال)',
                'value' => '۴,۸۵۰,۰۰۰',
                'unit'  => 'تومان',
                'trend' => 'up',
                'sub'   => 'رشد ۱۲٪ نسبت به دیروز',
                'tone'  => $activeTone,
                'href'  => '#',
                'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
            ],
            [
                'title' => 'سفارش‌های جدید',
                'value' => '۲۸',
                'unit'  => 'سفارش',
                'trend' => 'up',
                'sub'   => '۴ فیش در انتظار بررسی',
                'tone'  => 'emerald',
                'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>',
            ],
            [
                'title' => 'موجودی رو به اتمام',
                'value' => '۳',
                'unit'  => 'کالا',
                'trend' => 'down',
                'sub'   => 'نیاز به تأمین فوری',
                'tone'  => 'rose',
                'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            ],
            [
                'title' => 'مشتریان جدید',
                'value' => '۱۴',
                'unit'  => 'کاربر',
                'trend' => 'flat',
                'sub'   => 'مشابه میانگین ماهانه',
                'tone'  => 'blue',
                'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            ],
        ],
    ]); ?>

    <div class="ab-m-block-md">
        <h4 class="ab-text-sm ab-text-muted">کارت شاخص منفرد (Standalone KPI Card):</h4>
        <div>
            <?php component('kpi_card', [
                'title' => 'شاخص انفرادی (Emerald Tone)',
                'value' => '۹۹.۴٪',
                'unit'  => 'پایداری',
                'trend' => 'up',
                'sub'   => 'بررسی ۳۰ روزه سیستم',
                'tone'  => 'emerald',
            ]); ?>
        </div>
    </div>

    <!-- 4. Buttons, Sizes & Interactive States -->
    <?php ob_start(); ?>
    <div class="ab-stack ab-stack--md">
        <!-- Variants -->
        <div>
            <h4 class="ab-text-sm ab-text-muted">گونه‌های ظاهری دکمه (Variants):</h4>
            <div class="ab-cluster ab-cluster--sm">
                <?php component('button', ['label' => 'اصلی (Primary)', 'variant' => 'primary']); ?>
                <?php component('button', ['label' => 'ثانویه (Secondary)', 'variant' => 'secondary']); ?>
                <?php component('button', ['label' => 'خط دور (Outline)', 'variant' => 'outline']); ?>
                <?php component('button', ['label' => 'شبح (Ghost)', 'variant' => 'ghost']); ?>
                <?php component('button', ['label' => 'هشدار/خطر (Danger)', 'variant' => 'danger']); ?>
                <?php component('button', ['label' => 'موفقیت (Success)', 'variant' => 'success']); ?>
            </div>
        </div>

        <!-- Sizes & Icons -->
        <div>
            <h4 class="ab-text-sm ab-text-muted">اندازه‌ها و آیکون‌ها:</h4>
            <div class="ab-cluster ab-cluster--sm">
                <?php component('button', [
                    'label'   => 'کوچک (sm)',
                    'variant' => 'primary',
                    'size'    => 'sm',
                    'icon'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>',
                ]); ?>
                <?php component('button', [
                    'label'   => 'متوسط استاندارد (md)',
                    'variant' => 'secondary',
                    'size'    => 'md',
                ]); ?>
                <?php component('button', [
                    'label'   => 'بزرگ (lg)',
                    'variant' => 'primary',
                    'size'    => 'lg',
                ]); ?>
                <?php component('button', [
                    'label'     => 'آیکون تنها',
                    'icon_only' => true,
                    'variant'   => 'secondary',
                    'icon'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
                ]); ?>
            </div>
        </div>

        <!-- States: Disabled & Busy -->
        <div>
            <h4 class="ab-text-sm ab-text-muted">حالات تعاملی خاص (غیرفعال، در حال بارگذاری):</h4>
            <div class="ab-cluster ab-cluster--sm">
                <?php component('button', ['label' => 'غیرفعال (Disabled)', 'disabled' => true]); ?>
                <?php component('button', ['label' => 'در حال بارگذاری...', 'busy' => true]); ?>
                <?php component('button', ['label' => 'لینک دکمه‌ای', 'href' => 'index.php', 'variant' => 'outline']); ?>
            </div>
        </div>
    </div>
    <?php $btnContent = ob_get_clean(); ?>

    <?php component('card', [
        'title'   => '۲. خانواده دکمه‌ها و حالات تعاملی',
        'content' => $btnContent,
    ]); ?>


    <!-- 5. Form Fields, Controls & Intrinsic Grids -->
    <?php ob_start(); ?>
    <div class="ab-stack ab-stack--md">
        <div class="ab-form-grid">
            <?php component('form_field', [
                'name'        => 'demo_title',
                'label'       => 'عنوان کالا',
                'placeholder' => 'مثال: جوراب مچی طرح کهکشان',
                'required'    => true,
                'hint'        => 'نام محصول در فروشگاه و فاکتورها درج می‌شود.',
            ]); ?>

            <?php component('form_field', [
                'name'        => 'demo_price',
                'label'       => 'قیمت فروش',
                'type'        => 'number',
                'value'       => '68000',
                'unit'        => 'تومان',
                'dir'         => 'ltr',
                'required'    => true,
            ]); ?>

            <?php component('form_field', [
                'name'    => 'demo_category',
                'label'   => 'دسته‌بندی',
                'type'    => 'select',
                'options' => [
                    'college' => 'جوراب کالج',
                    'crew'    => 'جوراب ساقدار',
                    'ankle'   => 'جوراب مچی',
                    'sports'  => 'جوراب ورزشی',
                ],
            ]); ?>

            <?php component('form_field', [
                'name'     => 'demo_error_field',
                'label'    => 'کد شناسه محصول (SKU)',
                'value'    => 'INVALID_CODE',
                'dir'      => 'ltr',
                'error'    => 'شناسه وارد شده معتبر نیست یا تکراری است.',
                'required' => true,
            ]); ?>

            <?php component('form_field', [
                'name'     => 'demo_disabled_field',
                'label'    => 'موجودی انبار مرکزی (غیرقابل ویرایش مستقیم)',
                'value'    => '۱۲۰ جفت',
                'disabled' => true,
            ]); ?>

            <?php component('form_field', [
                'name'    => 'demo_switch',
                'label'   => 'نمایش در ویترین صفحه اصلی',
                'type'    => 'switch',
                'checked' => true,
            ]); ?>

            <?php component('form_field', [
                'name'    => 'demo_check',
                'label'   => 'شامل ارسال رایگان برای سفارش‌های بالای ۳۰۰ هزار تومان',
                'type'    => 'check',
                'checked' => true,
            ]); ?>
        </div>

        <?php component('form_field', [
            'name'        => 'demo_textarea',
            'label'       => 'توضیحات و مشخصات بافت',
            'type'        => 'textarea',
            'placeholder' => 'جزئیات جنس نخ، کشسانی و نحوه شست‌وشو...',
            'rows'        => 3,
        ]); ?>
    </div>
    <?php $formContent = ob_get_clean(); ?>

    <?php component('card', [
        'title'   => '۳. کنترل‌های ورودی فرم (Fields & Form Controls)',
        'content' => $formContent,
    ]); ?>


    <!-- 6. Data Table in Stack Mode with Priority Columns -->
    <?php ob_start(); ?>
    <div class="ab-stack">
        <?php component('toolbar', [
            'search'  => [
                'name'        => 'q_demo',
                'placeholder' => 'فیلتر زنده در محصولات جدول...',
                'target'      => '#demoProductTable',
            ],
            'actions' => [
                [
                    'label'   => 'افزودن سطر تستی',
                    'variant' => 'primary',
                    'size'    => 'sm',
                ],
            ],
        ]); ?>

        <?php component('data_table', [
            'id'        => 'demoProductTable',
            'mode'      => 'stack',
            'columns'   => [
                ['key' => 'id',         'label' => 'شناسه',      'priority' => 3, 'type' => 'number'],
                ['key' => 'code',       'label' => 'کد انبار',    'priority' => 2],
                ['key' => 'name',       'label' => 'نام محصول',   'priority' => 1],
                ['key' => 'category',   'label' => 'دسته',       'priority' => 3],
                ['key' => 'price',      'label' => 'قیمت',       'priority' => 1, 'type' => 'price', 'align' => 'end'],
                ['key' => 'status',     'label' => 'وضعیت',      'priority' => 1, 'type' => 'badge'],
                ['key' => 'date',       'label' => 'تاریخ ورود', 'priority' => 3, 'type' => 'date'],
            ],
            'rows'      => $sampleTableRows,
            'row_attrs' => fn($r) => ['data-search' => $r['search'], 'data-id' => $r['id']],
        ]); ?>

        <?php component('pagination', [
            'page'     => 1,
            'pages'    => 5,
            'base_url' => '?page=',
            'total'    => 96,
            'per_page' => 20,
        ]); ?>
    </div>
    <?php $tableContent = ob_get_clean(); ?>

    <?php component('card', [
        'title'    => '۴. جدول داده هوشمند با تغییر حالت واکنش‌گرا (Stack Mode)',
        'subtitle' => 'در صفحه‌های باریک (موبایل/تبلت)، سطرها تبدیل به کارت‌های مستقل می‌شوند و عناوین ستون‌ها با data-label نمایش می‌یابند.',
        'flush'    => true,
        'content'  => $tableContent,
    ]); ?>


    <!-- 7. Badges, Chips, Alerts & Progress Meters -->
    <?php ob_start(); ?>
    <div class="ab-stack ab-stack--md">
        <!-- Badges -->
        <div>
            <h4 class="ab-text-sm ab-text-muted">نشان‌های وضعیت (Badges with data-state):</h4>
            <div class="ab-cluster ab-cluster--sm">
                <?php component('badge', ['label' => 'تأیید شده', 'state' => 'success', 'dot' => true]); ?>
                <?php component('badge', ['label' => 'لغو گردید', 'state' => 'danger', 'dot' => true]); ?>
                <?php component('badge', ['label' => 'در انتظار پرداخت', 'state' => 'warning', 'dot' => true]); ?>
                <?php component('badge', ['label' => 'ارسال پستی', 'state' => 'info']); ?>
                <?php component('badge', ['label' => 'پیش‌نویس', 'state' => 'muted']); ?>
                <?php component('badge', ['label' => 'تن دامنه', 'state' => 'primary']); ?>
            </div>
        </div>

        <!-- Alerts -->
        <div>
            <h4 class="ab-text-sm ab-text-muted">اعلان‌های بنری (Alerts):</h4>
            <div class="ab-stack ab-stack--sm">
                <?php component('alert', [
                    'state'       => 'success',
                    'title'       => 'پرداخت با موفقیت ثبت شد',
                    'message'     => 'سفارش شماره ۱۰۴۴ آماده بسته‌بندی و ارسال به انبار است.',
                    'dismissible' => true,
                ]); ?>
                <?php component('alert', [
                    'state'       => 'warning',
                    'message'     => 'توجه: حجم پایگاه داده به ۷۵٪ سقف مجاز هاست رسیده است.',
                    'dismissible' => false,
                ]); ?>
                <?php component('alert', [
                    'state'       => 'danger',
                    'message'     => 'خطا در ارتباط با وب‌سرویس پیامک! لطفاً اعتبار پنل را بررسی کنید.',
                    'dismissible' => true,
                ]); ?>
            </div>
        </div>

        <!-- Progress Meters -->
        <div>
            <h4 class="ab-text-sm ab-text-muted">نوارهای پیشرفت و ظرفیت (Progress):</h4>
            <div class="ab-stack ab-stack--sm">
                <?php component('progress', [
                    'label'        => 'مصرف سهمیه دیتابیس (۲۰۰ مگابایت)',
                    'value'        => 68,
                    'max'          => 100,
                    'tone'         => 'brand',
                    'show_percent' => true,
                ]); ?>
                <?php component('progress', [
                    'label'        => 'پیشرفت آماده‌سازی سفارش‌های امروز',
                    'value'        => 92,
                    'max'          => 100,
                    'tone'         => 'emerald',
                    'show_percent' => true,
                ]); ?>
            </div>
        </div>
    </div>
    <?php $feedbackContent = ob_get_clean(); ?>

    <?php component('card', [
        'title'   => '۵. نشان‌ها، اعلان‌های پیام و میله‌های پیشرفت',
        'content' => $feedbackContent,
    ]); ?>


    <!-- 8. Native Dialogs, Drawers & Overlays -->
    <?php ob_start(); ?>
    <div class="ab-stack">
        <p class="ab-text-sm ab-text-muted">
            مودال‌ها و کشوهای جانبی بر پایه عنصر استاندارد HTML5 <code>&lt;dialog&gt;</code> ساخته شده‌اند و در بالاترین لایه مرورگر (Top Layer) بدون تداخل با Container Queries قرار می‌گیرند.
        </p>
        <div class="ab-cluster ab-cluster--sm">
            <?php component('button', [
                'label'   => 'باز کردن مودال متوسط',
                'variant' => 'primary',
                'attrs'   => ['data-ab-modal-open' => 'demoModalMd'],
            ]); ?>
            <?php component('button', [
                'label'   => 'باز کردن مودال کوچک',
                'variant' => 'secondary',
                'attrs'   => ['data-ab-modal-open' => 'demoModalSm'],
            ]); ?>
            <?php component('button', [
                'label'   => 'باز کردن کشوی جانبی (Drawer)',
                'variant' => 'outline',
                'attrs'   => ['data-ab-modal-open' => 'demoDrawer'],
            ]); ?>
            <?php component('button', [
                'label'   => 'نمایش اعلان موقت (Toast)',
                'variant' => 'ghost',
                'attrs'   => ['onclick' => "AB.toast('عملیات با موفقیت در سیستم اعمال گردید.', 'success')"],
            ]); ?>
        </div>
    </div>
    <?php $modalTriggerContent = ob_get_clean(); ?>

    <?php component('card', [
        'title'   => '۶. دیالوگ‌های بومی و کشوی جانبی (<dialog> Modals & Drawers)',
        'content' => $modalTriggerContent,
    ]); ?>


    <!-- 9. Split View Workstation (.ab-split) -->
    <?php ob_start(); ?>
    <div class="ab-split" id="demoSplitWorkspace" data-split-view="master">
        <div class="ab-split__master">
            <div class="ab-toolbar">
                <span class="ab-text-sm ab-text-muted">فهرست پیام‌ها (Master)</span>
            </div>
            <div class="ab-stack ab-stack--xs ab-card__body">
                <button type="button" class="ab-btn ab-btn--ghost is-active" data-ab-split-select="pane-1">
                    <span>سفارش شماره ۱۰۸۸</span>
                </button>
                <button type="button" class="ab-btn ab-btn--ghost" data-ab-split-select="pane-2">
                    <span>سفارش شماره ۱۰۸۹</span>
                </button>
            </div>
        </div>
        <div class="ab-split__detail">
            <div class="ab-toolbar">
                <button type="button" class="ab-btn ab-btn--sm ab-btn--secondary ab-split__back" data-ab-split-back>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                    بازگشت به فهرست
                </button>
                <span class="ab-text-sm ab-text-muted">جزئیات آیتم انتخابی (Detail)</span>
            </div>
            <div class="ab-card__body" data-split-pane="pane-1">
                <h4>جزئیات سفارش ۱۰۸۸</h4>
                <?php component('definition_list', [
                    'items' => [
                        ['label' => 'نام خریدار:', 'value' => 'علی رضایی'],
                        ['label' => 'شماره تماس:', 'value' => '۰۹۱۲۳۴۵۶۷۸۹'],
                        ['label' => 'مبلغ کل:', 'value' => '۲۴۰,۰۰۰ تومان'],
                    ],
                ]); ?>
            </div>
            <div class="ab-card__body" data-split-pane="pane-2" hidden>
                <h4>جزئیات سفارش ۱۰۸۹</h4>
                <?php component('definition_list', [
                    'items' => [
                        ['label' => 'نام خریدار:', 'value' => 'فاطمه موسوی'],
                        ['label' => 'شماره تماس:', 'value' => '۰۹۳۵۰۰۰۱۱۲۲'],
                        ['label' => 'مبلغ کل:', 'value' => '۱۸۵,۰۰۰ تومان'],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <?php $splitContent = ob_get_clean(); ?>

    <?php component('card', [
        'title'    => '۷. چیدمان نمای دوگانه ایستگاه کاری (.ab-split)',
        'subtitle' => 'در نمایشگرهای کوچک یا کانتینرهای باریک، فهرست و جزئیات خودبه‌خود تبدیل به تک‌پنجره با دکمه بازگشت می‌شوند.',
        'flush'    => true,
        'content'  => $splitContent,
    ]); ?>


    <!-- 10. Stress Tests & Edge Cases -->
    <?php ob_start(); ?>
    <div class="ab-stack ab-stack--md">
        <div>
            <h4 class="ab-text-sm ab-text-muted">الف) متن طولانی بدون فاصله (Unbroken Token Overflow Check):</h4>
            <div class="ab-badge ab-badge-info">
                https://ab-socks.ir/catalog/deep-inventory/item-sku-very-long-unbroken-slug-identifier-test-overflow-boundary-check-20261007
            </div>
        </div>

        <div>
            <h4 class="ab-text-sm ab-text-muted">ب) متن طولانی زبان فارسی (Long RTL Paragraph Check):</h4>
            <p class="ab-text-sm">
                فروشگاه اینترنتی تخصصی جوراب ای‌بی با هدف ارائه محصولات باکیفیت و استاندارد با نخ‌های پنبه‌ای و ضد حساسیت طراحی شده است. سیستم حاضر با بهره‌گیری از معماری کانتینری و طراحی بدون وابستگی به فریم‌ورک‌های سنگین، عملکرد پرسرعت و بهینه‌ای را در هاست‌های اشتراکی تضمین می‌کند.
            </p>
        </div>

        <div>
            <h4 class="ab-text-sm ab-text-muted">ج) آزمون جدول خالی (Zero Rows Empty State):</h4>
            <?php component('data_table', [
                'columns' => [
                    ['key' => 'id', 'label' => 'کد'],
                    ['key' => 'title', 'label' => 'عنوان'],
                ],
                'rows'    => [],
                'empty'   => [
                    'title'   => 'هیچ آیتمی یافت نشد',
                    'message' => 'این جدول در حال حاضر داده‌ای ندارد.',
                ],
            ]); ?>
        </div>

        <div>
            <h4 class="ab-text-sm ab-text-muted">د) وضعیت خالی مستقل (Standalone Empty State):</h4>
            <?php component('empty_state', [
                'title'   => 'هیچ رکوردی یافت نشد',
                'message' => 'می‌توانید با دکمه زیر رکورد جدیدی اضافه نمایید.',
                'action'  => [
                    'label'   => 'افزودن رکورد جدید',
                    'href'    => '#',
                    'variant' => 'primary',
                ],
            ]); ?>
        </div>

        <div>
            <h4 class="ab-text-sm ab-text-muted">د) آزمون ۱۲ ستون در حالت افقی (Scroll Mode Multi-Column):</h4>
            <?php component('data_table', [
                'id'      => 'stress12Table',
                'mode'    => 'scroll',
                'columns' => $stress12Cols,
                'rows'    => $stress12Rows,
            ]); ?>
        </div>
    </div>
    <?php $stressContent = ob_get_clean(); ?>

    <?php component('card', [
        'title'   => '۸. آزمون‌های استرس و لبه‌های نامتعارف (Stress Tests & Edge Cases)',
        'content' => $stressContent,
    ]); ?>

</div><!-- /.ab-pattern-list -->


<!-- Modal sm Instance -->
<?php component('modal', [
    'id'             => 'demoModalSm',
    'title'          => 'حذف آیتم',
    'size'           => 'sm',
    'content'        => '<p class="ab-text-sm">آیا از حذف این رکورد اطمینان دارید؟ این عملیات قابل بازگشت نیست.</p>',
    'footer_actions' => [
        ['label' => 'انصراف', 'variant' => 'secondary', 'attrs' => ['data-ab-modal-close' => '']],
        ['label' => 'بله، حذف شود', 'variant' => 'danger'],
    ],
]); ?>

<!-- Modal md Instance -->
<?php component('modal', [
    'id'             => 'demoModalMd',
    'title'          => 'ویرایش سریع مشخصات',
    'size'           => 'md',
    'content'        => render_component('form_field', [
        'name'        => 'modal_item_title',
        'label'       => 'نام جدید کالا',
        'value'       => 'جوراب ساق‌کوتاه پنبه‌ای طرح ماری',
        'required'    => true,
    ]),
    'footer_actions' => [
        ['label' => 'بستن', 'variant' => 'secondary', 'attrs' => ['data-ab-modal-close' => '']],
        ['label' => 'ذخیره تغییرات', 'variant' => 'primary'],
    ],
]); ?>

<!-- Drawer Instance -->
<?php component('drawer', [
    'id'             => 'demoDrawer',
    'title'          => 'پرونده و گزارش جامع سفارش',
    'side'           => 'end',
    'content'        => render_component('definition_list', [
        'items' => [
            ['label' => 'شماره سفارش:', 'value' => '#10992'],
            ['label' => 'وضعیت مالی:', 'value' => 'پرداخت موفق'],
            ['label' => 'کد رهگیری پستی:', 'value' => '۳۹۴۸۲۰۰۲۹۱۸۲۳'],
            ['label' => 'آدرس تحویل:', 'value' => 'تهران، میدان ونک، خیابان ملاصدرا، پلاک ۱۲'],
        ],
    ]),
    'footer_actions' => [
        ['label' => 'بستن کشو', 'variant' => 'secondary', 'attrs' => ['data-ab-modal-close' => '']],
    ],
]); ?>

<!-- Sticky Save Bar Preview -->
<?php component('savebar', [
    'primary'    => ['label' => 'ذخیره تمام تغییرات'],
    'secondary'  => ['label' => 'صرف‌نظر'],
    'dirty_hint' => 'پیش‌نمایش نوار شناور ذخیره در انتهای صفحه',
]); ?>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
