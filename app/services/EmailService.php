<?php
/**
 * Email-sending service (for the customer email-verification code) using
 * PHPMailer over authenticated SMTP.
 *
 * Why PHPMailer instead of PHP's built-in mail()? Because mail() on shared
 * hosting is usually flagged as spam by Gmail/Outlook/etc.; sending via
 * real SMTP+Auth has a much better delivery rate.
 *
 * Since 1.2.2, this captures PHPMailer's full internal debug output (the
 * line-by-line SMTP conversation) and stores it in the email_log.debug_info
 * column, so the exact reason a send failed (connection, authentication,
 * SSL certificate, etc.) can be seen from the admin panel ("SMS & email
 * log") without needing SSH.
 */

require_once __DIR__ . '/../vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService
{
    /**
     * @return array ['ok'=>bool, 'error'=>string|null]
     */
    public static function send(string $toEmail, string $subject, string $htmlBody): array
    {
        if (!SMTP_ENABLED) {
            self::logAttempt($toEmail, $subject, 'logged (SMTP not configured)', 'SMTP_ENABLED روی false است.');
            return ['ok' => false, 'error' => null];
        }

        $debugLines = [];
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = 'tls'; // STARTTLS on port 587
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 15;

            // Capture the full SMTP conversation (line by line) for debugging, never shown to the end user
            $mail->SMTPDebug = 2; // client -> server and server -> client
            $mail->Debugoutput = function ($str, $level) use (&$debugLines) {
                $debugLines[] = trim($str);
            };

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            $mail->send();
            self::logAttempt($toEmail, $subject, 'sent', implode("\n", $debugLines));
            return ['ok' => true, 'error' => null];
        } catch (PHPMailerException $e) {
            $debugText = "ErrorInfo: {$mail->ErrorInfo}\n\n--- SMTP Conversation ---\n" . implode("\n", $debugLines);
            error_log('EmailService send failed: ' . $mail->ErrorInfo);
            self::logAttempt($toEmail, $subject, 'failed: ' . $mail->ErrorInfo, $debugText);
            return ['ok' => false, 'error' => 'ارسال ایمیل ناموفق بود. جزئیات در صفحه «لاگ پیامک و ایمیل» پنل ادمین موجود است.'];
        }
    }

    public static function sendOtp(string $toEmail, string $code): array
    {
        $subject = 'کد تایید ایمیل — ' . SITE_NAME;
        $body = '
            <div dir="rtl" style="font-family:Tahoma,sans-serif; text-align:right;">
                <p>کد تایید ایمیل شما در ' . e(SITE_NAME) . ':</p>
                <p style="font-size:24px; font-weight:bold; letter-spacing:3px;">' . e($code) . '</p>
                <p>این کد تا ۱۰ دقیقه دیگر معتبر است. اگر این درخواست را شما نداده‌اید، این ایمیل را نادیده بگیرید.</p>
            </div>';
        return self::send($toEmail, $subject, $body);
    }

    /**
     * Test the SMTP connection without sending a real email — only checks the
     * connection + STARTTLS + authentication.
     * Used on the admin panel's "Diagnostics" page.
     */
    public static function testConnection(): array
    {
        if (!SMTP_ENABLED) {
            return ['ok' => false, 'summary' => 'SMTP_ENABLED روی false است.', 'debug' => null];
        }

        $debugLines = [];
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = 'tls';
            $mail->Port = SMTP_PORT;
            $mail->Timeout = 15;
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function ($str) use (&$debugLines) {
                $debugLines[] = trim($str);
            };

            $connected = $mail->smtpConnect();
            $debugText = implode("\n", $debugLines);

            if ($connected) {
                $mail->smtpClose();
                return ['ok' => true, 'summary' => 'اتصال و احراز هویت SMTP موفق بود.', 'debug' => $debugText];
            }
            return ['ok' => false, 'summary' => 'اتصال SMTP ناموفق بود.', 'debug' => $debugText];
        } catch (PHPMailerException $e) {
            $debugText = "ErrorInfo: {$mail->ErrorInfo}\n\n" . implode("\n", $debugLines);
            return ['ok' => false, 'summary' => $mail->ErrorInfo, 'debug' => $debugText];
        }
    }

    private static function logAttempt(string $email, string $subject, string $status, ?string $debug): void
    {
        try {
            db()->prepare("INSERT INTO email_log (email, subject, status, debug_info) VALUES (?, ?, ?, ?)")
                ->execute([$email, $subject, $status, $debug]);
        } catch (Exception $e) {
            error_log('email_log insert failed: ' . $e->getMessage());
        }
    }
}
