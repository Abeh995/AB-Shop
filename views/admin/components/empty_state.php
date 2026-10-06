<?php
/**
 * Standard Empty State Presentation Component (views/admin/components/empty_state.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var string            $title        Main message headline
 * @var string|null       $message      Secondary explanatory text
 * @var string|null       $icon         SVG markup or icon
 * @var string|null       $action_url   CTA link URL
 * @var string|null       $action_label CTA button label
 * @var string|null       $action_modal Modal ID to open via data-ab-modal-open
 * @var string|null       $class        Extra CSS class
 * @var string|null       $id           Element ID
 */

$extraClass = !empty($class) ? ' ' . e($class) : '';
$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';
?>
<div class="ab-empty-state<?= $extraClass ?>"<?= $idAttr ?>>
    <div class="ab-empty-icon">
        <?php if (!empty($icon)): ?>
            <?= $icon ?>
        <?php else: ?>
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
        <?php endif; ?>
    </div>

    <h3 class="ab-empty-title"><?= e($title) ?></h3>

    <?php if (!empty($message)): ?>
        <p class="ab-empty-desc"><?= e($message) ?></p>
    <?php endif; ?>

    <?php if (!empty($action_label)): ?>
        <div class="ab-empty-action">
            <?php if (!empty($action_modal)): ?>
                <?php component('button', [
                    'label'   => $action_label,
                    'variant' => 'primary',
                    'attrs'   => ['data-ab-modal-open' => $action_modal],
                ]); ?>
            <?php elseif (!empty($action_url)): ?>
                <?php component('button', [
                    'label'   => $action_label,
                    'variant' => 'primary',
                    'href'    => $action_url,
                ]); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
