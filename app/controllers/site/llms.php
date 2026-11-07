<?php
/**
 * LLMs.txt & LLMs-full.txt generator for Generative Engine Optimization (GEO).
 * Strictly delegates data gathering and markdown assembly to SeoService (Rule 7).
 */

require_once __DIR__ . '/../../bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: index, follow');

$isFull = ($_GET['format'] ?? '') === 'full';
echo seoRenderLlmsText($isFull);
