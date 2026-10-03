<?php
/**
 * Diagnostics Partial: Notification Dispatch Logs (SMS & Email) Pane
 * View Purity: 0 SQL, 0 $_POST, pure presentation (Rule 7).
 */
$type = $notifications['type'] ?? 'sms';
$rows = $notifications['rows'] ?? [];
$stats = $notifications['stats'] ?? [];
$totalMatches = $notifications['total_matches'] ?? 0;
?>
<div class="diag-tab-pane <?= ($activeTab ?? 'health') === 'notifications' ? 'active' : '' ?>" id="pane-notifications" role="tabpanel">

    <div class="diag-card">
        <div class="diag-card-header">
            <div>
                <h3 class="diag-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>مرکز پایش لاگ‌های اطلاع‌رسانی (پیامک و ایمیل)</span>
                </h3>
                <p class="diag-card-desc">سوابق ارسال، تایید وب‌سرویس و جزئیات خطای ارسال به مشتریان و مدیران</p>
            </div>

            <!-- Sub-type pills -->
            <div style="display:flex; gap:8px;">
                <a href="diagnostics.php?tab=notifications&type=sms#notifications" class="btn btn-sm <?= $type === 'sms' ? 'btn-primary' : 'btn-outline' ?>">
                    پیامک‌ها (SMS)
                </a>
                <a href="diagnostics.php?tab=notifications&type=email#notifications" class="btn btn-sm <?= $type === 'email' ? 'btn-primary' : 'btn-outline' ?>">
                    ایمیل‌ها (Email)
                </a>
            </div>
        </div>

        <div class="diag-card-body">

            <!-- Filter Toolbar -->
            <form method="get" action="diagnostics.php" class="diag-toolbar">
                <input type="hidden" name="tab" value="notifications">
                <input type="hidden" name="type" value="<?= e($type) ?>">

                <input type="text" name="q" class="diag-input" style="flex:1 1 240px;" placeholder="جستجو در <?= $type === 'sms' ? 'شماره موبایل یا متن...' : 'آدرس ایمیل یا موضوع...' ?>" value="<?= e($notifSearch ?? '') ?>">

                <select name="status" class="diag-input">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="sent" <?= ($notifStatus ?? '') === 'sent' ? 'selected' : '' ?>>ارسال موفق (sent)</option>
                    <option value="failed" <?= ($notifStatus ?? '') === 'failed' ? 'selected' : '' ?>>خطای ارسال (failed)</option>
                    <option value="logged" <?= ($notifStatus ?? '') === 'logged' ? 'selected' : '' ?>>ثبت آزمایشی (logged)</option>
                </select>

                <button type="submit" class="btn btn-sm btn-primary">فیلتر لاگ‌ها</button>
                <?php if (!empty($notifSearch) || !empty($notifStatus)): ?>
                    <a href="diagnostics.php?tab=notifications&type=<?= e($type) ?>#notifications" class="btn btn-sm btn-outline">پاکسازی فیلتر</a>
                <?php endif; ?>
            </form>

            <!-- Table of Log Rows -->
            <div style="overflow-x:auto;">
                <table class="diag-log-table">
                    <thead>
                        <tr>
                            <th>گیرنده (<?= $type === 'sms' ? 'موبایل' : 'ایمیل' ?>)</th>
                            <th><?= $type === 'sms' ? 'متن پیامک' : 'موضوع ایمیل' ?></th>
                            <th>وضعیت</th>
                            <th>تاریخ و ساعت</th>
                            <th>جزئیات فنی</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): 
                            $status = $r['status'] ?? '';
                            $isSent = ($status === 'sent');
                            $isFailed = ($status === 'failed' || stripos($status, 'error') !== false);
                            $badgeClass = $isSent ? 'success' : ($isFailed ? 'danger' : 'warning');
                        ?>
                            <tr>
                                <td class="mono-num" style="font-weight:700;">
                                    <?= e($r[$type === 'sms' ? 'phone' : 'email'] ?? '') ?>
                                </td>
                                <td style="max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= e($r[$type === 'sms' ? 'message' : 'subject'] ?? '') ?>">
                                    <?= e($r[$type === 'sms' ? 'message' : 'subject'] ?? '') ?>
                                </td>
                                <td>
                                    <span class="diag-badge <?= $badgeClass ?>" style="padding:2px 8px; font-size:.74rem;">
                                        <?= e($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= toPersianDigits(date('Y/m/d H:i:s', strtotime($r['created_at'] ?? 'now'))) ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['debug_info'])): ?>
                                        <details>
                                            <summary style="cursor:pointer; color:var(--diag-primary); font-size:.78rem; font-weight:600;">مشاهده خطا/پاسخ</summary>
                                            <div class="diag-code-box" style="margin-top:6px; max-width:440px;">
                                                <?= e($r['debug_info']) ?>
                                            </div>
                                        </details>
                                    <?php else: ?>
                                        <span style="color:var(--diag-text-muted); font-size:.76rem;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:30px; color:var(--diag-text-muted);">
                                    هیچ رکوردی برای نمایش با فیلترهای انتخابی یافت نشد.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px; display:flex; justify-content:space-between; align-items:center; font-size:.8rem; color:var(--diag-text-muted);">
                <span>تعداد موارد نمایش‌یافته: <?= toPersianDigits(count($rows)) ?> از <?= toPersianDigits($totalMatches) ?> مورد</span>
            </div>

        </div>
    </div>

</div>
