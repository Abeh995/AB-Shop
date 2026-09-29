<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<!-- 1. Date Range Toolbar & Presets -->
<div class="admin-card" style="padding: 14px 18px; margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <!-- Preset Quick Chips -->
        <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
            <span style="font-size: 0.85rem; font-weight: 600; color: var(--color-muted); margin-left: 4px;">بازه سریع:</span>
            <a href="finance_dashboard.php?range=today" class="btn btn-sm <?= ($range === 'today') ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px;">امروز</a>
            <a href="finance_dashboard.php?range=7days" class="btn btn-sm <?= ($range === '7days') ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px;">۷ روز اخیر</a>
            <a href="finance_dashboard.php?range=30days" class="btn btn-sm <?= ($range === '30days') ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px;">۳۰ روز اخیر</a>
            <a href="finance_dashboard.php?range=this_month" class="btn btn-sm <?= ($range === 'this_month' || (empty($range) && $startDate === date('Y-m-01'))) ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px;">ماه جاری</a>
            <a href="finance_dashboard.php?range=this_year" class="btn btn-sm <?= ($range === 'this_year') ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px;">امسال</a>
        </div>

        <!-- Custom Date Range Form -->
        <form method="get" action="finance_dashboard.php" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 0.8rem; color: var(--color-muted);">از:</span>
                <input class="form-control" type="date" name="start_date" value="<?= e($startDate) ?>" style="height: 36px; padding: 2px 8px; font-size: 0.85rem;">
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 0.8rem; color: var(--color-muted);">تا:</span>
                <input class="form-control" type="date" name="end_date" value="<?= e($endDate) ?>" style="height: 36px; padding: 2px 8px; font-size: 0.85rem;">
            </div>
            <button type="submit" class="btn btn-outline" style="height: 36px; padding: 0 14px; font-size: 0.85rem;">اعمال تاریخ</button>
        </form>
    </div>
</div>

<!-- 2. Warning for Orders with Incomplete Cost Data (if any) -->
<?php if (!empty($summary['orders_with_incomplete_cost_data'])): ?>
<div class="alert alert-error" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 1.3rem;">⚠️</span>
        <div style="font-size: 0.88rem; line-height: 1.6;">
            <strong>هشدار عدم ثبت بهای تمام‌شده:</strong>
            تعداد <strong><?= toPersianDigits((string)$summary['orders_with_incomplete_cost_data']) ?> سفارش</strong> در این بازه، شامل اقلامی بوده که در لحظه ثبت سفارش فاقد بهای خرید بوده‌اند. سود خالص ممکن است بالاتر از مقدار واقعی برآورد شده باشد.
        </div>
    </div>
    <a href="products.php" class="btn btn-sm btn-outline" style="background: #fff; white-space: nowrap;">
        تکمیل قیمت تمام‌شده محصولات ←
    </a>
</div>
<?php endif; ?>

