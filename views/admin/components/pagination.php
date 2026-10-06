<?php
/**
 * Pagination Presentation Component (views/admin/components/pagination.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var int         $page      Current active page (default 1)
 * @var int         $pages     Total number of pages (default 1)
 * @var string|null $base_url  Base URL prefix (e.g. "?page=" or "users.php?status=active&page=")
 * @var int|null    $total     Total items count
 * @var int|null    $per_page  Items per page
 * @var string|null $class     Extra pagination class
 * @var string|null $id        Pagination ID
 */

$curPage = max(1, (int)($page ?? 1));
$totalPages = max(1, (int)($pages ?? 1));
$urlPrefix = $base_url ?? '?page=';

$extraClass = !empty($class) ? ' ' . e($class) : '';
$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';

// Calculate start and end indices if total and per_page are provided
$infoText = '';
if (!empty($total)) {
    $totalCount = (int)$total;
    $itemsPerPage = !empty($per_page) ? (int)$per_page : 20;
    $startItem = min($totalCount, ($curPage - 1) * $itemsPerPage + 1);
    $endItem = min($totalCount, $curPage * $itemsPerPage);
    $infoText = 'نمایش ' . toPersianDigits((string)$startItem) . ' تا ' . toPersianDigits((string)$endItem) . ' از ' . toPersianDigits((string)$totalCount) . ' مورد';
} else {
    $infoText = 'صفحه ' . toPersianDigits((string)$curPage) . ' از ' . toPersianDigits((string)$totalPages);
}

// Generate page numbers window (with ellipsis)
$window = 2;
$pageNumbers = [];
for ($p = 1; $p <= $totalPages; $p++) {
    if ($p === 1 || $p === $totalPages || ($p >= $curPage - $window && $p <= $curPage + $window)) {
        $pageNumbers[] = $p;
    } elseif (!empty($pageNumbers) && end($pageNumbers) !== '...') {
        $pageNumbers[] = '...';
    }
}
?>
<nav class="ab-pagination<?= $extraClass ?>"<?= $idAttr ?> aria-label="ناوبری صفحات لیست">
    <div class="ab-pagination__info">
        <?= $infoText ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="ab-pagination__nav">
            <!-- Previous Page -->
            <?php if ($curPage > 1): ?>
                <a href="<?= e($urlPrefix . ($curPage - 1)) ?>" class="ab-pagination__item" aria-label="صفحه قبلی">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            <?php endif; ?>

            <!-- Page Number Items -->
            <?php foreach ($pageNumbers as $pNum): ?>
                <?php if ($pNum === '...'): ?>
                    <span class="ab-pagination__ellipsis">…</span>
                <?php elseif ($pNum === $curPage): ?>
                    <span class="ab-pagination__item active" aria-current="page"><?= toPersianDigits((string)$pNum) ?></span>
                <?php else: ?>
                    <a href="<?= e($urlPrefix . $pNum) ?>" class="ab-pagination__item"><?= toPersianDigits((string)$pNum) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Next Page -->
            <?php if ($curPage < $totalPages): ?>
                <a href="<?= e($urlPrefix . ($curPage + 1)) ?>" class="ab-pagination__item" aria-label="صفحه بعدی">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</nav>
