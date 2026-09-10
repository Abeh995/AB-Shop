<?php
/**
 * Financial dashboard — store-wide revenue/cost/profit summary for a
 * chosen date range, backed entirely by AccountingService's read-only
 * queries over already-snapshotted order/expense data.
 */

$pageTitle = 'داشبورد مالی';

// Default range: the current calendar month
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !strtotime($startDate)) {
    $startDate = date('Y-m-01');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) || !strtotime($endDate)) {
    $endDate = date('Y-m-d');
}

$summary = getFinancialSummary($startDate, $endDate);

renderView('admin/finance_dashboard', compact('pageTitle', 'startDate', 'endDate', 'summary'));
