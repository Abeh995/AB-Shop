<?php
/**
 * Diagnostics Partial: System PHP & Server Errors Log Pane
 * View Purity: 0 SQL, 0 $_POST, pure presentation (Rule 7).
 */
$entries = $systemErrors['entries'] ?? [];
$linesCount = $systemErrors['lines_count'] ?? 0;
$sizeKb = $systemErrors['size_kb'] ?? 0;
?>
<div class="diag-tab-pane <?= ($activeTab ?? 'health') === 'errors' ? 'active' : '' ?>" id="pane-errors" role="tabpanel">

    <div class="diag-card">
        <div class="diag-card-header">
            <div>
                <h3 class="diag-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>دیده‌بان خطاهای سرور و PHP (storage_errors.log)</span>
                </h3>
                <p class="diag-card-desc">ثبت بی‌درنگ استثناهای کشنده، قطعی‌های پایگاه داده و خطاهای سمت سرور</p>
            </div>

            <!-- Action buttons -->
            <div style="display:flex; gap:10px; align-items:center;">
                <span class="diag-badge" style="font-size:.76rem;">حجم فایل: <?= toPersianDigits($sizeKb) ?> KB</span>
                <form method="post" action="diagnostics.php" onsubmit="return confirm('آیا از تخلیه کامل فایل لاگ خطاهای سرور اطمینان دارید؟');">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="clear_system_log">
                    <button type="submit" class="btn btn-sm btn-outline" style="color:#DC2626; border-color:rgba(220,38,38,0.3);">
                        تخلیه لاگ خطاهای سرور
                    </button>
                </form>
            </div>
        </div>

        <div class="diag-card-body">

            <?php if (empty($entries)): ?>
                <div style="text-align:center; padding:40px 20px;">
                    <div style="font-size:2.5rem; margin-bottom:10px;">🎉</div>
                    <h4 style="margin:0 0 6px; font-weight:700; color:var(--diag-text-main);">هیچ خطای سیستمی ثبت نشده است!</h4>
                    <p style="margin:0; font-size:.82rem; color:var(--diag-text-muted);">فایل لاگ خالی است و سیستم با پایداری کامل در حال اجراست.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="diag-log-table">
                        <thead>
                            <tr>
                                <th style="width:170px;">زمان ثبت</th>
                                <th style="width:110px;">سطح خطا</th>
                                <th>پیام و ردیابی فنی</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $e): 
                                $lvl = $e['level'] ?? 'Info';
                                $badgeClass = ($lvl === 'Fatal' || $lvl === 'Database') ? 'danger' : (($lvl === 'Warning') ? 'warning' : 'info');
                            ?>
                                <tr>
                                    <td class="mono-num" style="font-size:.76rem; color:var(--diag-text-muted);">
                                        <?= e($e['timestamp'] ?: 'نامشخص') ?>
                                    </td>
                                    <td>
                                        <span class="diag-badge <?= $badgeClass ?>" style="padding:2px 8px; font-size:.74rem;">
                                            <?= e($lvl) ?>
                                        </span>
                                    </td>
                                    <td class="mono-num" style="word-break:break-all; font-size:.78rem; color:var(--diag-text-main);">
                                        <?= e($e['message']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:14px; font-size:.78rem; color:var(--diag-text-muted);">
                    نمایش <?= toPersianDigits(count($entries)) ?> خط آخر فایل لاگ خطاهای سیستمی سرور.
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>
