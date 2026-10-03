<?php
/**
 * Diagnostics Partial: Administrative Audit Trail Logs Pane
 * View Purity: 0 SQL, 0 $_POST, pure presentation (Rule 7).
 */
$rows = $auditTrail['rows'] ?? [];
$actions = $auditTrail['actions'] ?? [];
?>
<div class="diag-tab-pane <?= ($activeTab ?? 'health') === 'audit' ? 'active' : '' ?>" id="pane-audit" role="tabpanel">

    <div class="diag-card">
        <div class="diag-card-header">
            <div>
                <h3 class="diag-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>ردپای امنیتی و فعالیت‌های مدیران (Admin Audit Trail)</span>
                </h3>
                <p class="diag-card-desc">سوابق ورود به پنل، تغییرات قیمت، ویرایش دسترسی‌ها و اقدامات حساس مدیران</p>
            </div>
        </div>

        <div class="diag-card-body">

            <!-- Filter Toolbar -->
            <form method="get" action="diagnostics.php" class="diag-toolbar">
                <input type="hidden" name="tab" value="audit">

                <input type="text" name="audit_q" class="diag-input" style="flex:1 1 240px;" placeholder="جستجو در شرح عملیات، نام مدیر، آدرس IP..." value="<?= e($auditSearch ?? '') ?>">

                <select name="audit_action" class="diag-input">
                    <option value="">همه عملیات‌ها</option>
                    <?php foreach ($actions as $act): ?>
                        <option value="<?= e($act) ?>" <?= ($auditAction ?? '') === $act ? 'selected' : '' ?>>
                            <?= e($act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-sm btn-primary">فیلتر فعالیت‌ها</button>
                <?php if (!empty($auditSearch) || !empty($auditAction)): ?>
                    <a href="diagnostics.php?tab=audit#audit" class="btn btn-sm btn-outline">پاکسازی فیلتر</a>
                <?php endif; ?>
            </form>

            <div style="overflow-x:auto;">
                <table class="diag-log-table">
                    <thead>
                        <tr>
                            <th>مدیر</th>
                            <th>نوع عملیات</th>
                            <th>شرح اقدام</th>
                            <th>آدرس IP</th>
                            <th>زمان ثبت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): 
                            $act = $row['action'] ?? '';
                            $actClass = ($act === 'login') ? 'success' : (($act === 'delete') ? 'danger' : 'warning');
                        ?>
                            <tr>
                                <td>
                                    <div style="font-weight:700;"><?= e($row['admin_username'] ?? '—') ?></div>
                                    <?php if (!empty($row['admin_full_name'])): ?>
                                        <div style="font-size:.74rem; color:var(--diag-text-muted);"><?= e($row['admin_full_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="diag-badge <?= $actClass ?>" style="padding:2px 8px; font-size:.74rem;">
                                        <?= e($act) ?>
                                    </span>
                                </td>
                                <td style="font-size:.82rem;">
                                    <?= e($row['description'] ?? '—') ?>
                                </td>
                                <td class="mono-num" style="font-size:.78rem; color:var(--diag-text-muted);">
                                    <?= e($row['ip_address'] ?? '—') ?>
                                </td>
                                <td>
                                    <?= toPersianDigits(date('Y/m/d H:i:s', strtotime($row['created_at'] ?? 'now'))) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:30px; color:var(--diag-text-muted);">
                                    هیچ سابقه امنیتی برای نمایش یافت نشد.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
