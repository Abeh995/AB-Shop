<?php
/**
 * AB-Socks Logistics & Shipping Methods — Bento KPI Statistics Header
 * @var array $metrics
 */
?>
<section class="shipping-bento-grid">
    <!-- 1. Total & Active Methods -->
    <div class="shipping-kpi-card">
        <div class="kpi-icon-wrap kpi-icon-blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
        </div>
        <div class="kpi-content">
            <span class="kpi-label">روش‌های ارسال فعال</span>
            <span class="kpi-val"><?= toPersianDigits((string)$metrics['active_methods']) ?> <small style="font-size:.8rem; font-weight:500; color:var(--ship-muted);">از <?= toPersianDigits((string)$metrics['total_methods']) ?></small></span>
            <span class="kpi-sub"><?= $metrics['active_methods'] > 0 ? 'سرویس‌دهی لجستیک فعال است' : 'هیچ روش فعالی وجود ندارد!' ?></span>
        </div>
    </div>

    <!-- 2. Average Customer Fee -->
    <div class="shipping-kpi-card">
        <div class="kpi-icon-wrap kpi-icon-green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
        </div>
        <div class="kpi-content">
            <span class="kpi-label">میانگین کرایه مشتری</span>
            <span class="kpi-val"><?= formatPrice($metrics['avg_cost']) ?></span>
            <span class="kpi-sub">تعرفه دریافتی در سبد خرید</span>
        </div>
    </div>

    <!-- 3. Average Courier Actual Cost -->
    <div class="shipping-kpi-card">
        <div class="kpi-icon-wrap kpi-icon-amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
        </div>
        <div class="kpi-content">
            <span class="kpi-label">میانگین بهای واقعی پست/پیک</span>
            <span class="kpi-val"><?= formatPrice($metrics['avg_actual_cost']) ?></span>
            <span class="kpi-sub">هزینه نهایی پرداختی فروشگاه</span>
        </div>
    </div>

    <!-- 4. Logistics Subsidy & Margin Health -->
    <div class="shipping-kpi-card">
        <div class="kpi-icon-wrap kpi-icon-purple">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/></svg>
        </div>
        <div class="kpi-content">
            <span class="kpi-label">تراز کرایه و یارانه فروشگاه</span>
            <?php if ($metrics['net_unit_subsidy'] > 0): ?>
                <span class="kpi-val" style="color:var(--ship-danger); font-size:1.15rem;">−<?= formatPrice($metrics['net_unit_subsidy']) ?></span>
                <span class="kpi-sub">یارانه پرداختی فروشگاه در هر بسته</span>
            <?php elseif ($metrics['net_unit_subsidy'] < 0): ?>
                <span class="kpi-val" style="color:var(--ship-success); font-size:1.15rem;">+<?= formatPrice(abs($metrics['net_unit_subsidy'])) ?></span>
                <span class="kpi-sub">حاشیه سود جانبی لجستیک</span>
            <?php else: ?>
                <span class="kpi-val" style="color:var(--ship-muted); font-size:1.15rem;">سربه‌سر</span>
                <span class="kpi-sub">تعرفه و بهای پست کاملاً برابر</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- 5. Free Shipping Policy -->
    <div class="shipping-kpi-card">
        <div class="kpi-icon-wrap kpi-icon-green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
        </div>
        <div class="kpi-content">
            <span class="kpi-label">قوانین ارسال رایگان</span>
            <?php if ($metrics['min_free_threshold'] !== null): ?>
                <span class="kpi-val" style="font-size:1.15rem;"><?= formatPrice($metrics['min_free_threshold']) ?></span>
                <span class="kpi-sub"><?= toPersianDigits((string)$metrics['free_shipping_count']) ?> روش با آستانه ارسال رایگان</span>
            <?php else: ?>
                <span class="kpi-val" style="font-size:1.1rem; color:var(--ship-muted);">غیرفعال</span>
                <span class="kpi-sub">همه روش‌ها دارای هزینه هستند</span>
            <?php endif; ?>
        </div>
    </div>
</section>
