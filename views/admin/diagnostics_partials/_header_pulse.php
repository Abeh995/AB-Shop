<?php
/**
 * Diagnostics Partial: System Pulse Header
 * Displays host quotas, database usage, disk usage, and server synchronization state.
 * View Purity: 0 SQL, 0 $_POST, pure presentation (Rule 7).
 */
$db = $health['database'] ?? [];
$storage = $health['storage'] ?? [];
$clock = $health['clock'] ?? [];
$php = $health['php'] ?? [];
$sysLog = $health['system_log'] ?? [];

$dbWarnClass = ($db['percent'] ?? 0) > 85 ? 'danger' : (($db['percent'] ?? 0) > 65 ? 'warning' : 'success');
$diskWarnClass = ($storage['percent'] ?? 0) > 85 ? 'danger' : (($storage['percent'] ?? 0) > 65 ? 'warning' : 'success');
$clockClass = ($clock['is_synced'] ?? false) ? 'success' : 'danger';
?>
<div class="diag-pulse-card">
    <div class="diag-pulse-lead">
        <div class="diag-pulse-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div>
            <h2 class="diag-pulse-title">مرکز عیب‌یابی و پایش سلامت سیستم</h2>
            <p class="diag-pulse-desc">دیده‌بانی سهمیه هاست، تست زنده وب‌سرویس‌ها، تحلیل خطاهای سرور و لاگ‌های امنیتی</p>
        </div>
    </div>

    <div class="diag-pulse-metrics">
        <!-- DB Quota Badge -->
        <span class="diag-badge <?= $dbWarnClass ?>" title="مصرف پایگاه داده در برابر سقف ۲۰۰ مگابایت هاست اشتراکی">
            <span class="diag-dot"></span>
            دیتابیس: <?= toPersianDigits(number_format((float)($db['size_mb'] ?? 0), 1)) ?>MB / <?= toPersianDigits((int)($db['quota_mb'] ?? 200)) ?>MB (<?= toPersianDigits((float)($db['percent'] ?? 0)) ?>٪)
        </span>

        <!-- Disk Quota Badge -->
        <span class="diag-badge <?= $diskWarnClass ?>" title="فضای ذخیره‌سازی پوشه‌های آپلود در برابر سقف ۱.۵ گیگابایت هاست">
            <span class="diag-dot"></span>
            پوشه رسانه: <?= toPersianDigits(number_format((float)($storage['total_mb'] ?? 0), 1)) ?>MB / <?= toPersianDigits((int)($storage['quota_mb'] ?? 1500)) ?>MB
        </span>

        <!-- Clock Sync Badge -->
        <span class="diag-badge <?= $clockClass ?>" title="همگام بودن زمان نشست MySQL با زمان PHP Asia/Tehran">
            <span class="diag-dot"></span>
            <?= ($clock['is_synced'] ?? false) ? 'زمان‌سنجی: همگام' : 'ناهماهنگی ساعت دیتابیس!' ?>
        </span>

        <!-- PHP Version Badge -->
        <span class="diag-badge" title="نسخه فعال موتور PHP">
            PHP v<?= e($php['version'] ?? PHP_VERSION) ?>
        </span>
    </div>
</div>
