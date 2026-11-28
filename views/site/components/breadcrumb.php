<?php
/**
 * Storefront Breadcrumb Navigation Component
 *
 * Pure presentation trail with current-page semantic state.
 * Props:
 * - items (array): list of ['name' => string, 'url' => string|null]
 */
$items = !empty($items) && is_array($items) ? array_values($items) : [];
if ($items === []) {
    return;
}

$lastIdx = count($items) - 1;
?>
<nav class="c-breadcrumb breadcrumb-trail" aria-label="مسیر صفحه">
    <?php foreach ($items as $bi => $bc):
        $name = (string) ($bc['name'] ?? '');
        $url = $bc['url'] ?? null;
        $isLast = ($bi === $lastIdx);
    ?>
        <?php if ($bi > 0): ?><span class="c-breadcrumb__sep" aria-hidden="true">/</span><?php endif; ?>
        <?php if ($isLast || empty($url)): ?>
            <span class="c-fw-bold"<?= $isLast ? ' aria-current="page"' : '' ?>><?= e($name) ?></span>
        <?php else: ?>
            <a href="<?= e((string) $url) ?>"><?= e($name) ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
