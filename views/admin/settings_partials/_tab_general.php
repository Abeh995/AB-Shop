<?php
/**
 * Settings Partial: General Business Info & Contact Details
 */
?>
<div class="settings-tab-pane" id="pane-general" role="tabpanel">
    <div class="settings-card">
        <div class="settings-card-header">
            <div>
                <h3 class="settings-card-title">🏢 هویت کسب‌وکار، اطلاعات تماس و شناسه رسمی</h3>
                <p class="settings-card-subtitle">اطلاعات نمایش‌یافته در صفحه تماس با ما، فوتر، فاکتورها و هشدارهای مدیریت</p>
            </div>
        </div>

        <form method="post" action="settings.php">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="business">
            <input type="hidden" name="tab" value="general">

            <div class="settings-card-body">

                <!-- 1. Contact Information -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>📞 راه‌های ارتباطی و پاسخ‌گویی</span>
                    </div>

                    <div class="settings-grid-2">
                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">ایمیل پشتیبانی مشتریان</label>
                            <input class="form-control" type="email" name="store_email" dir="ltr" value="<?= e($storeEmail ?? '') ?>" placeholder="support@ab-socks.ir">
                        </div>

                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">شماره همراه پشتیبانی / واتساپ</label>
                            <input class="form-control mono-num" type="text" name="store_mobile" dir="ltr" value="<?= e($storeMobile ?? '') ?>" placeholder="09123456789">
                        </div>
                    </div>

                    <div class="settings-grid-2" style="margin-top:14px;">
                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">تلفن ثابت دفتر یا انبار</label>
                            <input class="form-control mono-num" type="text" name="store_phone" dir="ltr" value="<?= e($storePhone ?? '') ?>" placeholder="021-12345678">
                        </div>

                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">ساعات پاسخ‌گویی و کاری</label>
                            <input class="form-control" type="text" name="store_support_hours" value="<?= e($storeSupportHours ?? '') ?>" placeholder="شنبه تا چهارشنبه ۹ الی ۱۸">
                        </div>
                    </div>
                </div>

                <!-- 2. Address & Warehouse Location -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>📍 نشانی دفتر، انبار و کد پستی</span>
                    </div>

                    <div class="form-group">
                        <label style="font-size:.82rem; font-weight:700;">نشانی پستی کامل</label>
                        <input class="form-control" type="text" name="store_address" value="<?= e($storeAddress ?? '') ?>" placeholder="استان، شهر، خیابان، پلاک، واحد">
                    </div>

                    <div class="settings-grid-2" style="margin-top:14px;">
                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">کد پستی انبار (۱۰ رقمی)</label>
                            <input class="form-control mono-num" type="text" name="store_postal_code" dir="ltr" maxlength="10" value="<?= e($storePostalCode ?? '') ?>" placeholder="1234567890">
                        </div>

                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">تاریخ آغاز فعالیت فروشگاه</label>
                            <input class="form-control" type="text" name="store_start_date" value="<?= e($storeStartDate ?? '') ?>" placeholder="مثلاً: مرداد ۱۴۰۳">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:14px;">
                        <label style="font-size:.82rem; font-weight:700;">متن معرفی در بالای صفحه «تماس با ما»:</label>
                        <textarea class="form-control" name="contact_intro" rows="2" placeholder="همواره مشتاق شنیدن نظرات، انتقادات و راهنمایی شما عزیزان هستیم..."><?= e($contactIntro ?? '') ?></textarea>
                    </div>
                </div>

                <!-- 3. Legal / Administrative IDs -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>📋 شناسه‌های حقوقی و هشدارهای مدیریت</span>
                    </div>

                    <div class="settings-grid-2">
                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">کد اقتصادی / شناسه صنفی (اختیاری)</label>
                            <input class="form-control mono-num" type="text" name="store_economic_code" dir="ltr" value="<?= e($storeEconomicCode ?? '') ?>" placeholder="شناسه اقتصادی ۱۲ رقمی">
                            <p class="form-helper">جهت درج در سربرگ فاکتورهای رسمی.</p>
                        </div>

                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">شناسه ملی یا کد ملی مدیر</label>
                            <input class="form-control mono-num" type="text" name="store_national_id" dir="ltr" value="<?= e($storeNationalId ?? '') ?>" placeholder="کد ۱۰ رقمی یا شناسه ۱۱ رقمی">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:14px; max-width:480px;">
                        <label style="font-size:.82rem; font-weight:700;">شماره موبایل دریافت هشدارهای مدیریتی (پیامک سفارش جدید و فیش)</label>
                        <input class="form-control mono-num" type="text" name="admin_alert_mobile" dir="ltr" value="<?= e($adminAlertMobile ?? '') ?>" placeholder="0912...">
                        <p class="form-helper">پیامک‌های اطلاع‌رسانی سفارش جدید و ثبت فیش کارت‌به‌کارت به این شماره ارسال خواهد شد.</p>
                    </div>
                </div>

                <div class="settings-form-actions">
                    <button type="submit" class="btn-set-save">
                        <span>ذخیره اطلاعات کسب‌وکار</span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
