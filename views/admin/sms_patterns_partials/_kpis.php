<?php
/**
 * AB-Socks SMS Patterns — Bento KPI Statistics Header
 * @var array $metrics
 */
$gateway = $metrics['gateway_status'] ?? ['ok' => false, 'summary' => 'بررسی نشده'];
?>
<section class="sms-bento-grid">
    <!-- 1. Active & Total Patterns -->
    <div class="sms-kpi-card">
        <div class="sms-kpi-icon icon-indigo">📱</div>
        <div class="sms-kpi-content">
            <span class="sms-kpi-label">الگوهای فعال سیستم</span>
            <span class="sms-kpi-val"><?= toPersianDigits((string)$metrics['active_patterns']) ?> <small style="font-size:.8rem; font-weight:500; color:var(--sms-muted);">از <?= toPersianDigits((string)$metrics['total_patterns']) ?></small></span>
            <span class="sms-kpi-sub"><?= $metrics['active_patterns'] > 0 ? 'سرویس پیامکی در حال ارسال است' : 'هیچ الگویی فعال نیست' ?></span>
        </div>
    </div>

    <!-- 2. Gateway Status & Balance -->
    <div class="sms-kpi-card">
        <div class="sms-kpi-icon <?= $gateway['ok'] ? 'icon-emerald' : 'icon-amber' ?>">⚡</div>
        <div class="sms-kpi-content">
            <span class="sms-kpi-label">وضعیت درگاه فراز اس‌ام‌اس</span>
            <span class="sms-kpi-val" style="font-size:1.05rem; <?= $gateway['ok'] ? 'color:var(--sms-success);' : 'color:var(--sms-warning);' ?>">
                <?= $gateway['ok'] ? 'متصل و فعال' : 'نیازمند بررسی' ?>
            </span>
            <span class="sms-kpi-sub" title="<?= e($gateway['summary']) ?>">
                <?= e($gateway['summary']) ?>
            </span>
        </div>
    </div>

    <!-- 3. Unset / Action Required Patterns -->
    <div class="sms-kpi-card">
        <div class="sms-kpi-icon <?= $metrics['unset_patterns'] > 0 ? 'icon-amber' : 'icon-emerald' ?>">⚠️</div>
        <div class="sms-kpi-content">
            <span class="sms-kpi-label">الگوهای بدون کد پترن</span>
            <span class="sms-kpi-val" style="<?= $metrics['unset_patterns'] > 0 ? 'color:var(--sms-warning);' : 'color:var(--sms-success);' ?>">
                <?= toPersianDigits((string)$metrics['unset_patterns']) ?> <small style="font-size:.8rem; font-weight:500; color:var(--sms-muted);">مورد</small>
            </span>
            <span class="sms-kpi-sub">
                <?= $metrics['unset_patterns'] > 0 ? 'نیازمند ثبت کد پترن فراز' : 'همه الگوها پیکربندی شده‌اند' ?>
            </span>
        </div>
    </div>

    <!-- 4. Messages Sent Today -->
    <div class="sms-kpi-card">
        <div class="sms-kpi-icon icon-cyan">📨</div>
        <div class="sms-kpi-content">
            <span class="sms-kpi-label">پیامک‌های ثبت‌شده امروز</span>
            <span class="sms-kpi-val"><?= toPersianDigits((string)$metrics['total_sent_today']) ?> <small style="font-size:.8rem; font-weight:500; color:var(--sms-muted);">پیامک</small></span>
            <span class="sms-kpi-sub">
                <a href="notifications_log.php" style="color:var(--sms-primary); text-decoration:none; font-size:.74rem;">مشاهده لاگ پیامک‌ها ←</a>
            </span>
        </div>
    </div>
</section>
