<?php
/**
 * Gift box / post-order service.
 *
 * A gift_items row is a single catalog entity used in up to two roles:
 *   - "gift": an admin attaches it to an order for free (order_detail.php)
 *   - "post_order": a customer adds it as a paid checkout add-on (cart/checkout)
 * Which roles are available is just is_giftable/is_post_orderable on the
 * row — not two tables — so an item's role can change without recreating it.
 *
 * Every attachment to an order is a snapshot in order_gift_items: the name,
 * image, and both prices at that moment, independent of the catalog row's
 * current values. Stock changes always go through a locked read
 * (SELECT ... FOR UPDATE) inside a transaction, matching the pattern
 * PricingService uses for price changes.
 */

/**
 * Active, post-orderable items with stock — what the customer is offered
 * on the cart page.
 */
function getAvailablePostOrderItems(): array
{
    return db()->query("
        SELECT * FROM gift_items
        WHERE is_active = 1 AND is_post_orderable = 1 AND stock > 0
        ORDER BY name ASC
    ")->fetchAll();
}

/**
 * Active, giftable items — the choices offered to an admin on the order
 * detail page. Stock is shown but not filtered here, so the admin can see
 * (and decide not to use) an out-of-stock item rather than have it silently
 * disappear from the list.
 */
function getGiftableItems(): array
{
    return db()->query("
        SELECT * FROM gift_items
        WHERE is_active = 1 AND is_giftable = 1
        ORDER BY name ASC
    ")->fetchAll();
}

/**
 * Re-validate a customer's post-order selection (session-stored
 * [gift_item_id => quantity]) against the live catalog — price and stock
 * are never trusted from the client or from an earlier page load.
 *
 * @param array<int,int> $selection
 * @return array{ok:bool, lines:array, total:int, errors:string[]}
 */
function validatePostOrderSelection(array $selection): array
{
    $lines = [];
    $errors = [];
    $total = 0;

    foreach ($selection as $giftItemId => $qty) {
        $qty = (int) $qty;
        if ($qty < 1) continue;

        $stmt = db()->prepare("SELECT * FROM gift_items WHERE id = ? AND is_active = 1 AND is_post_orderable = 1");
        $stmt->execute([(int) $giftItemId]);
        $item = $stmt->fetch();

        if (!$item) {
            $errors[] = 'یکی از محصولات جانبی انتخابی دیگر در دسترس نیست.';
            continue;
        }
        if ($qty > (int) $item['stock']) {
            $errors[] = 'موجودی «' . $item['name'] . '» کافی نیست.';
            continue;
        }

        $unitPrice = (int) $item['post_order_price'];
        $lines[] = [
            'gift_item_id' => (int) $item['id'],
            'name' => $item['name'],
            'image' => $item['image'],
            'quantity' => $qty,
            'unit_cost_price' => (int) $item['cost_price'],
            'unit_selling_price' => $unitPrice,
            'line_total' => $unitPrice * $qty,
        ];
        $total += $unitPrice * $qty;
    }

    return ['ok' => empty($errors), 'lines' => $lines, 'total' => $total, 'errors' => $errors];
}

/**
 * Write already-validated post-order lines into an order, inside the
 * caller's existing transaction (checkout.php runs one for the whole
 * order). Decrements stock with a locked, conditional UPDATE, the same
 * guard checkout.php already uses for regular product stock.
 *
 * @param array $lines Output of validatePostOrderSelection()['lines']
 * @throws Exception if a line's stock changed since validation
 */
function attachPostOrderLines(PDO $pdo, int $orderId, array $lines): void
{
    $insert = $pdo->prepare("
        INSERT INTO order_gift_items
            (order_id, gift_item_id, name, image, quantity, unit_cost_price, unit_selling_price, role)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'post_order')
    ");

    foreach ($lines as $line) {
        $dec = $pdo->prepare("UPDATE gift_items SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $dec->execute([$line['quantity'], $line['gift_item_id'], $line['quantity']]);
        if ($dec->rowCount() === 0) {
            throw new Exception('موجودی کافی نیست: ' . $line['name']);
        }

        $insert->execute([
            $orderId, $line['gift_item_id'], $line['name'], $line['image'],
            $line['quantity'], $line['unit_cost_price'], $line['unit_selling_price'],
        ]);
    }
}

/**
 * Admin action: attach a gift_items row to an existing order for free.
 * Its own transaction (unlike attachPostOrderLines, which joins the
 * checkout transaction) since this is called on its own from the order
 * detail page, not as part of placing the order.
 *
 * @return array{ok:bool, error?:string}
 */
function assignGiftToOrder(int $orderId, int $giftItemId, int $qty, int $adminId, ?string $note = null): array
{
    if ($qty < 1) {
        return ['ok' => false, 'error' => 'تعداد نامعتبر است.'];
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $orderCheck = $pdo->prepare("SELECT id FROM orders WHERE id = ?");
        $orderCheck->execute([$orderId]);
        if (!$orderCheck->fetch()) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'سفارش مورد نظر پیدا نشد.'];
        }

        $stmt = $pdo->prepare("SELECT * FROM gift_items WHERE id = ? AND is_active = 1 AND is_giftable = 1 FOR UPDATE");
        $stmt->execute([$giftItemId]);
        $item = $stmt->fetch();
        if (!$item) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'این آیتم برای اهدا در دسترس نیست.'];
        }

        $dec = $pdo->prepare("UPDATE gift_items SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $dec->execute([$qty, $giftItemId, $qty]);
        if ($dec->rowCount() === 0) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'موجودی «' . $item['name'] . '» کافی نیست.'];
        }

        $insert = $pdo->prepare("
            INSERT INTO order_gift_items
                (order_id, gift_item_id, name, image, quantity, unit_cost_price, unit_selling_price, role, note, assigned_by_admin_id)
            VALUES (?, ?, ?, ?, ?, ?, 0, 'gift', ?, ?)
        ");
        $insert->execute([$orderId, $giftItemId, $item['name'], $item['image'], $qty, $item['cost_price'], $note, $adminId]);

        $pdo->commit();
        return ['ok' => true];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'خطای پایگاه‌داده: ' . $e->getMessage()];
    }
}

/**
 * All gift/post-order lines attached to an order, for the order detail view.
 */
function getOrderGiftItems(int $orderId): array
{
    $stmt = db()->prepare("
        SELECT ogi.*, a.username AS admin_username
        FROM order_gift_items ogi
        LEFT JOIN admins a ON a.id = ogi.assigned_by_admin_id
        WHERE ogi.order_id = ?
        ORDER BY ogi.id ASC
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}
