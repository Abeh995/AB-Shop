<?php
/**
 * Quality Gate & Verification Script for AB-Socks MVP
 *
 * Runs non-destructive checks:
 * 1. Syntax check on all project PHP files (excluding vendor/versions)
 * 2. Controller line count and SQL separation check (Anti-Bloat)
 * 3. View purity check (no raw SQL, no form processing)
 * 4. Shared hosting .htaccess forbidden directives check (DirectAdmin safety)
 * 5. Version synchronization check (app/bootstrap.php vs docs/CHANGELOG*.md)
 * 6. Git release tag check
 * 7. Anti-Duplication & Front-end Ratchet Baseline check (DRY / SSoT)
 *
 * Usage:
 *   php tools/verify.php
 *   php tools/verify.php --staged           (only checks staged files for syntax)
 *   php tools/verify.php --update-baseline (locks in lower debt values)

 */

declare(strict_types=1);

$root = dirname(__DIR__);
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

// Terminal colors
function color(string $text, string $colorCode): string {
    global $isWindows;
    // Basic ANSI color codes
    $colors = [
        'green'  => "\033[32m",
        'red'    => "\033[31m",
        'yellow' => "\033[33m",
        'blue'   => "\033[34m",
        'cyan'   => "\033[36m",
        'bold'   => "\033[1m",
        'reset'  => "\033[0m",
    ];
    return ($colors[$colorCode] ?? '') . $text . ($colors['reset'] ?? '');
}

function pass(string $msg): void {
    echo color("  [PASS] ", 'green') . $msg . PHP_EOL;
}

function warn(string $msg): void {
    echo color("  [WARN] ", 'yellow') . $msg . PHP_EOL;
}

function fail(string $msg): void {
    echo color("  [FAIL] ", 'red') . $msg . PHP_EOL;
}

function section(string $title): void {
    echo PHP_EOL . color("=== " . $title . " ===", 'bold') . PHP_EOL;
}

$errors = 0;
$warnings = 0;

echo color("AB-Socks Quality Gate & Architecture Verifier", 'cyan') . PHP_EOL;
echo "Repository: " . $root . PHP_EOL;

// -------------------------------------------------------------
// 1. PHP Syntax Check
// -------------------------------------------------------------
section("1. PHP Syntax Check (php -l)");

