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

        if ($paymentMethod === 'card_to_card') {
            if (!$receiptFilename && !CardToCardReceiptService::hasPending()) {
                return ['ok' => false, 'error' => 'تصویر رسید کارت‌به‌کارت را بارگذاری کنید.', 'order_id' => null, 'order_code' => null, 'total' => null];
            }
        }

        $finalReceipt = null;
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

            if ($paymentMethod === 'card_to_card' && empty($receiptFilename)) {
                $finalReceipt = CardToCardReceiptService::finalizePending($orderId, $orderCode);
                if (!$finalReceipt) {
                    throw new Exception('ذخیره نهایی تصویر رسید انجام نشد.');
                }
                $receiptFilename = $finalReceipt;
                $pdo->prepare("UPDATE orders SET card_to_card_receipt = ? WHERE id = ?")->execute([$receiptFilename, $orderId]);
            }

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
            if ($paymentMethod === 'card_to_card' && !empty($finalReceipt)) {
                CardToCardReceiptService::restorePending($finalReceipt);
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

    /**
     * Check if the tracking_code column exists in orders table (defensive for pending migrations)
     */
    public static function hasTrackingCodeColumn(): bool
    {
        static $has = null;
        if ($has === null) {
            try {
                db()->query("SELECT tracking_code FROM orders LIMIT 0");
                $has = true;
            } catch (Throwable $e) {
                $has = false;
            }
        }
        return $has;
    }

    /**
     * Get paginated admin orders with items and filters
     */
    public static function getAdminOrders(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        // Status Filter
        if (!empty($filters['status']) && in_array($filters['status'], ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'], true)) {
            $where[] = 'orders.status = ?';
            $params[] = $filters['status'];
        }

        // Payment Method Filter
        if (!empty($filters['payment_method']) && in_array($filters['payment_method'], ['zarinpal', 'card_to_card'], true)) {
            $where[] = 'orders.payment_method = ?';
            $params[] = $filters['payment_method'];
        }

        // Payment Status Filter
        if (!empty($filters['payment_status']) && in_array($filters['payment_status'], ['paid', 'unpaid', 'failed'], true)) {
            $where[] = 'orders.payment_status = ?';
            $params[] = $filters['payment_status'];
        }

        // Date Range Filter
        if (!empty($filters['date_range'])) {
            switch ($filters['date_range']) {
                case 'today':
                    $where[] = 'DATE(orders.created_at) = CURDATE()';
                    break;
                case '3days':
                    $where[] = 'orders.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)';
                    break;
                case 'this_week':
                    $where[] = 'orders.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'this_month':
                    $where[] = 'orders.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
            }
        }

        // Search Query
        if (!empty($filters['search'])) {
            $q = '%' . trim($filters['search']) . '%';
            if (self::hasTrackingCodeColumn()) {
                $where[] = '(orders.order_code LIKE ? OR orders.customer_name LIKE ? OR orders.phone LIKE ? OR orders.city LIKE ? OR orders.province LIKE ? OR orders.tracking_code LIKE ?)';
                $params[] = $q; $params[] = $q; $params[] = $q; $params[] = $q; $params[] = $q; $params[] = $q;
            } else {
                $where[] = '(orders.order_code LIKE ? OR orders.customer_name LIKE ? OR orders.phone LIKE ? OR orders.city LIKE ? OR orders.province LIKE ?)';
                $params[] = $q; $params[] = $q; $params[] = $q; $params[] = $q; $params[] = $q;
            }
        }

        $whereSql = implode(' AND ', $where);

        // Count Total
        $countStmt = db()->prepare("SELECT COUNT(*) FROM orders WHERE $whereSql");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Fetch Orders
        $selectCols = "orders.*";
        $stmt = db()->prepare("SELECT $selectCols FROM orders WHERE $whereSql ORDER BY orders.created_at DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        // Batch load order items and gift items
        if (!empty($orders)) {
            $orderIds = array_column($orders, 'id');
            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

            // Load items
            $itemStmt = db()->prepare("SELECT order_id, product_name, variant_label, quantity, unit_price, line_total
                                       FROM order_items WHERE order_id IN ($placeholders)
                                       ORDER BY id ASC");
            $itemStmt->execute($orderIds);
            $itemsMap = [];
            foreach ($itemStmt->fetchAll() as $it) {
                $itemsMap[$it['order_id']][] = $it;
            }

            // Load gift items
            $giftMap = [];
            try {
                $giftStmt = db()->prepare("SELECT order_id, name, quantity, role FROM order_gift_items WHERE order_id IN ($placeholders)");
                $giftStmt->execute($orderIds);
                foreach ($giftStmt->fetchAll() as $g) {
                    $giftMap[$g['order_id']][] = $g;
                }
            } catch (Throwable $e) {}

            foreach ($orders as &$ord) {
                $ord['items'] = $itemsMap[$ord['id']] ?? [];
                $ord['gifts'] = $giftMap[$ord['id']] ?? [];
                $ord['has_gift'] = !empty($ord['gifts']);
                if (!isset($ord['tracking_code'])) {
                    $ord['tracking_code'] = null;
                }
            }
            unset($ord);
        }

        $pages = (int) ceil($total / $perPage);

        return [
            'orders'   => $orders,
            'total'    => $total,
            'pages'    => max(1, $pages),
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * Get KPI summary metrics for admin orders dashboard
     */
    public static function getAdminOrderStats(): array
    {
        $pdo = db();
        $stats = [
            'pending_count'    => 0,
            'total_sales'      => 0,
            'total_orders'     => 0,
            'processing_count' => 0,
        ];

        try {
            $stats['pending_count'] = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
            $stats['total_orders'] = (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
            $stats['processing_count'] = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'processing'")->fetchColumn();
            $stats['total_sales'] = (int) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid' OR status NOT IN ('cancelled')")->fetchColumn();
        } catch (Throwable $e) {
            error_log('Failed to fetch admin order stats: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Get order count per status tab
     */
    public static function getAdminStatusCounts(): array
    {
        $counts = [
            'all'        => 0,
            'pending'    => 0,
            'confirmed'  => 0,
            'processing' => 0,
            'shipped'    => 0,
            'delivered'  => 0,
            'cancelled'  => 0,
        ];

        try {
            $stmt = db()->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
            $rows = $stmt->fetchAll();
            $all = 0;
            foreach ($rows as $r) {
                $st = $r['status'];
                $cnt = (int) $r['cnt'];
                $counts[$st] = $cnt;
                $all += $cnt;
            }
            $counts['all'] = $all;
        } catch (Throwable $e) {
            error_log('Failed to fetch status counts: ' . $e->getMessage());
        }

        return $counts;
    }

    /**
     * Update an order's status and optionally tracking code
     */
    public static function updateOrderStatus(int $orderId, string $newStatus, ?string $trackingCode = null): array
    {
        $allowed = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $allowed, true)) {
            return ['ok' => false, 'error' => 'وضعیت انتخاب‌شده معتبر نیست.'];
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            return ['ok' => false, 'error' => 'سفارش مورد نظر یافت نشد.'];
        }

        try {
            $statusLabels = [
                'pending' => 'در انتظار بررسی', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش',
                'shipped' => 'ارسال شده', 'delivered' => 'تحویل داده شده', 'cancelled' => 'لغو شده',
            ];

            if ($trackingCode !== null && self::hasTrackingCodeColumn()) {
                $upd = $pdo->prepare("UPDATE orders SET status = ?, tracking_code = ? WHERE id = ?");
                $upd->execute([$newStatus, trim($trackingCode) ?: null, $orderId]);
            } else {
                $upd = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $upd->execute([$newStatus, $orderId]);
            }

            if ($newStatus !== $order['status']) {
                SmsService::notifyOrderStatusChanged($order['phone'], $order['order_code'], $statusLabels[$newStatus] ?? $newStatus);
            }

            return ['ok' => true, 'error' => null];
        } catch (Throwable $e) {
            error_log('Failed to update order status: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'خطا در ثبت وضعیت سفارش.'];
        }
    }

    /**
     * Update postal tracking code
     */
    public static function updateTrackingCode(int $orderId, string $trackingCode): array
    {
        if (!self::hasTrackingCodeColumn()) {
            return ['ok' => false, 'error' => 'ستون کد رهگیری پستی در پایگاه‌داده هنوز اعمال نشده است.'];
        }

        try {
            $stmt = db()->prepare("UPDATE orders SET tracking_code = ? WHERE id = ?");
            $stmt->execute([trim($trackingCode) ?: null, $orderId]);
            return ['ok' => true, 'error' => null];
        } catch (Throwable $e) {
            error_log('Failed to update tracking code: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'خطا در ثبت کد رهگیری پستی.'];
        }
    }

    /**
     * Verify or reject card-to-card receipt for an order
     */
    public static function verifyCardToCardReceipt(int $orderId, bool $approved): array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            return ['ok' => false, 'error' => 'سفارش مورد نظر یافت نشد.'];
        }

        $newPaymentStatus = $approved ? 'paid' : 'failed';
        $newOrderStatus = ($approved && $order['status'] === 'pending') ? 'processing' : $order['status'];

        try {
            $upd = $pdo->prepare("UPDATE orders SET payment_status = ?, status = ? WHERE id = ?");
            $upd->execute([$newPaymentStatus, $newOrderStatus, $orderId]);

            if (!empty($order['phone'])) {
                SmsService::notifyPaymentStatusChanged($order['phone'], $order['order_code'], $newPaymentStatus);
            }

            return ['ok' => true, 'error' => null];
        } catch (Throwable $e) {
            error_log('Failed to verify c2c receipt: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'خطا در ثبت وضعیت فیش کارت‌به‌کارت.'];
        }
    }

    /**
     * Update order payment status
     */
    public static function updatePaymentStatus(int $orderId, string $newPaymentStatus): array
    {
        $allowed = ['unpaid', 'paid', 'failed'];
        if (!in_array($newPaymentStatus, $allowed, true)) {
            return ['ok' => false, 'error' => 'وضعیت پرداخت نامعتبر است.'];
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            return ['ok' => false, 'error' => 'سفارش یافت نشد.'];
        }

        if ($newPaymentStatus === 'paid' && $order['payment_method'] === 'card_to_card' && empty($order['card_to_card_receipt'])) {
            return ['ok' => false, 'error' => 'برای تایید پرداخت کارت‌به‌کارت ابتدا باید رسید موجود باشد.'];
        }

        try {
            $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?")->execute([$newPaymentStatus, $orderId]);
            if ($newPaymentStatus !== $order['payment_status'] && in_array($newPaymentStatus, ['paid', 'failed'], true)) {
                if (!empty($order['phone'])) {
                    SmsService::notifyPaymentStatusChanged($order['phone'], $order['order_code'], $newPaymentStatus);
                }
            }
            return ['ok' => true, 'error' => null];
        } catch (Throwable $e) {
            error_log('Failed to update payment status: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'خطا در ثبت وضعیت پرداخت.'];
        }
    }

    /**
     * Transactional bulk status update
     */
    public static function bulkUpdateStatus(array $orderIds, string $newStatus): array
    {
        $allowed = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $allowed, true)) {
            return ['ok' => false, 'error' => 'وضعیت نامعتبر است.'];
        }

        $orderIds = array_filter(array_map('intval', $orderIds));
        if (empty($orderIds)) {
            return ['ok' => false, 'error' => 'سفارشی انتخاب نشده است.'];
        }

        $pdo = db();
        try {
            $pdo->beginTransaction();
            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id IN ($placeholders)");
            $params = array_merge([$newStatus], $orderIds);
            $stmt->execute($params);
            $pdo->commit();
            return ['ok' => true, 'error' => null, 'count' => count($orderIds)];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Bulk status update failed: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'خطا در تغییر وضعیت گروهی سفارشات.'];
        }
    }

    /**
     * Delete order (SuperAdmin only)
     */
    public static function deleteOrder(int $orderId): array
    {
        if (!isSuperAdmin()) {
            return ['ok' => false, 'error' => 'تنها مدیر کل مجاز به حذف سفارش است.'];
        }

        try {
            $stmt = db()->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            return ['ok' => true, 'error' => null];
        } catch (Throwable $e) {
            error_log('Failed to delete order: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'خطا در حذف سفارش.'];
        }
    }

    /**
     * Get count of pending orders for badge display.
     */
    public static function getPendingCount(): int
    {
        try {
            return (int) db()->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'payment_pending')")->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Find single order by ID.
     */
    public static function getOrder(int $orderId): ?array
    {
        $stmt = db()->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    /**
     * Get order items enriched with product gallery images for responsive view & lightbox.
     *
     * @param int $orderId
     * @return array
     */
    public static function getOrderItemsWithGallery(int $orderId): array
    {
        $pdo = db();
        $itemsStmt = $pdo->prepare("
            SELECT oi.*, p.image AS product_main_image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll();

        $productIds = array_filter(array_unique(array_column($items, 'product_id')));
        $productImagesMap = [];
        if (!empty($productIds)) {
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $galStmt = $pdo->prepare("SELECT product_id, image_path FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
            $galStmt->execute(array_values($productIds));
            while ($row = $galStmt->fetch()) {
                $productImagesMap[$row['product_id']][] = $row['image_path'];
            }
        }

        foreach ($items as &$it) {
            $imgs = !empty($it['product_main_image']) ? [$it['product_main_image']] : [];
            $extra = (!empty($it['product_id']) && isset($productImagesMap[$it['product_id']])) ? $productImagesMap[$it['product_id']] : [];
            $rawImgs = array_values(array_unique(array_merge($imgs, $extra)));
            $fullUrls = [];
            foreach ($rawImgs as $r) {
                $fullUrls[] = UPLOAD_URL . $r;
            }
            $it['images'] = !empty($fullUrls) ? $fullUrls : ['/assets/img/placeholder-sock.svg'];
            $it['thumb_url'] = !empty($fullUrls) ? $fullUrls[0] : '/assets/img/placeholder-sock.svg';
        }
        unset($it);

        return $items;
    }
}

