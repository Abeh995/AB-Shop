<?php
$pageScripts = ['/assets/js/client/verify-phone.js'];
require APP_ROOT . '/views/layout/header.php';
?>

<div class="c-auth-shell">
    <div class="c-auth-card">
        <h1 class="c-mb-2">احراز شماره موبایل</h1>

        <p class="c-text-muted c-mb-4">
            کد ۶ رقمی ارسال‌شده به شماره
            <span dir="ltr" class="c-fw-bold"><?= e($phone) ?></span>
            را وارد کنید.
        </p>

        <?php site_component('alert', ['state' => 'error', 'message' => $error ?? '']); ?>
        <?php site_component('alert', ['state' => 'success', 'message' => $info ?? '']); ?>

        <form method="post" id="phone-verification-form" data-ab-webotp>
            <?= csrfField() ?>
            <input type="hidden" name="action" value="verify">

            <div class="c-form-group">
                <label for="otp-code">کد تایید</label>
                <input
                    class="c-form-control c-tracking-otp"
                    type="text"
                    id="otp-code"
                    name="code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    dir="ltr"
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="c-btn c-btn--primary c-btn--block">تایید</button>
        </form>

        <form method="post" class="c-mt-3">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="resend">
            <button type="submit" class="c-btn c-btn--outline c-btn--block">ارسال مجدد کد</button>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
