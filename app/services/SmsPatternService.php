<?php
/**
 * SMS Pattern Service
 *
 * Encapsulates SMS Pattern CRUD, dynamic variable definitions, event mappings,
 * token data-binding catalog, and live testing/dispatching via FarazSmsService.
 *
 * Invariants:
 * - All database queries for sms_patterns live here (Rule 7).
 * - Variable configs are validated and serialized as valid JSON.
 * - dispatchSmsEvent never throws uncaught exceptions or breaks order flows.
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
        'order_paid'            => 'تایید پرداخت سفارش',
        'order_shipped'         => 'ارسال و تحویل سفارش به پست/پیک',
        'order_delivered'       => 'تحویل سفارش به مشتری',
        'order_cancelled'       => 'لغو سفارش',
        'c2c_instructions'      => 'دستورالعمل واریز کارت‌به‌کارت برای مشتری',
        'card_to_card_approved' => 'تایید واریز کارت‌به‌کارت',
        'card_to_card_rejected' => 'رد فیش کارت‌به‌کارت',
        'admin_new_order'       => 'اطلاع سفارش جدید به مدیر فروشگاه',
        'admin_c2c_receipt'     => 'اطلاع ثبت فیش کارت‌به‌کارت به مدیر',
    ];
}

/**
 * Catalog of contextual data tokens available for dynamic variable data-binding.
 *
 * @param string|null $eventKey Specific event key or null for all events map
 * @return array Token key => Persian descriptive label
 */
function getSmsEventTokens(?string $eventKey = null): array
{
    $catalog = [
        'otp' => [
            'code'           => 'کد اعتبارسنجی (OTP)',
            'site_title'     => 'نام فروشگاه',
        ],
        'order_created' => [
            'order_code'     => 'کد پیگیری سفارش',
            'customer_name'  => 'نام و نام‌خانوادگی مشتری',
            'total_price'    => 'مبلغ کل سفارش (تومان)',
            'site_title'     => 'نام فروشگاه',
        ],
        'order_paid' => [
            'order_code'     => 'کد پیگیری سفارش',
            'customer_name'  => 'نام مشتری',
            'total_price'    => 'مبلغ پرداخت شده (تومان)',
            'ref_id'         => 'شماره تراکنش / پیگیری بانکی',
            'site_title'     => 'نام فروشگاه',
        ],
        'order_shipped' => [
            'order_code'     => 'کد پیگیری سفارش',
            'customer_name'  => 'نام مشتری',
            'tracking_code'  => 'کد رهگیری پستی ۲۴ رقمی',
            'site_title'     => 'نام فروشگاه',
        ],
        'order_delivered' => [
            'order_code'     => 'کد پیگیری سفارش',
            'customer_name'  => 'نام مشتری',
            'site_title'     => 'نام فروشگاه',
        ],
        'order_cancelled' => [
            'order_code'     => 'کد پیگیری سفارش',
            'customer_name'  => 'نام مشتری',
            'site_title'     => 'نام فروشگاه',
        ],
        'c2c_instructions' => [
            'order_code'     => 'کد پیگیری سفارش',
            'customer_name'  => 'نام مشتری',
            'total_price'    => 'مبلغ واریزی (تومان)',
            'card_number'    => 'شماره کارت فروشگاه',
            'card_holder'    => 'نام صاحب حساب فروشگاه',
            'site_title'     => 'نام فروشگاه',
        ],
        'card_to_card_approved' => [
            'order_code'     => 'کد پیگیری سفارش',
            'customer_name'  => 'نام مشتری',
            'total_price'    => 'مبلغ تایید شده (تومان)',
            'site_title'     => 'نام فروشگاه',
        ],
        'card_to_card_rejected' => [
            'order_code'       => 'کد پیگیری سفارش',
            'customer_name'    => 'نام مشتری',
            'rejection_reason' => 'علت رد فیش بانکی',
            'site_title'       => 'نام فروشگاه',
        ],
        'admin_new_order' => [
            'order_code'     => 'کد سفارش جدید',
            'customer_name'  => 'نام مشتری',
            'total_price'    => 'مبلغ سفارش (تومان)',
            'payment_method' => 'روش پرداخت (درگاه / کارت‌به‌کارت)',
            'customer_phone' => 'شماره تماس مشتری',
            'site_title'     => 'نام فروشگاه',
        ],
        'admin_c2c_receipt' => [
            'order_code'     => 'کد سفارش',
            'customer_name'  => 'نام مشتری',
            'total_price'    => 'مبلغ فیش (تومان)',
            'customer_phone' => 'شماره تماس مشتری',
            'site_title'     => 'نام فروشگاه',
        ],
    ];

    if ($eventKey !== null) {
        return $catalog[$eventKey] ?? getSmsGlobalTokens();
    }

    return $catalog;
}

