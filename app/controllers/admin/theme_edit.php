<?php
/**
 * Create or edit a single theme's name and color tokens.
 * All persistence is delegated to ThemeService (Rule 7).
 */

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$theme = null;

if ($id) {
    $theme = getThemeById($id);
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
    $tokens = $_POST['token'] ?? [];

    $res = saveThemeRecord($id, $name, $tokens);
    if ($res['ok']) {
        setFlash('success', 'تم ذخیره شد.');
        redirect('themes.php');
    } else {
        setFlash('error', $res['error']);
        redirect($id ? "theme_edit.php?id=$id" : 'theme_edit.php');
    }
}

$currentTokens = $theme['tokens'] ?? [];

renderView('admin/theme_edit', compact('pageTitle', 'theme', 'colorTokenDefs', 'currentTokens'));
