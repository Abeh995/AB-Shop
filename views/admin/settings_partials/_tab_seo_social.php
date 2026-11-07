<?php
/**
 * Settings Partial: SEO, Generative Engine Optimization (GEO/AI), Webmaster & Social Networks
 */
?>
<div class="settings-tab-pane" id="pane-seo_social" role="tabpanel">
    <div class="settings-card">
        <div class="settings-card-header">
            <div>
                <h3 class="settings-card-title">🌐 سئو، موتورهای جستجو، هوش مصنوعی و شبکه‌های اجتماعی</h3>
                <p class="settings-card-subtitle">کنترل ایندکسینگ، احراز هویت وبمستر، پروتکل‌های هوش مصنوعی (llms.txt)، ریدایرکت‌های ۳۰۱ و نمادهای اعتماد</p>
            </div>
        </div>

        <form method="post" action="settings.php">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="social">
            <input type="hidden" name="tab" value="seo_social">

            <div class="settings-card-body">

                <!-- 1. Search Engine Indexing (SEO) -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>🔎 ایندکسینگ در گوگل و موتورهای جستجو (SEO)</span>
                    </div>

                    <label class="settings-toggle-wrap">
                        <div class="toggle-info">
                            <span class="toggle-label">مجوز ثبت و ایندکس صفحات در گوگل و بینگ</span>
                            <p class="toggle-desc">در صورت خاموش بودن، تگ <code>noindex, nofollow</code> در تمام صفحات قرار گرفته و خزنده‌های جستجو از سایت منع می‌شوند.</p>
                        </div>
                        <div class="toggle-switch">
                            <input type="checkbox" name="seo_indexing_enabled" value="1" <?= ($seoIndexingEnabled ?? false) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </div>
                    </label>

                    <div class="settings-grid-2">
                        <div class="form-group">
                            <label class="cat-label">کد تایید Google Search Console (محتوای content تگ meta):</label>
                            <input class="form-control mono-num" type="text" name="google_site_verification" dir="ltr" value="<?= e($googleSiteVerification ?? '') ?>" placeholder="مثلاً: AbCdEf1234567890...">
                        </div>
                        <div class="form-group">
                            <label class="cat-label">کد تایید Bing Webmaster Tools (کد msvalidate.01):</label>
                            <input class="form-control mono-num" type="text" name="bing_site_verification" dir="ltr" value="<?= e($bingSiteVerification ?? '') ?>" placeholder="مثلاً: 9A8B7C6D5E4F3E2D...">
                        </div>
                    </div>

                    <div class="seo-diag-bar">
                        <span class="seo-diag-title">ابزارهای بررسی وضعیت سلامت و خزش:</span>
                        <div class="seo-diag-links">
                            <a href="/robots.txt" target="_blank" class="seo-diag-btn">مشاهده robots.txt ↗</a>
                            <a href="/sitemap.xml" target="_blank" class="seo-diag-btn">مشاهده sitemap.xml ↗</a>
                            <a href="/llms.txt" target="_blank" class="seo-diag-btn">مشاهده خوراک AI (llms.txt) ↗</a>
                        </div>
                    </div>
                </div>

                <!-- 2. Schema Rich Policies (Return & Shipping for Google Merchant) -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>📦 داده‌های ساختاریافته فروش و ارسال (Merchant Schema Policies)</span>
                    </div>
                    <div class="settings-grid-2">
                        <div class="form-group">
                            <label class="cat-label">مهلت بازگشت و مرجوعی کالا (روز):</label>
                            <input class="form-control mono-num" type="number" name="return_policy_days" min="1" max="90" value="<?= (int)($returnPolicyDays ?? 7) ?>">
                        </div>
                        <div class="form-group">
                            <label class="cat-label">زمان آماده‌سازی و تحویل به پست (روز):</label>
                            <input class="form-control mono-num" type="number" name="shipping_lead_time_days" min="1" max="14" value="<?= (int)($shippingLeadTimeDays ?? 2) ?>">
                        </div>
                    </div>
                </div>

                <!-- 3. URL 301 Redirects -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>🔀 مدیریت ریدایرکت‌های ۳۰۱ (جلوگیری از خطای ۴۰۴ و حفظ رتبه)</span>
                    </div>
                    <p class="form-helper">در صورت تغییر اسلاگ محصول یا دسته، ریدایرکت خودکار ثبت می‌شود. همچنین می‌توانید آدرس‌های قدیمی را به آدرس‌های جدید هدایت کنید.</p>

                    <div class="seo-redirect-add-card">
                        <span class="seo-redirect-add-title">افزودن مسیر ریدایرکت دستی جدید:</span>
                        <div class="seo-redirect-grid">
                            <div>
                                <label class="seo-redirect-label">آدرس مبدا (قدیمی):</label>
                                <input class="form-control" type="text" name="new_redirect_source" dir="ltr" placeholder="/product/old-slug">
                            </div>
                            <div>
                                <label class="seo-redirect-label">آدرس مقصد (جدید):</label>
                                <input class="form-control" type="text" name="new_redirect_target" dir="ltr" placeholder="/product/new-slug">
                            </div>
                            <div>
                                <label class="seo-redirect-label">کد وضعیت:</label>
                                <select class="form-control" name="new_redirect_status">
                                    <option value="301">301 (دائمی)</option>
                                    <option value="302">302 (موقت)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($seoRedirects)): ?>
                    <div class="seo-redirect-table-wrap">
                        <table class="seo-redirect-table">
                            <thead>
                                <tr>
                                    <th>مبدا</th>
                                    <th>مقصد</th>
                                    <th class="center-cell">نوع</th>
                                    <th class="center-cell">بازدیدها</th>
                                    <th class="center-cell">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($seoRedirects as $r): ?>
                                <tr>
                                    <td class="url-cell"><?= e($r['source_url']) ?></td>
                                    <td class="url-cell"><?= e($r['target_url']) ?></td>
                                    <td class="center-cell"><?= e($r['status_code']) ?></td>
                                    <td class="center-cell"><?= toPersianDigits((string)$r['hits']) ?></td>
                                    <td class="center-cell">
                                        <button type="submit" name="delete_redirect_id" value="<?= (int)$r['id'] ?>" class="seo-btn-del" onclick="return confirm('این ریدایرکت حذف شود؟')">حذف</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 4. Social Media Links -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>📱 شبکه‌های اجتماعی و کانال‌های رسمی</span>
                    </div>
                    <p class="form-helper" style="margin-top:-6px; margin-bottom:16px;">آیکون شبکه‌های فعال به همراه لینک مستقیم در فوتر سایت نمایش داده خواهند شد.</p>

                    <div class="settings-grid-2">
                        <?php foreach (($socialNetworks ?? []) as $sKey => $sLabel): ?>
                            <div style="background:#FFFFFF; border:1px solid var(--set-border); border-radius:var(--set-radius-sm); padding:14px;">
                                <label style="display:flex; align-items:center; justify-content:space-between; cursor:pointer; margin-bottom:10px;">
                                    <span style="font-weight:700; font-size:.88rem;"><?= e($sLabel) ?></span>
                                    <input type="checkbox" name="social_<?= $sKey ?>_enabled" value="1" <?= !empty($socialSettings[$sKey]['enabled']) ? 'checked' : '' ?>>
                                </label>
                                <input class="form-control" type="text" name="social_<?= $sKey ?>_url" dir="ltr" value="<?= e($socialSettings[$sKey]['url'] ?? '') ?>" placeholder="https://...">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 5. Enamad & Trust Badges -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>🛡️ نماد اعتماد الکترونیکی (اینماد)</span>
                    </div>

                    <label class="settings-toggle-wrap" style="margin-bottom:14px;">
                        <div class="toggle-info">
                            <span class="toggle-label">نمایش اینماد در فوتر سایت</span>
                            <p class="toggle-desc">با فعال کردن این گزینه و درج کد اسکریپت دریافتی از سامانه اینماد، لوگوی نماد در پاورقی سایت درج می‌شود.</p>
                        </div>
                        <div class="toggle-switch">
                            <input type="checkbox" name="enamad_enabled" value="1" <?= ($enamadEnabled ?? false) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </div>
                    </label>

                    <div class="form-group">
                        <label style="font-size:.82rem; font-weight:700;">کد HTML / اسکریپت نماد اعتماد:</label>
                        <textarea class="form-control mono-num" name="enamad_embed_code" rows="3" dir="ltr" placeholder="<a referrerpolicy=..."><?= e($enamadEmbedCode ?? '') ?></textarea>
                    </div>
                </div>

                <div class="settings-form-actions">
                    <button type="submit" class="btn-set-save">
                        <span>ذخیره تنظیمات سئو و شبکه‌ها</span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
