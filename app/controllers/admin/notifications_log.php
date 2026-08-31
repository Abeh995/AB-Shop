<?php
/**
 * Full log of SMS/email send attempts — for troubleshooting without
 * needing SSH or server log-file access.
 */

requireSuperAdmin();
$pageTitle = 'لاگ پیامک و ایمیل';

$tab = $_GET['tab'] === 'email' ? 'email' : 'sms';

if ($tab === 'sms') {
    $rows = db()->query("SELECT * FROM sms_log ORDER BY created_at DESC LIMIT 100")->fetchAll();
} else {
    $rows = db()->query("SELECT * FROM email_log ORDER BY created_at DESC LIMIT 100")->fetchAll();
}

renderView('admin/notifications_log', compact('pageTitle', 'tab', 'rows'));
