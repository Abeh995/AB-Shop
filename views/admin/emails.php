<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>
<div class="admin-card email-client">
 <div class="email-toolbar"><h3>صندوق ایمیل</h3><a class="btn btn-primary" href="/admin/email_compose.php">✉️ ارسال ایمیل</a></div>
 <?php if (!$accounts): ?><p>هنوز هیچ حساب ایمیلی در پنل ثبت نشده است.</p><a class="btn btn-outline" href="/admin/email_accounts.php">افزودن حساب ایمیل</a>
 <?php else: ?>
 <div class="email-switcher"><label>حساب ایمیل:</label><select onchange="location.href='/admin/emails.php?account='+this.value"><option value="">انتخاب...</option><?php foreach($accounts as $a): ?><option value="<?= (int)$a['id'] ?>" <?= $selectedId==(int)$a['id']?'selected':'' ?>><?= e($a['display_name'].' — '.$a['email_address']) ?></option><?php endforeach; ?></select></div>
 <?php if(isset($_GET['sent'])):?><div class="alert alert-success">ایمیل با موفقیت ارسال شد.</div><?php endif; ?>
 <?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php elseif($account): ?>
 <table class="admin-table"><thead><tr><th>وضعیت</th><th>فرستنده</th><th>موضوع</th><th>تاریخ</th></tr></thead><tbody>
 <?php foreach($messages as $m): ?><tr class="<?= $m['seen']?'':'email-unread' ?>"><td><?= $m['seen']?'خوانده':'جدید' ?></td><td><?=e($m['from'])?></td><td><a href="/admin/email_read.php?account=<?=$selectedId?>&uid=<?=$m['uid']?>"><?=e($m['subject'])?></a></td><td dir="ltr"><?=e($m['date'])?></td></tr><?php endforeach; ?>
 <?php if(!$messages): ?><tr><td colspan="4">صندوق ورودی خالی است.</td></tr><?php endif; ?></tbody></table>
 <?php elseif(!$error): ?><p>یک حساب را انتخاب کنید.</p><?php endif; ?><?php endif; ?>
</div>
