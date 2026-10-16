<?php
/**
 * Filter toolbar and action bar for Coupons Workstation (views/admin/coupons_partials/_toolbar.php)
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 */
$currentStatus = $filters['status'] ?? 'all';
$currentSort = $filters['sort'] ?? 'newest';
$searchQuery = $filters['q'] ?? '';
?>
<div class="ab-toolbar">
    <!-- Status Filter Chips -->
    <div class="ab-toolbar__lead">
        <div class="ab-cluster ab-cluster--xs">
            <a href="/admin/coupons.php?status=all<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
               class="ab-chip <?= $currentStatus === 'all' ? 'is-active' : '' ?>"
               aria-pressed="<?= $currentStatus === 'all' ? 'true' : 'false' ?>">
                همه کدهای تخفیف
            </a>
            <a href="/admin/coupons.php?status=active<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
               class="ab-chip <?= $currentStatus === 'active' ? 'is-active' : '' ?>"
               aria-pressed="<?= $currentStatus === 'active' ? 'true' : 'false' ?>"
               data-tone="emerald">
                <span class="ab-badge-dot"></span>
                فعال
            </a>
            <a href="/admin/coupons.php?status=expired<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
               class="ab-chip <?= $currentStatus === 'expired' ? 'is-active' : '' ?>"
               aria-pressed="<?= $currentStatus === 'expired' ? 'true' : 'false' ?>"
               data-tone="amber">
                منقضی‌شده
            </a>
            <a href="/admin/coupons.php?status=exhausted<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
               class="ab-chip <?= $currentStatus === 'exhausted' ? 'is-active' : '' ?>"
               aria-pressed="<?= $currentStatus === 'exhausted' ? 'true' : 'false' ?>">
                تکمیل ظرفیت
            </a>
            <a href="/admin/coupons.php?status=inactive<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
               class="ab-chip <?= $currentStatus === 'inactive' ? 'is-active' : '' ?>"
               aria-pressed="<?= $currentStatus === 'inactive' ? 'true' : 'false' ?>"
               data-tone="rose">
                غیرفعال دستی
            </a>
        </div>

        <!-- Search Form -->
        <form method="get" action="/admin/coupons.php" class="ab-toolbar__search">
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
            <input type="hidden" name="sort" value="<?= e($currentSort) ?>">
            <input type="search" name="q" value="<?= e($searchQuery) ?>" 
                   placeholder="جستجوی کد یا عنوان..." 
                   class="ab-input" autocomplete="off"
                   data-ab-filter-target="#couponsTable">
        </form>
    </div>

    <!-- Actions -->
    <div class="ab-toolbar__actions">
        <button type="button" class="ab-btn ab-btn--primary" data-ab-modal-open="couponModal">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            افزودن کد تخفیف
        </button>
    </div>
</div>
