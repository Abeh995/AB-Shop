<?php
/**
 * Expenses Ledger Controller
 * Thin controller managing expense listing, filtering, pagination, CSV export, and archiving.
 */

$pageTitle = 'دفتر هزینه‌های عملیاتی';

// Handle POST actions (archive / restore)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    $returnUrl = $_POST['return_url'] ?? 'expenses.php';

    if ($id > 0) {
        if ($action === 'archive') {
            archiveExpense($id);
            setFlash('success', 'سند هزینه با موفقیت به بخش بایگانی منتقل شد.');
        } elseif ($action === 'restore') {
            restoreExpense($id);
            setFlash('success', 'سند هزینه با موفقیت از بایگانی بازیابی و فعال شد.');
        }
    }
    redirect($returnUrl);
}

// Resolve dates (supports preset chips: today, 7days, 30days, this_month, last_month, this_year)
$range = trim($_GET['range'] ?? '');
$customStart = !empty($_GET['start_date']) ? trim($_GET['start_date']) : null;
$customEnd = !empty($_GET['end_date']) ? trim($_GET['end_date']) : null;

$startDate = '';
$endDate = '';
if ($range !== '' || $customStart !== null || $customEnd !== null) {
    [$startDate, $endDate, $range] = resolveFinancialDateRange($range, $customStart, $customEnd);
}

$status = in_array($_GET['status'] ?? '', ['active', 'archived', 'all'], true) ? $_GET['status'] : 'active';
$category = trim($_GET['category'] ?? '');
$paymentSource = trim($_GET['payment_source'] ?? '');
$expenseNature = trim($_GET['expense_nature'] ?? '');
$search = trim($_GET['q'] ?? '');
$sort = in_array($_GET['sort'] ?? '', ['date_desc', 'date_asc', 'amount_desc', 'amount_asc'], true) ? $_GET['sort'] : 'date_desc';
$page = max(1, (int) ($_GET['page'] ?? 1));

$filters = [
    'status'         => $status,
    'category'       => $category,
    'payment_source' => $paymentSource,
    'expense_nature' => $expenseNature,
    'start_date'     => $startDate,
    'end_date'       => $endDate,
    'q'              => $search,
];

// Handle CSV export request
if (($_GET['export'] ?? '') === 'csv') {
    exportExpensesCsv($filters);
}

// Hydrate datasets
$expensesData = getExpensesList($filters, $page, 20, $sort);
$summaryMetrics = getExpenseSummaryMetrics($filters);
$categories = getExpenseCategories();
$paymentSources = getExpensePaymentSources();

renderView('admin/expenses', compact(
    'pageTitle', 'expensesData', 'summaryMetrics', 'categories', 'paymentSources',
    'status', 'category', 'paymentSource', 'expenseNature', 'startDate', 'endDate',
    'range', 'search', 'sort', 'page'
));
