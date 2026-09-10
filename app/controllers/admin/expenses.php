<?php
/**
 * General expense ledger — list, filter, soft delete (archive).
 */

$pageTitle = 'هزینه‌ها';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'archive') {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    db()->prepare("UPDATE expenses SET status = 'archived' WHERE id = ?")->execute([$id]);
    setFlash('success', 'هزینه بایگانی شد.');
    redirect('expenses.php');
}

$category = trim($_GET['category'] ?? '');
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$search = trim($_GET['q'] ?? '');

$where = "status = 'active'";
$params = [];
if ($category !== '') {
    $where .= ' AND category = ?';
    $params[] = $category;
}
if ($startDate !== '') {
    $where .= ' AND expense_date >= ?';
    $params[] = $startDate;
}
if ($endDate !== '') {
    $where .= ' AND expense_date <= ?';
    $params[] = $endDate;
}
if ($search !== '') {
    $where .= ' AND (title LIKE ? OR description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$stmt = db()->prepare("
    SELECT e.*, a.username AS admin_username
    FROM expenses e LEFT JOIN admins a ON a.id = e.created_by
    WHERE $where ORDER BY e.expense_date DESC, e.id DESC
");
$stmt->execute($params);
$expenses = $stmt->fetchAll();

$totalStmt = db()->prepare("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE $where");
$totalStmt->execute($params);
$totalAmount = (int) $totalStmt->fetchColumn();

$categories = db()->query("SELECT DISTINCT category FROM expenses WHERE status = 'active' ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

renderView('admin/expenses', compact('pageTitle', 'expenses', 'totalAmount', 'category', 'startDate', 'endDate', 'search', 'categories'));
