<?php
/**
 * PDO database connection using a singleton pattern; all queries use prepared statements.
 */

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            // Important: the host usually runs MySQL in UTC, while this project uses a local PHP timezone
            // configured as Asia/Tehran in config.php. Without this setting, CURRENT_TIMESTAMP values
            // stored in the database (such as cart added_at or verification created_at) can differ from PHP time()/ 
            // and strtotime() by about 3.5 hours, causing time-sensitive logic
            // such as cart price guarantees and verification resend limits to be calculated incorrectly.
            // This keeps the database session timezone aligned with PHP.
            $offsetSeconds = (new DateTime('now', new DateTimeZone(date_default_timezone_get())))->getOffset();
            $offsetSign = $offsetSeconds >= 0 ? '+' : '-';
            $offsetSeconds = abs($offsetSeconds);
            $offsetFormatted = sprintf('%s%02d:%02d', $offsetSign, floor($offsetSeconds / 3600), floor(($offsetSeconds % 3600) / 60));
            $pdo->exec("SET time_zone = " . $pdo->quote($offsetFormatted));

        } catch (PDOException $e) {
            error_log('DB Connection failed: ' . $e->getMessage());
            http_response_code(500);
            if (APP_DEBUG) {
                die('خطای اتصال دیتابیس: ' . $e->getMessage());
            }
            die('در حال حاضر امکان اتصال به سرور وجود ندارد. لطفاً بعداً تلاش کنید.');
        }
    }

    return $pdo;
}
