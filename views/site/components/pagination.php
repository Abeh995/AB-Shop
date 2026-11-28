<?php
/**
 * Storefront Pagination Component
 *
 * Pure presentation pagination bar with Persian numerals and preserved query parameters.
 * Props:
 * - page (int): current 1-based page number
 * - totalPages (int): total available pages
 * - params (array): query string parameters to preserve (also accepts $paginationParams)
 */
$totalPages = (int) ($totalPages ?? 1);
if ($totalPages <= 1) {
    return;
}

$page = max(1, (int) ($page ?? 1));
$params = $params ?? ($paginationParams ?? $_GET);
if (!is_array($params)) {
    $params = [];
}
?>
<nav class="c-pagination pagination" aria-label="صفحه‌بندی">
    <?php if ($page > 1):
        $prevParams = array_merge($params, ['page' => $page - 1]);
    ?>
        <a href="?<?= e(http_build_query($prevParams)) ?>" class="c-pagination__item pagination-arrow" aria-label="صفحه قبلی">&rarr;</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++):
        if ($totalPages > 7) {
            if ($i !== 1 && $i !== $totalPages && abs($i - $page) > 2) {
                if ($i === 2 || $i === $totalPages - 1) {
                    echo '<span class="pagination-ellipsis" aria-hidden="true">…</span>';
                }
                continue;
            }
        }
        $pageParams = array_merge($params, ['page' => $i]);
        $isActive = ($i === $page);
    ?>
        <a href="?<?= e(http_build_query($pageParams)) ?>"
           class="c-pagination__item<?= $isActive ? ' is-active active' : '' ?>"
           <?= $isActive ? 'aria-current="page"' : '' ?>>
            <?= toPersianDigits((string) $i) ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $totalPages):
        $nextParams = array_merge($params, ['page' => $page + 1]);
    ?>
        <a href="?<?= e(http_build_query($nextParams)) ?>" class="c-pagination__item pagination-arrow" aria-label="صفحه بعدی">&larr;</a>
    <?php endif; ?>
</nav>
