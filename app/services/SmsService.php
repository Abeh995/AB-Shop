<?php
/**
 * SMS-sending service
 *
 * Runs in "log mode" by default; that is, until a real API key
 * (Kavenegar/Melipayamak/etc.) is set in config.php, no real SMS is sent,
 * but the message text is stored in the sms_log table so you can review it
 * in the panel. This means missing SMS credentials never causes an error
 * or crashes the site.
 *
 * To actually enable it (e.g. with Kavenegar):
 *   SMS_ENABLED = true
 *   SMS_PROVIDER_API_KEY = 'your real key'
 *   SMS_SENDER_LINE = 'your sending line number'
 */

class SmsService
{
    public static function send(string $phone, string $message): bool
    {
        $sent = false;
        $status = 'logged';

        if (SMS_ENABLED && SMS_PROVIDER_API_KEY !== '') {
            $result = self::sendViaKavenegar($phone, $message);
            $sent = $result['ok'];
            $status = $result['ok'] ? 'sent' : 'failed: ' . $result['error'];
        }

        // Always recorded in the log table (whether it's a real send or just a log)
        try {
            $stmt = db()->prepare("INSERT INTO sms_log (phone, message, status) VALUES (?, ?, ?)");
            $stmt->execute([$phone, $message, $status]);
        } catch (Exception $e) {
            error_log('SMS log insert failed: ' . $e->getMessage());
        }

        return $sent;
    }

    /**
     * Send via Kavenegar (Iran's most common SMS provider)
     * Docs: https://kavenegar.com/rest.html
     */
    private static function sendViaKavenegar(string $phone, string $message): array
    {
        if (!function_exists('curl_init')) {
            return ['ok' => false, 'error' => 'cURL فعال نیست'];
        }

        $url = 'https://api.kavenegar.com/v1/' . SMS_PROVIDER_API_KEY . '/sms/send.json';
        $params = [
            'receptor' => $phone,
            'message'  => $message,
            'sender'   => SMS_SENDER_LINE,
        ];

        $ch = curl_init($url . '?' . http_build_query($params));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'error' => $error];
        }

        $data = json_decode($response, true);
        $status = $data['return']['status'] ?? 0;
        if ($status == 200) {
            return ['ok' => true, 'error' => null];
        }
        return ['ok' => false, 'error' => $data['return']['message'] ?? 'خطای نامشخص'];
    }

    // ---------- Ready-made messages for various order events ----------

    public static function notifyOrderConfirmed(string $phone, string $orderCode): void
    {
        self::send($phone, SITE_NAME . "\nسفارش شما با کد {$orderCode} با موفقیت پرداخت و ثبت شد. با تشکر از خرید شما.");
    }

    public static function notifyOrderStatusChanged(string $phone, string $orderCode, string $statusLabel): void
    {
        self::send($phone, SITE_NAME . "\nوضعیت سفارش {$orderCode} به «{$statusLabel}» تغییر کرد.");
    }
}
