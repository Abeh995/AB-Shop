<?php

requireSuperAdmin();
$pageTitle = 'مدیریت ادمین‌ها';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = ($_POST['role'] ?? 'admin') === 'super_admin' ? 'super_admin' : 'admin';

        if (mb_strlen($username) < 3) {
            setFlash('error', 'نام کاربری باید حداقل ۳ کاراکتر باشد.');
        } elseif (mb_strlen($password) < 8) {
            setFlash('error', 'رمز عبور باید حداقل ۸ کاراکتر باشد.');
        } else {
            $check = db()->prepare("SELECT id FROM admins WHERE username = ?");
            $check->execute([$username]);
            if ($check->fetch()) {
                setFlash('error', 'این نام کاربری قبلاً استفاده شده است.');
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                db()->prepare("INSERT INTO admins (username, password_hash, role, full_name, is_active) VALUES (?,?,?,?,1)")
                    ->execute([$username, $hash, $role, $fullName ?: null]);
                setFlash('success', 'ادمین جدید با موفقیت اضافه شد.');
            }
        }
    } elseif ($action === 'toggle_active') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) $_SESSION['admin_id']) {
            setFlash('error', 'نمی‌توانید حساب خودتان را غیرفعال کنید.');
        } else {
            db()->prepare("UPDATE admins SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
            setFlash('success', 'وضعیت حساب به‌روزرسانی شد.');
        }
    } elseif ($action === 'change_password') {
        $id = (int) ($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';
        if (mb_strlen($password) < 8) {
            setFlash('error', 'رمز عبور باید حداقل ۸ کاراکتر باشد.');
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            db()->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")->execute([$hash, $id]);
            setFlash('success', 'رمز عبور با موفقیت تغییر کرد.');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) $_SESSION['admin_id']) {
            setFlash('error', 'نمی‌توانید حساب خودتان را حذف کنید.');
        } else {
            $superCount = (int) db()->query("SELECT COUNT(*) FROM admins WHERE role = 'super_admin'")->fetchColumn();
            $target = db()->prepare("SELECT role FROM admins WHERE id = ?");
            $target->execute([$id]);
            $targetRole = $target->fetchColumn();
            if ($targetRole === 'super_admin' && $superCount <= 1) {
                setFlash('error', 'باید حداقل یک مدیر کل در سیستم باقی بماند.');
            } else {
                db()->prepare("DELETE FROM admins WHERE id = ?")->execute([$id]);
                setFlash('success', 'حساب ادمین حذف شد.');
            }
        }
    }

    redirect('users.php');
}

$admins = db()->query("SELECT id, username, full_name, role, is_active, created_at FROM admins ORDER BY created_at ASC")->fetchAll();

renderView('admin/users', compact('pageTitle', 'admins'));
