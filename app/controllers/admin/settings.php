<?php
/**
 * Store settings controller — operational settings, branding, social links, and editable
 * public business/legal content.
 *
 * All business logic, validations, file uploads, and persistence are delegated
 * to SettingService (Rule 7).
 */

$pageTitle = 'تنظیمات فروشگاه';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $section = $_POST['section'] ?? '';

    $res = saveStoreSection($section, $_POST, $_FILES);
    if ($res['ok']) {
        setFlash('success', $res['message'] ?? 'تنظیمات ذخیره شد.');
    } else {
        setFlash('error', $res['error'] ?? 'خطا در ذخیره تنظیمات.');
    }
    redirect('settings.php');
}

$settings = getStoreSettingsData();
$stats = getSettingsDirectoryStats();

renderView('admin/settings', array_merge(
    ['pageTitle' => $pageTitle, 'stats' => $stats],
    $settings
));
