<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section">
    <h1 style="margin-bottom:24px;">سبد خرید</h1>

    <?php if (!empty($cart['price_guarantee']) && $cart['price_guarantee']['enabled']): ?>
        <?php if ($cart['price_guarantee']['active']): ?>
        <div class="alert alert-info">
            💰 قیمت کالاهای این سبد تا تاریخ <?= toPersianDigits(date('Y/m/d', strtotime($cart['price_guarantee']['expires_at']))) ?> برای شما تضمین شده است.
        </div>
        <?php else: ?>
        <div class="alert alert-info" style="background:#FEF3C7; color:#92400E;">
            ⏰ مهلت تضمین قیمت این سبد به پایان رسیده؛ قیمت‌ها بر اساس نرخ لحظه‌ای محاسبه شده‌اند.
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (empty($cart['items'])): ?>
        <div class="empty-state">
            سبد خرید شما خالی است.<br><br>
            <a href="/" class="btn btn-primary">مشاهده محصولات</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div>
                <table class="cart-table">
                    <?php foreach ($cart['items'] as $item):
                        $p = $item['product'];
                        $img = $p['image'] ? UPLOAD_URL . e($p['image']) : '/assets/img/placeholder-sock.svg';
                        $variantLabel = $item['variant'] ? trim(($item['variant']['size'] ?? '') . ' ' . ($item['variant']['color'] ?? '')) : '';
                    ?>
                    <tr class="cart-item-row">
                        <td>
                            <a href="/product/<?= e($p['slug']) ?>" class="cart-item-info">
                                <img src="<?= $img ?>" alt="">
                                <div>
                                    <div class="cart-item-name"><?= e($p['name']) ?></div>
                                    <?php if ($variantLabel): ?><div class="cart-item-variant"><?= e($variantLabel) ?></div><?php endif; ?>
                                    <?php if (!empty($item['price_locked'])): ?><div class="cart-item-variant" style="color:var(--color-success);">قیمت تضمین‌شده</div><?php endif; ?>
                                </div>
                            </a>
                        </td>
                        <td><?= formatPrice($item['unit_price']) ?></td>
                        <td>
                            <form method="post" action="/ajax/cart_update.php">
                                <?= csrfField() ?>
                                <input type="hidden" name="key" value="<?= e($item['key']) ?>">
                                <input class="cart-qty-input" type="number" name="qty" value="<?= (int)$item['qty'] ?>" min="1" max="<?= (int)$item['stock'] ?>" style="width:60px; padding:6px; border:1px solid var(--color-border); border-radius:6px; font-family:inherit;">
                            </form>
                        </td>
                        <td style="font-weight:700;"><?= formatPrice($item['line_total']) ?></td>
                        <td>
                            <form method="post" action="/ajax/cart_remove.php">
                                <?= csrfField() ?>
                                <input type="hidden" name="key" value="<?= e($item['key']) ?>">
                                <button type="submit" class="remove-btn">حذف</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <?php if ($postOrderResult['lines']): ?>
                <div style="margin-top:20px;">
                    <h3 style="font-size:1rem; margin-bottom:12px;">پیشنهاد بعد از سبد شما</h3>
                    <table class="cart-table">
                        <?php foreach ($postOrderResult['lines'] as $line):
                            $giftImg = $line['image'] ? UPLOAD_URL . e($line['image']) : '/assets/img/placeholder-sock.svg';
                        ?>
                        <tr class="cart-item-row">
                            <td>
                                <div class="cart-item-info">
                                    <img src="<?= $giftImg ?>" alt="">
                                    <div class="cart-item-name"><?= e($line['name']) ?></div>
                                </div>
                            </td>
                            <td><?= formatPrice($line['unit_selling_price']) ?></td>
                            <td><?= toPersianDigits((string)$line['quantity']) ?></td>
                            <td style="font-weight:700;"><?= formatPrice($line['line_total']) ?></td>
                            <td>
                                <form method="post" action="/ajax/post_order_remove.php">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="gift_item_id" value="<?= (int)$line['gift_item_id'] ?>">
                                    <button type="submit" class="remove-btn">حذف</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>

                <?php if ($availablePostOrderItems): ?>
                <div style="margin-top:20px;">
                    <h3 style="font-size:1rem; margin-bottom:12px;">می‌خواهید این‌ها رو هم اضافه کنید؟</h3>
                    <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
                        <?php foreach ($availablePostOrderItems as $gi):
                            $giftImg = $gi['image'] ? UPLOAD_URL . e($gi['image']) : '/assets/img/placeholder-sock.svg';
                        ?>
                        <div class="product-card">
                            <div class="thumb"><img src="<?= $giftImg ?>" alt=""></div>
                            <div class="body">
                                <div class="name"><?= e($gi['name']) ?></div>
                                <div class="price-row"><span class="price-current"><?= formatPrice((int)$gi['post_order_price']) ?></span></div>
                                <form method="post" action="/ajax/post_order_add.php" style="margin-top:8px;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="gift_item_id" value="<?= (int)$gi['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline btn-block">افزودن</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="cart-summary">
                <div class="row"><span>جمع کل کالاها</span><span><?= formatPrice($cart['subtotal']) ?></span></div>

                <?php if ($appliedCoupon): ?>
                    <div class="row" style="color:var(--color-success);">
                        <span>تخفیف (<?= e($appliedCoupon['code']) ?>)</span>
                        <span>−<?= formatPrice($discount) ?></span>
                    </div>
                    <form method="post" action="/ajax/coupon_remove.php" style="margin-bottom:10px;">
                        <?= csrfField() ?>
                        <button type="submit" class="remove-btn" style="font-size:.8rem;">حذف کد تخفیف</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="/ajax/coupon_apply.php" style="display:flex; gap:6px; margin-bottom:12px;">
                        <?= csrfField() ?>
                        <input class="form-control" type="text" name="coupon_code" placeholder="کد تخفیف" style="flex:1;">
                        <button type="submit" class="btn btn-sm btn-outline">اعمال</button>
                    </form>
                <?php endif; ?>

                <?php if ($postOrderResult['total'] > 0): ?>
                <div class="row"><span>پیشنهاد بعد از سبد</span><span><?= formatPrice($postOrderResult['total']) ?></span></div>
                <?php endif; ?>

                <div class="row total-row"><span>مبلغ قابل پرداخت</span><span><?= formatPrice($cart['subtotal'] - $discount + $postOrderResult['total']) ?></span></div>
                <p style="font-size:.78rem; color:var(--color-muted); margin-top:6px;">+ هزینه ارسال، که در مرحله بعد بر اساس آدرستان محاسبه می‌شود</p>
                <a href="/checkout" class="btn btn-primary btn-block" style="margin-top:10px;">ادامه فرآیند خرید</a>
                <?php if (!isCustomerLoggedIn()): ?><p style="font-size:.78rem; color:var(--color-muted); margin:8px 0 0; text-align:center;">برای نهایی کردن سفارش، ابتدا حساب کاربری بسازید یا وارد شوید.</p><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
