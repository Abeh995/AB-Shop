<?php
/**
 * Financial Dashboard Controller
 * Thin controller managing store profitability reporting and date range presets.
 */

$pageTitle = 'داشبورد مالی و تحلیل سود';

// Range preset mapper
$range = trim($_GET['range'] ?? '');
$today = date('Y-m-d');

switch ($range) {
    case 'today':
        $startDate = $today;
        $endDate = $today;
        break;
    case '7days':
        $startDate = date('Y-m-d', strtotime('-6 days'));
        $endDate = $today;
        break;
    case '30days':
        $startDate = date('Y-m-d', strtotime('-29 days'));
        $endDate = $today;
        break;
    case 'this_year':
        $startDate = date('Y-01-01');
        $endDate = $today;
        break;
    case 'this_month':
    default:
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? $today;
        break;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !strtotime($startDate)) {
    $startDate = date('Y-m-01');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) || !strtotime($endDate)) {
    $endDate = $today;
}

// Ensure start date <= end date
if ($startDate > $endDate) {
    $temp = $startDate;
    $startDate = $endDate;
    $endDate = $temp;
}

$summary = getFinancialSummary($startDate, $endDate);

renderView('admin/finance_dashboard', compact(
    'pageTitle',
    'startDate',
    'endDate',
    'range',
    'summary'
));
