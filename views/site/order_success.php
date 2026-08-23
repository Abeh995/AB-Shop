<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <div class="success-box">
        <div class="success-icon">✓</div>
        <h1>سفارش شما با موفقیت ثبت شد</h1>
        <p>
            <?php if ($order['payment_status'] === 'paid'): ?>
                پرداخت شما با موفقیت انجام شد. همکاران ما به‌زودی جهت ارسال با شما هماهنگ می‌کنند.
            <?php else: ?>
                همکاران ما به‌زودی جهت هماهنگی ارسال و پرداخت با شما تماس خواهند گرفت.
            <?php endif; ?>
        </p>
        <div class="order-code-box" dir="ltr"><?= e($order['order_code']) ?></div>
        <p style="color:var(--color-muted); font-size:.9rem;">شماره سفارش خود را برای پیگیری ذخیره کنید.</p>
        <a href="/" class="btn btn-primary" style="margin-top:20px;">بازگشت به فروشگاه</a>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
