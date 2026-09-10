<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card">
    <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group">
            <label>از تاریخ</label>
            <input class="form-control" type="date" name="start_date" value="<?= e($startDate) ?>">
        </div>
        <div class="form-group">
            <label>تا تاریخ</label>
            <input class="form-control" type="date" name="end_date" value="<?= e($endDate) ?>">
        </div>
        <button type="submit" class="btn btn-outline">اعمال بازه</button>
        <a href="finance_dashboard.php" class="btn btn-outline">ماه جاری</a>
    </form>
</div>

<?php if ($summary['orders_with_incomplete_cost_data'] > 0): ?>
<div class="alert alert-error" style="margin-bottom:20px;">
    ⚠️ <?= toPersianDigits((string)$summary['orders_with_incomplete_cost_data']) ?> سفارش در این بازه شامل محصولی است که در لحظه فروش قیمت تمام‌شده ثبت‌شده‌ای نداشته؛ سود این سفارش‌ها ممکن است کمتر از واقعیت محاسبه شده باشد. برای دقت بیشتر، قیمت تمام‌شده محصولات را از صفحه ویرایش هرکدام تکمیل کنید.
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-box"><div class="num"><?= toPersianDigits((string)$summary['order_count']) ?></div><div class="lbl">تعداد سفارش</div></div>
    <div class="stat-box"><div class="num"><?= formatPrice($summary['total_revenue']) ?></div><div class="lbl">مجموع فروش</div></div>
    <div class="stat-box"><div class="num"><?= formatPrice($summary['total_cogs']) ?></div><div class="lbl">هزینه تمام‌شده کالا/هدیه/ارسال</div></div>
    <div class="stat-box"><div class="num"><?= formatPrice($summary['gross_profit']) ?></div><div class="lbl">سود ناخالص</div></div>
    <div class="stat-box"><div class="num"><?= formatPrice($summary['total_expenses']) ?></div><div class="lbl">هزینه‌های عملیاتی</div></div>
    <div class="stat-box"><div class="num" style="color: <?= $summary['net_profit'] >= 0 ? 'var(--color-success)' : 'var(--color-danger)' ?>;"><?= formatPrice($summary['net_profit']) ?></div><div class="lbl">سود خالص</div></div>
</div>

<div class="admin-card">
    <h3 style="margin-bottom:14px;">هزینه‌های عملیاتی به تفکیک دسته‌بندی</h3>
    <?php if ($summary['expenses_by_category']): ?>
    <table class="admin-table">
        <thead><tr><th>دسته‌بندی</th><th>مبلغ</th></tr></thead>
        <tbody>
        <?php foreach ($summary['expenses_by_category'] as $row): ?>
        <tr><td><?= e($row['category']) ?></td><td><?= formatPrice((int)$row['total']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="color:var(--color-muted); font-size:.9rem;">در این بازه هزینه‌ای ثبت نشده است.</p>
    <?php endif; ?>
    <a href="expenses.php" class="btn btn-outline" style="margin-top:14px;">مدیریت هزینه‌ها ←</a>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
