<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section" style="max-width:420px;">
    <h1 style="margin-bottom:8px;">احراز شماره موبایل</h1>
    <p style="color:var(--color-muted); margin-bottom:20px;">
        کد ۶ رقمی ارسال‌شده به شماره <span dir="ltr" style="font-weight:600;"><?= e($phone) ?></span> را وارد کنید.
    </p>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($info): ?><div class="alert alert-success"><?= e($info) ?></div><?php endif; ?>

    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="verify">
        <div class="form-group">
            <label>کد تایید</label>
            <input class="form-control" type="text" inputmode="numeric" name="code" dir="ltr" maxlength="6" style="letter-spacing:4px; font-size:1.2rem; text-align:center;" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-block">تایید</button>
    </form>

    <form method="post" style="margin-top:14px;">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="resend">
        <button type="submit" class="btn btn-outline btn-block">ارسال مجدد کد</button>
    </form>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
