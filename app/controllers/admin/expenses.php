<?php
/**
 * Expenses Ledger Controller
 * Thin controller managing expense listing, filtering, pagination, and archiving.
 */

$pageTitle = 'هزینه‌های عملیاتی';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'archive') {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        archiveExpense($id);
        setFlash('success', 'سند هزینه با موفقیت بایگانی شد.');
    }
    $returnUrl = $_POST['return_url'] ?? 'expenses.php';
    redirect($returnUrl);
}

$category = trim($_GET['category'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$search = trim($_GET['q'] ?? '');
$page = (int) ($_GET['page'] ?? 1);

$filters = [
    'category' => $category,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'q' => $search,
];

$expensesData = getExpensesList($filters, $page, 20);
$categories = getExpenseCategories();

renderView('admin/expenses', compact(
    'pageTitle',
    'expensesData',
    'categories',
    'category',
    'startDate',
    'endDate',
    'search'
));
