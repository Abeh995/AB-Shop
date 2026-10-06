<?php
/**
 * Sticky Save Bar Presentation Component (views/admin/components/savebar.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var array|string|null $primary    Primary button props or text
 * @var array|string|null $secondary  Secondary button props or text
 * @var string|null       $dirty_hint Note/message explaining unsaved changes
 * @var string|null       $form_id    Form element ID (for form="..." attribute)
 * @var string|null       $class      Extra class
 */

$extraClass = !empty($class) ? ' ' . e($class) : '';

$primaryProps = is_array($primary) ? $primary : ['label' => (string)($primary ?? 'ذخیره تغییرات')];
$primaryProps['variant'] = $primaryProps['variant'] ?? 'primary';
$primaryProps['type'] = $primaryProps['type'] ?? 'submit';
if (!empty($form_id)) {
    $primaryProps['attrs'] = array_merge($primaryProps['attrs'] ?? [], ['form' => $form_id]);
}

$secondaryProps = null;
if (!empty($secondary)) {
    $secondaryProps = is_array($secondary) ? $secondary : ['label' => (string)$secondary];
    $secondaryProps['variant'] = $secondaryProps['variant'] ?? 'secondary';
    $secondaryProps['type'] = $secondaryProps['type'] ?? 'button';
}
?>
<aside class="ab-savebar<?= $extraClass ?>" aria-label="نوار عملیات ذخیره‌سازی">
    <div class="ab-savebar__lead">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span class="ab-savebar__hint"><?= e($dirty_hint ?? 'تغییرات اعمال‌شده را بررسی و ذخیره کنید') ?></span>
    </div>

    <div class="ab-savebar__actions">
        <?php if (!empty($secondaryProps)): ?>
            <?php component('button', $secondaryProps); ?>
        <?php endif; ?>
        <?php component('button', $primaryProps); ?>
    </div>
</aside>
