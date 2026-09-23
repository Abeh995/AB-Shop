<?php
require APP_ROOT . '/views/layout/header.php';

$rawCardDigits = preg_replace('/[^\d]/', '', (string)$cardNumber);
$cardBlocks = str_split($rawCardDigits, 4);
$cardFormatted = implode(' - ', array_map('toPersianDigits', $cardBlocks));
?>

<div class="container section">
    <div class="c2c-wrapper">
        <div class="c2c-header">
            <a href="/checkout" class="c2c-back-link">&larr; بازگشت به تسویه حساب</a>
            
            <div class="c2c-stepper">
                <span class="c2c-step-done">۱. ثبت سفارش و آدرس &#10003;</span>
                <span class="c2c-step-arrow">&larr;</span>
                <span class="c2c-step-active">۲. واریز و ثبت رسید کارت‌به‌کارت</span>
                <span class="c2c-step-arrow">&larr;</span>
                <span>۳. تحویل بسته</span>
            </div>

            <h1 class="c2c-title">💳 پرداخت و ثبت رسید کارت‌به‌کارت</h1>
            <p class="c2c-subtitle">لطفاً مبلغ فاکتور را به شماره کارت زیر واریز کرده و تصویر فیش را برای تایید نهایی سفارش بارگذاری نمایید.</p>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-error" style="margin-bottom: 20px;">
                <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="c2c-grid">
            <!-- Left Main Column -->
            <div class="c2c-main">
                <!-- 1. Amount to Pay -->
                <div class="c2c-amount-card">
                    <div class="c2c-amount-meta">
                        <span class="c2c-amount-label">مبلغ دقیق قابل پرداخت:</span>
                        <div class="c2c-amount-val">
                            <?= toPersianDigits(number_format($grandTotal)) ?>
                            <small>تومان</small>
                        </div>
                    </div>
                    <button type="button" class="c2c-btn-copy-amount" id="btnCopyAmount" data-copy="<?= e((string)$grandTotal) ?>" title="کپی مبلغ برای همراه بانک">
                        📋 <span class="copy-text">کپی مبلغ عددی</span>
                    </button>
                </div>

                <!-- 2. Realistic Luxury Bank Card -->
                <div class="c2c-bank-card">
                    <div class="c2c-card-top">
                        <span class="c2c-card-brand">فروشگاه جوراب اِی‌بی • AB Socks</span>
                        <div class="c2c-card-chip-wrap">
                            <span class="c2c-card-contactless" title="بدون تماس">📶</span>
                            <div class="c2c-card-chip" title="تراشه بانکی"></div>
                        </div>
                    </div>

                    <div class="c2c-card-number-wrap">
                        <div class="c2c-card-number-label">شماره کارت مقصد:</div>
                        <div class="c2c-card-number" dir="ltr">
                            <?php foreach ($cardBlocks as $block): ?>
                                <span><?= toPersianDigits($block) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="c2c-card-bottom">
                        <div class="c2c-card-holder-info">
                            <span class="c2c-card-holder-label">صاحب کارت:</span>
                            <span class="c2c-card-holder-name">👤 <?= e($cardHolder) ?></span>
                        </div>
                        <button type="button" class="c2c-btn-copy-card" id="btnCopyCard" data-copy="<?= e($rawCardDigits) ?>" title="کپی شماره کارت ۱۶ رقمی">
                            📋 <span class="copy-card-text">کپی شماره کارت</span>
                        </button>
                    </div>
                </div>

                <!-- 3. Store Note if any -->
                <?php if ($cardNote): ?>
                    <div class="c2c-note-box">
                        <strong>💡 یادداشت فروشگاه:</strong><br>
                        <?= nl2br(e($cardNote)) ?>
                    </div>
                <?php endif; ?>

                <!-- 4. Modern Drag & Drop Receipt Uploader -->
                <form method="post" action="/payment/card-to-card" id="cardToCardForm">
                    <?= csrfField() ?>
                    <div class="c2c-upload-card">
                        <div class="c2c-upload-header">
                            <h2 class="c2c-upload-title">📷 تصویر رسید یا فیش واریزی</h2>
                            <p class="c2c-upload-desc">فیش را آپلود کنید تا پس از بررسی ادمین، سفارش شما فوراً ارسال شود.</p>
                        </div>

                        <!-- Dropzone Area -->
                        <div class="c2c-dropzone" id="c2cDropzone" tabindex="0">
                            <input type="file" id="c2cFileInput" accept="image/jpeg,image/png,image/webp,.heic,.heif" hidden>
                            
                            <!-- Empty Initial State -->
                            <div id="c2cEmptyState">
                                <div class="c2c-dropzone-icon">🧾</div>
                                <div class="c2c-dropzone-text">
                                    <strong>تصویر فیش را اینجا بکشید</strong> یا برای انتخاب کلیک کنید
                                </div>
                                <div class="c2c-dropzone-sub">
                                    پشتیبانی از انواع تصاویر (JPG, PNG, WebP) و عکس دوربین آیفون
                                </div>
                                <div class="c2c-dropzone-badges">
                                    ⚡ بهینه‌سازی خودکار در مرورگر • حجم کمتر از ۳۰۰ کیلوبایت
                                </div>
                            </div>

                            <!-- Processing State -->
                            <div id="c2cLoadingState" class="c2c-loading-box" hidden>
                                <div class="c2c-spinner"></div>
                                <div class="c2c-loading-text" id="c2cLoadingText">در حال بهینه‌سازی و فشرده‌سازی تصویر رسید...</div>
                            </div>

                            <!-- Preview State -->
                            <div id="c2cPreviewState" class="c2c-preview-card" hidden>
                                <img id="c2cPreviewThumb" class="c2c-preview-thumb" src="" alt="رسید واریز">
                                <div class="c2c-preview-info">
                                    <span class="c2c-preview-title" id="c2cPreviewFilename">رسید پرداخت</span>
                                    <span class="c2c-preview-meta" id="c2cPreviewMeta">حجم: ۶۸ کیلوبایت (WebP بهینه)</span>
                                    <span class="c2c-preview-badge">&#10003; فیش واریز با موفقیت ثبت شد و آماده ارسال است</span>
                                </div>
                                <button type="button" class="c2c-btn-remove" id="c2cBtnRemove" title="حذف یا تعویض تصویر">✕ تغییر</button>
                            </div>
                        </div>

                        <div id="c2cStatusMsg" style="min-height: 22px; font-size: 0.82rem; margin-top: 10px; color: var(--color-danger);" aria-live="polite"></div>

                        <!-- Submit Button -->
                        <button type="submit" class="c2c-submit-btn" id="c2cSubmitBtn" <?= $receiptUploaded ? '' : 'disabled' ?>>
                            🔒 ثبت نهایی سفارش و ارسال رسید
                        </button>
                        <p class="c2c-submit-note">
                            🛡️ پس از ثبت، سفارش در سیستم رزرو شده و وضعیت آن مرحله به مرحله از طریق پیامک به شما اطلاع داده خواهد شد.
                        </p>
                    </div>
                </form>
            </div>

            <!-- Right Sidebar: Order Summary -->
            <aside class="c2c-sidebar">
                <h3 class="c2c-sidebar-title">خلاصه فاکتور سفارش</h3>

                <?php foreach ($cart['items'] as $item): ?>
                    <div class="c2c-item-row">
                        <span class="c2c-item-name"><?= e($item['product']['name']) ?> × <?= toPersianDigits((string)$item['qty']) ?></span>
                        <span class="c2c-item-price"><?= formatPrice($item['line_total']) ?></span>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($postOrderResult['lines'])): ?>
                    <?php foreach ($postOrderResult['lines'] as $line): ?>
                        <div class="c2c-item-row">
                            <span class="c2c-item-name"><?= e($line['name']) ?> × <?= toPersianDigits((string)$line['quantity']) ?></span>
                            <span class="c2c-item-price"><?= formatPrice($line['line_total']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="c2c-summary-divider"></div>

                <div class="c2c-summary-row">
                    <span>جمع اقلام سبد:</span>
                    <span><?= formatPrice($cart['subtotal']) ?></span>
                </div>

                <?php if ($discount > 0): ?>
                    <div class="c2c-summary-row" style="color: var(--color-success); font-weight: 600;">
                        <span>تخفیف کوپن:</span>
                        <span>−<?= formatPrice($discount) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($postOrderResult['total'] > 0): ?>
                    <div class="c2c-summary-row">
                        <span>آیتم‌های ویژه:</span>
                        <span><?= formatPrice($postOrderResult['total']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="c2c-summary-row">
                    <span>هزینه ارسال (<?= e($shippingPreview['method_name']) ?>):</span>
                    <span><?= $shippingPreview['cost'] > 0 ? formatPrice($shippingPreview['cost']) : 'رایگان' ?></span>
                </div>

                <div class="c2c-summary-row total-row">
                    <span>مبلغ کل پرداختی:</span>
                    <span style="color: var(--color-primary-dark); font-size: 1.25rem;"><?= formatPrice($grandTotal) ?></span>
                </div>

                <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed var(--color-border); font-size: 0.8rem; color: var(--color-muted); line-height: 1.6;">
                    📍 گیرنده: <strong><?= e($pending['customer_name']) ?></strong><br>
                    📱 موبایل: <strong dir="ltr"><?= e($pending['phone']) ?></strong><br>
                    🚚 مقصد: <?= e($pending['province']) ?> - <?= e($pending['city']) ?>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
(function () {
    // -------------------------------------------------------------
    // 1. One-click Copy for Card Number & Amount
    // -------------------------------------------------------------
    function setupCopyBtn(btnId, textSelector, successMsg) {
        var btn = document.getElementById(btnId);
        if (!btn) return;
        btn.addEventListener('click', function () {
            var val = btn.getAttribute('data-copy') || '';
            var textEl = btn.querySelector(textSelector);
            var origText = textEl ? textEl.textContent : '';

            var copyAction = function () {
                btn.classList.add('is-copied');
                if (textEl) textEl.textContent = successMsg;
                setTimeout(function () {
                    btn.classList.remove('is-copied');
                    if (textEl) textEl.textContent = origText;
                }, 2200);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(val).then(copyAction).catch(function () {
                    fallbackCopy(val, copyAction);
                });
            } else {
                fallbackCopy(val, copyAction);
            }
        });
    }

    function fallbackCopy(text, cb) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            if (cb) cb();
        } catch (e) {}
        ta.remove();
    }

    setupCopyBtn('btnCopyCard', '.copy-card-text', 'کپی شد! ✓');
    setupCopyBtn('btnCopyAmount', '.copy-text', 'مبلغ کپی شد! ✓');

    // -------------------------------------------------------------
    // 2. Client-Side Image Compression & Drag-and-Drop Uploader
    // -------------------------------------------------------------
    var dropzone = document.getElementById('c2cDropzone');
    var fileInput = document.getElementById('c2cFileInput');
    var emptyState = document.getElementById('c2cEmptyState');
    var loadingState = document.getElementById('c2cLoadingState');
    var loadingText = document.getElementById('c2cLoadingText');
    var previewState = document.getElementById('c2cPreviewState');
    var previewThumb = document.getElementById('c2cPreviewThumb');
    var previewFilename = document.getElementById('c2cPreviewFilename');
    var previewMeta = document.getElementById('c2cPreviewMeta');
    var btnRemove = document.getElementById('c2cBtnRemove');
    var submitBtn = document.getElementById('c2cSubmitBtn');
    var statusMsg = document.getElementById('c2cStatusMsg');
    var csrfInput = document.querySelector('#cardToCardForm input[name="csrf_token"]');

    var currentPreviewUrl = null;

    function formatBytes(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' مگابایت';
        if (bytes >= 1024) return Math.round(bytes / 1024) + ' کیلوبایت';
        return bytes + ' بایت';
    }

    function showStatus(msg, isError) {
        statusMsg.textContent = msg || '';
        statusMsg.style.color = isError ? 'var(--color-danger)' : 'var(--color-success)';
    }

    // Load HEIC converter on demand if needed
    var heicScriptPromise = null;
    function loadHeicLibrary() {
        if (window.heic2any) return Promise.resolve();
        if (heicScriptPromise) return heicScriptPromise;
        heicScriptPromise = new Promise(function (resolve, reject) {
            var s = document.createElement('script');
            s.src = '/assets/js/vendor/heic2any.min.js';
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
        return heicScriptPromise;
    }

    // Proportional canvas resize & WebP compression
    async function compressImageToWebp(file) {
        var sourceBlob = file;
        var isHeic = file.name.match(/\.(heic|heif)$/i) || file.type.includes('heic');

        if (isHeic) {
            loadingText.textContent = 'در حال تبدیل فرمت تصویر آیفون (HEIC)...';
            await loadHeicLibrary();
            var converted = await window.heic2any({ blob: file, toType: 'image/jpeg', quality: 0.90 });
            sourceBlob = Array.isArray(converted) ? converted[0] : converted;
        }

        loadingText.textContent = 'در حال بهینه‌سازی و فشرده‌سازی تصویر رسید...';

        var img = await new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(sourceBlob);
            var image = new Image();
            image.onload = function () {
                URL.revokeObjectURL(url);
                resolve(image);
            };
            image.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('بارگذاری فایل تصویر انجام نشد.'));
            };
            image.src = url;
        });

        var maxDim = 1200;
        var origW = img.naturalWidth;
        var origH = img.naturalHeight;
        var targetW = origW;
        var targetH = origH;

        if (origW > maxDim || origH > maxDim) {
            if (origW >= origH) {
                targetW = maxDim;
                targetH = Math.round((origH * maxDim) / origW);
            } else {
                targetH = maxDim;
                targetW = Math.round((origW * maxDim) / origH);
            }
        }

        var canvas = document.createElement('canvas');
        canvas.width = targetW;
        canvas.height = targetH;
        var ctx = canvas.getContext('2d', { alpha: true });
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(img, 0, 0, targetW, targetH);

        // Attempt 1: quality 0.78
        var webpBlob = await new Promise(function (resolve) {
            canvas.toBlob(resolve, 'image/webp', 0.78);
        });

        // Ensure < 300KB
        if (webpBlob && webpBlob.size > 300 * 1024) {
            webpBlob = await new Promise(function (resolve) {
                canvas.toBlob(resolve, 'image/webp', 0.65);
            });
        }
        if (webpBlob && webpBlob.size > 300 * 1024) {
            webpBlob = await new Promise(function (resolve) {
                canvas.toBlob(resolve, 'image/webp', 0.50);
            });
        }

        // Fallback to jpeg if browser doesn't support canvas WebP export
        if (!webpBlob || webpBlob.size === 0) {
            webpBlob = await new Promise(function (resolve) {
                canvas.toBlob(resolve, 'image/jpeg', 0.75);
            });
        }

        return webpBlob || file;
    }

    async function handleReceiptFile(file) {
        if (!file) return;
        showStatus('', false);

        emptyState.hidden = true;
        previewState.hidden = true;
        loadingState.hidden = false;
        submitBtn.disabled = true;

        try {
            var compressedBlob = await compressImageToWebp(file);
            var uploadFile = new File([compressedBlob], 'receipt.webp', { type: compressedBlob.type || 'image/webp' });

            // Send via AJAX
            loadingText.textContent = 'در حال ذخیره‌سازی امن فیش روی سرور...';
            var formData = new FormData();
            formData.append('csrf_token', csrfInput ? csrfInput.value : '');
            formData.append('receipt', uploadFile);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/ajax/card_to_card_receipt_upload.php');
            xhr.onload = function () {
                loadingState.hidden = true;
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch (e) {}

                if (xhr.status >= 200 && xhr.status < 300 && res && res.ok) {
                    if (currentPreviewUrl) URL.revokeObjectURL(currentPreviewUrl);
                    currentPreviewUrl = URL.createObjectURL(uploadFile);

                    previewThumb.src = currentPreviewUrl;
                    previewFilename.textContent = file.name || 'رسید پرداخت واریزی';
                    previewMeta.textContent = 'حجم بهینه‌شده: ' + formatBytes(res.size || uploadFile.size) + ' (WebP)';
                    previewState.hidden = false;
                    submitBtn.disabled = false;
                    showStatus('✓ تصویر فیش با موفقیت تایید و آماده ثبت سفارش شد.', false);
                } else {
                    emptyState.hidden = false;
                    showStatus(res && res.error ? res.error : 'خطا در بارگذاری فیش. لطفاً دوباره تلاش کنید.', true);
                }
            };
            xhr.onerror = function () {
                loadingState.hidden = true;
                emptyState.hidden = false;
                showStatus('ارتباط با سرور برقرار نشد. اینترنت خود را بررسی کنید.', true);
            };
            xhr.send(formData);
        } catch (err) {
            console.error('Receipt compression error:', err);
            loadingState.hidden = true;
            emptyState.hidden = false;
            showStatus('خطا در پردازش تصویر: ' + (err.message || err), true);
        }
    }

    // Dropzone interactions
    dropzone.addEventListener('click', function (e) {
        if (e.target === btnRemove) return;
        fileInput.click();
    });
    dropzone.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            fileInput.click();
        }
    });
    btnRemove.addEventListener('click', function (e) {
        e.stopPropagation();
        fileInput.value = '';
        if (currentPreviewUrl) URL.revokeObjectURL(currentPreviewUrl);
        currentPreviewUrl = null;
        previewState.hidden = true;
        emptyState.hidden = false;
        submitBtn.disabled = true;
        showStatus('تصویر فیش حذف شد. لطفاً فیش معتبر انتخاب کنید.', true);
    });
    fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files.length) {
            handleReceiptFile(fileInput.files[0]);
        }
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
        dropzone.addEventListener(evt, function (e) {
            e.preventDefault();
            dropzone.classList.add('is-dragover');
        });
    });
    ['dragleave', 'drop'].forEach(function (evt) {
        dropzone.addEventListener(evt, function (e) {
            e.preventDefault();
            dropzone.classList.remove('is-dragover');
        });
    });
    dropzone.addEventListener('drop', function (e) {
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
            handleReceiptFile(e.dataTransfer.files[0]);
        }
    });
})();
</script>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
