<?php
/**
 * Form Field & Control Presentation Component (views/admin/components/form_field.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var string            $name        Field name
 * @var string|null       $label       Field label
 * @var string|null       $type        'text' (default), 'number', 'email', 'password', 'tel', 'url',
 *                                     'select', 'textarea', 'switch', 'checkbox', 'check', 'radio'
 * @var mixed             $value       Current value
 * @var array|null        $options     Key/value or item list for select/radio
 * @var string|null       $hint        Helpful hint text below control
 * @var string|null       $error       Validation error message
 * @var bool|null         $required    Required field
 * @var string|null       $unit        Suffix or prefix unit (creates .ab-input-group)
 * @var string|null       $prefix      Explicit prefix addon
 * @var string|null       $suffix      Explicit suffix addon
 * @var string|null       $dir         'ltr' or 'rtl' (ltr for numbers/codes/emails)
 * @var string|null       $placeholder Placeholder string
 * @var bool|null         $disabled    Disabled control
 * @var bool|null         $readonly    Readonly control
 * @var bool|null         $checked     Checked state for switch/check/radio
 * @var int|null          $rows        Rows for textarea (default 4)
 * @var array|string|null $attrs       Additional HTML attributes
 * @var string|null       $class       Wrapper CSS class
 * @var string|null       $input_class Additional control class
 * @var string|null       $id          Element ID (defaults to $name)
 */

$fieldType = $type ?? 'text';
$fieldId = $id ?? ('field_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $name));
$val = $value ?? '';
$hasError = !empty($error);
$isRequired = !empty($required);
$isLtr = ($dir === 'ltr') || in_array($fieldType, ['number', 'email', 'tel', 'url', 'password'], true);

$wrapperClasses = ['ab-field'];
if ($hasError) $wrapperClasses[] = 'has-error';
if (!empty($class)) $wrapperClasses[] = e($class);

$controlClasses = [];
if ($fieldType === 'select') {
    $controlClasses[] = 'ab-select';
} elseif ($fieldType === 'textarea') {
    $controlClasses[] = 'ab-textarea';
} elseif (!in_array($fieldType, ['switch', 'check', 'checkbox', 'radio'], true)) {
    $controlClasses[] = 'ab-input';
}
if ($isLtr) $controlClasses[] = 'ab-num';
if (!empty($input_class)) $controlClasses[] = e($input_class);

$controlClassAttr = !empty($controlClasses) ? ' class="' . implode(' ', $controlClasses) . '"' : '';
$dirAttr = $isLtr ? ' dir="ltr"' : '';
$reqAttr = $isRequired ? ' required' : '';
$disAttr = !empty($disabled) ? ' disabled' : '';
$roAttr = !empty($readonly) ? ' readonly' : '';
$invAttr = $hasError ? ' aria-invalid="true"' : '';
$phAttr = !empty($placeholder) ? ' placeholder="' . e($placeholder) . '"' : '';

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

$hasAddon = !empty($prefix) || !empty($suffix) || !empty($unit);
?>
<div class="<?= implode(' ', $wrapperClasses) ?>">
    <?php if (!empty($label) && !in_array($fieldType, ['switch', 'check', 'checkbox'], true)): ?>
        <label for="<?= e($fieldId) ?>" class="ab-field__label">
            <?= e($label) ?>
            <?php if ($isRequired): ?><span class="ab-field__required">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <?php if ($fieldType === 'select'): ?>
        <select name="<?= e($name) ?>" id="<?= e($fieldId) ?>"<?= $controlClassAttr ?><?= $reqAttr ?><?= $disAttr ?><?= $invAttr ?><?= $extraAttrs ?>>
            <?php foreach (($options ?? []) as $optKey => $optVal): ?>
                <?php
                if (is_array($optVal)) {
                    $optKey = $optVal['value'] ?? $optKey;
                    $optLabel = $optVal['label'] ?? $optKey;
                } else {
                    $optLabel = $optVal;
                }
                $isSelected = ((string)$val === (string)$optKey);
                ?>
                <option value="<?= e((string)$optKey) ?>" <?= $isSelected ? 'selected' : '' ?>>
                    <?= e((string)$optLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>

    <?php elseif ($fieldType === 'textarea'): ?>
        <textarea name="<?= e($name) ?>" id="<?= e($fieldId) ?>" rows="<?= (int)($rows ?? 4) ?>"<?= $controlClassAttr ?><?= $dirAttr ?><?= $phAttr ?><?= $reqAttr ?><?= $disAttr ?><?= $roAttr ?><?= $invAttr ?><?= $extraAttrs ?>><?= e((string)$val) ?></textarea>

    <?php elseif ($fieldType === 'switch'): ?>
        <label class="ab-switch">
            <input type="checkbox" name="<?= e($name) ?>" id="<?= e($fieldId) ?>" value="1" <?= !empty($checked) ? 'checked' : '' ?><?= $disAttr ?><?= $extraAttrs ?>>
            <span class="ab-switch__track"><span class="ab-switch__thumb"></span></span>
            <?php if (!empty($label)): ?>
                <span class="ab-switch__label"><?= e($label) ?><?php if ($isRequired): ?><span class="ab-field__required">*</span><?php endif; ?></span>
            <?php endif; ?>
        </label>

    <?php elseif ($fieldType === 'check' || $fieldType === 'checkbox'): ?>
        <label class="ab-check">
            <input type="checkbox" name="<?= e($name) ?>" id="<?= e($fieldId) ?>" value="<?= e((string)($check_value ?? '1')) ?>" <?= !empty($checked) ? 'checked' : '' ?><?= $disAttr ?><?= $extraAttrs ?>>
            <span class="ab-check__label"><?= e($label ?? '') ?><?php if ($isRequired): ?><span class="ab-field__required">*</span><?php endif; ?></span>
        </label>

    <?php else: ?>
        <?php if ($hasAddon): ?>
            <div class="ab-input-group">
                <?php if (!empty($prefix)): ?>
                    <span class="ab-input-group__addon"><?= e($prefix) ?></span>
                <?php endif; ?>
                <input type="<?= e($fieldType) ?>" name="<?= e($name) ?>" id="<?= e($fieldId) ?>" value="<?= e((string)$val) ?>"<?= $controlClassAttr ?><?= $dirAttr ?><?= $phAttr ?><?= $reqAttr ?><?= $disAttr ?><?= $roAttr ?><?= $invAttr ?><?= $extraAttrs ?>>
                <?php if (!empty($suffix) || !empty($unit)): ?>
                    <span class="ab-input-group__addon"><?= e($suffix ?? $unit) ?></span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <input type="<?= e($fieldType) ?>" name="<?= e($name) ?>" id="<?= e($fieldId) ?>" value="<?= e((string)$val) ?>"<?= $controlClassAttr ?><?= $dirAttr ?><?= $phAttr ?><?= $reqAttr ?><?= $disAttr ?><?= $roAttr ?><?= $invAttr ?><?= $extraAttrs ?>>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($hasError): ?>
        <div class="ab-field__error"><?= e($error) ?></div>
    <?php elseif (!empty($hint)): ?>
        <div class="ab-field__hint"><?= e($hint) ?></div>
    <?php endif; ?>
</div>
