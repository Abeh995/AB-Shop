<?php
$pageTitle = 'تماس با ما';
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $sent = true;
}

require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="static-page">
        <h1>تماس با ما</h1>
        <p>سوالی دارید؟ فرم زیر را پر کنید یا با شماره <span dir="ltr">021-00000000</span> تماس بگیرید.</p>

        <?php if ($sent): ?>
            <div class="alert alert-success">پیام شما ارسال شد. به‌زودی با شما تماس می‌گیریم.</div>
        <?php else: ?>
        <form method="post" action="/contact">
            <?= csrfField() ?>
            <div class="form-group">
                <label>نام</label>
                <input class="form-control" type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>ایمیل یا شماره تماس</label>
                <input class="form-control" type="text" name="contact" required>
            </div>
            <div class="form-group">
                <label>پیام</label>
                <textarea class="form-control" name="message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">ارسال پیام</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
