<?php
/**
 * SMS Patterns management controller.
 * Allows store admins to view, toggle, delete, and manage pattern-based SMS templates.
 */

$pageTitle = 'الگوهای پیامک (SMS Patterns)';

$availableEvents = [
    'otp'                   => 'کد تایید ورود و ثبت‌نام (OTP)',
    'order_created'         => 'ثبت سفارش جدید برای مشتری',
    'order_shipped'         => 'ارسال و تحویل سفارش به پست/پیک',
    'card_to_card_approved' => 'تایید واریز کارت‌به‌کارت',
    'admin_new_order'       => 'اطلاع سفارش جدید به مدیر فروشگاه',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id > 0) {
        $stmt = db()->prepare("SELECT is_active, title FROM sms_patterns WHERE id = ?");
        $stmt->execute([$id]);
        $pattern = $stmt->fetch();
        if ($pattern) {
            $newStatus = $pattern['is_active'] ? 0 : 1;
            db()->prepare("UPDATE sms_patterns SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);
            setFlash('success', 'وضعیت الگوی «' . $pattern['title'] . '» به ' . ($newStatus ? 'فعال' : 'غیرفعال') . ' تغییر کرد.');
        }
        redirect('sms_patterns.php');
    }

    if ($action === 'delete' && $id > 0) {
        $stmt = db()->prepare("SELECT event_key, title FROM sms_patterns WHERE id = ?");
        $stmt->execute([$id]);
        $pattern = $stmt->fetch();
        if ($pattern) {
            db()->prepare("DELETE FROM sms_patterns WHERE id = ?")->execute([$id]);
            setFlash('success', 'الگوی «' . $pattern['title'] . '» حذف شد.');
        }
        redirect('sms_patterns.php');
    }
}

$patterns = db()->query("SELECT * FROM sms_patterns ORDER BY id ASC")->fetchAll();

renderView('admin/sms_patterns', compact('pageTitle', 'patterns', 'availableEvents'));
