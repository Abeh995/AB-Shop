<?php
/**
 * Financial Dashboard Controller
 * Thin delegator managing store profitability reporting and view hydration.
 */

$pageTitle = 'داشبورد مالی و تحلیل سود';

[$startDate, $endDate, $range] = resolveFinancialDateRange(
    $_GET['range'] ?? '',
    $_GET['start_date'] ?? null,
    $_GET['end_date'] ?? null
);

// Stream CSV export if requested
if (($_GET['export'] ?? '') === 'csv') {
    exportFinancialCsv($startDate, $endDate);
}

// Hydrate analytical datasets
$summary = getFinancialSummary($startDate, $endDate);
$dailyTrends = getFinancialDailyTrends($startDate, $endDate);
$topProducts = getTopProfitProducts($startDate, $endDate, 5);
$paymentBreakdown = getPaymentMethodBreakdown($startDate, $endDate);
$shippingBreakdown = getShippingMethodFinancialBreakdown($startDate, $endDate);
$incompleteProducts = !empty($summary['orders_with_incomplete_cost_data'])
    ? getIncompleteCostProducts($startDate, $endDate, 5)
    : [];

renderView('admin/finance_dashboard', compact(
    'pageTitle', 'startDate', 'endDate', 'range', 'summary',
    'dailyTrends', 'topProducts', 'paymentBreakdown', 'shippingBreakdown', 'incompleteProducts'
));
