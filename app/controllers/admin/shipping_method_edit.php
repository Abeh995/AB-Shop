<?php
/**
 * Create/edit a single shipping method rule.
 * All persistence is delegated to ShippingService (Rule 7).
 */

$id = (int) ($_GET['id'] ?? 0);
$method = null;

if ($id) {
    $method = getShippingMethodById($id);
    if (!$method) {
        redirect('shipping_methods.php');
    }
}

$pageTitle = $method ? 'ویرایش روش ارسال' : 'روش ارسال جدید';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $res = saveShippingMethodRecord($id, $_POST);
    if ($res['ok']) {
        setFlash('success', $method ? 'روش ارسال به‌روزرسانی شد.' : 'روش ارسال اضافه شد.');
        redirect('shipping_methods.php');
    } else {
        $errors[] = $res['error'];
        $method = array_merge($method ?? ['id' => $id], $_POST);
    }
}

renderView('admin/shipping_method_edit', compact('pageTitle', 'method', 'errors'));
