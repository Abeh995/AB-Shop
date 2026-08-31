<?php
/**
 * PDO database connection (singleton) — every query is a prepared statement.
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

            // Important: the host usually runs MySQL on UTC, while this project's
            // PHP is set to Asia/Tehran (in config.php). Without this line,
            // CURRENT_TIMESTAMP values stored in the database (e.g. the cart's
            // added_at or a verification code's created_at) would end up about
            // 3.5 hours off from PHP's time()/strtotime(), breaking
            // time-sensitive logic (the cart price guarantee, the resend
            // interval for verification codes). This line keeps the database
            // session's clock exactly in sync with PHP's.
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
