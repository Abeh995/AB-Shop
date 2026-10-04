<?php
/**
 * AB-Socks SMS Patterns — Bento KPI Statistics Header
 * SSoT: Rendered via component('kpi_card', ...)
 *
 * @var array $metrics
 */
$gateway = $metrics['gateway_status'] ?? ['ok' => false, 'summary' => 'بررسی نشده'];
?>
<section class="sms-bento-grid ab-kpi-grid">
    <?php
    // 1. Active & Total Patterns
    component('kpi_card', [
        'title'     => 'الگوهای فعال سیستم',
        'value'     => toPersianDigits((string)$metrics['active_patterns']) . ' <small style="font-size:.8rem; font-weight:500; opacity:0.8;">از ' . toPersianDigits((string)$metrics['total_patterns']) . '</small>',
        'raw_value' => true,
        'sub'       => $metrics['active_patterns'] > 0 ? 'سرویس پیامکی در حال ارسال است' : 'هیچ الگویی فعال نیست',
        'color'     => 'primary',
        'icon'      => '📱',
    ]);

    // 2. Gateway Status & Balance
    component('kpi_card', [
        'title'       => 'وضعیت درگاه فراز اس‌ام‌اس',
        'value'       => $gateway['ok'] ? 'متصل و فعال' : 'نیازمند بررسی',
        'value_style' => 'font-size:1.05rem;',
        'sub'         => '<span title="' . e($gateway['summary']) . '">' . e($gateway['summary']) . '</span>',
        'color'       => $gateway['ok'] ? 'emerald' : 'amber',
        'icon'        => '⚡',
    ]);

    // 3. Unset / Action Required Patterns
    component('kpi_card', [
        'title'     => 'الگوهای بدون کد پترن',
        'value'     => toPersianDigits((string)$metrics['unset_patterns']) . ' <small style="font-size:.8rem; font-weight:500; opacity:0.8;">مورد</small>',
        'raw_value' => true,
        'sub'       => $metrics['unset_patterns'] > 0 ? 'نیازمند ثبت کد پترن فراز' : 'همه الگوها پیکربندی شده‌اند',
        'color'     => $metrics['unset_patterns'] > 0 ? 'amber' : 'emerald',
        'icon'      => '⚠️',
    ]);

    // 4. Messages Sent Today
    component('kpi_card', [
        'title'     => 'پیامک‌های ثبت‌شده امروز',
        'value'     => toPersianDigits((string)$metrics['total_sent_today']) . ' <small style="font-size:.8rem; font-weight:500; opacity:0.8;">پیامک</small>',
        'raw_value' => true,
        'sub'       => '<a href="notifications_log.php" style="color:var(--primary); text-decoration:none; font-size:.74rem;">مشاهده لاگ پیامک‌ها ←</a>',
        'color'     => 'sky',
        'icon'      => '📨',
    ]);
    ?>
</section>
