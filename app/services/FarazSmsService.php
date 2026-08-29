<?php
/**
 * Pattern-based SMS service using Faraz SMS (Iran Payamak).
 * Documentation: https://docs.iranpayamak.com — Endpoint: POST /ws/v1/sms/pattern
 *
 * This service is dedicated to OTP delivery and is separate from the general SmsService
 * used for routine notifications such as order status updates. Pattern messages must use
 * a pre-approved template configured in the Faraz panel.
 *
 * Fail-safe by default: when FARAZ_SMS_ENABLED is false or the API key is empty, no real
 * SMS is sent; the verification code is stored and logged for development/testing.
 */

class FarazSmsService
{
    private const API_URL = 'https://api.iranpayamak.com/ws/v1/sms/pattern';

    /**
     * Send a verification code using the registered Faraz pattern.
     * @return array ['ok'=>bool, 'error'=>string|null]
     */
    public static function sendOtp(string $phone, string $code): array
    {
        $message = "کد تایید: {$code}"; // Log-only metadata; the actual SMS body is generated from the configured Faraz pattern.

        if (!FARAZ_SMS_ENABLED || FARAZ_API_KEY === '' || FARAZ_OTP_PATTERN_CODE === '' || FARAZ_LINE_NUMBER === '') {
            self::logOnly($phone, $message, 'logged (Faraz SMS not configured)');
            return ['ok' => false, 'error' => null]; // null means the service is not configured, not that a network error occurred; the controller decides what to show.
        }

        if (!function_exists('curl_init')) {
            self::logOnly($phone, $message, 'failed (no curl)');
            return ['ok' => false, 'error' => 'افزونه cURL روی سرور فعال نیست.'];
        }

        $payload = [
            'code' => FARAZ_OTP_PATTERN_CODE,
            'recipient' => $phone,
            'attributes' => [FARAZ_OTP_PATTERN_VAR => $code],
            'line_number' => FARAZ_LINE_NUMBER,
            'number_format' => 'en',
        ];

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Api-Key: ' . FARAZ_API_KEY,
            ],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log('FarazSMS request failed: ' . $curlError);
            self::logOnly($phone, $message, 'failed: connection error');
            return ['ok' => false, 'error' => 'ارتباط با سرور پیامک برقرار نشد.'];
        }

        $data = json_decode($response, true);
        if (($data['status'] ?? '') === 'success') {
            self::logOnly($phone, $message, 'sent');
            return ['ok' => true, 'error' => null];
        }

        $errMsg = is_string($data['messages'] ?? null) ? $data['messages'] : 'خطای نامشخص از سرویس پیامک.';
        error_log('FarazSMS send failed: ' . $response);
        self::logOnly($phone, $message, 'failed: ' . $errMsg);
        return ['ok' => false, 'error' => $errMsg];
    }

    private static function logOnly(string $phone, string $message, string $status): void
    {
        try {
            db()->prepare("INSERT INTO sms_log (phone, message, status) VALUES (?, ?, ?)")->execute([$phone, $message, $status]);
        } catch (Exception $e) {
            error_log('sms_log insert failed: ' . $e->getMessage());
        }
    }
}
