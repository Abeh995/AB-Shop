<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section">
    <h1 style="margin-bottom:24px;">سبد خرید</h1>

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

                <div class="row total-row"><span>مبلغ قابل پرداخت</span><span><?= formatPrice($cart['subtotal'] - $discount) ?></span></div>
                <a href="/checkout" class="btn btn-primary btn-block" style="margin-top:16px;">ادامه فرآیند خرید</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
