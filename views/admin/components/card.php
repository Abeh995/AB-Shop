<?php
/**
 * Card Container Presentation Component (views/admin/components/card.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var string|null       $title      Card title
 * @var string|null       $subtitle   Card subtitle
 * @var string|null       $icon       SVG icon markup
 * @var array|string|null $actions    Array of button props or raw HTML
 * @var string|null       $content    Card body HTML
 * @var string|null       $footer     Footer HTML or array of button props
 * @var string|null       $body_class Additional CSS class for body
 * @var bool|null         $flush      If true, removes padding from body (for tables)
 * @var array|string|null $attrs      Extra HTML attributes
 * @var string|null       $class      Extra card wrapper class
 * @var string|null       $id         Card ID
 */

$classes = ['ab-card'];
if (!empty($flush)) $classes[] = 'ab-card--flush';
if (!empty($class)) $classes[] = e($class);

$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';

$extraAttrs = '';
if (!empty($attrs)) {
    if (is_array($attrs)) {
        foreach ($attrs as $k => $v) {
            $extraAttrs .= ' ' . e((string)$k) . '="' . e((string)$v) . '"';
        }
    } else {
        $extraAttrs .= ' ' . $attrs;
    }
}

$hasHead = !empty($title) || !empty($subtitle) || !empty($actions) || !empty($icon);
?>
<div class="<?= implode(' ', $classes) ?>"<?= $idAttr ?><?= $extraAttrs ?>>
    <?php if ($hasHead): ?>
        <div class="ab-card__head">
            <div class="ab-card__titles">
                <?php if (!empty($title)): ?>
                    <h3 class="ab-card__title">
                        <?php if (!empty($icon)): ?><span class="ab-card__icon"><?= $icon ?></span><?php endif; ?>
                        <?= e($title) ?>
                    </h3>
                <?php endif; ?>
                <?php if (!empty($subtitle)): ?>
                    <p class="ab-card__subtitle"><?= e($subtitle) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($actions)): ?>
                <div class="ab-card__actions">
                    <?php if (is_array($actions)): ?>
                        <?php foreach ($actions as $act): ?>
                            <?php if (is_string($act)): echo $act; else: component('button', $act); endif; ?>
                        <?php endforeach; ?>
                    <?php else: echo $actions; endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="ab-card__body<?= !empty($body_class) ? ' ' . e($body_class) : '' ?><?= !empty($flush) ? ' ab-card__body--flush' : '' ?>">
        <?= $content ?? '' ?>
    </div>

    <?php if (!empty($footer)): ?>
        <div class="ab-card__foot">
            <?php if (is_array($footer)): ?>
                <?php foreach ($footer as $fAct): ?>
                    <?php if (is_string($fAct)): echo $fAct; else: component('button', $fAct); endif; ?>
                <?php endforeach; ?>
            <?php else: echo $footer; endif; ?>
        </div>
    <?php endif; ?>
</div>
