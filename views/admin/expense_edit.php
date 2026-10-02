<?php
/**
 * Modern High-Density Admin Expense Edit & Creation Workstation
 * Part of AB-Socks Financial Hub.
 */
require APP_ROOT . '/views/admin/layout/header.php';

$isEdit = $expense && !empty($expense['id']);
$currentNature = $expense['expense_nature'] ?? 'variable';
$currentPaymentSource = $expense['payment_source'] ?? 'کارت اصلی فروشگاه';
$currentReceipt = $expense['receipt_image'] ?? null;
?>

<main class="fin-workspace">

    <!-- Top Breadcrumb & Navigation -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; max-width: 760px; margin-left: auto; margin-right: auto;">
        <div>
            <h2 style="margin: 0 0 4px 0; font-size: 1.25rem; font-weight: 800; color: var(--fin-text-primary); display: flex; align-items: center; gap: 8px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <?= $isEdit ? 'ویرایش سند هزینه' : 'ثبت سند هزینه عملیاتی جدید' ?>
            </h2>
            <div style="font-size: 0.82rem; color: var(--fin-text-muted);">
                تکمیل اطلاعات مالی، فاکتور ضمیمه و تعیین سرفصل هزینه
            </div>
        </div>
        <a href="expenses.php" class="fin-action-btn fin-btn-outline" style="font-size: 0.82rem;">
            ← بازگشت به دفتر کل
        </a>
    </div>

    <!-- Main Form Card -->
    <div class="admin-card" style="max-width: 760px; margin: 0 auto; padding: 24px 28px; border-radius: var(--fin-radius-md); box-shadow: var(--fin-shadow-sm); border: 1px solid var(--fin-border);">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" style="margin-bottom: 22px; border-radius: 8px; padding: 14px 18px;">
                <div style="font-weight: 700; margin-bottom: 6px; font-size: 0.9rem;">خطا در ثبت سند هزینه:</div>
                <ul style="margin: 0; padding-right: 18px; font-size: 0.85rem; line-height: 1.6;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="expense_edit.php<?= $id > 0 ? '?id=' . $id : '' ?>" enctype="multipart/form-data">
            <?= csrfField() ?>

            <!-- Title -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label style="font-weight: 700; font-size: 0.86rem; color: var(--fin-text-primary); margin-bottom: 6px; display: block;">
                    عنوان و شرح سند هزینه <span style="color: var(--fin-rose);">*</span>
                </label>
                <input class="form-control" type="text" name="title" value="<?= e($expense['title'] ?? '') ?>" placeholder="مثلاً: خرید ۱۰۰ جفت جوراب ساق‌دار از تولیدی، خرید کارتن پستی سایز ۲" required style="height: 42px; font-size: 0.9rem;">
            </div>

            <!-- Payee / Vendor -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label style="font-weight: 700; font-size: 0.86rem; color: var(--fin-text-primary); margin-bottom: 6px; display: block;">
                    طرف حساب / نام فروشنده یا کارگاه (اختیاری)
                </label>
                <input class="form-control" type="text" name="payee" list="payeeList" value="<?= e($expense['payee'] ?? '') ?>" placeholder="مثلاً: کارگاه بافت البرز، شرکت ملی پست، پارس آنلاین..." style="height: 40px; font-size: 0.88rem;">
                <datalist id="payeeList">
                    <?php foreach ($payees as $py): ?>
                        <option value="<?= e($py) ?>">
                    <?php endforeach; ?>
                </datalist>
                <span style="font-size: 0.74rem; color: var(--fin-text-muted); margin-top: 4px; display: block;">ثبت طرف‌حساب به شما کمک می‌کند در آینده گزارش کل مبالغ پرداختی به یک فروشنده را بگیرید.</span>
            </div>

            <!-- Amount & Date Row -->
            <div class="form-row" style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 16px; margin-bottom: 18px;">
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 700; font-size: 0.86rem; color: var(--fin-text-primary); margin-bottom: 6px; display: block;">
                        مبلغ پرداختی (تومان) <span style="color: var(--fin-rose);">*</span>
                    </label>
                    <input class="form-control" type="text" inputmode="numeric" id="expenseAmountInput" name="amount" value="<?= e((string)($expense['amount'] ?? '')) ?>" placeholder="مثلاً: 450,000" required style="height: 42px; font-size: 1rem; font-weight: 700;">
                    <div id="expenseAmountWords" style="font-size: 0.8rem; color: var(--fin-primary); font-weight: 600; margin-top: 4px; min-height: 18px;"></div>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 700; font-size: 0.86rem; color: var(--fin-text-primary); margin-bottom: 6px; display: block;">
                        تاریخ وقوع هزینه <span style="color: var(--fin-rose);">*</span>
                    </label>
                    <input class="form-control" type="date" name="expense_date" value="<?= e($expense['expense_date'] ?? date('Y-m-d')) ?>" required style="height: 42px; font-size: 0.88rem;">
                </div>
            </div>

            <!-- Category & Payment Source Row -->
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                <!-- Category -->
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 700; font-size: 0.86rem; color: var(--fin-text-primary); margin-bottom: 6px; display: block;">
                        سرفصل و دسته‌بندی هزینه <span style="color: var(--fin-rose);">*</span>
                    </label>
                    <input class="form-control" type="text" id="expenseCategoryInput" name="category" list="categoryList" value="<?= e($expense['category'] ?? '') ?>" required placeholder="انتخاب از لیست یا تایپ عنوان جدید" style="height: 40px; font-size: 0.86rem;">
                    <datalist id="categoryList">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= e($c) ?>">
                        <?php endforeach; ?>
                    </datalist>

                    <!-- Quick Category Chips -->
                    <div style="margin-top: 8px; display: flex; gap: 4px; flex-wrap: wrap;">
                        <?php 
                        $quickCats = array_slice($categories, 0, 5);
                        foreach ($quickCats as $qc): 
                        ?>
                            <button type="button" class="btn btn-sm btn-outline quick-cat-chip" data-val="<?= e($qc) ?>" style="font-size: 0.72rem; padding: 2px 7px; border-radius: 12px;">
                                <?= e($qc) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Payment Source -->
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 700; font-size: 0.86rem; color: var(--fin-text-primary); margin-bottom: 6px; display: block;">
                        منبع تأمین وجه (حساب/کارت) <span style="color: var(--fin-rose);">*</span>
                    </label>
                    <input class="form-control" type="text" id="paymentSourceInput" name="payment_source" list="sourceList" value="<?= e($currentPaymentSource) ?>" required placeholder="انتخاب یا درج منبع پرداخت" style="height: 40px; font-size: 0.86rem;">
                    <datalist id="sourceList">
                        <?php foreach ($paymentSources as $ps): ?>
                            <option value="<?= e($ps) ?>">
                        <?php endforeach; ?>
                    </datalist>

                    <!-- Quick Source Chips -->
                    <div style="margin-top: 8px; display: flex; gap: 4px; flex-wrap: wrap;">
                        <?php foreach ($paymentSources as $qps): ?>
                            <button type="button" class="btn btn-sm btn-outline quick-source-chip" data-val="<?= e($qps) ?>" style="font-size: 0.72rem; padding: 2px 7px; border-radius: 12px;">
                                <?= e($qps) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Expense Nature (Fixed vs Variable vs Capital) -->
            <div class="form-group" style="margin-bottom: 22px;">
                <label style="font-weight: 700; font-size: 0.86rem; color: var(--fin-text-primary); margin-bottom: 8px; display: block;">
                    ماهیت ساختاری هزینه (جهت دقت در تحلیل نقطه سر‌به‌سر)
                </label>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                    <!-- Variable -->
                    <label style="display: flex; align-items: flex-start; gap: 8px; padding: 12px; border: 1px solid var(--fin-border); border-radius: var(--fin-radius-sm); cursor: pointer; background: <?= ($currentNature === 'variable') ? '#EFF6FF' : '#FFF' ?>;">
                        <input type="radio" name="expense_nature" value="variable" <?= ($currentNature === 'variable') ? 'checked' : '' ?> style="margin-top: 3px;">
                        <div>
                            <div style="font-size: 0.84rem; font-weight: 700; color: #1E40AF;">متغیر عملیاتی</div>
                            <div style="font-size: 0.72rem; color: var(--fin-text-muted); line-height: 1.4;">بسته‌بندی، چسب، کارتن و مخارج وابسته به حجم فروش</div>
                        </div>
                    </label>

                    <!-- Fixed -->
                    <label style="display: flex; align-items: flex-start; gap: 8px; padding: 12px; border: 1px solid var(--fin-border); border-radius: var(--fin-radius-sm); cursor: pointer; background: <?= ($currentNature === 'fixed') ? '#F5F3FF' : '#FFF' ?>;">
                        <input type="radio" name="expense_nature" value="fixed" <?= ($currentNature === 'fixed') ? 'checked' : '' ?> style="margin-top: 3px;">
                        <div>
                            <div style="font-size: 0.84rem; font-weight: 700; color: #5B21B6;">ثابت بالاسری</div>
                            <div style="font-size: 0.72rem; color: var(--fin-text-muted); line-height: 1.4;">هاست، دامنه، اجاره، نرم‌افزار و هزینه‌های ماهانه منظم</div>
                        </div>
                    </label>

                    <!-- Capital -->
                    <label style="display: flex; align-items: flex-start; gap: 8px; padding: 12px; border: 1px solid var(--fin-border); border-radius: var(--fin-radius-sm); cursor: pointer; background: <?= ($currentNature === 'capital') ? '#ECFDF5' : '#FFF' ?>;">
                        <input type="radio" name="expense_nature" value="capital" <?= ($currentNature === 'capital') ? 'checked' : '' ?> style="margin-top: 3px;">
                        <div>
                            <div style="font-size: 0.84rem; font-weight: 700; color: #065F46;">سرمایه‌ای / تجهیزات</div>
                            <div style="font-size: 0.72rem; color: var(--fin-text-muted); line-height: 1.4;">خرید دارایی، رگال، قفسه انبار، پرینتر، عکاسی اولیه</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Receipt / Invoice Attachment -->
            <div class="form-group" style="margin-bottom: 22px; padding: 16px; background: #F8FAFC; border: 1px dashed var(--fin-border); border-radius: var(--fin-radius-sm);">
                <label style="font-weight: 700; font-size: 0.86rem; color: var(--fin-text-primary); margin-bottom: 6px; display: block;">
                    تصویر فاکتور خرید یا رسید واریز (اختیاری)
                </label>

                <?php if (!empty($currentReceipt)): ?>
                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 12px; background: #FFF; padding: 10px 14px; border: 1px solid var(--fin-border); border-radius: 6px;">
                        <img src="/uploads/expenses/<?= e($currentReceipt) ?>" alt="فاکتور فعلی" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #E2E8F0;">
                        <div style="flex: 1;">
                            <div style="font-size: 0.82rem; font-weight: 700; color: var(--fin-text-primary);">فاکتور فعلی ثبت‌شده در سیستم</div>
                            <a href="/uploads/expenses/<?= e($currentReceipt) ?>" target="_blank" style="font-size: 0.76rem; color: var(--fin-primary); text-decoration: none;">مشاهده تصویر اصلی ↗</a>
                        </div>
                        <label style="font-size: 0.78rem; color: var(--fin-rose); display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" name="delete_receipt" value="1">
                            حذف این فاکتور
                        </label>
                    </div>
                <?php endif; ?>

                <input class="form-control" type="file" name="receipt" accept="image/jpeg,image/png,image/webp" style="height: auto; padding: 6px 10px; font-size: 0.84rem;">
                <span style="font-size: 0.74rem; color: var(--fin-text-muted); margin-top: 4px; display: block;">فرمت‌های مجاز: JPG، PNG، WEBP (تصویر به صورت خودکار بهینه‌سازی و ذخیره می‌شود).</span>
            </div>

            <!-- Notes & Description -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="font-weight: 700; font-size: 0.86rem; color: var(--fin-text-primary); margin-bottom: 6px; display: block;">
                    یادداشت و توضیحات تکمیلی (اختیاری)
                </label>
                <textarea class="form-control" name="description" rows="3" placeholder="شماره پیگیری واریز، شماره فاکتور، اقلام خریداری‌شده یا توافقات..." style="font-size: 0.86rem; line-height: 1.5;"><?= e($expense['description'] ?? '') ?></textarea>
            </div>

            <!-- Submit & Cancel Actions -->
            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="submit" class="fin-action-btn fin-btn-primary" style="padding: 10px 28px; font-size: 0.95rem; font-weight: 700;">
                    <?= $isEdit ? 'ذخیره تغییرات سند' : 'ثبت سند هزینه' ?>
                </button>
                <a href="expenses.php" class="fin-action-btn fin-btn-outline" style="padding: 10px 20px; font-size: 0.9rem;">
                    انصراف و بازگشت
                </a>
            </div>
        </form>
    </div>