/**
 * Returns complete fallback list of all system tokens that can be mapped to variables.
 */
function getSmsGlobalTokens(): array
{
    return [
        'order_code'       => 'کد پیگیری سفارش',
        'customer_name'    => 'نام مشتری',
        'customer_phone'   => 'شماره موبایل مشتری',
        'total_price'      => 'مبلغ کل / واریزی (تومان)',
        'tracking_code'    => 'کد رهگیری پستی',
        'ref_id'           => 'شماره پیگیری تراکنش بانکی',
        'rejection_reason' => 'علت رد فیش بانکی',
        'card_number'      => 'شماره کارت مقصد فروشگاه',
        'card_holder'      => 'نام صاحب حساب بانکی فروشگاه',
        'code'             => 'کد اعتبارسنجی (OTP)',
        'site_title'       => 'نام فروشگاه',
        'payment_method'   => 'روش پرداخت',
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

    // Build variables configuration with source_token data-binding
    $varNames = $data['var_name'] ?? [];
    $varTypes = $data['var_type'] ?? [];
    $varMaxLens = $data['var_max_len'] ?? [];
    $varLabels = $data['var_label'] ?? [];
    $varTokens = $data['var_token'] ?? [];

    $variablesConfig = [];
    foreach ($varNames as $idx => $vName) {
        $vName = trim($vName);
        if ($vName !== '') {
            $variablesConfig[] = [
                'name'         => $vName,
                'type'         => in_array($varTypes[$idx] ?? '', ['string', 'numeric', 'alphanumeric'], true) ? $varTypes[$idx] : 'string',
                'max_len'      => max(1, (int) ($varMaxLens[$idx] ?? 50)),
                'label'        => trim($varLabels[$idx] ?? $vName),
                'source_token' => trim($varTokens[$idx] ?? ''),
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

/**
 * Dispatch an SMS event by looking up the active pattern mapped to the event,
 * binding context data to the pattern's dynamic variables, and sending via FarazSmsService.
 *
 * @param string $eventKey System event key (e.g., 'order_created', 'order_shipped', 'admin_new_order')
 * @param array $contextData Associative array of contextual tokens/values
 * @param string|null $recipientPhone Recipient phone number (falls back to store_mobile for admin events)
 * @return array{ok: bool, error: ?string, skipped: bool}
 */
function dispatchSmsEvent(string $eventKey, array $contextData, ?string $recipientPhone = null): array
{
    try {
        $stmt = db()->prepare("SELECT * FROM sms_patterns WHERE event_key = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$eventKey]);
        $pattern = $stmt->fetch();

        if (!$pattern || empty($pattern['pattern_code']) || $pattern['pattern_code'] === 'unset') {
            return ['ok' => true, 'error' => null, 'skipped' => true];
        }

        // Determine recipient phone
        $phone = trim((string) $recipientPhone);
        if ($phone === '' && in_array($eventKey, ['admin_new_order', 'admin_c2c_receipt'], true)) {
            $phone = trim((string) (getSiteContent('store_mobile') ?: getSetting('store_mobile', '')));
        }

        $cleanPhone = preg_replace('/\D+/', '', $phone);
        if (empty($cleanPhone) || strlen($cleanPhone) < 10) {
            return ['ok' => false, 'error' => 'شماره همراه گیرنده برای رویداد پیامکی نامعتبر است.', 'skipped' => true];
        }

        // Ensure default global tokens exist in context
        if (!isset($contextData['site_title'])) {
            $contextData['site_title'] = defined('SITE_NAME') ? SITE_NAME : 'فروشگاه ای‌بی ساکس';
        }

        $config = json_decode($pattern['variables_config'] ?? '[]', true);
        $attributes = [];

        if (is_array($config) && !empty($config)) {
            foreach ($config as $var) {
                $varName = trim($var['name'] ?? '');
                if ($varName === '') {
                    continue;
                }
                $token = trim($var['source_token'] ?? '');
                $val = '';

                if ($token !== '' && array_key_exists($token, $contextData)) {
                    $val = (string) $contextData[$token];
                } elseif (array_key_exists($varName, $contextData)) {
                    $val = (string) $contextData[$varName];
                }

                // If numeric type in Faraz, keep only digits
                if (($var['type'] ?? '') === 'numeric') {
                    $val = preg_replace('/\D+/', '', $val);
                }

                $maxLen = (int) ($var['max_len'] ?? 0);
                if ($maxLen > 0 && mb_strlen($val) > $maxLen) {
                    $val = mb_substr($val, 0, $maxLen);
                }

                $attributes[$varName] = $val;
            }
        } else {
            // Direct attribute fallback
            foreach ($contextData as $k => $v) {
                if (is_scalar($v)) {
                    $attributes[$k] = (string) $v;
                }
            }
        }

        $logLabel = "رویداد {$eventKey}: " . ($contextData['order_code'] ?? $pattern['title']);
        $result = FarazSmsService::sendPattern($pattern['pattern_code'], $cleanPhone, $attributes, $logLabel);

        return [
            'ok'      => (bool) ($result['ok'] ?? false),
            'error'   => $result['error'] ?? null,
            'skipped' => false,
        ];
    } catch (Throwable $e) {
        error_log("dispatchSmsEvent failed for {$eventKey}: " . $e->getMessage());
        return ['ok' => false, 'error' => $e->getMessage(), 'skipped' => true];
    }
}

/**
 * Calculate summary metrics for the SMS patterns dashboard.
 *
 * @return array{
 *     total_patterns: int,
 *     active_patterns: int,
 *     configured_patterns: int,
 *     unset_patterns: int,
 *     total_sent_today: int,
 *     gateway_status: array{ok: bool, summary: string, debug: mixed}
 * }
 */
function getSmsPatternsSummaryMetrics(): array
{
    $pdo = db();
    $patterns = getSmsPatternsList();
    $total = count($patterns);
    $active = 0;
    $configured = 0;
    $unset = 0;

    foreach ($patterns as $p) {
        if (!empty($p['is_active'])) {
            $active++;
        }
        $code = trim($p['pattern_code'] ?? '');
        if ($code !== '' && $code !== 'unset') {
            $configured++;
        } else {
            $unset++;
        }
    }

    $sentToday = 0;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM sms_log WHERE DATE(created_at) = CURRENT_DATE()");
        $sentToday = (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $sentToday = 0;
    }

    $gatewayStatus = FarazSmsService::checkBalance();

    return [
        'total_patterns'      => $total,
        'active_patterns'     => $active,
        'configured_patterns' => $configured,
        'unset_patterns'      => $unset,
        'total_sent_today'    => $sentToday,
        'gateway_status'      => $gatewayStatus,
    ];
}

/**
 * Resolve event category key for grouping and filtering.
 */
function getSmsEventCategory(string $eventKey): string
{
    switch ($eventKey) {
        case 'otp':
            return 'auth';
        case 'order_created':
        case 'order_paid':
        case 'order_shipped':
        case 'order_delivered':
        case 'order_cancelled':
            return 'orders';
        case 'c2c_instructions':
        case 'card_to_card_approved':
        case 'card_to_card_rejected':
            return 'c2c';
        case 'admin_new_order':
        case 'admin_c2c_receipt':
            return 'admin';
        default:
            return 'other';
    }
}

