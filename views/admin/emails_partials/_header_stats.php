<?php
/**
 * Email Studio: Header KPIs & Quick Actions
 */
?>
<div class="email-kpi-grid">
    <div class="email-kpi-card">
        <div class="email-kpi-info">
            <h4>صندوق جاری</h4>
            <div class="kpi-num" style="font-size:1.1rem; font-weight:700;">
                <?= $account ? e($account['display_name']) : 'هیچ حسابی انتخاب نشده' ?>
            </div>
            <small style="color:var(--mail-text-muted); direction:ltr; display:inline-block; font-size:.76rem;">
                <?= $account ? e($account['email_address']) : '—' ?>
            </small>
        </div>
        <div class="email-kpi-icon" style="background:rgba(59, 130, 246, 0.1); color:#2563EB;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
        </div>
    </div>

    <div class="email-kpi-card">
        <div class="email-kpi-info">
            <h4>پیام‌های دریافتی</h4>
            <div class="kpi-num"><?= count($messages) ?></div>
            <small style="color:var(--mail-text-muted); font-size:.76rem;">آخرین پیام‌های اینباکس</small>
        </div>
        <div class="email-kpi-icon" style="background:rgba(16, 185, 129, 0.1); color:#059669;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v7"/><path d="M2 13h5l2 3h6l2-3h5"/></svg>
        </div>
    </div>

    <div class="email-kpi-card">
        <div class="email-kpi-info">
            <h4>وضعیت وب‌میل هاست</h4>
            <div class="kpi-num" style="font-size:1rem; color:<?= $imapAvailable ? '#059669' : '#DC2626' ?>;">
                <?= $imapAvailable ? 'پروتکل IMAP فعال' : 'عدم پشتیبانی IMAP' ?>
            </div>
            <small style="color:var(--mail-text-muted); font-size:.76rem;"><?= count($accounts) ?> حساب متصل</small>
        </div>
        <div class="email-kpi-icon" style="background:<?= $imapAvailable ? 'rgba(5, 150, 105, 0.1)' : 'rgba(220, 38, 38, 0.1)' ?>; color:<?= $imapAvailable ? '#059669' : '#DC2626' ?>;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/></svg>
        </div>
    </div>
</div>
