<?php
/**
 * Create/edit a single expense record.
 */

$id = (int) ($_GET['id'] ?? 0);
$expense = null;

if ($id) {
    $stmt = db()->prepare("SELECT * FROM expenses WHERE id = ?");
    $stmt->execute([$id]);
    $expense = $stmt->fetch();
    if (!$expense) redirect('expenses.php');
}

$pageTitle = $expense ? 'ویرایش هزینه' : 'ثبت هزینه جدید';
$errors = [];

// Suggested categories for the <datalist> — the column itself is free text,
// so this is guidance, not an enforced list.
$suggestedCategories = [
    'خرید جوراب/محصول', 'خرید Gift Box', 'بسته‌بندی', 'تجهیزات', 'هاست', 'دامنه',
    'سرویس‌های آنلاین', 'تبلیغات', 'حمل‌ونقل', 'خدمات', 'تعمیرات', 'سایر',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $title = trim($_POST['title'] ?? '');
    $amount = (int) preg_replace('/\D/', '', $_POST['amount'] ?? '0');
    $expenseDate = trim($_POST['expense_date'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') $errors[] = 'عنوان هزینه الزامی است.';
    if ($amount < 1) $errors[] = 'مبلغ معتبر وارد کنید.';
    if ($category === '') $errors[] = 'دسته‌بندی الزامی است.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expenseDate) || !strtotime($expenseDate)) {
        $errors[] = 'تاریخ معتبر وارد کنید.';
    }

    if (empty($errors)) {
        if ($expense) {
            $stmt = db()->prepare("UPDATE expenses SET title=?, amount=?, expense_date=?, category=?, description=? WHERE id=?");
            $stmt->execute([$title, $amount, $expenseDate, $category, $description ?: null, $id]);
            setFlash('success', 'هزینه به‌روزرسانی شد.');
        } else {
            $adminId = (int) ($_SESSION['admin_id'] ?? 0);
            $stmt = db()->prepare("INSERT INTO expenses (title, amount, expense_date, category, description, created_by) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$title, $amount, $expenseDate, $category, $description ?: null, $adminId]);
            setFlash('success', 'هزینه ثبت شد.');
        }
        redirect('expenses.php');
    }

    $expense = [
        'id' => $id, 'title' => $title, 'amount' => $amount,
        'expense_date' => $expenseDate, 'category' => $category, 'description' => $description,
    ];
}

renderView('admin/expense_edit', compact('pageTitle', 'expense', 'errors', 'suggestedCategories'));
