<?php
/**
 * SMS Pattern Create / Edit / Test controller.
 * Supports dynamic variable definition (name, type, max length, label) and live test sending.
 */

$id = (int) ($_GET['id'] ?? 0);
$isNew = ($id === 0);
$pageTitle = $isNew ? 'تعریف الگوی پیامک جدید' : 'ویرایش الگوی پیامک';

$availableEvents = [
    ''                      => '-- بدون انتساب به رویداد سیستمی --',
    'otp'                   => 'کد تایید ورود و ثبت‌نام (OTP)',
    'order_created'         => 'ثبت سفارش جدید برای مشتری',
    'order_shipped'         => 'ارسال و تحویل سفارش به پست/پیک',
    'card_to_card_approved' => 'تایید واریز کارت‌به‌کارت',
    'admin_new_order'       => 'اطلاع سفارش جدید به مدیر فروشگاه',
];

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
    $stmt = db()->prepare("SELECT * FROM sms_patterns WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
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
        $testPhone = preg_replace('/\D+/', '', trim($_POST['test_phone'] ?? ''));
        $testPatternCode = trim($_POST['pattern_code'] ?? $pattern['pattern_code']);
        
        if (empty($testPhone) || strlen($testPhone) < 10) {
            setFlash('error', 'لطفاً شماره تلفن همراه معتبر برای تست وارد کنید.');
            redirect("sms_pattern_edit.php?id={$id}");
        }

        if (empty($testPatternCode)) {
            setFlash('error', 'کد پترن خالی است.');
            redirect("sms_pattern_edit.php?id={$id}");
        }

        $testAttrs = [];
        $varNames = $_POST['test_var_name'] ?? [];
        $varValues = $_POST['test_var_value'] ?? [];
        foreach ($varNames as $idx => $vName) {
            $vName = trim($vName);
            if ($vName !== '') {
                $testAttrs[$vName] = trim($varValues[$idx] ?? '');
            }
        }

        $testResult = FarazSmsService::sendPattern($testPatternCode, $testPhone, $testAttrs, "تست الگوی {$testPatternCode}");
        if ($testResult['ok']) {
            setFlash('success', "پیامک تستی با موفقیت به شماره {$testPhone} ارسال شد.");
        } else {
            setFlash('error', 'ارسال پیامک تستی ناموفق بود: ' . ($testResult['error'] ?? 'خطای نامشخص'));
        }
        redirect($isNew ? 'sms_patterns.php' : "sms_pattern_edit.php?id={$id}");
    }

    // Save action
    $patternCode = trim($_POST['pattern_code'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $eventKey = trim($_POST['event_key'] ?? '');
    $patternText = trim($_POST['pattern_text'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $varNames = $_POST['var_name'] ?? [];
    $varTypes = $_POST['var_type'] ?? [];
    $varMaxLens = $_POST['var_max_len'] ?? [];
    $varLabels = $_POST['var_label'] ?? [];

    $variablesConfig = [];
    foreach ($varNames as $idx => $vName) {
        $vName = trim($vName);
        if ($vName !== '') {
            $variablesConfig[] = [
                'name'    => $vName,
                'type'    => in_array($varTypes[$idx] ?? '', ['string', 'numeric', 'alphanumeric'], true) ? $varTypes[$idx] : 'string',
                'max_len' => max(1, min(200, (int) ($varMaxLens[$idx] ?? 30))),
                'label'   => trim($varLabels[$idx] ?? ''),
            ];
        }
    }

    $variablesCount = count($variablesConfig);
    if ($variablesCount === 0) {
        $variablesCount = max(1, (int) ($_POST['variables_count'] ?? 1));
    }

    if ($patternCode === '') {
        setFlash('error', 'کد پترن نمی‌تواند خالی باشد.');
        redirect($isNew ? 'sms_pattern_edit.php' : "sms_pattern_edit.php?id={$id}");
    }

    if ($title === '') {
        setFlash('error', 'عنوان الگو الزامی است.');
        redirect($isNew ? 'sms_pattern_edit.php' : "sms_pattern_edit.php?id={$id}");
    }

    $jsonConfig = json_encode($variablesConfig, JSON_UNESCAPED_UNICODE);

    if ($isNew) {
        $stmt = db()->prepare("
            INSERT INTO sms_patterns 
            (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $patternCode,
            $title,
            $eventKey !== '' ? $eventKey : null,
            $patternText,
            $description,
            $variablesCount,
            $jsonConfig,
            $isActive
        ]);
        $newId = (int) db()->lastInsertId();
        setFlash('success', 'الگوی پیامک جدید با موفقیت ذخیره شد.');
        redirect("sms_pattern_edit.php?id={$newId}");
    } else {
        $stmt = db()->prepare("
            UPDATE sms_patterns SET
                pattern_code = ?,
                title = ?,
                event_key = ?,
                pattern_text = ?,
                description = ?,
                variables_count = ?,
                variables_config = ?,
                is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $patternCode,
            $title,
            $eventKey !== '' ? $eventKey : null,
            $patternText,
            $description,
            $variablesCount,
            $jsonConfig,
            $isActive,
            $id
        ]);
        setFlash('success', 'الگوی پیامک با موفقیت به‌روزرسانی شد.');
        redirect("sms_pattern_edit.php?id={$id}");
    }
}

renderView('admin/sms_pattern_edit', compact(
    'pageTitle',
    'isNew',
    'id',
    'pattern',
    'variables',
    'availableEvents'
));
