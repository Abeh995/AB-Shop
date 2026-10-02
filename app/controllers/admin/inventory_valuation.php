<?php
/**
 * Inventory Valuation & Capital Health Controller
 * Thin delegator computing stock capital valuation, unrealized margins, and dead inventory.
 */

$pageTitle = 'ارزش‌گذاری و سلامت سرمایه انبار';

$report = getInventoryValuationReport();

renderView('admin/inventory_valuation', compact('pageTitle', 'report'));
