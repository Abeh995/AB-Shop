<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section" style="max-width:420px;">
    <h1 style="margin-bottom:24px;">ورود به حساب کاربری</h1>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="/login">
        <?= csrfField() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <div class="form-group">
            <label>شماره موبایل</label>
            <input class="form-control" type="tel" name="phone" dir="ltr" placeholder="09123456789" required autofocus>
        </div>
        <div class="form-group">
            <label>رمز عبور</label>
            <input class="form-control" type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">ورود</button>
    </form>

    <p style="text-align:center; margin-top:16px; font-size:.9rem; color:var(--color-muted);">
        حساب ندارید؟ <a href="/signup" style="color:var(--color-primary); font-weight:600;">ثبت‌نام کنید</a>
    </p>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
