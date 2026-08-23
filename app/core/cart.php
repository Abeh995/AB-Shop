<?php

function cartKey(int $productId, ?int $variantId): string
{
    return $productId . '-' . ($variantId ?? 0);
}

function cartAdd(int $productId, ?int $variantId, int $qty): void
{
    if ($qty < 1) $qty = 1;
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
    if (!isset($_SESSION['cart'][$key])) return;
    if ($qty < 1) {
        unset($_SESSION['cart'][$key]);
    } else {
        $_SESSION['cart'][$key]['qty'] = $qty;
    }
}

function cartRemove(string $key): void
{
    unset($_SESSION['cart'][$key]);
}

function cartClear(): void
{
    $_SESSION['cart'] = [];
}

function cartCount(): int
{
    $count = 0;
    foreach ($_SESSION['cart'] ?? [] as $item) {
        $count += $item['qty'];
    }
    return $count;
}

function cartDetails(): array
{
    $items = [];
    $subtotal = 0;

    if (empty($_SESSION['cart'])) {
        return ['items' => [], 'subtotal' => 0];
    }

    foreach ($_SESSION['cart'] as $key => $entry) {
        $stmt = db()->prepare('SELECT id, name, slug, price, discount_price, stock, image, is_active FROM products WHERE id = ?');
        $stmt->execute([$entry['product_id']]);
        $product = $stmt->fetch();

        if (!$product || !$product['is_active']) {
            continue;
        }

        $variant = null;
        $availableStock = (int) $product['stock'];
        $unitPrice = effectivePrice($product);

        if (!empty($entry['variant_id'])) {
            $vstmt = db()->prepare('SELECT id, size, color, stock, price_override FROM product_variants WHERE id = ? AND product_id = ?');
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
            'key' => $key,
            'product' => $product,
            'variant' => $variant,
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'stock' => $availableStock,
        ];
    }

    return ['items' => $items, 'subtotal' => $subtotal];
}
