<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <div class="success-box">
        <div class="success-icon" style="background:var(--color-danger);">!</div>
        <h1>پرداخت انجام نشد</h1>
        <p><?= e($errorMessage ?? 'در اتصال به درگاه پرداخت مشکلی پیش آمد.') ?></p>
        <div class="order-code-box" dir="ltr"><?= e($order['order_code']) ?></div>
        <p style="color:var(--color-muted); font-size:.9rem;">
            سفارش شما با موفقیت ثبت شده و نگه داشته شده؛ فقط پرداخت انجام نشد. می‌توانید دوباره تلاش کنید.
        </p>

        <form method="post" action="/payment/retry.php" style="margin-top:20px;">
            <?= csrfField() ?>
            <input type="hidden" name="code" value="<?= e($order['order_code']) ?>">
            <button type="submit" class="btn btn-primary">تلاش مجدد برای پرداخت</button>
        </form>
        <a href="/contact" class="btn btn-outline" style="margin-top:10px;">تماس با پشتیبانی</a>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
