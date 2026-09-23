<?php
/**
 * Pattern-based SMS-sending service via Faraz SMS (Iran Payamak)
 * Docs: https://docs.iranpayamak.com — Endpoint: POST /ws/v1/sms/pattern
 *
 * OTP pattern:
 *   کد احراز:
 *   %code%
 *   AB Socks-Shop
 *
 *   @absocks.ir #%code2%
 *
 * The same OTP value is sent to both pattern variables:
 *   %code%  = $code
 *   %code2% = $code
 *
 * Since 1.2.2, this logs every send attempt in full (request + raw response
 * + HTTP code) in the sms_log.debug_info column, so the exact reason a send
 * failed can be seen from the admin panel ("SMS & email log") without
 * needing SSH or server log access.
 */

class FarazSmsService
{
    private const API_URL = 'https://api.iranpayamak.com/ws/v1/sms/pattern';
    private const BALANCE_URL = 'https://api.iranpayamak.com/ws/v1/account/balance';
    private const PATTERN_URL = 'https://api.iranpayamak.com/ws/v1/patterns/';

    /**
     * Secondary variable required by the Faraz pattern.
     *
     * The registered pattern currently uses:
     *   %code%
     *   %code2%
     *
     * Both variables intentionally receive the exact same OTP.
     */
    private const OTP_SECOND_PATTERN_VAR = 'code2';

    /**
     * Send an arbitrary pattern using Faraz SMS.
     *
     * @param string $patternCode Registered pattern code in Faraz panel
     * @param string $phone Recipient mobile number (e.g. 09123456789)
     * @param array $attributes Key-value array of pattern variables (e.g. ['code' => '123456'])
     * @param string $logLabel Optional label for sms_log entry
     * @return array ['ok'=>bool, 'error'=>string|null]
     */
    public static function sendPattern(string $patternCode, string $phone, array $attributes, string $logLabel = ''): array
    {
        $logMessage = $logLabel !== '' ? $logLabel : "پترن {$patternCode}: " . json_encode($attributes, JSON_UNESCAPED_UNICODE);

        if (
            !FARAZ_SMS_ENABLED ||
            FARAZ_API_KEY === '' ||
            $patternCode === '' ||
            FARAZ_LINE_NUMBER === ''
        ) {
            self::logAttempt(
                $phone,
                $logMessage,
                'logged (Faraz SMS not configured)',
                [
                    'reason' => 'یکی از تنظیمات FARAZ_SMS_ENABLED / FARAZ_API_KEY / FARAZ_LINE_NUMBER یا کد پترن خالی یا غیرفعال است.',
                    'pattern_code' => $patternCode,
                ]
            );

            return ['ok' => false, 'error' => null];
        }

        if (!function_exists('curl_init')) {
            self::logAttempt(
                $phone,
                $logMessage,
                'failed (no curl)',
                [
                    'reason' => 'افزونه cURL روی این سرور فعال نیست.',
                ]
            );

            return [
                'ok' => false,
                'error' => 'افزونه cURL روی سرور فعال نیست.'
            ];
        }

        $payload = [
            'code' => $patternCode,
            'recipient' => $phone,
            'attributes' => $attributes,
            'line_number' => FARAZ_LINE_NUMBER,
            'number_format' => 'english',
        ];

        $result = self::callApi('POST', self::API_URL, $payload);

        if (!$result['ok']) {
            self::logAttempt(
                $phone,
                $logMessage,
                'failed: connection error',
                $result['debug']
            );

            return [
                'ok' => false,
                'error' => 'ارتباط با سرور پیامک برقرار نشد. جزئیات در صفحه «لاگ پیامک و ایمیل» پنل ادمین موجود است.'
            ];
        }

        $data = $result['data'];

        if (($data['status'] ?? '') === 'success') {
            self::logAttempt(
                $phone,
                $logMessage,
                'sent',
                $result['debug']
            );

            return ['ok' => true, 'error' => null];
        }

        $errMsg =
            self::extractMessage($data['messages'] ?? null)
            ?: 'خطای نامشخص از سرویس پیامک.';

        self::logAttempt(
            $phone,
            $logMessage,
            'failed: ' . $errMsg,
            $result['debug']
        );

        return [
            'ok' => false,
            'error' => $errMsg
        ];
    }

