<?php
/**
 * Modern High-Density Admin Gifts & Cart Add-ons Workstation (Desktop Dual-Pane)
 * Modularized Master-Detail Workspace: Bento KPIs + Master Matrix Table + Sticky Studio.
 */
require APP_ROOT . '/views/admin/layout/header.php';

// Prepare lightweight JSON dataset for client-side Studio and instant inspection
$giftItemsJsonMap = [];
foreach ($items as $it) {
    $giftItemsJsonMap[(int)$it['id']] = [
        'id'                => (int)$it['id'],
        'name'              => $it['name'],
        'tagline'           => $it['tagline'] ?? '',
        'badge_text'        => $it['badge_text'] ?? '',
        'image_url'         => !empty($it['image']) ? UPLOAD_URL . e($it['image']) : '/assets/img/placeholder-sock.svg',
        'is_active'         => (int)$it['is_active'],
        'is_giftable'       => (int)$it['is_giftable'],
        'is_post_orderable' => (int)$it['is_post_orderable'],
        'cost_price'        => (int)$it['cost_price'],
        'post_order_price'  => $it['post_order_price'] !== null ? (int)$it['post_order_price'] : '',
        'min_cart_total'    => (int)($it['min_cart_total'] ?? 0),
        'stock'             => (int)$it['stock'],
        'sort_order'        => (int)($it['sort_order'] ?? 0),
        'profit_per_unit'   => $it['profit_per_unit'],
        'margin_percent'    => $it['margin_percent'],
        'gifted_units'      => (int)$it['gifted_units'],
        'sold_units'        => (int)$it['sold_units'],
        'gross_revenue'     => (int)$it['gross_revenue'],
        'gross_profit'      => (int)($it['gross_profit'] ?? 0),
    ];
}
$activeEditItem = $editItem ?? null;
?>

<meta name="csrf-token" content="<?= e(csrfToken()) ?>">

<main class="gift-workspace">

    <!-- 1. Bento KPI Stats Header -->
    <?php require APP_ROOT . '/views/admin/gift_items_partials/_kpis.php'; ?>

    <!-- 2. Dual-Pane Master-Detail Workbench Grid -->
    <div class="gift-split-grid">
        <!-- Master Column: Filter Toolbar & Reorderable Matrix Table -->
        <?php require APP_ROOT . '/views/admin/gift_items_partials/_table.php'; ?>

        <!-- Detail Column: Sticky Smart Studio & Live Customer Simulation -->
        <?php require APP_ROOT . '/views/admin/gift_items_partials/_studio.php'; ?>
    </div>

</main>

<script>
window.__GIFT_ITEMS_DATA__ = <?= json_encode($giftItemsJsonMap, JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php
$adminGiftItemsJsVer = APP_VERSION . '.' . (@filemtime(APP_ROOT . '/assets/js/admin-gift-items.js') ?: 1);
?>
<script src="/assets/js/admin-gift-items.js?v=<?= $adminGiftItemsJsVer ?>"></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
