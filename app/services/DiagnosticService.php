<?php
/**
 * System Health & Diagnostic Service
 *
 * Centralizes host quotas, resource monitoring, connectivity tests,
 * notification dispatch logs, system PHP error parsing, and admin audit trail.
 *
 * Adheres to:
 * - DirectAdmin shared hosting constraints (200MB DB quota, 1500MB Disk quota).
 * - Layered architecture (Rule 7): controllers only call this service.
 * - Exception insulation: tests never crash host processes or abort execution.
 */

class DiagnosticService
{
    public const DB_QUOTA_MB = 200;
    public const DISK_QUOTA_MB = 1500;

    /**
     * Compute comprehensive system health metrics (server, database, storage, clock).
     */
    public static function getServerHealthMetrics(): array
    {
        $pdo = db();

        // 1. Database Quota & Metrics
        $dbSizeMb = 0.0;
        $tableCount = 0;
        $totalRows = 0;
        $topTables = [];

        try {
            $stmt = $pdo->query("
                SELECT 
                    table_name AS `name`,
                    table_rows AS `rows`,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS `size_mb`
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()
                ORDER BY (data_length + index_length) DESC
            ");
            $tables = $stmt->fetchAll();
            $tableCount = count($tables);

            foreach ($tables as $t) {
                $dbSizeMb += (float) $t['size_mb'];
                $totalRows += (int) $t['rows'];
            }
            $topTables = array_slice($tables, 0, 6);
        } catch (\Throwable $e) {
            error_log('DiagnosticService::getServerHealthMetrics DB error: ' . $e->getMessage());
        }

        $dbPercent = self::DB_QUOTA_MB > 0 ? round(($dbSizeMb / self::DB_QUOTA_MB) * 100, 1) : 0;

        // 2. Storage Directory Metrics (Uploads)
        $directories = [
            'products' => [
                'name' => 'تصاویر محصولات',
                'path' => defined('UPLOAD_DIR') ? UPLOAD_DIR : APP_ROOT . '/uploads/products/',
            ],
            'branding' => [
                'name' => 'هویت برند و لوگو',
                'path' => defined('BRANDING_UPLOAD_DIR') ? BRANDING_UPLOAD_DIR : APP_ROOT . '/uploads/branding/',
            ],
            'card_to_card' => [
                'name' => 'فیش‌های کارت‌به‌کارت',
                'path' => defined('CARD_TO_CARD_UPLOAD_DIR') ? CARD_TO_CARD_UPLOAD_DIR : APP_ROOT . '/uploads/card_to_card/',
            ],
            'tmp_uploads' => [
                'name' => 'فایل‌های موقت فیش',
                'path' => defined('CARD_TO_CARD_TMP_DIR') ? CARD_TO_CARD_TMP_DIR : APP_ROOT . '/uploads/card_to_card/tmp/',
            ],
            'expenses' => [
                'name' => 'ضمایم هزینه‌ها',
                'path' => defined('EXPENSE_UPLOAD_DIR') ? EXPENSE_UPLOAD_DIR : APP_ROOT . '/uploads/expenses/',
            ],
        ];

        $totalStorageBytes = 0;
        $dirStats = [];

        foreach ($directories as $key => $d) {
            $path = $d['path'];
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);
            $bytes = 0;
            $filesCount = 0;

            if ($exists) {
                try {
                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::SELF_FIRST
                    );
                    foreach ($iterator as $item) {
                        if ($item->isFile()) {
                            $bytes += $item->getSize();
                            $filesCount++;
                        }
                    }
                } catch (\Throwable $e) {
                    // Fallback for permission quirks
                }
            }

            $totalStorageBytes += $bytes;
            $dirStats[$key] = [
                'name'        => $d['name'],
                'path'        => $path,
                'exists'      => $exists,
                'writable'    => $writable,
                'files_count' => $filesCount,
                'size_mb'     => round($bytes / 1024 / 1024, 2),
            ];
        }

        $totalStorageMb = round($totalStorageBytes / 1024 / 1024, 2);
        $diskPercent = self::DISK_QUOTA_MB > 0 ? round(($totalStorageMb / self::DISK_QUOTA_MB) * 100, 1) : 0;

        // 3. PHP & DirectAdmin Environment Checks
        $webpSupported = function_exists('imagewebp') && function_exists('imagecreatefromwebp');
        $extensions = [
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'curl'      => extension_loaded('curl'),
            'mbstring'  => extension_loaded('mbstring'),
            'gd'        => extension_loaded('gd'),
            'webp_gd'   => $webpSupported,
            'openssl'   => extension_loaded('openssl'),
            'imap'      => extension_loaded('imap'),
        ];

