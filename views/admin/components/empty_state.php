<?php
/**
 * Standard Empty State Component (views/admin/components/empty_state.php)
 *
 * @var string $title             Main message headline
 * @var string|null $message      Secondary explanatory text
 * @var string|null $icon         SVG markup or emoji
 * @var string|null $action_url   CTA link URL
 * @var string|null $action_label CTA button label
 * @var string|null $action_modal Modal ID to open via data-ab-modal-open
 * @var string|null $class        Optional additional CSS class
 */

$extraClass = !empty($class) ? ' ' . e($class) : '';
?>
<div class="ab-empty-state<?= $extraClass ?>">
    <?php if (!empty($icon)): ?>
        <div class="ab-empty-icon"><?= $icon ?></div>
    <?php else: ?>
        <div class="ab-empty-icon">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
        </div>
    <?php endif; ?>

    <h3 class="ab-empty-title"><?= e($title) ?></h3>

    <?php if (!empty($message)): ?>
        <p class="ab-empty-desc"><?= e($message) ?></p>
    <?php endif; ?>

    <?php if (!empty($action_label)): ?>
        <?php if (!empty($action_modal)): ?>
            <button type="button" class="btn btn-primary" data-ab-modal-open="<?= e($action_modal) ?>">
                <?= e($action_label) ?>
            </button>
        <?php elseif (!empty($action_url)): ?>
            <a href="<?= e($action_url) ?>" class="btn btn-primary">
                <?= e($action_label) ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>
