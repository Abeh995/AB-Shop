<?php
/**
 * Order creation service — owns the final checkout validation, transaction,
 * inventory decrement, and financial snapshots for customer orders.
 */

class OrderService
{
    /**
     * Create an order from already-collected checkout fields.
     * All cart, coupon, add-on, shipping, stock, and total values are re-read
     * server-side before the transaction is committed.
     *
     * @return array{ok:bool,error:string|null,order_id:int|null,order_code:string|null,total:int|null}
     */
    /**
     * Validate customer-facing checkout fields before an order is created.
     * @return string[]
     */
    public static function validateCheckoutData(array $data): array
    {
        $errors = [];
        if (mb_strlen($data['customer_name'] ?? '') < 3) $errors[] = 'نام و نام‌خانوادگی را کامل وارد کنید.';
        if (!isValidIranPhone($data['phone'] ?? '')) $errors[] = 'شماره موبایل معتبر نیست (مثال: 09123456789).';
        if (($data['email'] ?? '') !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'ایمیل وارد شده معتبر نیست.';
        if (mb_strlen($data['province'] ?? '') < 2) $errors[] = 'استان را وارد کنید.';
        if (mb_strlen($data['city'] ?? '') < 2) $errors[] = 'شهر را وارد کنید.';
        if (mb_strlen($data['address'] ?? '') < 10) $errors[] = 'آدرس دقیق را کامل‌تر وارد کنید.';
        return $errors;
    }

    public static function createFromCheckout(array $data, string $paymentMethod, ?string $receiptFilename = null): array
    {
        if (!isCustomerLoggedIn()) {
            return ['ok' => false, 'error' => 'برای ثبت سفارش باید وارد حساب کاربری شوید.', 'order_id' => null, 'order_code' => null, 'total' => null];
        }
        $verifiedStmt = db()->prepare("SELECT phone_verified_at FROM customers WHERE id = ? LIMIT 1");
        $verifiedStmt->execute([(int) $_SESSION['customer_id']]);
        if (!$verifiedStmt->fetchColumn()) {
            return ['ok' => false, 'error' => 'برای ثبت سفارش ابتدا شماره موبایل خود را احراز کنید.', 'order_id' => null, 'order_code' => null, 'total' => null];
        }

        $allowedMethods = ['zarinpal', 'card_to_card'];
        if (!in_array($paymentMethod, $allowedMethods, true)) {
            return ['ok' => false, 'error' => 'روش پرداخت انتخاب‌شده معتبر نیست.', 'order_id' => null, 'order_code' => null, 'total' => null];
        }

        $cart = cartDetails();
        if (empty($cart['items'])) {
            return ['ok' => false, 'error' => 'سبد خرید شما خالی است.', 'order_id' => null, 'order_code' => null, 'total' => null];
        }

        foreach ($cart['items'] as $item) {
            if ($item['qty'] > $item['stock']) {
                return ['ok' => false, 'error' => 'موجودی «' . $item['product']['name'] . '» کافی نیست.', 'order_id' => null, 'order_code' => null, 'total' => null];
            }
        }

        $couponRow = null;
        $discount = 0;
        $appliedCoupon = $_SESSION['coupon'] ?? null;
        if ($appliedCoupon) {
            $check = CouponService::validate($appliedCoupon['code'], $cart['subtotal']);
            if ($check['ok']) {
                $discount = $check['discount'];
                $couponRow = $check['coupon'];
            } else {
                unset($_SESSION['coupon']);
            }
        }

        $postOrderResult = validatePostOrderSelection($_SESSION['post_order_selection'] ?? []);
        $giftItemsTotal = $postOrderResult['total'];
        $shipping = calculateShippingCost($data['province'], $cart['subtotal']);
        $shippingCost = $shipping['cost'];
        $total = (int) max(0, $cart['subtotal'] - $discount + $shippingCost + $giftItemsTotal);

        if ($paymentMethod === 'card_to_card' && !$receiptFilename) {
            return ['ok' => false, 'error' => 'تصویر رسید کارت‌به‌کارت را بارگذاری کنید.', 'order_id' => null, 'order_code' => null, 'total' => null];
        }

        $pdo = db();
        try {
            $pdo->beginTransaction();

            $orderCode = generateOrderCode();
            $customerId = isCustomerLoggedIn() ? (int) $_SESSION['customer_id'] : null;

            $stmt = $pdo->prepare("INSERT INTO orders
                (customer_id, order_code, customer_name, phone, email, province, city, address, postal_code, notes,
                 subtotal, discount_total, shipping_cost, shipping_method_name, shipping_actual_cost, gift_items_total, total,
                 coupon_code, coupon_id, payment_method, card_to_card_receipt, card_to_card_submitted_at, status, payment_status)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),'pending','unpaid')");
            $stmt->execute([
                $customerId, $orderCode, $data['customer_name'], $data['phone'], $data['email'] ?: null,
                $data['province'], $data['city'], $data['address'], $data['postal_code'] ?: null, $data['notes'] ?: null,
                $cart['subtotal'], $discount, $shippingCost, $shipping['method_name'], $shipping['actual_cost'], $giftItemsTotal, $total,
                $couponRow ? $couponRow['code'] : null, $couponRow ? $couponRow['id'] : null,
                $paymentMethod, $receiptFilename,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO order_items
                (order_id, product_id, variant_id, product_name, variant_label, unit_price, unit_cost_price, quantity, line_total)
                VALUES (?,?,?,?,?,?,?,?,?)");

            foreach ($cart['items'] as $item) {
                $variantLabel = $item['variant'] ? trim(($item['variant']['size'] ?? '') . ' ' . ($item['variant']['color'] ?? '')) : null;
                $unitCostPrice = $item['variant']['cost_price'] ?? $item['product']['cost_price'] ?? null;
                $itemStmt->execute([
                    $orderId, $item['product']['id'], $item['variant']['id'] ?? null,
                    $item['product']['name'], $variantLabel ?: null,
                    $item['unit_price'], $unitCostPrice, $item['qty'], $item['line_total'],
                ]);

                if (!empty($item['variant'])) {
                    $dec = $pdo->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ? AND stock >= ?");
                    $dec->execute([$item['qty'], $item['variant']['id'], $item['qty']]);
                } else {
                    $dec = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                    $dec->execute([$item['qty'], $item['product']['id'], $item['qty']]);
                }
                if ($dec->rowCount() === 0) {
                    throw new Exception('موجودی کافی نیست: ' . $item['product']['name']);
                }
            }

            if ($couponRow) {
                CouponService::markUsed($couponRow['id']);
            }

            if ($postOrderResult['lines']) {
                attachPostOrderLines($pdo, $orderId, $postOrderResult['lines']);
            }

            $pdo->commit();

            cartClear();
            unset($_SESSION['coupon'], $_SESSION['post_order_selection']);

            return ['ok' => true, 'error' => null, 'order_id' => $orderId, 'order_code' => $orderCode, 'total' => $total];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Order creation failed: ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => 'خطا در ثبت سفارش. لطفاً دوباره تلاش کنید.' . (APP_DEBUG ? ' (' . $e->getMessage() . ')' : ''),
                'order_id' => null,
                'order_code' => null,
                'total' => null,
            ];
        }
    }
}
