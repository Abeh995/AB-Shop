<?php
/**
 * Admin Users Partial: Security & Activity Audit Trail
 */

$actionColorMap = [
    'login' => ['bg' => 'rgba(5, 150, 105, 0.1)', 'color' => '#059669', 'label' => 'ورود به سیستم'],
    'admin_create' => ['bg' => 'rgba(79, 70, 229, 0.1)', 'color' => '#4F46E5', 'label' => 'ثبت مدیر'],
    'admin_update' => ['bg' => 'rgba(2, 132, 199, 0.1)', 'color' => '#0284C7', 'label' => 'ویرایش مدیر'],
    'password_change' => ['bg' => 'rgba(217, 119, 6, 0.1)', 'color' => '#D97706', 'label' => 'تغییر رمز'],
    'status_toggle' => ['bg' => 'rgba(124, 58, 237, 0.1)', 'color' => '#7C3AED', 'label' => 'تغییر دسترسی'],
    'admin_delete' => ['bg' => 'rgba(220, 38, 38, 0.1)', 'color' => '#DC2626', 'label' => 'حذف مدیر'],
];
?>
<div class="usr-audit-card">
    <div class="usr-audit-header">
        <h4 class="usr-audit-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>ردپای امنیتی و لاگ رویدادهای مدیران (Audit Trail)</span>
        </h4>
        <span class="pulse-badge active" style="font-size:0.72rem;">پایش بلادرنگ</span>
    </div>

    <?php if (!empty($recentLogs)): ?>
        <table class="usr-audit-table">
            <thead>
                <tr>
                    <th>مجری عملیات</th>
                    <th>نوع رویداد</th>
                    <th>شرح اقدام</th>
                    <th>آدرس IP</th>
                    <th>زمان ثبت</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentLogs as $log): 
                    $actMeta = $actionColorMap[$log['action']] ?? ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => $log['action']];
                ?>
                <tr>
                    <td>
                        <span style="font-weight:700; color:var(--usr-text);">
                            <?= e($log['full_name'] ?: $log['username'] ?: ('ادمین #' . $log['admin_id'])) ?>
                        </span>
                        <?php if (!empty($log['username'])): ?>
                            <span style="font-size:0.72rem; color:var(--usr-text-muted); font-family:monospace;" dir="ltr">(@<?= e($log['username']) ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="usr-action-tag" style="background:<?= $actMeta['bg'] ?>; color:<?= $actMeta['color'] ?>;">
                            <?= e($actMeta['label']) ?>
                        </span>
                    </td>
                    <td>
                        <span style="font-size:0.8rem; color:var(--usr-text);"><?= e($log['description']) ?></span>
                    </td>
                    <td>
                        <span style="font-family:monospace; font-size:0.74rem; color:var(--usr-text-muted);" dir="ltr"><?= e($log['ip_address']) ?></span>
                    </td>
                    <td>
                        <span style="font-size:0.76rem; color:var(--usr-text-muted);"><?= toPersianDigits(date('Y/m/d H:i:s', strtotime($log['created_at']))) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding:28px; text-align:center; color:var(--usr-text-muted); font-size:0.86rem;">
            رویداد امنیتی جدیدی در سیستم ثبت نشده است.
        </div>
    <?php endif; ?>
</div>
