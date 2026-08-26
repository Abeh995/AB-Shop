<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section" style="max-width:460px;">
    <h1 style="margin-bottom:24px;">ثبت‌نام</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <form method="post" action="/signup">
        <?= csrfField() ?>
        <div class="form-group">
            <label>شماره موبایل</label>
            <input class="form-control" type="tel" name="phone" dir="ltr" placeholder="09123456789" value="<?= e($_POST['phone'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>نام کامل (اختیاری)</label>
            <input class="form-control" type="text" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>رمز عبور (حداقل ۶ کاراکتر)</label>
            <input class="form-control" type="password" name="password" required minlength="6">
        </div>
        <div class="form-group">
            <label>تکرار رمز عبور</label>
            <input class="form-control" type="password" name="password_confirm" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary btn-block">ثبت‌نام</button>
    </form>

    <p style="text-align:center; margin-top:16px; font-size:.9rem; color:var(--color-muted);">
        قبلاً حساب دارید؟ <a href="/login" style="color:var(--color-primary); font-weight:600;">وارد شوید</a>
    </p>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
