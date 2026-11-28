<?php
/**
 * Storefront presentation data service.
 *
 * Keeps site controllers thin (Rule 7): storefront queries live here so
 * controllers only assemble view data and templates remain pure presentation.
 */

/**
 * Active top-level categories for storefront home grid and category strip.
 *
 * @return array<int, array<string, mixed>>
 */
function getHomeCategories(): array
{
    return db()->query(
        'SELECT id, name, slug, image
           FROM categories
          WHERE is_active = 1 AND parent_id IS NULL
          ORDER BY sort_order ASC'
    )->fetchAll();
}

/**
 * Active featured products with effective stock for the homepage carousel.
 *
 * @return array<int, array<string, mixed>>
 */
function getHomeFeaturedProducts(int $limit = 6): array
{
    $limit = max(2, min(24, $limit));
    $stockSql = effectiveStockSqlFragment('products');

    $stmt = db()->prepare(
        "SELECT *, $stockSql AS effective_stock
           FROM products
          WHERE is_active = 1 AND is_featured = 1
          ORDER BY created_at DESC
          LIMIT ?"
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Active newest products with effective stock for the homepage carousel.
 *
 * @return array<int, array<string, mixed>>
 */
function getHomeNewestProducts(int $limit = 6): array
{
    $limit = max(2, min(24, $limit));
    $stockSql = effectiveStockSqlFragment('products');

    $stmt = db()->prepare(
        "SELECT *, $stockSql AS effective_stock
           FROM products
          WHERE is_active = 1
          ORDER BY created_at DESC
          LIMIT ?"
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Shared storefront cart workspace payload used by both SSR /cart and HTML-over-the-Wire AJAX endpoints.
 *
 * Re-validates the active coupon and post-order gift selection against live store state.
 *
 * @return array<string, mixed>
 */
function getStorefrontCartWorkspaceData(): array
{
    $cart = cartDetails();
    $appliedCoupon = $_SESSION['coupon'] ?? null;

    $discount = 0;
    if ($appliedCoupon) {
        $cust = currentCustomer();
        $phone = $cust['phone'] ?? null;
        $check = CouponService::validate($appliedCoupon['code'], $cart['subtotal'], $phone, null, $cart['items'] ?? []);
        if ($check['ok']) {
            $discount = $check['discount'];
        } else {
            unset($_SESSION['coupon']);
            $appliedCoupon = null;
        }
    }

    $cartSubtotal = (int) ($cart['subtotal'] ?? 0);
    $postOrderResult = validatePostOrderSelection($_SESSION['post_order_selection'] ?? [], $cartSubtotal);
    $selectedPostOrderIds = array_column($postOrderResult['lines'], 'gift_item_id');
    $availablePostOrderItems = array_values(array_filter(
        getAvailablePostOrderItems($cartSubtotal),
        static fn($item) => !in_array((int) $item['id'], $selectedPostOrderIds, true)
    ));

    return [
        'cart' => $cart,
        'appliedCoupon' => $appliedCoupon,
        'discount' => $discount,
        'postOrderResult' => $postOrderResult,
        'availablePostOrderItems' => $availablePostOrderItems,
        'storeOrderStatus' => getSetting('store_order_status', 'active'),
        'storePausedMessage' => getSetting('store_paused_message', 'ثبت سفارش موقتاً به دلیل انبارگردانی یا تعطیلات متوقف شده است.'),
        'minOrderAmount' => (int) getSetting('min_order_amount', '0'),
    ];
}

/**
 * Detect whether the current storefront request expects an async JSON/HTML-fragment response.
 */
function isStorefrontAjaxRequest(): bool
{
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
        || !empty($_POST['ajax']);
}

/**
 * Unified progressive-enhancement responder for cart, coupon, and post-order mutations.
 * Returns fresh workspace HTML fragment on XHR without setting session flash; falls back to redirect otherwise.
 */
function respondCartMutation(bool $ok, string $message = '', bool $flashOnRedirect = true): void
{
    if (isStorefrontAjaxRequest()) {
        $workspace = getStorefrontCartWorkspaceData();
        jsonResponse([
            'ok' => $ok,
            'message' => $message,
            'cartCount' => cartCount(),
            'html' => render_site_component('cart_workspace', $workspace),
        ]);
    }

    if ($flashOnRedirect && $message !== '') {
        setFlash($ok ? 'success' : 'error', $message);
    }
    redirect('/cart');
}
