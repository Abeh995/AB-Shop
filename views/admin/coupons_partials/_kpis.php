<?php
/**
 * Bento KPI summary cards for Coupons Workstation.
 * SSoT: Rendered via component('kpi_card', ...)
 */
?>
<section class="cpn-kpi-grid ab-kpi-grid">
    <?php
    component('kpi_card', [
        'title' => 'کدهای تخفیف فعال',
        'value' => toPersianDigits((string)$overviewStats['active_count']),
        'sub' => 'آماده اعمال در تسویه حساب',
        'color' => 'emerald',
        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H2v7l6.29 6.29c.94.94 2.48.94 3.42 0l5.58-5.58c.94-.94.94-2.48 0-3.42L11 3H9Z"/><circle cx="6" cy="8" r="1.5" fill="currentColor"/></svg>',
    ]);

    component('kpi_card', [
        'title' => 'دفعات استفاده خریداران',
        'value' => toPersianDigits((string)$overviewStats['total_usages']),
        'sub' => 'کل استفاده در سفارشات ثبت‌شده',
        'color' => 'primary',
        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 5 5L20 7"/></svg>',
    ]);

    component('kpi_card', [
        'title' => 'مجموع تخفیف اعطایی',
        'value' => formatPrice($overviewStats['total_discount_given']),
        'sub' => 'تخفیف کسر شده از سبدها',
        'color' => 'rose',
        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
    ]);

    component('kpi_card', [
        'title' => 'فروش کل با کدهای تخفیف',
        'value' => formatPrice($overviewStats['gross_revenue']),
        'sub' => 'ارزش سفارشات کوپن‌دار',
        'color' => 'sky',
        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    ]);
    ?>
</section>
