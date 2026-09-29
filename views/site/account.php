<?php
/**
 * Customer Account Hub — Profile settings, order history, and active cart.
 * Pure presentation; all data prepared by CustomerService and OrderService.
 */
require APP_ROOT . '/views/layout/header.php';
?>

<div class="container section">
    <div class="account-hub-header">
        <div class="account-user-meta">
            <div class="account-avatar">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div>
                <h1 class="account-title"><?= !empty($customer['full_name']) ? e($customer['full_name']) : 'کاربر گرامی' ?></h1>
                <p class="account-subtitle">
                    شماره همراه: <span dir="ltr"><?= e($customer['phone']) ?></span>
                    <span class="badge-verified">✓ تایید شده</span>
                </p>
            </div>
        </div>
        <a href="/logout" class="account-logout-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            خروج از حساب
        </a>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="account-hub-grid">
        <div class="account-main-col">
            <!-- 1. Orders Section -->
            <div class="customer-card">
                <div class="customer-card-header">
                    <h3>سفارش‌های من</h3>
                    <span class="customer-card-badge"><?= toPersianDigits((string)count($orders)) ?> سفارش</span>
                </div>

                <?php if (!$orders): ?>
                    <div class="empty-state" style="padding: 24px 0;">
                        هنوز سفارشی ثبت نکرده‌اید.<br><br>
                        <a href="/" class="btn btn-primary btn-sm">مشاهده و خرید محصولات</a>
                    </div>
                <?php else: ?>
                    <div class="customer-orders-list">
                        <?php foreach ($orders as $o): ?>
                        <div class="customer-order-card">
                            <div class="customer-order-header">
                                <div class="customer-order-code-wrap">
                                    <span class="customer-order-label">کد سفارش:</span>
                                    <strong class="customer-order-code" dir="ltr"><?= e($o['order_code']) ?></strong>
                                </div>
                                <span class="status-pill status-<?= e($o['status']) ?>">
                                    <?= e($statusLabels[$o['status']] ?? $o['status']) ?>
                                </span>
                            </div>
                            <div class="customer-order-details">
                                <div class="customer-order-detail-item">
                                    <span class="customer-order-detail-label">مبلغ کل:</span>
                                    <strong class="customer-order-price"><?= formatPrice($o['total']) ?></strong>
                                </div>
                                <div class="customer-order-detail-item">
                                    <span class="customer-order-detail-label">تاریخ ثبت:</span>
                                    <span><?= toPersianDigits(date('Y/m/d', strtotime($o['created_at']))) ?></span>
                                </div>
                            </div>
                            <div class="customer-order-footer">
                                <a href="/account/order/<?= e($o['order_code']) ?>" class="btn btn-sm btn-outline customer-order-btn">
                                    مشاهده جزئیات و فاکتور &larr;
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 2. Profile Edit Card -->
            <div class="customer-card" style="margin-top: 24px;">
                <div class="customer-card-header">
                    <h3>اطلاعات حساب کاربری</h3>
                </div>
                <form method="post" action="/account">
                    <?= csrfField() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>نام و نام‌خانوادگی</label>
                            <input class="form-control" type="text" name="full_name" value="<?= e($customer['full_name'] ?? '') ?>" placeholder="نام خود را وارد کنید">
                        </div>
                        <div class="form-group">
                            <label>شماره موبایل</label>
                            <input class="form-control" type="text" dir="ltr" value="<?= e($customer['phone']) ?>" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>ایمیل</label>
                        <input class="form-control" type="email" name="email" dir="ltr" value="<?= e($customer['email'] ?? '') ?>" placeholder="example@email.com">
                        <?php if (!empty($customer['email'])): ?>
                            <?php if (!empty($customer['email_verified_at'])): ?>
                                <span class="customer-field-note note-success">✓ ایمیل تایید شده است</span>
                            <?php else: ?>
                                <span class="customer-field-note note-warning">
                                    ⚠ ایمیل تایید نشده — <a href="/verify-email">تایید ایمیل</a>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات مشخصات</button>
                </form>
            </div>
        </div>

        <!-- 3. Sidebar: Active Cart & Quick Links -->
        <aside class="account-side-col">
            <div class="customer-card">
                <div class="customer-card-header">
                    <h3>سبد خرید فعلی</h3>
                </div>
                <?php if (empty($cart['items'])): ?>
                    <p style="color:var(--color-muted); font-size:.88rem; margin: 0;">سبد خرید شما در حال حاضر خالی است.</p>
                <?php else: ?>
                    <div class="account-cart-items">
                        <?php foreach ($cart['items'] as $item): ?>
                            <div class="account-cart-row">
                                <span class="account-cart-name"><?= e($item['product']['name']) ?> <small>× <?= toPersianDigits((string)$item['qty']) ?></small></span>
                                <strong class="account-cart-price"><?= formatPrice($item['line_total']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="account-cart-total">
                        <span>جمع کل کالاها:</span>
                        <strong><?= formatPrice($cart['subtotal']) ?></strong>
                    </div>
                    <a href="/cart" class="btn btn-primary btn-block" style="margin-top:14px;">تکمیل و نهایی کردن خرید</a>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
