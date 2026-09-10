<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section" style="max-width:420px;">
    <h1 style="margin-bottom:8px;">احراز شماره موبایل</h1>

    <p style="color:var(--color-muted); margin-bottom:20px;">
        کد ۶ رقمی ارسال‌شده به شماره
        <span dir="ltr" style="font-weight:600;"><?= e($phone) ?></span>
        را وارد کنید.
    </p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($info): ?>
        <div class="alert alert-success"><?= e($info) ?></div>
    <?php endif; ?>

    <form method="post" id="phone-verification-form">
        <?= csrfField() ?>

        <input type="hidden" name="action" value="verify">

        <div class="form-group">
            <label for="otp-code">کد تایید</label>

            <input
                class="form-control"
                type="text"
                id="otp-code"
                name="code"
                inputmode="numeric"
                autocomplete="one-time-code"
                pattern="[0-9]{6}"
                maxlength="6"
                dir="ltr"
                style="letter-spacing:4px; font-size:1.2rem; text-align:center;"
                required
                autofocus
            >
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            تایید
        </button>
    </form>

    <form method="post" style="margin-top:14px;">
        <?= csrfField() ?>

        <input type="hidden" name="action" value="resend">

        <button type="submit" class="btn btn-outline btn-block">
            ارسال مجدد کد
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('otp-code');
    const form = document.getElementById('phone-verification-form');

    if (!input || !form) {
        return;
    }

    /*
     * Layer 1:
     * Tell browsers that this field expects a one-time verification code.
     *
     * This is useful even on browsers that do not support WebOTP,
     * including Safari's SMS OTP autofill behavior.
     */
    input.setAttribute('autocomplete', 'one-time-code');

    /*
     * Layer 2:
     * WebOTP API.
     *
     * Not supported by all browsers, so everything is feature-detected.
     */
    if (!('OTPCredential' in window) || !navigator.credentials) {
        return;
    }

    const controller = new AbortController();

    /*
     * Don't keep waiting for an OTP after the user manually
     * submits the form.
     */
    form.addEventListener('submit', function () {
        controller.abort();
    }, { once: true });

    /*
     * Avoid keeping the WebOTP request alive indefinitely.
     */
    const timeoutId = setTimeout(function () {
        controller.abort();
    }, 60 * 1000);

    navigator.credentials.get({
        otp: {
            transport: ['sms']
        },
        signal: controller.signal
    })
    .then(function (otp) {
        if (!otp || !otp.code) {
            return;
        }

        /*
         * Put the OTP into the existing input.
         */
        input.value = otp.code;

        /*
         * Notify any other frontend logic that the value changed.
         */
        input.dispatchEvent(
            new Event('input', { bubbles: true })
        );

        input.dispatchEvent(
            new Event('change', { bubbles: true })
        );

        /*
         * Automatically submit the existing verification form.
         *
         * requestSubmit() preserves normal HTML form validation
         * and submit behavior.
         */
        form.requestSubmit();
    })
    .catch(function (error) {
        /*
         * AbortError is expected when:
         * - user submits manually
         * - timeout is reached
         * - page/navigation interrupts the request
         *
         * We intentionally don't show an error to the user because
         * Autofill is an enhancement, not a requirement.
         */
        if (!error || error.name !== 'AbortError') {
            console.debug('WebOTP unavailable:', error);
        }
    })
    .finally(function () {
        clearTimeout(timeoutId);
    });
});
</script>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>