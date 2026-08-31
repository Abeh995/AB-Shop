<?php
/**
 * Pattern-based SMS-sending service via Faraz SMS (Iran Payamak)
 * Docs: https://docs.iranpayamak.com — Endpoint: POST /ws/v1/sms/pattern
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
     * Send a verification code using the pattern registered in the Faraz panel
     * @return array ['ok'=>bool, 'error'=>string|null]
     */
    public static function sendOtp(string $phone, string $code): array
    {
        $message = "کد تایید: {$code}"; // For logging only; the actual SMS text is built from the pattern in the Faraz panel

        if (!FARAZ_SMS_ENABLED || FARAZ_API_KEY === '' || FARAZ_OTP_PATTERN_CODE === '' || FARAZ_LINE_NUMBER === '') {
            self::logAttempt($phone, $message, 'logged (Faraz SMS not configured)', [
                'reason' => 'یکی از تنظیمات FARAZ_SMS_ENABLED / FARAZ_API_KEY / FARAZ_OTP_PATTERN_CODE / FARAZ_LINE_NUMBER خالی یا غیرفعال است.',
            ]);
            return ['ok' => false, 'error' => null];
        }

        if (!function_exists('curl_init')) {
            self::logAttempt($phone, $message, 'failed (no curl)', ['reason' => 'افزونه cURL روی این سرور فعال نیست.']);
            return ['ok' => false, 'error' => 'افزونه cURL روی سرور فعال نیست.'];
        }

        $payload = [
            'code' => FARAZ_OTP_PATTERN_CODE,
            'recipient' => $phone,
            'attributes' => [FARAZ_OTP_PATTERN_VAR => $code],
            'line_number' => FARAZ_LINE_NUMBER,
            'number_format' => 'english',
        ];

        $result = self::callApi('POST', self::API_URL, $payload);

        if (!$result['ok']) {
            self::logAttempt($phone, $message, 'failed: connection error', $result['debug']);
            return ['ok' => false, 'error' => 'ارتباط با سرور پیامک برقرار نشد. جزئیات در صفحه «لاگ پیامک و ایمیل» پنل ادمین موجود است.'];
        }

        $data = $result['data'];
        if (($data['status'] ?? '') === 'success') {
            self::logAttempt($phone, $message, 'sent', $result['debug']);
            return ['ok' => true, 'error' => null];
        }

        $errMsg = self::extractMessage($data['messages'] ?? null) ?: 'خطای نامشخص از سرویس پیامک.';
        self::logAttempt($phone, $message, 'failed: ' . $errMsg, $result['debug']);
        return ['ok' => false, 'error' => $errMsg];
    }

    /**
     * Check the API key's validity by fetching the account balance — sends no
     * SMS and has no cost.
     * Used on the admin panel's "Diagnostics" page.
     */
    public static function checkBalance(): array
    {
        if (FARAZ_API_KEY === '') {
            return ['ok' => false, 'summary' => 'کلید API خالی است.', 'debug' => null];
        }
        $result = self::callApi('GET', self::BALANCE_URL, null);
        if (!$result['ok']) {
            return ['ok' => false, 'summary' => 'اتصال به سرور فراز برقرار نشد.', 'debug' => $result['debug']];
        }
        $data = $result['data'];
        if (($data['status'] ?? '') === 'success') {
            $amount = $data['data']['balanceAmount'] ?? '?';
            $count = $data['data']['balanceCount'] ?? '?';
            return ['ok' => true, 'summary' => "کلید API معتبر است. موجودی: {$amount} تومان، تقریباً {$count} پیامک.", 'debug' => $result['debug']];
        }
        $errMsg = self::extractMessage($data['message'] ?? ($data['messages'] ?? null)) ?: 'کلید API نامعتبر است یا خطای دیگری رخ داده.';
        return ['ok' => false, 'summary' => $errMsg, 'debug' => $result['debug']];
    }

    /**
     * Fetch a pattern's details (including its variables' exact names) to
     * compare against FARAZ_OTP_PATTERN_VAR in config.php.
     */
    public static function checkPattern(string $patternCode): array
    {
        if (FARAZ_API_KEY === '' || $patternCode === '') {
            return ['ok' => false, 'summary' => 'کلید API یا کد پترن خالی است.', 'debug' => null];
        }
        $result = self::callApi('GET', self::PATTERN_URL . rawurlencode($patternCode), null);
        if (!$result['ok']) {
            return ['ok' => false, 'summary' => 'اتصال به سرور فراز برقرار نشد.', 'debug' => $result['debug']];
        }
        $data = $result['data'];
        if (($data['status'] ?? '') === 'success') {
            return ['ok' => true, 'summary' => 'پترن پیدا شد.', 'raw' => $data, 'debug' => $result['debug']];
        }
        $errMsg = self::extractMessage($data['messages'] ?? ($data['message'] ?? null)) ?: 'پترن با این کد پیدا نشد.';
        return ['ok' => false, 'summary' => $errMsg, 'debug' => $result['debug']];
    }

    /**
     * Generic cURL call that records full details for debugging (HTTP code,
     * raw response, cURL error)
     */
    private static function callApi(string $method, string $url, ?array $payload): array
    {
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
            $options[CURLOPT_POSTFIELDS] = json_encode($payload);
        }
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
            'raw_response' => $response !== false ? mb_substr((string) $response, 0, 2000) : null,
        ];

        if ($response === false || $curlErrno !== 0) {
            error_log('FarazSMS request failed: ' . $curlError);
            return ['ok' => false, 'data' => null, 'debug' => $debug];
        }

        $data = json_decode($response, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $debug['json_error'] = json_last_error_msg();
            return ['ok' => false, 'data' => null, 'debug' => $debug];
        }

        return ['ok' => true, 'data' => $data, 'debug' => $debug];
    }

    private static function extractMessage($messages): ?string
    {
        if (is_string($messages)) return $messages;
        if (is_array($messages)) return implode(' | ', array_map('strval', $messages));
        return null;
    }

    private static function logAttempt(string $phone, string $message, string $status, $debug): void
    {
        try {
            db()->prepare("INSERT INTO sms_log (phone, message, status, debug_info) VALUES (?, ?, ?, ?)")
                ->execute([$phone, $message, $status, $debug ? json_encode($debug, JSON_UNESCAPED_UNICODE) : null]);
        } catch (Exception $e) {
            error_log('sms_log insert failed: ' . $e->getMessage());
        }
    }
}
