<?php
$pageTitle='مشاهده ایمیل'; $id=(int)($_GET['account']??0); $uid=(int)($_GET['uid']??0); $account=MailboxService::account($id); $message=null; $error=null;
if(!$account || !$uid){$error='ایمیل نامعتبر است.';} else {try{$message=MailboxService::read($account,$uid);}catch(Throwable $e){$error=$e->getMessage();}}
renderView('admin/email_read', compact('pageTitle','account','message','error'));
