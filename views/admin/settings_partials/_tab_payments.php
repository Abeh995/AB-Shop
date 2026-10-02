<?php
/**
 * Settings Partial: Payments, Banking & Invoicing
 */
?>
<div class="settings-tab-pane" id="pane-payments" role="tabpanel">
    <div class="settings-card">
        <div class="settings-card-header">
            <div>
                <h3 class="settings-card-title">💳 مالی، درگاه‌های پرداخت، حساب‌های بانکی و فاکتور</h3>
                <p class="settings-card-subtitle">فعال‌سازی درگاه پرداخت آنلاین، اطلاعات حساب کارت‌به‌کارت و یادداشت چاپی فاکتور</p>
            </div>
        </div>

        <form method="post" action="settings.php">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="payment">
            <input type="hidden" name="tab" value="payments">

            <div class="settings-card-body">

                <!-- 1. Online Payment Gateway (Zarinpal) -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>🌐 پرداخت اینترنتی آنلاین (درگاه زرین‌پال)</span>
                    </div>

                    <label class="settings-toggle-wrap">
                        <div class="toggle-info">
                            <span class="toggle-label">اتصال به درگاه زرین‌پال در مرحله تسویه حساب</span>
                            <p class="toggle-desc">در صورت فعال بودن، مشتریان می‌توانند مبلغ سفارش را به صورت آنلاین و خودکار از طریق تمامی کارت‌های عضو شتاب پرداخت نمایند.</p>
                        </div>
                        <div class="toggle-switch">
                            <input type="checkbox" name="payment_zarinpal_enabled" value="1" <?= ($paymentZarinpalEnabled ?? false) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </div>
                    </label>
                </div>

                <!-- 2. Bank Accounts & Card-to-Card -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>🏦 حساب بانکی و کارت‌به‌کارت فروشگاه</span>
                    </div>

                    <div class="settings-grid-2">
                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">شماره کارت ۱۶ رقمی</label>
                            <input class="form-control mono-num" type="text" name="card_to_card_number" dir="ltr" maxlength="16" value="<?= e($cardToCardNumber ?? '') ?>" placeholder="۶۰۳۷۹۹...">
                            <p class="form-helper">شماره کارتی که خریدار باید وجه را به آن واریز نماید.</p>
                        </div>

                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">نام و نام خانوادگی صاحب حساب</label>
                            <input class="form-control" type="text" name="card_to_card_holder" value="<?= e($cardToCardHolder ?? '') ?>" placeholder="مثلاً: ابوالفضل بهزادی">
                            <p class="form-helper">جهت تطبیق مشتری در برنامه بانکی قبل از انتقال وجه.</p>
                        </div>
                    </div>

                    <div class="settings-grid-2" style="margin-top:14px;">
                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">نام بانک صادرکننده</label>
                            <input class="form-control" type="text" name="store_bank_name" value="<?= e($storeBankName ?? '') ?>" placeholder="مثلاً: بانک ملی ایران">
                        </div>

                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">شماره شبا (IBAN - ۲۴ رقم بدون IR)</label>
                            <div class="settings-input-group">
                                <span class="settings-input-addon">IR</span>
                                <input class="form-control mono-num" type="text" name="store_shaba" dir="ltr" maxlength="24" value="<?= e($storeShaba ?? '') ?>" placeholder="012345678901234567890123">
                            </div>
                            <p class="form-helper">جهت درج در فاکتورهای رسمی و استرداد وجه سفارش‌های لغو شده.</p>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:14px;">
                        <label style="font-size:.82rem; font-weight:700;">راهنما و توضیحات پرداخت کارت‌به‌کارت برای مشتری:</label>
                        <textarea class="form-control" name="card_to_card_note" rows="2" placeholder="لطفاً پس از واریز، شماره پیگیری و تصویر فیش را در سایت آپلود نمایید..."><?= e($cardToCardNote ?? '') ?></textarea>
                    </div>
                </div>

                <!-- 3. Invoicing & VAT -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>🧾 فاکتور چاپی و ساختار مالیاتی</span>
                    </div>

                    <div class="form-group">
                        <label style="font-size:.82rem; font-weight:700;">یادداشت پاورقی فاکتور چاپی:</label>
                        <textarea class="form-control" name="invoice_footer_note" rows="2" placeholder="از خرید و اعتماد شما سپاسگزاریم. شرایط مرجوعی کالا تا ۷ روز پس از دریافت..."><?= e($invoiceFooterNote ?? '') ?></textarea>
                        <p class="form-helper">این متن در پایین فاکتورهای چاپی سفارش‌ها چاپ می‌شود.</p>
                    </div>

                    <div style="margin-top:14px;">
                        <label class="settings-toggle-wrap" style="margin-bottom:10px;">
                            <div class="toggle-info">
                                <span class="toggle-label">محاسبه مالیات بر ارزش افزوده (VAT)</span>
                                <p class="toggle-desc">در صورت فعال‌سازی، درصد تعیین‌شده به عنوان ردیف مالیات به جمع سفارش اضافه می‌گردد (پیش‌فرض: غیرفعال).</p>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="tax_enabled" value="1" <?= ($taxEnabled ?? false) ? 'checked' : '' ?> onchange="document.getElementById('taxPercentGroup').style.display = this.checked ? 'block' : 'none';">
                                <span class="toggle-slider"></span>
                            </div>
                        </label>

                        <div id="taxPercentGroup" style="<?= ($taxEnabled ?? false) ? '' : 'display:none;' ?>; max-width:240px; margin-top:8px;">
                            <label style="font-size:.82rem; font-weight:700;">درصد مالیات بر ارزش افزوده:</label>
                            <div class="settings-input-group">
                                <input class="form-control mono-num" type="number" name="tax_percentage" min="0" max="100" step="0.5" value="<?= (float)($taxPercentage ?? 0) ?>">
                                <span class="settings-input-addon addon-suffix">٪</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-form-actions">
                    <button type="submit" class="btn-set-save">
                        <span>ذخیره تنظیمات مالی و پرداخت</span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
