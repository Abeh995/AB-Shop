<?php
/**
 * Storefront Alert Banner Component
 *
 * Unified feedback box supporting single messages, error arrays, and optional title.
 * Props:
 * - state (string): 'success' | 'error' | 'warning' | 'info'
 * - message (string|null): primary feedback text
 * - messages (array): list of messages (e.g. form validation errors)
 * - title (string|null): optional bold heading line
 * - class (string): optional extra CSS classes
 */
$allowedStates = ['success', 'error', 'warning', 'info'];
$state = isset($state) && in_array((string) $state, $allowedStates, true) ? (string) $state : 'info';
$message = isset($message) ? (string) $message : '';
$messages = !empty($messages) && is_array($messages) ? array_values(array_filter(array_map('strval', $messages), static fn($v) => $v !== '')) : [];
$title = isset($title) ? (string) $title : '';
$extraClass = isset($class) ? trim((string) $class) : '';

if ($title === '' && $message === '' && $messages === []) {
    return;
}

$role = ($state === 'error' || $state === 'warning') ? 'alert' : 'status';
$legacyTone = $state === 'error' ? 'alert-error' : ($state === 'success' ? 'alert-success' : 'alert-info');
?>
<div class="c-alert c-alert--<?= e($state) ?> alert <?= e($legacyTone) ?><?= $extraClass !== '' ? ' ' . e($extraClass) : '' ?>" role="<?= e($role) ?>">
    <?php if ($title !== ''): ?>
        <strong class="c-alert__title"><?= e($title) ?></strong>
    <?php endif; ?>

    <?php if ($message !== ''): ?>
        <p class="<?= $messages === [] ? 'c-mb-0' : 'c-mb-2' ?>"><?= e($message) ?></p>
    <?php endif; ?>

    <?php if (count($messages) === 1): ?>
        <div><?= e($messages[0]) ?></div>
    <?php elseif (count($messages) > 1): ?>
        <ul class="c-alert__list">
            <?php foreach ($messages as $m): ?>
                <li><?= e($m) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
