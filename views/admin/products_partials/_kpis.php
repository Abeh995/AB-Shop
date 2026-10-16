<?php
/**
 * Bento KPI summary cards for Products Catalog (views/admin/products_partials/_kpis.php)
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 */
?>
<section class="ab-kpi-grid">
    <?php
    component('kpi_card', [
        'title' => 'کل محصولات کاتالوگ',
        'value' => toPersianDigits((string)($stats['total_products'] ?? 0)),
        'sub'   => toPersianDigits((string)($stats['active_products'] ?? 0)) . ' فعال • ' . toPersianDigits((string)(($stats['total_products'] ?? 0) - ($stats['active_products'] ?? 0))) . ' غیرفعال',
        'tone'  => 'purple',
        'href'  => 'products.php?status=all',
        'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
    ]);

    component('kpi_card', [
        'title' => 'موجودی رو به اتمام (≤ ۳)',
        'value' => toPersianDigits((string)($stats['low_stock_count'] ?? 0)),
        'sub'   => 'کلیک جهت مشاهده و شارژ انبار',
        'tone'  => 'amber',
        'href'  => 'products.php?status=low_stock',
        'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    ]);

    component('kpi_card', [
        'title' => 'کالاهای ناموجود (صفر)',
        'value' => toPersianDigits((string)($stats['out_of_stock_count'] ?? 0)),
        'sub'   => 'توقف فروش به علت کسری موجودی',
        'tone'  => 'rose',
        'href'  => 'products.php?status=out_of_stock',
        'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>',
    ]);

    component('kpi_card', [
        'title' => 'ارزش سرمایه انبار',
        'value' => formatPrice((int)($stats['inventory_valuation'] ?? 0)),
        'sub'   => 'محاسبه بر مبنای بهای خرید / قیمت کالا',
        'tone'  => 'emerald',
        'icon'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    ]);
    ?>
</section>
