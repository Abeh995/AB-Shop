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
                            <span class="c2c-card-holder-name">👤 <?= e($cardHolder) ?><?= !empty($storeBankName) ? ' (' . e($storeBankName) . ')' : '' ?></span>
                        </div>
                        <button type="button" class="c2c-btn-copy-card" id="btnCopyCard" data-copy="<?= e($rawCardDigits) ?>" title="کپی شماره کارت ۱۶ رقمی">
                            📋 <span class="copy-card-text">کپی شماره کارت</span>
                        </button>
                    </div>
                    <?php if (!empty($storeShaba)): ?>
                        <div style="margin-top:10px; padding-top:8px; border-top:1px dashed rgba(255,255,255,0.25); font-size:0.75rem; display:flex; justify-content:space-between; align-items:center;">
                            <span style="opacity:0.85;">شماره شبا:</span>
                            <span dir="ltr" style="font-family:monospace; letter-spacing:1px; font-weight:700;">IR<?= e($storeShaba) ?></span>
                        </div>
                    <?php endif; ?>
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
                                    فرمت‌های مجاز: JPG، PNG، WebP (حداکثر ۵ مگابایت)
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

<script src="/assets/js/card-to-card.js?v=<?= APP_VERSION ?>" defer></script>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
