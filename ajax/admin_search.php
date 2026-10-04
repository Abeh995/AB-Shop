<?php
/**
 * Admin live search dispatcher endpoint.
 * Authenticates admin session, delegates query resolution to SearchService provider registry,
 * and emits standardized JSON response.
 */

require_once __DIR__ . '/../app/bootstrap.php';

// Strictly enforce admin authentication
if (!isAdmin()) {
    jsonResponse(['ok' => false, 'message' => 'دسترسی غیرمجاز.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['ok' => false, 'message' => 'روش درخواست نامعتبر است.'], 405);
}

$rawQuery = trim($_GET['q'] ?? '');
$type = isset($_GET['type']) && trim((string) $_GET['type']) !== '' ? trim((string) $_GET['type']) : null;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

// Delegate to SearchService provider registry
$results = SearchService::search($rawQuery, $type, $limit);

jsonResponse($results);
