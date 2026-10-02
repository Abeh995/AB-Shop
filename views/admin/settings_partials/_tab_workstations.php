<?php
/**
 * Settings Partial: Specialized Workstations (Bento Grid)
 */
?>
<div class="settings-tab-pane" id="pane-workstations" role="tabpanel">
    <div style="margin-bottom:16px;">
        <h3 style="margin:0 0 4px; font-size:1.1rem; font-weight:800; color:var(--set-text-main);">ایستگاه‌های کاری تخصصی فروشگاه</h3>
        <p style="margin:0; font-size:.85rem; color:var(--set-text-muted);">
            برای پیکربندی فرآیندهای چندمرحله‌ای و ابزارهای متمرکز وارد ایستگاه مربوطه شوید.
        </p>
    </div>

    <div class="settings-bento-grid">

        <!-- 1. Appearance & Landing -->
        <a href="appearance.php" class="bento-station-card">
            <div>
                <div class="station-lead">
                    <div class="station-icon-box" style="background:rgba(180, 83, 9, 0.1); color:#B45309;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                    </div>
                    <span class="pulse-badge active" style="font-size:.72rem;">تم: <?= e($stats['activeThemeName'] ?? 'پیش‌فرض') ?></span>
                </div>
                <h4 class="station-title">ظاهر، لندینگ و تم‌ها</h4>
                <p class="station-desc">پیکربندی بخش‌های صفحه اصلی، پالت رنگی، لوگوی سایت، نوار اعلان و متن‌های فوتر.</p>
            </div>
            <div class="station-action-link">
                <span>مدیریت ظاهر و لندینگ ←</span>
            </div>
        </a>

        <!-- 2. Shipping Methods -->
        <a href="shipping_methods.php" class="bento-station-card">
            <div>
                <div class="station-lead">
                    <div class="station-icon-box" style="background:rgba(3, 105, 161, 0.1); color:#0369A1;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-5l-3-4h-5v10"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>
                    </div>
                    <span class="pulse-badge active" style="font-size:.72rem;"><?= (int)($stats['shippingActiveCount'] ?? 0) ?> روش فعال</span>
                </div>
                <h4 class="station-title">روش‌ها و هزینه ارسال</h4>
                <p class="station-desc">قواعد پستی استان‌ها، هزینه پیک شهری، هزینه پست پیشتاز و سقف ارسال رایگان.</p>
            </div>
            <div class="station-action-link">
                <span>تنظیم روش‌های ارسال ←</span>
            </div>
        </a>

        <!-- 3. SMS Patterns -->
        <a href="sms_patterns.php" class="bento-station-card">
            <div>
                <div class="station-lead">
                    <div class="station-icon-box" style="background:rgba(124, 58, 237, 0.1); color:#7C3AED;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <span class="pulse-badge active" style="font-size:.72rem;"><?= (int)($stats['smsActiveCount'] ?? 0) ?> الگوی فعال</span>
                </div>
                <h4 class="station-title">الگوهای پیامک (فراز اس‌ام‌اس)</h4>
                <p class="station-desc">کدهای پترن، متغیرهای پویا، رویدادهای تغییر وضعیت سفارش، پیامک OTP و تست زنده.</p>
            </div>
            <div class="station-action-link">
                <span>مدیریت الگوهای پیامک ←</span>
            </div>
        </a>

        <!-- 4. Email Accounts -->
        <a href="email_accounts.php" class="bento-station-card">
            <div>
                <div class="station-lead">
                    <div class="station-icon-box" style="background:rgba(5, 150, 105, 0.1); color:#059669;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                    <span class="pulse-badge active" style="font-size:.72rem;"><?= (int)($stats['emailActiveCount'] ?? 0) ?> حساب ایمیل</span>
                </div>
                <h4 class="station-title">حساب‌های ایمیل و وب‌میل</h4>
                <p class="station-desc">پیکربندی IMAP/SMTP، صندوق دریافت و ارسال ایمیل سازمانی برای ارتباط رسمی با خریداران.</p>
            </div>
            <div class="station-action-link">
                <span>مدیریت ایمیل‌ها ←</span>
            </div>
        </a>

        <?php if (function_exists('isSuperAdmin') && isSuperAdmin()): ?>
            <!-- 5. Admins & Access -->
            <a href="users.php" class="bento-station-card">
                <div>
                    <div class="station-lead">
                        <div class="station-icon-box" style="background:rgba(79, 70, 229, 0.1); color:#4F46E5;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <span class="pulse-badge active" style="font-size:.72rem;"><?= (int)($stats['adminActiveCount'] ?? 0) ?> ادمین</span>
                    </div>
                    <h4 class="station-title">مدیران سایت و دسترسی‌ها</h4>
                    <p class="station-desc">تعریف مدیر جدید، تفکیک نقش‌ها، تغییر رمز عبور و فعال/غیرفعال‌سازی حساب‌های کاربری.</p>
                </div>
                <div class="station-action-link">
                    <span>مدیریت مدیران ←</span>
                </div>
            </a>

            <!-- 6. Diagnostics & Logs -->
            <a href="diagnostics.php" class="bento-station-card">
                <div>
                    <div class="station-lead">
                        <div class="station-icon-box" style="background:rgba(220, 38, 38, 0.1); color:#DC2626;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        </div>
                        <span class="pulse-badge active" style="font-size:.72rem;">پایش سلامت</span>
                    </div>
                    <h4 class="station-title">عیب‌یابی، اتصال و لاگ‌ها</h4>
                    <p class="station-desc">تست زنده اتصال وب‌سرویس پیامک و وب‌میل، مانده اعتبار پیامک و بررسی لاگ خطاهای سیستم.</p>
                </div>
                <div class="station-action-link">
                    <span>بررسی وضعیت سیستم ←</span>
                </div>
            </a>
        <?php endif; ?>

    </div>
</div>