        $phpInfo = [
            'version'           => PHP_VERSION,
            'memory_limit'      => ini_get('memory_limit') ?: 'نامشخص',
            'upload_max_filesize' => ini_get('upload_max_filesize') ?: 'نامشخص',
            'post_max_size'     => ini_get('post_max_size') ?: 'نامشخص',
            'max_execution_time'=> ini_get('max_execution_time') ?: 'نامشخص',
            'server_software'   => $_SERVER['SERVER_SOFTWARE'] ?? 'Web Server',
            'extensions'        => $extensions,
        ];

        // 4. Timezone & Clock Sync Check
        $phpTime = date('Y-m-d H:i:s');
        $phpTz = date_default_timezone_get();
        $dbTime = '';
        $dbTz = '';
        $clockSyncOk = false;
        $driftSeconds = 0;

        try {
            $tzRow = $pdo->query("SELECT NOW() as db_now, @@session.time_zone as session_tz")->fetch();
            if ($tzRow) {
                $dbTime = $tzRow['db_now'];
                $dbTz = $tzRow['session_tz'];
                $driftSeconds = abs(strtotime($phpTime) - strtotime($dbTime));
                $clockSyncOk = ($driftSeconds <= 60);
            }
        } catch (\Throwable $e) {
            $clockSyncOk = false;
        }

        // 5. System Error Log File Stats
        $logPath = APP_ROOT . '/storage_errors.log';
        $logExists = file_exists($logPath);
        $logSizeBytes = $logExists ? (int) @filesize($logPath) : 0;
        $logWritable = $logExists ? is_writable($logPath) : is_writable(APP_ROOT);

