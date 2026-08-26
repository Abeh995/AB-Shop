<?php
/**
 * ZarinPal payment gateway integration (ZarinPal REST API v4).
 *
 * Sandbox mode is enabled by default so the complete payment flow can be tested before obtaining a live merchant ID.
 * To switch to live mode, update the following values in config.php:
 *   ZARINPAL_MERCHANT_ID  -> The live merchant ID from the ZarinPal dashboard
 *   ZARINPAL_SANDBOX      -> false
 *
 * Currency note: prices are stored in Toman, while the ZarinPal API expects Rials.
 * This service performs the Toman-to-Rial conversion automatically by multiplying by 10.
 */

class ZarinpalService
{
    private static function baseUrl(): string
    {
        return ZARINPAL_SANDBOX
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/'
            : 'https://payment.zarinpal.com/pg/v4/payment/';
    }

    private static function startPayUrl(): string
    {
        return ZARINPAL_SANDBOX
            ? 'https://sandbox.zarinpal.com/pg/StartPay/'
            : 'https://payment.zarinpal.com/pg/StartPay/';
    }

    /**
     * Create a payment transaction request.
     * @param int    $amountToman Payment amount in Toman.
     * @param string $description Transaction description, e.g. an order number.
     * @param string $callbackUrl URL where the customer is redirected after payment.
     * @param string|null $mobile Optional customer mobile number for prefilling the ZarinPal form.
     * @param string|null $email Optional customer email.
     * @return array ['ok'=>bool, 'authority'=>string|null, 'pay_url'=>string|null, 'error'=>string|null]
     */
    public static function request(int $amountToman, string $description, string $callbackUrl, ?string $mobile = null, ?string $email = null): array
    {
        $payload = [
            'merchant_id'  => ZARINPAL_MERCHANT_ID,
            'amount'       => $amountToman * 10, // Convert Toman to Rials.
            'description'  => $description,
            'callback_url' => $callbackUrl,
        ];
        if ($mobile) $payload['metadata']['mobile'] = $mobile;
        if ($email)  $payload['metadata']['email'] = $email;

        $result = self::postJson(self::baseUrl() . 'request.json', $payload);

        if (!$result['ok']) {
            return ['ok' => false, 'authority' => null, 'pay_url' => null, 'error' => $result['error']];
        }

        $data = $result['data']['data'] ?? null;
        if (!$data || empty($data['code']) || $data['code'] != 100) {
            $errMsg = $result['data']['errors']['message'] ?? 'خطا در ایجاد تراکنش پرداخت.';
            return ['ok' => false, 'authority' => null, 'pay_url' => null, 'error' => $errMsg];
        }

        $authority = $data['authority'];
        return [
            'ok' => true,
            'authority' => $authority,
            'pay_url' => self::startPayUrl() . $authority,
            'error' => null,
        ];
    }

    /**
     * Verify the transaction after the customer returns from the gateway.
     * @param int    $amountToman The same amount passed to request(); it must match exactly.
     * @param string $authority   Authority returned by ZarinPal in the callback.
     * @return array ['ok'=>bool, 'ref_id'=>string|null, 'error'=>string|null]
     */
    public static function verify(int $amountToman, string $authority): array
    {
        $payload = [
            'merchant_id' => ZARINPAL_MERCHANT_ID,
            'amount'      => $amountToman * 10,
            'authority'   => $authority,
        ];

        $result = self::postJson(self::baseUrl() . 'verify.json', $payload);

        if (!$result['ok']) {
            return ['ok' => false, 'ref_id' => null, 'error' => $result['error']];
        }

        $data = $result['data']['data'] ?? null;
        // Code 100 = newly verified payment; code 101 = already verified and still considered successful.
        if ($data && in_array((int)($data['code'] ?? 0), [100, 101], true)) {
            return ['ok' => true, 'ref_id' => (string)($data['ref_id'] ?? ''), 'error' => null];
        }

        $errMsg = $result['data']['errors']['message'] ?? 'پرداخت تایید نشد یا ناموفق بود.';
        return ['ok' => false, 'ref_id' => null, 'error' => $errMsg];
    }

    /**
     * Send a POST request with a JSON body and parse the JSON response.
     */
    private static function postJson(string $url, array $payload): array
    {
        if (!function_exists('curl_init')) {
            return ['ok' => false, 'data' => null, 'error' => 'افزونه cURL روی سرور فعال نیست.'];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log('Zarinpal request failed: ' . $curlError);
            return ['ok' => false, 'data' => null, 'error' => 'ارتباط با درگاه پرداخت برقرار نشد.'];
        }

        $data = json_decode($response, true);
        if ($data === null) {
            error_log('Zarinpal invalid JSON response: ' . $response);
            return ['ok' => false, 'data' => null, 'error' => 'پاسخ نامعتبر از درگاه پرداخت.'];
        }

        return ['ok' => true, 'data' => $data, 'error' => null];
    }
}