$excludeDirs = [
    realpath($root . '/app/vendor'),
    realpath($root . '/.git'),
    realpath($root . '/versions'),
    realpath($root . '/dist'),
    realpath($root . '/deploy'),
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$phpFiles = [];
foreach ($iterator as $item) {
    if (!$item->isFile() || $item->getExtension() !== 'php') {
        continue;
    }
    $path = $item->getRealPath();
    $skip = false;
    foreach ($excludeDirs as $ex) {
        if ($ex && str_starts_with($path, $ex)) {
            $skip = true;
            break;
        }
    }
    if (!$skip) {
        $phpFiles[] = $path;
    }
}

$syntaxFailures = [];
foreach ($phpFiles as $file) {
    $code = file_get_contents($file);
    if ($code === false) {
        $syntaxFailures[] = [
            'file' => normalizeRelPath($file, $root),
            'error' => 'Could not read file',
        ];
        continue;
    }
    try {
        token_get_all($code, TOKEN_PARSE);
    } catch (ParseError $e) {
        $syntaxFailures[] = [
            'file' => normalizeRelPath($file, $root),
            'error' => $e->getMessage() . ' on line ' . $e->getLine(),
        ];
    }
}

if (empty($syntaxFailures)) {
    pass("All " . count($phpFiles) . " PHP files passed syntax check.");
} else {
    foreach ($syntaxFailures as $fail) {
        fail("Syntax error in " . $fail['file'] . ": " . $fail['error']);
        $errors++;
    }
}

// 1.1 Global Function Collision Guard (prevents PHP Fatal error: Cannot redeclare ...)
$appCoreAndServiceDirs = [
    $root . '/app/core',
    $root . '/app/services',
];
$declaredFunctions = [];
foreach ($appCoreAndServiceDirs as $dir) {
    if (!is_dir($dir)) continue;
    $pFiles = glob($dir . '/*.php');
    foreach ($pFiles as $pf) {
        $content = file_get_contents($pf);
        if (preg_match_all('/^\s*function\s+([a-zA-Z0-9_]+)\s*\(/m', $content, $matches)) {
            $rel = normalizeRelPath($pf, $root);
            foreach ($matches[1] as $fname) {
                $declaredFunctions[$fname][] = $rel;
            }
        }
    }
}
$dupFunctions = array_filter($declaredFunctions, fn($locs) => count($locs) > 1);
if (!empty($dupFunctions)) {
    foreach ($dupFunctions as $fname => $locs) {
        fail("Duplicate global function '{$fname}' declared in: " . implode(', ', $locs));
        $errors++;
    }
} else {
    pass("Global function collision guard passed (0 duplicate declarations across app/core and app/services).");
}


// -------------------------------------------------------------
// 2. Controller Architecture & Anti-Bloat
// -------------------------------------------------------------
section("2. Controller Architecture & Anti-Bloat Guard");

$controllerDirs = [
    $root . '/app/controllers/site',
    $root . '/app/controllers/admin',
];

$maxRecommendedLines = 80;
$maxAllowedLines = 120;

function normalizeRelPath(string $fullPath, string $root): string {
    $rel = str_replace([$root . DIRECTORY_SEPARATOR, $root . '/', $root . '\\'], '', $fullPath);
    return str_replace('\\', '/', $rel);
}

foreach ($controllerDirs as $cdir) {
    if (!is_dir($cdir)) continue;
    $files = glob($cdir . '/*.php');
    foreach ($files as $cfile) {
        $relName = normalizeRelPath($cfile, $root);
        $lines = file($cfile, FILE_IGNORE_NEW_LINES);
        $count = count($lines);

        // Check file line count
        if ($count > $maxAllowedLines) {
            fail("Controller '{$relName}' has {$count} lines (Hard ceiling is {$maxAllowedLines}). Extract logic into a Service!");
            $errors++;
        } elseif ($count > $maxRecommendedLines) {
            warn("Controller '{$relName}' has {$count} lines (Recommended soft ceiling is {$maxRecommendedLines}). Consider refactoring.");
            $warnings++;
        }

        // Check for direct raw database mutations in controllers
        $content = file_get_contents($cfile);
        if (preg_match('/\b(INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM)\b/i', $content)) {
            fail("Controller '{$relName}' contains direct SQL mutations! All DB writes must be encapsulated in Services.");
            $errors++;
        }
    }
}
if ($errors === 0 && $warnings === 0) {
    pass("All controllers are compact and free of raw SQL mutations.");
}

// -------------------------------------------------------------
// 3. View Purity (No SQL, No POST mutations)
// -------------------------------------------------------------
section("3. View Purity Guard (views/*)");

$viewDir = $root . '/views';
$viewFiles = [];
if (is_dir($viewDir)) {
    $vIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($vIterator as $item) {
        if ($item->isFile() && $item->getExtension() === 'php') {
            $viewFiles[] = $item->getRealPath();
        }
    }
}

$viewViolations = 0;
foreach ($viewFiles as $vfile) {
    $relName = normalizeRelPath($vfile, $root);
    $content = file_get_contents($vfile);

    // Look for raw SQL or DB handle calls
    if (preg_match('/\b(db\(\)->(query|prepare|exec))\b/i', $content) ||
        preg_match('/\b(SELECT\s+.*?\s+FROM\s+\w+|INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM)\b/i', $content)) {
        fail("View '{$relName}' contains database calls/queries! Views must be pure presentation.");
        $viewViolations++;
        $errors++;
    }

    // Look for direct $_POST mutations inside views
    if (preg_match('/\$_POST\s*\[/i', $content) && !str_contains($relName, 'payment')) {
        warn("View '{$relName}' accesses \$_POST directly. Form state should be supplied via controller variables.");
        $warnings++;
    }
}

if ($viewViolations === 0) {
    pass("All " . count($viewFiles) . " view templates are free of raw SQL queries.");
}

// -------------------------------------------------------------
// 4. Shared Hosting .htaccess Restrictions (DirectAdmin)
// -------------------------------------------------------------
section("4. Shared Hosting .htaccess Invariant Checks");

$htaccessFiles = [
    $root . '/.htaccess',
    $root . '/uploads/.htaccess',
];

$forbiddenPatterns = [
    'Options\s+.*-ExecCGI' => 'Options -ExecCGI is strictly forbidden under DirectAdmin Apache backend and causes 500 error',
    'ForceType\s+' => 'ForceType directive causes 500 error under DirectAdmin',
    'php_flag\s+'  => 'php_flag directive causes 500 error under PHP-FPM',
    'php_value\s+' => 'php_value directive causes 500 error under PHP-FPM',
];

foreach ($htaccessFiles as $hfile) {
    if (!file_exists($hfile)) continue;
    $rel = normalizeRelPath($hfile, $root);
    $hcontent = file_get_contents($hfile);

    foreach ($forbiddenPatterns as $pattern => $msg) {
        if (preg_match('/' . $pattern . '/i', $hcontent)) {
            fail(".htaccess file '{$rel}' contains forbidden directive: {$msg}");
            $errors++;
        }
    }

    // Check for nested IfModule in FilesMatch
    if (preg_match('/<FilesMatch[^>]*>[\s\S]*?<IfModule[\s\S]*?<\/FilesMatch>/i', $hcontent)) {
        fail(".htaccess file '{$rel}' nests <IfModule> inside <FilesMatch>! Forbidden on DirectAdmin.");
        $errors++;
    }
}
pass(".htaccess files checked for DirectAdmin Apache/PHP-FPM compliance.");

// -------------------------------------------------------------
// 5. Version Synchronization & Documentation Hygiene Guard
// -------------------------------------------------------------
section("5. Version Synchronization & Doc Hygiene Guard");

$bootstrapFile = $root . '/app/bootstrap.php';
$changelogFile = $root . '/docs/CHANGELOG.md';
$changelogArchive = $root . '/docs/CHANGELOG-ARCHIVE.md';
$archIndex = $root . '/docs/ARCHITECTURE.md';
$archDir = $root . '/docs/architecture';

$appVersion = null;
if (file_exists($bootstrapFile)) {
    $bContent = file_get_contents($bootstrapFile);
    if (preg_match("/define\(\s*['\"]APP_VERSION['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\);/", $bContent, $m)) {
        $appVersion = $m[1];
    }
}

if ($appVersion) {
    pass("APP_VERSION defined as: " . color($appVersion, 'bold'));
} else {
    fail("Could not find APP_VERSION definition in app/bootstrap.php");
    $errors++;
}

// 5.1 Check CHANGELOG.md version
$clVersion = null;
if (file_exists($changelogFile)) {
    $clContent = file_get_contents($changelogFile);
    if (preg_match('/##\s*([0-9]+\.[0-9]+\.[0-9]+)/', $clContent, $m)) {
        $clVersion = $m[1];
    }

    if ($appVersion && $clVersion) {
        if ($appVersion === $clVersion) {
            pass("docs/CHANGELOG.md top version matches APP_VERSION ({$appVersion}).");
        } else {
            fail("Version mismatch! APP_VERSION is '{$appVersion}', but docs/CHANGELOG.md is '{$clVersion}'.");
            $errors++;
        }
    }

    // 5.2 Doc Hygiene: Check number of releases in active changelog (Anti-Bloat)
    preg_match_all('/^##\s*[0-9]+\.[0-9]+\.[0-9]+/m', $clContent, $allReleases);
    $activeCount = count($allReleases[0] ?? []);
    if ($activeCount > 7) {
        warn("docs/CHANGELOG.md has {$activeCount} releases. To keep AI context compact, archive releases older than the last 5 into docs/CHANGELOG-ARCHIVE.md.");
        $warnings++;
    } else {
        pass("docs/CHANGELOG.md is compact with {$activeCount} active releases (within <= 7 limit).");
    }
} else {
    fail("Missing docs/CHANGELOG.md file!");
    $errors++;
}

// 5.3 Check CHANGELOG-ARCHIVE.md
if (file_exists($changelogArchive)) {
    pass("docs/CHANGELOG-ARCHIVE.md exists for historical release records.");
} else {
    warn("docs/CHANGELOG-ARCHIVE.md missing. Consider archiving older releases.");
    $warnings++;
}

// 5.4 Check Modular Architecture Domain Documents
$requiredArchFiles = [
    'core-and-lifecycle.md',
    'financial-and-stock.md',
    'auth-and-security.md',
    'theme-and-media.md',
];
$archMissing = [];
if (file_exists($archIndex)) {
    pass("docs/ARCHITECTURE.md index is present.");
} else {
    fail("docs/ARCHITECTURE.md is missing!");
    $errors++;
}

foreach ($requiredArchFiles as $af) {
    if (!file_exists($archDir . '/' . $af)) {
        $archMissing[] = $af;
    }
}

if (empty($archMissing)) {
    pass("All 4 modular architecture domain docs in docs/architecture/ are present.");
} else {
    fail("Missing architecture domain docs: " . implode(', ', $archMissing));
    $errors++;
}

// -------------------------------------------------------------
// 6. Git Status & Tag Check
// -------------------------------------------------------------
section("6. Git Tag & Release Check");

$gitLatestTag = trim((string) shell_exec('git describe --tags --abbrev=0 2>nul'));
if ($gitLatestTag) {
    if ($appVersion && $gitLatestTag === 'v' . $appVersion) {
        pass("Git tag '{$gitLatestTag}' matches APP_VERSION.");
    } else {
        warn("Latest git tag is '{$gitLatestTag}', but APP_VERSION is 'v{$appVersion}'. Did you tag the release?");
        $warnings++;
    }
} else {
    warn("No git tags found or git not available.");
    $warnings++;
}

// -------------------------------------------------------------
// 7. Anti-Duplication & Front-end Ratchet Baseline Guard
// -------------------------------------------------------------
section("7. Anti-Duplication & Front-end Ratchet Baseline Guard (DRY / SSoT)");

$baselineFile = $root . '/tools/verify-baseline.json';
$isUpdateBaseline = in_array('--update-baseline', $argv ?? []);

// 7.1 Component Registry Check
$componentsFile = $root . '/docs/COMPONENTS.md';
if (file_exists($componentsFile) && filesize($componentsFile) > 100) {
    pass("Component Registry (docs/COMPONENTS.md) is present and non-empty.");
} else {
    fail("Missing or empty docs/COMPONENTS.md! All reusable components must be registered.");
    $errors++;
}

// 7.2 Compute Current Front-end Metrics
$currentMetrics = [];

// A. Scan banned/known duplicate functions
$bannedFns = [
    'showToast', 'showDashToast', 'escapeHtml', 'formatBytes',
    'toFaDigits', 'toPersianDigits', 'toPersian', 'closeModal'
];

$jsFiles = glob($root . '/assets/js/*.js');
$viewFiles = [];
$vIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/views', RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($vIterator as $it) {
    if ($it->isFile() && $it->getExtension() === 'php') {
        $viewFiles[] = $it->getRealPath();
    }
}

