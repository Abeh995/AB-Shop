<?php
/**
 * Bento KPI Card Component (views/admin/components/kpi_card.php)
 *
 * @var string $title        Card headline/label
 * @var string|int $value    Metric value
 * @var string|null $unit    Optional unit text (e.g. تومان, کالا)
 * @var string|null $sub     Optional subtext or trend note
 * @var string|null $icon    SVG markup or icon string
 * @var string|null $color   Color theme ('primary', 'emerald', 'rose', 'sky', 'amber', 'purple', 'blue')
 * @var string|null $url     Optional link if card is clickable
 * @var string|null $class   Optional additional CSS class
 * @var string|null $id      Optional element ID
 */

$tag = !empty($url) ? 'a' : 'div';
$hrefAttr = !empty($url) ? ' href="' . e($url) . '"' : '';
$clickableClass = !empty($url) ? ' is-clickable' : '';
$colorClass = !empty($color) ? ' theme-' . e($color) : ' theme-primary';
$extraClass = !empty($class) ? ' ' . e($class) : '';
$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';
?>
<<?= $tag ?><?= $hrefAttr ?><?= $idAttr ?> class="ab-kpi-card<?= $clickableClass ?><?= $colorClass ?><?= $extraClass ?>">
    <div class="ab-kpi-info">
        <h4 class="ab-kpi-title"><?= e($title ?? '') ?></h4>
        <div class="ab-kpi-value-row">
            <?php
            $valClass = !empty($value_class) ? ' ' . e($value_class) : '';
            $valStyle = !empty($value_style) ? ' style="' . e($value_style) . '"' : '';
            ?>
            <span class="ab-kpi-value<?= $valClass ?>"<?= $valStyle ?>><?= !empty($raw_value) ? ($value ?? '—') : e((string)($value ?? '—')) ?></span>
            <?php if (!empty($unit)): ?>
                <span class="ab-kpi-unit"><?= e($unit) ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($sub)): ?>
            <div class="ab-kpi-sub"><?= $sub ?></div>
        <?php endif; ?>
    </div>
    <?php if (!empty($icon)): ?>
        <div class="ab-kpi-icon icon-<?= e($color ?? 'primary') ?>">
            <?= $icon ?>
        </div>
    <?php endif; ?>
</<?= $tag ?>>
