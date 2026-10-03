<?php
/**
 * System Diagnostics & Health Studio Controller
 *
 * Coordinates host quotas, real-time connectivity testing, notification logs,
 * system exception viewer, and administrative audit trails.
 * Strictly adheres to layer boundaries and line ceilings (Rule 7).
 */

requireSuperAdmin();
$pageTitle = 'عیب‌یابی، پایش سلامت و لاگ‌ها';

$testResult = null;
$testAction = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'clean_tmp_files') {
        $res = DiagnosticService::cleanTemporaryUploads(24);
        setFlash($res['ok'] ? 'success' : 'error', $res['message'] ?? 'عملیات انجام شد.');
        redirect('diagnostics.php#health');
    } elseif ($action === 'clear_system_log') {
        $res = DiagnosticService::clearSystemErrorLogs();
        setFlash($res['ok'] ? 'success' : 'error', $res['message'] ?? ($res['error'] ?? 'خطا در تخلیه لاگ.'));
        redirect('diagnostics.php#errors');
    } elseif (in_array($action, ['check_balance', 'check_pattern', 'check_smtp', 'check_db', 'check_zarinpal'], true)) {
        $testResult = DiagnosticService::testConnectivity($action);
        $testAction = $action;
    }
}

$activeTab = $_GET['tab'] ?? ($testAction ? 'tests' : 'health');
$notifType = in_array($_GET['type'] ?? '', ['sms', 'email'], true) ? $_GET['type'] : 'sms';
$notifSearch = trim((string) ($_GET['q'] ?? ''));
$notifStatus = trim((string) ($_GET['status'] ?? ''));
$notifLimit = (int) ($_GET['limit'] ?? 50);

$auditSearch = trim((string) ($_GET['audit_q'] ?? ''));
$auditAction = trim((string) ($_GET['audit_action'] ?? ''));

$health = DiagnosticService::getServerHealthMetrics();
$notifications = DiagnosticService::getNotificationLogs($notifType, $notifLimit, $notifSearch, $notifStatus);
$systemErrors = DiagnosticService::getSystemErrorLogs(100);
$auditTrail = DiagnosticService::getAdminAuditTrail(50, $auditSearch, $auditAction);

$configSnapshot = [
    'FARAZ_SMS_ENABLED'      => FARAZ_SMS_ENABLED ? 'true' : 'false',
    'FARAZ_API_KEY'          => DiagnosticService::maskSecret(FARAZ_API_KEY),
    'FARAZ_OTP_PATTERN_CODE' => FARAZ_OTP_PATTERN_CODE ?: '(خالی)',
    'FARAZ_OTP_PATTERN_VAR'  => FARAZ_OTP_PATTERN_VAR ?: '(خالی)',
    'FARAZ_LINE_NUMBER'      => FARAZ_LINE_NUMBER ?: '(خالی)',
    'SMTP_ENABLED'           => SMTP_ENABLED ? 'true' : 'false',
    'SMTP_HOST'              => SMTP_HOST,
    'SMTP_PORT'              => (string) SMTP_PORT,
    'SMTP_USERNAME'          => SMTP_USERNAME,
    'SMTP_PASSWORD'          => DiagnosticService::maskSecret(SMTP_PASSWORD),
];

renderView('admin/diagnostics', compact(
    'pageTitle', 'activeTab', 'health', 'notifications', 'systemErrors',
    'auditTrail', 'configSnapshot', 'testResult', 'testAction',
    'notifType', 'notifSearch', 'notifStatus', 'auditSearch', 'auditAction'
));
