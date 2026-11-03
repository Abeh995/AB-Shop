<?php
/**
 * Landing Studio controller — assembles the boot payload for the visual editor
 * of the storefront landing page.
 *
 * All persistence and sanitising is delegated to LandingService /
 * LandingSchemaService (Rule 7). This file only parses input and hands data to
 * the view. The page works entirely over AJAX; the POST branch below is a
 * no-JavaScript fallback for publish / discard / reset.
 */

$pageTitle = 'استودیو صفحه اصلی';
$pageTone = 'brand';
$pageStylesheets = ['/assets/css/admin-landing.css'];
$pageScripts = ['/assets/js/landing-studio.js'];

$adminId = (int) ($_SESSION['admin_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = preg_replace('/[^a-z_]/', '', (string) ($_POST['action'] ?? ''));
    $result = landingStudioDispatch($action, $_POST, $_FILES, $adminId);
    setFlash(
        empty($result['ok']) ? 'error' : 'success',
        empty($result['ok']) ? (string) ($result['error'] ?? 'خطا در انجام عملیات.') : (string) ($result['message'] ?? 'انجام شد.')
    );
    redirect('landing.php');
}

$studioBoot = [
    'sections'    => landingStudioSections(),
    'iconSets'    => landingIconSets(),
    'config'      => getLandingDraftConfig(),
    'defaults'    => landingDefaultConfig(),
    'meta'        => getLandingStudioMeta(),
    'revisions'   => listLandingRevisions(),
    'suggestions' => ['categories' => landingCategorySuggestions()],
    'endpoints'   => [
        'ajax'    => '/ajax/landing_studio.php',
        'preview' => '/?lb_preview=1',
        'live'    => '/',
    ],
    'csrf'        => csrfToken(),
    'storeName'   => SITE_NAME,
    'appVersion'  => APP_VERSION,
];

renderView('admin/landing', compact('pageTitle', 'pageTone', 'pageStylesheets', 'pageScripts', 'studioBoot'));
