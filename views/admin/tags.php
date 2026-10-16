<?php
/**
 * Admin Product Tags Management View (views/admin/tags.php)
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 * Archetype: List (ADMIN-UI-PLAN §3.8)
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="ab-pattern-list">

    <!-- 1. Page Header -->
    <?php
    component('page_header', [
        'title'       => $pageTitle ?? 'برچسب‌ها و مشخصات محصولات',
        'subtitle'    => 'دسته‌بندی و برچسب‌گذاری کالاها جهت تسهیل فیلتر و بهبود سئو',
        'icon'        => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r="1.5" fill="currentColor"/></svg>',
        'breadcrumbs' => [
            ['label' => 'داشبورد', 'url' => 'index.php'],
            ['label' => 'محصولات', 'url' => 'products.php'],
            ['label' => 'برچسب‌ها'],
        ],
        'actions'     => [
            [
                'label'   => 'افزودن برچسب جدید',
                'variant' => 'primary',
                'icon'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
                'attrs'   => ['data-ab-modal-open' => 'tagModal', 'type' => 'button'],
            ],
        ],
    ]);
    ?>

    <!-- 2. Alert Messages -->
    <?php if (isset($_GET['saved'])): ?>
        <?php component('alert', ['state' => 'success', 'message' => 'اطلاعات برچسب با موفقیت ذخیره شد.', 'dismissible' => true]); ?>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <?php component('alert', ['state' => 'success', 'message' => 'برچسب با موفقیت حذف گردید.', 'dismissible' => true]); ?>
    <?php endif; ?>
    <?php if (isset($_GET['cleaned'])): ?>
        <?php component('alert', ['state' => 'success', 'message' => toPersianDigits((string)(int)$_GET['cleaned']) . ' برچسب بلااستفاده با موفقیت پاکسازی شدند.', 'dismissible' => true]); ?>
    <?php endif; ?>
    <?php if ($error): ?>
        <?php component('alert', ['state' => 'danger', 'message' => e($error), 'dismissible' => true]); ?>
    <?php endif; ?>

    <!-- 3. Bento KPI Grid -->
    <section class="ab-kpi-grid">
        <?php
        component('kpi_card', [
            'title' => 'کل برچسب‌ها و مشخصات',
            'value' => toPersianDigits((string)$stats['total_tags']),
            'sub'   => 'تگ‌های ثبت‌شده در پایگاه داده',
            'tone'  => 'purple',
            'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r="1.5" fill="currentColor"/></svg>',
        ]);

        component('kpi_card', [
            'title' => 'برچسب‌های متصل به کالا',
            'value' => toPersianDigits((string)$stats['used_tags']),
            'sub'   => 'دارای حداقل یک محصول در کاتالوگ',
            'tone'  => 'emerald',
            'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>',
        ]);

        component('kpi_card', [
            'title' => 'برچسب‌های بدون استفاده',
            'value' => toPersianDigits((string)$stats['unused_tags']),
            'sub'   => 'قابل پاکسازی خودکار',
            'tone'  => 'amber',
            'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/></svg>',
        ]);
        ?>
    </section>

    <!-- 4. Interactive Tags List Card (Toolbar + Data Table) -->
    <?php ob_start(); ?>
    <div class="ab-stack ab-stack--xs">
        <!-- Toolbar -->
        <div class="ab-toolbar">
            <div class="ab-toolbar__lead">
                <form method="get" action="/admin/tags.php" class="ab-toolbar__search">
                    <input type="search" name="q" value="<?= e($search) ?>" 
                           placeholder="جستجوی برچسب یا اسلاگ..." 
                           class="ab-input" autocomplete="off" 
                           data-ab-filter-target="#tagsTable">
                </form>
            </div>
            <div class="ab-toolbar__actions">
                <?php if ($stats['unused_tags'] > 0): ?>
                    <form method="post" action="/admin/tags.php" onsubmit="return confirm('آیا از پاکسازی تمام برچسب‌های بدون استفاده اطمینان دارید؟');" class="ab-inline-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="cleanup">
                        <button type="submit" class="ab-btn ab-btn--outline ab-btn--sm">
                            پاکسازی تگ‌های بلااستفاده (<?= toPersianDigits((string)$stats['unused_tags']) ?>)
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Data Table -->
        <?php
        component('data_table', [
            'id'          => 'tagsTable',
            'mode'        => 'stack',
            'columns'     => [
                ['key' => 'name',           'label' => 'عنوان برچسب',     'priority' => 1],
                ['key' => 'slug',           'label' => 'نامک سئو (Slug)',   'priority' => 2],
                ['key' => 'products_count', 'label' => 'محصولات متصل',    'priority' => 1, 'align' => 'center'],
                ['key' => 'created_at',     'label' => 'تاریخ ایجاد',      'priority' => 3, 'align' => 'center'],
                ['key' => 'actions',        'label' => 'عملیات',          'priority' => 1, 'type' => 'actions', 'align' => 'end'],
            ],
            'rows'        => $tags,
            'row_attrs'   => fn($t) => [
                'data-id'     => $t['id'],
                'data-search' => $t['name'] . ' ' . $t['slug'],
            ],
            'cell'        => [
                'name' => fn($t) => '<span class="ab-font-bold">' . e($t['name']) . '</span>',
                'slug' => fn($t) => '<code class="ab-code ab-num" dir="auto">' . e($t['slug']) . '</code>',
                'products_count' => function($t) {
                    $cnt = (int)$t['products_count'];
                    if ($cnt > 0) {
                        return '<a href="/admin/products.php?tag_id=' . (int)$t['id'] . '" class="ab-badge" data-tone="purple">'
                            . toPersianDigits((string)$cnt) . ' کالا'
                            . '</a>';
                    }
                    return '<span class="ab-text-muted ab-text-xs">بدون محصول</span>';
                },
                'created_at' => fn($t) => '<span class="ab-text-muted ab-text-sm ab-num">' . appDateTime($t['created_at'], 'shamsi') . '</span>',
                'actions' => function($t) {
                    $editBtn = '<button type="button" class="ab-icon-btn ab-icon-btn--sm" title="ویرایش برچسب" data-tag-edit data-id="' . (int)$t['id'] . '" data-name="' . e($t['name']) . '" data-slug="' . e($t['slug']) . '" aria-label="ویرایش">'
                        . '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>'
                        . '</button>';

                    $deleteBtn = '<form method="post" action="/admin/tags.php" onsubmit="return confirm(\'آیا از حذف این برچسب اطمینان دارید؟\');" class="ab-inline-form">'
                        . csrfField()
                        . '<input type="hidden" name="action" value="delete">'
                        . '<input type="hidden" name="id" value="' . (int)$t['id'] . '">'
                        . '<button type="submit" class="ab-icon-btn ab-icon-btn--sm ab-btn--danger" title="حذف برچسب" aria-label="حذف">'
                        . '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>'
                        . '</button>'
                        . '</form>';

                    return '<div class="ab-cluster ab-cluster--xs ab-cluster--end">' . $editBtn . $deleteBtn . '</div>';
                },
            ],
            'empty'       => [
                'title'   => 'هیچ برچسبی یافت نشد',
                'message' => 'می‌توانید با دکمه بالا برچسب جدیدی ثبت کنید.',
            ],
            'sticky_head' => true,
        ]);
        ?>
    </div>
    <?php $tagsTableCard = ob_get_clean(); ?>

    <?php component('card', [
        'title'    => 'فهرست برچسب‌های کاتالوگ',
        'subtitle' => 'مدیریت تگ‌ها، لینک‌های سئو و بازبینی کالاهای وابسته (' . toPersianDigits((string)count($tags)) . ' برچسب)',
        'icon'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r="1.5" fill="currentColor"/></svg>',
        'content'  => $tagsTableCard,
        'flush'    => true,
    ]); ?>

    <!-- 5. Tag Create/Edit Modal -->
    <dialog id="tagModal" class="ab-modal ab-modal--sm" data-ab-modal <?= $editTag ? 'open' : '' ?>>
        <header class="ab-modal__header">
            <h3 class="ab-modal__title" id="tagModalTitle">
                <?= $editTag ? 'ویرایش برچسب' : 'تعریف برچسب جدید' ?>
            </h3>
            <button type="button" class="ab-icon-btn ab-icon-btn--sm ab-modal__close" data-ab-modal-close aria-label="بستن"></button>
        </header>
        <form method="post" action="/admin/tags.php" id="tagForm">
            <div class="ab-modal__body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="tagFormId" value="<?= (int)($editTag['id'] ?? 0) ?>">

                <div class="ab-stack ab-stack--sm">
                    <?php
                    component('form_field', [
                        'name'     => 'name',
                        'id'       => 'tagNameInput',
                        'label'    => 'عنوان برچسب (فارسی) *',
                        'required' => true,
                        'value'    => $editTag['name'] ?? '',
                        'attrs'    => ['placeholder' => 'مثلاً: جوراب نخی یا پاییزی'],
                    ]);

                    component('form_field', [
                        'name'     => 'slug',
                        'id'       => 'tagSlugInput',
                        'label'    => 'نامک در آدرس سئو (Slug)',
                        'dir'      => 'ltr',
                        'value'    => $editTag['slug'] ?? '',
                        'hint'     => 'می‌تواند انگلیسی یا فارسی باشد (اختیاری - خودکار تولید می‌شود).',
                        'attrs'    => ['placeholder' => 'cotton-socks'],
                    ]);
                    ?>
                </div>
            </div>
            <footer class="ab-modal__footer">
                <button type="button" class="ab-btn ab-btn--outline" data-ab-modal-close>انصراف</button>
                <button type="submit" class="ab-btn ab-btn--primary" id="tagSubmitBtn">
                    <?= $editTag ? 'ذخیره تغییرات' : 'ایجاد برچسب' ?>
                </button>
            </footer>
        </form>
    </dialog>

</div>

<script src="<?= e(asset('/assets/js/admin-tags.js')) ?>" defer></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
