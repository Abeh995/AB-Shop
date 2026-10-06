<?php
/**
 * Bento KPI Grid Presentation Component (views/admin/components/kpi_grid.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var array<int, array> $cards Array of kpi_card prop dictionaries
 * @var string|null       $class Extra grid CSS class
 * @var string|null       $id    Grid ID
 */

$extraClass = !empty($class) ? ' ' . e($class) : '';
$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';
?>
<section class="ab-kpi-grid<?= $extraClass ?>"<?= $idAttr ?>>
    <?php foreach (($cards ?? []) as $cardProps): ?>
        <?php component('kpi_card', $cardProps); ?>
    <?php endforeach; ?>
</section>
