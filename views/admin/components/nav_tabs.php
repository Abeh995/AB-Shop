<?php
/**
 * Master Navigation Tabs Component (views/admin/components/nav_tabs.php)
 * Renders tab pills supporting either data-tab buttons or anchor links.
 *
 * @var array<int, array> $tabs  Array of tab definitions
 * @var string|null $activeTab   Active tab key
 * @var string|null $class       Optional nav CSS class
 * @var string|null $id          Optional nav ID
 * @var string|null $ariaLabel   Accessibility label
 */

$currentActive = $activeTab ?? '';
$extraClass = !empty($class) ? ' ' . e($class) : '';
$btnExtraClass = !empty($btnClass) ? ' ' . e($btnClass) : '';

// Auto-derive legacy button classes for full backward compatibility if not passed
if (empty($btnClass) && !empty($class)) {
    if (strpos($class, 'settings-tabs-nav') !== false) {
        $btnExtraClass = ' settings-tab-btn';
    } elseif (strpos($class, 'diag-tabs-nav') !== false) {
        $btnExtraClass = ' diag-tab-btn';
    } elseif (strpos($class, 'appearance-tabs-nav') !== false) {
        $btnExtraClass = ' appearance-tab-btn';
    }
}

$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';
$labelAttr = !empty($ariaLabel) ? ' aria-label="' . e($ariaLabel) . '"' : ' aria-label="تب‌های ناوبری"';
?>
<nav class="ab-nav-tabs<?= $extraClass ?>"<?= $idAttr ?><?= $labelAttr ?>>
    <?php foreach (($tabs ?? []) as $t): ?>
        <?php
        $tabKey = $t['tab'] ?? $t['id'] ?? '';
        $isActive = !empty($t['active']) || ($currentActive !== '' && $currentActive === $tabKey);
        $activeClass = $isActive ? ' active' : '';
        $isLink = !empty($t['url']);
        $itemBtnClass = !empty($t['btn_class']) ? ' ' . e($t['btn_class']) : $btnExtraClass;
        ?>
        <?php if ($isLink): ?>
            <a href="<?= e($t['url']) ?>" class="ab-tab-btn<?= $itemBtnClass ?><?= $activeClass ?>" data-tab="<?= e($tabKey) ?>">
                <?php if (!empty($t['icon'])): ?>
                    <span class="ab-tab-icon tab-icon"><?= $t['icon'] ?></span>
                <?php endif; ?>
                <span class="ab-tab-label"><?= e($t['label']) ?></span>
                <?php if (!empty($t['badge'])): ?>
                    <span class="ab-tab-badge <?= e($t['badge_class'] ?? '') ?>"><?= e((string)$t['badge']) ?></span>
                <?php endif; ?>
            </a>
        <?php else: ?>
            <button type="button" class="ab-tab-btn<?= $itemBtnClass ?><?= $activeClass ?>" data-tab="<?= e($tabKey) ?>">
                <?php if (!empty($t['icon'])): ?>
                    <span class="ab-tab-icon tab-icon"><?= $t['icon'] ?></span>
                <?php endif; ?>
                <span class="ab-tab-label"><?= e($t['label']) ?></span>
                <?php if (!empty($t['badge'])): ?>
                    <span class="ab-tab-badge <?= e($t['badge_class'] ?? '') ?>"><?= e((string)$t['badge']) ?></span>
                <?php endif; ?>
            </button>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
