<?php
/**
 * UI Kit & Component Showcase Controller (admin/ui-kit.php)
 * Accessible to authenticated admin users. Strictly adheres to Rule 7 (Controller <80 lines).
 */

requireLogin();
requireAdmin();

$pageTitle = 'کتابخانه مؤلفه‌ها و راهنمای استایل (UI Kit v2)';
$activeTone = in_array($_GET['tone'] ?? '', ['brand', 'blue', 'sky', 'emerald', 'amber', 'rose', 'purple', 'teal', 'slate'], true)
    ? $_GET['tone']
    : 'brand';
$pageTone = $activeTone;

$tones = ['brand', 'blue', 'sky', 'emerald', 'amber', 'rose', 'purple', 'teal', 'slate'];

// Sample data for data_table demonstration
$sampleTableRows = [
    ['id' => 101, 'code' => 'SKU-8821', 'name' => 'جوراب کالج پنبه‌ای اعلا', 'category' => 'کالج', 'stock' => 45, 'price' => 58000, 'cost_price' => 32000, 'status' => ['label' => 'موجود', 'state' => 'success', 'dot' => true], 'date' => '۱۴۰۳/۰۷/۱۵', 'search' => '101 SKU-8821 کالج موجود'],
    ['id' => 102, 'code' => 'SKU-9940', 'name' => 'جوراب ساقدار پشمی گرم زمستانه', 'category' => 'ساقدار', 'stock' => 4, 'price' => 95000, 'cost_price' => 62000, 'status' => ['label' => 'موجودی اندک', 'state' => 'warning', 'dot' => true], 'date' => '۱۴۰۳/۰۷/۱۴', 'search' => '102 SKU-9940 ساقدار موجودی اندک'],
    ['id' => 103, 'code' => 'SKU-4412', 'name' => 'جوراب نیم‌ساق نانو مشکی', 'category' => 'نیم‌ساق', 'stock' => 0, 'price' => 72000, 'cost_price' => 45000, 'status' => ['label' => 'ناموجود', 'state' => 'danger', 'dot' => true], 'date' => '۱۴۰۳/۰۷/۱۰', 'search' => '103 SKU-4412 نیم‌ساق ناموجود'],
    ['id' => 104, 'code' => 'SKU-7751', 'name' => 'جوراب فانتزی طرح گربه', 'category' => 'فانتزی', 'stock' => 18, 'price' => 65000, 'cost_price' => 38000, 'status' => ['label' => 'پیش‌نویس', 'state' => 'muted', 'dot' => false], 'date' => '۱۴۰۳/۰۷/۰۸', 'search' => '104 SKU-7751 فانتزی پیش‌نویس'],
];

// Sample multi-column 12-column test rows
$stress12Cols = array_map(fn($c) => [
    'key' => 'col_' . $c,
    'label' => 'ستون ' . toPersianDigits((string)$c),
    'priority' => ($c <= 2 ? 1 : ($c <= 5 ? 2 : 3)),
], range(1, 12));
$stress12Rows = [array_combine(array_column($stress12Cols, 'key'), array_map(fn($k) => 'داده ' . $k, array_column($stress12Cols, 'key')))];

renderView('admin/ui-kit', compact(
    'pageTitle', 'pageTone', 'activeTone', 'tones', 'sampleTableRows', 'stress12Cols', 'stress12Rows'
));
