<?php
/**
 * Definition List Presentation Component (views/admin/components/definition_list.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var array       $items   Array of ['label' => string, 'value' => mixed, 'raw' => bool|null]
 * @var bool|null   $stacked Vertical stacked layout
 * @var string|null $class   Extra class
 */

$isStacked = !empty($stacked);
$classes = ['ab-dl'];
if ($isStacked) $classes[] = 'ab-dl--stacked';
if (!empty($class)) $classes[] = e($class);
?>
<dl class="<?= implode(' ', $classes) ?>">
    <?php foreach (($items ?? []) as $it): ?>
        <dt class="ab-dl__label"><?= e($it['label'] ?? '') ?></dt>
        <dd class="ab-dl__value">
            <?php if (!empty($it['raw'])): ?>
                <?= $it['value'] ?? '' ?>
            <?php else: ?>
                <?= e((string)($it['value'] ?? '—')) ?>
            <?php endif; ?>
        </dd>
    <?php endforeach; ?>
</dl>
