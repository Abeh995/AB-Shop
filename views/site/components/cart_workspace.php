<?php
/**
 * Storefront Interactive Cart Workspace Fragment
 *
 * Designed for both initial SSR (/cart) and HTML-over-the-Wire AJAX swaps inside [data-ab-cart-root].
 * Props:
 * - cart (array): CartService summary structure (items, subtotal, price_guarantee)
 * - appliedCoupon (array|null): active coupon row
 * - discount (float|int): discount amount in Toman
 * - postOrderResult (array): ['lines' => array, 'total' => int|float]
 * - availablePostOrderItems (array): upsell gift items available for cart add
 * - storeOrderStatus (string): 'active' | 'paused'
 * - storePausedMessage (string): owner notice when paused
 * - minOrderAmount (int|float): minimum cart subtotal required for checkout
 * - isLoggedIn (bool|null): customer auth state (defaults to isCustomerLoggedIn())
 */
$cart = is_array($cart ?? null) ? $cart : ['items' => [], 'subtotal' => 0, 'price_guarantee' => []];
$appliedCoupon = !empty($appliedCoupon) && is_array($appliedCoupon) ? $appliedCoupon : null;
$discount = (float) ($discount ?? 0);
$postOrderResult = is_array($postOrderResult ?? null) ? $postOrderResult : ['lines' => [], 'total' => 0];
$postOrderLines = is_array($postOrderResult['lines'] ?? null) ? $postOrderResult['lines'] : [];
$postOrderTotal = (float) ($postOrderResult['total'] ?? 0);
$availablePostOrderItems = is_array($availablePostOrderItems ?? null) ? $availablePostOrderItems : [];
$storeOrderStatus = (string) ($storeOrderStatus ?? 'active');
$storePausedMessage = (string) ($storePausedMessage ?? 'ثبت سفارش موقتاً متوقف شده است.');
$minOrderAmount = (float) ($minOrderAmount ?? 0);
$loggedIn = isset($isLoggedIn) && $isLoggedIn !== null ? (bool) $isLoggedIn : isCustomerLoggedIn();

$subtotal = (float) ($cart['subtotal'] ?? 0);
$grandTotal = max(0, $subtotal - $discount + $postOrderTotal);
?>
<?php if (!empty($cart['price_guarantee']['enabled'])): ?>
    <?php if (!empty($cart['price_guarantee']['active'])): ?>
        <?php site_component('alert', [
            'state' => 'info',
            'message' => 'قیمت کالاهای این سبد تا تاریخ ' . toPersianDigits(date('Y/m/d', strtotime((string) $cart['price_guarantee']['expires_at']))) . ' برای شما تضمین شده است.',
        ]); ?>
    <?php else: ?>
        <?php site_component('alert', [
            'state' => 'warning',
            'message' => 'مهلت تضمین قیمت این سبد به پایان رسیده؛ قیمت‌ها بر اساس نرخ لحظه‌ای محاسبه شده‌اند.',
        ]); ?>
    <?php endif; ?>
<?php endif; ?>

<?php if (empty($cart['items'])): ?>
    <?php site_component('empty_state', [
        'message' => 'سبد خرید شما خالی است.',
        'actionUrl' => '/',
        'actionLabel' => 'مشاهده محصولات',
    ]); ?>
