<?php
/**
 * Service that manages verification (OTP) codes for customer phone and
 * email verification.
 *
 * Security / anti-abuse rules:
 * - Each code is valid for 10 minutes.
 * - A maximum of 5 incorrect attempts is allowed; after that, a new code
 *   must be requested.
 * - At least 60 seconds must pass between two code-send requests
 *   (prevents cost/abuse from repeated SMS sends).
 * - The code is stored hashed (sha256) in the database, never as plain text.
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
     * Generate and send a new code for the customer. Rejected if fewer than
     * 60 seconds have passed since the last send.
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

        // If the service isn't configured (error === null means a
        // development/not-configured state, not a real network error), the
        // code is still stored in the database and the customer can get it
        // via support/the log; we let the controller show an appropriate
        // message based on APP_DEBUG.
        if (!$result['ok'] && $result['error'] !== null) {
            return ['ok' => false, 'error' => $result['error'], 'wait_seconds' => null];
        }

        return ['ok' => true, 'error' => null, 'wait_seconds' => null, 'not_configured' => !$result['ok']];
    }

    /**
     * Check the code entered by the customer
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
