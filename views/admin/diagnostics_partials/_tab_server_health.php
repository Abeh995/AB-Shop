<?php
/**
 * Diagnostics Partial: Server & Host Resource Health Pane
 * View Purity: 0 SQL, 0 $_POST, pure presentation (Rule 7).
 */
$db = $health['database'] ?? [];
$storage = $health['storage'] ?? [];
$php = $health['php'] ?? [];
$clock = $health['clock'] ?? [];
$dirs = $storage['directories'] ?? [];

$dbBarClass = ($db['percent'] ?? 0) > 85 ? 'crit' : (($db['percent'] ?? 0) > 65 ? 'warn' : 'safe');
$diskBarClass = ($storage['percent'] ?? 0) > 85 ? 'crit' : (($storage['percent'] ?? 0) > 65 ? 'warn' : 'safe');
?>
<div class="diag-tab-pane <?= ($activeTab ?? 'health') === 'health' ? 'active' : '' ?>" id="pane-health" role="tabpanel">

    <div class="diag-grid-2">
        <!-- 1. Database Quota Card -->
        <div class="diag-card">
            <div class="diag-card-header">
                <div>
                    <h3 class="diag-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                        <span>سهمیه پایگاه داده (MySQL Quota)</span>
                    </h3>
                    <p class="diag-card-desc">سقف مجاز هاست اشتراکی: ۲۰۰ مگابایت (دایرکت‌ادمین)</p>
                </div>
                <span class="diag-badge <?= $dbBarClass === 'safe' ? 'success' : ($dbBarClass === 'warn' ? 'warning' : 'danger') ?>">
                    <?= toPersianDigits((float)($db['percent'] ?? 0)) ?>٪ مصرف
                </span>
            </div>
            <div class="diag-card-body">
                <div class="diag-gauge-wrap">
                    <div class="diag-gauge-info">
                        <span style="font-weight:700;">حجم مصرفی فعلی: <?= toPersianDigits(number_format((float)($db['size_mb'] ?? 0), 2)) ?> مگابایت</span>
                        <span style="color:var(--diag-text-muted);">سقف: <?= toPersianDigits((int)($db['quota_mb'] ?? 200)) ?> مگابایت</span>
                    </div>
                    <div class="diag-gauge-track">
                        <div class="diag-gauge-bar <?= $dbBarClass ?>" style="width: <?= min(100, max(2, (float)($db['percent'] ?? 0))) ?>%;"></div>
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; margin-top:16px; font-size:.84rem; border-bottom:1px solid var(--diag-border-subtle); padding-bottom:10px;">
                    <span>تعداد کل جداول: <strong><?= toPersianDigits((int)($db['table_count'] ?? 0)) ?></strong> جدول</span>
                    <span>مجموع ردیف‌های تخمینی: <strong><?= toPersianDigits(number_format((int)($db['total_rows'] ?? 0))) ?></strong> ردیف</span>
                </div>

                <h4 style="margin:16px 0 8px; font-size:.86rem; font-weight:700; color:var(--diag-text-secondary);">سنگین‌ترین جداول دیتابیس:</h4>
                <div style="display:grid; grid-template-columns:1fr; gap:6px;">
                    <?php foreach (($db['top_tables'] ?? []) as $table): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; background:var(--diag-bg-subtle); padding:6px 10px; border-radius:6px; font-size:.8rem;">
                            <span class="mono-num" style="font-weight:600;"><?= e($table['name']) ?></span>
                            <div style="display:flex; gap:12px; align-items:center;">
                                <span style="color:var(--diag-text-muted); font-size:.76rem;"><?= toPersianDigits(number_format((int)($table['rows'] ?? 0))) ?> ردیف</span>
                                <span class="mono-num" style="font-weight:700; color:var(--diag-primary);"><?= toPersianDigits(number_format((float)($table['size_mb'] ?? 0), 2)) ?> MB</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 2. Storage & Directory Card -->
        <div class="diag-card">
            <div class="diag-card-header">
                <div>
                    <h3 class="diag-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        <span>فضای ذخیره‌سازی رسانه‌ها و آپلود</span>
                    </h3>
                    <p class="diag-card-desc">سقف مجاز فضای دیسک هاست: ۱.۵ گیگابایت (۱۵۰۰ مگابایت)</p>
                </div>
                <span class="diag-badge <?= $diskBarClass === 'safe' ? 'success' : ($diskBarClass === 'warn' ? 'warning' : 'danger') ?>">
                    <?= toPersianDigits((float)($storage['percent'] ?? 0)) ?>٪ مصرف
                </span>
            </div>
            <div class="diag-card-body">
                <div class="diag-gauge-wrap">
                    <div class="diag-gauge-info">
                        <span style="font-weight:700;">حجم پوشه‌های آپلود: <?= toPersianDigits(number_format((float)($storage['total_mb'] ?? 0), 2)) ?> مگابایت</span>
                        <span style="color:var(--diag-text-muted);">سقف: <?= toPersianDigits((int)($storage['quota_mb'] ?? 1500)) ?> مگابایت</span>
                    </div>
                    <div class="diag-gauge-track">
                        <div class="diag-gauge-bar <?= $diskBarClass ?>" style="width: <?= min(100, max(1, (float)($storage['percent'] ?? 0))) ?>%;"></div>
                    </div>
                </div>

                <div style="margin-top:16px;">
                    <table class="diag-log-table">
                        <thead>
                            <tr>
                                <th>دایرکتوری</th>
                                <th>مجوز نوشتن</th>
                                <th>تعداد فایل</th>
                                <th>حجم</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dirs as $key => $dir): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600;"><?= e($dir['name']) ?></div>
                                        <div class="mono-num" style="font-size:.72rem; color:var(--diag-text-muted);"><?= e(basename($dir['path'])) ?>/</div>
                                    </td>
                                    <td>
                                        <?php if ($dir['writable']): ?>
                                            <span class="diag-badge success" style="padding:2px 8px; font-size:.74rem;">قابل نوشتن ✓</span>
                                        <?php else: ?>
                                            <span class="diag-badge danger" style="padding:2px 8px; font-size:.74rem;">غیرقابل دسترس ✗</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="mono-num"><?= toPersianDigits(number_format($dir['files_count'])) ?></td>
                                    <td class="mono-num" style="font-weight:700;"><?= toPersianDigits(number_format((float)$dir['size_mb'], 2)) ?> MB</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:16px; display:flex; justify-content:flex-end;">
                    <form method="post" action="diagnostics.php" onsubmit="return confirm('آیا از پاکسازی فایل‌های موقت قدیمی‌تر از ۲۴ ساعت اطمینان دارید؟');">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="clean_tmp_files">
                        <button type="submit" class="btn btn-sm btn-outline" style="font-size:.8rem; display:inline-flex; align-items:center; gap:6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            <span>پاکسازی فایل‌های موقت فیش بانکی (سطل زباله)</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="diag-grid-2">
        <!-- 3. PHP Environment Card -->
        <div class="diag-card">
            <div class="diag-card-header">
                <div>
                    <h3 class="diag-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m10 10-2 2 2 2"/><path d="m14 14 2-2-2-2"/></svg>
                        <span>محیط اجرا و اکستنشن‌های PHP</span>
                    </h3>
                    <p class="diag-card-desc">پیکربندی سرور آپاچی / PHP-FPM هاست اشتراکی</p>
                </div>
            </div>
            <div class="diag-card-body">
                <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:10px; margin-bottom:16px; font-size:.84rem;">
                    <div style="background:var(--diag-bg-subtle); padding:10px; border-radius:6px;">
                        <span style="color:var(--diag-text-muted); display:block; font-size:.76rem;">سقف حافظه مجاز (Memory Limit):</span>
                        <strong class="mono-num"><?= e($php['memory_limit']) ?></strong>
                    </div>
                    <div style="background:var(--diag-bg-subtle); padding:10px; border-radius:6px;">
                        <span style="color:var(--diag-text-muted); display:block; font-size:.76rem;">سقف حجم آپلود فایل (Upload Max):</span>
                        <strong class="mono-num"><?= e($php['upload_max_filesize']) ?></strong>
                    </div>
                    <div style="background:var(--diag-bg-subtle); padding:10px; border-radius:6px;">
                        <span style="color:var(--diag-text-muted); display:block; font-size:.76rem;">حداکثر حجم بسته ارسالی (Post Max):</span>
                        <strong class="mono-num"><?= e($php['post_max_size']) ?></strong>
                    </div>
                    <div style="background:var(--diag-bg-subtle); padding:10px; border-radius:6px;">
                        <span style="color:var(--diag-text-muted); display:block; font-size:.76rem;">حداکثر زمان پردازش (Execution Time):</span>
                        <strong class="mono-num"><?= e($php['max_execution_time']) ?> ثانیه</strong>
                    </div>
                </div>

                <h4 style="margin:14px 0 8px; font-size:.86rem; font-weight:700; color:var(--diag-text-secondary);">وضعیت اکستنشن‌های حیاتی:</h4>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    <?php foreach (($php['extensions'] ?? []) as $extName => $extStatus): ?>
                        <span class="diag-badge <?= $extStatus ? 'success' : 'danger' ?>" style="font-size:.76rem;">
                            <?= $extStatus ? '✓' : '✗' ?> <?= e(strtoupper($extName)) ?>
                            <?= ($extName === 'webp_gd' && $extStatus) ? '(WebP فعال)' : '' ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 4. Clock Synchronization Card -->
        <div class="diag-card">
            <div class="diag-card-header">
                <div>
                    <h3 class="diag-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>همگام‌سازی زمان (Clock Synchronization)</span>
                    </h3>
                    <p class="diag-card-desc">تطابق ساعت PHP با نشست پایگاه داده MySQL</p>
                </div>
                <span class="diag-badge <?= ($clock['is_synced'] ?? false) ? 'success' : 'danger' ?>">
                    <?= ($clock['is_synced'] ?? false) ? 'کاملاً همگام' : 'ناهماهنگ' ?>
                </span>
            </div>
            <div class="diag-card-body">
                <div style="background:var(--diag-bg-subtle); border-radius:8px; padding:12px; margin-bottom:14px; font-size:.83rem;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="color:var(--diag-text-muted);">زمان سیستم (PHP Asia/Tehran):</span>
                        <span class="mono-num" style="font-weight:700;"><?= e($clock['php_time'] ?? '') ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="color:var(--diag-text-muted);">زمان پایگاه داده (MySQL Session NOW):</span>
                        <span class="mono-num" style="font-weight:700;"><?= e($clock['db_time'] ?? '') ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--diag-text-muted);">اختلاف زمانی:</span>
                        <span class="mono-num"><?= toPersianDigits((int)($clock['drift_sec'] ?? 0)) ?> ثانیه</span>
                    </div>
                </div>
                <p style="margin:0; font-size:.78rem; color:var(--diag-text-muted); line-height:1.6;">
                    <strong>چرا این شاخص حیاتی است؟</strong> هاست‌های اشتراکی ساعت MySQL را روی UTC نگه می‌دارند. در صورت عدم انطباق نشست دیتابیس با ساعت ایران، گارانتی قیمت سبد خرید و اعتبار کدهای پیامکی منقضی می‌شوند. پروژه با دستور پویا در <code>db.php</code> ساعت را همگام نگه می‌دارد.
                </p>
            </div>
        </div>
    </div>

</div>
