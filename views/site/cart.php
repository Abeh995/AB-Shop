<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container c-section section">
    <h1 class="c-mb-6">سبد خرید</h1>

    <div class="c-cart-workspace" data-ab-cart-root>
        <?php site_component('cart_workspace', [
            'cart' => $cart,
            'appliedCoupon' => $appliedCoupon,
            'discount' => $discount,
            'postOrderResult' => $postOrderResult,
            'availablePostOrderItems' => $availablePostOrderItems,
            'storeOrderStatus' => $storeOrderStatus,
            'storePausedMessage' => $storePausedMessage,
            'minOrderAmount' => $minOrderAmount,
        ]); ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
