<?php
/**
 * Master Products Catalog Data Table & Pagination (views/admin/products_partials/_table.php)
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 */

$returnUrl = 'products.php?' . http_build_query($_GET);

component('data_table', [
    'id'          => 'productsTable',
    'mode'        => 'stack',
    'selectable'  => true,
    'sticky_head' => true,
    'columns'     => [
        ['key' => 'image',       'label' => 'تصویر',             'priority' => 1, 'align' => 'center'],
        ['key' => 'name',        'label' => 'مشخصات و عنوان کالا', 'priority' => 1],
        ['key' => 'sku',         'label' => 'کد SKU',            'priority' => 2],
        ['key' => 'category',    'label' => 'دسته‌بندی',         'priority' => 2],
        ['key' => 'price',       'label' => 'قیمت فروش',         'priority' => 1],
        ['key' => 'cost_margin', 'label' => 'بهای خرید و سود',   'priority' => 2],
        ['key' => 'stock',       'label' => 'وضعیت انبار',       'priority' => 1],
        ['key' => 'is_featured', 'label' => 'ویترین',            'priority' => 3, 'align' => 'center'],
        ['key' => 'is_active',   'label' => 'وضعیت',             'priority' => 1, 'align' => 'center'],
        ['key' => 'actions',     'label' => 'عملیات',            'priority' => 1, 'type' => 'actions', 'align' => 'end'],
    ],
    'rows'        => $catalog['items'],
    'row_attrs'   => fn($p) => [
        'id'          => 'prod-row-' . (int)$p['id'],
        'class'       => 'prod-row',
        'data-id'     => (int)$p['id'],
        'data-search' => $p['name'] . ' ' . ($p['sku'] ?? '') . ' ' . ($p['category_name'] ?? ''),
    ],
    'cell'        => [
        'image' => function($p) {
            $pid = (int)$p['id'];
            $img = !empty($p['image']) ? UPLOAD_URL . e($p['image']) : '/assets/img/placeholder-sock.svg';
            return '<button type="button" class="prod-thumb-box btn-inspect" data-id="' . $pid . '" title="مشاهده پیش‌نمایش در کشو" aria-label="پیش‌نمایش در کشو">'
                . '<img src="' . $img . '" class="prod-thumb-img" alt="" loading="lazy" width="44" height="44">'
                . '</button>';
        },

        'name' => function($p) {
            $pid = (int)$p['id'];
            $hasDiscount = !empty($p['discount_price']) && $p['discount_price'] < $p['price'];
            $variantCount = (int)$p['variant_count'];

            $badges = '';
            if (!empty($p['is_featured'])) {
                $badges .= '<span class="ab-badge" data-tone="amber">ویژه</span>';
            }
            if ($hasDiscount) {
                $badges .= '<span class="ab-badge" data-tone="rose">تخفیف‌دار</span>';
            }
            if ($variantCount > 0) {
                $badges .= '<span class="ab-badge" data-tone="purple">' . toPersianDigits((string)$variantCount) . ' واریانت</span>';
            }

            $titleLink = '<a href="#" class="prod-title-link ab-font-bold" data-id="' . $pid . '" title="بررسی سریع مشخصات کالا">' . e($p['name']) . '</a>';
            $badgesRow = !empty($badges) ? '<div class="ab-cluster ab-cluster--xs">' . $badges . '</div>' : '';

            return '<div class="ab-stack ab-stack--xs">' . $titleLink . $badgesRow . '</div>';
        },

        'sku' => function($p) {
            if (!empty($p['sku'])) {
                return '<div class="ab-cluster ab-cluster--xs">'
                    . '<code class="ab-code prod-sku-chip ab-num" data-sku="' . e($p['sku']) . '" title="کلیک برای کپی SKU" dir="ltr">' . e($p['sku']) . '</code>'
                    . '</div>';
            }
            return '<span class="ab-text-muted ab-text-sm">—</span>';
        },

        'category' => function($p) {
            if (!empty($p['category_name'])) {
                return '<span class="ab-badge" data-tone="slate">' . e($p['category_name']) . '</span>';
            }
            return '<span class="ab-text-muted ab-text-sm">—</span>';
        },

        'price' => function($p) {
            $hasDiscount = !empty($p['discount_price']) && $p['discount_price'] < $p['price'];
            if ($hasDiscount) {
                return '<div class="ab-stack ab-stack--xs">'
                    . '<span class="ab-font-bold ab-text-danger ab-num">' . formatPrice((int)$p['discount_price']) . '</span>'
                    . '<span class="ab-text-xs ab-text-muted ab-line-through ab-num">' . formatPrice((int)$p['price']) . '</span>'
                    . '</div>';
            }
            return '<span class="ab-font-bold ab-num">' . formatPrice((int)$p['price']) . '</span>';
        },

        'cost_margin' => function($p) {
            $costVal = !empty($p['cost_price']) ? (int)$p['cost_price'] : 0;
            $hasDiscount = !empty($p['discount_price']) && $p['discount_price'] < $p['price'];
            $actualSalePrice = $hasDiscount ? (int)$p['discount_price'] : (int)$p['price'];
            $marginPct = ($actualSalePrice > 0 && $costVal > 0) ? (int)round((($actualSalePrice - $costVal) / $actualSalePrice) * 100) : null;

            if ($costVal > 0) {
                $costHtml = '<span class="ab-text-sm ab-num">' . formatPrice($costVal) . '</span>';
                $marginHtml = '';
                if ($marginPct !== null) {
                    $tone = $marginPct > 0 ? 'emerald' : 'slate';
                    $marginHtml = '<span class="ab-badge ab-badge--sm" data-tone="' . $tone . '">' . toPersianDigits((string)$marginPct) . '٪ سود</span>';
                }
                return '<div class="ab-stack ab-stack--xs">' . $costHtml . $marginHtml . '</div>';
            }
            return '<span class="ab-text-muted ab-text-xs">ثبت نشده</span>';
        },

        'stock' => function($p) {
            $pid = (int)$p['id'];
            $effStock = (int)$p['effective_stock'];
            $variantCount = (int)$p['variant_count'];

            $state = $effStock === 0 ? 'danger' : ($effStock <= 3 ? 'warning' : 'success');

            $stockPill = '<span class="ab-badge" data-state="' . $state . '" id="stock-badge-' . $pid . '">'
                . toPersianDigits((string)$effStock) . ' عدد'
                . '</span>';

            if ($variantCount > 0) {
                $variantsBtn = '<button type="button" class="ab-btn ab-btn--xs ab-btn--ghost btn-inspect" data-id="' . $pid . '" title="مشاهده موجودی جزئی واریانت‌ها">'
                    . '<span>جزئیات سایزها</span>'
                    . '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>'
                    . '</button>';
                return '<div class="ab-stack ab-stack--xs">' . $stockPill . $variantsBtn . '</div>';
            }

            return $stockPill;
        },

        'is_featured' => function($p) {
            $pid = (int)$p['id'];
            return '<label class="ab-switch" data-tone="amber" title="سوییچ پیشنهاد ویژه">'
                . '<input type="checkbox" data-id="' . $pid . '" data-field="is_featured" ' . (!empty($p['is_featured']) ? 'checked' : '') . ' aria-label="پیشنهاد ویژه">'
                . '<span class="ab-switch__track"><span class="ab-switch__thumb"></span></span>'
                . '</label>';
        },

        'is_active' => function($p) {
            $pid = (int)$p['id'];
            return '<label class="ab-switch" data-tone="emerald" title="سوییچ انتشار محصول">'
                . '<input type="checkbox" data-id="' . $pid . '" data-field="is_active" ' . (!empty($p['is_active']) ? 'checked' : '') . ' aria-label="انتشار محصول">'
                . '<span class="ab-switch__track"><span class="ab-switch__thumb"></span></span>'
                . '</label>';
        },

        'actions' => function($p) use ($returnUrl) {
            $pid = (int)$p['id'];
            $inspectBtn = '<button type="button" class="ab-icon-btn ab-icon-btn--sm btn-inspect" data-id="' . $pid . '" title="بررسی سریع و تنظیم انبار" aria-label="بررسی سریع">'
                . '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>'
                . '</button>';

            $editBtn = '<a href="product_edit.php?id=' . $pid . '" class="ab-icon-btn ab-icon-btn--sm" title="ویرایش کامل کالا" aria-label="ویرایش کامل">'
                . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>'
                . '</a>';

            $storeBtn = '<a href="/product.php?slug=' . urlencode($p['slug']) . '" target="_blank" rel="noopener" class="ab-icon-btn ab-icon-btn--sm" title="مشاهده در فروشگاه" aria-label="مشاهده در فروشگاه">'
                . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>'
                . '</a>';

            $deleteBtn = '<form method="post" action="products.php" onsubmit="return confirm(\'آیا از حذف محصول «' . e(addslashes($p['name'])) . '» اطمینان دارید؟\');" class="ab-inline-form">'
                . csrfField()
                . '<input type="hidden" name="action" value="delete">'
                . '<input type="hidden" name="id" value="' . $pid . '">'
                . '<input type="hidden" name="return_url" value="' . e($returnUrl) . '">'
                . '<button type="submit" class="ab-icon-btn ab-icon-btn--sm ab-btn--danger" title="حذف محصول" aria-label="حذف محصول">'
                . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>'
                . '</button>'
                . '</form>';

            return '<div class="ab-cluster ab-cluster--xs ab-cluster--end">' . $inspectBtn . $editBtn . $storeBtn . $deleteBtn . '</div>';
        },
    ],
    'empty'       => [
        'title'   => 'هیچ محصولی با معیارهای انتخاب‌شده پیدا نشد',
        'message' => 'می‌توانید فیلترها را ریست کنید یا محصول جدیدی اضافه نمایید.',
    ],
]);

if ($catalog['total_pages'] > 1):
    $baseQuery = $_GET;
    unset($baseQuery['page']);
    if (!empty($perPage) && (int)$perPage !== 20) {
        $baseQuery['per_page'] = (int)$perPage;
    }
    $pageUrlPrefix = 'products.php?' . (!empty($baseQuery) ? http_build_query($baseQuery) . '&' : '') . 'page=';
    component('pagination', [
        'page'     => (int)$catalog['current_page'],
        'pages'    => (int)$catalog['total_pages'],
        'total'    => (int)$catalog['total_count'],
        'per_page' => (int)$catalog['per_page'],
        'base_url' => $pageUrlPrefix,
    ]);
endif;