</main>

<script>
// Clickable category & source chips
document.querySelectorAll('.quick-cat-chip').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('expenseCategoryInput').value = this.getAttribute('data-val');
    });
});
document.querySelectorAll('.quick-source-chip').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('paymentSourceInput').value = this.getAttribute('data-val');
    });
});

// Live Persian currency formatting with Persian words converter
var amountInput = document.getElementById('expenseAmountInput');
var wordsDisplay = document.getElementById('expenseAmountWords');

function numToPersianWords(n) {
    if (n === 0) return 'صفر تومان';
    var units = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
    var teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
    var tens = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
    var hundreds = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
    var thousands = ['', 'هزار', 'میلیون', 'میلیارد'];

    function convertGroup(val) {
        var str = '';
        var h = Math.floor(val / 100);
        var t = Math.floor((val % 100) / 10);
        var u = val % 10;
        var parts = [];

        if (h > 0) parts.push(hundreds[h]);
        if (t === 1) {
            parts.push(teens[u]);
        } else {
            if (t > 1) parts.push(tens[t]);
            if (u > 0) parts.push(units[u]);
        }
        return parts.join(' و ');
    }

    var groups = [];
    var temp = n;
    while (temp > 0) {
        groups.push(temp % 1000);
        temp = Math.floor(temp / 1000);
    }

    var resultParts = [];
    for (var i = groups.length - 1; i >= 0; i--) {
        if (groups[i] > 0) {
            var gText = convertGroup(groups[i]);
            if (thousands[i]) gText += ' ' + thousands[i];
            resultParts.push(gText);
        }
    }
    return resultParts.join(' و ') + ' تومان';
}

function updateAmountPreview() {
    var raw = amountInput.value.replace(/\D/g, '');
    if (raw.length > 0) {
        var num = parseInt(raw, 10);
        wordsDisplay.innerText = num.toLocaleString('fa-IR') + ' تومان — معادل: ' + numToPersianWords(num);
    } else {
        wordsDisplay.innerText = '';
    }
}

amountInput.addEventListener('input', updateAmountPreview);
updateAmountPreview();
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
