<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="c-auth-shell">
    <div class="c-auth-card">
        <h1 class="c-mb-6">ورود به حساب کاربری</h1>

        <?php site_component('alert', ['state' => 'error', 'message' => $error ?? '']); ?>

        <form method="post" action="/login">
            <?= csrfField() ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">
            <div class="c-form-group">
                <label for="login-phone">شماره موبایل</label>
                <input class="c-form-control" id="login-phone" type="tel" name="phone" dir="ltr" placeholder="09123456789" required autofocus>
            </div>
            <div class="c-form-group">
                <label for="login-password">رمز عبور</label>
                <input class="c-form-control" id="login-password" type="password" name="password" required>
            </div>
            <button type="submit" class="c-btn c-btn--primary c-btn--block">ورود</button>
        </form>

        <p class="c-text-center c-mt-4 c-mb-0 c-text-sm c-text-muted">
            حساب ندارید؟ <a href="/signup" class="c-text-primary c-fw-bold">ثبت‌نام کنید</a>
        </p>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
