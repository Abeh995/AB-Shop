<?php
/**
 * Shopping cart logic supports two modes:
 *
 * 1) Guest users: the cart is kept in $_SESSION['cart'], as before. Prices are always read
 *    live from the database because guests do not have a persistent cart identity.
 *
 * 2) Logged-in customers: items are stored in `cart_items` and persist across devices/sessions.
 *    Each item has a `locked_unit_price`; this price is honored for `price_guarantee_days`
 *    from the oldest current cart item, after which the live price is used. Clearing the cart
 *    and adding a new item starts the guarantee window again.
 *
 * cartAdd/cartUpdateQty/cartRemove/cartClear/cartCount/cartDetails expose the same interface
 * in both modes, so controllers and views do not need to know which storage mode is active.
 */

function cartKey(int $productId, ?int $variantId): string
{
    return $productId . '-' . ($variantId ?? 0);
}

function cartAdd(int $productId, ?int $variantId, int $qty): void
{
    if ($qty < 1) $qty = 1;
    $variantIdNorm = $variantId ?? 0;

    if (isCustomerLoggedIn()) {
        $customerId = (int) $_SESSION['customer_id'];
        $unitPrice = getLiveUnitPrice($productId, $variantId ?: null);

        $stmt = db()->prepare("SELECT id, quantity FROM cart_items WHERE customer_id = ? AND product_id = ? AND variant_id = ?");
        $stmt->execute([$customerId, $productId, $variantIdNorm]);
        $existing = $stmt->fetch();

        if ($existing) {
            db()->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE id = ?")
                ->execute([$qty, $existing['id']]);
        } else {
            db()->prepare("INSERT INTO cart_items (customer_id, product_id, variant_id, quantity, locked_unit_price) VALUES (?,?,?,?,?)")
                ->execute([$customerId, $productId, $variantIdNorm, $qty, $unitPrice]);
        }
        return;
    }

    // ---------- Guest cart: session storage ----------
    $key = cartKey($productId, $variantId);
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$key] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'qty' => $qty,
        ];
    }
}

function cartUpdateQty(string $key, int $qty): void
{
    [$productId, $variantId] = array_map('intval', explode('-', $key));

    if (isCustomerLoggedIn()) {
        $customerId = (int) $_SESSION['customer_id'];
        if ($qty < 1) {
            db()->prepare("DELETE FROM cart_items WHERE customer_id = ? AND product_id = ? AND variant_id = ?")
                ->execute([$customerId, $productId, $variantId]);
        } else {
            db()->prepare("UPDATE cart_items SET quantity = ? WHERE customer_id = ? AND product_id = ? AND variant_id = ?")
                ->execute([$qty, $customerId, $productId, $variantId]);
        }
        return;
    }

    if (!isset($_SESSION['cart'][$key])) return;
    if ($qty < 1) {
        unset($_SESSION['cart'][$key]);
    } else {
        $_SESSION['cart'][$key]['qty'] = $qty;
    }
}

function cartRemove(string $key): void
{
    if (isCustomerLoggedIn()) {
        [$productId, $variantId] = array_map('intval', explode('-', $key));
        db()->prepare("DELETE FROM cart_items WHERE customer_id = ? AND product_id = ? AND variant_id = ?")
            ->execute([(int) $_SESSION['customer_id'], $productId, $variantId]);
        return;
    }

    unset($_SESSION['cart'][$key]);
}

function cartClear(): void
{
    if (isCustomerLoggedIn()) {
        db()->prepare("DELETE FROM cart_items WHERE customer_id = ?")->execute([(int) $_SESSION['customer_id']]);
    }
    $_SESSION['cart'] = [];
}

function cartCount(): int
{
    if (isCustomerLoggedIn()) {
        $stmt = db()->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE customer_id = ?");
        $stmt->execute([(int) $_SESSION['customer_id']]);
        return (int) $stmt->fetchColumn();
    }

    $count = 0;
    foreach ($_SESSION['cart'] ?? [] as $item) {
        $count += $item['qty'];
    }
    return $count;
}

/**
 * Fetch the current effective price for a product or variant directly from the database.
 * No cache or price lock is applied.
 */
