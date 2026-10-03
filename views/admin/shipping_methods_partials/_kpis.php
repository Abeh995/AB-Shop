<?php
/**
 * AB-Socks Logistics & Shipping Methods — Bento KPI Statistics Header
 * @var array $metrics
 */
?>
<section class="shipping-bento-grid">
    <!-- 1. Total & Active Methods -->
    <div class="shipping-kpi-card">
        <div class="kpi-icon-wrap kpi-icon-blue">🚚</div>
        <div class="kpi-content">
            <span class="kpi-label">روش‌های ارسال فعال</span>
            <span class="kpi-val"><?= toPersianDigits((string)$metrics['active_methods']) ?> <small style="font-size:.8rem; font-weight:500; color:var(--ship-muted);">از <?= toPersianDigits((string)$metrics['total_methods']) ?></small></span>
            <span class="kpi-sub"><?= $metrics['active_methods'] > 0 ? 'سرویس‌دهی لجستیک فعال است' : 'هیچ روش فعالی وجود ندارد!' ?></span>
        </div>
    </div>

    <!-- 2. Average Customer Fee -->
    <div class="shipping-kpi-card">
        <div class="kpi-icon-wrap kpi-icon-green">💳</div>
        <div class="kpi-content">
            <span class="kpi-label">میانگین کرایه مشتری</span>
            <span class="kpi-val"><?= formatPrice($metrics['avg_cost']) ?></span>
            <span class="kpi-sub">تعرفه دریافتی در سبد خرید</span>
        </div>
    </div>

    <!-- 3. Average Courier Actual Cost -->
    <div class="shipping-kpi-card">
        <div class="kpi-icon-wrap kpi-icon-amber">📦</div>
        <div class="kpi-content">
            <span class="kpi-label">میانگین بهای واقعی پست/پیک</span>
            <span class="kpi-val"><?= formatPrice($metrics['avg_actual_cost']) ?></span>
            <span class="kpi-sub">هزینه نهایی پرداختی فروشگاه</span>
        </div>
    </div>

    <!-- 4. Logistics Subsidy & Margin Health -->
    <div class="shipping-kpi-card">
        <div class="kpi-icon-wrap kpi-icon-purple">⚖️</div>
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
        <div class="kpi-icon-wrap kpi-icon-green">🎁</div>
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
