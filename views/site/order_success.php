<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <div class="success-box">
        <div class="success-icon">✓</div>
        <h1>سفارش شما با موفقیت ثبت شد</h1>
        <p>
            <?php if ($cardToCardPendingReview): ?>
                رسید کارت‌به‌کارت شما دریافت شد. سفارش شما در انتظار بررسی ادمین سایت است؛ پس از بررسی و تغییر وضعیت، نتیجه از طریق پیامک به شما اطلاع داده خواهد شد.
            <?php elseif ($order['payment_status'] === 'paid'): ?>
                پرداخت شما با موفقیت انجام شد. همکاران ما به‌زودی جهت ارسال با شما هماهنگ می‌کنند.
            <?php else: ?>
                سفارش شما ثبت شده و همکاران ما به‌زودی جهت هماهنگی با شما تماس خواهند گرفت.
            <?php endif; ?>
        </p>
        <div class="success-order-meta">
            <span>وضعیت سفارش</span>
            <strong><?= $cardToCardPendingReview ? 'در انتظار بررسی رسید' : ($order['payment_status'] === 'paid' ? 'پرداخت شده' : 'در حال بررسی') ?></strong>
        </div>
        <div class="order-code-box" dir="ltr"><?= e($order['order_code']) ?></div>
        <p style="color:var(--color-muted); font-size:.9rem;">شماره سفارش خود را برای پیگیری ذخیره کنید.</p>
        <a href="/" class="btn btn-primary" style="margin-top:20px;">بازگشت به فروشگاه</a>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
