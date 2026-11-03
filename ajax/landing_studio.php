<?php
/**
 * Landing Studio AJAX endpoint.
 * Authenticates the admin session, validates the CSRF token and forwards the
 * action to LandingService (all rules, sanitising and persistence live there).
 */

require_once __DIR__ . '/../app/bootstrap.php';

if (!isAdminLoggedIn()) {
    jsonResponse(['ok' => false, 'error' => 'نشست شما منقضی شده است؛ دوباره وارد شوید.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'error' => 'روش درخواست نامعتبر است.'], 405);
}

$token = (string) ($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    jsonResponse(['ok' => false, 'error' => 'درخواست نامعتبر است؛ صفحه را رفرش کنید.'], 403);
}

$action = preg_replace('/[^a-z_]/', '', (string) ($_POST['action'] ?? ''));
$result = landingStudioDispatch($action, $_POST, $_FILES, (int) ($_SESSION['admin_id'] ?? 0));

jsonResponse($result, empty($result['ok']) ? 422 : 200);
