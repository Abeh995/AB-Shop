<?php
/**
 * AB-Socks Modern Logistics & Shipping Methods Workstation
 * v1.26.0 - High-density Bento KPIs, Dual-Pane Master Table & Sticky Live Studio
 *
 * @var string $pageTitle
 * @var array $methods
 * @var array $metrics
 * @var ?array $editMethod
 */
require APP_ROOT . '/views/admin/layout/header.php';

// Prepare lightweight JSON dataset for client-side Studio and instant inspection
$shippingMethodsJsonMap = [];
foreach ($methods as $m) {
    $shippingMethodsJsonMap[(int)$m['id']] = [
        'id'                 => (int)$m['id'],
        'name'               => $m['name'],
        'description'        => $m['description'] ?? '',
        'estimated_delivery' => $m['estimated_delivery'] ?? '',
        'match_type'         => $m['match_type'],
        'match_value'        => $m['match_value'] ?? '',
        'cost'               => (int)$m['cost'],
        'actual_cost'        => $m['actual_cost'] !== null ? (int)$m['actual_cost'] : '',
        'free_above_amount'  => $m['free_above_amount'] !== null ? (int)$m['free_above_amount'] : '',
        'is_active'          => (int)$m['is_active'],
        'sort_order'         => (int)$m['sort_order'],
    ];
}
?>

<meta name="csrf-token" content="<?= e(csrfToken()) ?>">

<main class="shipping-workspace">

    <!-- 1. Bento KPI Logistics Header -->
    <?php require APP_ROOT . '/views/admin/shipping_methods_partials/_kpis.php'; ?>

    <!-- 2. Dual-Pane Master-Detail Workbench Grid -->
    <div class="shipping-split-grid">
        <!-- Master Column: Priority Table & Status -->
        <?php require APP_ROOT . '/views/admin/shipping_methods_partials/_table.php'; ?>

        <!-- Detail Column: Sticky Smart Studio & Live Customer Simulation -->
        <?php require APP_ROOT . '/views/admin/shipping_methods_partials/_studio.php'; ?>
    </div>

</main>

<script>
window.__SHIPPING_METHODS__ = <?= json_encode($shippingMethodsJsonMap, JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php
$adminShippingJsVer = APP_VERSION . '.' . (@filemtime(APP_ROOT . '/assets/js/admin-shipping.js') ?: 1);
?>
<script src="/assets/js/admin-shipping.js?v=<?= $adminShippingJsVer ?>"></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
