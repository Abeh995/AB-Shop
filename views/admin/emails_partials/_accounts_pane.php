<?php
/**
 * Email Studio: Accounts Management & Live Server Diagnostics
 */
?>
<div style="display:flex; flex-direction:column; gap:20px;">

    <!-- 1. Form to Add/Edit Account -->
    <div class="account-card" style="border:2px solid var(--mail-border);">
        <div class="account-card-header">
            <div>
                <h3 class="account-card-title"><?= $editAccount ? 'ویرایش حساب سازمانی' : 'افزودن حساب سازمانی جدید (IMAP / SMTP)' ?></h3>
                <span class="account-card-email">
                    رمز عبور صندوق‌ها در پایگاه داده با استاندارد AES-256 رمزنگاری می‌شود و در پنل نمایش داده نخواهد شد.
                </span>
            </div>
            <?php if ($editAccount): ?>
                <a href="/admin/emails.php?tab=accounts" class="btn btn-sm btn-outline">انصراف از ویرایش</a>
            <?php endif; ?>
        </div>

        <form method="post" action="/admin/emails.php?tab=accounts">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save_account">
            <input type="hidden" name="id" value="<?= (int)($editAccount['id'] ?? 0) ?>">

            <div class="mail-form-row">
                <div class="mail-form-group">
                    <label>آدرس ایمیل سازمانی *</label>
                    <input class="mail-form-control" type="email" name="email_address" dir="ltr" required 
                           value="<?= e($editAccount['email_address'] ?? '') ?>" placeholder="info@absocks.ir">
                </div>
                <div class="mail-form-group">
                    <label>نام نمایشی فرستنده *</label>
                    <input class="mail-form-control" type="text" name="display_name" required 
                           value="<?= e($editAccount['display_name'] ?? 'پشتیبانی جوراب AB') ?>" placeholder="پشتیبانی جوراب AB">
                </div>
                <div class="mail-form-group">
                    <label>رمز عبور ایمیل <?= $editAccount ? '(فقط جهت تغییر وارد کنید)' : '*' ?></label>
                    <input class="mail-form-control" type="password" name="password" autocomplete="new-password" 
                           <?= $editAccount ? '' : 'required' ?> placeholder="••••••••">
                </div>
            </div>

            <div class="mail-form-row" style="margin-top:10px;">
                <div class="mail-form-group">
                    <label>سرور ورودی (IMAP Host) *</label>
                    <input class="mail-form-control" type="text" name="imap_host" dir="ltr" required 
                           value="<?= e($editAccount['imap_host'] ?? 'mail.absocks.ir') ?>">
                </div>
                <div class="mail-form-group">
                    <label>پورت IMAP</label>
                    <input class="mail-form-control" type="number" name="imap_port" dir="ltr" 
                           value="<?= (int)($editAccount['imap_port'] ?? 993) ?>">
                </div>
                <div class="mail-form-group">
                    <label>رمزنگاری IMAP</label>
                    <select class="mail-form-control" name="imap_encryption">
                        <option value="ssl" <?= ($editAccount['imap_encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' ?>>SSL (پیش‌فرض ۹۹۳)</option>
                        <option value="tls" <?= ($editAccount['imap_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>STARTTLS</option>
                    </select>
                </div>
            </div>

            <div class="mail-form-row" style="margin-top:10px;">
                <div class="mail-form-group">
                    <label>سرور خروجی (SMTP Host) *</label>
                    <input class="mail-form-control" type="text" name="smtp_host" dir="ltr" required 
                           value="<?= e($editAccount['smtp_host'] ?? 'mail.absocks.ir') ?>">
                </div>
                <div class="mail-form-group">
                    <label>پورت SMTP</label>
                    <input class="mail-form-control" type="number" name="smtp_port" dir="ltr" 
                           value="<?= (int)($editAccount['smtp_port'] ?? 587) ?>">
                </div>
                <div class="mail-form-group">
                    <label>رمزنگاری SMTP</label>
                    <select class="mail-form-control" name="smtp_encryption">
                        <option value="tls" <?= ($editAccount['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>STARTTLS (پیش‌فرض ۵۸۷)</option>
                        <option value="ssl" <?= ($editAccount['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (۴۶۵)</option>
                    </select>
                </div>
            </div>

            <div class="mail-form-group" style="margin-top:10px;">
                <label>امضای رسمی در انتهای ایمیل‌های ارسالی (اختیاری)</label>
                <textarea class="mail-form-control" name="signature" rows="2" placeholder="با احترام، تیم فروشگاه جوراب AB | پشتیبانی: ۰۹۱۲..."><?= e($editAccount['signature'] ?? '') ?></textarea>
            </div>

            <div style="margin-top:16px; display:flex; justify-content:flex-end;">
                <button type="submit" class="btn btn-primary" style="padding:10px 24px; font-weight:700;">
                    <?= $editAccount ? 'بروزرسانی حساب سازمانی' : 'افزودن و ذخیره حساب' ?>
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Existing Accounts List -->
    <div class="accounts-grid">
        <?php foreach ($allAccounts as $a): ?>
            <div class="account-card">
                <div>
                    <div class="account-card-header">
                        <div>
                            <h4 class="account-card-title"><?= e($a['display_name']) ?></h4>
                            <span class="account-card-email"><?= e($a['email_address']) ?></span>
                        </div>
                        <span class="badge" style="background:<?= $a['is_active'] ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' ?>; color:<?= $a['is_active'] ? '#059669' : '#DC2626' ?>; padding:4px 8px; border-radius:6px; font-size:.76rem; font-weight:700;">
                            <?= $a['is_active'] ? 'فعال' : 'غیرفعال' ?>
                        </span>
                    </div>

                    <ul class="account-detail-list">
                        <li>
                            <span>دریافت (IMAP):</span>
                            <span dir="ltr"><b><?= e($a['imap_host']) ?>:<?= (int)$a['imap_port'] ?></b> (<?= e($a['imap_encryption']) ?>)</span>
                        </li>
                        <li>
                            <span>ارسال (SMTP):</span>
                            <span dir="ltr"><b><?= e($a['smtp_host']) ?>:<?= (int)$a['smtp_port'] ?></b> (<?= e($a['smtp_encryption']) ?>)</span>
                        </li>
                        <?php if (!empty($a['signature'])): ?>
                            <li style="margin-top:4px; font-size:.78rem; color:var(--mail-text-muted);">
                                <span>امضا:</span>
                                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;"><?= e($a['signature']) ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="account-actions">
                    <a href="/admin/emails.php?account=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline">
                        ورود به اینباکس
                    </a>
                    <a href="/admin/emails.php?tab=accounts&edit_account=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline">
                        ویرایش
                    </a>

                    <form method="post" action="/admin/emails.php?tab=accounts" style="margin:0; display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="toggle_account">
                        <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline" title="<?= $a['is_active'] ? 'غیرفعال‌سازی' : 'فعال‌سازی' ?>">
                            <?= $a['is_active'] ? 'تعلیق' : 'فعال' ?>
                        </button>
                    </form>

                    <form method="post" action="/admin/emails.php?tab=accounts" onsubmit="return confirm('آیا از حذف این حساب سازمانی اطمینان دارید؟');" style="margin:0; display:inline; margin-right:auto;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete_account">
                        <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background:#fee2e2; color:#b91c1c; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;" title="حذف حساب">
                            حذف
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
