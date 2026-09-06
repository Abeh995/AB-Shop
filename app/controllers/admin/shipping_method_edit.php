<?php
/**
 * Create/edit a single shipping method rule.
 */

$id = (int) ($_GET['id'] ?? 0);
$method = null;

if ($id) {
    $stmt = db()->prepare("SELECT * FROM shipping_methods WHERE id = ?");
    $stmt->execute([$id]);
    $method = $stmt->fetch();
    if (!$method) redirect('shipping_methods.php');
}

$pageTitle = $method ? 'ویرایش روش ارسال' : 'روش ارسال جدید';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $matchType = ($_POST['match_type'] ?? '') === 'province_contains' ? 'province_contains' : 'default';
    $matchValue = trim($_POST['match_value'] ?? '');
    $cost = (int) preg_replace('/\D/', '', $_POST['cost'] ?? '0');
    $freeAboveRaw = trim($_POST['free_above_amount'] ?? '');
    $freeAbove = $freeAboveRaw === '' ? null : (int) preg_replace('/\D/', '', $freeAboveRaw);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') $errors[] = 'نام روش ارسال الزامی است.';
    if ($matchType === 'province_contains' && $matchValue === '') {
        $errors[] = 'برای «تطبیق با استان»، متن استان را وارد کنید (مثلا تهران).';
    }
    if ($cost < 0) $errors[] = 'هزینه نمی‌تواند منفی باشد.';

    if (empty($errors)) {
        if ($method) {
            $stmt = db()->prepare("UPDATE shipping_methods SET name=?, description=?, match_type=?, match_value=?, cost=?, free_above_amount=?, is_active=? WHERE id=?");
            $stmt->execute([$name, $description ?: null, $matchType, $matchType === 'province_contains' ? $matchValue : null, $cost, $freeAbove, $isActive, $id]);
            setFlash('success', 'روش ارسال به‌روزرسانی شد.');
        } else {
            $maxSortStmt = db()->query("SELECT COALESCE(MAX(sort_order), 0) FROM shipping_methods");
            $nextSort = ((int) $maxSortStmt->fetchColumn()) + 1;
            $stmt = db()->prepare("INSERT INTO shipping_methods (name, description, match_type, match_value, cost, free_above_amount, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$name, $description ?: null, $matchType, $matchType === 'province_contains' ? $matchValue : null, $cost, $freeAbove, $isActive, $nextSort]);
            setFlash('success', 'روش ارسال اضافه شد.');
        }
        redirect('shipping_methods.php');
    }

    $method = [
        'id' => $id, 'name' => $name, 'description' => $description, 'match_type' => $matchType,
        'match_value' => $matchValue, 'cost' => $cost, 'free_above_amount' => $freeAbove, 'is_active' => $isActive,
    ];
}

renderView('admin/shipping_method_edit', compact('pageTitle', 'method', 'errors'));
