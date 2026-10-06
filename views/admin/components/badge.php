<?php
/**
 * Status Badge & Chip Presentation Component (views/admin/components/badge.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var string            $label   Badge label
 * @var string|null       $text    Alias of $label
 * @var string|null       $state   Semantic state: 'success', 'danger', 'warning', 'info', 'primary', 'muted'
 * @var string|null       $type    Alias of $state
 * @var string|null       $tone    Optional color tone ('brand', 'purple', 'teal', etc.)
 * @var bool|null         $dot     Show pulsating status dot indicator
 * @var string|null       $icon    Optional SVG icon markup
 * @var string|null       $class   Extra CSS class
 */

$badgeText = $label ?? $text ?? '';
$badgeState = $state ?? $type ?? 'muted';

$classes = ['ab-badge'];
$classes[] = 'ab-badge-' . e($badgeState);
if (!empty($class)) $classes[] = e($class);

$toneAttr = !empty($tone) ? ' data-tone="' . e($tone) . '"' : '';
?>
<span class="<?= implode(' ', $classes) ?>" data-state="<?= e($badgeState) ?>"<?= $toneAttr ?>>
    <?php if (!empty($dot)): ?>
        <span class="ab-badge-dot"></span>
    <?php endif; ?>
    <?php if (!empty($icon)): ?>
        <span class="ab-badge-icon"><?= $icon ?></span>
    <?php endif; ?>
    <span class="ab-badge-text"><?= e($badgeText) ?></span>
</span>
