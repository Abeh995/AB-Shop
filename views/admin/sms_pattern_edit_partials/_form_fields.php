<?php
/**
 * AB-Socks SMS Pattern Edit — Base Form Fields
 * @var array $pattern
 * @var array $availableEvents
 */
?>
<div class="form-row" style="display:grid; grid-template-columns:1fr 1.6fr; gap:14px; margin-bottom:14px;">
    <div class="form-group" style="margin:0;">
        <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">کد پترن فراز اس‌ام‌اس <span style="color:var(--sms-danger);">*</span></label>
        <input class="form-control" type="text" name="pattern_code" dir="ltr" value="<?= e($pattern['pattern_code']) ?>" placeholder="مثلاً: s3w2n7f1k6" required>
        <small style="color:var(--sms-muted); font-size:0.75rem;">کد تایید شده در پنل Faraz SMS</small>
    </div>
    <div class="form-group" style="margin:0;">
        <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">عنوان الگو <span style="color:var(--sms-danger);">*</span></label>
        <input class="form-control" type="text" name="title" value="<?= e($pattern['title']) ?>" placeholder="مثلاً: ثبت سفارش جدید برای خریدار" required>
    </div>
</div>

<div class="form-row" style="display:grid; grid-template-columns:1.6fr 1fr; gap:14px; margin-bottom:14px;">
    <div class="form-group" style="margin:0;">
        <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">انتساب به رویداد سیستمی</label>
        <select class="form-control" name="event_key" id="eventKeySelect">
            <?php foreach ($availableEvents as $eKey => $eLabel): ?>
                <option value="<?= e($eKey) ?>" <?= ($pattern['event_key'] ?? '') === $eKey ? 'selected' : '' ?>>
                    <?= e($eLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group" style="margin:0; display:flex; align-items:center; padding-top:20px;">
        <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:0.88rem;">
            <input type="checkbox" name="is_active" value="1" <?= $pattern['is_active'] ? 'checked' : '' ?>>
            الگو فعال باشد و در سیستم ارسال شود
        </label>
    </div>
</div>

<div class="form-group" style="margin-bottom:14px;">
    <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">متن الگو در فراز اس‌ام‌اس (جهت راهنمایی، شبیه‌سازی و بایگانی)</label>
    <textarea class="form-control" name="pattern_text" rows="3" placeholder="سفارش %order_code% برای %name% ثبت شد.&#10;فروشگاه ای‌بی ساکس"><?= e($pattern['pattern_text']) ?></textarea>
    <p style="font-size:0.75rem; color:var(--sms-muted); margin-top:4px;">
        متغیرها را به‌صورت <code>%variable_name%</code> در متن الگو قرار دهید تا در شبیه‌ساز موبایل به‌صورت زنده جایگزین شوند.
    </p>
</div>

<div class="form-group" style="margin-bottom:18px;">
    <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">توضیحات داخلی (اختیاری)</label>
    <input class="form-control" type="text" name="description" value="<?= e($pattern['description']) ?>" placeholder="توضیح کوتاه درباره نحوه و زمان ارسال این الگو">
</div>
