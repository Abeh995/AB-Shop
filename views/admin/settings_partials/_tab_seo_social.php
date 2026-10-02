<?php
/**
 * Settings Partial: SEO, Social Networks & Trust Badges
 */
?>
<div class="settings-tab-pane" id="pane-seo_social" role="tabpanel">
    <div class="settings-card">
        <div class="settings-card-header">
            <div>
                <h3 class="settings-card-title">🌐 سئو، موتورهای جستجو، نمادها و شبکه‌های اجتماعی</h3>
                <p class="settings-card-subtitle">کنترل ایندکسینگ گوگل، پیوندهای شبکه‌های اجتماعی در فوتر و نماد اعتماد الکترونیکی</p>
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
                            <p class="toggle-desc">در صورت خاموش بودن، تگ <code>noindex, nofollow</code> در تمام صفحات قرار گرفته و سایت از نتایج جستجو مخفی می‌ماند (مناسب زمان توسعه یا قبل از راه‌اندازی رسمی).</p>
                        </div>
                        <div class="toggle-switch">
                            <input type="checkbox" name="seo_indexing_enabled" value="1" <?= ($seoIndexingEnabled ?? false) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </div>
                    </label>
                </div>

                <!-- 2. Social Media Links -->
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

                <!-- 3. Enamad & Trust Badges -->
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
