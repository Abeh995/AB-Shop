<?php
declare(strict_types=1);

/**
 * CustomerService — Encapsulated customer profile management, order queries,
 * and account data retrieval for the storefront.
 */
class CustomerService
{
    /**
     * Update customer profile info and handle email verification reset if email changed.
     *
     * @param int $customerId
     * @param string $fullName
     * @param string $email
     * @return array ['ok' => bool, 'error' => ?string, 'email_changed' => bool]
     */
    public static function updateProfile(int $customerId, string $fullName, string $email): array
    {
        $fullName = trim($fullName);
        $email = trim($email);

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'ok' => false,
                'error' => 'ایمیل وارد شده معتبر نیست.',
                'email_changed' => false,
            ];
        }

        $stmt = db()->prepare("SELECT email FROM customers WHERE id = ?");
        $stmt->execute([$customerId]);
        $current = $stmt->fetch();
        if (!$current) {
            return [
                'ok' => false,
                'error' => 'حساب کاربری یافت نشد.',
                'email_changed' => false,
            ];
        }

        $emailChanged = ($email !== ($current['email'] ?? ''));
        if ($emailChanged) {
            db()->prepare("UPDATE customers SET full_name = ?, email = ?, email_verified_at = NULL WHERE id = ?")
                ->execute([$fullName ?: null, $email ?: null, $customerId]);
        } else {
            db()->prepare("UPDATE customers SET full_name = ? WHERE id = ?")
                ->execute([$fullName ?: null, $customerId]);
        }

        return [
            'ok' => true,
            'error' => null,
            'email_changed' => $emailChanged,
        ];
    }

    /**
     * Fetch fresh customer row by ID.
     *
     * @param int $customerId
     * @return array|null
     */
    public static function getById(int $customerId): ?array
    {
        $stmt = db()->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch();
        return $customer ?: null;
    }

    /**
     * Fetch order history for a customer ordered by creation date descending.
     *
     * @param int $customerId
     * @return array
     */
    public static function getOrders(int $customerId): array
    {
        $stmt = db()->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }
}
