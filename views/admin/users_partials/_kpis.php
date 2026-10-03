<?php
/**
 * Admin Users Partial: Bento KPIs
 */
?>
<div class="usr-kpi-grid">
    <!-- 1. Total Admins -->
    <div class="usr-kpi-card">
        <div class="usr-kpi-icon" style="background:rgba(79, 70, 229, 0.1); color:#4F46E5;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="usr-kpi-info">
            <span class="usr-kpi-label">کل مدیران سیستم</span>
            <span class="usr-kpi-value"><?= toPersianDigits((string)($metrics['total'] ?? 0)) ?></span>
            <span class="usr-kpi-sub"><?= toPersianDigits((string)($metrics['active'] ?? 0)) ?> حساب فعال</span>
        </div>
    </div>

    <!-- 2. Super Admins -->
    <div class="usr-kpi-card">
        <div class="usr-kpi-icon" style="background:rgba(217, 119, 6, 0.1); color:#D97706;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div class="usr-kpi-info">
            <span class="usr-kpi-label">مدیران ارشد (Super Admin)</span>
            <span class="usr-kpi-value"><?= toPersianDigits((string)($metrics['super_admins'] ?? 0)) ?></span>
            <span class="usr-kpi-sub">دسترسی تام و بدون محدودیت</span>
        </div>
    </div>

    <!-- 3. Standard Admins -->
    <div class="usr-kpi-card">
        <div class="usr-kpi-icon" style="background:rgba(5, 150, 105, 0.1); color:#059669;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
        </div>
        <div class="usr-kpi-info">
            <span class="usr-kpi-label">مدیران عملیاتی</span>
            <span class="usr-kpi-value"><?= toPersianDigits((string)($metrics['standard_admins'] ?? 0)) ?></span>
            <span class="usr-kpi-sub">مدیریت سفارشات، محصولات، مالی</span>
        </div>
    </div>

    <!-- 4. Last Active Session -->
    <div class="usr-kpi-card">
        <div class="usr-kpi-icon" style="background:rgba(2, 132, 199, 0.1); color:#0284C7;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="usr-kpi-info">
            <span class="usr-kpi-label">آخرین ورود به سیستم</span>
            <?php if (!empty($metrics['last_login'])): ?>
                <span class="usr-kpi-value" style="font-size:1.05rem;" dir="ltr"><?= e($metrics['last_login']['username']) ?></span>
                <span class="usr-kpi-sub"><?= toPersianDigits(date('Y/m/d H:i', strtotime($metrics['last_login']['last_login_at']))) ?> (<?= e($metrics['last_login']['last_login_ip'] ?? '') ?>)</span>
            <?php else: ?>
                <span class="usr-kpi-value" style="font-size:1rem;">—</span>
                <span class="usr-kpi-sub">ثبت ورود جدیدی نیست</span>
            <?php endif; ?>
        </div>
    </div>
</div>
