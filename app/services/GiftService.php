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

/**
 * Aggregate summary KPIs for gift & post-order catalog health and usage.
 *
 * @return array{
 *   total_items: int,
 *   active_items: int,
 *   inactive_items: int,
 *   giftable_count: int,
 *   post_orderable_count: int,
 *   hybrid_count: int,
 *   total_stock: int,
 *   total_inventory_valuation: int,
 *   low_stock_count: int,
 *   lifetime_gifted_units: int,
 *   lifetime_sold_units: int,
 *   lifetime_post_order_revenue: int
 * }
 */
function getAdminGiftItemsMetrics(): array
{
    $pdo = db();

    // 1. Catalog inventory aggregations
    $catalogStats = $pdo->query("
        SELECT
            COUNT(*) AS total_items,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_items,
            SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS inactive_items,
            SUM(CASE WHEN is_giftable = 1 THEN 1 ELSE 0 END) AS giftable_count,
            SUM(CASE WHEN is_post_orderable = 1 THEN 1 ELSE 0 END) AS post_orderable_count,
            SUM(CASE WHEN is_giftable = 1 AND is_post_orderable = 1 THEN 1 ELSE 0 END) AS hybrid_count,
            COALESCE(SUM(stock), 0) AS total_stock,
            COALESCE(SUM(stock * cost_price), 0) AS total_inventory_valuation,
            SUM(CASE WHEN stock <= 5 THEN 1 ELSE 0 END) AS low_stock_count
        FROM gift_items
    ")->fetch() ?: [];

    // 2. Order usage aggregations from order_gift_items
    $orderStats = $pdo->query("
        SELECT
            COALESCE(SUM(CASE WHEN role = 'gift' THEN quantity ELSE 0 END), 0) AS lifetime_gifted_units,
            COALESCE(SUM(CASE WHEN role = 'post_order' THEN quantity ELSE 0 END), 0) AS lifetime_sold_units,
            COALESCE(SUM(CASE WHEN role = 'post_order' THEN quantity * unit_selling_price ELSE 0 END), 0) AS lifetime_post_order_revenue
        FROM order_gift_items
    ")->fetch() ?: [];

    return [
        'total_items'                 => (int) ($catalogStats['total_items'] ?? 0),
        'active_items'                => (int) ($catalogStats['active_items'] ?? 0),
        'inactive_items'              => (int) ($catalogStats['inactive_items'] ?? 0),
        'giftable_count'              => (int) ($catalogStats['giftable_count'] ?? 0),
        'post_orderable_count'        => (int) ($catalogStats['post_orderable_count'] ?? 0),
        'hybrid_count'                => (int) ($catalogStats['hybrid_count'] ?? 0),
        'total_stock'                 => (int) ($catalogStats['total_stock'] ?? 0),
        'total_inventory_valuation'   => (int) ($catalogStats['total_inventory_valuation'] ?? 0),
        'low_stock_count'             => (int) ($catalogStats['low_stock_count'] ?? 0),
        'lifetime_gifted_units'       => (int) ($orderStats['lifetime_gifted_units'] ?? 0),
        'lifetime_sold_units'         => (int) ($orderStats['lifetime_sold_units'] ?? 0),
        'lifetime_post_order_revenue' => (int) ($orderStats['lifetime_post_order_revenue'] ?? 0),
    ];
}

/**
 * Administrative list of gift/post-order catalog items with attached lifetime performance.
 *
 * @param string $search
 * @param string $roleFilter 'all'|'giftable'|'post_orderable'|'hybrid'|'low_stock'|'inactive'
 * @return array
 */
function getAdminGiftItemsList(string $search = '', string $roleFilter = 'all'): array
{
    $where = ['1=1'];
    $params = [];

    if ($search !== '') {
        $where[] = 'g.name LIKE ?';
        $params[] = '%' . $search . '%';
    }

    switch ($roleFilter) {
        case 'giftable':
            $where[] = 'g.is_giftable = 1';
            break;
        case 'post_orderable':
            $where[] = 'g.is_post_orderable = 1';
            break;
        case 'hybrid':
            $where[] = 'g.is_giftable = 1 AND g.is_post_orderable = 1';
            break;
        case 'low_stock':
            $where[] = 'g.stock <= 5';
            break;
        case 'inactive':
            $where[] = 'g.is_active = 0';
            break;
    }

    $whereSql = implode(' AND ', $where);

    $sql = "
        SELECT
            g.*,
            COALESCE(SUM(CASE WHEN ogi.role = 'gift' THEN ogi.quantity ELSE 0 END), 0) AS gifted_units,
            COALESCE(SUM(CASE WHEN ogi.role = 'post_order' THEN ogi.quantity ELSE 0 END), 0) AS sold_units,
            COALESCE(SUM(CASE WHEN ogi.role = 'post_order' THEN ogi.quantity * ogi.unit_selling_price ELSE 0 END), 0) AS gross_revenue
        FROM gift_items g
        LEFT JOIN order_gift_items ogi ON ogi.gift_item_id = g.id
        WHERE {$whereSql}
        GROUP BY g.id
        ORDER BY g.created_at DESC
    ";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$item) {
        $cost = (int) $item['cost_price'];
        $sale = $item['post_order_price'] !== null ? (int) $item['post_order_price'] : null;
        $item['profit_per_unit'] = ($sale !== null) ? ($sale - $cost) : null;
        $item['margin_percent'] = ($sale !== null && $sale > 0) ? (int) round((($sale - $cost) / $sale) * 100) : null;
        $item['gifted_units'] = (int) $item['gifted_units'];
        $item['sold_units'] = (int) $item['sold_units'];
        $item['gross_revenue'] = (int) $item['gross_revenue'];
    }
    unset($item);

    return $rows;
}

/**
 * Toggle active status of a gift item.
 *
 * @param int $id
 * @return array{ok: bool, is_active?: int, error?: string}
 */
function toggleGiftItemActive(int $id): array
{
    $pdo = db();
    $stmt = $pdo->prepare("SELECT is_active FROM gift_items WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetchColumn();
    if ($current === false) {
        return ['ok' => false, 'error' => 'آیتم مورد نظر یافت نشد.'];
    }

    $newVal = ((int) $current === 1) ? 0 : 1;
    $up = $pdo->prepare("UPDATE gift_items SET is_active = ? WHERE id = ?");
    $up->execute([$newVal, $id]);

    return ['ok' => true, 'is_active' => $newVal];
}

/**
 * Fast administrative adjustment of a gift item's stock.
 *
 * @param int $id
 * @param int $stock
 * @return array{ok: bool, stock?: int, error?: string}
 */
function updateGiftItemStock(int $id, int $stock): array
{
    if ($stock < 0) {
        return ['ok' => false, 'error' => 'موجودی نمی‌تواند منفی باشد.'];
    }

    $pdo = db();
    $stmt = $pdo->prepare("UPDATE gift_items SET stock = ? WHERE id = ?");
    $stmt->execute([$stock, $id]);

    if ($stmt->rowCount() === 0) {
        $check = $pdo->prepare("SELECT id FROM gift_items WHERE id = ?");
        $check->execute([$id]);
        if (!$check->fetch()) {
            return ['ok' => false, 'error' => 'آیتم مورد نظر یافت نشد.'];
        }
    }

    return ['ok' => true, 'stock' => $stock];
}

/**
 * Fetch a single gift item by ID.
 */
function getAdminGiftItemById(int $id): ?array
{
    $stmt = db()->prepare("SELECT * FROM gift_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    return $item ?: null;
}

/**
 * Save (create or update) a gift/post-order catalog item.
 *
 * @param array $data Form fields
 * @param array|null $file $_FILES['image'] or null
 * @param int $adminId
 * @return array{ok: bool, id?: int, errors?: array}
 */
function saveGiftItem(array $data, ?array $file, int $adminId): array
{
    $id = (int) ($data['id'] ?? 0);
    $name = trim($data['name'] ?? '');
    $isActive = isset($data['is_active']) ? 1 : 0;
    $isGiftable = isset($data['is_giftable']) ? 1 : 0;
    $isPostOrderable = isset($data['is_post_orderable']) ? 1 : 0;
    $costPrice = (int) preg_replace('/\D/', '', $data['cost_price'] ?? '0');
    $postOrderPriceRaw = trim($data['post_order_price'] ?? '');
    $postOrderPrice = $postOrderPriceRaw === '' ? null : (int) preg_replace('/\D/', '', $postOrderPriceRaw);
    $stock = (int) ($data['stock'] ?? 0);

    $errors = [];
    if ($name === '') $errors[] = 'نام آیتم الزامی است.';
    if ($costPrice < 1) $errors[] = 'قیمت تمام‌شده معتبر وارد کنید.';
    if (!$isGiftable && !$isPostOrderable) $errors[] = 'حداقل یکی از دو حالت «قابل اهدا» یا «قابل فروش به‌عنوان پیشنهاد بعد از سبد» را انتخاب کنید.';
    if ($isPostOrderable && $postOrderPrice === null) $errors[] = 'برای آیتم قابل‌فروش، قیمت پیشنهاد بعد از سبد را وارد کنید.';
    if ($stock < 0) $errors[] = 'موجودی نمی‌تواند منفی باشد.';

    $existingItem = $id > 0 ? getAdminGiftItemById($id) : null;
    $newImageName = $existingItem['image'] ?? null;

    if ($file && !empty($file['name']) && ($file['error'] ?? 1) === UPLOAD_ERR_OK) {
        $uploadResult = $existingItem
            ? handleProductImageUpload($file, 'giftitem', $id, 'main')
            : handleProductImageUpload($file);
        if ($uploadResult['ok']) {
            if ($newImageName && file_exists(UPLOAD_DIR . $newImageName)) {
                @unlink(UPLOAD_DIR . $newImageName);
            }
            $newImageName = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }

    if (!empty($errors)) {
        return ['ok' => false, 'errors' => $errors];
    }

    $pdo = db();
    if ($existingItem) {
        $stmt = $pdo->prepare("UPDATE gift_items SET name=?, image=?, is_active=?, is_giftable=?, is_post_orderable=?, cost_price=?, post_order_price=?, stock=? WHERE id=?");
        $stmt->execute([$name, $newImageName, $isActive, $isGiftable, $isPostOrderable, $costPrice, $postOrderPrice, $stock, $id]);
        $itemId = $id;
    } else {
        $stmt = $pdo->prepare("INSERT INTO gift_items (name, image, is_active, is_giftable, is_post_orderable, cost_price, post_order_price, stock, created_by) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$name, $newImageName, $isActive, $isGiftable, $isPostOrderable, $costPrice, $postOrderPrice, $stock, $adminId]);
        $itemId = (int) $pdo->lastInsertId();

        if ($newImageName) {
            $renamed = renameUploadedImage($newImageName, 'giftitem', $itemId, 'main');
            if ($renamed) {
                $pdo->prepare("UPDATE gift_items SET image = ? WHERE id = ?")->execute([$renamed, $itemId]);
            }
        }
    }

    return ['ok' => true, 'id' => $itemId];
}

/**
 * Delete a gift item and its image.
 */
function deleteGiftItem(int $id): array
{
    $pdo = db();
    $stmt = $pdo->prepare("SELECT image FROM gift_items WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists(UPLOAD_DIR . $img)) {
        @unlink(UPLOAD_DIR . $img);
    }

    $pdo->prepare("DELETE FROM gift_items WHERE id = ?")->execute([$id]);
    return ['ok' => true];
}
