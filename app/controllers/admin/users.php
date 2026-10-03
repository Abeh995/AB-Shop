<?php
/**
 * Admin account management & audit logging — accessible strictly to super_admin.
 * All business logic, DB mutations, and audit tracking are delegated to AdminUserService (Rule 7).
 */

requireSuperAdmin();
$pageTitle = 'مدیران سایت و سطوح دسترسی';
$currentAdminId = (int) $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'create') {
        $res = createAdminUserRecord([
            'username' => $_POST['username'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? 'admin',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'actor_id' => $currentAdminId,
        ]);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'حساب مدیر جدید با موفقیت اضافه شد.' : $res['error']);
    } elseif ($action === 'update') {
        $res = updateAdminUserRecord($id, $_POST, $currentAdminId);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'مشخصات مدیر با موفقیت به‌روزرسانی شد.' : $res['error']);
    } elseif ($action === 'toggle_active') {
        $res = toggleAdminUserActiveStatus($id, $currentAdminId);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'وضعیت دسترسی حساب به‌روزرسانی شد.' : $res['error']);
    } elseif ($action === 'change_password') {
        $res = changeAdminUserPasswordRecord($id, $_POST['password'] ?? '', $currentAdminId);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'رمز عبور حساب با موفقیت تغییر کرد.' : $res['error']);
    } elseif ($action === 'delete') {
        $res = deleteAdminUserRecord($id, $currentAdminId);
        setFlash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'حساب مدیر با موفقیت حذف شد.' : $res['error']);
    }

    redirect('users.php');
}

$admins = getAdminUsersList();
$metrics = getAdminUsersMetrics();
$recentLogs = getRecentAdminAuditLogs(12);

renderView('admin/users', compact('pageTitle', 'admins', 'metrics', 'recentLogs', 'currentAdminId'));
