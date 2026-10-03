<?php
/**
 * Bento KPI summary cards for Coupons Workstation.
 * Displays real-time operational and financial metrics.
 */
?>
<section class="cpn-kpi-grid">
    <!-- 1. Active Coupons -->
    <div class="cpn-kpi-card">
        <div class="cpn-kpi-info">
            <h4>کدهای تخفیف فعال</h4>
            <div class="cpn-kpi-val" style="color: var(--cpn-emerald);">
                <?= toPersianDigits((string)$overviewStats['active_count']) ?>
            </div>
            <div class="cpn-kpi-sub">آماده اعمال در تسویه حساب</div>
        </div>
        <div class="cpn-kpi-icon" style="background: var(--cpn-emerald-light); color: var(--cpn-emerald);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 5H2v7l6.29 6.29c.94.94 2.48.94 3.42 0l5.58-5.58c.94-.94.94-2.48 0-3.42L11 3H9Z"/>
                <circle cx="6" cy="8" r="1.5" fill="currentColor"/>
            </svg>
        </div>
    </div>

    <!-- 2. Lifetime Usages -->
    <div class="cpn-kpi-card">
        <div class="cpn-kpi-info">
            <h4>دفعات استفاده خریداران</h4>
            <div class="cpn-kpi-val" style="color: var(--cpn-primary);">
                <?= toPersianDigits((string)$overviewStats['total_usages']) ?>
            </div>
            <div class="cpn-kpi-sub">کل استفاده در سفارشات ثبت‌شده</div>
        </div>
        <div class="cpn-kpi-icon" style="background: var(--cpn-primary-light); color: var(--cpn-primary);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m5 12 5 5L20 7"/>
            </svg>
        </div>
    </div>

    <!-- 3. Total Discount Given (Financials) -->
    <div class="cpn-kpi-card">
        <div class="cpn-kpi-info">
            <h4>مجموع تخفیف اعطایی</h4>
            <div class="cpn-kpi-val" style="color: var(--cpn-rose);">
                <?= formatPrice($overviewStats['total_discount_given']) ?>
            </div>
            <div class="cpn-kpi-sub">تخفیف کسر شده از سبدها</div>
        </div>
        <div class="cpn-kpi-icon" style="background: var(--cpn-rose-light); color: var(--cpn-rose);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
    </div>

    <!-- 4. Gross Revenue with Coupons -->
    <div class="cpn-kpi-card">
        <div class="cpn-kpi-info">
            <h4>فروش کل با کدهای تخفیف</h4>
            <div class="cpn-kpi-val" style="color: var(--cpn-sky);">
                <?= formatPrice($overviewStats['gross_revenue']) ?>
            </div>
            <div class="cpn-kpi-sub">ارزش سفارشات کوپن‌دار</div>
        </div>
        <div class="cpn-kpi-icon" style="background: var(--cpn-sky-light); color: var(--cpn-sky);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
    </div>
</section>
