<?php
/**
 * Bento KPI Grid Component (views/admin/components/kpi_grid.php)
 * Renders multiple KPI cards inside a responsive grid layout.
 *
 * @var array<int, array> $cards  Array of kpi_card prop dictionaries
 * @var string|null $class       Optional additional grid CSS class
 * @var string|null $id          Optional grid ID
 */

$extraClass = !empty($class) ? ' ' . e($class) : '';
$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';
?>
<section class="ab-kpi-grid<?= $extraClass ?>"<?= $idAttr ?>>
    <?php foreach (($cards ?? []) as $cardProps): ?>
        <?php component('kpi_card', $cardProps); ?>
    <?php endforeach; ?>
</section>
