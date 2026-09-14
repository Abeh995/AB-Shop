<?php require APP_ROOT . '/views/layout/header.php'; ?>
<div class="container">
    <div class="static-page">
        <h1>تماس با ما</h1>
        <p><?= e($contactIntro) ?></p>

        <div class="contact-details">
            <?php if ($storePhone !== ''): ?>
                <p><strong>تلفن ثابت:</strong> <a href="tel:<?= e($storePhone) ?>" dir="ltr"><?= e($storePhone) ?></a></p>
            <?php endif; ?>
            <?php if ($storeMobile !== ''): ?>
                <p><strong>شماره همراه:</strong> <a href="tel:<?= e($storeMobile) ?>" dir="ltr"><?= e($storeMobile) ?></a></p>
            <?php endif; ?>
            <?php if ($storeEmail !== ''): ?>
                <p><strong>ایمیل:</strong> <a href="mailto:<?= e($storeEmail) ?>" dir="ltr"><?= e($storeEmail) ?></a></p>
            <?php endif; ?>
            <?php if ($storeSupportHours !== ''): ?>
                <p><strong>ساعات پاسخ‌گویی:</strong> <?= e($storeSupportHours) ?></p>
            <?php endif; ?>
            <?php if ($storePostalCode !== ''): ?>
                <p><strong>کدپستی:</strong> <span dir="ltr"><?= e($storePostalCode) ?></span></p>
            <?php endif; ?>
            <?php if ($storeAddress !== ''): ?>
                <p><strong>آدرس:</strong> <?= e($storeAddress) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($contactFormAvailable): ?>
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
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
