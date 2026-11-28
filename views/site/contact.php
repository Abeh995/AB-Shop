<?php require APP_ROOT . '/views/layout/header.php'; ?>
<div class="container">
    <section class="c-section static-page">
        <h1>تماس با ما</h1>
        <p><?= e($contactIntro) ?></p>

        <div class="c-card contact-details">
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
            <div class="c-card">
                <form method="post" action="/contact">
                    <?= csrfField() ?>
                    <div class="c-form-group">
                        <label for="contactName">نام</label>
                        <input class="c-form-control" id="contactName" type="text" name="name" required>
                    </div>
                    <div class="c-form-group">
                        <label for="contactTarget">ایمیل یا شماره تماس</label>
                        <input class="c-form-control" id="contactTarget" type="text" name="contact" required>
                    </div>
                    <div class="c-form-group">
                        <label for="contactMessage">پیام</label>
                        <textarea class="c-form-control" id="contactMessage" name="message" required></textarea>
                    </div>
                    <button type="submit" class="c-btn c-btn--primary">ارسال پیام</button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
