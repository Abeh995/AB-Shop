<?php
/**
 * SMS Pattern Create / Edit / Test controller.
 * Supports dynamic variable definition and live test sending.
 * All database operations and API calls are delegated to SmsPatternService (Rule 7).
 */

$id = (int) ($_GET['id'] ?? 0);
$isNew = ($id === 0);
$pageTitle = $isNew ? 'تعریف الگوی پیامک جدید' : 'ویرایش الگوی پیامک';
$availableEvents = getAvailableSmsEvents();

$pattern = [
    'id'               => 0,
    'pattern_code'     => '',
    'title'            => '',
    'event_key'        => '',
    'pattern_text'     => '',
    'description'      => '',
    'variables_count'  => 1,
    'variables_config' => '[]',
    'is_active'        => 1,
];

if (!$isNew) {
    $existing = getSmsPatternById($id);
    if (!$existing) {
        setFlash('error', 'الگوی پیامک مورد نظر یافت نشد.');
        redirect('sms_patterns.php');
    }
    $pattern = $existing;
}

$variables = json_decode($pattern['variables_config'] ?? '[]', true);
if (!is_array($variables)) {
    $variables = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $subAction = $_POST['sub_action'] ?? 'save';

    if ($subAction === 'test_send') {
        $testPhone = trim($_POST['test_phone'] ?? '');
        $testPatternCode = trim($_POST['pattern_code'] ?? $pattern['pattern_code']);
        $varNames = $_POST['test_var_name'] ?? [];
        $varValues = $_POST['test_var_value'] ?? [];
        $testAttrs = [];
        foreach ($varNames as $idx => $vName) {
            $vName = trim($vName);
            if ($vName !== '') {
                $testAttrs[$vName] = trim($varValues[$idx] ?? '');
            }
        }

        $testResult = testSendSmsPatternRecord($testPatternCode, $testPhone, $testAttrs);
        if ($testResult['ok']) {
            setFlash('success', "پیامک تستی با موفقیت به شماره {$testPhone} ارسال شد.");
        } else {
            setFlash('error', 'ارسال پیامک تستی ناموفق بود: ' . ($testResult['error'] ?? 'خطای نامشخص'));
        }
        redirect($isNew ? 'sms_patterns.php' : "sms_pattern_edit.php?id={$id}");
    }

    $saveRes = saveSmsPatternRecord($id, $_POST);
    if ($saveRes['ok']) {
        setFlash('success', 'الگوی پیامک با موفقیت ذخیره شد.');
        redirect('sms_patterns.php');
    } else {
        setFlash('error', $saveRes['error']);
        redirect($isNew ? 'sms_pattern_edit.php' : "sms_pattern_edit.php?id={$id}");
    }
}

renderView('admin/sms_pattern_edit', compact('pageTitle', 'pattern', 'isNew', 'availableEvents', 'variables'));
