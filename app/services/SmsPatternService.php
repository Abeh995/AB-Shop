<?php
/**
 * SMS Pattern Service
 *
 * Encapsulates SMS Pattern CRUD, dynamic variable definitions, event mappings,
 * and live testing via FarazSmsService.
 *
 * Invariants:
 * - All database queries for sms_patterns live here (Rule 7).
 * - Variable configs are validated and serialized as valid JSON.
 */

/**
 * Available system events that SMS patterns can be assigned to.
 */
function getAvailableSmsEvents(): array
{
    return [
        ''                      => '-- بدون انتساب به رویداد سیستمی --',
        'otp'                   => 'کد تایید ورود و ثبت‌نام (OTP)',
        'order_created'         => 'ثبت سفارش جدید برای مشتری',
        'order_shipped'         => 'ارسال و تحویل سفارش به پست/پیک',
        'card_to_card_approved' => 'تایید واریز کارت‌به‌کارت',
        'admin_new_order'       => 'اطلاع سفارش جدید به مدیر فروشگاه',
    ];
}

/**
 * Fetch all SMS patterns.
 */
function getSmsPatternsList(): array
{
    return db()->query("SELECT * FROM sms_patterns ORDER BY id ASC")->fetchAll();
}

/**
 * Fetch a single SMS pattern by ID.
 */
function getSmsPatternById(int $id): ?array
{
    $stmt = db()->prepare("SELECT * FROM sms_patterns WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Toggle active state of an SMS pattern.
 *
 * @return array{ok: bool, error: ?string, new_status: ?int, title: ?string}
 */
function toggleSmsPatternStatus(int $id): array
{
    $stmt = db()->prepare("SELECT is_active, title FROM sms_patterns WHERE id = ?");
    $stmt->execute([$id]);
    $pattern = $stmt->fetch();
    if (!$pattern) {
        return ['ok' => false, 'error' => 'الگوی پیامک یافت نشد.', 'new_status' => null, 'title' => null];
    }

    $newStatus = $pattern['is_active'] ? 0 : 1;
    db()->prepare("UPDATE sms_patterns SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);

    return ['ok' => true, 'error' => null, 'new_status' => $newStatus, 'title' => $pattern['title']];
}

/**
 * Delete an SMS pattern.
 *
 * @return array{ok: bool, error: ?string, title: ?string}
 */
function deleteSmsPatternRecord(int $id): array
{
    $stmt = db()->prepare("SELECT title FROM sms_patterns WHERE id = ?");
    $stmt->execute([$id]);
    $pattern = $stmt->fetch();
    if (!$pattern) {
        return ['ok' => false, 'error' => 'الگوی پیامک یافت نشد.', 'title' => null];
    }

    db()->prepare("DELETE FROM sms_patterns WHERE id = ?")->execute([$id]);
    return ['ok' => true, 'error' => null, 'title' => $pattern['title']];
}

/**
 * Save (create or update) an SMS pattern record.
 *
 * @return array{ok: bool, error: ?string, id: ?int}
 */
function saveSmsPatternRecord(int $id, array $data): array
{
    $pdo = db();
    $patternCode = trim($data['pattern_code'] ?? '');
    $title = trim($data['title'] ?? '');
    $eventKey = trim($data['event_key'] ?? '');
    $patternText = trim($data['pattern_text'] ?? '');
    $description = trim($data['description'] ?? '');
    $isActive = !empty($data['is_active']) ? 1 : 0;

    if ($patternCode === '') {
        return ['ok' => false, 'error' => 'کد پترن الزامی است.', 'id' => null];
    }
    if ($title === '') {
        return ['ok' => false, 'error' => 'عنوان الگو الزامی است.', 'id' => null];
    }

    // Build variables configuration
    $varNames = $data['var_name'] ?? [];
    $varTypes = $data['var_type'] ?? [];
    $varMaxLens = $data['var_max_len'] ?? [];
    $varLabels = $data['var_label'] ?? [];

    $variablesConfig = [];
    foreach ($varNames as $idx => $vName) {
        $vName = trim($vName);
        if ($vName !== '') {
            $variablesConfig[] = [
                'name'    => $vName,
                'type'    => in_array($varTypes[$idx] ?? '', ['string', 'numeric', 'alphanumeric'], true) ? $varTypes[$idx] : 'string',
                'max_len' => max(1, (int) ($varMaxLens[$idx] ?? 50)),
                'label'   => trim($varLabels[$idx] ?? $vName),
            ];
        }
    }

    $variablesCount = count($variablesConfig);
    $variablesJson = json_encode($variablesConfig, JSON_UNESCAPED_UNICODE);

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE sms_patterns 
                SET pattern_code = ?, title = ?, event_key = ?, pattern_text = ?, description = ?, 
                    variables_count = ?, variables_config = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$patternCode, $title, $eventKey ?: null, $patternText, $description, $variablesCount, $variablesJson, $isActive, $id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO sms_patterns 
                    (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$patternCode, $title, $eventKey ?: null, $patternText, $description, $variablesCount, $variablesJson, $isActive]);
            $id = (int) $pdo->lastInsertId();
        }

        return ['ok' => true, 'error' => null, 'id' => $id];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'خطا در ذخیره الگوی پیامک: ' . $e->getMessage(), 'id' => null];
    }
}

/**
 * Send a live test SMS via FarazSmsService.
 *
 * @return array{ok: bool, error: ?string}
 */
function testSendSmsPatternRecord(string $patternCode, string $phone, array $attrs): array
{
    $phone = preg_replace('/\D+/', '', trim($phone));
    $patternCode = trim($patternCode);

    if (empty($phone) || strlen($phone) < 10) {
        return ['ok' => false, 'error' => 'شماره تلفن همراه معتبر نیست.'];
    }
    if (empty($patternCode)) {
        return ['ok' => false, 'error' => 'کد پترن خالی است.'];
    }

    return FarazSmsService::sendPattern($patternCode, $phone, $attrs, "تست الگوی {$patternCode}");
}
