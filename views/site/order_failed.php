<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <div class="success-box">
        <div class="success-icon success-icon--danger">!</div>
        <h1>پرداخت انجام نشد</h1>
        <p><?= e($errorMessage ?? 'در اتصال به درگاه پرداخت مشکلی پیش آمد.') ?></p>
        <div class="order-code-box" dir="ltr"><?= e($order['order_code']) ?></div>
        <p class="c-text-muted c-text-sm">
            سفارش شما با موفقیت ثبت شده و نگه داشته شده؛ فقط پرداخت انجام نشد. می‌توانید دوباره تلاش کنید.
        </p>

        <form method="post" action="/payment/retry.php" class="c-mt-4">
            <?= csrfField() ?>
            <input type="hidden" name="code" value="<?= e($order['order_code']) ?>">
            <button type="submit" class="c-btn c-btn--primary">تلاش مجدد برای پرداخت</button>
        </form>
        <a href="/contact" class="c-btn c-btn--outline c-mt-3">تماس با پشتیبانی</a>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
