<?php
/**
 * Settings Partial: Legal Content & Public CMS Pages
 */
?>
<div class="settings-tab-pane" id="pane-legal" role="tabpanel">
    <div class="settings-card">
        <div class="settings-card-header">
            <div>
                <h3 class="settings-card-title">📄 محتوای صفحات عمومی و متنی (CMS)</h3>
                <p class="settings-card-subtitle">متن «درباره ما»، «قوانین و مقررات» و «حریم خصوصی». (فرمت ساده: خط خالی = پاراگراف، <code>## عنوان</code> = تیتر، <code>- مورد</code> = لیست).</p>
            </div>
            <!-- Sub-tab pills -->
            <div style="display:flex; gap:6px; background:var(--set-bg-subtle); padding:4px; border-radius:var(--set-radius-sm); border:1px solid var(--set-border);">
                <button type="button" class="btn btn-sm cms-subtab-btn" data-target="#subtab-about" style="padding:6px 14px; font-weight:700; border-radius:6px; border:none; background:var(--set-primary); color:#fff; cursor:pointer;">درباره ما</button>
                <button type="button" class="btn btn-sm cms-subtab-btn" data-target="#subtab-terms" style="padding:6px 14px; font-weight:700; border-radius:6px; border:none; background:transparent; color:var(--set-text-muted); cursor:pointer;">قوانین و مقررات</button>
                <button type="button" class="btn btn-sm cms-subtab-btn" data-target="#subtab-privacy" style="padding:6px 14px; font-weight:700; border-radius:6px; border:none; background:transparent; color:var(--set-text-muted); cursor:pointer;">حریم خصوصی</button>
            </div>
        </div>

        <form method="post" action="settings.php">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="legal_content">
            <input type="hidden" name="tab" value="legal">

            <div class="settings-card-body">

                <div id="subtab-about" class="cms-pane">
                    <label style="font-size:.85rem; font-weight:800; display:block; margin-bottom:8px;">متن صفحه «درباره ما»:</label>
                    <textarea class="form-control" name="about_content" rows="12" placeholder="داستان برند، کیفیت جوراب‌ها، متریال الیاف و اهداف مجموعه..."><?= e($aboutContent ?? '') ?></textarea>
                </div>

                <div id="subtab-terms" class="cms-pane" style="display:none;">
                    <label style="font-size:.85rem; font-weight:800; display:block; margin-bottom:8px;">متن صفحه «قوانین و مقررات فروشگاه»:</label>
                    <textarea class="form-control" name="terms_content" rows="12" placeholder="شرایط خرید، نحوه ارسال، رویه مرجوعی کالا و بازگشت وجه..."><?= e($termsContent ?? '') ?></textarea>
                </div>

                <div id="subtab-privacy" class="cms-pane" style="display:none;">
                    <label style="font-size:.85rem; font-weight:800; display:block; margin-bottom:8px;">متن صفحه «حریم خصوصی و امنیت داده‌ها»:</label>
                    <textarea class="form-control" name="privacy_content" rows="12" placeholder="حفاظت از شماره تماس و اطلاعات مشتریان..."><?= e($privacyContent ?? '') ?></textarea>
                </div>

                <div class="settings-form-actions">
                    <button type="submit" class="btn-set-save">
                        <span>ذخیره متون صفحات عمومی</span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
