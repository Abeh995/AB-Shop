<?php
/**
 * Bento KPI Stats Grid for Admin Gift Items & Cart Add-ons Workstation
 * Pure presentation partial rendering catalog health, attach rates, and profitability.
 */
?>
<section class="gift-kpi-grid">
    <!-- 1. Post-Order Revenue & Gross Profit -->
    <div class="gift-kpi-card highlight-purple is-clickable" onclick="location.href='gift_items.php?role=post_orderable'" title="مشاهده اقلام قابل فروش در سبد">
        <div class="gift-kpi-header">
            <span class="gift-kpi-title">درآمد و سود فروش در سبد</span>
            <div class="gift-kpi-icon icon-purple">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </div>
        </div>
        <div class="gift-kpi-val-row">
            <span class="gift-kpi-val text-purple"><?= formatPrice($metrics['lifetime_post_order_revenue']) ?></span>
        </div>
        <div class="gift-kpi-sub">
            <span class="badge-profit-pill">سود ناخالص: <?= formatPrice($metrics['lifetime_post_order_net_profit']) ?></span>
            <span>•</span>
            <span><?= toPersianDigits((string)$metrics['lifetime_sold_units']) ?> واحد فروش</span>
        </div>
    </div>

    <!-- 2. Cart Attach Rate (Take Rate) -->
    <div class="gift-kpi-card highlight-blue" title="درصد سفارشاتی که حداقل یک قلم جانبی یا بسته‌بندی را همراه خود خریده‌اند">
        <div class="gift-kpi-header">
            <span class="gift-kpi-title">نرخ نفوذ در سبد (Attach Rate)</span>
            <div class="gift-kpi-icon icon-blue">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
            </div>
        </div>
        <div class="gift-kpi-val-row">
            <span class="gift-kpi-val"><?= toPersianDigits((string)$metrics['attach_rate_percent']) ?>٪</span>
            <span class="gift-kpi-unit">از کل سفارشات</span>
        </div>
        <div class="gift-kpi-sub">
            <div class="kpi-mini-bar-wrap">
                <div class="kpi-mini-bar-fill" style="width: <?= min(100, max(4, (float)$metrics['attach_rate_percent'])) ?>%;"></div>
            </div>
            <span><?= toPersianDigits((string)$metrics['post_order_orders_count']) ?> سفارش با مکمل</span>
        </div>
    </div>

    <!-- 3. Inventory Valuation & Low Stock Alert -->
    <div class="gift-kpi-card highlight-amber is-clickable" onclick="location.href='gift_items.php?role=low_stock'" title="فیلتر اقلام با موجودی کم">
        <div class="gift-kpi-header">
            <span class="gift-kpi-title">ارزش موجودی ملزومات</span>
            <div class="gift-kpi-icon icon-amber">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
        </div>
        <div class="gift-kpi-val-row">
            <span class="gift-kpi-val"><?= formatPrice($metrics['total_inventory_valuation']) ?></span>
        </div>
        <div class="gift-kpi-sub">
            <?php if ($metrics['low_stock_count'] > 0): ?>
                <span class="text-danger font-bold">⚠️ <?= toPersianDigits((string)$metrics['low_stock_count']) ?> قلم رو به اتمام (موجودی ≤ ۵)</span>
            <?php else: ?>
                <span class="text-success font-bold">✓ موجودی تمام <?= toPersianDigits((string)$metrics['total_items']) ?> قلم تکمیل است</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4. Top Performer Hero Card -->
    <div class="gift-kpi-card highlight-teal" title="پرفروش‌ترین و سودآورترین قلم جانبی در سبد مشتریان">
        <div class="gift-kpi-header">
            <span class="gift-kpi-title">پرفروش‌ترین قلم جانبی</span>
            <div class="gift-kpi-icon icon-teal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
        </div>
        <?php if (!empty($metrics['top_performer'])):
            $top = $metrics['top_performer'];
            $topImg = !empty($top['image']) ? UPLOAD_URL . e($top['image']) : '/assets/img/placeholder-sock.svg';
        ?>
            <div class="gift-kpi-hero-row">
                <img src="<?= $topImg ?>" alt="<?= e($top['name']) ?>" class="gift-kpi-hero-thumb">
                <div class="gift-kpi-hero-info">
                    <div class="gift-kpi-hero-name" title="<?= e($top['name']) ?>"><?= e($top['name']) ?></div>
                    <div class="gift-kpi-hero-stats">
                        <span><?= toPersianDigits((string)$top['sold_qty']) ?> واحد فروش</span>
                        <span>•</span>
                        <span class="text-teal font-bold"><?= formatPrice($top['net_profit']) ?> سود</span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="gift-kpi-val-row">
                <span class="gift-kpi-val" style="font-size: 1.05rem; color: var(--gift-text-muted);">در انتظار سفارشات</span>
            </div>
            <div class="gift-kpi-sub">
                <span>پس از خرید اولین محصول جانبی فعال می‌شود</span>
            </div>
        <?php endif; ?>
    </div>
</section>
