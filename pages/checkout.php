<?php
$pageTitle = 'تسویه حساب';
$errors = [];

$cart = cartDetails();
if (empty($cart['items'])) {
    redirect('/cart');
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

    if (empty($errors)) {
        $pdo = db();
        try {
            $pdo->beginTransaction();

            $orderCode = generateOrderCode();
            $subtotal = $cart['subtotal'];
            $shippingCost = 0;
            $total = $subtotal + $shippingCost;

            $stmt = $pdo->prepare("INSERT INTO orders
                (order_code, customer_name, phone, email, province, city, address, postal_code, notes, subtotal, discount_total, shipping_cost, total, status)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending')");
            $stmt->execute([$orderCode, $name, $phone, $email ?: null, $province, $city, $address, $postalCode ?: null, $notes ?: null, $subtotal, 0, $shippingCost, $total]);
            $orderId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO order_items
                (order_id, product_id, variant_id, product_name, variant_label, unit_price, quantity, line_total)
                VALUES (?,?,?,?,?,?,?,?)");

            foreach ($cart['items'] as $item) {
                $variantLabel = $item['variant'] ? trim(($item['variant']['size'] ?? '') . ' ' . ($item['variant']['color'] ?? '')) : null;
                $itemStmt->execute([
                    $orderId,
                    $item['product']['id'],
                    $item['variant']['id'] ?? null,
                    $item['product']['name'],
                    $variantLabel ?: null,
                    $item['unit_price'],
                    $item['qty'],
                    $item['line_total'],
                ]);

                // کسر موجودی
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

            $pdo->commit();
            cartClear();
            redirect('/order/success/' . $orderCode);

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Order creation failed: ' . $e->getMessage());
            $errors[] = 'خطا در ثبت سفارش. لطفاً دوباره تلاش کنید. (' . (APP_DEBUG ? $e->getMessage() : 'خطای سرور') . ')';
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="container section">
    <h1 style="margin-bottom:24px;">تکمیل خرید</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="checkout-layout">
        <form method="post" action="/checkout">
            <?= csrfField() ?>

            <div class="form-group">
                <label>نام و نام‌خانوادگی</label>
                <input class="form-control" type="text" name="customer_name" value="<?= e($_POST['customer_name'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>شماره موبایل</label>
                    <input class="form-control" type="tel" name="phone" dir="ltr" placeholder="09123456789" value="<?= e($_POST['phone'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>ایمیل (اختیاری)</label>
                    <input class="form-control" type="email" name="email" dir="ltr" value="<?= e($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>استان</label>
                    <input class="form-control" type="text" name="province" value="<?= e($_POST['province'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>شهر</label>
                    <input class="form-control" type="text" name="city" value="<?= e($_POST['city'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>آدرس کامل</label>
                <textarea class="form-control" name="address" required><?= e($_POST['address'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>کد پستی (اختیاری)</label>
                <input class="form-control" type="text" dir="ltr" name="postal_code" value="<?= e($_POST['postal_code'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>توضیحات سفارش (اختیاری)</label>
                <textarea class="form-control" name="notes"><?= e($_POST['notes'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">ثبت نهایی سفارش</button>
        </form>

        <div class="order-review">
            <h3 style="margin-bottom:14px;">خلاصه سفارش</h3>
            <?php foreach ($cart['items'] as $item): ?>
                <div class="item-line">
                    <span><?= e($item['product']['name']) ?> × <?= toPersianDigits((string)$item['qty']) ?></span>
                    <span><?= formatPrice($item['line_total']) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="row total-row" style="margin-top:14px;">
                <span>مبلغ قابل پرداخت</span>
                <span><?= formatPrice($cart['subtotal']) ?></span>
            </div>
            <p style="font-size:.82rem; color:var(--color-muted); margin-top:14px;">
                در حال حاضر سفارش شما به‌صورت پرداخت در محل / هماهنگی تلفنی ثبت می‌شود. اطلاعات تماس بعد از ثبت سفارش با شما هماهنگ می‌شود.
            </p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