<!-- 3. Bento Financial KPI Matrix (4 Core Cards) -->
<div class="c2c-kpi-grid" style="margin-bottom: 20px;">
    
    <!-- Gross Revenue -->
    <div class="c2c-kpi-card" style="border-right: 4px solid var(--color-primary);">
        <div class="c2c-kpi-header">
            <span class="c2c-kpi-title">مجموع فروش ناخالص</span>
            <div class="c2c-kpi-icon" style="background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.12); color: var(--color-primary);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
        </div>
        <div class="c2c-kpi-value" style="color: var(--color-primary);"><?= formatPrice($summary['total_revenue']) ?></div>
        <div class="c2c-kpi-sub">
            <span><?= toPersianDigits((string)$summary['order_count']) ?> سفارش تحقق‌یافته</span>
            <?php if ($summary['discount_total'] > 0): ?>
                <span style="color: var(--color-danger); margin-right: 4px;">(<?= formatPrice($summary['discount_total']) ?> تخفیف)</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- COGS (Cost of Goods Sold & Shipping) -->
    <div class="c2c-kpi-card" style="border-right: 4px solid #8b5cf6;">
        <div class="c2c-kpi-header">
            <span class="c2c-kpi-title">بهای تمام‌شده کالا و ارسال</span>
            <div class="c2c-kpi-icon" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            </div>
        </div>
        <div class="c2c-kpi-value" style="color: #7c3aed;"><?= formatPrice($summary['total_cogs']) ?></div>
        <div class="c2c-kpi-sub">
            <span>کالا: <?= formatPrice($summary['product_cost']) ?></span>
            <span style="margin: 0 4px;">•</span>
            <span>پست: <?= formatPrice($summary['shipping_cost']) ?></span>
        </div>
    </div>

    <!-- Operating Expenses -->
    <div class="c2c-kpi-card" style="border-right: 4px solid #f59e0b;">
        <div class="c2c-kpi-header">
            <span class="c2c-kpi-title">هزینه‌های جاری عملیاتی</span>
            <div class="c2c-kpi-icon" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
        </div>
        <div class="c2c-kpi-value" style="color: #d97706;"><?= formatPrice($summary['total_expenses']) ?></div>
        <div class="c2c-kpi-sub">
            <span>ثبت‌شده در دفتر کل هزینه‌ها</span>
            <a href="expenses.php" style="margin-right: 4px; color: var(--color-primary); text-decoration: none;">(مشاهده ریز)</a>
        </div>
    </div>

    <!-- Net Profit & Margin -->
    <?php 
    $isProfitable = ($summary['net_profit'] >= 0);
    $netColor = $isProfitable ? 'var(--color-success)' : 'var(--color-danger)';
    ?>
    <div class="c2c-kpi-card" style="border-right: 4px solid <?= $netColor ?>; background: <?= $isProfitable ? 'rgba(16, 185, 129, 0.03)' : 'rgba(239, 68, 68, 0.03)' ?>;">
        <div class="c2c-kpi-header">
            <span class="c2c-kpi-title" style="font-weight: 700;">سود خالص نهایی</span>
            <div class="c2c-kpi-icon" style="background: <?= $isProfitable ? 'rgba(16, 185, 129, 0.12)' : 'rgba(239, 68, 68, 0.12)' ?>; color: <?= $netColor ?>;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
        </div>
        <div class="c2c-kpi-value" style="color: <?= $netColor ?>; font-weight: 800;">
            <?= formatPrice($summary['net_profit']) ?>
        </div>
        <div class="c2c-kpi-sub" style="display: flex; align-items: center; gap: 6px;">
            <span class="status-pill" style="font-size: 0.75rem; padding: 2px 8px; background: <?= $isProfitable ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' ?>; color: <?= $netColor ?>; font-weight: 700;">
                مارجین خالص: <?= toPersianDigits(number_format($summary['net_margin_percent'], 1)) ?>٪
            </span>
            <span style="font-size: 0.78rem; color: var(--color-muted);">سود ناخالص: <?= formatPrice($summary['gross_profit']) ?></span>
        </div>
    </div>

</div>

<!-- 4. Visual Financial Flow Breakdown (Waterfall) -->
<?php if ($summary['total_revenue'] > 0): 
    $cogsPct = round(($summary['total_cogs'] / $summary['total_revenue']) * 100, 1);
    $expensePct = round(($summary['total_expenses'] / $summary['total_revenue']) * 100, 1);
    $netProfitPct = max(0, round(($summary['net_profit'] / $summary['total_revenue']) * 100, 1));
