<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section">
    <h1 style="margin-bottom:20px;">حساب کاربری</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <div class="cart-layout">
        <div>
            <!-- ---------- کارت پروفایل ---------- -->
            <div class="admin-card">
                <h3 style="margin-bottom:16px;">اطلاعات من</h3>
                <form method="post">
                    <?= csrfField() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>نام و نام‌خانوادگی</label>
                            <input class="form-control" type="text" name="full_name" value="<?= e($customer['full_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>شماره موبایل</label>
                            <input class="form-control" type="text" dir="ltr" value="<?= e($customer['phone']) ?>" disabled>
                            <span style="color:var(--color-success); font-size:.78rem;">✓ تایید شده</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>ایمیل</label>
                        <input class="form-control" type="email" name="email" dir="ltr" value="<?= e($customer['email'] ?? '') ?>" placeholder="ایمیل خود را وارد کنید">
                        <?php if (!empty($customer['email'])): ?>
                            <?php if (!empty($customer['email_verified_at'])): ?>
                                <span style="color:var(--color-success); font-size:.78rem;">✓ تایید شده</span>
                            <?php else: ?>
                                <span style="color:#B7791F; font-size:.78rem;">⚠ تایید نشده — <a href="/verify-email" style="color:var(--color-primary); font-weight:600;">تایید کنید</a></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </form>
                <p style="margin-top:14px;"><a href="/logout" style="color:var(--color-danger); font-size:.88rem;">خروج از حساب</a></p>
            </div>

            <!-- ---------- تاریخچه سفارش‌ها ---------- -->
            <div class="admin-card">
                <h3 style="margin-bottom:14px;">سفارش‌های من</h3>
                <?php if (!$orders): ?>
                    <div class="empty-state">هنوز سفارشی ثبت نکرده‌اید. <a href="/">مشاهده محصولات</a></div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead><tr><th>کد سفارش</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td dir="ltr"><?= e($o['order_code']) ?></td>
                            <td><?= formatPrice($o['total']) ?></td>
                            <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e($statusLabels[$o['status']] ?? $o['status']) ?></span></td>
                            <td><?= toPersianDigits(date('Y/m/d', strtotime($o['created_at']))) ?></td>
                            <td><a href="/account/order/<?= e($o['order_code']) ?>" class="btn btn-sm btn-outline">جزئیات</a></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ---------- سبد خرید فعلی ---------- -->
        <div class="cart-summary">
            <h3 style="margin-bottom:14px;">سبد خرید فعلی</h3>
            <?php if (empty($cart['items'])): ?>
                <p style="color:var(--color-muted); font-size:.9rem;">سبد خرید شما خالی است.</p>
            <?php else: ?>
                <?php foreach ($cart['items'] as $item): ?>
                    <div class="row" style="font-size:.85rem;">
                        <span><?= e($item['product']['name']) ?> × <?= toPersianDigits((string)$item['qty']) ?></span>
                        <span><?= formatPrice($item['line_total']) ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="row total-row"><span>جمع کل</span><span><?= formatPrice($cart['subtotal']) ?></span></div>
                <a href="/cart" class="btn btn-primary btn-block" style="margin-top:14px;">مشاهده سبد خرید</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
