<?php
/**
 * IMAP Mailbox access, SMTP sending, and account management for admin-managed domain mailboxes.
 * Encapsulates all email client logic, credential encryption, and logging.
 */

require_once __DIR__ . '/../vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailboxService
{
    public static function available(): bool
    {
        return function_exists('imap_open');
    }

    public static function accounts(): array
    {
        return db()->query("SELECT * FROM email_accounts WHERE is_active=1 ORDER BY id ASC")->fetchAll();
    }

    public static function allAccounts(): array
    {
        return db()->query("SELECT id, email_address, display_name, imap_host, imap_port, imap_encryption, smtp_host, smtp_port, smtp_encryption, signature, is_active, created_at FROM email_accounts ORDER BY id ASC")->fetchAll();
    }

    public static function account(int $id): ?array
    {
        $s = db()->prepare("SELECT * FROM email_accounts WHERE id=? AND is_active=1");
        $s->execute([$id]);
        $r = $s->fetch();
        return $r ?: null;
    }

    public static function accountAny(int $id): ?array
    {
        $s = db()->prepare("SELECT * FROM email_accounts WHERE id=?");
        $s->execute([$id]);
        $r = $s->fetch();
        return $r ?: null;
    }

    private static function password(array $a): string
    {
        return decryptMailboxSecret($a['password_encrypted']);
    }

    private static function mailbox(array $a, string $folder = 'INBOX'): string
    {
        $enc = strtolower($a['imap_encryption'] ?? 'ssl');
        $flags = '/imap';
        if ($enc === 'ssl') {
            $flags .= '/ssl/novalidate-cert';
        } elseif ($enc === 'tls') {
            $flags .= '/tls/novalidate-cert';
        } else {
            $flags .= '/notls';
        }
        return '{' . $a['imap_host'] . ':' . (int)$a['imap_port'] . $flags . '}' . $folder;
    }

    public static function open(array $a, string $folder = 'INBOX')
    {
        if (!self::available()) {
            throw new RuntimeException('اکستنشن PHP IMAP روی این سرور هاست فعال نیست.');
        }

        $mb = @imap_open(self::mailbox($a, $folder), $a['email_address'], self::password($a), OP_READONLY, 1, [
            'DISABLE_AUTHENTICATOR' => 'GSSAPI'
        ]);

        if (!$mb) {
            $err = imap_last_error() ?: 'خطا در برقراری ارتباط با سرور IMAP.';
            throw new RuntimeException($err);
        }

        return $mb;
    }

    public static function messages(array $a, int $limit = 50, string $search = ''): array
    {
        $mb = self::open($a);
        $searchQuery = 'ALL';
        if ($search !== '') {
            $searchQuery = 'SUBJECT "' . addslashes($search) . '"';
        }

        $ids = @imap_search($mb, $searchQuery, SE_UID) ?: [];
        rsort($ids);
        $ids = array_slice($ids, 0, $limit);
        $out = [];

        foreach ($ids as $uid) {
            $ov = @imap_fetch_overview($mb, (string)$uid, FT_UID);
            if (!$ov || empty($ov[0])) continue;
            $o = $ov[0];
            $out[] = [
                'uid'     => (int)$uid,
                'subject' => self::decode($o->subject ?? '(بدون موضوع)'),
                'from'    => self::decode($o->from ?? 'ناشناس'),
                'date'    => $o->date ?? '',
                'seen'    => (bool)($o->seen ?? false),
            ];
        }

        @imap_close($mb);
        return $out;
    }

    public static function read(array $a, int $uid): array
    {
        $mb = self::open($a);
        $ov = @imap_fetch_overview($mb, (string)$uid, FT_UID);
        if (!$ov || empty($ov[0])) {
            @imap_close($mb);
            throw new RuntimeException('ایمیل مورد نظر در صندوق یافت نشد.');
        }

        $o = $ov[0];
        $structure = @imap_fetchstructure($mb, $uid, FT_UID);
        $bodyData = self::fetchBodyParts($mb, $uid, $structure);

        // Mark message as seen
        @imap_setflag_full($mb, (string)$uid, "\\Seen", ST_UID);
        @imap_close($mb);

        return [
            'uid'        => $uid,
            'subject'    => self::decode($o->subject ?? '(بدون موضوع)'),
            'from'       => self::decode($o->from ?? ''),
            'to'         => self::decode($o->to ?? ''),
            'date'       => $o->date ?? '',
            'body_html'  => $bodyData['html'],
            'body_plain' => $bodyData['plain'],
        ];
    }

    private static function fetchBodyParts($mb, int $uid, $structure): array
    {
        $html = '';
        $plain = '';

        if (empty($structure->parts)) {
            // Single part email
            $raw = @imap_body($mb, $uid, FT_UID | FT_PEEK);
            $decoded = self::decodePart($raw, $structure->encoding ?? 0);
            $charset = self::extractCharset($structure->parameters ?? []);
            $text = self::convertEncoding($decoded, $charset);

            if (($structure->subtype ?? '') === 'HTML') {
                $html = $text;
            } else {
                $plain = $text;
            }
        } else {
            // Multipart email
            foreach ($structure->parts as $partNum => $part) {
                $section = (string)($partNum + 1);
                $raw = @imap_fetchbody($mb, $uid, $section, FT_UID | FT_PEEK);
                $decoded = self::decodePart($raw, $part->encoding ?? 0);
                $charset = self::extractCharset($part->parameters ?? []);
                $text = self::convertEncoding($decoded, $charset);

                if (($part->subtype ?? '') === 'HTML') {
                    $html = $text;
                } elseif (($part->subtype ?? '') === 'PLAIN' && $plain === '') {
                    $plain = $text;
                }
            }
        }

        if ($html !== '') {
            $html = self::sanitizeHtml($html);
        }

        return ['html' => $html, 'plain' => $plain];
    }

    private static function decodePart(string $data, int $encoding): string
    {
        switch ($encoding) {
            case 3: return base64_decode($data);
            case 4: return quoted_printable_decode($data);
            default: return $data;
        }
    }

    private static function extractCharset(array $params): string
    {
        foreach ($params as $p) {
            if (strcasecmp($p->attribute ?? '', 'charset') === 0) {
                return $p->value ?? 'UTF-8';
            }
        }
        return 'UTF-8';
    }

    private static function convertEncoding(string $s, string $charset): string
    {
        if (strcasecmp($charset, 'UTF-8') === 0 || $charset === '') {
            return $s;
        }
        try {
            return @mb_convert_encoding($s, 'UTF-8', $charset);
        } catch (Throwable $e) {
            return $s;
        }
    }

    public static function sanitizeHtml(string $html): string
    {
        // Strip script tags, iframes, object, embed, event handlers
        $cleaned = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $cleaned = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $cleaned);
        $cleaned = preg_replace('#<object(.*?)>(.*?)</object>#is', '', $cleaned);
        $cleaned = preg_replace('#<embed(.*?)>(.*?)</embed>#is', '', $cleaned);
        $cleaned = preg_replace('#\s*on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#is', '', $cleaned);
        $cleaned = preg_replace('#href\s*=\s*["\']javascript:[^"\']*["\']#is', 'href="#"', $cleaned);
        return $cleaned;
    }

    private static function decode(string $s): string
    {
        $elements = @imap_mime_header_decode($s);
        if (!$elements) return $s;
        $out = '';
        foreach ($elements as $el) {
            $charset = $el->charset ?? 'UTF-8';
            $text = $el->text ?? '';
            $out .= self::convertEncoding($text, $charset);
        }
        return $out;
    }

    public static function deleteMessage(array $a, int $uid): array
    {
        if (!self::available()) {
            return ['ok' => false, 'error' => 'اکستنشن PHP IMAP فعال نیست.'];
        }
        $mb = @imap_open(self::mailbox($a), $a['email_address'], self::password($a));
        if (!$mb) {
            return ['ok' => false, 'error' => imap_last_error() ?: 'عدم اتصال به سرور جهت حذف ایمیل.'];
        }
        @imap_delete($mb, (string)$uid, FT_UID);
        @imap_expunge($mb);
        @imap_close($mb);
        return ['ok' => true, 'error' => null];
    }

    public static function send(array $a, string $to, string $subject, string $body, bool $isHtml = true): array
    {
        $mail = new PHPMailer(true);
        $debugLines = [];

        try {
            $mail->isSMTP();
            $mail->Host = $a['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $a['email_address'];
            $mail->Password = self::password($a);
            $mail->SMTPSecure = strtolower($a['smtp_encryption'] ?? '') === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)$a['smtp_port'];
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 15;

            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function ($str) use (&$debugLines) {
                $debugLines[] = trim($str);
            };

            $mail->setFrom($a['email_address'], $a['display_name']);
            $mail->addAddress($to);
            $mail->Subject = $subject;

            if ($isHtml) {
                $mail->isHTML(true);
                $mail->Body = self::buildHtmlEmailTemplate($body, $a);
                $mail->AltBody = strip_tags($body);
            } else {
                $mail->isHTML(false);
                $mail->Body = $body;
            }

            $mail->send();
            self::logSend($a['email_address'], $to, $subject, 'sent', implode("\n", $debugLines));
            return ['ok' => true, 'error' => null];
        } catch (PHPMailerException $e) {
            $debugText = "ErrorInfo: {$mail->ErrorInfo}\n\n--- SMTP Conversation ---\n" . implode("\n", $debugLines);
            self::logSend($a['email_address'], $to, $subject, 'failed: ' . $mail->ErrorInfo, $debugText);
            return ['ok' => false, 'error' => $mail->ErrorInfo];
        }
    }

    private static function buildHtmlEmailTemplate(string $content, array $account): string
    {
        $siteName = e(SITE_NAME);
        $signature = !empty($account['signature']) ? nl2br(e($account['signature'])) : '';
        $bodyContent = nl2br(e($content));

        return <<<HTML
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Tahoma, 'Segoe UI', sans-serif; background-color: #f8fafc; margin: 0; padding: 24px 12px; color: #1e293b; direction: rtl; text-align: right; }
.mail-card { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
.mail-header { background: #0f172a; padding: 20px 24px; color: #ffffff; text-align: center; }
.mail-header h1 { margin: 0; font-size: 1.15rem; font-weight: 700; letter-spacing: 0.5px; }
.mail-body { padding: 28px 24px; font-size: 0.95rem; line-height: 1.7; color: #334155; }
.mail-signature { margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9; font-size: 0.85rem; color: #64748b; line-height: 1.5; }
.mail-footer { background: #f8fafc; padding: 14px 24px; text-align: center; font-size: 0.78rem; color: #94a3b8; border-top: 1px solid #f1f5f9; }
</style>
</head>
<body>
<div class="mail-card">
    <div class="mail-header">
        <h1>{$siteName}</h1>
    </div>
    <div class="mail-body">
        {$bodyContent}
        <div class="mail-signature">
            {$signature}
        </div>
    </div>
    <div class="mail-footer">
        این ایمیل از طرف پشتیبانی {$siteName} ارسال شده است.
    </div>
</div>
</body>
</html>
HTML;
    }

    public static function testConnection(array $a): array
    {
        $imapOk = false;
        $imapError = '';

        if (!self::available()) {
            $imapError = 'اکستنشن PHP IMAP روی هاست فعال نیست.';
        } else {
            try {
                $mb = @imap_open(self::mailbox($a), $a['email_address'], self::password($a), OP_READONLY, 1, [
                    'DISABLE_AUTHENTICATOR' => 'GSSAPI'
                ]);
                if ($mb) {
                    $imapOk = true;
                    @imap_close($mb);
                } else {
                    $imapError = imap_last_error() ?: 'اتصال به پورت IMAP ناموفق بود.';
                }
            } catch (Throwable $e) {
                $imapError = $e->getMessage();
            }
        }

        $smtpOk = false;
        $debugLines = [];
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $a['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $a['email_address'];
            $mail->Password = self::password($a);
            $mail->SMTPSecure = strtolower($a['smtp_encryption'] ?? '') === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)$a['smtp_port'];
            $mail->Timeout = 10;
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function ($str) use (&$debugLines) {
                $debugLines[] = trim($str);
            };

            $connected = $mail->smtpConnect();
            if ($connected) {
                $smtpOk = true;
                $mail->smtpClose();
            }
        } catch (Throwable $e) {
            $debugLines[] = $e->getMessage();
        }

        return [
            'ok'         => ($imapOk && $smtpOk),
            'imap_ok'    => $imapOk,
            'imap_error' => $imapError,
            'smtp_ok'    => $smtpOk,
            'smtp_debug' => implode("\n", $debugLines),
        ];
    }

    public static function saveAccountRecord(?int $id, array $data): array
    {
        $email = trim($data['email_address'] ?? '');
        $name = trim($data['display_name'] ?? '');
        $pass = $data['password'] ?? '';
        $ih = trim($data['imap_host'] ?? '');
        $ip = (int) ($data['imap_port'] ?? 993);
        $ie = $data['imap_encryption'] ?? 'ssl';
        $sh = trim($data['smtp_host'] ?? '');
        $sp = (int) ($data['smtp_port'] ?? 587);
        $se = $data['smtp_encryption'] ?? 'tls';
        $sig = trim($data['signature'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || $ih === '' || $sh === '' || $ip < 1 || $sp < 1 || (!$id && $pass === '')) {
            return ['ok' => false, 'error' => 'اطلاعات حساب ایمیل کامل یا معتبر نیست.'];
        }

        $pdo = db();
        if ($id && $id > 0) {
            if ($pass !== '') {
                $q = $pdo->prepare("UPDATE email_accounts SET email_address=?, display_name=?, imap_host=?, imap_port=?, imap_encryption=?, smtp_host=?, smtp_port=?, smtp_encryption=?, signature=?, password_encrypted=? WHERE id=?");
                $q->execute([$email, $name, $ih, $ip, $ie, $sh, $sp, $se, $sig, encryptMailboxSecret($pass), $id]);
            } else {
                $q = $pdo->prepare("UPDATE email_accounts SET email_address=?, display_name=?, imap_host=?, imap_port=?, imap_encryption=?, smtp_host=?, smtp_port=?, smtp_encryption=?, signature=? WHERE id=?");
                $q->execute([$email, $name, $ih, $ip, $ie, $sh, $sp, $se, $sig, $id]);
            }
        } else {
            $q = $pdo->prepare("INSERT INTO email_accounts (email_address, display_name, imap_host, imap_port, imap_encryption, smtp_host, smtp_port, smtp_encryption, signature, password_encrypted) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $q->execute([$email, $name, $ih, $ip, $ie, $sh, $sp, $se, $sig, encryptMailboxSecret($pass)]);
        }

        return ['ok' => true, 'error' => null];
    }

    public static function toggleAccountActive(int $id): array
    {
        $a = self::accountAny($id);
        if (!$a) return ['ok' => false, 'error' => 'حساب ایمیل یافت نشد.'];
        $newStatus = $a['is_active'] ? 0 : 1;
        db()->prepare("UPDATE email_accounts SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);
        return ['ok' => true, 'error' => null];
    }

    public static function deleteAccount(int $id): array
    {
        db()->prepare("DELETE FROM email_accounts WHERE id = ?")->execute([$id]);
        return ['ok' => true, 'error' => null];
    }

    public static function logSend(string $from, string $to, string $subject, string $status, ?string $debug = null): void
    {
        try {
            db()->prepare("INSERT INTO email_log (email, subject, status, debug_info) VALUES (?, ?, ?, ?)")
                ->execute([$to, $subject, $status, $debug]);
        } catch (Throwable $e) {
            error_log('MailboxService logSend failed: ' . $e->getMessage());
        }
    }
}
