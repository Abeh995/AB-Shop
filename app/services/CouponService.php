<?php
/**
 * Coupon validation and discount calculation service.
 * Supports percentage and fixed amount discounts with min orders, max caps,
 * usage limits, and expiration windows.
 */

class CouponService
{
    /**
     * Check whether a coupon code is valid for a given order amount.
     *
     * @return array ['ok' => bool, 'coupon' => array|null, 'discount' => int, 'message' => string]
     */
    public static function validate(string $code, float $subtotal): array
    {
        $code = trim($code);
        if ($code === '') {
            return ['ok' => false, 'coupon' => null, 'discount' => 0, 'message' => 'کد تخفیف را وارد کنید.'];
        }

        $stmt = db()->prepare("SELECT * FROM coupons WHERE code = ? LIMIT 1");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            return ['ok' => false, 'coupon' => null, 'discount' => 0, 'message' => 'کد تخفیف نامعتبر است.'];
        }
        if (!$coupon['is_active']) {
            return ['ok' => false, 'coupon' => null, 'discount' => 0, 'message' => 'این کد تخفیف غیرفعال است.'];
        }
        if (!empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < strtotime('today')) {
            return ['ok' => false, 'coupon' => null, 'discount' => 0, 'message' => 'مهلت استفاده از این کد تخفیف تمام شده است.'];
        }
        $limit = $coupon['max_uses'] ?? ($coupon['usage_limit'] ?? null);
        if ($limit !== null && (int) $coupon['used_count'] >= (int) $limit) {
            return ['ok' => false, 'coupon' => null, 'discount' => 0, 'message' => 'ظرفیت استفاده از این کد تخفیف تکمیل شده است.'];
        }
        $minAmount = (float) ($coupon['min_order_amount'] ?? 0);
        if ($minAmount > 0 && $subtotal < $minAmount) {
            return [
                'ok' => false,
                'coupon' => null,
                'discount' => 0,
                'message' => 'حداقل مبلغ سفارش برای این کد تخفیف ' . formatPrice($minAmount) . ' است.'
            ];
        }

        $discount = self::calculateDiscount($coupon, $subtotal);

        return ['ok' => true, 'coupon' => $coupon, 'discount' => $discount, 'message' => 'کد تخفیف با موفقیت اعمال شد.'];
    }

    /**
     * Calculate exact discount amount considering discount type and max caps.
     */
    public static function calculateDiscount(array $coupon, float $subtotal): int
    {
        $type = $coupon['type'] ?? ($coupon['discount_type'] ?? 'percent');
        $val = (float) ($coupon['value'] ?? ($coupon['discount_value'] ?? 0));

        if ($type === 'percent') {
            $discount = $subtotal * ($val / 100);
            if (!empty($coupon['max_discount_amount']) && (float) $coupon['max_discount_amount'] > 0) {
                $discount = min($discount, (float) $coupon['max_discount_amount']);
            }
        } else {
            $discount = $val;
        }

        // The discount must never exceed the subtotal
        return (int) min(round($discount), $subtotal);
    }

    /**
     * Increment a coupon's usage counter (call this after an order is finalized).
     */
    public static function markUsed(int $couponId): void
    {
        db()->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$couponId]);
    }

    /**
     * Fetch all coupons for admin table.
     */
    public static function getAll(): array
    {
        return db()->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
    }

    /**
     * Fetch single coupon by ID.
     */
    public static function getById(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Save coupon record (insert or update).
     */
    public static function save(array $data, ?int $id = null): array
    {
        $code = strtoupper(trim($data['code'] ?? ''));
        $type = ($data['type'] ?? '') === 'fixed' ? 'fixed' : 'percent';
        $val = (float) ($data['value'] ?? 0);
        $minOrder = max(0, (float) ($data['min_order_amount'] ?? 0));
        $maxCap = !empty($data['max_discount_amount']) ? max(0, (float) $data['max_discount_amount']) : null;
        $maxUses = !empty($data['max_uses']) ? max(1, (int) $data['max_uses']) : null;
        $expiresAt = !empty($data['expires_at']) ? trim($data['expires_at']) : null;
        $isActive = isset($data['is_active']) ? 1 : 0;

        if ($code === '' || strlen($code) < 3) {
            return ['ok' => false, 'error' => 'کد تخفیف باید حداقل ۳ کاراکتر باشد.'];
        }
        if (!preg_match('/^[A-Z0-9_\-]+$/', $code)) {
            return ['ok' => false, 'error' => 'کد تخفیف فقط می‌تواند شامل حروف انگلیسی، اعداد و خط تیره باشد.'];
        }
        if ($val <= 0) {
            return ['ok' => false, 'error' => 'مقدار تخفیف باید بزرگتر از صفر باشد.'];
        }
        if ($type === 'percent' && $val > 100) {
            return ['ok' => false, 'error' => 'درصد تخفیف نمی‌تواند بیش از ۱۰۰ درصد باشد.'];
        }

        // Check uniqueness
        $pdo = db();
        if ($id && $id > 0) {
            $check = $pdo->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
            $check->execute([$code, $id]);
        } else {
            $check = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
            $check->execute([$code]);
        }
        if ($check->fetch()) {
            return ['ok' => false, 'error' => 'این کد تخفیف قبلاً تعریف شده است.'];
        }

        if ($id && $id > 0) {
            $stmt = $pdo->prepare("UPDATE coupons SET code=?, type=?, value=?, min_order_amount=?, max_discount_amount=?, max_uses=?, expires_at=?, is_active=? WHERE id=?");
            $stmt->execute([$code, $type, $val, $minOrder, $maxCap, $maxUses, $expiresAt, $isActive, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO coupons (code, type, value, min_order_amount, max_discount_amount, max_uses, expires_at, is_active) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$code, $type, $val, $minOrder, $maxCap, $maxUses, $expiresAt, $isActive]);
        }

        return ['ok' => true, 'error' => null];
    }

    /**
     * Toggle coupon active state.
     */
    public static function toggle(int $id): array
    {
        $c = self::getById($id);
        if (!$c) return ['ok' => false, 'error' => 'کد تخفیف یافت نشد.'];
        $newStatus = $c['is_active'] ? 0 : 1;
        db()->prepare("UPDATE coupons SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);
        return ['ok' => true, 'error' => null];
    }

    /**
     * Delete coupon.
     */
    public static function delete(int $id): array
    {
        db()->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
        return ['ok' => true, 'error' => null];
    }
}
