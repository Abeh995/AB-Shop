<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container section">
    <div class="payment-page-head">
        <a href="/checkout" class="payment-back-link">← بازگشت به تسویه حساب</a>
        <h1>پرداخت کارت‌به‌کارت</h1>
        <p>مبلغ نهایی را به کارت زیر واریز کنید و تصویر فیش را برای بررسی سفارش ارسال کنید.</p>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card-to-card-layout">
        <div>
            <div class="payment-card payment-card-highlight">
                <div class="payment-card-label">مبلغ قابل پرداخت</div>
                <button type="button" class="copy-value payment-amount" data-copy-value="<?= e((string)$grandTotal) ?>" title="کپی مبلغ">
                    <span><?= formatPrice($grandTotal) ?></span>
                    <span class="copy-hint">کپی</span>
                </button>
            </div>

            <div class="payment-card">
                <div class="payment-card-label">اطلاعات کارت مقصد</div>
                <div class="payment-detail-row">
                    <span>شماره کارت</span>
                    <button type="button" class="copy-value copy-value-large" data-copy-value="<?= e($cardNumber) ?>">
                        <span dir="ltr"><?= e($cardNumber) ?></span><span class="copy-hint">کپی</span>
                    </button>
                </div>
                <div class="payment-detail-row">
                    <span>به نام</span>
                    <button type="button" class="copy-value" data-copy-value="<?= e($cardHolder) ?>">
                        <span><?= e($cardHolder) ?></span><span class="copy-hint">کپی</span>
                    </button>
                </div>
                <?php if ($cardNote): ?>
                <div class="payment-note"><?= nl2br(e($cardNote)) ?></div>
                <?php endif; ?>
            </div>

            <form method="post" action="/payment/card-to-card" id="cardToCardForm">
                <?= csrfField() ?>
                <div class="payment-card">
                    <div class="payment-card-label">تصویر فیش واریز</div>
                    <p class="upload-help">تصویر رسید کارت‌به‌کارت را اینجا بکشید یا برای انتخاب فایل کلیک کنید. حداکثر حجم: ۲ مگابایت.</p>

                    <div class="receipt-dropzone" id="receiptDropzone" tabindex="0">
                        <input type="file" id="receiptInput" accept="image/jpeg,image/png,image/webp" hidden>
                        <div class="receipt-empty" id="receiptEmpty">
                            <div class="receipt-upload-icon">↑</div>
                            <strong>رسید را اینجا رها کنید</strong>
                            <span>یا برای انتخاب تصویر کلیک کنید</span>
                        </div>
                        <div class="receipt-preview" id="receiptPreview" hidden>
                            <img id="receiptPreviewImage" alt="پیش‌نمایش رسید">
                            <div class="receipt-preview-overlay">
                                <span id="receiptFileName"></span>
                                <button type="button" class="btn btn-sm btn-outline" id="changeReceiptButton">تغییر تصویر</button>
                            </div>
                        </div>
                        <div class="receipt-progress" id="receiptProgress" hidden>
                            <div class="receipt-progress-top"><span>در حال بارگذاری رسید...</span><strong id="receiptProgressValue">0%</strong></div>
                            <div class="receipt-progress-track"><div id="receiptProgressBar"></div></div>
                        </div>
                    </div>
                    <div class="receipt-upload-status" id="receiptUploadStatus" aria-live="polite">
                        <?php if ($receiptUploaded): ?>تصویر رسید آماده ارسال است.<?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="submitCardPayment" <?= $receiptUploaded ? '' : 'disabled' ?>>ثبت سفارش و ارسال رسید</button>
                    <p class="payment-submit-note">پس از ثبت، سفارش شما در وضعیت «در انتظار بررسی» قرار می‌گیرد و رسید توسط ادمین بررسی می‌شود.</p>
                </div>
            </form>
        </div>

        <aside class="order-review payment-order-review">
            <h3 style="margin-bottom:14px;">خلاصه سفارش</h3>
            <?php foreach ($cart['items'] as $item): ?>
                <div class="item-line">
                    <span><?= e($item['product']['name']) ?> × <?= toPersianDigits((string)$item['qty']) ?></span>
                    <span><?= formatPrice($item['line_total']) ?></span>
                </div>
            <?php endforeach; ?>
            <?php foreach ($postOrderResult['lines'] as $line): ?>
                <div class="item-line"><span><?= e($line['name']) ?> × <?= toPersianDigits((string)$line['quantity']) ?></span><span><?= formatPrice($line['line_total']) ?></span></div>
            <?php endforeach; ?>
            <div class="row" style="margin-top:14px;"><span>جمع کالاها</span><span><?= formatPrice($cart['subtotal']) ?></span></div>
            <?php if ($discount > 0): ?><div class="row" style="color:var(--color-success);"><span>تخفیف</span><span>−<?= formatPrice($discount) ?></span></div><?php endif; ?>
            <?php if ($postOrderResult['total'] > 0): ?><div class="row"><span>پیشنهاد بعد از سبد</span><span><?= formatPrice($postOrderResult['total']) ?></span></div><?php endif; ?>
            <div class="row"><span>هزینه ارسال</span><span><?= $shippingPreview['cost'] > 0 ? formatPrice($shippingPreview['cost']) : 'رایگان' ?></span></div>
            <div class="row total-row"><span>مبلغ نهایی</span><span><?= formatPrice($grandTotal) ?></span></div>
        </aside>
    </div>
