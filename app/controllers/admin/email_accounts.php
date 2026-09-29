<?php
/**
 * Email accounts management controller.
 * All database operations and encryption delegated to MailboxService (Rule 7).
 */

$pageTitle = 'حساب‌های ایمیل';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);

    $res = MailboxService::saveAccountRecord($id, $_POST);
    if ($res['ok']) {
        header('Location: /admin/email_accounts.php?saved=1');
        exit;
    }
    $error = $res['error'];
}

$accounts = MailboxService::allAccounts();
$editId = (int) ($_GET['edit'] ?? 0);
$edit = $editId ? MailboxService::account($editId) : null;

renderView('admin/email_accounts', compact('pageTitle', 'accounts', 'edit', 'error'));
