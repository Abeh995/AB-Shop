<?php
/**
 * Email Studio: Smart Compose Modal with Canned Responses
 */
?>
<div class="email-modal-overlay" id="composeModal">
    <div class="email-modal-card">
        <div class="email-modal-header">
            <h3>✉️ ارسال ایمیل جدید</h3>
            <button type="button" class="btn btn-sm btn-outline" onclick="closeComposeModal()" style="border:none; font-size:1.2rem; cursor:pointer;">✕</button>
        </div>

        <form method="post" action="/admin/emails.php?account=<?= (int)$selectedId ?>" class="email-modal-body">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="send">

            <div class="mail-form-group">
                <label>ارسال از حساب سازمانی:</label>
                <select class="mail-form-control" name="account_id" id="composeAccountSelect" required>
                    <?php foreach ($accounts as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" <?= $selectedId === (int)$a['id'] ? 'selected' : '' ?>>
                            <?= e($a['display_name']) ?> &lt;<?= e($a['email_address']) ?>&gt;
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mail-form-group">
                <label>ایمیل گیرنده *</label>
                <input class="mail-form-control" type="email" name="to" id="composeTo" dir="ltr" required placeholder="customer@example.com">
            </div>

            <div class="mail-form-group">
                <label>موضوع ایمیل *</label>
                <input class="mail-form-control" type="text" name="subject" id="composeSubject" required placeholder="مثلاً: وضعیت سفارش شما در جوراب AB">
            </div>

            <div class="mail-form-group">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                    <label style="margin:0;">متن پیام *</label>
                    <select class="mail-form-control" id="cannedSelect" onchange="applyCannedTemplate(this.value)" style="width:auto; padding:4px 8px; font-size:.78rem;">
                        <option value="">-- درج متن آماده (الگو) --</option>
                        <option value="tracking">ارسال کد رهگیری پستی</option>
                        <option value="c2c_approved">تایید واریز کارت‌به‌کارت</option>
                        <option value="stock_inquiry">پاسخ استعلام موجودی کالا</option>
                        <option value="general_greeting">خوش‌آمدگویی و پشتیبانی عمومی</option>
                    </select>
                </div>
                <textarea class="mail-form-control" name="body" id="composeBody" rows="10" required placeholder="متن پیام خود را بنویسید..."></textarea>
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:20px; padding-top:14px; border-top:1px solid var(--mail-border);">
                <button type="button" class="btn btn-outline" onclick="closeComposeModal()">انصراف</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 28px; font-weight:700;">
                    ارسال ایمیل ✉️
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openComposeModal(prefillTo, prefillSubject) {
    if (prefillTo) document.getElementById('composeTo').value = prefillTo;
    if (prefillSubject) document.getElementById('composeSubject').value = prefillSubject;
    document.getElementById('composeModal').classList.add('active');
}

function closeComposeModal() {
    document.getElementById('composeModal').classList.remove('active');
}

function openReplyModal(sender, subject) {
    // Extract raw email if format is "Name <email@domain>"
    let emailMatch = sender.match(/<([^>]+)>/);
    let toEmail = emailMatch ? emailMatch[1] : sender.trim();
    openComposeModal(toEmail, subject);
}

function applyCannedTemplate(key) {
    const body = document.getElementById('composeBody');
    const subject = document.getElementById('composeSubject');
    if (!key) return;

    if (key === 'tracking') {
        if (!subject.value) subject.value = 'ارسال سفارش و کد رهگیری پستی — جوراب AB';
        body.value = "سلام و درود،\nسفارش شما بسته‌بندی و تحویل شرکت پست گردید.\n\nکد رهگیری مرسوله پستی:\n[کد رهگیری را اینجا بنویسید]\n\nجهت پیگیری آنلاین می‌توانید به سامانه tracking.post.ir مراجعه فرمایید.\nاز خرید و اعتماد شما صمیمانه سپاسگزاریم.";
    } else if (key === 'c2c_approved') {
        if (!subject.value) subject.value = 'تایید فیش واریز بانکی — جوراب AB';
        body.value = "سلام و احترام،\nفیش واریزی کارت‌به‌کارت شما برای سفارش با موفقیت بررسی و تایید گردید.\nسفارش شما در فرآیند آماده‌سازی و ارسال قرار گرفت.\n\nبا احترام,\nپشتیبانی فروشگاه جوراب AB";
    } else if (key === 'stock_inquiry') {
        if (!subject.value) subject.value = 'پاسخ به استعلام موجودی جوراب AB';
        body.value = "سلام و احترام،\nدر پاسخ به پرسش شما در خصوص موجودی کالا، محصول مورد نظر بررسی شد و در انبار موجود/ناموجود می‌باشد.\nدر صورت تمایل به ثبت سفارش می‌توانید از طریق سایت اقدام فرمایید.";
    } else if (key === 'general_greeting') {
        body.value = "سلام و احترام،\nامیدواریم حالتان عالی باشد.\nدر رابطه با پیام ارسالی شما:\n\n[متن خود را بنویسید]\n\nهمواره آماده پاسخگویی به شما هستیم.";
    }
}

// Close modal on click outside
window.addEventListener('click', function(e) {
    const modal = document.getElementById('composeModal');
    if (e.target === modal) {
        closeComposeModal();
    }
});
</script>
