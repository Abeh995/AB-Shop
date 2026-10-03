<?php
/**
 * Admin User Management & Audit Trail Service
 *
 * Encapsulates all administrative account operations: listing, creation,
 * profile updating, password modification, activation toggles, safe deletion,
 * and security audit logging.
 *
 * Invariants:
 * - SuperAdmin access check is verified prior to execution (Rule 4).
 * - Password hashes use secure standard BCRYPT.
 * - Self-deactivation and self-deletion are strictly blocked.
 * - The last remaining super_admin cannot be deleted, demoted, or deactivated.
 * - All state-changing administrative operations produce an immutable audit log entry.
 */

/**
 * Fetch all admin user accounts with contact and session status.
 */
function getAdminUsersList(): array
{
    try {
        return db()->query("
            SELECT id, username, full_name, phone, email, role, is_active, last_login_at, last_login_ip, created_at 
            FROM admins 
            ORDER BY (role = 'super_admin') DESC, id ASC
        ")->fetchAll();
    } catch (\Throwable $e) {
        // Fallback if migration 026 not yet executed
        return db()->query("
            SELECT id, username, full_name, NULL AS phone, NULL AS email, role, is_active, NULL AS last_login_at, NULL AS last_login_ip, created_at 
            FROM admins 
            ORDER BY (role = 'super_admin') DESC, id ASC
        ")->fetchAll();
    }
}

/**
 * Fetch a single admin user record by ID.
 */
function getAdminUserById(int $id): ?array
{
    try {
        $stmt = db()->prepare("
            SELECT id, username, full_name, phone, email, role, is_active, last_login_at, last_login_ip, created_at
            FROM admins 
            WHERE id = ? 
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (\Throwable $e) {
        $stmt = db()->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

/**
 * Aggregate summary metrics for the Admin Management workspace.
 */
function getAdminUsersMetrics(): array
{
    $pdo = db();
    $total = (int) $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    $active = (int) $pdo->query("SELECT COUNT(*) FROM admins WHERE is_active = 1")->fetchColumn();
    $superAdmins = (int) $pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'super_admin'")->fetchColumn();

    $lastLogin = null;
    try {
        $lastStmt = $pdo->query("
            SELECT username, full_name, last_login_at, last_login_ip 
            FROM admins 
            WHERE last_login_at IS NOT NULL 
            ORDER BY last_login_at DESC 
            LIMIT 1
        ");
        $lastLogin = $lastStmt->fetch() ?: null;
    } catch (\Throwable $e) {
        $lastLogin = null;
    }

    return [
        'total' => $total,
        'active' => $active,
        'super_admins' => $superAdmins,
        'standard_admins' => max(0, $total - $superAdmins),
        'last_login' => $lastLogin,
    ];
}

/**
 * Create a new admin account.
 * Supports both array payload and positional parameters for backwards compatibility.
 *
 * @param array|string $usernameOrData
 * @return array{ok: bool, error: ?string}
 */
function createAdminUserRecord($usernameOrData, string $fullName = '', string $password = '', string $role = 'admin', int $actorAdminId = 0): array
{
    if (is_array($usernameOrData)) {
        $username = trim($usernameOrData['username'] ?? '');
        $fullName = trim($usernameOrData['full_name'] ?? '');
        $password = (string) ($usernameOrData['password'] ?? '');
        $role = ($usernameOrData['role'] ?? 'admin') === 'super_admin' ? 'super_admin' : 'admin';
        $phone = trim($usernameOrData['phone'] ?? '');
        $email = trim($usernameOrData['email'] ?? '');
        $actorAdminId = (int) ($usernameOrData['actor_id'] ?? $actorAdminId);
    } else {
        $username = trim((string) $usernameOrData);
        $role = ($role === 'super_admin') ? 'super_admin' : 'admin';
        $phone = '';
        $email = '';
    }

    if (mb_strlen($username) < 3) {
        return ['ok' => false, 'error' => 'نام کاربری باید حداقل ۳ کاراکتر باشد.'];
    }
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $username)) {
        return ['ok' => false, 'error' => 'نام کاربری فقط می‌تواند شامل حروف انگلیسی، اعداد، خط فاصله، زیرخط و نقطه باشد.'];
    }
    if (mb_strlen($password) < 8) {
        return ['ok' => false, 'error' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.'];
    }

    $pdo = db();
    $check = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $check->execute([$username]);
    if ($check->fetch()) {
        return ['ok' => false, 'error' => 'این نام کاربری قبلاً استفاده شده است.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admins (username, password_hash, role, full_name, phone, email, is_active, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([
            $username,
            $hash,
            $role,
            $fullName ?: null,
            $phone ?: null,
            $email ?: null,
        ]);
        $newId = (int) $pdo->lastInsertId();
    } catch (\Throwable $e) {
        // Fallback for pre-migration table structure
        $stmt = $pdo->prepare("
            INSERT INTO admins (username, password_hash, role, full_name, is_active, created_at) 
            VALUES (?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$username, $hash, $role, $fullName ?: null]);
        $newId = (int) $pdo->lastInsertId();
    }

    logAdminAction($actorAdminId ?: $newId, 'admin_create', 'admin', $newId, "ایجاد حساب مدیر جدید: {$username} ({$role})");

    return ['ok' => true, 'error' => null];
}

/**
 * Update an existing admin profile (name, phone, email, role, username).
 *
 * @return array{ok: bool, error: ?string}
 */
function updateAdminUserRecord(int $id, array $data, int $actorAdminId): array
{
    $pdo = db();
    $target = getAdminUserById($id);
    if (!$target) {
        return ['ok' => false, 'error' => 'مدیر مورد نظر یافت نشد.'];
    }

    $username = trim($data['username'] ?? $target['username']);
    $fullName = trim($data['full_name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $email = trim($data['email'] ?? '');
    $role = ($data['role'] ?? $target['role']) === 'super_admin' ? 'super_admin' : 'admin';

    if (mb_strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $username)) {
        return ['ok' => false, 'error' => 'نام کاربری نامعتبر است (حداقل ۳ کاراکتر مجاز انگلیسی).'];
    }

    // Check username uniqueness if changed
    if ($username !== $target['username']) {
        $check = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
        $check->execute([$username, $id]);
        if ($check->fetch()) {
            return ['ok' => false, 'error' => 'این نام کاربری توسط کاربر دیگری ثبت شده است.'];
        }
    }

    // Check demotion safety
    if ($target['role'] === 'super_admin' && $role !== 'super_admin') {
        $superCount = (int) $pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'super_admin'")->fetchColumn();
        if ($superCount <= 1) {
            return ['ok' => false, 'error' => 'امکان خلع درجه تنها مدیر ارشد (super_admin) سایت وجود ندارد.'];
        }
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE admins 
            SET username = ?, full_name = ?, phone = ?, email = ?, role = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $username,
            $fullName ?: null,
            $phone ?: null,
            $email ?: null,
            $role,
            $id,
        ]);
    } catch (\Throwable $e) {
        $stmt = $pdo->prepare("UPDATE admins SET username = ?, full_name = ?, role = ? WHERE id = ?");
        $stmt->execute([$username, $fullName ?: null, $role, $id]);
    }

    logAdminAction($actorAdminId, 'admin_update', 'admin', $id, "ویرایش مشخصات مدیر: {$username}");

    return ['ok' => true, 'error' => null];
}

/**
 * Toggle active status of an admin account safely.
 *
 * @return array{ok: bool, error: ?string}
 */
function toggleAdminUserActiveStatus(int $id, int $currentAdminId): array
{
    if ($id === $currentAdminId) {
        return ['ok' => false, 'error' => 'نمی‌توانید حساب کاربری خودتان را غیرفعال کنید.'];
    }

    $pdo = db();
    $target = getAdminUserById($id);
    if (!$target) {
        return ['ok' => false, 'error' => 'کاربر مورد نظر یافت نشد.'];
    }

    // If currently active super_admin, check if it's the last active super_admin
    if ((int)$target['is_active'] === 1 && $target['role'] === 'super_admin') {
        $activeSuperCount = (int) $pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'super_admin' AND is_active = 1")->fetchColumn();
        if ($activeSuperCount <= 1) {
            return ['ok' => false, 'error' => 'نمی‌توانید تنها مدیر ارشد فعال سیستم را غیرفعال کنید.'];
        }
    }

    $newStatus = 1 - (int) $target['is_active'];
    $stmt = $pdo->prepare("UPDATE admins SET is_active = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);

    $statusLabel = $newStatus ? 'فعال‌سازی' : 'غیرفعال‌سازی';
    logAdminAction($currentAdminId, 'status_toggle', 'admin', $id, "{$statusLabel} حساب مدیر {$target['username']}");

    return ['ok' => true, 'error' => null];
}

/**
 * Change an admin account password.
 *
 * @return array{ok: bool, error: ?string}
 */
function changeAdminUserPasswordRecord(int $id, string $password, int $actorAdminId = 0): array
{
    if (mb_strlen($password) < 8) {
        return ['ok' => false, 'error' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.'];
    }

    $target = getAdminUserById($id);
    if (!$target) {
        return ['ok' => false, 'error' => 'کاربر مورد نظر یافت نشد.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = db()->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
    $stmt->execute([$hash, $id]);

    logAdminAction($actorAdminId ?: $id, 'password_change', 'admin', $id, "تغییر رمز عبور حساب مدیر: {$target['username']}");

    return ['ok' => true, 'error' => null];
}

/**
 * Delete an admin account safely.
 *
 * @return array{ok: bool, error: ?string}
 */
function deleteAdminUserRecord(int $id, int $currentAdminId): array
{
    if ($id === $currentAdminId) {
        return ['ok' => false, 'error' => 'نمی‌توانید حساب کاربری خودتان را حذف کنید.'];
    }

    $pdo = db();
    $target = getAdminUserById($id);
    if (!$target) {
        return ['ok' => false, 'error' => 'کاربر مورد نظر یافت نشد.'];
    }

    if ($target['role'] === 'super_admin') {
        $superCount = (int) $pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'super_admin'")->fetchColumn();
        if ($superCount <= 1) {
            return ['ok' => false, 'error' => 'نمی‌توانید تنها مدیر ارشد (super_admin) سایت را حذف کنید.'];
        }
    }

    $username = $target['username'];
    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->execute([$id]);

    logAdminAction($currentAdminId, 'admin_delete', 'admin', $id, "حذف دائمی حساب مدیر: {$username}");

    return ['ok' => true, 'error' => null];
}

/**
 * Log an administrative action to admin_audit_logs.
 * Guaranteed never to throw or disrupt user execution.
 */
function logAdminAction(int $adminId, string $action, ?string $entityType, ?int $entityId, string $description): bool
{
    if ($adminId <= 0) {
        return false;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    try {
        $stmt = db()->prepare("
            INSERT INTO admin_audit_logs (admin_id, action, entity_type, entity_id, description, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$adminId, $action, $entityType, $entityId, $description, $ip]);
    } catch (\Throwable $e) {
        // Suppress errors if audit table not yet migrated
        return false;
    }
}

/**
 * Fetch recent admin audit logs for security oversight.
 */
function getRecentAdminAuditLogs(int $limit = 15): array
{
    try {
        $limit = max(1, min(100, $limit));
        $stmt = db()->prepare("
            SELECT l.id, l.admin_id, l.action, l.entity_type, l.entity_id, l.description, l.ip_address, l.created_at,
                   a.username, a.full_name, a.role
            FROM admin_audit_logs l
            LEFT JOIN admins a ON l.admin_id = a.id
            ORDER BY l.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (\Throwable $e) {
        return [];
    }
}
