<?php
/**
 * Admin Email Studio & Webmail Master View
 * Pure presentation, zero raw SQL, zero direct form mutations (Rule 7).
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="email-workspace">

    <!-- 1. System Pulse & KPI Stats -->
    <?php require __DIR__ . '/emails_partials/_header_stats.php'; ?>

    <!-- 2. Email Studio Navigation Bar -->
    <div class="email-nav-bar">
        <div class="email-tabs-list">
            <a href="/admin/emails.php?account=<?= (int)$selectedId ?>" 
               class="email-tab-link <?= $tab === 'inbox' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                <span>صندوق پیام‌ها (Inbox)</span>
            </a>

            <a href="/admin/emails.php?tab=accounts" 
               class="email-tab-link <?= $tab === 'accounts' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>پیکربندی حساب‌ها (<?= count($allAccounts) ?>)</span>
            </a>
        </div>

        <div class="email-action-btns">
            <button type="button" class="btn-compose-trigger" onclick="openComposeModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                <span>ارسال ایمیل جدید</span>
            </button>
        </div>
    </div>

    <!-- Alert Notices -->
    <?php if (isset($_GET['sent'])): ?>
        <div class="alert alert-success" style="margin:0;">ایمیل با موفقیت از سرور ارسال شد.</div>
    <?php endif; ?>
    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success" style="margin:0;">اطلاعات حساب با موفقیت ذخیره گردید.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success" style="margin:0;">حساب مورد نظر حذف گردید.</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg_deleted'])): ?>
        <div class="alert alert-success" style="margin:0;">ایمیل مورد نظر با موفقیت از سرور حذف شد.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" style="margin:0;"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- 3. Active Pane -->
    <?php if ($tab === 'accounts'): ?>
        <?php require __DIR__ . '/emails_partials/_accounts_pane.php'; ?>
    <?php else: ?>
        <?php require __DIR__ . '/emails_partials/_inbox_pane.php'; ?>
    <?php endif; ?>

    <!-- 4. Smart Compose Modal -->
    <?php require __DIR__ . '/emails_partials/_compose_modal.php'; ?>

</div>

<?php if (isset($_GET['compose'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    openComposeModal();
});
</script>
<?php endif; ?>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
