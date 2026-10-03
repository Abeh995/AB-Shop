<?php
/**
 * Admin Email Studio & Webmail controller.
 * Encapsulates inbox browsing, reading, composing, and account maintenance (Rule 7).
 */

$pageTitle = 'ایمیل‌ها و وب‌میل سازمانی';
$accounts = MailboxService::accounts();
$allAccounts = MailboxService::allAccounts();
$selectedId = (int)($_GET['account'] ?? ($accounts[0]['id'] ?? 0));
$account = $selectedId ? MailboxService::account($selectedId) : null;
$tab = trim($_GET['tab'] ?? 'inbox');
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'send') {
        $accId = (int)($_POST['account_id'] ?? 0);
        $senderAcc = MailboxService::account($accId);
        $to = trim($_POST['to'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');
        if (!$senderAcc || !filter_var($to, FILTER_VALIDATE_EMAIL) || $subject === '' || $body === '') {
            $error = 'اطلاعات گیرنده، موضوع یا متن پیام کامل نیست.';
        } else {
            $res = MailboxService::send($senderAcc, $to, $subject, $body, true);
            if ($res['ok']) {
                redirect('/admin/emails.php?account=' . $accId . '&sent=1');
            }
            $error = 'خطا در ارسال ایمیل: ' . $res['error'];
        }
    } elseif ($action === 'save_account') {
        $res = MailboxService::saveAccountRecord(!empty($_POST['id']) ? (int)$_POST['id'] : null, $_POST);
        if ($res['ok']) {
            redirect('/admin/emails.php?tab=accounts&saved=1');
        }
        $error = $res['error'];
    } elseif ($action === 'toggle_account') {
        MailboxService::toggleAccountActive((int)($_POST['id'] ?? 0));
        redirect('/admin/emails.php?tab=accounts');
    } elseif ($action === 'delete_account') {
        MailboxService::deleteAccount((int)($_POST['id'] ?? 0));
        redirect('/admin/emails.php?tab=accounts&deleted=1');
    } elseif ($action === 'delete_message' && $account) {
        $res = MailboxService::deleteMessage($account, (int)($_POST['uid'] ?? 0));
        redirect('/admin/emails.php?account=' . $selectedId . ($res['ok'] ? '&msg_deleted=1' : ''));
    }
}

// Fetch messages and active message preview
$messages = [];
$activeMessage = null;
$viewUid = (int)($_GET['view'] ?? 0);
$search = trim($_GET['q'] ?? '');

if ($account && $tab === 'inbox') {
    try {
        if ($viewUid > 0) {
            $activeMessage = MailboxService::read($account, $viewUid);
        }
        $messages = MailboxService::messages($account, 40, $search);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$editAccountId = (int)($_GET['edit_account'] ?? 0);
$editAccount = $editAccountId ? MailboxService::accountAny($editAccountId) : null;
$imapAvailable = MailboxService::available();

renderView('admin/emails', compact(
    'pageTitle', 'accounts', 'allAccounts', 'account', 'selectedId',
    'tab', 'messages', 'activeMessage', 'viewUid', 'search',
    'editAccount', 'imapAvailable', 'error', 'success'
));
