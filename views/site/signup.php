<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="c-auth-shell c-max-w-sm">
    <div class="c-auth-card">
        <h1 class="c-mb-6">ثبت‌نام</h1>

        <?php site_component('alert', ['state' => 'error', 'messages' => $errors ?? []]); ?>

        <form method="post" action="/signup">
            <?= csrfField() ?>
            <div class="c-form-group">
                <label for="signup-phone">شماره موبایل</label>
                <input class="c-form-control" id="signup-phone" type="tel" name="phone" dir="ltr" placeholder="09123456789" value="<?= e($formData['phone'] ?? '') ?>" required>
            </div>
            <div class="c-form-group">
                <label for="signup-name">نام کامل (اختیاری)</label>
                <input class="c-form-control" id="signup-name" type="text" name="full_name" value="<?= e($formData['full_name'] ?? '') ?>">
            </div>
            <div class="c-form-group">
                <label for="signup-email">ایمیل (اختیاری — در صورت وارد کردن، بعداً باید تایید شود)</label>
                <input class="c-form-control" id="signup-email" type="email" name="email" dir="ltr" value="<?= e($formData['email'] ?? '') ?>">
            </div>
            <div class="c-form-group">
                <label for="signup-pass">رمز عبور (حداقل ۶ کاراکتر)</label>
                <input class="c-form-control" id="signup-pass" type="password" name="password" required minlength="6">
            </div>
            <div class="c-form-group">
                <label for="signup-pass-confirm">تکرار رمز عبور</label>
                <input class="c-form-control" id="signup-pass-confirm" type="password" name="password_confirm" required minlength="6">
            </div>
            <button type="submit" class="c-btn c-btn--primary c-btn--block">ثبت‌نام</button>
        </form>

        <p class="c-text-center c-mt-4 c-mb-0 c-text-sm c-text-muted">
            قبلاً حساب دارید؟ <a href="/login" class="c-text-primary c-fw-bold">وارد شوید</a>
        </p>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
