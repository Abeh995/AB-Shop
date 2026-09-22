<?php
$pageTitle='ارسال ایمیل'; $accounts=MailboxService::accounts();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf(); $id=(int)($_POST['account_id']??0); $a=MailboxService::account($id); $to=trim($_POST['to']??''); $subject=trim($_POST['subject']??''); $body=trim($_POST['body']??'');
    if(!$a || !filter_var($to,FILTER_VALIDATE_EMAIL) || $subject==='' || $body===''){ $error='اطلاعات ایمیل کامل یا معتبر نیست.'; } else { $r=MailboxService::send($a,$to,$subject,$body); if($r['ok']){ header('Location: /admin/emails.php?account='.$id.'&sent=1'); exit; } $error=$r['error']; }
}
renderView('admin/email_compose', compact('pageTitle','accounts','error'));
