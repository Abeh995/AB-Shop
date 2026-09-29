<?php
/**
 * Expense Edit & Create Controller
 * Thin controller managing individual expense records.
 */

$id = (int) ($_GET['id'] ?? 0);
$expense = $id > 0 ? getExpenseById($id) : null;

if ($id > 0 && !$expense) {
    setFlash('error', 'سند هزینه مورد نظر یافت نشد.');
    redirect('expenses.php');
}

$pageTitle = $expense ? 'ویرایش سند هزینه' : 'ثبت هزینه جدید';
$errors = [];

$suggestedCategories = [
    'خرید جوراب و کالای فروشگاه',
    'خرید جعبه هدیه و Gift Box',
    'ملزومات بسته‌بندی و پاکت پستی',
    'تجهیزات و سخت‌افزار',
    'هاست، سرور و دامنه',
    'سرویس‌های آنلاین و پنل پیامک',
    'تبلیغات، اینفلوئنسر و مارکتینگ',
    'کرایه حمل‌ونقل و پیک',
    'تعمیرات و نگهداری',
    'سایر مخارج عملیاتی',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $adminId = (int) ($_SESSION['admin_id'] ?? 0);
    $payload = $_POST;
    $payload['id'] = $id;

    $result = saveExpense($payload, $adminId);

    if ($result['ok']) {
        setFlash('success', $id > 0 ? 'سند هزینه با موفقیت به‌روزرسانی شد.' : 'سند هزینه جدید با موفقیت ثبت شد.');
        redirect('expenses.php');
    } else {
        $errors = $result['errors'] ?? ['خطا در ثبت هزینه.'];
        // Preserve user input
        $expense = array_merge($expense ?? [], [
            'title' => trim($_POST['title'] ?? ''),
            'amount' => trim($_POST['amount'] ?? ''),
            'expense_date' => trim($_POST['expense_date'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ]);
    }
}

renderView('admin/expense_edit', compact(
    'pageTitle',
    'id',
    'expense',
    'errors',
    'suggestedCategories'
));