?>
<div class="admin-card" style="margin-bottom: 24px;">
    <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 1.05rem; display: flex; justify-content: space-between; align-items: center;">
        <span>جریان تفکیک درآمد فروش (توزیع ۱۰۰٪ ورودی صندوق)</span>
        <span style="font-size: 0.85rem; font-weight: normal; color: var(--color-muted);">کل فروش: <?= formatPrice($summary['total_revenue']) ?></span>
    </h3>

    <!-- Multi-segmented Progress Bar -->
    <div style="height: 18px; border-radius: 9px; overflow: hidden; display: flex; background: #e5e7eb; margin-bottom: 14px;">
        <div style="width: <?= min(100, $cogsPct) ?>%; background: #8b5cf6;" title="بهای کالا و ارسال: <?= $cogsPct ?>٪"></div>
        <div style="width: <?= min(100 - $cogsPct, $expensePct) ?>%; background: #f59e0b;" title="هزینه‌های عملیاتی: <?= $expensePct ?>٪"></div>
        <?php if ($summary['net_profit'] > 0): ?>
            <div style="width: <?= min(100 - $cogsPct - $expensePct, $netProfitPct) ?>%; background: #10b981;" title="سود خالص: <?= $netProfitPct ?>٪"></div>
        <?php endif; ?>
    </div>

    <!-- Legend -->
    <div style="display: flex; gap: 20px; flex-wrap: wrap; font-size: 0.82rem;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 12px; height: 12px; border-radius: 3px; background: #8b5cf6; display: inline-block;"></span>
            <span>بهای تمام‌شده و لجستیک: <strong><?= toPersianDigits((string)$cogsPct) ?>٪</strong> (<?= formatPrice($summary['total_cogs']) ?>)</span>
        </div>
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 12px; height: 12px; border-radius: 3px; background: #f59e0b; display: inline-block;"></span>
            <span>مخارج جاری: <strong><?= toPersianDigits((string)$expensePct) ?>٪</strong> (<?= formatPrice($summary['total_expenses']) ?>)</span>
        </div>
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 12px; height: 12px; border-radius: 3px; background: #10b981; display: inline-block;"></span>
            <span>سود خالص باقی‌مانده: <strong style="color: var(--color-success);"><?= toPersianDigits(number_format($summary['net_margin_percent'], 1)) ?>٪</strong> (<?= formatPrice($summary['net_profit']) ?>)</span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 5. Operating Expenses by Category with Visual Progress Bars -->
<div class="admin-card" style="padding: 0; overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="margin: 0; font-size: 1.05rem;">هزینه‌های عملیاتی به تفکیک سرفصل‌ها</h3>
            <p style="font-size: 0.8rem; color: var(--color-muted); margin: 2px 0 0 0;">بررسی توزیع بودجه و سهم هر حوزه از کل مخارج دوره</p>
        </div>
        <a href="expenses.php" class="btn btn-sm btn-outline">مدیریت و ثبت اسناد هزینه ←</a>
    </div>

    <?php if (!empty($summary['expenses_by_category'])): ?>
    <div style="padding: 16px 20px;">
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($summary['expenses_by_category'] as $row): ?>
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-weight: 600; font-size: 0.9rem;"><?= e($row['category']) ?></span>
                        <span style="font-size: 0.75rem; color: var(--color-muted); background: var(--color-bg-subtle, rgba(0,0,0,0.04)); padding: 1px 6px; border-radius: 4px;">
                            <?= toPersianDigits((string)$row['entry_count']) ?> سند
                        </span>
                    </div>
                    <div>
                        <strong style="font-size: 0.92rem;"><?= formatPrice((int)$row['total']) ?></strong>
                        <span style="color: var(--color-muted); font-size: 0.8rem; margin-right: 6px;">(<?= toPersianDigits(number_format($row['share_percent'], 1)) ?>٪)</span>
                    </div>
                </div>
                <!-- Category Progress Bar -->
                <div style="height: 8px; border-radius: 4px; background: #e5e7eb; overflow: hidden;">
                    <div style="height: 100%; width: <?= min(100, $row['share_percent']) ?>%; background: var(--color-primary); border-radius: 4px;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
        <div style="padding: 30px; text-align: center; color: var(--color-muted); font-size: 0.9rem;">
            در این بازه تاریخی هیچ سند هزینه‌ای ثبت نشده است.
        </div>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