function getLiveUnitPrice(int $productId, ?int $variantId): float
{
    $stmt = db()->prepare("SELECT price, discount_price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) return 0;

    $price = effectivePrice($product);

    if ($variantId) {
        $vstmt = db()->prepare("SELECT price_override FROM product_variants WHERE id = ? AND product_id = ?");
        $vstmt->execute([$variantId, $productId]);
        $variant = $vstmt->fetch();
        if ($variant && $variant['price_override'] !== null) {
            $price = (float) $variant['price_override'];
        }
    }

    return $price;
}

/**
 * Load the cart with complete, current details.
 * Stock is always read live from the database. Logged-in customers may receive a locked price
 * during the guarantee window; guest carts always use the live price.
 *
 * The optional 'price_guarantee' field contains price-guarantee information for display
 * and is meaningful only for logged-in customers.
 */
function cartDetails(): array
{
    if (isCustomerLoggedIn()) {
        return cartDetailsForCustomer((int) $_SESSION['customer_id']);
    }
    return cartDetailsForGuest();
}

function cartDetailsForGuest(): array
{
    $items = [];
    $subtotal = 0;

    foreach ($_SESSION['cart'] ?? [] as $key => $entry) {
        $stmt = db()->prepare("SELECT id, name, slug, price, discount_price, stock, image, is_active FROM products WHERE id = ?");
        $stmt->execute([$entry['product_id']]);
        $product = $stmt->fetch();
        if (!$product || !$product['is_active']) continue;

        $variant = null;
        $availableStock = (int) $product['stock'];
        $unitPrice = effectivePrice($product);

        if (!empty($entry['variant_id'])) {
            $vstmt = db()->prepare("SELECT id, size, color, stock, price_override FROM product_variants WHERE id = ? AND product_id = ?");
            $vstmt->execute([$entry['variant_id'], $product['id']]);
            $variant = $vstmt->fetch();
            if ($variant) {
                $availableStock = (int) $variant['stock'];
                if ($variant['price_override'] !== null) {
                    $unitPrice = (float) $variant['price_override'];
                }
            }
        }

        $qty = min($entry['qty'], max($availableStock, 0));
        if ($qty < 1) continue;

        $lineTotal = $unitPrice * $qty;
        $subtotal += $lineTotal;

        $items[] = [
            'key' => $key, 'product' => $product, 'variant' => $variant,
            'qty' => $qty, 'unit_price' => $unitPrice, 'line_total' => $lineTotal,
            'stock' => $availableStock, 'price_locked' => false,
        ];
    }

    return ['items' => $items, 'subtotal' => $subtotal, 'price_guarantee' => null];
}

function cartDetailsForCustomer(int $customerId): array
{
    $rows = db()->prepare("SELECT * FROM cart_items WHERE customer_id = ? ORDER BY added_at ASC");
    $rows->execute([$customerId]);
    $rows = $rows->fetchAll();

    $items = [];
    $subtotal = 0;

    if (empty($rows)) {
        return ['items' => [], 'subtotal' => 0, 'price_guarantee' => null];
    }

    // ---------- Calculate the price guarantee from the oldest item in the current cart ----------
    $guaranteeEnabled = getSetting('price_guarantee_enabled', '1') === '1';
    $guaranteeDays = max(0, (int) getSetting('price_guarantee_days', '7'));
    $cartStartedAt = $rows[0]['added_at']; // ORDER BY added_at ASC ensures the first row is the oldest item.
    $ageInSeconds = time() - strtotime($cartStartedAt);
    $ageInDays = $ageInSeconds / 86400;
    $withinGuarantee = $guaranteeEnabled && $guaranteeDays > 0 && $ageInDays < $guaranteeDays;
    $expiresAt = date('Y-m-d H:i:s', strtotime($cartStartedAt) + $guaranteeDays * 86400);

    foreach ($rows as $row) {
        $stmt = db()->prepare("SELECT id, name, slug, price, discount_price, stock, image, is_active FROM products WHERE id = ?");
        $stmt->execute([$row['product_id']]);
        $product = $stmt->fetch();
        if (!$product || !$product['is_active']) continue;

        $variant = null;
        $availableStock = (int) $product['stock'];
        $liveUnitPrice = effectivePrice($product);

        if ($row['variant_id'] > 0) {
            $vstmt = db()->prepare("SELECT id, size, color, stock, price_override FROM product_variants WHERE id = ? AND product_id = ?");
            $vstmt->execute([$row['variant_id'], $product['id']]);
            $variant = $vstmt->fetch();
            if ($variant) {
                $availableStock = (int) $variant['stock'];
                if ($variant['price_override'] !== null) {
                    $liveUnitPrice = (float) $variant['price_override'];
                }
            }
        }

        $qty = min((int) $row['quantity'], max($availableStock, 0));
        if ($qty < 1) continue;

        $unitPrice = $withinGuarantee ? (float) $row['locked_unit_price'] : $liveUnitPrice;
        $lineTotal = $unitPrice * $qty;
        $subtotal += $lineTotal;

        $items[] = [
            'key' => cartKey($row['product_id'], $row['variant_id'] ?: null),
            'product' => $product, 'variant' => $variant,
            'qty' => $qty, 'unit_price' => $unitPrice, 'line_total' => $lineTotal,
            'stock' => $availableStock,
            'price_locked' => $withinGuarantee,
            'live_unit_price' => $liveUnitPrice,
        ];
    }

    return [
        'items' => $items,
        'subtotal' => $subtotal,
        'price_guarantee' => [
            'active' => $withinGuarantee,
            'enabled' => $guaranteeEnabled,
            'started_at' => $cartStartedAt,
            'expires_at' => $expiresAt,
            'days' => $guaranteeDays,
        ],
    ];
}

/**
 * After successful login or signup, merge any items added as a guest into the customer's persistent cart.
 * Their prices are locked at merge time because they are newly inserted into the persistent cart.
 */
function mergeGuestCartIntoCustomerCart(int $customerId): void
{
    if (empty($_SESSION['cart'])) return;

    foreach ($_SESSION['cart'] as $entry) {
        $variantIdNorm = $entry['variant_id'] ?? 0;
        $unitPrice = getLiveUnitPrice($entry['product_id'], $entry['variant_id'] ?: null);

        $stmt = db()->prepare("SELECT id, quantity FROM cart_items WHERE customer_id = ? AND product_id = ? AND variant_id = ?");
        $stmt->execute([$customerId, $entry['product_id'], $variantIdNorm]);
        $existing = $stmt->fetch();

        if ($existing) {
            db()->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE id = ?")
                ->execute([$entry['qty'], $existing['id']]);
        } else {
            db()->prepare("INSERT INTO cart_items (customer_id, product_id, variant_id, quantity, locked_unit_price) VALUES (?,?,?,?,?)")
                ->execute([$customerId, $entry['product_id'], $variantIdNorm, $entry['qty'], $unitPrice]);
        }
    }

    $_SESSION['cart'] = [];
}
