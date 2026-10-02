<?php
/**
 * Settings Partial: Header & System Pulse Bar
 * Displays live indicators: store status, payment connectivity, order policies, and version.
 */
?>
<div class="settings-pulse-card">
    <div class="settings-pulse-title-wrap">
        <div class="settings-pulse-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>
        <div>
            <h2 class="settings-pulse-title">مرکز تنظیمات و پیکربندی فروشگاه</h2>
            <p class="settings-pulse-desc">مدیریت سیاست‌های خرید، اطلاعات بانکی، درگاه‌ها، کاتالوگ و صفحات عمومی</p>
        </div>
    </div>

    <div class="settings-pulse-badges">
        <?php if (($stats['storeOrderStatus'] ?? 'active') === 'active'): ?>
            <span class="pulse-badge active" title="فروشگاه در حال دریافت سفارش است">
                <span class="pulse-dot"></span> پذیرش سفارش فعال
            </span>
        <?php else: ?>
            <span class="pulse-badge paused" title="سفارش‌گیری موقتاً متوقف است">
                <span class="pulse-dot"></span> تعلیق موقت سفارش‌ها
            </span>
        <?php endif; ?>

        <span class="pulse-badge neutral" title="روش‌های فعال پرداخت">
            💳 <?= e($stats['paymentSummary'] ?? 'درگاه‌ها') ?>
        </span>

        <?php if (!empty($stats['minOrderAmount'])): ?>
            <span class="pulse-badge neutral" title="حداقل ارزش سبد خرید برای ثبت سفارش">
                حداقل سبد: <?= number_format((int)$stats['minOrderAmount']) ?> تومان
            </span>
        <?php endif; ?>

        <span class="pulse-badge neutral" title="نسخه فعلی هسته سیستم">
            نسخه: v<?= APP_VERSION ?>
        </span>
    </div>
</div>
