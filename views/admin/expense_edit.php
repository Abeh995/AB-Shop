<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="max-width: 640px; margin: 0 auto;">
    <h3 style="margin-top: 0; margin-bottom: 18px; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <?= $expense && !empty($expense['id']) ? 'ویرایش سند هزینه' : 'ثبت سند هزینه جدید' ?>
    </h3>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="margin-bottom: 18px;">
            <div style="font-weight: 700; margin-bottom: 4px;">خطا در ثبت سند هزینه:</div>
            <?php foreach ($errors as $err): ?>
                <div>• <?= e($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="expense_edit.php<?= $id > 0 ? '?id=' . $id : '' ?>">
        <?= csrfField() ?>

        <div class="form-group">
            <label style="font-weight: 600;">عنوان و شرح مختصر هزینه <span style="color: var(--color-danger);">*</span></label>
            <input class="form-control" type="text" name="title" value="<?= e($expense['title'] ?? '') ?>" placeholder="مثلاً: خرید ۱۰۰ جفت جوراب ساق‌دار از کارگاه تولیدی" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label style="font-weight: 600;">مبلغ پرداختی (تومان) <span style="color: var(--color-danger);">*</span></label>
                <input class="form-control" type="text" inputmode="numeric" id="expenseAmountInput" name="amount" value="<?= e((string)($expense['amount'] ?? '')) ?>" placeholder="مثلاً: 350,000" required>
                <div id="expenseAmountFormatted" style="font-size: 0.8rem; color: var(--color-primary); font-weight: 600; margin-top: 4px; min-height: 18px;"></div>
            </div>
            <div class="form-group">
                <label style="font-weight: 600;">تاریخ وقوع هزینه <span style="color: var(--color-danger);">*</span></label>
                <input class="form-control" type="date" name="expense_date" value="<?= e($expense['expense_date'] ?? date('Y-m-d')) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label style="font-weight: 600;">دسته‌بندی سرفصل هزینه <span style="color: var(--color-danger);">*</span></label>
            <input class="form-control" type="text" id="expenseCategoryInput" name="category" list="categoryList" value="<?= e($expense['category'] ?? '') ?>" required placeholder="انتخاب از موارد زیر یا تایپ عنوان دلخواه">
            <datalist id="categoryList">
                <?php foreach ($suggestedCategories as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
            </datalist>

            <!-- Quick Category Selection Chips -->
            <div style="margin-top: 8px;">
                <span style="font-size: 0.75rem; color: var(--color-muted); display: block; margin-bottom: 6px;">دسته‌بندی‌های پرتکرار (کلیک کنید):</span>
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <?php foreach ($suggestedCategories as $sc): ?>
                        <button type="button" class="btn btn-sm btn-outline quick-cat-chip" data-cat="<?= e($sc) ?>" style="font-size: 0.75rem; padding: 2px 8px; border-radius: 14px;">
                            <?= e($sc) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 22px;">
            <label style="font-weight: 600;">یادداشت و توضیحات تکمیلی (اختیاری)</label>
            <textarea class="form-control" name="description" rows="3" placeholder="شماره پیگیری واریز، نام فروشنده یا فاکتور..."><?= e($expense['description'] ?? '') ?></textarea>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 700;">
                <?= $expense && !empty($expense['id']) ? 'ذخیره تغییرات سند' : 'ثبت سند هزینه' ?>
            </button>
            <a href="expenses.php" class="btn btn-outline">انصراف و بازگشت</a>
        </div>
    </form>
</div>

<script>
// Clickable category chips
document.querySelectorAll('.quick-cat-chip').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('expenseCategoryInput').value = this.getAttribute('data-cat');
    });
});

// Live Persian currency formatting
var amountInput = document.getElementById('expenseAmountInput');
var formattedDisplay = document.getElementById('expenseAmountFormatted');

function formatAmountDisplay() {
    var raw = amountInput.value.replace(/\D/g, '');
    if (raw.length > 0) {
        var num = parseInt(raw, 10);
        formattedDisplay.innerText = num.toLocaleString('fa-IR') + ' تومان';
    } else {
        formattedDisplay.innerText = '';
    }
}

amountInput.addEventListener('input', formatAmountDisplay);
formatAmountDisplay();
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
