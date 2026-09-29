<?php
/**
 * Shared pagination component.
 * Expects $totalPages (int), $page (int), and optional $paginationParams (array).
 */
if (!isset($totalPages) || $totalPages <= 1) {
    return;
}

$page = max(1, (int) ($page ?? 1));
$params = $paginationParams ?? $_GET;
?>
<div class="pagination" role="navigation" aria-label="صفحه‌بندی">
    <?php if ($page > 1):
        $prevParams = array_merge($params, ['page' => $page - 1]);
    ?>
        <a href="?<?= http_build_query($prevParams) ?>" class="pagination-arrow" aria-label="صفحه قبلی">&rarr;</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++):
        if ($totalPages > 7) {
            // Keep window around current page
            if ($i !== 1 && $i !== $totalPages && abs($i - $page) > 2) {
                if ($i === 2 || $i === $totalPages - 1) {
                    echo '<span class="pagination-ellipsis">…</span>';
                }
                continue;
            }
        }
        $pageParams = array_merge($params, ['page' => $i]);
    ?>
        <a href="?<?= http_build_query($pageParams) ?>"
           class="<?= $i === $page ? 'active' : '' ?>"
           <?= $i === $page ? 'aria-current="page"' : '' ?>>
            <?= toPersianDigits((string)$i) ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $totalPages):
        $nextParams = array_merge($params, ['page' => $page + 1]);
    ?>
        <a href="?<?= http_build_query($nextParams) ?>" class="pagination-arrow" aria-label="صفحه بعدی">&larr;</a>
    <?php endif; ?>
</div>
