<?php
/**
 * LLMs.txt & LLMs-full.txt generator for Generative Engine Optimization (GEO).
 * Strictly delegates data gathering and markdown assembly to SeoService (Rule 7).
 * Implements Conditional GET (ETag / 304 Not Modified) to protect shared hosting resources.
 */

require_once __DIR__ . '/../../bootstrap.php';

$isFull = ($_GET['format'] ?? '') === 'full';
$content = seoRenderLlmsText($isFull);

$etag = '"' . sha1($content . APP_VERSION) . '"';

header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: index, follow');
header('Cache-Control: public, max-age=3600, stale-while-revalidate=7200');
header('ETag: ' . $etag);

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
    http_response_code(304);
    exit;
}

echo $content;
