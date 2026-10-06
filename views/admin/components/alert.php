<?php
/**
 * Alert & Notification Banner Presentation Component (views/admin/components/alert.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var string            $message     Alert text / HTML
 * @var string|null       $state       Semantic state: 'success', 'warning', 'danger', 'error', 'info' (default 'info')
 * @var string|null       $title       Optional alert headline
 * @var bool|null         $dismissible If true, renders a close button
 * @var string|null       $icon        Custom SVG icon
 * @var string|null       $class       Extra CSS class
 * @var string|null       $id          Element ID
 */

$st = $state ?? 'info';
if ($st === 'error') $st = 'danger';

$extraClass = !empty($class) ? ' ' . e($class) : '';
$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';

$defaultIcons = [
    'success' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
    'warning' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    'danger'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    'info'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
];

$iconMarkup = $icon ?? ($defaultIcons[$st] ?? $defaultIcons['info']);
?>
<div class="ab-alert<?= $extraClass ?>" data-state="<?= e($st) ?>" role="alert"<?= $idAttr ?>>
    <div class="ab-alert__icon">
        <?= $iconMarkup ?>
    </div>
    <div class="ab-alert__content">
        <?php if (!empty($title)): ?>
            <h4 class="ab-alert__title"><?= e($title) ?></h4>
        <?php endif; ?>
        <div class="ab-alert__message">
            <?= $message ?? '' ?>
        </div>
    </div>
    <?php if (!empty($dismissible)): ?>
        <button type="button" class="ab-icon-btn ab-icon-btn--sm ab-alert__close" onclick="this.closest('.ab-alert').remove()" aria-label="بستن اعلان">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    <?php endif; ?>
</div>
