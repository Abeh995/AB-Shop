<?php
/**
 * Expense Edit & Create Controller
 * Thin delegator managing individual expense records, receipt file uploads, and validation.
 */

$id = (int) ($_GET['id'] ?? 0);
$expense = $id > 0 ? getExpenseById($id) : null;

if ($id > 0 && !$expense) {
    setFlash('error', 'سند هزینه مورد نظر یافت نشد.');
    redirect('expenses.php');
}

$pageTitle = $expense ? 'ویرایش سند هزینه' : 'ثبت هزینه عملیاتی جدید';
$errors = [];

$categories = getExpenseCategories();
$paymentSources = getExpensePaymentSources();
$payees = getExpensePayees();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $adminId = (int) ($_SESSION['admin_id'] ?? 0);
    $payload = $_POST;
    $payload['id'] = $id;

    $receiptFile = $_FILES['receipt'] ?? null;
    $result = saveExpense($payload, $receiptFile, $adminId);

    if ($result['ok']) {
        setFlash('success', $id > 0 ? 'سند هزینه با موفقیت به‌روزرسانی شد.' : 'سند هزینه جدید با موفقیت ثبت شد.');
        redirect('expenses.php');
    } else {
        $errors = $result['errors'] ?? ['خطا در ثبت سند هزینه.'];
        // Preserve user input across validation failure
        $expense = array_merge($expense ?? [], [
            'title'          => trim($_POST['title'] ?? ''),
            'amount'         => trim($_POST['amount'] ?? ''),
            'expense_date'   => trim($_POST['expense_date'] ?? ''),
            'category'       => trim($_POST['category'] ?? ''),
            'payment_source' => trim($_POST['payment_source'] ?? 'کارت اصلی فروشگاه'),
            'payee'          => trim($_POST['payee'] ?? ''),
            'expense_nature' => trim($_POST['expense_nature'] ?? 'variable'),
            'description'    => trim($_POST['description'] ?? ''),
        ]);
    }
}

renderView('admin/expense_edit', compact(
    'pageTitle', 'id', 'expense', 'errors',
    'categories', 'paymentSources', 'payees'
));
