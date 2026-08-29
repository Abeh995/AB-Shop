<?php
/**
 * Email delivery service for customer email verification using PHPMailer over authenticated SMTP.
 *
 * PHPMailer is used instead of PHP's mail() function because shared hosts often route mail
 * through local sendmail/exim services with poor deliverability. Authenticated SMTP provides
 * better delivery reliability. PHPMailer is bundled directly under app/vendor/PHPMailer, so
 * no Composer installation is required on the host.
 *
 * Fail-safe by default: when SMTP_ENABLED is false, no connection attempt is made and a
 * clear error is returned without crashing the site.
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
            return ['ok' => false, 'error' => null]; // Service is not configured; the controller decides which message to display.
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = 'tls'; // STARTTLS on port 587.
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 15;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            $mail->send();
            return ['ok' => true, 'error' => null];
        } catch (PHPMailerException $e) {
            error_log('EmailService send failed: ' . $mail->ErrorInfo);
            return ['ok' => false, 'error' => 'ارسال ایمیل ناموفق بود. لطفاً بعداً دوباره تلاش کنید.'];
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
}
