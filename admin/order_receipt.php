<?php
/**
 * Authenticated stream endpoint for private card-to-card receipt images.
 */

require_once __DIR__ . '/../app/bootstrap.php';
requireAdmin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT card_to_card_receipt FROM orders WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$filename = $stmt->fetchColumn();
$path = $filename ? CardToCardReceiptService::pathForStoredFile((string) $filename) : null;

if (!$path || !is_file($path)) {
    http_response_code(404);
    exit('Receipt not found.');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimeMap = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
];
$mime = $mimeMap[$ext] ?? null;

if (!$mime && function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $path);
    finfo_close($finfo);
}

if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    http_response_code(404);
    exit('Receipt not found.');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: inline; filename="receipt.' . $ext . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=86400');
readfile($path);
exit;
