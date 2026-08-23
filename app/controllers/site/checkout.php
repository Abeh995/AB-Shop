<?php

$pageTitle = 'تسویه حساب';
$errors = [];

$cart = cartDetails();
if (empty($cart['items'])) {
    redirect('/cart');
}

$appliedCoupon = $_SESSION['coupon'] ?? null;
$discount = 0;
$couponRow = null;
if ($appliedCoupon) {
    $check = CouponService::validate($appliedCoupon['code'], $cart['subtotal']);
    if ($check['ok']) {
        $discount = $check['discount'];
        $couponRow = $check['coupon'];
    } else {
        unset($_SESSION['coupon']);
        $appliedCoupon = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $paymentMethod = ($_POST['payment_method'] ?? 'online') === 'cod' ? 'cod' : 'online';

    if (mb_strlen($name) < 3) $errors[] = 'نام و نام‌خانوادگی را کامل وارد کنید.';
    if (!isValidIranPhone($phone)) $errors[] = 'شماره موبایل معتبر نیست (مثال: 09123456789).';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'ایمیل وارد شده معتبر نیست.';
    if (mb_strlen($province) < 2) $errors[] = 'استان را وارد کنید.';
    if (mb_strlen($city) < 2) $errors[] = 'شهر را وارد کنید.';
    if (mb_strlen($address) < 10) $errors[] = 'آدرس دقیق را کامل‌تر وارد کنید.';

    $cart = cartDetails();
    if (empty($cart['items'])) {
        $errors[] = 'سبد خرید شما خالی است.';
    }
    foreach ($cart['items'] as $item) {
        if ($item['qty'] > $item['stock']) {
            $errors[] = 'موجودی «' . $item['product']['name'] . '» کافی نیست.';
        }
    }

    $discount = 0;
    $couponRow = null;
    if ($appliedCoupon) {
        $check = CouponService::validate($appliedCoupon['code'], $cart['subtotal']);
        if ($check['ok']) {
            $discount = $check['discount'];
            $couponRow = $check['coupon'];
        } else {
            unset($_SESSION['coupon']);
            $appliedCoupon = null;
        }
    }

    if (empty($errors)) {
        $pdo = db();
        try {
            $pdo->beginTransaction();

            $orderCode = generateOrderCode();
            $subtotal = $cart['subtotal'];
            $shippingCost = 0; 
            $total = max(0, $subtotal - $discount + $shippingCost);

            $stmt = $pdo->prepare("INSERT INTO orders
                (order_code, customer_name, phone, email, province, city, address, postal_code, notes,
                 subtotal, discount_total, shipping_cost, total, coupon_code, coupon_id, status, payment_status)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending', 'unpaid')");
            $stmt->execute([
                $orderCode, $name, $phone, $email ?: null, $province, $city, $address, $postalCode ?: null, $notes ?: null,
                $subtotal, $discount, $shippingCost, $total,
                $couponRow ? $couponRow['code'] : null, $couponRow ? $couponRow['id'] : null,
            ]);
            $orderId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO order_items
                (order_id, product_id, variant_id, product_name, variant_label, unit_price, quantity, line_total)
                VALUES (?,?,?,?,?,?,?,?)");

            foreach ($cart['items'] as $item) {
                $variantLabel = $item['variant'] ? trim(($item['variant']['size'] ?? '') . ' ' . ($item['variant']['color'] ?? '')) : null;
                $itemStmt->execute([
                    $orderId, $item['product']['id'], $item['variant']['id'] ?? null,
                    $item['product']['name'], $variantLabel ?: null,
                    $item['unit_price'], $item['qty'], $item['line_total'],
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

            $pdo->commit();
            cartClear();
            unset($_SESSION['coupon']);

            if ($paymentMethod === 'cod') {
                redirect('/order/success/' . $orderCode);
            }

            // ---------- ZarinPal Payment ----------
            $callbackUrl = rtrim(SITE_URL, '/') . '/payment/zarinpal_callback.php';
            $payResult = ZarinpalService::request((int) $total, 'پرداخت سفارش ' . $orderCode, $callbackUrl, $phone, $email ?: null);

            if ($payResult['ok']) {
                db()->prepare("UPDATE orders SET payment_authority = ? WHERE id = ?")->execute([$payResult['authority'], $orderId]);
                redirect($payResult['pay_url']);
            } else {
                redirect('/order/failed/' . $orderCode . '?err=' . urlencode($payResult['error']));
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Order creation failed: ' . $e->getMessage());
            $errors[] = 'خطا در ثبت سفارش. لطفاً دوباره تلاش کنید. (' . (APP_DEBUG ? $e->getMessage() : 'خطای سرور') . ')';
        }
    }
}

renderView('site/checkout', compact('pageTitle', 'errors', 'cart', 'appliedCoupon', 'discount'));
