<?php
/**
 * Admin panel authentication — supports multiple admins and two access levels:
 *   super_admin: full access + managing other admin accounts
 *   admin:       full access except managing admin accounts
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
 * Only super_admin is allowed; otherwise redirects to the dashboard with an error message.
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
        // Prevent session fixation
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];

        // Track last login session and security audit trail
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        try {
            $upStmt = db()->prepare('UPDATE admins SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
            $upStmt->execute([$clientIp, $admin['id']]);
        } catch (\Throwable $e) {
            // Non-breaking fallback if migration not yet applied
        }

        if (function_exists('logAdminAction')) {
            logAdminAction((int)$admin['id'], 'login', 'admin', (int)$admin['id'], 'ورود موفق به پنل مدیریت');
        }

        return true;
    }

    // Small delay to slow down brute-force attacks
    usleep(400000);
    return false;
}

function adminLogout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}
