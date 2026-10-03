<?php
require APP_ROOT . '/views/admin/layout/header.php';
?>
<div class="admin-card">
    <p>در حال باز کردن بخش ارسال ایمیل...</p>
    <script>window.location.href = '/admin/emails.php?compose=1';</script>
</div>
<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
