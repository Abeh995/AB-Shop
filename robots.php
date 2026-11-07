<?php
/**
 * Dynamic robots.txt — driven by the seo_indexing_enabled setting in the
 * admin panel (Store settings section).
 *
 * Configured for modern web search engines (Google, Bing) and AI crawlers
 * (GPTBot, PerplexityBot, ClaudeBot, Applebot).
 */

require_once __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

$indexingEnabled = getSetting('seo_indexing_enabled', '0') === '1';
$baseUrl = rtrim(SITE_URL, '/');

if ($indexingEnabled) {
    // 1. General search engines
    echo "User-agent: *\n";
    echo "Allow: /\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /ajax/\n";
    echo "Disallow: /payment/\n";
    echo "Disallow: /cart\n";
    echo "Disallow: /checkout\n";
    echo "Disallow: /account\n";
    echo "Disallow: /*?*sort=\n";
    echo "Disallow: /*?*available=\n\n";

    // 2. AI & Generative Search Engines (Permitted for Citation & Search Indexing)
    $aiBots = ['GPTBot', 'ChatGPT-User', 'PerplexityBot', 'ClaudeBot', 'Google-Extended', 'Applebot-Extended'];
    foreach ($aiBots as $bot) {
        echo "User-agent: {$bot}\n";
        echo "Allow: /\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /ajax/\n";
        echo "Disallow: /cart\n";
        echo "Disallow: /checkout\n\n";
    }

    // 3. Sitemaps and Knowledge Resources
    echo "Sitemap: {$baseUrl}/sitemap.xml\n";
    echo "# AI Knowledge Base: {$baseUrl}/llms.txt\n";
} else {
    echo "User-agent: *\n";
    echo "Disallow: /\n";
}
