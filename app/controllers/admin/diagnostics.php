<?php
/**
 * Diagnostics page — live connectivity test for Faraz SMS and SMTP, run
 * directly from the real server. Restricted to super_admin since it
 * reveals technical details and part of the API key.
 */

requireSuperAdmin();
$pageTitle = 'عیب‌یابی پیامک و ایمیل';

$balanceResult = null;
$patternResult = null;
$smtpResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'check_balance') {
        $balanceResult = FarazSmsService::checkBalance();
    } elseif ($action === 'check_pattern') {
        $patternResult = FarazSmsService::checkPattern(FARAZ_OTP_PATTERN_CODE);
    } elseif ($action === 'check_smtp') {
        $smtpResult = EmailService::testConnection();
    }
}

// Show the current config values (the API key is partially masked)
function maskSecret(string $value): string
{
    if ($value === '') return '(خالی)';
    if (mb_strlen($value) <= 8) return str_repeat('•', mb_strlen($value));
    return mb_substr($value, 0, 4) . str_repeat('•', mb_strlen($value) - 8) . mb_substr($value, -4);
}

$configSnapshot = [
    'FARAZ_SMS_ENABLED' => FARAZ_SMS_ENABLED ? 'true' : 'false',
    'FARAZ_API_KEY' => maskSecret(FARAZ_API_KEY),
    'FARAZ_OTP_PATTERN_CODE' => FARAZ_OTP_PATTERN_CODE ?: '(خالی)',
    'FARAZ_OTP_PATTERN_VAR' => FARAZ_OTP_PATTERN_VAR ?: '(خالی)',
    'FARAZ_LINE_NUMBER' => FARAZ_LINE_NUMBER ?: '(خالی)',
    'SMTP_ENABLED' => SMTP_ENABLED ? 'true' : 'false',
    'SMTP_HOST' => SMTP_HOST,
    'SMTP_PORT' => (string) SMTP_PORT,
    'SMTP_USERNAME' => SMTP_USERNAME,
    'SMTP_PASSWORD' => maskSecret(SMTP_PASSWORD),
];

// Simple sanity check on the line-number format (a warning, not a hard rejection — the exact format can differ between SMS panels)
$lineNumberWarning = null;
if (FARAZ_LINE_NUMBER !== '') {
    $digitsOnly = preg_replace('/\D/', '', FARAZ_LINE_NUMBER);
    if (strlen($digitsOnly) > 13 || strpos(FARAZ_LINE_NUMBER, '+98') === 0) {
        $lineNumberWarning = 'مقدار FARAZ_LINE_NUMBER غیرمعمول به نظر می‌رسد (خیلی طولانی است یا با +98 شروع می‌شود). شماره خط‌های فراز معمولاً بدون پیش‌شماره کشور و کوتاه‌تر هستند؛ لطفاً آن را دقیقاً از پنل فراز > بخش «خطوط» کپی کنید.';
    }
}

renderView('admin/diagnostics', compact('pageTitle', 'configSnapshot', 'lineNumberWarning', 'balanceResult', 'patternResult', 'smtpResult'));