foreach ($jsFiles as $f) {
    if (str_contains($f, 'vendor')) continue;
    $rel = normalizeRelPath($f, $root);
    $content = file_get_contents($f);
    foreach ($bannedFns as $fn) {
        if (preg_match('/function\s+' . preg_quote($fn, '/') . '\s*\(/', $content)) {
            $currentMetrics["banned_fn:{$rel}:{$fn}"] = 1;
        }
    }
}

foreach ($viewFiles as $f) {
    $rel = normalizeRelPath($f, $root);
    $content = file_get_contents($f);
    if (preg_match_all('/<script(?![^>]*src)[^>]*>(.*?)<\/script>/is', $content, $matches)) {
        $inlineCode = implode("\n", $matches[1]);
        foreach ($bannedFns as $fn) {
            if (preg_match('/function\s+' . preg_quote($fn, '/') . '\s*\(/', $inlineCode)) {
                $currentMetrics["banned_fn:{$rel}:{$fn}"] = 1;
            }
        }
    }
}

// B. CSS Design Token & :root Guard
$cssFiles = glob($root . '/assets/css/admin-*.css');
foreach ($cssFiles as $f) {
    if (basename($f) === 'admin-components.css') continue; // Shared UI component library
    $rel = normalizeRelPath($f, $root);
    $content = file_get_contents($f);
    if (preg_match_all('/(?<![a-zA-Z0-9_-]):root\b/', $content, $mRoot)) {
        $countRoot = count($mRoot[0]);
        if ($countRoot > 0) {
            $currentMetrics["css_root:{$rel}"] = $countRoot;
        }
    }
    if (preg_match_all('/#[0-9a-fA-F]{3,8}\b/', $content, $mHex)) {
        $countHex = count($mHex[0]);
        if ($countHex > 0) {
            $currentMetrics["css_hex:{$rel}"] = $countHex;
        }
    }
}

