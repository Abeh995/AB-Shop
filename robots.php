<?php
/**
 * Dynamic robots.txt — driven by the seo_indexing_enabled setting in the
 * admin panel (Store settings section).
 * When it's off (the default, until real content is ready), every bot is
 * blocked from the entire site.
 */

require_once __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

$indexingEnabled = getSetting('seo_indexing_enabled', '0') === '1';

if ($indexingEnabled) {
    echo "User-agent: *\n";
    echo "Allow: /\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /ajax/\n";
    echo "Disallow: /payment/\n";
    echo "Disallow: /cart\n";
    echo "Disallow: /checkout\n";
    echo "Disallow: /account\n";
    echo "Sitemap: " . rtrim(SITE_URL, '/') . "/sitemap.xml\n";
} else {
    echo "User-agent: *\n";
    echo "Disallow: /\n";
}
