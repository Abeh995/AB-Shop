<?php
/**
 * AB-Socks SMS Pattern Edit — Smartphone Simulation & Live Test Dispatch
 * @var array $pattern
 * @var array $variables
 * @var bool $isNew
 * @var array $globalTokens
 */
?>
<div class="phone-mockup-wrap">
    <!-- 1. Real Smartphone Mockup Frame -->
    <div class="phone-mockup">
        <div class="phone-speaker-notch"></div>
        
        <div class="phone-screen">
            <div>
                <div class="phone-header-bar">
                    <span style="font-weight:700;">پیامک دریافتی</span>
                    <span><?= e(SITE_NAME) ?></span>
                    <span>12:00</span>
                </div>

                <div class="phone-bubble" id="mobilePreviewBubble">
                    <?= e($pattern['pattern_text'] ?: 'متن پیش‌فرض پیامک...') ?>
                </div>

                <div class="phone-bubble-footer">
                    <span>هم‌اکنون</span>
                    <span>✓✓</span>
                </div>
            </div>

            <!-- Character & GSM Part Counter -->
            <div class="char-counter-card">
                <span>تعداد کاراکتر: <strong id="smsCharCount" class="char-counter-pill">0</strong></span>
                <span id="smsPartCount" class="char-counter-pill">۱ پارت پیامک</span>
            </div>
        </div>
    </div>

    <!-- 2. Live Test Sending Console -->
    <?php if (!$isNew): ?>
    <div class="admin-card" style="margin-top:20px; border-top:3px solid var(--sms-primary); padding:18px;">
        <h4 style="margin:0 0 8px 0; font-size:1rem; font-weight:800; display:flex; align-items:center; gap:6px;">
            <span>🚀</span> کنسول تست ارسال زنده
        </h4>
        <p style="font-size:0.78rem; color:var(--sms-muted); margin-bottom:14px;">
            با وارد کردن شماره همراه، دریافت پیامک واقعی با این الگو را از طریق سرور فراز فوراً آزمایش کنید.
        </p>

        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="sub_action" value="test_send">
            <input type="hidden" name="pattern_code" value="<?= e($pattern['pattern_code']) ?>">

            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:0.8rem; font-weight:700; display:block; margin-bottom:2px;">شماره همراه گیرنده تست</label>
                <input class="form-control" type="text" name="test_phone" dir="ltr" inputmode="numeric" placeholder="09123456789" required style="font-size:0.85rem;">
            </div>

            <?php if (!empty($variables)): ?>
                <div style="margin-bottom:14px;">
                    <label style="font-size:0.78rem; font-weight:700; color:var(--sms-muted); display:block; margin-bottom:6px;">مقادیر تستی متغیرها:</label>
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <?php foreach ($variables as $v): ?>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="min-width:90px; font-size:0.78rem; direction:ltr; text-align:left;">
                                    <code>%<?= e($v['name']) ?>%</code>
                                </div>
                                <input type="hidden" name="test_var_name[]" value="<?= e($v['name']) ?>">
                                <input class="form-control sample-var-input" type="text" name="test_var_value[]" placeholder="<?= e($v['label'] ?: $v['name']) ?>" value="123456" style="font-size:0.82rem; padding:4px 8px;" required>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-outline btn-block" style="border-color:var(--sms-primary); color:var(--sms-primary); font-size:0.85rem; padding:8px;">
                ارسال پیامک تستی به این شماره
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>
