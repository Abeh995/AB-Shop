<?php
/**
 * Bulk price update tool. Two-phase flow:
 *   1) POST action=preview  — compute the new price for every selected
 *      product, without writing anything, and show the admin a table to
 *      review before committing.
 *   2) POST action=apply    — re-submits the exact same selection/settings
 *      (via hidden fields on the preview screen) and actually applies the
 *      change through applyBulkPriceChange().
 * Selection is a plain multi-select list of product ids — not tied to a
 * category, per the "may be four completely unrelated products" requirement.
 */

$pageTitle = 'تغییر قیمت گروهی';
$adminId = (int) ($_SESSION['admin_id'] ?? 0);

$preview = null;
$applyResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $productIds = array_values(array_unique(array_map('intval', $_POST['product_ids'] ?? [])));
    $field = ($_POST['field'] ?? '') === 'cost_price' ? 'cost_price' : 'sale_price';
    $method = in_array($_POST['method'] ?? '', ['fixed_amount', 'percentage', 'direct_value'], true)
        ? $_POST['method'] : 'fixed_amount';
    $value = (float) str_replace(',', '', $_POST['value'] ?? '0');
    $reason = trim($_POST['reason'] ?? '') ?: null;

    if ($action === 'preview' && $productIds) {
        $column = $field === 'cost_price' ? 'cost_price' : 'price';
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = db()->prepare("SELECT id, name, sku, $column AS current_value FROM products WHERE id IN ($placeholders) ORDER BY name ASC");
        $stmt->execute($productIds);
        $rows = $stmt->fetchAll();

        $previewRows = [];
        foreach ($rows as $row) {
            $current = $row['current_value'] !== null ? (int) $row['current_value'] : null;
            $computed = computeNewPrice($method, $current, $value);
            $previewRows[] = [
                'id' => $row['id'], 'name' => $row['name'], 'sku' => $row['sku'],
                'current_value' => $current, 'new_value' => $computed['new_value'],
                'change_amount' => $computed['change_amount'], 'change_percentage' => $computed['change_percentage'],
            ];
        }
        $preview = [
            'product_ids' => $productIds, 'field' => $field, 'method' => $method,
            'value' => $value, 'reason' => $reason, 'rows' => $previewRows,
        ];
    } elseif ($action === 'apply' && $productIds) {
        $result = applyBulkPriceChange($productIds, $field, $method, $value, $adminId, $reason);
        $msg = toPersianDigits((string) $result['succeeded']) . ' محصول با موفقیت به‌روزرسانی شد.';
        if ($result['skipped']) {
            $msg .= ' ' . toPersianDigits((string) count($result['skipped'])) . ' محصول رد شد.';
        }
        setFlash('success', $msg);
        redirect('pricing.php');
    }
}

$search = trim($_GET['q'] ?? '');
$where = '1=1';
$params = [];
if ($search !== '') {
    $where .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
$stmt = db()->prepare("SELECT p.id, p.name, p.sku, p.price, p.cost_price, c.name AS category_name
                        FROM products p JOIN categories c ON c.id = p.category_id
                        WHERE $where ORDER BY p.name ASC");
$stmt->execute($params);
$products = $stmt->fetchAll();

$recentOps = db()->query("
    SELECT bo.*, a.username AS admin_username
    FROM bulk_price_operations bo
    LEFT JOIN admins a ON a.id = bo.admin_id
    ORDER BY bo.created_at DESC LIMIT 10
")->fetchAll();

renderView('admin/pricing', compact('pageTitle', 'search', 'products', 'preview', 'recentOps'));
