<?php
/**
 * Coupon validation and discount calculation service.
 * Supports percentage, fixed amount, and free-shipping discounts with min orders,
 * max caps, per-customer usage limits, campaign scoping, and financial analytics.
 */

class CouponService
{
    /**
     * Check whether a coupon code is valid for a given order amount.
     *
     * @param string $code
     * @param float $subtotal
     * @param string|null $phone
     * @param int|null $shippingCost
     * @param array $cartItems
     * @return array ['ok' => bool, 'coupon' => array|null, 'discount' => int, 'message' => string]
     */
    public static function validate(string $code, float $subtotal, ?string $phone = null, ?int $shippingCost = null, array $cartItems = []): array
    {
        $code = strtoupper(trim($code));
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
        $limit = $coupon['max_uses'] ?? null;
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

        // Check per-customer usage limit if phone is known
        $cleanPhone = $phone ? trim($phone) : null;
        $maxPerCustomer = !empty($coupon['max_uses_per_customer']) ? (int)$coupon['max_uses_per_customer'] : 0;
        if ($cleanPhone && $maxPerCustomer > 0) {
            $userCheck = db()->prepare("SELECT COUNT(*) FROM orders WHERE coupon_id = ? AND phone = ? AND status != 'cancelled'");
            $userCheck->execute([$coupon['id'], $cleanPhone]);
            $timesUsed = (int) $userCheck->fetchColumn();
            if ($timesUsed >= $maxPerCustomer) {
                return [
                    'ok' => false,
                    'coupon' => null,
                    'discount' => 0,
                    'message' => 'شما قبلاً به سقف مجاز استفاده از این کد تخفیف رسیده‌اید.'
                ];
            }
        }

        // Check category restriction if category_id is set
        if (!empty($coupon['category_id']) && !empty($cartItems)) {
            $targetCatId = (int)$coupon['category_id'];
            $hasMatchingCategory = false;
            foreach ($cartItems as $item) {
                $itemCatId = (int)($item['product']['category_id'] ?? 0);
                if ($itemCatId === $targetCatId) {
                    $hasMatchingCategory = true;
                    break;
                }
            }
            if (!$hasMatchingCategory) {
                return [
                    'ok' => false,
                    'coupon' => null,
                    'discount' => 0,
                    'message' => 'این کد تخفیف ویژه دسته‌بندی خاصی است که در سبد خرید شما وجود ندارد.'
                ];
            }
        }

        $discount = self::calculateDiscount($coupon, $subtotal, $shippingCost);

        $msg = $coupon['type'] === 'free_shipping'
            ? 'کد تخفیف ارسال رایگان با موفقیت اعمال شد.'
            : 'کد تخفیف با موفقیت اعمال شد.';

        return ['ok' => true, 'coupon' => $coupon, 'discount' => $discount, 'message' => $msg];
    }

