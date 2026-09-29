<?php
/**
 * SMS Patterns management controller.
 * Allows store admins to view, toggle, delete, and manage pattern-based SMS templates.
 * All database operations are encapsulated in SmsPatternService (Rule 7).
 */

$pageTitle = 'الگوهای پیامک (SMS Patterns)';
$availableEvents = getAvailableSmsEvents();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id > 0) {
        $res = toggleSmsPatternStatus($id);
        if ($res['ok']) {
            setFlash('success', 'وضعیت الگوی «' . $res['title'] . '» به ' . ($res['new_status'] ? 'فعال' : 'غیرفعال') . ' تغییر کرد.');
        } else {
            setFlash('error', $res['error']);
        }
    } elseif ($action === 'delete' && $id > 0) {
        $res = deleteSmsPatternRecord($id);
        if ($res['ok']) {
            setFlash('success', 'الگوی «' . $res['title'] . '» حذف شد.');
        } else {
            setFlash('error', $res['error']);
        }
    }
    redirect('sms_patterns.php');
}

$patterns = getSmsPatternsList();

renderView('admin/sms_patterns', compact('pageTitle', 'patterns', 'availableEvents'));