    /**
     * Send a pattern mapped to a specific system event (e.g. 'otp', 'order_shipped').
     *
     * @return array ['ok'=>bool, 'error'=>string|null]
     */
    public static function sendPatternByEvent(string $eventKey, string $phone, array $attributes, string $logLabel = ''): array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM sms_patterns WHERE event_key = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$eventKey]);
            $pattern = $stmt->fetch();

            if ($pattern && !empty($pattern['pattern_code'])) {
                return self::sendPattern($pattern['pattern_code'], $phone, $attributes, $logLabel);
            }
        } catch (Exception $e) {
            error_log("Failed to query sms_patterns for event {$eventKey}: " . $e->getMessage());
        }

        return [
            'ok' => false,
            'error' => "الگوی پیامک فعالی برای رویداد '{$eventKey}' یافت نشد."
        ];
    }

    /**
     * Send a verification code using the pattern registered in the Faraz panel.
     * Looks up active pattern in sms_patterns table with fallback to config.php.
     *
     * @return array ['ok'=>bool, 'error'=>string|null]
     */
    public static function sendOtp(string $phone, string $code): array
    {
        $patternCode = FARAZ_OTP_PATTERN_CODE;
        $attributes = [
            FARAZ_OTP_PATTERN_VAR => $code,
            self::OTP_SECOND_PATTERN_VAR => $code,
        ];

        // Check if an active OTP pattern exists in the database
        try {
            $stmt = db()->prepare("SELECT * FROM sms_patterns WHERE event_key = 'otp' AND is_active = 1 LIMIT 1");
            $stmt->execute();
            $dbPattern = $stmt->fetch();

            if ($dbPattern && !empty($dbPattern['pattern_code'])) {
                $patternCode = $dbPattern['pattern_code'];
                $config = json_decode($dbPattern['variables_config'] ?? '[]', true);
                if (!empty($config) && is_array($config)) {
                    $customAttrs = [];
                    foreach ($config as $var) {
                        $varName = trim($var['name'] ?? '');
                        if ($varName !== '') {
                            $customAttrs[$varName] = $code;
                        }
                    }
                    if (!empty($customAttrs)) {
                        $attributes = $customAttrs;
                    }
                }
            }
        } catch (Exception $e) {
            // Table might not exist yet during migration; fall back safely
        }

        return self::sendPattern($patternCode, $phone, $attributes, "کد تایید: {$code}");
    }

    /**
     * Check the API key's validity by fetching the account balance.
     * Sends no SMS and has no cost.
     *
     * Used on the admin panel's "Diagnostics" page.
     */
    public static function checkBalance(): array
    {
        if (FARAZ_API_KEY === '') {
            return [
                'ok' => false,
                'summary' => 'کلید API خالی است.',
                'debug' => null
            ];
        }

        $result = self::callApi(
            'GET',
            self::BALANCE_URL,
            null
        );

        if (!$result['ok']) {
            return [
                'ok' => false,
                'summary' => 'اتصال به سرور فراز برقرار نشد.',
                'debug' => $result['debug']
            ];
        }

        $data = $result['data'];

        if (($data['status'] ?? '') === 'success') {
            $amount = $data['data']['balanceAmount'] ?? '?';
            $count = $data['data']['balanceCount'] ?? '?';

            return [
                'ok' => true,
                'summary' => "کلید API معتبر است. موجودی: {$amount} تومان، تقریباً {$count} پیامک.",
                'debug' => $result['debug']
            ];
        }

        $errMsg =
            self::extractMessage(
                $data['message'] ?? ($data['messages'] ?? null)
            )
            ?: 'کلید API نامعتبر است یا خطای دیگری رخ داده.';

        return [
            'ok' => false,
            'summary' => $errMsg,
            'debug' => $result['debug']
        ];
    }

    /**
     * Fetch a pattern's details (including its variables' exact names)
     * to compare against FARAZ_OTP_PATTERN_VAR in config.php.
     */
    public static function checkPattern(string $patternCode): array
    {
        if (
            FARAZ_API_KEY === '' ||
            $patternCode === ''
        ) {
            return [
                'ok' => false,
                'summary' => 'کلید API یا کد پترن خالی است.',
                'debug' => null
            ];
        }

        $result = self::callApi(
            'GET',
            self::PATTERN_URL . rawurlencode($patternCode),
            null
        );

        if (!$result['ok']) {
            return [
                'ok' => false,
                'summary' => 'اتصال به سرور فراز برقرار نشد.',
                'debug' => $result['debug']
            ];
        }

        $data = $result['data'];

        if (($data['status'] ?? '') === 'success') {
            return [
                'ok' => true,
                'summary' => 'پترن پیدا شد.',
                'raw' => $data,
                'debug' => $result['debug']
            ];
        }

        $errMsg =
            self::extractMessage(
                $data['messages'] ?? ($data['message'] ?? null)
            )
            ?: 'پترن با این کد پیدا نشد.';

        return [
            'ok' => false,
            'summary' => $errMsg,
            'debug' => $result['debug']
        ];
    }

    /**
     * Generic cURL call that records full details for debugging.
     *
     * Includes:
     * - HTTP code
     * - raw response
     * - cURL error
     * - request payload
     */
    private static function callApi(
        string $method,
        string $url,
        ?array $payload
    ): array {
        $ch = curl_init($url);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Api-Key: ' . FARAZ_API_KEY,
            ],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE
            );
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        $httpCode = curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);

        curl_close($ch);

        $debug = [
            'url' => $url,
            'method' => $method,
            'request_payload' => $payload,
            'http_code' => $httpCode,
            'curl_errno' => $curlErrno,
            'curl_error' => $curlError ?: null,
            'raw_response' =>
                $response !== false
                    ? mb_substr((string) $response, 0, 2000)
                    : null,
        ];

        if (
            $response === false ||
            $curlErrno !== 0
        ) {
            error_log(
                'FarazSMS request failed: ' . $curlError
            );

            return [
                'ok' => false,
                'data' => null,
                'debug' => $debug
            ];
        }

        $data = json_decode(
            $response,
            true
        );

        if (
            $data === null &&
            json_last_error() !== JSON_ERROR_NONE
        ) {
            $debug['json_error'] =
                json_last_error_msg();

            return [
                'ok' => false,
                'data' => null,
                'debug' => $debug
            ];
        }

        return [
            'ok' => true,
            'data' => $data,
            'debug' => $debug
        ];
    }

    private static function extractMessage($messages): ?string
    {
        if (is_string($messages)) {
            return $messages;
        }

        if (is_array($messages)) {
            return implode(
                ' | ',
                array_map('strval', $messages)
            );
        }

        return null;
    }

    private static function logAttempt(
        string $phone,
        string $message,
        string $status,
        $debug
    ): void {
        try {
            db()
                ->prepare(
                    "INSERT INTO sms_log
                    (phone, message, status, debug_info)
                    VALUES (?, ?, ?, ?)"
                )
                ->execute([
                    $phone,
                    $message,
                    $status,
                    $debug
                        ? json_encode(
                            $debug,
                            JSON_UNESCAPED_UNICODE
                        )
                        : null
                ]);
        } catch (Exception $e) {
            error_log(
                'sms_log insert failed: ' .
                $e->getMessage()
            );
        }
    }
}