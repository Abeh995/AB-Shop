<?php
/**
 * Diagnostics Partial: Master Tab Navigation
 * View Purity: 0 SQL, 0 $_POST, pure presentation (Rule 7).
 */
$currentTab = $activeTab ?? 'health';
?>
<nav class="diag-tabs-nav" id="diagTabsNav" aria-label="تب‌های عیب‌یابی و لاگ">
    <button type="button" class="diag-tab-btn <?= $currentTab === 'health' ? 'active' : '' ?>" data-tab="health">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        <span>پایش منابع و سرور</span>
    </button>

    <button type="button" class="diag-tab-btn <?= $currentTab === 'tests' ? 'active' : '' ?>" data-tab="tests">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
        <span>تست زنده اتصال سرویس‌ها</span>
    </button>

    <button type="button" class="diag-tab-btn <?= $currentTab === 'notifications' ? 'active' : '' ?>" data-tab="notifications">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span>لاگ پیامک و ایمیل</span>
    </button>

    <button type="button" class="diag-tab-btn <?= $currentTab === 'errors' ? 'active' : '' ?>" data-tab="errors">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>خطاهای سیستمی PHP</span>
        <?php if (!empty($systemErrors['lines_count'])): ?>
            <span class="diag-badge danger" style="padding:2px 8px; font-size:.72rem;"><?= toPersianDigits($systemErrors['lines_count']) ?></span>
        <?php endif; ?>
    </button>

    <button type="button" class="diag-tab-btn <?= $currentTab === 'audit' ? 'active' : '' ?>" data-tab="audit">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span>ردپای امنیتی مدیران</span>
    </button>
</nav>