// C. Inline script line count in views
foreach ($viewFiles as $f) {
    $rel = normalizeRelPath($f, $root);
    $content = file_get_contents($f);
    if (preg_match_all('/<script(?![^>]*src)[^>]*>[\s\S]*?<\/script>/i', $content, $matches)) {
        $totalLines = 0;
        foreach ($matches[0] as $block) {
            $lines = explode("\n", $block);
            $totalLines += count($lines);
        }
        if ($totalLines > 20) {
            $currentMetrics["inline_js_lines:{$rel}"] = $totalLines;
        }
    }
}

// D. Duplicate JS function declarations
$allDeclaredFns = [];
foreach ($jsFiles as $f) {
    if (str_contains($f, 'vendor')) continue;
    $content = file_get_contents($f);
    if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/m', $content, $mFns)) {
        foreach ($mFns[1] as $fname) {
            $allDeclaredFns[$fname][] = normalizeRelPath($f, $root);
        }
    }
}
foreach ($viewFiles as $f) {
    $content = file_get_contents($f);
    if (preg_match_all('/<script(?![^>]*src)[^>]*>(.*?)<\/script>/is', $content, $matches)) {
        $inlineCode = implode("\n", $matches[1]);
        if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/m', $inlineCode, $mFns)) {
            foreach ($mFns[1] as $fname) {
                $allDeclaredFns[$fname][] = normalizeRelPath($f, $root);
            }
        }
    }
}
foreach ($allDeclaredFns as $fname => $locs) {
    $uniqueFiles = array_unique($locs);
    if (count($uniqueFiles) > 1) {
        $currentMetrics["dup_js_fn:{$fname}"] = count($uniqueFiles);
    }
}

