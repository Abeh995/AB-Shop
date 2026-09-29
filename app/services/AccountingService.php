<?php
/**
 * Store Accounting Service
 *
 * Handles order-level profitability calculations, financial summaries (P&L),
 * operational expense ledger management, and category distributions.
 *
 * Architectural Invariants:
 * - Read-only reporting over financial snapshots (Rule 2).
 * - All SQL queries for accounting and expense mutations are strictly encapsulated here (Rule 7).
 * - Controllers must remain thin delegators (< 80 lines).
 */

// ==========================================
// 1. Order Profitability & Financial Summary
// ==========================================

/**
 * Full revenue/cost/profit breakdown for one order.
 *
 * @return array{
 *   ok:bool, revenue:int, product_revenue:int, discount:int,
 *   post_order_revenue:int, shipping_revenue:int, product_cost:int,
 *   gift_cost:int, shipping_cost:int, total_cost:int, gross_profit:int,
 *   margin_percent:float, has_incomplete_cost_data:bool
 * }
 */
function getOrderProfitability(int $orderId): array
{
    $pdo = db();
    $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch();
    if (!$order) {
        return ['ok' => false];
    }

    $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$orderId]);

    $productRevenue = 0;
    $productCost = 0;
    $hasIncompleteCostData = false;

    foreach ($itemsStmt->fetchAll() as $it) {
        $productRevenue += (int) $it['line_total'];
        if ($it['unit_cost_price'] === null) {
            $hasIncompleteCostData = true;
        } else {
            $productCost += (int) $it['unit_cost_price'] * (int) $it['quantity'];
        }
    }

    $giftStmt = $pdo->prepare("SELECT * FROM order_gift_items WHERE order_id = ?");
    $giftStmt->execute([$orderId]);

    $postOrderRevenue = 0;
    $giftCost = 0;
    foreach ($giftStmt->fetchAll() as $gi) {
        $postOrderRevenue += (int) $gi['unit_selling_price'] * (int) $gi['quantity'];
        $giftCost += (int) $gi['unit_cost_price'] * (int) $gi['quantity'];
    }

    $shippingRevenue = (int) $order['shipping_cost'];
    $shippingCost = $order['shipping_actual_cost'] !== null
        ? (int) $order['shipping_actual_cost']
        : $shippingRevenue;

    $discount = (int) $order['discount_total'];
    $revenue = $productRevenue - $discount + $postOrderRevenue + $shippingRevenue;
    $totalCost = $productCost + $giftCost + $shippingCost;
    $grossProfit = $revenue - $totalCost;
    $marginPercent = ($revenue > 0) ? round(($grossProfit / $revenue) * 100, 1) : 0.0;

    return [
        'ok' => true,
        'revenue' => $revenue,
        'product_revenue' => $productRevenue,
        'discount' => $discount,
        'post_order_revenue' => $postOrderRevenue,
        'shipping_revenue' => $shippingRevenue,
        'product_cost' => $productCost,
        'gift_cost' => $giftCost,
        'shipping_cost' => $shippingCost,
        'total_cost' => $totalCost,
        'gross_profit' => $grossProfit,
        'margin_percent' => $marginPercent,
        'has_incomplete_cost_data' => $hasIncompleteCostData,
    ];
}

/**
 * High-performance store-wide financial summary for a date range.
 * Uses batch aggregation queries instead of N+1 individual queries.
 *
 * @param string $startDate 'Y-m-d'
 * @param string $endDate 'Y-m-d'
 */
