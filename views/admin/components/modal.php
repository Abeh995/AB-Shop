<?php
/**
 * Modal Dialog Presentation Component (views/admin/components/modal.php)
 * Native <dialog> component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var string            $id             Dialog ID (used by data-ab-modal-open / AB.modal.open)
 * @var string            $title          Modal title
 * @var string|null       $size           'sm', 'md' (default), 'lg', 'xl'
 * @var string|null       $content        Modal body HTML
 * @var array|string|null $footer_actions Array of button props or raw HTML
 * @var array|string|null $attrs          Extra HTML attributes
 * @var string|null       $class          Extra class
 */

$sz = $size ?? 'md';
$modalId = $id ?? ('modal_' . bin2hex(random_bytes(3)));

$classes = ['ab-modal', 'ab-modal--' . e($sz)];
if (!empty($class)) $classes[] = e($class);

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
?>
<dialog class="<?= implode(' ', $classes) ?>" id="<?= e($modalId) ?>" data-ab-modal<?= $extraAttrs ?>>
    <header class="ab-modal__header">
        <h3 class="ab-modal__title"><?= e($title) ?></h3>
        <button type="button" class="ab-icon-btn ab-icon-btn--sm ab-modal__close" data-ab-modal-close aria-label="بستن">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </header>

    <div class="ab-modal__body">
        <?= $content ?? '' ?>
    </div>

    <?php if (!empty($footer_actions)): ?>
        <footer class="ab-modal__footer">
            <?php if (is_array($footer_actions)): ?>
                <?php foreach ($footer_actions as $act): ?>
                    <?php if (is_string($act)): echo $act; else: component('button', $act); endif; ?>
                <?php endforeach; ?>
            <?php else: echo $footer_actions; endif; ?>
        </footer>
    <?php endif; ?>
</dialog>
