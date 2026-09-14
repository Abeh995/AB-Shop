<?php require APP_ROOT . '/views/layout/header.php'; ?>
<div class="container">
    <div class="static-page">
        <h1>درباره ما</h1>
        <?= renderSiteContent($aboutContent) ?>

        <h2>اطلاعات و راه‌های ارتباطی</h2>
        <div class="contact-details">
            <p><strong>نام کسب‌وکار:</strong> <?= e(SITE_NAME) ?></p>
            <p><strong>وب‌سایت:</strong> <span dir="ltr"><?= e(SITE_URL) ?></span></p>
            <?php if ($storeEmail !== ''): ?>
                <p><strong>ایمیل پشتیبانی:</strong> <a href="mailto:<?= e($storeEmail) ?>" dir="ltr"><?= e($storeEmail) ?></a></p>
            <?php endif; ?>
            <?php if ($storePhone !== ''): ?>
                <p><strong>تلفن ثابت:</strong> <a href="tel:<?= e($storePhone) ?>" dir="ltr"><?= e($storePhone) ?></a></p>
            <?php endif; ?>
            <?php if ($storeMobile !== ''): ?>
                <p><strong>شماره همراه:</strong> <a href="tel:<?= e($storeMobile) ?>" dir="ltr"><?= e($storeMobile) ?></a></p>
            <?php endif; ?>
            <?php if ($storeSupportHours !== ''): ?>
                <p><strong>ساعات پاسخ‌گویی:</strong> <?= e($storeSupportHours) ?></p>
            <?php endif; ?>
            <?php if ($storePostalCode !== ''): ?>
                <p><strong>کدپستی:</strong> <span dir="ltr"><?= e($storePostalCode) ?></span></p>
            <?php endif; ?>
            <?php if ($storeAddress !== ''): ?>
                <p><strong>نشانی:</strong> <?= e($storeAddress) ?></p>
            <?php endif; ?>
            <?php if ($storeStartDate !== ''): ?>
                <p><strong>تاریخ شروع فعالیت:</strong> <?= e($storeStartDate) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
