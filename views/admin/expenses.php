<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<!-- 1. Top Bento KPI & Actions Header -->
<div class="c2c-kpi-grid" style="margin-bottom: 20px;">
    <!-- Filtered Total Amount -->
    <div class="c2c-kpi-card" style="border-right: 4px solid var(--color-primary);">
        <div class="c2c-kpi-header">
            <span class="c2c-kpi-title">مجموع هزینه‌های فیلترشده</span>
            <div class="c2c-kpi-icon" style="background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.12); color: var(--color-primary);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
        </div>
        <div class="c2c-kpi-value" style="color: var(--color-primary);"><?= formatPrice($expensesData['total_amount']) ?></div>
        <div class="c2c-kpi-sub">جمع جبری مخارج در بازه انتخابی</div>
    </div>

    <!-- Filtered Count -->
    <div class="c2c-kpi-card" style="border-right: 4px solid #10b981;">
        <div class="c2c-kpi-header">
            <span class="c2c-kpi-title">تعداد اسناد هزینه</span>
            <div class="c2c-kpi-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
        </div>
        <div class="c2c-kpi-value"><?= toPersianDigits((string)$expensesData['total_count']) ?> <span style="font-size: 0.85rem; font-weight: 500; color: var(--color-muted);">سند فعال</span></div>
        <div class="c2c-kpi-sub">ثبت‌شده توسط مدیران فروشگاه</div>
    </div>

    <!-- Quick Action Card -->
    <div class="c2c-kpi-card" style="display: flex; flex-direction: column; justify-content: center; align-items: stretch; border-right: 4px solid #6366f1;">
        <span style="font-size: 0.85rem; color: var(--color-muted); margin-bottom: 8px;">عملیات حسابداری و سند جدید:</span>
        <a href="expense_edit.php" class="btn btn-primary" style="justify-content: center; gap: 6px; padding: 10px 16px; font-weight: 700; font-size: 0.95rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            + ثبت سند هزینه جدید
        </a>
    </div>
</div>

<!-- 2. Filter Toolbar Card -->
<div class="admin-card" style="padding: 14px 18px; margin-bottom: 20px;">
    <form method="get" action="expenses.php" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        <div style="flex: 2; min-width: 200px;">
            <input class="form-control" type="text" name="q" value="<?= e($search) ?>" placeholder="جستجوی عنوان یا توضیحات هزینه..." style="height: 38px;">
        </div>

        <div style="flex: 1.5; min-width: 170px;">
            <select class="form-control" name="category" style="height: 38px;">
                <option value="">همه دسته‌بندی‌ها</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c) ?>" <?= ($category === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="font-size: 0.8rem; color: var(--color-muted);">از:</span>
            <input class="form-control" type="date" name="start_date" value="<?= e($startDate) ?>" style="height: 38px; padding: 2px 8px; font-size: 0.85rem;">
        </div>

        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="font-size: 0.8rem; color: var(--color-muted);">تا:</span>
            <input class="form-control" type="date" name="end_date" value="<?= e($endDate) ?>" style="height: 38px; padding: 2px 8px; font-size: 0.85rem;">
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-outline" style="height: 38px; padding: 0 16px;">اعمال فیلتر</button>
            <?php if ($search !== '' || $category !== '' || $startDate !== '' || $endDate !== ''): ?>
                <a href="expenses.php" class="btn btn-sm btn-outline" style="height: 38px; display: inline-flex; align-items: center; color: var(--color-muted);">✕ حذف فیلترها</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- 3. Expenses High-Density Table Card -->
