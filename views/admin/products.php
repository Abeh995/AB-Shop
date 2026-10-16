<?php
/**
 * Modern High-Density Admin Products Catalog Workstation (views/admin/products.php)
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 * Archetype: List (ADMIN-UI-PLAN §3.8)
 */

require APP_ROOT . '/views/admin/layout/header.php';

// Prepare lightweight JSON dataset for client-side Drawer & instant inspection
$productsJsonMap = [];
foreach ($catalog['items'] as $item) {
    $salePrice = !empty($item['discount_price']) && $item['discount_price'] < $item['price'] ? (int)$item['discount_price'] : (int)$item['price'];
    $costPrice = !empty($item['cost_price']) ? (int)$item['cost_price'] : null;
    $marginPercent = null;
    if ($salePrice > 0 && $costPrice !== null && $costPrice > 0) {
        $marginPercent = (int) round((($salePrice - $costPrice) / $salePrice) * 100);
    }

    $galleryUrls = [];
    foreach ($item['gallery_images'] ?? [] as $gImg) {
        if (!empty($gImg)) {
            $galleryUrls[] = UPLOAD_URL . e($gImg);
        }
    }

    $productsJsonMap[(int)$item['id']] = [
        'id'             => (int)$item['id'],
        'name'           => $item['name'],
        'sku'            => $item['sku'] ?? '',
        'category_name'  => $item['category_name'] ?? '',
        'price'          => (int)$item['price'],
        'price_fmt'      => formatPrice((int)$item['price']),
        'discount_price' => $item['discount_price'] ? (int)$item['discount_price'] : null,
        'discount_fmt'   => $item['discount_price'] ? formatPrice((int)$item['discount_price']) : '',
        'cost_price'     => $costPrice,
        'cost_fmt'       => $costPrice ? formatPrice($costPrice) : '',
        'margin_percent' => $marginPercent,
        'stock'          => (int)$item['effective_stock'],
        'is_active'      => (int)$item['is_active'],
        'is_featured'    => (int)$item['is_featured'],
        'image_url'      => !empty($item['image']) ? UPLOAD_URL . e($item['image']) : '/assets/img/placeholder-sock.svg',
        'variants'       => $item['variants'] ?? [],
        'gallery_urls'   => array_values(array_filter($galleryUrls)),
    ];
}
?>

<div class="ab-pattern-list">

    <!-- 1. Page Header -->
    <?php
    component('page_header', [
        'title'       => $pageTitle ?? 'محصولات',
        'subtitle'    => 'مدیریت کاتالوگ، بررسی موجودی انبار، واریانت‌ها و تنظیمات فروش',
        'icon'        => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        'breadcrumbs' => [
            ['label' => 'داشبورد', 'url' => 'index.php'],
            ['label' => 'محصولات'],
        ],
        'actions'     => [
            [
                'label'   => 'محصول جدید',
                'variant' => 'primary',
                'href'    => 'product_edit.php',
                'icon'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
            ],
        ],
    ]);
    ?>

    <!-- 2. Bento KPI Metrics Grid -->
    <?php require __DIR__ . '/products_partials/_kpis.php'; ?>

    <!-- 3. Master Products Catalog Card (Toolbar + Table + Pagination) -->
    <?php ob_start(); ?>
    <div class="ab-stack ab-stack--xs">
        <?php require __DIR__ . '/products_partials/_toolbar.php'; ?>
        <?php require __DIR__ . '/products_partials/_table.php'; ?>
    </div>
    <?php $productsTableCard = ob_get_clean(); ?>

    <?php component('card', [
        'title'    => 'کاتالوگ محصولات',
        'subtitle' => 'مدیریت و پایش موجودی، قیمت‌گذاری و وضعیت فروش محصولات (' . toPersianDigits((string)($catalog['total_count'] ?? 0)) . ' کالا)',
        'icon'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        'content'  => $productsTableCard,
        'flush'    => true,
    ]); ?>

    <!-- 4. Floating Bulk Action Bar -->
    <?php require __DIR__ . '/products_partials/_bulk_bar.php'; ?>

    <!-- 5. Side Dossier Quick Inspect Drawer -->
    <?php require __DIR__ . '/products_partials/_drawer_inspect.php'; ?>

</div>

<!-- Hydrate client-side product dataset for instant drawer & interactions -->
<script type="application/json" id="productsJsonData"><?= json_encode($productsJsonMap, JSON_UNESCAPED_UNICODE) ?></script>
<script src="<?= e(asset('/assets/js/admin-products.js')) ?>" defer></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
