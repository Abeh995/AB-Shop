<?php
/**
 * Filter toolbar and action bar for Coupons Workstation.
 */
$currentStatus = $filters['status'] ?? 'all';
$currentSort = $filters['sort'] ?? 'newest';
$searchQuery = $filters['q'] ?? '';
?>
<section class="cpn-toolbar-card">
    <!-- Status Filter Tabs -->
    <div class="cpn-filter-tabs">
        <a href="/admin/coupons.php?status=all<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
           class="cpn-filter-tab <?= $currentStatus === 'all' ? 'active' : '' ?>">
            همه کدهای تخفیف
        </a>
        <a href="/admin/coupons.php?status=active<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
           class="cpn-filter-tab <?= $currentStatus === 'active' ? 'active' : '' ?>">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10B981;"></span>
            فعال
        </a>
        <a href="/admin/coupons.php?status=expired<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
           class="cpn-filter-tab <?= $currentStatus === 'expired' ? 'active' : '' ?>">
            منقضی‌شده
        </a>
        <a href="/admin/coupons.php?status=exhausted<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
           class="cpn-filter-tab <?= $currentStatus === 'exhausted' ? 'active' : '' ?>">
            تکمیل ظرفیت
        </a>
        <a href="/admin/coupons.php?status=inactive<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
           class="cpn-filter-tab <?= $currentStatus === 'inactive' ? 'active' : '' ?>">
            غیرفعال دستی
        </a>
    </div>

    <!-- Search Form and CTA Actions -->
    <div class="cpn-toolbar-actions">
        <form method="get" action="/admin/coupons.php" class="cpn-search-form">
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
            <input type="hidden" name="sort" value="<?= e($currentSort) ?>">
            <input type="text" name="q" value="<?= e($searchQuery) ?>" 
                   placeholder="جستجوی کد یا عنوان..." class="cpn-search-input">
            <svg class="cpn-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <?php if ($searchQuery !== ''): ?>
                <a href="/admin/coupons.php?status=<?= e($currentStatus) ?>" style="position:absolute; left:28px; color:#94a3b8; text-decoration:none; font-size:12px;">✕</a>
            <?php endif; ?>
        </form>

        <button type="button" class="btn btn-primary" onclick="openCouponModal()" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; font-size:.84rem; padding:8px 16px; border-radius:10px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            افزودن کد تخفیف
        </button>
    </div>
</section>
