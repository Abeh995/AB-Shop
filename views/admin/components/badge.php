<?php
/**
 * Status Badge Component (views/admin/components/badge.php)
 *
 * @var string $text       Badge text
 * @var string|null $type  Variant: success, danger, warning, info, primary, muted
 * @var bool|null $dot     Whether to show an active dot indicator
 * @var string|null $icon  Optional SVG icon
 * @var string|null $class Optional additional CSS class
 */

$variant = $type ?? 'muted';
$extraClass = !empty($class) ? ' ' . e($class) : '';
?>
<span class="ab-badge ab-badge-<?= e($variant) ?><?= $extraClass ?>">
    <?php if (!empty($dot)): ?>
        <span class="ab-badge-dot"></span>
    <?php endif; ?>
    <?php if (!empty($icon)): ?>
        <span class="ab-badge-icon"><?= $icon ?></span>
    <?php endif; ?>
    <span class="ab-badge-text"><?= e($text) ?></span>
</span>
