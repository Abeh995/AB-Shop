<?php
/**
 * Bento KPI Card Presentation Component (views/admin/components/kpi_card.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var string            $title        Card headline/label
 * @var string|int        $value        Metric value
 * @var string|null       $unit         Optional unit text (e.g. تومان, کالا)
 * @var string|null       $sub          Optional subtext or trend note
 * @var string|null       $trend        Trend indicator: 'up', 'down', 'flat'
 * @var string|null       $icon         SVG markup or icon string
 * @var string|null       $tone         Tone identifier: 'brand', 'emerald', 'rose', 'sky', 'amber', 'purple', 'blue'
 * @var string|null       $color        Deprecated alias of $tone
 * @var string|null       $href         Link URL if card is clickable
 * @var string|null       $url          Deprecated alias of $href
 * @var bool|null         $raw_value    If true, renders value unescaped
 * @var string|null       $value_class  Extra CSS class for value element
 * @var string|null       $class        Extra card CSS class
 * @var string|null       $id           Element ID
 */

$linkUrl = $href ?? $url ?? null;
$tag = !empty($linkUrl) ? 'a' : 'div';
$hrefAttr = !empty($linkUrl) ? ' href="' . e($linkUrl) . '"' : '';

$cardTone = $tone ?? $color ?? 'brand';
if ($cardTone === 'primary') $cardTone = 'brand';

$classes = ['ab-kpi-card'];
if (!empty($linkUrl)) $classes[] = 'is-clickable';
$classes[] = 'theme-' . e($cardTone);
if (!empty($class)) $classes[] = e($class);

$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';
?>
<<?= $tag ?><?= $hrefAttr ?><?= $idAttr ?> class="<?= implode(' ', $classes) ?>" data-tone="<?= e($cardTone) ?>">
    <div class="ab-kpi-info">
        <h4 class="ab-kpi-title"><?= e($title ?? '') ?></h4>
        <div class="ab-kpi-value-row">
            <?php
            $vClasses = ['ab-kpi-value'];
            if (!empty($value_class)) $vClasses[] = e($value_class);
            ?>
            <span class="<?= implode(' ', $vClasses) ?>">
                <?= !empty($raw_value) ? ($value ?? '—') : e((string)($value ?? '—')) ?>
            </span>
            <?php if (!empty($unit)): ?>
                <span class="ab-kpi-unit"><?= e($unit) ?></span>
            <?php endif; ?>
        </div>

        <?php if (!empty($trend) || !empty($sub)): ?>
            <div class="ab-kpi-sub">
                <?php if (!empty($trend)): ?>
                    <span class="ab-kpi-trend ab-kpi-trend--<?= e($trend) ?>">
                        <?php if ($trend === 'up'): ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m18 15-6-6-6 6"/></svg>
                        <?php elseif ($trend === 'down'): ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                        <?php else: ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($sub)): ?>
                    <span><?= $sub ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($icon)): ?>
        <div class="ab-kpi-icon icon-<?= e($cardTone) ?>">
            <?= $icon ?>
        </div>
    <?php endif; ?>
</<?= $tag ?>>
