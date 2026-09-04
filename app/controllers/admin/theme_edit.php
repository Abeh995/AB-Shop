<?php
/**
 * Create or edit a single theme's name and color tokens.
 */

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$theme = null;

if ($id) {
    $stmt = db()->prepare("SELECT * FROM themes WHERE id = ?");
    $stmt->execute([$id]);
    $theme = $stmt->fetch();
    if (!$theme) {
        setFlash('error', 'تم مورد نظر پیدا نشد.');
        redirect('themes.php');
    }
}

$pageTitle = $theme ? 'ویرایش تم: ' . $theme['name'] : 'تم جدید';
$colorTokenDefs = defaultThemeColorTokens();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        setFlash('error', 'نام تم را وارد کنید.');
        redirect($id ? "theme_edit.php?id=$id" : 'theme_edit.php');
    }

    if ($id) {
        db()->prepare("UPDATE themes SET name = ? WHERE id = ?")->execute([$name, $id]);
    } else {
        db()->prepare("INSERT INTO themes (name, is_active) VALUES (?, 0)")->execute([$name]);
        $id = (int) db()->lastInsertId();
    }

    $tokenStmt = db()->prepare("
        INSERT INTO theme_tokens (theme_id, token_group, token_key, token_value)
        VALUES (?, 'color', ?, ?)
        ON DUPLICATE KEY UPDATE token_value = VALUES(token_value)
    ");
    foreach (array_keys($colorTokenDefs) as $key) {
        $value = trim($_POST['token'][$key] ?? '');
        if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $value)) {
            $tokenStmt->execute([$id, $key, $value]);
        }
    }

    setFlash('success', 'تم ذخیره شد.');
    redirect('themes.php');
}

$currentTokens = $id ? getThemeTokens($id) : [];

renderView('admin/theme_edit', compact('pageTitle', 'theme', 'colorTokenDefs', 'currentTokens'));
