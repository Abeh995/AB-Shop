<?php
/**
 * Button & Icon Button Presentation Component (views/admin/components/button.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var string|null $label         Button text
 * @var string|null $variant       'primary' (default), 'secondary', 'outline', 'ghost', 'danger', 'success'
 * @var string|null $size          'sm', 'md' (default), 'lg'
 * @var string|null $icon          SVG markup or icon HTML
 * @var string|null $icon_position 'start' (default) or 'end'
 * @var bool|null   $icon_only     If true, renders as an icon button (.ab-icon-btn)
 * @var string|null $href          If provided, renders as <a>, else <button>
 * @var string|null $type          Button type ('button', 'submit', 'reset') - default 'button'
 * @var bool|null   $disabled      Disabled state
 * @var bool|null   $busy          Loading spinner state (sets aria-busy="true")
 * @var bool|null   $loading       Alias for $busy
 * @var string|null $tone          Optional data-tone override
 * @var array|string|null $attrs   Extra HTML attributes
 * @var string|null $class         Additional CSS classes
 * @var string|null $id            Element ID
 */

$var = $variant ?? 'primary';
$sz = $size ?? 'md';
$pos = $icon_position ?? 'start';
$isBusy = !empty($busy) || !empty($loading);
$isDisabled = !empty($disabled);
$isIconOnly = !empty($icon_only);

$classes = [];
if ($isIconOnly) {
    $classes[] = 'ab-icon-btn';
    if ($sz === 'sm') $classes[] = 'ab-icon-btn--sm';
} else {
    $classes[] = 'ab-btn';
    $classes[] = 'ab-btn--' . e($var);
    if ($sz === 'sm') $classes[] = 'ab-btn--sm';
    if ($sz === 'lg') $classes[] = 'ab-btn--lg';
}

if (!empty($class)) {
    $classes[] = e($class);
}

$classAttr = ' class="' . implode(' ', $classes) . '"';
$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';
$toneAttr = !empty($tone) ? ' data-tone="' . e($tone) . '"' : '';
$busyAttr = $isBusy ? ' aria-busy="true"' : '';

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

$tag = !empty($href) ? 'a' : 'button';
$typeAttr = ($tag === 'button') ? ' type="' . e($type ?? 'button') . '"' : '';
$hrefAttr = !empty($href) ? ' href="' . e($href) . '"' : '';
$disAttr = '';
if ($isDisabled) {
    $disAttr = ($tag === 'button') ? ' disabled aria-disabled="true"' : ' aria-disabled="true" tabindex="-1"';
}

$ariaLabelAttr = '';
if ($isIconOnly && !empty($label)) {
    $ariaLabelAttr = ' aria-label="' . e($label) . '" title="' . e($label) . '"';
}
?>
<<?= $tag ?><?= $hrefAttr ?><?= $typeAttr ?><?= $classAttr ?><?= $idAttr ?><?= $toneAttr ?><?= $busyAttr ?><?= $disAttr ?><?= $ariaLabelAttr ?><?= $extraAttrs ?>>
    <?php if (!empty($icon) && ($pos === 'start' || $isIconOnly)): ?>
        <span class="ab-btn__icon"><?= $icon ?></span>
    <?php endif; ?>
    <?php if (!$isIconOnly && isset($label) && $label !== ''): ?>
        <span class="ab-btn__label"><?= e($label) ?></span>
    <?php endif; ?>
    <?php if (!empty($icon) && $pos === 'end' && !$isIconOnly): ?>
        <span class="ab-btn__icon"><?= $icon ?></span>
    <?php endif; ?>
</<?= $tag ?>>