<div class="admin-card" style="padding: 0; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table class="admin-table" style="margin: 0; width: 100%;">
            <thead>
                <tr style="background: var(--color-bg-subtle, rgba(0,0,0,0.02)); border-bottom: 1px solid var(--color-border);">
                    <th style="width: 110px;">تاریخ سند</th>
                    <th>شرح و عنوان هزینه</th>
                    <th style="width: 170px;">دسته‌بندی</th>
                    <th style="width: 140px;">مبلغ هزینه</th>
                    <th style="width: 120px;">ثبت‌کننده</th>
                    <th style="width: 130px; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $returnUrl = 'expenses.php?' . http_build_query($_GET);
            foreach ($expensesData['items'] as $ex): 
            ?>
            <tr style="border-bottom: 1px solid var(--color-border); vertical-align: middle;">
                <td style="font-size: 0.85rem; color: var(--color-muted);">
                    <?= toPersianDigits(date('Y/m/d', strtotime($ex['expense_date']))) ?>
                </td>
                <td>
                    <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 2px;">
                        <?= e($ex['title']) ?>
                    </div>
                    <?php if (!empty($ex['description'])): ?>
                        <div style="font-size: 0.8rem; color: var(--color-muted); line-height: 1.5;">
                            <?= e($ex['description']) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-pill status-shipped" style="font-size: 0.75rem; padding: 2px 8px;">
                        <?= e($ex['category']) ?>
                    </span>
                </td>
                <td>
                    <strong style="font-size: 0.95rem; color: var(--color-text);">
                        <?= formatPrice((int)$ex['amount']) ?>
                    </strong>
                </td>
                <td style="font-size: 0.85rem; color: var(--color-text-secondary, #4b5563);">
                    <?= e($ex['admin_username'] ?? '—') ?>
                </td>
                <td style="text-align: center;">
                    <div class="admin-actions" style="justify-content: center; gap: 4px;">
                        <a href="expense_edit.php?id=<?= (int)$ex['id'] ?>" class="btn btn-sm btn-outline" style="padding: 4px 10px;" title="ویرایش سند">
                            ویرایش
                        </a>
                        <form method="post" action="expenses.php" onsubmit="return confirm('آیا از بایگانی این سند هزینه اطمینان دارید؟ (این هزینه از تراز مالی بعدی خارج می‌شود اما سوابق آن در سیستم حفظ می‌ماند)');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="archive">
                            <input type="hidden" name="id" value="<?= (int)$ex['id'] ?>">
                            <input type="hidden" name="return_url" value="<?= e($returnUrl) ?>">
                            <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 8px;" title="بایگانی">
                                ✕
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($expensesData['items'])): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 36px 20px; color: var(--color-muted);">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 6px; opacity: 0.5;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <div>هیچ سند هزینه‌ای با این مشخصات یافت نشد.</div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 4. Pagination Controls -->
    <?php if ($expensesData['total_pages'] > 1): 
        $currPage = $expensesData['current_page'];
        $totPages = $expensesData['total_pages'];
        $baseQuery = $_GET;
    ?>
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-top: 1px solid var(--color-border); background: var(--color-bg-subtle, rgba(0,0,0,0.01)); flex-wrap: wrap; gap: 12px;">
        <div style="font-size: 0.85rem; color: var(--color-muted);">
            نمایش <?= toPersianDigits((string)((($currPage - 1) * $expensesData['per_page']) + 1)) ?> تا <?= toPersianDigits((string)min($currPage * $expensesData['per_page'], $expensesData['total_count'])) ?> از <?= toPersianDigits((string)$expensesData['total_count']) ?> سند هزینه
        </div>
        <div style="display: flex; gap: 6px; align-items: center;">
            <?php if ($currPage > 1): 
                $baseQuery['page'] = $currPage - 1;
            ?>
                <a href="expenses.php?<?= http_build_query($baseQuery) ?>" class="btn btn-sm btn-outline">قبلی</a>
            <?php endif; ?>

            <?php for ($i = max(1, $currPage - 2); $i <= min($totPages, $currPage + 2); $i++): 
                $baseQuery['page'] = $i;
            ?>
                <a href="expenses.php?<?= http_build_query($baseQuery) ?>" class="btn btn-sm <?= ($i === $currPage) ? 'btn-primary' : 'btn-outline' ?>" style="min-width: 32px; text-align: center;">
                    <?= toPersianDigits((string)$i) ?>
                </a>
            <?php endfor; ?>

            <?php if ($currPage < $totPages): 
                $baseQuery['page'] = $currPage + 1;
            ?>
                <a href="expenses.php?<?= http_build_query($baseQuery) ?>" class="btn btn-sm btn-outline">بعدی</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