    /**
     * Calculate exact discount amount considering discount type and max caps.
     */
    public static function calculateDiscount(array $coupon, float $subtotal, ?int $shippingCost = null): int
    {
        $type = $coupon['type'] ?? 'percent';
        $val = (float) ($coupon['value'] ?? 0);

        if ($type === 'free_shipping') {
            return $shippingCost !== null ? max(0, $shippingCost) : 0;
        }

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
     * Fetch all coupons filtered and sorted for admin table.
     */
    public static function getAllFiltered(array $filters = []): array
    {
        $sql = "SELECT c.*, cat.name AS category_name
                FROM coupons c
                LEFT JOIN categories cat ON cat.id = c.category_id
                WHERE 1=1";
        $params = [];

        // Search query (code or title)
        $q = trim($filters['q'] ?? '');
        if ($q !== '') {
            $sql .= " AND (c.code LIKE ? OR c.title LIKE ?)";
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        // Status filter
        $status = $filters['status'] ?? 'all';
        if ($status === 'active') {
            $sql .= " AND c.is_active = 1 AND (c.expires_at IS NULL OR c.expires_at >= CURRENT_DATE) AND (c.max_uses IS NULL OR c.used_count < c.max_uses)";
        } elseif ($status === 'expired') {
            $sql .= " AND c.expires_at IS NOT NULL AND c.expires_at < CURRENT_DATE";
        } elseif ($status === 'exhausted') {
            $sql .= " AND c.max_uses IS NOT NULL AND c.used_count >= c.max_uses";
        } elseif ($status === 'inactive') {
            $sql .= " AND c.is_active = 0";
        }

        // Sorting
        $sort = $filters['sort'] ?? 'newest';
        $orderBy = match ($sort) {
            'most_used' => 'c.used_count DESC, c.id DESC',
            'highest_value' => 'c.value DESC, c.id DESC',
            'expiring_soon' => 'c.expires_at IS NULL ASC, c.expires_at ASC, c.id DESC',
            default => 'c.id DESC',
        };

        $sql .= " ORDER BY " . $orderBy;

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single coupon by ID.
     */
    public static function getById(int $id): ?array
    {
        $stmt = db()->prepare("SELECT c.*, cat.name AS category_name 
                               FROM coupons c 
                               LEFT JOIN categories cat ON cat.id = c.category_id 
                               WHERE c.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Calculate comprehensive financial ROI and order history for a coupon.
     */
    public static function getCouponPerformance(int $couponId): array
    {
        $pdo = db();
        
        $metricsStmt = $pdo->prepare("
            SELECT 
                COUNT(id) AS total_orders,
                COALESCE(SUM(total), 0) AS gross_revenue,
                COALESCE(SUM(discount_total), 0) AS total_discount_given,
                COALESCE(AVG(total), 0) AS avg_order_value
            FROM orders
            WHERE coupon_id = ? AND status != 'cancelled'
        ");
        $metricsStmt->execute([$couponId]);
        $metrics = $metricsStmt->fetch() ?: [
            'total_orders' => 0,
            'gross_revenue' => 0,
            'total_discount_given' => 0,
            'avg_order_value' => 0,
        ];

        // Fetch recent 12 orders linked to this coupon
        $ordersStmt = $pdo->prepare("
            SELECT id, order_code, customer_name, phone, total, discount_total, payment_status, status, created_at
            FROM orders
            WHERE coupon_id = ?
            ORDER BY id DESC
            LIMIT 12
        ");
        $ordersStmt->execute([$couponId]);
        $orders = $ordersStmt->fetchAll();

        return [
            'metrics' => $metrics,
            'recent_orders' => $orders,
        ];
    }

    /**
     * High-level Bento KPI metrics across all coupons.
     */
    public static function getOverviewStats(): array
    {
        $pdo = db();

        // 1. Active coupons count
        $activeCount = (int) $pdo->query("
            SELECT COUNT(*) FROM coupons 
            WHERE is_active = 1 
              AND (expires_at IS NULL OR expires_at >= CURRENT_DATE) 
              AND (max_uses IS NULL OR used_count < max_uses)
        ")->fetchColumn();

        // 2. Lifetime usage count
        $totalUsages = (int) $pdo->query("SELECT COALESCE(SUM(used_count), 0) FROM coupons")->fetchColumn();

        // 3. Lifetime financial metrics from orders
        $fin = $pdo->query("
            SELECT 
                COALESCE(SUM(discount_total), 0) AS total_discount_given,
                COALESCE(SUM(total), 0) AS gross_revenue
            FROM orders 
            WHERE coupon_id IS NOT NULL AND status != 'cancelled'
        ")->fetch() ?: ['total_discount_given' => 0, 'gross_revenue' => 0];

        return [
            'active_count' => $activeCount,
            'total_usages' => $totalUsages,
            'total_discount_given' => (float)$fin['total_discount_given'],
            'gross_revenue' => (float)$fin['gross_revenue'],
        ];
    }

    /**
     * Save coupon record (insert or update).
     */
    public static function save(array $data, ?int $id = null): array
    {
        $code = strtoupper(trim($data['code'] ?? ''));
        $title = trim($data['title'] ?? '') ?: null;
        $type = in_array($data['type'] ?? '', ['percent', 'fixed', 'free_shipping'], true) ? $data['type'] : 'percent';
        $val = $type === 'free_shipping' ? 0 : (float) ($data['value'] ?? 0);
        $minOrder = max(0, (float) ($data['min_order_amount'] ?? 0));
        $maxCap = !empty($data['max_discount_amount']) ? max(0, (float) $data['max_discount_amount']) : null;
        $maxUses = !empty($data['max_uses']) ? max(1, (int) $data['max_uses']) : null;
        $maxUsesPerCustomer = !empty($data['max_uses_per_customer']) ? max(1, (int) $data['max_uses_per_customer']) : 1;
        $categoryId = !empty($data['category_id']) ? (int) $data['category_id'] : null;
        $expiresAt = !empty($data['expires_at']) ? trim($data['expires_at']) : null;
        $isActive = isset($data['is_active']) ? 1 : 0;

        if ($code === '' || strlen($code) < 3) {
            return ['ok' => false, 'error' => 'کد تخفیف باید حداقل ۳ کاراکتر باشد.'];
        }
        if (!preg_match('/^[A-Z0-9_\-]+$/', $code)) {
            return ['ok' => false, 'error' => 'کد تخفیف فقط می‌تواند شامل حروف انگلیسی، اعداد و خط تیره باشد.'];
        }
        if ($type !== 'free_shipping' && $val <= 0) {
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
            $stmt = $pdo->prepare("
                UPDATE coupons 
                SET code=?, title=?, type=?, value=?, min_order_amount=?, max_discount_amount=?, 
                    max_uses=?, max_uses_per_customer=?, category_id=?, expires_at=?, is_active=? 
                WHERE id=?
            ");
            $stmt->execute([$code, $title, $type, $val, $minOrder, $maxCap, $maxUses, $maxUsesPerCustomer, $categoryId, $expiresAt, $isActive, $id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO coupons 
                (code, title, type, value, min_order_amount, max_discount_amount, max_uses, max_uses_per_customer, category_id, expires_at, is_active) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([$code, $title, $type, $val, $minOrder, $maxCap, $maxUses, $maxUsesPerCustomer, $categoryId, $expiresAt, $isActive]);
        }

        return ['ok' => true, 'error' => null];
    }

    /**
     * Generate an aesthetically clean random promo code.
     */
    public static function generateRandomCode(string $prefix = 'OFF'): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $randStr = '';
        for ($i = 0; $i < 4; $i++) {
            $randStr .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', $prefix)) ?: 'OFF';
        return $prefix . '-' . $randStr;
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
