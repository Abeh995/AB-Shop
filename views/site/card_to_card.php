<?php
$pageStylesheets = ['/assets/css/client-c2c.css'];
$pageScripts = ['/assets/js/client/card-to-card.js'];
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

        <?php site_component('alert', [
            'state' => 'error',
            'messages' => $errors ?? [],
            'class' => 'c-mb-4',
        ]); ?>

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
                    <button type="button" class="c2c-btn-copy-amount" id="btnCopyAmount" data-ab-copy="<?= e((string)$grandTotal) ?>" data-ab-copy-feedback="مبلغ کپی شد! ✓" data-ab-copy-toast="مبلغ سفارش کپی شد." title="کپی مبلغ برای همراه بانک">
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
                        <button type="button" class="c2c-btn-copy-card" id="btnCopyCard" data-ab-copy="<?= e($rawCardDigits) ?>" data-ab-copy-feedback="کپی شد! ✓" data-ab-copy-toast="شماره کارت کپی شد." title="کپی شماره کارت ۱۶ رقمی">
                            📋 <span class="copy-card-text">کپی شماره کارت</span>
                        </button>
                    </div>
                    <?php if (!empty($storeShaba)): ?>
                        <div class="c2c-shaba-row">
                            <span class="c2c-shaba-label">شماره شبا:</span>
                            <span dir="ltr" class="c2c-shaba-code">IR<?= e($storeShaba) ?></span>
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
                        <div class="c2c-dropzone" id="c2cDropzone" data-ab-c2c-uploader data-ab-heic-src="<?= e(asset('/assets/js/vendor/heic2any.min.js')) ?>" tabindex="0">
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
                                <img id="c2cPreviewThumb" class="c2c-preview-thumb" width="72" height="72" src="" alt="رسید واریز">
                                <div class="c2c-preview-info">
                                    <span class="c2c-preview-title" id="c2cPreviewFilename">رسید پرداخت</span>
                                    <span class="c2c-preview-meta" id="c2cPreviewMeta">حجم: ۶۸ کیلوبایت (WebP بهینه)</span>
                                    <span class="c2c-preview-badge">&#10003; فیش واریز با موفقیت ثبت شد و آماده ارسال است</span>
                                </div>
                                <button type="button" class="c2c-btn-remove" id="c2cBtnRemove" title="حذف یا تعویض تصویر">✕ تغییر</button>
                            </div>
                        </div>

                        <div id="c2cStatusMsg" class="c2c-status-msg" aria-live="polite"></div>

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
            <?php
            $c2cRecipientHtml = '<div class="c2c-recipient-meta">'
                . '📍 گیرنده: <strong>' . e($pending['customer_name'] ?? '') . '</strong><br>'
                . '📱 موبایل: <strong dir="ltr">' . e($pending['phone'] ?? '') . '</strong><br>'
                . '🚚 مقصد: ' . e($pending['province'] ?? '') . ' - ' . e($pending['city'] ?? '')
                . '</div>';

            site_component('order_summary', [
                'title' => 'خلاصه فاکتور سفارش',
                'class' => 'c2c-sidebar',
                'items' => $cart['items'],
                'giftLines' => $postOrderResult['lines'] ?? [],
                'subtotal' => $cart['subtotal'],
                'discount' => $discount,
                'couponCode' => $appliedCoupon['code'] ?? '',
                'postOrderTotal' => $postOrderResult['total'] ?? 0,
                'shippingLabel' => 'هزینه ارسال (' . ($shippingPreview['method_name'] ?? '') . ')',
                'shippingCost' => (float) ($shippingPreview['cost'] ?? 0),
                'grandTotal' => $grandTotal,
                'totalLabel' => 'مبلغ کل پرداختی',
                'footerHtml' => $c2cRecipientHtml,
            ]);
            ?>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
