<?php
/**
 * Landing Studio self-test (no database, no HTTP).
 *
 * Verifies the two invariants the Studio UX depends on:
 *  1. Coverage — every editable leaf of the shipped landing config has a field
 *     descriptor (so "every text is editable"), and every descriptor points at a
 *     real config path (no dead fields).
 *  2. Safety — the sanitizer keeps the config shape intact, strips markup,
 *     rejects hostile URLs/colours, clamps numbers and whitelists enums, while
 *     unknown keys can never leak into the stored JSON.
 *
 * Usage: php tools/landing-selftest.php
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/app/services/LandingSchemaService.php';

$failures = [];
$checks = 0;

function check(string $label, bool $condition, string $detail = ''): void
{
    global $failures, $checks;
    $checks++;
    if (!$condition) {
        $failures[] = $label . ($detail !== '' ? ' — ' . $detail : '');
    }
}

/**
 * Flatten to leaf paths, collapsing list indices ("a[0].b" => "a.b").
 */
function selftestLeaves($node, string $prefix = ''): array
{
    $leaves = [];
    if (!is_array($node)) {
        return [$prefix];
    }
    foreach ($node as $key => $value) {
        $path = is_int($key) ? $prefix : ($prefix === '' ? (string) $key : $prefix . '.' . $key);
        $leaves = array_merge($leaves, selftestLeaves($value, $path));
    }
    return $leaves;
}

$defaults = landingDefaultConfig();
$index = landingFieldIndex();

// ── 1. Coverage ────────────────────────────────────────────────────────────
$containerPaths = [];
foreach ($index as $path => $field) {
    if (in_array($field['type'], ['list', 'repeater', 'order'], true)) {
        $containerPaths[] = $path;
    }
}

$covered = 0;
$firstLeaf = null;
foreach (selftestLeaves($defaults) as $leaf) {
    if ($leaf === '' || $leaf === 'version') {
        // 'version' is the internal config schema stamp, not editable copy.
        continue;
    }
    $firstLeaf = $firstLeaf ?? $leaf;
    $isCovered = isset($index[$leaf]);
    if (!$isCovered) {
        foreach ($containerPaths as $container) {
            if ($leaf === $container || str_starts_with($leaf, $container . '.')) {
                $isCovered = true;
                break;
            }
        }
    }
    check('leaf has a studio field: ' . $leaf, $isCovered);
    $covered += $isCovered ? 1 : 0;
}

$deadFields = [];
foreach (array_keys($index) as $path) {
    if ($path === 'sectionOrder') {
        continue;
    }
    $isContainerChild = false;
    foreach ($containerPaths as $container) {
        if (str_starts_with($path, $container . '.')) {
            $isContainerChild = true;
            break;
        }
    }
    if ($isContainerChild) {
        // Repeater children live inside array items, not next to them.
        continue;
    }
    if (getPathProbe($defaults, $path) === null) {
        $deadFields[] = $path;
    }
}
foreach ($deadFields as $dead) {
    check('descriptor points at a real config path: ' . $dead, false);
}

// ── 2. Sanitizer shape & safety ────────────────────────────────────────────
$hostile = [
    'seo' => ['title' => '<script>alert(1)</script>عنوان', 'description' => str_repeat('ا', 900), 'ogImage' => 'javascript:alert(1)'],
    'hero' => [
        'headlineMain' => '<b>تیتر</b> تازه',
        'parallaxIntensity' => 99,
        'underlineColor' => 'red;background:url(x)',
        'underlinePreset' => 'not-a-preset',
        'productImage' => 'data:text/html;base64,PHNjcmlwdD4=',
        'trustChips' => ['یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت'],
        'miniTrustStats' => [['icon' => 'unknown-icon', 'title' => 'عنوان', 'subtitle' => 'زیر']],
        'showFeaturedCard' => '0',
    ],
    'sectionOrder' => [
        ['id' => 'hero', 'label' => 'hacked', 'enabled' => 'yes'],
        ['id' => 'evil-section', 'label' => 'evil', 'enabled' => true],
    ],
    'unknownRoot' => ['a' => 1],
    'editorial' => ['faq' => ['items' => [['q' => 'سوال', 'a' => '<iframe src=x>پاسخ']]]],
];

$clean = landingSanitizeConfig($hostile);

check('unknown root keys are dropped', !array_key_exists('unknownRoot', $clean));
check('scripts are stripped from text', strpos($clean['seo']['title'], '<') === false);
check('text length is clamped', mb_strlen($clean['seo']['description']) <= 320);
check('hostile image URL rejected', $clean['seo']['ogImage'] === '');
check('markup stripped from headline', $clean['hero']['headlineMain'] === 'تیتر تازه');
check('range clamped to max', $clean['hero']['parallaxIntensity'] <= 2);
check('invalid colour falls back to default', $clean['hero']['underlineColor'] === landingDefaultConfig()['hero']['underlineColor']);
check('invalid enum falls back to default', $clean['hero']['underlinePreset'] === landingDefaultConfig()['hero']['underlinePreset']);
check('javascript: image rejected', $clean['hero']['productImage'] === landingDefaultConfig()['hero']['productImage']);
check('list is capped by max', count($clean['hero']['trustChips']) <= 6);
check('invalid icon falls back to default', $clean['hero']['miniTrustStats'][0]['icon'] === landingDefaultConfig()['hero']['miniTrustStats'][0]['icon']);
check('string "0" becomes a boolean', $clean['hero']['showFeaturedCard'] === false);
check('sectionOrder drops unknown ids', count(array_filter($clean['sectionOrder'], static fn ($r) => $r['id'] === 'evil-section')) === 0);
check('sectionOrder keeps canonical labels', $clean['sectionOrder'][0]['label'] === landingDefaultConfig()['sectionOrder'][0]['label']);
check('sectionOrder is complete again', count($clean['sectionOrder']) === count(landingDefaultConfig()['sectionOrder']));
check('repeater items stay objects', is_array($clean['editorial']['faq']['items'][0]));
check('nested markup stripped', strpos($clean['editorial']['faq']['items'][0]['a'], '<') === false);

// Sanitizing twice must be idempotent (guards against drift between saves).
$twice = landingSanitizeConfig($clean);
check('sanitizer is idempotent', json_encode($twice) === json_encode($clean));

// Empty input must reproduce the shipped defaults exactly.
check('empty payload reproduces defaults', json_encode(landingSanitizeConfig([])) === json_encode(landingSanitizeConfig(landingDefaultConfig())));

// React bundle compatibility: the JSON sent to the storefront must round-trip.
$encoded = json_encode($clean, JSON_UNESCAPED_UNICODE);
check('config survives a JSON round-trip', is_array(json_decode($encoded, true)));

// ── Report ─────────────────────────────────────────────────────────────────
echo "Landing Studio self-test\n";
echo "  leaves checked : " . $covered . "\n";
echo "  fields indexed : " . count($index) . "\n";
echo "  assertions     : " . $checks . "\n";

if ($failures) {
    echo "\nFAILED (" . count($failures) . "):\n";
    foreach ($failures as $failure) {
        echo '  - ' . $failure . "\n";
    }
    exit(1);
}

echo "  result         : OK\n";
exit(0);

/**
 * Null-safe path probe used by the dead-field check.
 */
function getPathProbe(array $config, string $path)
{
    $node = $config;
    foreach (explode('.', $path) as $segment) {
        if (!is_array($node) || !array_key_exists($segment, $node)) {
            return null;
        }
        $node = $node[$segment];
    }
    return $node;
}
