<?php
/**
 * AB-Socks Logistics & Shipping Methods — Bento KPI Statistics Header
 * SSoT: Rendered via component('kpi_card', ...)
 *
 * @var array $metrics
 */

$netSubsidy = $metrics['net_unit_subsidy'] ?? 0;
if ($netSubsidy > 0) {
    $subsidyVal = '−' . formatPrice($netSubsidy);
    $subsidySub = 'یارانه پرداختی فروشگاه در هر بسته';
    $subsidyColor = 'rose';
} elseif ($netSubsidy < 0) {
    $subsidyVal = '+' . formatPrice(abs($netSubsidy));
    $subsidySub = 'حاشیه سود جانبی لجستیک';
    $subsidyColor = 'emerald';
} else {
    $subsidyVal = 'سربه‌سر';
    $subsidySub = 'تعرفه و بهای پست کاملاً برابر';
    $subsidyColor = 'primary';
}
?>
<section class="shipping-bento-grid ab-kpi-grid">
    <?php
    // 1. Total & Active Methods
    component('kpi_card', [
        'title'     => 'روش‌های ارسال فعال',
        'value'     => toPersianDigits((string)$metrics['active_methods']) . ' <small style="font-size:.8rem; font-weight:500; opacity:0.8;">از ' . toPersianDigits((string)$metrics['total_methods']) . '</small>',
        'raw_value' => true,
        'sub'       => $metrics['active_methods'] > 0 ? 'سرویس‌دهی لجستیک فعال است' : 'هیچ روش فعالی وجود ندارد!',
        'color'     => 'blue',
        'icon'      => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>',
    ]);

    // 2. Average Customer Fee
    component('kpi_card', [
        'title' => 'میانگین کرایه مشتری',
        'value' => formatPrice($metrics['avg_cost']),
        'sub'   => 'تعرفه دریافتی در سبد خرید',
        'color' => 'emerald',
        'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>',
    ]);

    // 3. Average Courier Actual Cost
    component('kpi_card', [
        'title' => 'میانگین بهای واقعی پست/پیک',
        'value' => formatPrice($metrics['avg_actual_cost']),
        'sub'   => 'هزینه نهایی پرداختی فروشگاه',
        'color' => 'amber',
        'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
    ]);

    // 4. Logistics Subsidy & Margin Health
    component('kpi_card', [
        'title'       => 'تراز کرایه و یارانه فروشگاه',
        'value'       => $subsidyVal,
        'value_style' => 'font-size:1.15rem;',
        'sub'         => $subsidySub,
        'color'       => $subsidyColor,
        'icon'        => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/></svg>',
    ]);

    // 5. Free Shipping Policy
    component('kpi_card', [
        'title'       => 'قوانین ارسال رایگان',
        'value'       => $metrics['min_free_threshold'] !== null ? formatPrice($metrics['min_free_threshold']) : 'غیرفعال',
        'value_style' => 'font-size:1.15rem;',
        'sub'         => $metrics['min_free_threshold'] !== null ? toPersianDigits((string)$metrics['free_shipping_count']) . ' روش با آستانه ارسال رایگان' : 'همه روش‌ها دارای هزینه هستند',
        'color'       => $metrics['min_free_threshold'] !== null ? 'emerald' : 'muted',
        'icon'        => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>',
    ]);
    ?>
</section>
