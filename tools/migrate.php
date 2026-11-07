<?php
/**
 * Safe, idempotent database migration runner for AB-Socks.
 * Scans database/migrations/*.sql and applies unapplied migrations.
 * Usage: php tools/migrate.php [--dry-run]
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

echo "=== AB-Socks Database Migration Runner ===\n";
echo "Database: " . DB_NAME . " on " . DB_HOST . "\n\n";

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);

    // Ensure schema_migrations table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `schema_migrations` (
            `migration` VARCHAR(191) NOT NULL PRIMARY KEY,
            `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $applied = $pdo->query("SELECT `migration` FROM `schema_migrations`")->fetchAll(PDO::FETCH_COLUMN);

    $migrationsDir = dirname(__DIR__) . '/database/migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files);

    $pending = [];
    foreach ($files as $file) {
        $base = basename($file);
        if (!in_array($base, $applied, true)) {
            $pending[] = $file;
        }
    }

    if (empty($pending)) {
        echo "All migrations are up to date! (Total registered: " . count($applied) . ")\n";
        exit(0);
    }

    $isDryRun = in_array('--dry-run', $argv, true);
    echo "Found " . count($pending) . " migration(s) to process:\n";

    foreach ($pending as $file) {
        $base = basename($file);
        echo "  - {$base} ... ";

        if ($isDryRun) {
            echo "[DRY-RUN SKIPPED]\n";
            continue;
        }

        $sql = file_get_contents($file);
        if (trim($sql) === '') {
            echo "[EMPTY FILE]\n";
            continue;
        }

        try {
            $pdo->exec($sql);
            $stmt = $pdo->prepare("INSERT IGNORE INTO `schema_migrations` (`migration`) VALUES (?)");
            $stmt->execute([$base]);
            echo "[APPLIED]\n";
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            // If already applied in legacy database without tracking (Duplicate column/table/key/entry)
            if (str_contains($msg, '1060') || str_contains($msg, '1050') || str_contains($msg, '1061') || str_contains($msg, '1062')) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO `schema_migrations` (`migration`) VALUES (?)");
                $stmt->execute([$base]);
                echo "[ALREADY APPLIED - REGISTERED]\n";
            } else {
                echo "[ERROR: " . $msg . "]\n";
                exit(1);
            }
        }
    }

    echo "\nAll pending migrations processed successfully.\n";

} catch (Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