        return [
            'database' => [
                'size_mb'      => $dbSizeMb,
                'quota_mb'     => self::DB_QUOTA_MB,
                'percent'      => $dbPercent,
                'table_count'  => $tableCount,
                'total_rows'   => $totalRows,
                'top_tables'   => $topTables,
            ],
            'storage' => [
                'total_mb'     => $totalStorageMb,
                'quota_mb'     => self::DISK_QUOTA_MB,
                'percent'      => $diskPercent,
                'directories'  => $dirStats,
            ],
            'php' => $phpInfo,
            'clock' => [
                'php_time'     => $phpTime,
                'php_tz'       => $phpTz,
                'db_time'      => $dbTime,
                'db_tz'        => $dbTz,
                'drift_sec'    => $driftSeconds,
                'is_synced'    => $clockSyncOk,
            ],
            'system_log' => [
                'path'         => $logPath,
                'exists'       => $logExists,
                'writable'     => $logWritable,
                'size_kb'      => round($logSizeBytes / 1024, 1),
            ],
        ];
    }

    /**
     * Execute live connectivity tests for services.
     */
    public static function testConnectivity(string $action): array
    {
        switch ($action) {
            case 'check_balance':
                return FarazSmsService::checkBalance();

            case 'check_pattern':
                $patternCode = defined('FARAZ_OTP_PATTERN_CODE') ? FARAZ_OTP_PATTERN_CODE : '';
                return FarazSmsService::checkPattern($patternCode);

            case 'check_smtp':
                return EmailService::testConnection();

            case 'check_db':
                $start = microtime(true);
                try {
                    $pdo = db();
                    $pdo->query("SELECT 1")->fetch();
                    $ms = round((microtime(true) - $start) * 1000, 2);
                    return [
                        'ok'      => true,
                        'summary' => "اتصال به پایگاه داده با موفقیت برقرار است (تاخیر: {$ms} میلی‌ثانیه).",
                        'latency' => $ms,
                        'debug'   => ['latency_ms' => $ms, 'charset' => DB_CHARSET, 'host' => DB_HOST],
                    ];
                } catch (\Throwable $e) {
                    return [
                        'ok'      => false,
                        'summary' => 'خطا در ارتباط با دیتابیس: ' . $e->getMessage(),
                        'latency' => null,
                        'debug'   => ['error' => $e->getMessage()],
                    ];
                }

            case 'check_zarinpal':
                $start = microtime(true);
                $isSandbox = defined('ZARINPAL_SANDBOX') && ZARINPAL_SANDBOX;
                $url = $isSandbox 
                    ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json' 
                    : 'https://api.zarinpal.com/pg/v4/payment/request.json';

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 6,
                    CURLOPT_CONNECTTIMEOUT => 4,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => '{}',
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                $ms = round((microtime(true) - $start) * 1000, 2);

                if ($curlError !== '') {
                    return [
                        'ok'      => false,
                        'summary' => "عدم توانایی برقراری تماس شبکه با زرین‌پال: {$curlError}",
                        'debug'   => ['error' => $curlError, 'latency_ms' => $ms],
                    ];
                }

                // 200 or 400 (validation error from empty payload) means gateway server responded
                $reachable = in_array($httpCode, [200, 400, 422], true);
                return [
                    'ok'      => $reachable,
                    'summary' => $reachable 
                        ? "سرور زرین‌پال در دسترس است (کد وضعیت: {$httpCode} - زمان پاسخ: {$ms}ms)."
                        : "پاسخ نامتعارف از درگاه زرین‌پال (کد HTTP: {$httpCode}).",
                    'debug'   => ['http_code' => $httpCode, 'latency_ms' => $ms, 'endpoint' => $url],
                ];

            default:
                return ['ok' => false, 'summary' => 'عملکرد تست نامعتبر است.'];
        }
    }

    /**
     * Retrieve filtered notification logs (SMS or Email) with pagination.
     */
    public static function getNotificationLogs(string $type = 'sms', int $limit = 50, string $search = '', string $status = ''): array
    {
        $pdo = db();
        $limit = max(10, min(200, $limit));
        $table = ($type === 'email') ? 'email_log' : 'sms_log';

        $where = [];
        $params = [];

        if ($search !== '') {
            if ($type === 'sms') {
                $where[] = '(phone LIKE ? OR message LIKE ?)';
            } else {
                $where[] = '(email LIKE ? OR subject LIKE ?)';
            }
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} {$sqlWhere}");
        $countStmt->execute($params);
        $totalMatches = (int) $countStmt->fetchColumn();

        // Get rows
        $dataStmt = $pdo->prepare("SELECT * FROM {$table} {$sqlWhere} ORDER BY created_at DESC LIMIT {$limit}");
        $dataStmt->execute($params);
        $rows = $dataStmt->fetchAll();

        // Overall stats
        $statsStmt = $pdo->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN status = 'failed' OR status LIKE '%error%' THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN status = 'logged' OR status LIKE 'logged%' THEN 1 ELSE 0 END) as logged_count
            FROM {$table}
        ");
        $stats = $statsStmt->fetch() ?: ['total' => 0, 'sent_count' => 0, 'failed_count' => 0, 'logged_count' => 0];

        return [
            'type'          => $type,
            'rows'          => $rows,
            'total_matches' => $totalMatches,
            'stats'         => $stats,
        ];
    }

    /**
     * Parse system PHP and database error logs from storage_errors.log.
     */
    public static function getSystemErrorLogs(int $maxLines = 100): array
    {
        $logPath = APP_ROOT . '/storage_errors.log';
        if (!file_exists($logPath) || filesize($logPath) === 0) {
            return [
                'exists'     => file_exists($logPath),
                'lines_count'=> 0,
                'size_kb'    => 0,
                'entries'    => [],
            ];
        }

        $fileSize = (int) @filesize($logPath);
        $content = @file_get_contents($logPath) ?: '';
        $rawLines = array_filter(array_map('trim', explode("\n", $content)));
        $rawLines = array_reverse($rawLines); // Most recent first
        $rawLines = array_slice($rawLines, 0, $maxLines);

        $entries = [];
        foreach ($rawLines as $line) {
            $parsed = self::parseErrorLine($line);
            if ($parsed) {
                $entries[] = $parsed;
            }
        }

        return [
            'exists'     => true,
            'lines_count'=> count($rawLines),
            'size_kb'    => round($fileSize / 1024, 1),
            'entries'    => $entries,
        ];
    }

    /**
     * Clear / truncate storage_errors.log.
     */
    public static function clearSystemErrorLogs(): array
    {
        $logPath = APP_ROOT . '/storage_errors.log';
        if (!file_exists($logPath)) {
            return ['ok' => true, 'message' => 'فایل لاگ از قبل خالی بوده است.'];
        }

        if (!is_writable($logPath)) {
            return ['ok' => false, 'error' => 'مجوز نوشتن روی فایل لاگ وجود ندارد.'];
        }

        $res = @file_put_contents($logPath, '');
        if ($res === false) {
            return ['ok' => false, 'error' => 'خطا در تخلیه فایل لاگ سرور.'];
        }

        return ['ok' => true, 'message' => 'فایل لاگ خطاهای سیستمی با موفقیت تخلیه شد.'];
    }

    /**
     * Retrieve security and admin audit logs from admin_audit_logs.
     */
    public static function getAdminAuditTrail(int $limit = 50, string $search = '', string $action = ''): array
    {
        $pdo = db();
        $limit = max(10, min(200, $limit));

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = '(l.description LIKE ? OR a.username LIKE ? OR a.full_name LIKE ? OR l.ip_address LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if ($action !== '') {
            $where[] = 'l.action = ?';
            $params[] = $action;
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT 
                l.*,
                COALESCE(a.username, 'سیستم/حذف‌شده') as admin_username,
                a.full_name as admin_full_name,
                a.role as admin_role
            FROM admin_audit_logs l
            LEFT JOIN admins a ON l.admin_id = a.id
            {$sqlWhere}
            ORDER BY l.created_at DESC
            LIMIT {$limit}
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Get distinct actions for filtering
        $actionsStmt = $pdo->query("SELECT DISTINCT action FROM admin_audit_logs ORDER BY action ASC");
        $distinctActions = $actionsStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return [
            'rows'    => $rows,
            'actions' => $distinctActions,
        ];
    }

    /**
     * Clean temporary upload files older than specified hours.
     */
    public static function cleanTemporaryUploads(int $olderThanHours = 24): array
    {
        $tmpDir = defined('CARD_TO_CARD_TMP_DIR') ? CARD_TO_CARD_TMP_DIR : APP_ROOT . '/uploads/card_to_card/tmp/';
        if (!is_dir($tmpDir)) {
            return ['ok' => true, 'deleted_count' => 0, 'freed_kb' => 0];
        }

        $thresholdTime = time() - ($olderThanHours * 3600);
        $deletedCount = 0;
        $freedBytes = 0;

        $files = scandir($tmpDir);
        foreach ($files as $f) {
            if ($f === '.' || $f === '..' || $f === '.gitkeep' || $f === '.htaccess') {
                continue;
            }
            $fullPath = $tmpDir . $f;
            if (is_file($fullPath)) {
                $mtime = filemtime($fullPath);
                if ($mtime !== false && $mtime < $thresholdTime) {
                    $size = filesize($fullPath);
                    if (@unlink($fullPath)) {
                        $deletedCount++;
                        $freedBytes += $size;
                    }
                }
            }
        }

        return [
            'ok'            => true,
            'deleted_count' => $deletedCount,
            'freed_kb'      => round($freedBytes / 1024, 1),
            'message'       => "تعداد {$deletedCount} فایل موقت منقضی‌شده پاکسازی شد (" . round($freedBytes / 1024, 1) . " کیلوبایت آزاد شد).",
        ];
    }

    /**
     * Helper to mask sensitive configuration values.
     */
    public static function maskSecret(string $value): string
    {
        if ($value === '') return '(خالی)';
        if (mb_strlen($value) <= 8) return str_repeat('•', mb_strlen($value));
        return mb_substr($value, 0, 4) . str_repeat('•', mb_strlen($value) - 8) . mb_substr($value, -4);
    }

    /**
     * Parse single line of PHP error log into readable structure.
     */
    private static function parseErrorLine(string $line): ?array
    {
        if (empty($line)) return null;

        $timestamp = '';
        $level = 'Info';
        $message = $line;

        // Match standard PHP error log format: [27-Sep-2026 00:41:15 Asia/Tehran] ...
        if (preg_match('/^\[([^\]]+)\]\s+(.*)$/', $line, $m)) {
            $timestamp = $m[1];
            $body = $m[2];

            if (stripos($body, 'Fatal error') !== false) {
                $level = 'Fatal';
            } elseif (stripos($body, 'Warning') !== false) {
                $level = 'Warning';
            } elseif (stripos($body, 'Notice') !== false) {
                $level = 'Notice';
            } elseif (stripos($body, 'DB Connection failed') !== false || stripos($body, 'SQLSTATE') !== false) {
                $level = 'Database';
            }

            $message = $body;
        }

        return [
            'raw'       => $line,
            'timestamp' => $timestamp,
            'level'     => $level,
            'message'   => $message,
        ];
    }
}
