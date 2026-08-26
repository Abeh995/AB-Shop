<?php
/**
 * Discount coupon validation and calculation service.
 */

class CouponService
{
    /**
     * Validate a coupon for a specific order amount.
     * @return array ['ok'=>bool, 'coupon'=>array|null, 'discount'=>int, 'message'=>string]
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
        if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < strtotime('today')) {
            return ['ok' => false, 'coupon' => null, 'discount' => 0, 'message' => 'مهلت استفاده از این کد تخفیف تمام شده است.'];
        }
        if ($coupon['max_uses'] !== null && $coupon['used_count'] >= $coupon['max_uses']) {
            return ['ok' => false, 'coupon' => null, 'discount' => 0, 'message' => 'ظرفیت استفاده از این کد تخفیف تکمیل شده است.'];
        }
        if ($subtotal < $coupon['min_order_amount']) {
            return ['ok' => false, 'coupon' => null, 'discount' => 0,
                     'message' => 'حداقل مبلغ سفارش برای این کد تخفیف ' . formatPrice($coupon['min_order_amount']) . ' است.'];
        }

        $discount = self::calculateDiscount($coupon, $subtotal);

        return ['ok' => true, 'coupon' => $coupon, 'discount' => $discount, 'message' => 'کد تخفیف با موفقیت اعمال شد.'];
    }

    public static function calculateDiscount(array $coupon, float $subtotal): int
    {
        if ($coupon['type'] === 'percent') {
            $discount = $subtotal * ((float)$coupon['value'] / 100);
        } else {
            $discount = (float) $coupon['value'];
        }
        // The discount must never exceed the order amount.
        return (int) min($discount, $subtotal);
    }

    /**
     * Increment the coupon usage counter; call this only after the order is committed.
     */
    public static function markUsed(int $couponId): void
    {
        db()->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$couponId]);
    }
}
