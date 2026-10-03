<?php
/**
 * SMS Patterns management controller.
 * Allows store admins to view, toggle, delete, and manage pattern-based SMS templates.
 * All database operations are encapsulated in SmsPatternService (Rule 7).
 */

$pageTitle = 'الگوهای پیامک (SMS Patterns)';
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id > 0) {
        $res = toggleSmsPatternStatus($id);
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($res['ok']) {
            setFlash('success', 'وضعیت الگوی «' . $res['title'] . '» به ' . ($res['new_status'] ? 'فعال' : 'غیرفعال') . ' تغییر کرد.');
        } else {
            setFlash('error', $res['error']);
        }
        redirect('sms_patterns.php');
    }

    if ($action === 'delete' && $id > 0) {
        $res = deleteSmsPatternRecord($id);
        if ($res['ok']) {
            setFlash('success', 'الگوی «' . $res['title'] . '» حذف شد.');
        } else {
            setFlash('error', $res['error']);
        }
        redirect('sms_patterns.php');
    }

    redirect('sms_patterns.php');
}

$patterns = getSmsPatternsList();
$availableEvents = getAvailableSmsEvents();
$metrics = getSmsPatternsSummaryMetrics();

renderView('admin/sms_patterns', compact('pageTitle', 'patterns', 'availableEvents', 'metrics'));
