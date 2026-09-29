<?php
require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="admin-page-settings" style="display:flex; flex-direction:column; gap:24px;">

    <!-- ============================================================== -->
    <!-- TIER 1: BENTO SETTINGS DIRECTORY (ایستگاه‌های کاری تخصصی)        -->
    <!-- ============================================================== -->
    <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
            <div>
                <h3 style="margin:0 0 4px; font-size:1.15rem; font-weight:800; color:var(--color-text);">مرکز فرماندهی تنظیمات فروشگاه</h3>
                <p style="margin:0; font-size:.85rem; color:var(--color-muted);">
                    برای پیکربندی ظاهر، روش‌های ارسال، پیامک‌ها و سرویس‌های تخصصی وارد ایستگاه کاری مربوطه شوید.
                </p>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="status-pill status-delivered" style="font-size:.78rem; font-weight:700;">
                    نسخه سیستم: v<?= APP_VERSION ?>
                </span>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">

            <!-- 1. Appearance & Landing -->
            <a href="appearance.php" class="admin-card" style="text-decoration:none; color:inherit; display:flex; flex-direction:column; justify-content:space-between; padding:18px 20px; border:1px solid var(--color-border); border-radius:14px; transition:transform .18s ease, box-shadow .18s ease; background:#FAF8F5;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:12px;">
                    <div style="width:42px; height:42px; border-radius:10px; background:rgba(180, 83, 9, 0.1); color:#B45309; display:flex; align-items:center; justify-content:center;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                    </div>
                    <span class="status-pill status-delivered" style="font-size:.72rem;">تم: <?= e($stats['activeThemeName']) ?></span>
                </div>
                <div>
                    <h4 style="margin:0 0 4px; font-size:1rem; font-weight:800;">ظاهر، لندینگ و تم‌ها</h4>
                    <p style="margin:0; font-size:.82rem; color:var(--color-muted); line-height:1.4;">بخش‌های صفحه اصلی، پالت رنگ، لوگو، نوار اعلان و فوتر</p>
                </div>
                <div style="margin-top:14px; font-size:.82rem; font-weight:700; color:var(--color-primary); display:flex; align-items:center; gap:4px;">
                    مدیریت ظاهر و لندینگ ←
                </div>
            </a>

            <!-- 2. Shipping Methods -->
            <a href="shipping_methods.php" class="admin-card" style="text-decoration:none; color:inherit; display:flex; flex-direction:column; justify-content:space-between; padding:18px 20px; border:1px solid var(--color-border); border-radius:14px; transition:transform .18s ease, box-shadow .18s ease; background:#fff;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:12px;">
                    <div style="width:42px; height:42px; border-radius:10px; background:rgba(3, 105, 161, 0.1); color:#0369A1; display:flex; align-items:center; justify-content:center;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-5l-3-4h-5v10"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>
                    </div>
                    <span class="status-pill status-delivered" style="font-size:.72rem;"><?= $stats['shippingActiveCount'] ?> روش فعال</span>
                </div>
                <div>
                    <h4 style="margin:0 0 4px; font-size:1rem; font-weight:800;">روش‌ها و هزینه ارسال</h4>
                    <p style="margin:0; font-size:.82rem; color:var(--color-muted); line-height:1.4;">قواعد پستی استان‌ها، هزینه مشتری، هزینه پیک و ارسال رایگان</p>
                </div>
                <div style="margin-top:14px; font-size:.82rem; font-weight:700; color:var(--color-primary); display:flex; align-items:center; gap:4px;">
                    تنظیم روش‌های ارسال ←
                </div>
            </a>

            <!-- 3. SMS Patterns -->
            <a href="sms_patterns.php" class="admin-card" style="text-decoration:none; color:inherit; display:flex; flex-direction:column; justify-content:space-between; padding:18px 20px; border:1px solid var(--color-border); border-radius:14px; transition:transform .18s ease, box-shadow .18s ease; background:#fff;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:12px;">
                    <div style="width:42px; height:42px; border-radius:10px; background:rgba(124, 58, 237, 0.1); color:#7C3AED; display:flex; align-items:center; justify-content:center;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <span class="status-pill status-delivered" style="font-size:.72rem;"><?= $stats['smsActiveCount'] ?> الگوی فعال</span>
                </div>
                <div>
                    <h4 style="margin:0 0 4px; font-size:1rem; font-weight:800;">الگوهای پیامک (فراز اس‌ام‌اس)</h4>
                    <p style="margin:0; font-size:.82rem; color:var(--color-muted); line-height:1.4;">کدهای پترن، متغیرهای پویا، رویدادهای سفارش و تست ارسال</p>
                </div>
                <div style="margin-top:14px; font-size:.82rem; font-weight:700; color:var(--color-primary); display:flex; align-items:center; gap:4px;">
                    مدیریت الگوهای پیامک ←
                </div>
            </a>

            <!-- 4. Email Accounts -->
            <a href="email_accounts.php" class="admin-card" style="text-decoration:none; color:inherit; display:flex; flex-direction:column; justify-content:space-between; padding:18px 20px; border:1px solid var(--color-border); border-radius:14px; transition:transform .18s ease, box-shadow .18s ease; background:#fff;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:12px;">
                    <div style="width:42px; height:42px; border-radius:10px; background:rgba(5, 150, 105, 0.1); color:#059669; display:flex; align-items:center; justify-content:center;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                    <span class="status-pill status-delivered" style="font-size:.72rem;"><?= $stats['emailActiveCount'] ?> حساب ایمیل</span>
                </div>
                <div>
                    <h4 style="margin:0 0 4px; font-size:1rem; font-weight:800;">حساب‌های ایمیل و وب‌میل</h4>
                    <p style="margin:0; font-size:.82rem; color:var(--color-muted); line-height:1.4;">پیکربندی IMAP/SMTP، صندوق دریافت و ارسال ایمیل سازمانی</p>
                </div>
                <div style="margin-top:14px; font-size:.82rem; font-weight:700; color:var(--color-primary); display:flex; align-items:center; gap:4px;">
                    مدیریت ایمیل‌ها ←
                </div>
            </a>

            <?php if (isSuperAdmin()): ?>
                <!-- 5. Admins & Access -->
                <a href="users.php" class="admin-card" style="text-decoration:none; color:inherit; display:flex; flex-direction:column; justify-content:space-between; padding:18px 20px; border:1px solid var(--color-border); border-radius:14px; transition:transform .18s ease, box-shadow .18s ease; background:#fff;">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:12px;">
                        <div style="width:42px; height:42px; border-radius:10px; background:rgba(79, 70, 229, 0.1); color:#4F46E5; display:flex; align-items:center; justify-content:center;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <span class="status-pill status-delivered" style="font-size:.72rem;"><?= $stats['adminActiveCount'] ?> ادمین</span>
                    </div>
                    <div>
                        <h4 style="margin:0 0 4px; font-size:1rem; font-weight:800;">مدیران سایت و دسترسی‌ها</h4>
                        <p style="margin:0; font-size:.82rem; color:var(--color-muted); line-height:1.4;">تعریف ادمین جدید، نقش‌ها، تغییر رمز عبور و فعال‌سازی</p>
                    </div>
                    <div style="margin-top:14px; font-size:.82rem; font-weight:700; color:var(--color-primary); display:flex; align-items:center; gap:4px;">
                        مدیریت مدیران ←
                    </div>
                </a>

                <!-- 6. Diagnostics & Logs -->
                <a href="diagnostics.php" class="admin-card" style="text-decoration:none; color:inherit; display:flex; flex-direction:column; justify-content:space-between; padding:18px 20px; border:1px solid var(--color-border); border-radius:14px; transition:transform .18s ease, box-shadow .18s ease; background:#fff;">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:12px;">
                        <div style="width:42px; height:42px; border-radius:10px; background:rgba(220, 38, 38, 0.1); color:#DC2626; display:flex; align-items:center; justify-content:center;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        </div>
                        <span class="status-pill status-delivered" style="font-size:.72rem;">پایش سلامت</span>
                    </div>
                    <div>
                        <h4 style="margin:0 0 4px; font-size:1rem; font-weight:800;">عیب‌یابی، اتصال و لاگ‌ها</h4>
                        <p style="margin:0; font-size:.82rem; color:var(--color-muted); line-height:1.4;">تست زنده اتصال پیامک و SMTP، مانده اعتبار و لاگ خطاها</p>
                    </div>
                    <div style="margin-top:14px; font-size:.82rem; font-weight:700; color:var(--color-primary); display:flex; align-items:center; gap:4px;">
                        بررسی وضعیت سیستم ←
                    </div>
                </a>
            <?php endif; ?>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TIER 2: GENERAL OPERATIONAL STORE SETTINGS                    -->
    <!-- ============================================================== -->
    <div>
        <div style="margin-bottom:14px;">
            <h3 style="margin:0 0 4px; font-size:1.15rem; font-weight:800; color:var(--color-text);">تنظیمات پایه و عمومی فروشگاه</h3>
            <p style="margin:0; font-size:.85rem; color:var(--color-muted);">
                اطلاعات کسب‌وکار، درگاه‌های پرداخت، جستجو، شبکه‌های اجتماعی و صفحات متنی
            </p>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:20px; align-items:start;">

            <!-- 1. Business Info & Contact Details -->
            <div class="admin-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <h4 style="margin:0; font-size:1rem; font-weight:800;">🏢 اطلاعات کسب‌وکار و تماس</h4>
                </div>
                <p style="color:var(--color-muted); font-size:.85rem; margin-bottom:16px;">
                    این اطلاعات در صفحات «درباره ما»، «تماس با ما» و فاکتورها نمایش داده می‌شوند.
                </p>
                <form method="post">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="business">
                    <div class="form-group">
                        <label style="font-size:.82rem;">ایمیل پشتیبانی</label>
                        <input class="form-control" type="email" name="store_email" dir="ltr" value="<?= e($storeEmail) ?>" placeholder="support@ab-socks.ir">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label style="font-size:.82rem;">تلفن ثابت</label>
                            <input class="form-control" type="text" name="store_phone" dir="ltr" value="<?= e($storePhone) ?>" placeholder="021-12345678">
                        </div>
                        <div class="form-group">
                            <label style="font-size:.82rem;">شماره همراه</label>
                            <input class="form-control" type="text" name="store_mobile" dir="ltr" value="<?= e($storeMobile) ?>" placeholder="09123456789">
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:.82rem;">نشانی فروشگاه / انبار</label>
                        <input class="form-control" type="text" name="store_address" value="<?= e($storeAddress) ?>" placeholder="نشانی کامل">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label style="font-size:.82rem;">کد پستی (۱۰ رقمی)</label>
                            <input class="form-control" type="text" name="store_postal_code" dir="ltr" value="<?= e($storePostalCode) ?>">
                        </div>
                        <div class="form-group">
                            <label style="font-size:.82rem;">تاریخ شروع فعالیت</label>
                            <input class="form-control" type="text" name="store_start_date" value="<?= e($storeStartDate) ?>" placeholder="مرداد ۱۴۰۵">
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:.82rem;">ساعات پاسخ‌گویی پشتیبانی</label>
                        <input class="form-control" type="text" name="store_support_hours" value="<?= e($storeSupportHours) ?>" placeholder="شنبه تا چهارشنبه، ۹ تا ۱۸">
                    </div>
                    <div class="form-group">
                        <label style="font-size:.82rem;">متن معرفی صفحه تماس با ما</label>
                        <textarea class="form-control" name="contact_intro" rows="2"><?= e($contactIntro) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">ذخیره اطلاعات تماس</button>
                </form>
            </div>

            <!-- 2. Payment Gateway & Card-to-Card -->
            <div class="admin-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <h4 style="margin:0; font-size:1rem; font-weight:800;">💳 درگاه پرداخت و کارت‌به‌کارت</h4>
                </div>
                <p style="color:var(--color-muted); font-size:.85rem; margin-bottom:16px;">
                    فعال‌سازی درگاه زرین‌پال و تنظیم شماره کارت بانکی برای تسویه حساب مشتریان.
                </p>
                <form method="post">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="payment">

                    <div style="padding:12px 14px; border:1px solid var(--color-border); border-radius:10px; background:#FAF8F5; margin-bottom:16px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:.9rem; margin:0;">
                            <input type="checkbox" name="payment_zarinpal_enabled" value="1" <?= $paymentZarinpalEnabled ? 'checked' : '' ?>>
                            <span>فعال‌سازی پرداخت اینترنتی آنلاین (درگاه زرین‌پال)</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label style="font-size:.82rem;">شماره کارت ۱۶ رقمی (کارت‌به‌کارت)</label>
                        <input class="form-control" type="text" name="card_to_card_number" dir="ltr" maxlength="16" value="<?= e($cardToCardNumber) ?>" placeholder="۶۰۳۷۹۹...">
                    </div>
                    <div class="form-group">
                        <label style="font-size:.82rem;">نام و نام خانوادگی صاحب کارت</label>
                        <input class="form-control" type="text" name="card_to_card_holder" value="<?= e($cardToCardHolder) ?>" placeholder="مثلاً: ابوالفضل بهزادی">
                    </div>
                    <div class="form-group">
                        <label style="font-size:.82rem;">راهنما و توضیحات کارت‌به‌کارت برای مشتری</label>
                        <textarea class="form-control" name="card_to_card_note" rows="2" placeholder="لطفاً پس از واریز، تصویر فیش را آپلود نمایید..."><?= e($cardToCardNote) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">ذخیره تنظیمات پرداخت</button>
                </form>
            </div>

            <!-- 3. Storefront Search, Tags & Price Guarantee -->
            <div class="admin-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <h4 style="margin:0; font-size:1rem; font-weight:800;">🔍 جستجوی زنده، ضمانت و برچسب‌ها</h4>
                </div>
                <p style="color:var(--color-muted); font-size:.85rem; margin-bottom:16px;">
                    پیکربندی قابلیت جستجوی زنده در هدر، ضمانت قیمت و برچسب‌های کاتالوگ.
                </p>
                <form method="post">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="search">

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:.88rem; margin-bottom:12px;">
                        <input type="checkbox" name="search_live_enabled" value="1" <?= $searchLiveEnabled ? 'checked' : '' ?>>
                        <span>جستجوی زنده هدر با پیشنهاد آنی (Autocomplete)</span>
                    </label>

                    <div class="form-row" style="margin-bottom:12px;">
                        <div class="form-group">
                            <label style="font-size:.8rem;">سقف نتایج پیشنهادی</label>
                            <input class="form-control" type="number" name="search_suggest_limit" min="2" max="20" value="<?= $searchSuggestLimit ?>">
                        </div>
                        <div class="form-group">
                            <label style="font-size:.8rem;">حداقل حروف برای شروع جستجو</label>
                            <input class="form-control" type="number" name="search_min_chars" min="1" max="5" value="<?= $searchMinChars ?>">
                        </div>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:16px;">
                        <span style="font-size:.8rem; color:var(--color-muted); font-weight:700;">دامنه‌های تحت پوشش جستجو:</span>
                        <label style="display:flex; align-items:center; gap:8px; font-size:.82rem; cursor:pointer;">
                            <input type="checkbox" name="search_scope_name" value="1" <?= $searchScopeName ? 'checked' : '' ?>>
                            <span>عنوان و نام محصول</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:.82rem; cursor:pointer;">
                            <input type="checkbox" name="search_scope_description" value="1" <?= $searchScopeDescription ? 'checked' : '' ?>>
                            <span>توضیحات و ویژگی‌های محصول</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:.82rem; cursor:pointer;">
                            <input type="checkbox" name="search_include_categories" value="1" <?= $searchIncludeCategories ? 'checked' : '' ?>>
                            <span>پیشنهاد دسته‌بندی‌های مرتبط در نتایج</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">ذخیره تنظیمات جستجو</button>
                </form>

                <hr style="margin:16px 0; border:0; border-top:1px solid var(--color-border);">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <form method="post">
                        <?= csrfField() ?>
                        <input type="hidden" name="section" value="tags">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:.82rem; margin-bottom:8px;">
                            <input type="checkbox" name="show_product_tags" value="1" <?= $showProductTags ? 'checked' : '' ?>>
                            <span>نمایش برچسب‌ها در محصول</span>
                        </label>
                        <button type="submit" class="btn btn-outline btn-sm" style="width:100%;">ذخیره برچسب</button>
                    </form>

                    <form method="post">
                        <?= csrfField() ?>
                        <input type="hidden" name="section" value="price_guarantee">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:.82rem; margin-bottom:8px;">
                            <input type="checkbox" name="price_guarantee_enabled" value="1" <?= $priceGuaranteeEnabled ? 'checked' : '' ?>>
                            <span>ضمانت قیمت (<?= $priceGuaranteeDays ?> روز)</span>
                        </label>
                        <button type="submit" class="btn btn-outline btn-sm" style="width:100%;">ذخیره ضمانت</button>
                    </form>
                </div>
            </div>

            <!-- 4. Social Media, Enamad & Trust Badges -->
            <div class="admin-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <h4 style="margin:0; font-size:1rem; font-weight:800;">🌐 شبکه‌های اجتماعی و اینماد</h4>
                </div>
                <p style="color:var(--color-muted); font-size:.85rem; margin-bottom:16px;">
                    لینک صفحات اجتماعی فروشگاه در فوتر و کد رهگیری نماد اعتماد الکترونیکی.
                </p>
                <form method="post">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="social">

                    <?php foreach ($socialNetworks as $sKey => $sLabel): ?>
                        <div style="margin-bottom:12px;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:.82rem; margin-bottom:4px; font-weight:700;">
                                <input type="checkbox" name="social_<?= $sKey ?>_enabled" value="1" <?= !empty($socialSettings[$sKey]['enabled']) ? 'checked' : '' ?>>
                                <span><?= e($sLabel) ?></span>
                            </label>
                            <input class="form-control" type="text" name="social_<?= $sKey ?>_url" dir="ltr" value="<?= e($socialSettings[$sKey]['url'] ?? '') ?>" placeholder="https://...">
                        </div>
                    <?php endforeach; ?>

                    <hr style="margin:16px 0; border:0; border-top:1px solid var(--color-border);">

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:.85rem; font-weight:700; margin-bottom:8px;">
                        <input type="checkbox" name="enamad_enabled" value="1" <?= $enamadEnabled ? 'checked' : '' ?>>
                        <span>نمایش نماد اعتماد الکترونیکی (اینماد) در فوتر</span>
                    </label>
                    <div class="form-group">
                        <label style="font-size:.8rem;">کد اسکریپت / HTML اینماد</label>
                        <textarea class="form-control" name="enamad_embed_code" rows="3" dir="ltr" placeholder="<a referrerpolicy=..."><?= e($enamadEmbedCode) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">ذخیره شبکه‌ها و نمادها</button>
                </form>
            </div>

            <!-- 5. SEO & Search Engine Indexing (Ready for Future SEO Module) -->
            <div class="admin-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <h4 style="margin:0; font-size:1rem; font-weight:800;">🔎 سئو و موتورهای جستجو</h4>
                    <span class="status-pill <?= $seoIndexingEnabled ? 'status-delivered' : 'status-cancelled' ?>" style="font-size:.72rem;">
                        <?= $seoIndexingEnabled ? 'ایندکس فعال' : 'ایندکس مسدود' ?>
                    </span>
                </div>
                <p style="color:var(--color-muted); font-size:.85rem; margin-bottom:16px;">
                    کنترل تگ‌های روبات برای ثبت در گوگل و پایش وضعیت دسترسی به سایت.
                </p>
                <form method="post">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="seo">

                    <div style="padding:14px; border:1px solid var(--color-border); border-radius:10px; background:#FAF8F5; margin-bottom:14px;">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:700; font-size:.88rem; margin-bottom:6px;">
                            <input type="checkbox" name="seo_indexing_enabled" value="1" <?= $seoIndexingEnabled ? 'checked' : '' ?>>
                            <span>اجازه ایندکس به موتورهای جستجو (Google, Bing)</span>
                        </label>
                        <p style="margin:0; font-size:.78rem; color:var(--color-muted); line-height:1.4;">
                            در صورت خاموش بودن، تگ <code>noindex, nofollow</code> در هدر تمام صفحات عمومی درج شده و ربات‌ها سایت را ثبت نخواهند کرد.
                        </p>
                    </div>

                    <div style="padding:10px 14px; border-radius:8px; background:rgba(3,105,161,0.06); border:1px dashed #0369A1; font-size:.8rem; color:#0369A1; margin-bottom:14px; line-height:1.4;">
                        🚀 <strong>ماژول سئو در آینده:</strong> بخش نقشه سایت پویا (sitemap.xml)، داده‌های ساختاریافته Schema.org و متاتگ‌های OpenGraph به زودی به این ایستگاه کاری افزوده می‌شوند.
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">ذخیره وضعیت سئو</button>
                </form>
            </div>

            <!-- 6. Legal & Static CMS Pages (Tabbed View) -->
            <div class="admin-card" style="grid-column: 1 / -1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
                    <div>
                        <h4 style="margin:0 0 4px; font-size:1rem; font-weight:800;">📄 محتوای صفحات عمومی و متنی (CMS)</h4>
                        <p style="margin:0; font-size:.85rem; color:var(--color-muted);">
                            متن «درباره ما»، «قوانین و مقررات» و «حریم خصوصی و امنیت». (قالب ساده: خط خالی = پاراگراف جدید، <code>## عنوان</code> = تیتر، <code>- مورد</code> = آیتم فهرست).
                        </p>
                    </div>
                    <!-- Tab Buttons -->
                    <div style="display:flex; gap:6px; background:#FAF8F5; padding:4px; border-radius:10px; border:1px solid var(--color-border);">
                        <button type="button" class="btn btn-sm legal-tab-btn" data-target="#tabAbout" style="padding:6px 14px; font-weight:700; border-radius:8px; border:none; background:var(--color-primary); color:#fff; cursor:pointer;">درباره ما</button>
                        <button type="button" class="btn btn-sm legal-tab-btn" data-target="#tabTerms" style="padding:6px 14px; font-weight:700; border-radius:8px; border:none; background:transparent; color:var(--color-muted); cursor:pointer;">قوانین و مقررات</button>
                        <button type="button" class="btn btn-sm legal-tab-btn" data-target="#tabPrivacy" style="padding:6px 14px; font-weight:700; border-radius:8px; border:none; background:transparent; color:var(--color-muted); cursor:pointer;">حریم خصوصی</button>
                    </div>
                </div>

                <form method="post">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="legal_content">

                    <div id="tabAbout" class="legal-tab-pane">
                        <label style="font-size:.82rem; font-weight:700; display:block; margin-bottom:6px;">متن صفحه «درباره ما»:</label>
                        <textarea class="form-control" name="about_content" rows="10" placeholder="داستان برند و کیفیت جوراب‌ها..."><?= e($aboutContent) ?></textarea>
                    </div>

                    <div id="tabTerms" class="legal-tab-pane" style="display:none;">
                        <label style="font-size:.82rem; font-weight:700; display:block; margin-bottom:6px;">متن صفحه «قوانین و مقررات فروشگاه»:</label>
                        <textarea class="form-control" name="terms_content" rows="10" placeholder="شرایط خرید، ارسال، بازگشت کالا..."><?= e($termsContent) ?></textarea>
                    </div>

                    <div id="tabPrivacy" class="legal-tab-pane" style="display:none;">
                        <label style="font-size:.82rem; font-weight:700; display:block; margin-bottom:6px;">متن صفحه «حریم خصوصی و حفاظت از داده‌ها»:</label>
                        <textarea class="form-control" name="privacy_content" rows="10" placeholder="اطلاعات مشتریان، ذخیره‌سازی و امنیت..."><?= e($privacyContent) ?></textarea>
                    </div>

                    <div style="margin-top:16px;">
                        <button type="submit" class="btn btn-primary" style="padding:10px 24px; font-weight:700;">ذخیره متون صفحات عمومی</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Legal CMS Tab Switcher
    const tabBtns = document.querySelectorAll('.legal-tab-btn');
    const panes = document.querySelectorAll('.legal-tab-pane');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            tabBtns.forEach(b => {
                b.style.background = 'transparent';
                b.style.color = 'var(--color-muted)';
            });
            panes.forEach(p => p.style.display = 'none');

            this.style.background = 'var(--color-primary)';
            this.style.color = '#fff';

            const targetPane = document.querySelector(this.getAttribute('data-target'));
            if (targetPane) targetPane.style.display = 'block';
        });
    });
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
