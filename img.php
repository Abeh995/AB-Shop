<?php
/**
 * Lightweight image proxy for shared-hosting environments where Apache's
 * AllowOverride settings prevent AddType / ForceType directives in .htaccess
 * from taking effect.
 *
 * Invoked by the RewriteRule in root .htaccess for all /uploads/ image requests.
 * Sets the correct Content-Type header and serves the file efficiently.
 *
 * Security guarantees:
 * - Only allows files from the /uploads/ directory tree (no traversal).
 * - Only serves allow-listed image extensions.
 * - Supports conditional GET (ETag + Last-Modified) for browser caching.
 */

// ── MIME allow-list ───────────────────────────────────────────────────────
$mimeMap = [
    'webp' => 'image/webp',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
];

// ── Resolve the document root (directory this file sits in) ──────────────
$docRoot = __DIR__;

// ── Decode and validate the requested file path ───────────────────────────
$raw = $_GET['f'] ?? '';
if ($raw === '') {
    http_response_code(400);
    exit('Bad Request');
}

// Normalise: ensure it starts with /uploads/
$raw = '/' . ltrim($raw, '/');
if (strncmp($raw, '/uploads/', 9) !== 0) {
    http_response_code(403);
    exit('Forbidden');
}

// Extension check
$ext  = strtolower(pathinfo($raw, PATHINFO_EXTENSION));
$mime = $mimeMap[$ext] ?? null;
if (!$mime) {
    http_response_code(415);
    exit('Unsupported Media Type');
}

// Resolve to an absolute path and prevent directory traversal
$absPath = realpath($docRoot . $raw);
$uploadsRoot = realpath($docRoot . '/uploads');

if (!$absPath || !$uploadsRoot) {
    http_response_code(404);
    exit('Not Found');
}

// Ensure the resolved path is still under /uploads/
if (strncmp($absPath, $uploadsRoot, strlen($uploadsRoot)) !== 0) {
    http_response_code(403);
    exit('Forbidden');
}

if (!is_file($absPath)) {
    http_response_code(404);
    exit('Not Found');
}

// ── Conditional GET (ETag + Last-Modified) ────────────────────────────────
$size  = filesize($absPath);
$mtime = filemtime($absPath);
$etag  = '"' . dechex($mtime) . '-' . dechex($size) . '"';

$ifNoneMatch = trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '');
$ifModSince  = trim($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '');

if (($ifNoneMatch && $ifNoneMatch === $etag) ||
    ($ifModSince && strtotime($ifModSince) >= $mtime)) {
    http_response_code(304);
    exit;
}

// ── Serve the file ────────────────────────────────────────────────────────
header('Content-Type: ' . $mime);
header('Content-Length: ' . $size);
header('ETag: ' . $etag);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
// Cache for 1 year; cache-buster suffix in filenames handles invalidation
header('Cache-Control: public, max-age=31536000, immutable');

readfile($absPath);
