<?php
/**
 * Storefront theme list — create/edit is handled by theme_edit.php.
 * A theme is a named set of color tokens (see theme_tokens); exactly one
 * theme is active at a time and its tokens override the defaults in
 * assets/css/style.css on every storefront page (see activeThemeCssVars()).
 */

$pageTitle = 'قالب و رنگ سایت';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'activate' && $id) {
        setActiveTheme($id);
        setFlash('success', 'تم فعال سایت تغییر کرد.');
    }

    if ($action === 'duplicate' && $id) {
        $stmt = db()->prepare("SELECT * FROM themes WHERE id = ?");
        $stmt->execute([$id]);
        $source = $stmt->fetch();
        if ($source) {
            $insert = db()->prepare("INSERT INTO themes (name, is_active) VALUES (?, 0)");
            $insert->execute([$source['name'] . ' (کپی)']);
            $newId = (int) db()->lastInsertId();

            $tokens = getThemeTokens($id);
            $tokenInsert = db()->prepare("INSERT INTO theme_tokens (theme_id, token_group, token_key, token_value) VALUES (?, 'color', ?, ?)");
            foreach ($tokens as $key => $value) {
                $tokenInsert->execute([$newId, $key, $value]);
            }
            setFlash('success', 'یک کپی از تم ساخته شد. حالا می‌توانید رنگ‌هایش را ویرایش کنید.');
            redirect('theme_edit.php?id=' . $newId);
        }
    }

    if ($action === 'delete' && $id) {
        $stmt = db()->prepare("SELECT is_active FROM themes WHERE id = ?");
        $stmt->execute([$id]);
        $theme = $stmt->fetch();
        $totalThemes = (int) db()->query("SELECT COUNT(*) FROM themes")->fetchColumn();

        if (!$theme) {
            // Nothing to do.
        } elseif ($theme['is_active']) {
            setFlash('error', 'تم فعال سایت را نمی‌توان حذف کرد؛ ابتدا تم دیگری را فعال کنید.');
        } elseif ($totalThemes <= 1) {
            setFlash('error', 'حداقل یک تم باید همیشه در سایت وجود داشته باشد.');
        } else {
            db()->prepare("DELETE FROM themes WHERE id = ?")->execute([$id]); // theme_tokens removed via CASCADE
            setFlash('success', 'تم حذف شد.');
        }
    }

    redirect('themes.php');
}

$themes = db()->query("SELECT * FROM themes ORDER BY is_active DESC, id ASC")->fetchAll();
$tokensByTheme = [];
foreach ($themes as $t) {
    $tokensByTheme[$t['id']] = getThemeTokens((int) $t['id']);
}

renderView('admin/themes', compact('pageTitle', 'themes', 'tokensByTheme'));
