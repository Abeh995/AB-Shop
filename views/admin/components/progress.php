<?php
/**
 * Progress & Meter Presentation Component (views/admin/components/progress.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var float|int   $value        Current progress value
 * @var float|int   $max          Maximum progress value (default 100)
 * @var string|null $tone         Color tone ('emerald', 'rose', 'amber', 'blue', etc.)
 * @var string|null $label        Progress label text
 * @var bool|null   $show_percent Show percentage indicator
 * @var string|null $class        Extra wrapper class
 */

$val = max(0, min((float)($value ?? 0), (float)($max ?? 100)));
$maxVal = max(1, (float)($max ?? 100));
$pct = round(($val / $maxVal) * 100);

$extraClass = !empty($class) ? ' ' . e($class) : '';
$toneAttr = !empty($tone) ? ' data-tone="' . e($tone) . '"' : '';
?>
<div class="ab-progress-wrap<?= $extraClass ?>">
    <?php if (!empty($label) || !empty($show_percent)): ?>
        <div class="ab-progress-label-row">
            <?php if (!empty($label)): ?>
                <span class="ab-progress-label"><?= e($label) ?></span>
            <?php endif; ?>
            <?php if (!empty($show_percent)): ?>
                <span class="ab-progress-pct ab-num"><?= toPersianDigits((string)$pct) ?>٪</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="ab-progress"<?= $toneAttr ?> role="progressbar" aria-valuenow="<?= $val ?>" aria-valuemin="0" aria-valuemax="<?= $maxVal ?>" style="--pct: <?= $pct ?>%;">
        <div class="ab-progress__bar" style="--pct-w: <?= $pct ?>%; width: <?= $pct ?>%;"></div>
    </div>
</div>
