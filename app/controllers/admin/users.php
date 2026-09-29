<?php
/**
 * Admin account management — accessible only to super_admin.
 * All business logic and SQL queries are delegated to AdminUserService (Rule 7).
 */

requireSuperAdmin();
$pageTitle = 'مدیریت ادمین‌ها';
$currentAdminId = (int) $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = ($_POST['role'] ?? 'admin') === 'super_admin' ? 'super_admin' : 'admin';

        $res = createAdminUserRecord($username, $fullName, $password, $role);
        if ($res['ok']) {
            setFlash('success', 'ادمین جدید با موفقیت اضافه شد.');
        } else {
            setFlash('error', $res['error']);
        }
    } elseif ($action === 'toggle_active') {
        $id = (int) ($_POST['id'] ?? 0);
        $res = toggleAdminUserActiveStatus($id, $currentAdminId);
        if ($res['ok']) {
            setFlash('success', 'وضعیت حساب به‌روزرسانی شد.');
        } else {
            setFlash('error', $res['error']);
        }
    } elseif ($action === 'change_password') {
        $id = (int) ($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';
        $res = changeAdminUserPasswordRecord($id, $password);
        if ($res['ok']) {
            setFlash('success', 'رمز عبور با موفقیت تغییر کرد.');
        } else {
            setFlash('error', $res['error']);
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $res = deleteAdminUserRecord($id, $currentAdminId);
        if ($res['ok']) {
            setFlash('success', 'حساب ادمین با موفقیت حذف شد.');
        } else {
            setFlash('error', $res['error']);
        }
    }
    redirect('users.php');
}

$admins = getAdminUsersList();

renderView('admin/users', compact('pageTitle', 'admins', 'currentAdminId'));
