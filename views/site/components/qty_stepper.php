<?php
/**
 * Storefront Quantity Stepper Component
 *
 * Declarative quantity selector wired to ABClient qty-stepper ([data-ab-qty]).
 * Props:
 * - name (string): form input field name
 * - value (int): current quantity value
 * - min (int): minimum allowed quantity
 * - max (int): maximum allowed quantity
 * - id (string|null): optional input id
 * - autoSubmit (bool): submit parent form immediately on +/- click
 * - ariaLabel (string): accessible input label
 */
$name = isset($name) && (string) $name !== '' ? (string) $name : 'qty';
$min = isset($min) ? max(0, (int) $min) : 1;
$max = isset($max) ? max($min, (int) $max) : 99;
$value = isset($value) ? max($min, min($max, (int) $value)) : $min;
$id = isset($id) ? trim((string) $id) : '';
$autoSubmit = !empty($autoSubmit);
$ariaLabel = isset($ariaLabel) && (string) $ariaLabel !== '' ? (string) $ariaLabel : 'تعداد';
?>
<div class="c-qty qty-selector" data-ab-qty<?= $autoSubmit ? ' data-ab-qty-auto' : '' ?>>
    <button type="button" class="c-qty__btn c-qty__btn--minus qty-minus" data-ab-qty-minus aria-label="کاهش تعداد">−</button>
    <input class="c-qty__input"
           type="number"
           <?= $id !== '' ? 'id="' . e($id) . '"' : '' ?>
           name="<?= e($name) ?>"
           value="<?= (int) $value ?>"
           min="<?= (int) $min ?>"
           max="<?= (int) $max ?>"
           aria-label="<?= e($ariaLabel) ?>">
    <button type="button" class="c-qty__btn c-qty__btn--plus qty-plus" data-ab-qty-plus aria-label="افزایش تعداد">+</button>
</div>
