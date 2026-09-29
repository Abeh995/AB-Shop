<?php
/**
 * Storefront theme list — create/edit is handled by theme_edit.php.
 * All queries and mutations are delegated to ThemeService (Rule 7).
 */

$pageTitle = 'قالب و رنگ سایت';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'activate' && $id) {
        setActiveTheme($id);
        setFlash('success', 'تم فعال سایت تغییر کرد.');
    } elseif ($action === 'duplicate' && $id) {
        $res = duplicateTheme($id);
        if ($res['ok']) {
            setFlash('success', 'یک کپی از تم ساخته شد. حالا می‌توانید رنگ‌هایش را ویرایش کنید.');
            redirect('theme_edit.php?id=' . $res['id']);
        } else {
            setFlash('error', $res['error']);
        }
    } elseif ($action === 'delete' && $id) {
        $res = deleteThemeRecord($id);
        if ($res['ok']) {
            setFlash('success', 'تم حذف شد.');
        } else {
            setFlash('error', $res['error']);
        }
    }
    redirect('themes.php');
}

$themes = getThemesList();

renderView('admin/themes', compact('pageTitle', 'themes'));
