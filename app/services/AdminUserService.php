<?php
/**
 * Admin User Management Service
 *
 * Encapsulates all administrative account operations: listing, creation,
 * password modification, activation toggles, and safe deletion.
 *
 * Invariants:
 * - SuperAdmin access check is verified prior to execution (Rule 4).
 * - Password hashes use secure standard BCRYPT.
 * - Self-deactivation and self-deletion are strictly blocked.
 * - The last remaining super_admin cannot be deleted.
 */

/**
 * Fetch all admin user accounts.
 */
function getAdminUsersList(): array
{
    return db()->query("
        SELECT id, username, full_name, role, is_active, created_at 
        FROM admins 
        ORDER BY id ASC
    ")->fetchAll();
}

/**
 * Create a new admin account.
 *
 * @return array{ok: bool, error: ?string}
 */
function createAdminUserRecord(string $username, string $fullName, string $password, string $role): array
{
    $pdo = db();
    $username = trim($username);
    $fullName = trim($fullName);
    $role = ($role === 'super_admin') ? 'super_admin' : 'admin';

    if (mb_strlen($username) < 3) {
        return ['ok' => false, 'error' => 'نام کاربری باید حداقل ۳ کاراکتر باشد.'];
    }
    if (mb_strlen($password) < 8) {
        return ['ok' => false, 'error' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.'];
    }

    $check = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $check->execute([$username]);
    if ($check->fetch()) {
        return ['ok' => false, 'error' => 'این نام کاربری قبلاً استفاده شده است.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("
        INSERT INTO admins (username, password_hash, role, full_name, is_active, created_at) 
        VALUES (?, ?, ?, ?, 1, NOW())
    ");
    $stmt->execute([$username, $hash, $role, $fullName ?: null]);

    return ['ok' => true, 'error' => null];
}

/**
 * Toggle active status of an admin account.
 *
 * @return array{ok: bool, error: ?string}
 */
function toggleAdminUserActiveStatus(int $id, int $currentAdminId): array
{
    if ($id === $currentAdminId) {
        return ['ok' => false, 'error' => 'نمی‌توانید حساب کاربری خودتان را غیرفعال کنید.'];
    }

    $pdo = db();
    $stmt = $pdo->prepare("UPDATE admins SET is_active = 1 - is_active WHERE id = ?");
    $stmt->execute([$id]);

    return ['ok' => true, 'error' => null];
}

/**
 * Change an admin account password.
 *
 * @return array{ok: bool, error: ?string}
 */
function changeAdminUserPasswordRecord(int $id, string $password): array
{
    if (mb_strlen($password) < 8) {
        return ['ok' => false, 'error' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = db()->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
    $stmt->execute([$hash, $id]);

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
    $superCount = (int) $pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'super_admin'")->fetchColumn();
    $target = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
    $target->execute([$id]);
    $targetRow = $target->fetch();

    if (!$targetRow) {
        return ['ok' => false, 'error' => 'کاربر مورد نظر یافت نشد.'];
    }

    if ($targetRow['role'] === 'super_admin' && $superCount <= 1) {
        return ['ok' => false, 'error' => 'نمی‌توانید تنها مدیر ارشد (super_admin) سایت را حذف کنید.'];
    }

    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->execute([$id]);

    return ['ok' => true, 'error' => null];
}
