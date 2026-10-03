<?php
/**
 * Appearance controller — manages customer-facing visual presentation:
 * Home/Landing sections visibility & titles, logo, themes, announcement bar, and footer teasers.
 *
 * All business logic, image processing, and persistence are delegated
 * to SettingService and ThemeService (Rule 7).
 */

$pageTitle = 'ظاهر و صفحه اصلی';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $section = $_POST['section'] ?? '';

    $res = saveStoreSection($section, $_POST, $_FILES);
    if ($res['ok']) {
        setFlash('success', $res['message'] ?? 'تنظیمات با موفقیت ذخیره شد.');
    } else {
        setFlash('error', $res['error'] ?? 'خطا در ذخیره تنظیمات.');
    }
    $tab = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($_POST['tab'] ?? ''));
    redirect('appearance.php' . ($tab !== '' ? '#' . $tab : ''));
}

$settings = getStoreSettingsData();
$activeTheme = getActiveTheme();
$stats = getAppearanceStats();

renderView('admin/appearance', array_merge(
    ['pageTitle' => $pageTitle, 'activeTheme' => $activeTheme, 'stats' => $stats],
    $settings
));
