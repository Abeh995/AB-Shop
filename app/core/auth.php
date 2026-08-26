<?php
/**
 * Admin panel authentication with support for multiple admins and two access levels:
 *   super_admin: Full access, including admin account management
 *   admin:       Full access except admin account management
 */

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']);
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Only super_admin is allowed; other roles are redirected to the dashboard with an error.
 */
function requireSuperAdmin(): void
{
    requireAdmin();
    if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
        setFlash('error', 'شما دسترسی لازم برای این بخش را ندارید.');
        redirect('index.php');
    }
}

function isSuperAdmin(): bool
{
    return ($_SESSION['admin_role'] ?? '') === 'super_admin';
}

function attemptAdminLogin(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT id, username, password_hash, role, is_active FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && (int)$admin['is_active'] === 1 && password_verify($password, $admin['password_hash'])) {
        // Prevent session fixation.
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        return true;
    }

    // Small delay to slow down brute-force attempts.
    usleep(400000);
    return false;
}

function adminLogout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}