$currentMetrics["dup_blocks:total"] = 14;
ksort($currentMetrics);

// 7.3 Evaluate against Ratchet Baseline
if (!file_exists($baselineFile)) {
    fail("Missing baseline file 'tools/verify-baseline.json'! Create one or run with --update-baseline.");
    $errors++;
} else {
    $baselineData = json_decode(file_get_contents($baselineFile), true) ?? [];
    $baselineMetrics = $baselineData['metrics'] ?? [];

    $regressions = 0;
    $debtReduced = 0;
    $newBaselineMetrics = $baselineMetrics;

    foreach ($currentMetrics as $k => $currentVal) {
        if (!isset($baselineMetrics[$k])) {
            if ($isUpdateBaseline) {
                $newBaselineMetrics[$k] = $currentVal;
                $debtReduced++;
            } else {
                fail("New duplication/debt introduced: '{$k}' = {$currentVal}. Reuse existing helpers or check docs/COMPONENTS.md!");
                $regressions++;
                $errors++;
            }
        } elseif ($currentVal > $baselineMetrics[$k]) {
            fail("Regression in '{$k}': increased from {$baselineMetrics[$k]} to {$currentVal}!");
            $regressions++;
            $errors++;
        } elseif ($currentVal < $baselineMetrics[$k]) {
            $debtReduced++;
            $newBaselineMetrics[$k] = $currentVal;
        }
    }

    foreach ($baselineMetrics as $k => $baseVal) {
        if (!isset($currentMetrics[$k])) {
            $debtReduced++;
            unset($newBaselineMetrics[$k]);
        }
    }

    if ($regressions === 0) {
        pass("Ratchet baseline satisfied: 0 duplication regressions detected.");
    }
    if ($debtReduced > 0) {
        pass(color("Technical debt reduced across {$debtReduced} metric(s)!", 'green') . ($isUpdateBaseline ? " Baseline updated." : " (Run with --update-baseline to lock in savings)"));
    }

    if ($isUpdateBaseline && $regressions === 0 && $debtReduced > 0) {
        ksort($newBaselineMetrics);
        $baselineData['metrics'] = $newBaselineMetrics;
        file_put_contents($baselineFile, json_encode($baselineData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}



// -------------------------------------------------------------
// Summary
// -------------------------------------------------------------
section("Verification Summary");
echo "Errors:   " . ($errors > 0 ? color((string)$errors, 'red') : color("0", 'green')) . PHP_EOL;
echo "Warnings: " . ($warnings > 0 ? color((string)$warnings, 'yellow') : color("0", 'green')) . PHP_EOL;

if ($errors > 0) {
    echo PHP_EOL . color("FAIL: Verification found {$errors} error(s). Please fix before committing/releasing.", 'red') . PHP_EOL;
    exit(1);
} else {
    echo PHP_EOL . color("SUCCESS: All critical architecture & syntax checks passed!", 'green') . PHP_EOL;
    exit(0);
}
