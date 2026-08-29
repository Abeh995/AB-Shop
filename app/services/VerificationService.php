<?php
/**
 * OTP management service for customer mobile and email verification.
 *
 * Security and abuse-prevention rules:
 * - Each code is valid for 10 minutes.
 * - A maximum of 5 failed attempts is allowed; a new code is required afterward.
 * - At least 60 seconds must pass between code requests to limit SMS abuse and cost.
 * - Codes are stored as SHA-256 hashes in the database, never as plaintext.
 */

class VerificationService
{
    private const CODE_LENGTH = 6;
    private const EXPIRY_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    private const RESEND_COOLDOWN_SECONDS = 60;

    private static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Generate and send a new customer verification code. Requests within 60 seconds of the previous send are rejected.
     * @return array ['ok'=>bool, 'error'=>string|null, 'wait_seconds'=>int|null]
     */
    public static function sendCode(int $customerId, string $type, string $target): array
    {
        $stmt = db()->prepare("SELECT created_at FROM verification_codes WHERE customer_id = ? AND type = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$customerId, $type]);
        $last = $stmt->fetchColumn();

        if ($last) {
            $secondsSince = time() - strtotime($last);
            if ($secondsSince < self::RESEND_COOLDOWN_SECONDS) {
                return ['ok' => false, 'error' => 'لطفاً کمی صبر کنید و دوباره تلاش کنید.', 'wait_seconds' => self::RESEND_COOLDOWN_SECONDS - $secondsSince];
            }
        }

        $code = self::generateCode();
        $codeHash = hash('sha256', $code);
        $expiresAt = date('Y-m-d H:i:s', time() + self::EXPIRY_MINUTES * 60);

        db()->prepare("INSERT INTO verification_codes (customer_id, type, code_hash, target, expires_at) VALUES (?,?,?,?,?)")
            ->execute([$customerId, $type, $codeHash, $target, $expiresAt]);

        if ($type === 'phone') {
            $result = FarazSmsService::sendOtp($target, $code);
        } else {
            $result = EmailService::sendOtp($target, $code);
        }

        // If the provider is not configured (error === null indicates development/unconfigured state, not a network failure),
        // the code is still stored in the database so it can be retrieved through support/logs;
        // the controller can then choose the appropriate message based on APP_DEBUG.
        if (!$result['ok'] && $result['error'] !== null) {
            return ['ok' => false, 'error' => $result['error'], 'wait_seconds' => null];
        }

        return ['ok' => true, 'error' => null, 'wait_seconds' => null, 'not_configured' => !$result['ok']];
    }

    /**
     * Verify a code submitted by the customer.
     * @return array ['ok'=>bool, 'error'=>string|null]
     */
    public static function verifyCode(int $customerId, string $type, string $inputCode): array
    {
        $stmt = db()->prepare("SELECT * FROM verification_codes
                                WHERE customer_id = ? AND type = ? AND consumed_at IS NULL
                                ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$customerId, $type]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['ok' => false, 'error' => 'کدی برای بررسی یافت نشد. لطفاً درخواست کد جدید دهید.'];
        }

        if (strtotime($row['expires_at']) < time()) {
            return ['ok' => false, 'error' => 'مهلت این کد تمام شده است. لطفاً کد جدید درخواست دهید.'];
        }

        if ($row['attempts'] >= self::MAX_ATTEMPTS) {
            return ['ok' => false, 'error' => 'تعداد تلاش‌های مجاز تمام شده. لطفاً کد جدید درخواست دهید.'];
        }

        if (!hash_equals($row['code_hash'], hash('sha256', trim($inputCode)))) {
            db()->prepare("UPDATE verification_codes SET attempts = attempts + 1 WHERE id = ?")->execute([$row['id']]);
            return ['ok' => false, 'error' => 'کد وارد‌شده نادرست است.'];
        }

        db()->prepare("UPDATE verification_codes SET consumed_at = NOW() WHERE id = ?")->execute([$row['id']]);

        $column = $type === 'phone' ? 'phone_verified_at' : 'email_verified_at';
        db()->prepare("UPDATE customers SET $column = NOW() WHERE id = ?")->execute([$customerId]);

        return ['ok' => true, 'error' => null];
    }
}
