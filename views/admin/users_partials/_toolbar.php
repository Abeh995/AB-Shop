<?php
/**
 * Admin Users Partial: Action & Filter Toolbar
 */
?>
<div class="usr-toolbar">
    <!-- Live Search -->
    <div class="usr-search-box">
        <svg class="usr-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="adminSearchInput" placeholder="جستجو بر اساس نام، نام کاربری، ایمیل یا موبایل...">
    </div>

    <!-- Filter Pills -->
    <div class="usr-filter-group">
        <button type="button" class="usr-filter-pill active" data-filter="all">همه (<?= toPersianDigits((string)count($admins)) ?>)</button>
        <button type="button" class="usr-filter-pill" data-filter="super_admin">مدیران ارشد</button>
        <button type="button" class="usr-filter-pill" data-filter="admin">مدیران عادی</button>
        <button type="button" class="usr-filter-pill" data-filter="active">فعال</button>
        <button type="button" class="usr-filter-pill" data-filter="inactive">غیرفعال</button>
    </div>

    <!-- Add Admin CTA -->
    <button type="button" class="btn btn-primary" onclick="openCreateModal()" style="display:flex; align-items:center; gap:6px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>افزودن مدیر جدید</span>
    </button>
</div>
