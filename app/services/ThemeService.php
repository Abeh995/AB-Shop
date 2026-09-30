<?php
/**
 * Theme & Storefront Styling Service
 *
 * Encapsulates all theme CRUD, color token management, and theme activation.
 *
 * Invariants:
 * - Exactly one theme is active at any time (enforced in setActiveTheme).
 * - All SQL queries & mutations for themes are encapsulated here (Rule 7).
 */

/**
 * Fetch all themes along with their color tokens.
 */
function getThemesList(): array
{
    $pdo = db();
    $themes = $pdo->query("SELECT * FROM themes ORDER BY is_active DESC, id ASC")->fetchAll();

    foreach ($themes as &$theme) {
        $theme['tokens'] = getThemeTokens((int) $theme['id']);
    }
    unset($theme);

    return $themes;
}

/**
 * Fetch a single theme by ID along with its color tokens.
 */
function getThemeById(int $id): ?array
{
    $stmt = db()->prepare("SELECT * FROM themes WHERE id = ?");
    $stmt->execute([$id]);
    $theme = $stmt->fetch();
    if (!$theme) {
        return null;
    }

    $theme['tokens'] = getThemeTokens($id);
    return $theme;
}

/**
 * Duplicate an existing theme and all its color tokens.
 *
 * @return array{ok: bool, error: ?string, id: ?int}
 */
function duplicateTheme(int $id): array
{
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM themes WHERE id = ?");
    $stmt->execute([$id]);
    $source = $stmt->fetch();
    if (!$source) {
        return ['ok' => false, 'error' => 'قالب منبع یافت نشد.', 'id' => null];
    }

    $pdo->beginTransaction();
    try {
        $insert = $pdo->prepare("INSERT INTO themes (name, is_active) VALUES (?, 0)");
        $insert->execute([$source['name'] . ' (کپی)']);
        $newId = (int) $pdo->lastInsertId();

        $tokens = getThemeTokens($id);
        $tokenInsert = $pdo->prepare("INSERT INTO theme_tokens (theme_id, token_group, token_key, token_value) VALUES (?, 'color', ?, ?)");
        foreach ($tokens as $key => $value) {
            $tokenInsert->execute([$newId, $key, $value]);
        }

        $pdo->commit();
        return ['ok' => true, 'error' => null, 'id' => $newId];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'خطا در تکثیر قالب: ' . $e->getMessage(), 'id' => null];
    }
}

/**
 * Save (create or update) a theme and its color tokens.
 *
 * @return array{ok: bool, error: ?string, id: ?int}
 */
function saveThemeRecord(?int $id, string $name, array $tokens): array
{
    $pdo = db();
    $name = trim($name);
    if ($name === '') {
        return ['ok' => false, 'error' => 'نام تم را وارد کنید.', 'id' => null];
    }

    $colorTokenDefs = defaultThemeColorTokens();

    $pdo->beginTransaction();
    try {
        if ($id && $id > 0) {
            $stmt = $pdo->prepare("UPDATE themes SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO themes (name, is_active) VALUES (?, 0)");
            $stmt->execute([$name]);
            $id = (int) $pdo->lastInsertId();
        }

        $tokenStmt = $pdo->prepare("
            INSERT INTO theme_tokens (theme_id, token_group, token_key, token_value)
            VALUES (?, 'color', ?, ?)
            ON DUPLICATE KEY UPDATE token_value = VALUES(token_value)
        ");

        foreach (array_keys($colorTokenDefs) as $key) {
            $val = trim($tokens[$key] ?? '');
            if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $val)) {
                $tokenStmt->execute([$id, $key, $val]);
            }
        }

        $pdo->commit();
        return ['ok' => true, 'error' => null, 'id' => $id];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'خطا در ذخیره تم: ' . $e->getMessage(), 'id' => null];
    }
}

/**
 * Delete a theme safely.
 *
 * @return array{ok: bool, error: ?string}
 */
function deleteThemeRecord(int $id): array
{
    $pdo = db();
    $stmt = $pdo->prepare("SELECT is_active FROM themes WHERE id = ?");
    $stmt->execute([$id]);
    $theme = $stmt->fetch();
    $totalThemes = (int) $pdo->query("SELECT COUNT(*) FROM themes")->fetchColumn();

    if (!$theme) {
        return ['ok' => false, 'error' => 'تم مورد نظر پیدا نشد.'];
    }
    if ($theme['is_active']) {
        return ['ok' => false, 'error' => 'تم فعال سایت را نمی‌توان حذف کرد؛ ابتدا تم دیگری را فعال کنید.'];
    }
    if ($totalThemes <= 1) {
        return ['ok' => false, 'error' => 'حداقل یک تم باید همیشه در سایت وجود داشته باشد.'];
    }

    $del = $pdo->prepare("DELETE FROM themes WHERE id = ?");
    $del->execute([$id]);
    return ['ok' => true, 'error' => null];
}
