<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="max-width:600px;">
    <?php if ($errors): ?>
        <div class="alert alert-error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <form method="post">
        <?= csrfField() ?>

        <div class="form-group">
            <label>عنوان</label>
            <input class="form-control" type="text" name="title" value="<?= e($expense['title'] ?? '') ?>" required placeholder="مثلا: خرید ۲۰ جفت جوراب از تأمین‌کننده">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>مبلغ (تومان)</label>
                <input class="form-control" type="text" inputmode="numeric" name="amount" value="<?= e((string)($expense['amount'] ?? '')) ?>" required>
            </div>
            <div class="form-group">
                <label>تاریخ</label>
                <input class="form-control" type="date" name="expense_date" value="<?= e($expense['expense_date'] ?? date('Y-m-d')) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>دسته‌بندی</label>
            <input class="form-control" type="text" name="category" list="categoryList" value="<?= e($expense['category'] ?? '') ?>" required placeholder="یکی را انتخاب کنید یا دسته دلخواه بنویسید">
            <datalist id="categoryList">
                <?php foreach ($suggestedCategories as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
            </datalist>
        </div>

        <div class="form-group">
            <label>توضیحات (اختیاری)</label>
            <textarea class="form-control" name="description" rows="3"><?= e($expense['description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><?= $expense && !empty($expense['id']) ? 'ذخیره تغییرات' : 'ثبت هزینه' ?></button>
        <a href="expenses.php" class="btn btn-outline">انصراف</a>
    </form>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