<?php else: ?>
    <div class="c-split-summary cart-layout">
        <div class="c-stack c-stack--lg">
            <table class="c-cart-table cart-table">
                <?php foreach ($cart['items'] as $item):
                    $p = $item['product'];
                    $img = !empty($p['image']) ? UPLOAD_URL . e($p['image']) : '/assets/img/placeholder-sock.svg';
                    $variantLabel = !empty($item['variant'])
                        ? trim((string) ($item['variant']['size'] ?? '') . ' ' . (string) ($item['variant']['color'] ?? ''))
                        : '';
                ?>
                <tr class="cart-item-row">
                    <td>
                        <a href="/product/<?= e($p['slug']) ?>" class="c-cart-item__info cart-item-info">
                            <img src="<?= $img ?>" alt="<?= e($p['name']) ?>" width="64" height="64" class="c-cart-item__img">
                            <div>
                                <div class="c-cart-item__name cart-item-name"><?= e($p['name']) ?></div>
                                <?php if ($variantLabel !== ''): ?>
                                    <div class="c-cart-item__meta cart-item-variant"><?= e($variantLabel) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['price_locked'])): ?>
                                    <div class="c-cart-item__meta c-text-success cart-item-variant">قیمت تضمین‌شده</div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </td>
                    <td><?= formatPrice($item['unit_price']) ?></td>
                    <td>
                        <form method="post" action="/ajax/cart_update.php" data-ab-async-form="cart-update">
                            <?= csrfField() ?>
                            <input type="hidden" name="key" value="<?= e($item['key']) ?>">
                            <?php site_component('qty_stepper', [
                                'name' => 'qty',
                                'value' => (int) $item['qty'],
                                'min' => 1,
                                'max' => max(1, (int) ($item['stock'] ?? 99)),
                                'autoSubmit' => true,
                                'ariaLabel' => 'تعداد ' . ($p['name'] ?? 'محصول'),
                            ]); ?>
                        </form>
                    </td>
                    <td class="c-fw-bold"><?= formatPrice($item['line_total']) ?></td>
                    <td>
                        <form method="post" action="/ajax/cart_remove.php" data-ab-async-form="cart-remove">
                            <?= csrfField() ?>
                            <input type="hidden" name="key" value="<?= e($item['key']) ?>">
                            <button type="submit" class="c-link-danger remove-btn">حذف</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <?php if ($postOrderLines !== []): ?>
                <section>
                    <h3 class="c-text-md c-mb-3">پیشنهاد بعد از سبد شما</h3>
                    <table class="c-cart-table cart-table">
                        <?php foreach ($postOrderLines as $line):
                            $giftImg = !empty($line['image']) ? UPLOAD_URL . e($line['image']) : '/assets/img/placeholder-sock.svg';
                        ?>
                        <tr class="cart-item-row">
                            <td>
                                <div class="c-cart-item__info cart-item-info">
                                    <img src="<?= $giftImg ?>" alt="<?= e($line['name']) ?>" width="64" height="64" class="c-cart-item__img">
                                    <div class="c-cart-item__name cart-item-name"><?= e($line['name']) ?></div>
                                </div>
                            </td>
                            <td><?= formatPrice($line['unit_selling_price']) ?></td>
                            <td><?= toPersianDigits((string) $line['quantity']) ?></td>
                            <td class="c-fw-bold"><?= formatPrice($line['line_total']) ?></td>
                            <td>
                                <form method="post" action="/ajax/post_order_remove.php" data-ab-async-form="post-order-remove">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="gift_item_id" value="<?= (int) $line['gift_item_id'] ?>">
                                    <button type="submit" class="c-link-danger remove-btn">حذف</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </section>
            <?php endif; ?>

            <?php if ($availablePostOrderItems !== []): ?>
                <section>
                    <h3 class="c-text-md c-mb-3">می‌خواهید این‌ها رو هم اضافه کنید؟</h3>
                    <div class="c-product-grid c-product-grid--compact product-grid">
                        <?php foreach ($availablePostOrderItems as $gi): ?>
                            <?php site_component('product_card', [
                                'product' => $gi,
                                'variant' => 'compact',
                                'badgeText' => $gi['badge_text'] ?? null,
                                'badgeTone' => 'discount',
                                'actionForm' => [
                                    'action' => '/ajax/post_order_add.php',
                                    'asyncMode' => 'post-order-add',
                                    'submitLabel' => 'افزودن',
                                    'inputs' => [
                                        'gift_item_id' => (int) $gi['id'],
                                        'quantity' => 1,
                                    ],
                                ],
                            ]); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <?php
        ob_start();
        if ($appliedCoupon): ?>
            <form method="post" action="/ajax/coupon_remove.php" data-ab-async-form="coupon-remove">
                <?= csrfField() ?>
                <button type="submit" class="c-link-danger c-text-xs remove-btn">حذف کد تخفیف</button>
            </form>
        <?php else: ?>
            <form method="post" action="/ajax/coupon_apply.php" class="c-inline-form" data-ab-async-form="coupon-apply">
                <?= csrfField() ?>
                <input class="c-form-control" type="text" name="coupon_code" placeholder="کد تخفیف">
                <button type="submit" class="c-btn c-btn--sm c-btn--outline">اعمال</button>
            </form>
        <?php endif;
        $couponFormHtml = (string) ob_get_clean();

        ob_start();
        if ($storeOrderStatus === 'paused'):
            site_component('alert', [
                'state' => 'warning',
                'title' => 'توقف موقت سفارش‌گیری:',
                'message' => $storePausedMessage,
                'class' => 'c-mb-0',
            ]);
        elseif ($minOrderAmount > 0 && $subtotal < $minOrderAmount):
            site_component('alert', [
                'state' => 'info',
                'message' => 'حداقل ارزش سفارش برای ثبت نهایی ' . formatPrice($minOrderAmount) . ' است. (مبلغ باقیمانده: ' . formatPrice($minOrderAmount - $subtotal) . ')',
                'class' => 'c-mb-3',
            ]);
            ?>
            <button type="button" class="c-btn c-btn--primary c-btn--block" disabled>حداقل مبلغ تکمیل نشده است</button>
        <?php else: ?>
            <a href="/checkout" class="c-btn c-btn--primary c-btn--block">ادامه فرآیند خرید</a>
        <?php endif;
        if (!$loggedIn): ?>
            <p class="c-text-xs c-text-muted c-mt-2 c-mb-0 c-text-center">برای نهایی کردن سفارش، ابتدا حساب کاربری بسازید یا وارد شوید.</p>
        <?php endif;
        $footerHtml = (string) ob_get_clean();

        site_component('order_summary', [
            'title' => 'خلاصه سبد خرید',
            'showItems' => false,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'couponCode' => $appliedCoupon['code'] ?? '',
            'couponFormHtml' => $couponFormHtml,
            'postOrderTotal' => $postOrderTotal,
            'grandTotal' => $grandTotal,
            'footerHtml' => $footerHtml,
        ]);
        ?>
    </div>
<?php endif; ?>
