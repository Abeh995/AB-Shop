<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="c-auth-shell">
    <div class="c-auth-card">
        <h1 class="c-mb-2">احراز ایمیل</h1>
        <p class="c-text-muted c-mb-4">
            کد ۶ رقمی ارسال‌شده به <span dir="ltr" class="c-fw-bold"><?= e($customer['email']) ?></span> را وارد کنید.
        </p>

        <?php site_component('alert', ['state' => 'error', 'message' => $error ?? '']); ?>
        <?php site_component('alert', ['state' => 'success', 'message' => $info ?? '']); ?>

        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="verify">
            <div class="c-form-group">
                <label for="email-otp-code">کد تایید</label>
                <input class="c-form-control c-tracking-otp" id="email-otp-code" type="text" inputmode="numeric" name="code" dir="ltr" maxlength="6" required autofocus>
            </div>
            <button type="submit" class="c-btn c-btn--primary c-btn--block">تایید</button>
        </form>

        <form method="post" class="c-mt-3">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="resend">
            <button type="submit" class="c-btn c-btn--outline c-btn--block">ارسال مجدد کد</button>
        </form>

        <p class="c-text-center c-mt-4 c-mb-0">
            <a href="/account" class="c-text-muted c-text-sm">فعلاً رد شو، بعداً تایید می‌کنم</a>
        </p>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
