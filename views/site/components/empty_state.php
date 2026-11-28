<?php
/**
 * Storefront Empty State Component
 *
 * Pure presentation empty message box with optional CTA link.
 * Props:
 * - message (string): localized empty-state copy
 * - actionUrl (string|null): optional link destination
 * - actionLabel (string|null): optional link button text
 * - compact (bool): reduced vertical padding
 * - icon (string|null): optional leading icon text/emoji
 */
$message = isset($message) && (string) $message !== '' ? (string) $message : 'موردی یافت نشد.';
$actionUrl = isset($actionUrl) ? (string) $actionUrl : '';
$actionLabel = isset($actionLabel) ? (string) $actionLabel : '';
$compact = !empty($compact);
$icon = isset($icon) ? (string) $icon : '';
?>
<div class="c-empty empty-state<?= $compact ? ' c-empty--compact' : '' ?>">
    <?php if ($icon !== ''): ?>
        <div class="c-empty__icon" aria-hidden="true"><?= e($icon) ?></div>
    <?php endif; ?>
    <p class="c-empty__text"><?= e($message) ?></p>
    <?php if ($actionUrl !== ''): ?>
        <a href="<?= e($actionUrl) ?>" class="c-btn c-btn--primary">
            <?= e($actionLabel !== '' ? $actionLabel : 'مشاهده محصولات') ?>
        </a>
    <?php endif; ?>
</div>
