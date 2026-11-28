<?php require APP_ROOT . '/views/layout/header.php'; ?>
<div class="container">
    <section class="c-section static-page">
        <h1>قوانین و مقررات</h1>
        <div class="c-card">
            <?= renderSiteContent($termsContent) ?>
        </div>
    </section>
</div>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