function getFinancialSummary(string $startDate, string $endDate): array
{
    $pdo = db();

    // 1. Fetch orders in date range
    $ordersStmt = $pdo->prepare("
        SELECT id, discount_total, shipping_cost, shipping_actual_cost
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'
    ");
    $ordersStmt->execute([$startDate, $endDate]);
    $orders = $ordersStmt->fetchAll();

    $orderCount = count($orders);
    $totalRevenue = 0;
    $totalProductCost = 0;
    $totalGiftCost = 0;
    $totalShippingCost = 0;
    $totalProductRevenue = 0;
    $totalPostOrderRevenue = 0;
    $totalShippingRevenue = 0;
    $totalDiscount = 0;
    $ordersWithIncompleteCostData = 0;

    if ($orderCount > 0) {
        $orderIds = array_column($orders, 'id');
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

        // Batch aggregate order_items
        $itemsStmt = $pdo->prepare("
            SELECT 
                order_id,
                SUM(line_total) AS prod_rev,
                SUM(CASE WHEN unit_cost_price IS NOT NULL THEN unit_cost_price * quantity ELSE 0 END) AS prod_cost,
                SUM(CASE WHEN unit_cost_price IS NULL THEN 1 ELSE 0 END) AS missing_cost_count
            FROM order_items 
            WHERE order_id IN ($placeholders)
            GROUP BY order_id
        ");
        $itemsStmt->execute($orderIds);
        $itemsByOrder = [];
        foreach ($itemsStmt->fetchAll() as $row) {
            $itemsByOrder[(int)$row['order_id']] = $row;
        }

        // Batch aggregate order_gift_items
        $giftsStmt = $pdo->prepare("
            SELECT 
                order_id,
                SUM(unit_selling_price * quantity) AS gift_rev,
                SUM(unit_cost_price * quantity) AS gift_cost
            FROM order_gift_items 
            WHERE order_id IN ($placeholders)
            GROUP BY order_id
        ");
        $giftsStmt->execute($orderIds);
        $giftsByOrder = [];
        foreach ($giftsStmt->fetchAll() as $row) {
            $giftsByOrder[(int)$row['order_id']] = $row;
        }

        // Aggregate order financials
        foreach ($orders as $ord) {
            $oid = (int) $ord['id'];
            $discount = (int) $ord['discount_total'];
            $shippingRev = (int) $ord['shipping_cost'];
            $shippingCost = $ord['shipping_actual_cost'] !== null
                ? (int) $ord['shipping_actual_cost']
                : $shippingRev;

            $itemData = $itemsByOrder[$oid] ?? ['prod_rev' => 0, 'prod_cost' => 0, 'missing_cost_count' => 0];
            $giftData = $giftsByOrder[$oid] ?? ['gift_rev' => 0, 'gift_cost' => 0];

            if ((int)$itemData['missing_cost_count'] > 0) {
                $ordersWithIncompleteCostData++;
            }

            $totalProductRevenue += (int) $itemData['prod_rev'];
            $totalProductCost += (int) $itemData['prod_cost'];
            $totalPostOrderRevenue += (int) $giftData['gift_rev'];
            $totalGiftCost += (int) $giftData['gift_cost'];
            $totalShippingRevenue += $shippingRev;
            $totalShippingCost += $shippingCost;
            $totalDiscount += $discount;
        }

        $totalRevenue = ($totalProductRevenue - $totalDiscount) + $totalPostOrderRevenue + $totalShippingRevenue;
    }

    $totalCogs = $totalProductCost + $totalGiftCost + $totalShippingCost;
    $grossProfit = $totalRevenue - $totalCogs;
    $grossMarginPercent = ($totalRevenue > 0) ? round(($grossProfit / $totalRevenue) * 100, 1) : 0.0;

    // Operational expenses query
    $expenseStmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) 
        FROM expenses 
        WHERE status = 'active' AND expense_date BETWEEN ? AND ?
    ");
    $expenseStmt->execute([$startDate, $endDate]);
    $totalExpenses = (int) $expenseStmt->fetchColumn();

    // Expenses breakdown by category with percentage share
    $expenseByCategoryStmt = $pdo->prepare("
        SELECT category, SUM(amount) AS total, COUNT(*) AS entry_count
        FROM expenses
        WHERE status = 'active' AND expense_date BETWEEN ? AND ?
        GROUP BY category 
        ORDER BY total DESC
    ");
    $expenseByCategoryStmt->execute([$startDate, $endDate]);
    $rawCategoryExpenses = $expenseByCategoryStmt->fetchAll();

    $expensesByCategory = [];
    foreach ($rawCategoryExpenses as $catRow) {
        $catTotal = (int) $catRow['total'];
        $catShare = ($totalExpenses > 0) ? round(($catTotal / $totalExpenses) * 100, 1) : 0.0;
        $expensesByCategory[] = [
            'category' => $catRow['category'],
            'total' => $catTotal,
            'entry_count' => (int) $catRow['entry_count'],
            'share_percent' => $catShare,
        ];
    }

    $netProfit = $grossProfit - $totalExpenses;
    $netMarginPercent = ($totalRevenue > 0) ? round(($netProfit / $totalRevenue) * 100, 1) : 0.0;

    return [
        'order_count' => $orderCount,
        'total_revenue' => $totalRevenue,
        'product_revenue' => $totalProductRevenue,
        'discount_total' => $totalDiscount,
        'post_order_revenue' => $totalPostOrderRevenue,
        'shipping_revenue' => $totalShippingRevenue,
        'product_cost' => $totalProductCost,
        'gift_cost' => $totalGiftCost,
        'shipping_cost' => $totalShippingCost,
        'total_cogs' => $totalCogs,
        'gross_profit' => $grossProfit,
        'gross_margin_percent' => $grossMarginPercent,
        'total_expenses' => $totalExpenses,
        'net_profit' => $netProfit,
        'net_margin_percent' => $netMarginPercent,
        'orders_with_incomplete_cost_data' => $ordersWithIncompleteCostData,
        'expenses_by_category' => $expensesByCategory,
    ];
}

// ==========================================
// 2. Operational Expenses Ledger (CRUD)
// ==========================================

/**
 * Fetch paginated and filtered list of operational expenses.
 *
 * @param array $filters ['q' => string, 'category' => string, 'start_date' => string, 'end_date' => string]
 * @param int $page
 * @param int $perPage
 * @return array{items: array, total_count: int, total_pages: int, current_page: int, per_page: int, total_amount: int}
 */
function getExpensesList(array $filters = [], int $page = 1, int $perPage = 25): array
{
    $pdo = db();
    $page = max(1, $page);
    $perPage = max(1, min(100, $perPage));
    $offset = ($page - 1) * $perPage;

    $where = ["e.status = 'active'"];
    $params = [];

    $category = trim($filters['category'] ?? '');
    if ($category !== '') {
        $where[] = 'e.category = ?';
        $params[] = $category;
    }

    $startDate = trim($filters['start_date'] ?? '');
    if ($startDate !== '') {
        $where[] = 'e.expense_date >= ?';
        $params[] = $startDate;
    }

    $endDate = trim($filters['end_date'] ?? '');
    if ($endDate !== '') {
        $where[] = 'e.expense_date <= ?';
        $params[] = $endDate;
    }

    $search = trim($filters['q'] ?? '');
    if ($search !== '') {
        $where[] = '(e.title LIKE ? OR e.description LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $whereSql = implode(' AND ', $where);

    // Total filtered sum & count
    $statStmt = $pdo->prepare("
        SELECT COUNT(*) AS total_count, COALESCE(SUM(e.amount), 0) AS total_amount
        FROM expenses e
        WHERE $whereSql
    ");
    $statStmt->execute($params);
    $statRow = $statStmt->fetch();

    $totalCount = (int) ($statRow['total_count'] ?? 0);
    $totalAmount = (int) ($statRow['total_amount'] ?? 0);
    $totalPages = (int) ceil($totalCount / $perPage);

    // Listing query
    $stmt = $pdo->prepare("
        SELECT e.*, a.username AS admin_username
        FROM expenses e 
        LEFT JOIN admins a ON a.id = e.created_by
        WHERE $whereSql 
        ORDER BY e.expense_date DESC, e.id DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    return [
        'items' => $items,
        'total_count' => $totalCount,
        'total_pages' => $totalPages,
        'current_page' => $page,
        'per_page' => $perPage,
        'total_amount' => $totalAmount,
    ];
}

/**
 * Fetch a single expense record by ID.
 */
function getExpenseById(int $id): ?array
{
    $stmt = db()->prepare("SELECT * FROM expenses WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Create or update an operational expense entry.
 *
 * @param array $data Form payload
 * @param int $adminId Current admin ID
 * @return array{ok: bool, id?: int, errors?: array}
 */
function saveExpense(array $data, int $adminId): array
{
    $pdo = db();
    $id = (int) ($data['id'] ?? 0);
    $title = trim($data['title'] ?? '');
    $amount = (int) preg_replace('/\D/', '', $data['amount'] ?? '0');
    $expenseDate = trim($data['expense_date'] ?? '');
    $category = trim($data['category'] ?? '');
    $description = trim($data['description'] ?? '');

    $errors = [];
    if ($title === '') $errors[] = 'عنوان هزینه الزامی است.';
    if ($amount < 1) $errors[] = 'مبلغ معتبر به تومان وارد کنید.';
    if ($category === '') $errors[] = 'دسته‌بندی هزینه الزامی است.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expenseDate) || !strtotime($expenseDate)) {
        $errors[] = 'تاریخ معتبر انتخاب کنید.';
    }

    if (!empty($errors)) {
        return ['ok' => false, 'errors' => $errors];
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE expenses 
            SET title = ?, amount = ?, expense_date = ?, category = ?, description = ? 
            WHERE id = ?
        ");
        $stmt->execute([$title, $amount, $expenseDate, $category, $description ?: null, $id]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO expenses (title, amount, expense_date, category, description, created_by, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([$title, $amount, $expenseDate, $category, $description ?: null, $adminId]);
        $id = (int) $pdo->lastInsertId();
    }

    return ['ok' => true, 'id' => $id];
}

/**
 * Soft-delete / archive an expense entry.
 */
function archiveExpense(int $id): array
{
    $pdo = db();
    $stmt = $pdo->prepare("UPDATE expenses SET status = 'archived' WHERE id = ?");
    $stmt->execute([$id]);
    return ['ok' => true];
}

/**
 * Fetch distinct active expense categories list.
 */
function getExpenseCategories(): array
{
    return db()->query("
        SELECT DISTINCT category 
        FROM expenses 
        WHERE status = 'active' 
        ORDER BY category ASC
    ")->fetchAll(PDO::FETCH_COLUMN);
}
