<?php
$pageTitle = 'ثبت سفارش موفق';
$code = $_GET['code'] ?? '';

$stmt = db()->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
$stmt->execute([$code]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return;
}

require __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="success-box">
        <div class="success-icon">✓</div>
        <h1>سفارش شما با موفقیت ثبت شد</h1>
        <p>همکاران ما به‌زودی جهت هماهنگی ارسال با شما تماس خواهند گرفت.</p>
        <div class="order-code-box" dir="ltr"><?= e($order['order_code']) ?></div>
        <p style="color:var(--color-muted); font-size:.9rem;">شماره سفارش خود را برای پیگیری ذخیره کنید.</p>
        <a href="/" class="btn btn-primary" style="margin-top:20px;">بازگشت به فروشگاه</a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
