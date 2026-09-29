<?php
/** IMAP mailbox access and SMTP sending for admin-managed domain mailboxes. */
require_once __DIR__ . '/../vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailboxService
{
    public static function available(): bool { return function_exists('imap_open'); }
    public static function accounts(): array { return db()->query("SELECT * FROM email_accounts WHERE is_active=1 ORDER BY id ASC")->fetchAll(); }
    public static function account(int $id): ?array { $s=db()->prepare("SELECT * FROM email_accounts WHERE id=? AND is_active=1"); $s->execute([$id]); $r=$s->fetch(); return $r ?: null; }
    private static function password(array $a): string { return decryptMailboxSecret($a['password_encrypted']); }
    private static function mailbox(array $a): string { return '{'.$a['imap_host'].':'.$a['imap_port'].'/imap/'.$a['imap_encryption'].'}INBOX'; }
    public static function open(array $a) {
        if (!self::available()) throw new RuntimeException('PHP IMAP extension is not enabled on this hosting.');
        $mb=@imap_open(self::mailbox($a), $a['email_address'], self::password($a), OP_READONLY, 1);
        if (!$mb) throw new RuntimeException(imap_last_error() ?: 'IMAP connection failed.');
        return $mb;
    }
    public static function messages(array $a, int $limit=50): array {
        $mb=self::open($a); $ids=imap_search($mb, 'ALL', SE_UID) ?: []; rsort($ids); $ids=array_slice($ids,0,$limit); $out=[];
        foreach($ids as $uid){ $ov=imap_fetch_overview($mb,$uid,FT_UID); if(!$ov) continue; $o=$ov[0]; $out[]=['uid'=>$uid,'subject'=>self::decode($o->subject??'(بدون موضوع)'),'from'=>self::decode($o->from??''),'date'=>$o->date??'','seen'=>(bool)($o->seen??false)]; }
        imap_close($mb); return $out;
    }
    public static function read(array $a,int $uid): array {
        $mb=self::open($a); $ov=imap_fetch_overview($mb,$uid,FT_UID); if(!$ov){imap_close($mb); throw new RuntimeException('ایمیل پیدا نشد.');}
        $o=$ov[0]; $body=imap_body($mb,$uid,FT_UID|FT_PEEK); $structure=imap_fetchstructure($mb,$uid,FT_UID);
        if(($structure->encoding??0)===3) $body=base64_decode($body); elseif(($structure->encoding??0)===4) $body=quoted_printable_decode($body);
        $body=self::charset($body); imap_setflag_full($mb,(string)$uid,"\Seen",ST_UID); imap_close($mb);
        return ['subject'=>self::decode($o->subject??''),'from'=>self::decode($o->from??''),'to'=>self::decode($o->to??''),'date'=>$o->date??'','body'=>$body];
    }
    private static function charset(string $s): string { $enc=mb_detect_encoding($s,['UTF-8','ISO-8859-1','Windows-1256','ISO-8859-6'],true); return $enc && strtoupper($enc)!=='UTF-8' ? mb_convert_encoding($s,'UTF-8',$enc) : $s; }
    private static function decode(string $s): string { $v=imap_mime_header_decode($s); $out=''; foreach($v as $x){$out.=self::charset($x->text);} return $out; }
    public static function send(array $a,string $to,string $subject,string $body): array {
        $mail=new PHPMailer(true); try{ $mail->isSMTP(); $mail->Host=$a['smtp_host']; $mail->SMTPAuth=true; $mail->Username=$a['email_address']; $mail->Password=self::password($a); $mail->SMTPSecure=$a['smtp_encryption']==='ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS; $mail->Port=(int)$a['smtp_port']; $mail->CharSet='UTF-8'; $mail->Timeout=20; $mail->setFrom($a['email_address'],$a['display_name']); $mail->addAddress($to); $mail->isHTML(false); $mail->Subject=$subject; $mail->Body=$body; $mail->send(); self::logSend($to, $subject, 'sent'); return ['ok'=>true,'error'=>null]; } catch(PHPMailerException $e){ self::logSend($to, $subject, 'failed: '.$mail->ErrorInfo); return ['ok'=>false,'error'=>$mail->ErrorInfo]; }
    }
    public static function allAccounts(): array {
        return db()->query("SELECT id, email_address, display_name, imap_host, imap_port, imap_encryption, smtp_host, smtp_port, smtp_encryption, is_active FROM email_accounts ORDER BY id ASC")->fetchAll();
    }

    public static function saveAccountRecord(?int $id, array $data): array {
        $email = trim($data['email_address'] ?? '');
        $name = trim($data['display_name'] ?? '');
        $pass = $data['password'] ?? '';
        $ih = trim($data['imap_host'] ?? '');
        $ip = (int) ($data['imap_port'] ?? 993);
        $ie = $data['imap_encryption'] ?? 'ssl';
        $sh = trim($data['smtp_host'] ?? '');
        $sp = (int) ($data['smtp_port'] ?? 587);
        $se = $data['smtp_encryption'] ?? 'tls';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || $ih === '' || $sh === '' || $ip < 1 || $sp < 1 || (!$id && $pass === '')) {
            return ['ok' => false, 'error' => 'اطلاعات حساب کامل یا معتبر نیست.'];
        }

        $pdo = db();
        if ($id && $id > 0) {
            if ($pass !== '') {
                $q = $pdo->prepare("UPDATE email_accounts SET email_address=?, display_name=?, imap_host=?, imap_port=?, imap_encryption=?, smtp_host=?, smtp_port=?, smtp_encryption=?, password_encrypted=? WHERE id=?");
                $q->execute([$email, $name, $ih, $ip, $ie, $sh, $sp, $se, encryptMailboxSecret($pass), $id]);
            } else {
                $q = $pdo->prepare("UPDATE email_accounts SET email_address=?, display_name=?, imap_host=?, imap_port=?, imap_encryption=?, smtp_host=?, smtp_port=?, smtp_encryption=? WHERE id=?");
                $q->execute([$email, $name, $ih, $ip, $ie, $sh, $sp, $se, $id]);
            }
        } else {
            $q = $pdo->prepare("INSERT INTO email_accounts (email_address, display_name, imap_host, imap_port, imap_encryption, smtp_host, smtp_port, smtp_encryption, password_encrypted) VALUES (?,?,?,?,?,?,?,?,?)");
            $q->execute([$email, $name, $ih, $ip, $ie, $sh, $sp, $se, encryptMailboxSecret($pass)]);
        }

        return ['ok' => true, 'error' => null];
    }
}