</div>

<script>
(function () {
    var dropzone = document.getElementById('receiptDropzone');
    var input = document.getElementById('receiptInput');
    var empty = document.getElementById('receiptEmpty');
    var preview = document.getElementById('receiptPreview');
    var previewImage = document.getElementById('receiptPreviewImage');
    var fileName = document.getElementById('receiptFileName');
    var progress = document.getElementById('receiptProgress');
    var progressBar = document.getElementById('receiptProgressBar');
    var progressValue = document.getElementById('receiptProgressValue');
    var status = document.getElementById('receiptUploadStatus');
    var submit = document.getElementById('submitCardPayment');
    var changeButton = document.getElementById('changeReceiptButton');
    var csrf = document.querySelector('#cardToCardForm input[name="csrf_token"]');
    var currentObjectUrl = null;

    function setStatus(text, isError) {
        status.textContent = text || '';
        status.className = 'receipt-upload-status' + (isError ? ' is-error' : '');
    }

    function upload(file) {
        if (!file) return;
        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
            setStatus('فقط تصاویر JPG، PNG یا WEBP مجاز هستند.', true);
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            setStatus('حجم تصویر باید حداکثر ۲ مگابایت باشد.', true);
            return;
        }

        if (currentObjectUrl) URL.revokeObjectURL(currentObjectUrl);
        currentObjectUrl = URL.createObjectURL(file);
        previewImage.src = currentObjectUrl;
        fileName.textContent = file.name;
        empty.hidden = true;
        preview.hidden = false;
        progress.hidden = false;
        progressBar.style.width = '0%';
        progressValue.textContent = '0%';
        submit.disabled = true;
        setStatus('در حال بارگذاری تصویر...', false);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/ajax/card_to_card_receipt_upload.php');
        xhr.upload.addEventListener('progress', function (event) {
            if (!event.lengthComputable) return;
            var percent = Math.round((event.loaded / event.total) * 100);
            progressBar.style.width = percent + '%';
            progressValue.textContent = percent + '%';
        });
        xhr.onload = function () {
            progress.hidden = true;
            var data = null;
            try { data = JSON.parse(xhr.responseText); } catch (e) {}
            if (xhr.status >= 200 && xhr.status < 300 && data && data.ok) {
                submit.disabled = false;
                setStatus('تصویر رسید با موفقیت بارگذاری شد و آماده ارسال است.', false);
            } else {
                submit.disabled = true;
                setStatus(data && data.error ? data.error : 'بارگذاری تصویر ناموفق بود.', true);
            }
        };
        xhr.onerror = function () {
            progress.hidden = true;
            submit.disabled = true;
            setStatus('ارتباط با سرور هنگام بارگذاری تصویر قطع شد.', true);
        };
        var formData = new FormData();
        formData.append('csrf_token', csrf ? csrf.value : '');
        formData.append('receipt', file);
        xhr.send(formData);
    }

    dropzone.addEventListener('click', function (event) {
        if (event.target === changeButton) return;
        input.click();
    });
    dropzone.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            input.click();
        }
    });
    changeButton.addEventListener('click', function (event) {
        event.stopPropagation();
        input.click();
    });
    input.addEventListener('change', function () {
        upload(input.files[0]);
        input.value = '';
    });
    ['dragenter', 'dragover'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            dropzone.classList.add('is-dragover');
        });
    });
    ['dragleave', 'drop'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            dropzone.classList.remove('is-dragover');
        });
    });
    dropzone.addEventListener('drop', function (event) {
        upload(event.dataTransfer.files[0]);
    });

    document.querySelectorAll('[data-copy-value]').forEach(function (button) {
        button.addEventListener('click', function () {
            var value = button.getAttribute('data-copy-value') || '';
            var done = function () {
                var hint = button.querySelector('.copy-hint');
                if (!hint) return;
                var original = hint.textContent;
                hint.textContent = 'کپی شد ✓';
                setTimeout(function () { hint.textContent = original; }, 1400);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(value).then(done).catch(function () {});
            } else {
                var temp = document.createElement('textarea');
                temp.value = value; temp.style.position = 'fixed'; temp.style.opacity = '0';
                document.body.appendChild(temp); temp.select();
                try { document.execCommand('copy'); done(); } catch (e) {}
                temp.remove();
            }
        });
    });
})();
</script>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
