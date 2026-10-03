<?php
/**
 * Admin Product Tags Controller.
 * Thin controller managing catalog tags, SEO taxonomy, and cleanup (Rule 7).
 */

$pageTitle = 'برچسب‌ها و مشخصات محصولات';
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'save';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'save') {
        $res = TagService::save($_POST, $id > 0 ? $id : null);
        if ($res['ok']) redirect('/admin/tags.php?saved=1');
        $error = $res['error'];
    } elseif ($action === 'delete' && $id > 0) {
        $res = TagService::delete($id);
        redirect('/admin/tags.php' . ($res['ok'] ? '?deleted=1' : ''));
    } elseif ($action === 'cleanup') {
        $res = TagService::cleanupUnused();
        redirect('/admin/tags.php?cleaned=' . (int)$res['deleted_count']);
    }
}

$search = trim($_GET['q'] ?? '');
$editId = (int)($_GET['edit'] ?? 0);
$editTag = $editId > 0 ? TagService::getById($editId) : null;
$tags = TagService::getAllWithCounts($search);
$stats = TagService::getStats();

renderView('admin/tags', compact('pageTitle', 'tags', 'stats', 'editTag', 'search', 'error', 'success'));
