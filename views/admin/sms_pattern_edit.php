<?php
require APP_ROOT . '/views/admin/layout/header.php';
?>

<div style="margin-bottom:16px;">
    <a href="sms_patterns.php" style="color:var(--color-muted); text-decoration:none; font-size:.9rem;">
        ← بازگشت به لیست الگوهای پیامک
    </a>
</div>

<div style="display:grid; grid-template-columns: 1fr; gap:20px; max-width:980px;">
    <div class="admin-card">
        <h3 style="margin-bottom:14px;"><?= e($pageTitle) ?></h3>
        <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:20px;">
            کد الگو را دقیقاً مطابق با کدی که در پنل فراز اس‌ام‌اس تایید شده وارد نمایید. متغیرها را بر اساس آنچه در متن الگو تعریف کرده‌اید تنظیم کنید و هر متغیر را به داده سیستمی متناظر متصل (Bind) نمایید.
        </p>

        <form method="post" id="smsPatternForm">
            <?= csrfField() ?>
            <input type="hidden" name="sub_action" value="save">

            <div class="form-row">
                <div class="form-group" style="flex:1;">
                    <label>کد پترن فراز اس‌ام‌اس (Pattern Code) <span style="color:var(--color-danger);">*</span></label>
                    <input class="form-control" type="text" name="pattern_code" dir="ltr" value="<?= e($pattern['pattern_code']) ?>" placeholder="مثلاً: s3w2n7f1k6" required>
                </div>
                <div class="form-group" style="flex:2;">
                    <label>عنوان الگو <span style="color:var(--color-danger);">*</span></label>
                    <input class="form-control" type="text" name="title" value="<?= e($pattern['title']) ?>" placeholder="مثلاً: کد تایید ورود و ثبت‌نام" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex:1;">
                    <label>انتساب به رویداد سیستمی</label>
                    <select class="form-control" name="event_key" id="eventKeySelect">
                        <?php foreach ($availableEvents as $eKey => $eLabel): ?>
                            <option value="<?= e($eKey) ?>" <?= ($pattern['event_key'] ?? '') === $eKey ? 'selected' : '' ?>>
                                <?= e($eLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="flex:1; display:flex; align-items:flex-end; padding-bottom:12px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" <?= $pattern['is_active'] ? 'checked' : '' ?>>
                        <strong>الگو فعال باشد</strong>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>متن الگو در فراز اس‌ام‌اس (جهت راهنمایی و بایگانی)</label>
                <textarea class="form-control" name="pattern_text" rows="3" placeholder="مثلاً:&#10;سفارش %order_code% برای %name% ثبت شد.&#10;فروشگاه ای‌بی ساکس"><?= e($pattern['pattern_text']) ?></textarea>
                <p style="font-size:.78rem; color:var(--color-muted); margin-top:4px;">
                    متغیرها معمولاً به شکل <code>%variable_name%</code> در متن الگو نوشته می‌شوند.
                </p>
            </div>

            <div class="form-group">
                <label>توضیحات داخلی (اختیاری)</label>
                <input class="form-control" type="text" name="description" value="<?= e($pattern['description']) ?>" placeholder="توضیح کوتاه درباره نحوه استفاده این پترن">
            </div>

            <hr style="border:none; border-top:1px solid var(--color-border); margin:24px 0 18px;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h4 style="margin:0; font-size:1.05rem;">متغیرهای الگو و نگاشت داده‌های سیستمی (Dynamic Variables & Data-Binding)</h4>
                <button type="button" class="btn btn-outline btn-sm" id="addVarBtn">➕ افزودن متغیر</button>
            </div>
            <p style="font-size:.85rem; color:var(--color-muted); margin-bottom:16px;">
                نام متغیر را دقیقاً طبق تاییدیه پنل فراز وارد کنید. در ستون <strong>داده متصل سیستمی</strong> مشخص کنید هنگام وقوع رویداد، کدام اطلاعات سیستم در این متغیر قرار گیرد.
            </p>

            <div id="variablesContainer" style="display:flex; flex-direction:column; gap:12px; margin-bottom:20px;">
                <!-- Variable rows generated via PHP / JS -->
                <?php 
                $selectedEvent = $pattern['event_key'] ?? '';
                $currEventTokens = $eventTokens[$selectedEvent] ?? [];
                if (empty($variables)): 
                ?>
                    <!-- Default 1 variable row -->
                    <div class="var-row" style="display:flex; gap:10px; align-items:center; background:#FAF8F5; padding:12px; border-radius:8px; border:1px solid var(--color-border); flex-wrap:wrap;">
                        <div style="flex:1; min-width:130px;">
                            <label style="font-size:.78rem; display:block; margin-bottom:2px;">نام متغیر (فراز)</label>
                            <input class="form-control" type="text" name="var_name[]" dir="ltr" value="code" placeholder="code" required>
                        </div>
                        <div style="flex:1.5; min-width:160px;">
                            <label style="font-size:.78rem; display:block; margin-bottom:2px;">داده متصل سیستمی (Token)</label>
                            <select class="form-control var-token-select" name="var_token[]">
                                <option value="">-- بدون انتساب (دستی) --</option>
                                <?php if (!empty($currEventTokens)): ?>
                                    <optgroup label="متغیرهای رویداد انتخابی" class="optgroup-event">
                                        <?php foreach ($currEventTokens as $tKey => $tLabel): ?>
                                            <option value="<?= e($tKey) ?>" <?= $tKey === 'code' ? 'selected' : '' ?>>
                                                <?= e($tLabel) ?> (<?= e($tKey) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                                <optgroup label="سایر متغیرهای عمومی" class="optgroup-global">
                                    <?php foreach ($globalTokens as $tKey => $tLabel): ?>
                                        <?php if (!isset($currEventTokens[$tKey])): ?>
                                            <option value="<?= e($tKey) ?>">
                                                <?= e($tLabel) ?> (<?= e($tKey) ?>)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <div style="flex:1; min-width:105px;">
                            <label style="font-size:.78rem; display:block; margin-bottom:2px;">نوع متغیر</label>
                            <select class="form-control" name="var_type[]">
                                <option value="numeric">عددی (Numeric)</option>
                                <option value="string">متنی (String)</option>
                                <option value="alphanumeric">حروف و عدد</option>
                            </select>
                        </div>
                        <div style="width:75px;">
                            <label style="font-size:.78rem; display:block; margin-bottom:2px;">حداکثر طول</label>
                            <input class="form-control" type="number" name="var_max_len[]" value="6" min="1" max="200">
                        </div>
                        <div style="flex:1.2; min-width:130px;">
                            <label style="font-size:.78rem; display:block; margin-bottom:2px;">عنوان فارسی</label>
                            <input class="form-control" type="text" name="var_label[]" value="کد تایید" placeholder="کد تایید">
                        </div>
                        <div style="padding-top:16px;">
                            <button type="button" class="btn btn-outline btn-sm remove-var-btn" style="color:var(--color-danger); border-color:var(--color-danger);" title="حذف متغیر">✕</button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($variables as $v): 
                        $curToken = $v['source_token'] ?? '';
                    ?>
                        <div class="var-row" style="display:flex; gap:10px; align-items:center; background:#FAF8F5; padding:12px; border-radius:8px; border:1px solid var(--color-border); flex-wrap:wrap;">
                            <div style="flex:1; min-width:130px;">
                                <label style="font-size:.78rem; display:block; margin-bottom:2px;">نام متغیر (فراز)</label>
                                <input class="form-control" type="text" name="var_name[]" dir="ltr" value="<?= e($v['name'] ?? '') ?>" placeholder="code" required>
                            </div>
                            <div style="flex:1.5; min-width:160px;">
                                <label style="font-size:.78rem; display:block; margin-bottom:2px;">داده متصل سیستمی (Token)</label>
                                <select class="form-control var-token-select" name="var_token[]">
                                    <option value="">-- بدون انتساب (دستی) --</option>
                                    <?php if (!empty($currEventTokens)): ?>
                                        <optgroup label="متغیرهای رویداد انتخابی" class="optgroup-event">
                                            <?php foreach ($currEventTokens as $tKey => $tLabel): ?>
                                                <option value="<?= e($tKey) ?>" <?= $curToken === $tKey ? 'selected' : '' ?>>
                                                    <?= e($tLabel) ?> (<?= e($tKey) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                    <optgroup label="سایر متغیرهای عمومی" class="optgroup-global">
                                        <?php foreach ($globalTokens as $tKey => $tLabel): ?>
                                            <?php if (!isset($currEventTokens[$tKey])): ?>
                                                <option value="<?= e($tKey) ?>" <?= $curToken === $tKey ? 'selected' : '' ?>>
                                                    <?= e($tLabel) ?> (<?= e($tKey) ?>)
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                            </div>
                            <div style="flex:1; min-width:105px;">
                                <label style="font-size:.78rem; display:block; margin-bottom:2px;">نوع متغیر</label>
                                <select class="form-control" name="var_type[]">
                                    <option value="numeric" <?= ($v['type'] ?? '') === 'numeric' ? 'selected' : '' ?>>عددی (Numeric)</option>
                                    <option value="string" <?= ($v['type'] ?? '') === 'string' ? 'selected' : '' ?>>متنی (String)</option>
                                    <option value="alphanumeric" <?= ($v['type'] ?? '') === 'alphanumeric' ? 'selected' : '' ?>>حروف و عدد</option>
                                </select>
                            </div>
                            <div style="width:75px;">
                                <label style="font-size:.78rem; display:block; margin-bottom:2px;">حداکثر طول</label>
                                <input class="form-control" type="number" name="var_max_len[]" value="<?= (int) ($v['max_len'] ?? 30) ?>" min="1" max="200">
                            </div>
                            <div style="flex:1.2; min-width:130px;">
                                <label style="font-size:.78rem; display:block; margin-bottom:2px;">عنوان فارسی</label>
                                <input class="form-control" type="text" name="var_label[]" value="<?= e($v['label'] ?? '') ?>" placeholder="عنوان متغیر">
                            </div>
                            <div style="padding-top:16px;">
                                <button type="button" class="btn btn-outline btn-sm remove-var-btn" style="color:var(--color-danger); border-color:var(--color-danger);" title="حذف متغیر">✕</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary" style="padding:10px 24px;">
                <?= $isNew ? 'ثبت و ذخیره الگو' : 'ذخیره تغییرات الگو' ?>
            </button>
        </form>
    </div>

    <!-- Live Test Sending Card -->
    <?php if (!$isNew): ?>
    <div class="admin-card" style="border-top:3px solid var(--color-primary);">
        <h4 style="margin-bottom:12px;">📱 تست زنده ارسال پیامک با این الگو</h4>
        <p style="color:var(--color-muted); font-size:.85rem; margin-bottom:16px;">
            می‌توانید با وارد کردن شماره همراه خود، دریافت پیامک از سرور فراز را فوراً آزمایش نمایید.
        </p>

        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="sub_action" value="test_send">
            <input type="hidden" name="pattern_code" value="<?= e($pattern['pattern_code']) ?>">

            <div class="form-group">
                <label>شماره همراه گیرنده برای تست</label>
                <input class="form-control" type="text" name="test_phone" dir="ltr" inputmode="numeric" placeholder="09123456789" style="max-width:280px;" required>
            </div>

            <?php if (!empty($variables)): ?>
                <div style="margin-bottom:16px;">
                    <label style="font-weight:600; font-size:.88rem; display:block; margin-bottom:8px;">مقادیر متغیرها برای تست:</label>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <?php foreach ($variables as $v): ?>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="min-width:140px; font-size:.85rem; direction:ltr; text-align:left;">
                                    <code>%<?= e($v['name']) ?>%</code>
                                    <?php if (!empty($v['source_token'])): ?>
                                        <div style="font-size:.74rem; color:var(--color-muted); direction:rtl; text-align:right;">
                                            🔗 <?= e($globalTokens[$v['source_token']] ?? $v['source_token']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="test_var_name[]" value="<?= e($v['name']) ?>">
                                <input class="form-control" type="text" name="test_var_value[]" placeholder="<?= e($v['label'] ?: $v['name']) ?>" value="123456" style="max-width:260px;" required>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-outline" style="border-color:var(--color-primary); color:var(--color-primary);">
                🚀 ارسال پیامک تستی
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var allEventTokens = <?= json_encode($eventTokens, JSON_UNESCAPED_UNICODE) ?>;
    var globalTokens = <?= json_encode($globalTokens, JSON_UNESCAPED_UNICODE) ?>;
    var container = document.getElementById('variablesContainer');
    var addBtn = document.getElementById('addVarBtn');
    var eventSelect = document.getElementById('eventKeySelect');

    function buildTokenOptionsHtml(selectedEvent, selectedVal) {
        var html = '<option value="">-- بدون انتساب (دستی) --</option>';
        var eventMap = allEventTokens[selectedEvent] || {};

        if (Object.keys(eventMap).length > 0) {
            html += '<optgroup label="متغیرهای رویداد انتخابی" class="optgroup-event">';
            for (var k in eventMap) {
                var isSel = (k === selectedVal) ? ' selected' : '';
                html += '<option value="' + k + '"' + isSel + '>' + eventMap[k] + ' (' + k + ')</option>';
            }
            html += '</optgroup>';
        }

        html += '<optgroup label="سایر متغیرهای عمومی" class="optgroup-global">';
        for (var gk in globalTokens) {
            if (!eventMap[gk]) {
                var isGSel = (gk === selectedVal) ? ' selected' : '';
                html += '<option value="' + gk + '"' + isGSel + '>' + globalTokens[gk] + ' (' + gk + ')</option>';
            }
        }
        html += '</optgroup>';
        return html;
    }

    if (eventSelect) {
        eventSelect.addEventListener('change', function() {
            var newEvent = eventSelect.value;
            var selects = container.querySelectorAll('.var-token-select');
            selects.forEach(function(sel) {
                var currVal = sel.value;
                sel.innerHTML = buildTokenOptionsHtml(newEvent, currVal);
            });
        });
    }

    if (addBtn && container) {
        addBtn.addEventListener('click', function() {
            var curEv = eventSelect ? eventSelect.value : '';
            var row = document.createElement('div');
            row.className = 'var-row';
            row.style = 'display:flex; gap:10px; align-items:center; background:#FAF8F5; padding:12px; border-radius:8px; border:1px solid var(--color-border); flex-wrap:wrap;';
            row.innerHTML = '<div style="flex:1; min-width:130px;">' +
                '<label style="font-size:.78rem; display:block; margin-bottom:2px;">نام متغیر (فراز)</label>' +
                '<input class="form-control" type="text" name="var_name[]" dir="ltr" placeholder="param" required>' +
                '</div>' +
                '<div style="flex:1.5; min-width:160px;">' +
                '<label style="font-size:.78rem; display:block; margin-bottom:2px;">داده متصل سیستمی (Token)</label>' +
                '<select class="form-control var-token-select" name="var_token[]">' +
                buildTokenOptionsHtml(curEv, '') +
                '</select>' +
                '</div>' +
                '<div style="flex:1; min-width:105px;">' +
                '<label style="font-size:.78rem; display:block; margin-bottom:2px;">نوع متغیر</label>' +
                '<select class="form-control" name="var_type[]">' +
                '<option value="numeric">عددی (Numeric)</option>' +
                '<option value="string" selected>متنی (String)</option>' +
                '<option value="alphanumeric">حروف و عدد</option>' +
                '</select>' +
                '</div>' +
                '<div style="width:75px;">' +
                '<label style="font-size:.78rem; display:block; margin-bottom:2px;">حداکثر طول</label>' +
                '<input class="form-control" type="number" name="var_max_len[]" value="30" min="1" max="200">' +
                '</div>' +
                '<div style="flex:1.2; min-width:130px;">' +
                '<label style="font-size:.78rem; display:block; margin-bottom:2px;">عنوان فارسی</label>' +
                '<input class="form-control" type="text" name="var_label[]" placeholder="عنوان متغیر">' +
                '</div>' +
                '<div style="padding-top:16px;">' +
                '<button type="button" class="btn btn-outline btn-sm remove-var-btn" style="color:var(--color-danger); border-color:var(--color-danger);" title="حذف متغیر">✕</button>' +
                '</div>';
            container.appendChild(row);
        });

        container.addEventListener('click', function(e) {
            if (e.target && (e.target.classList.contains('remove-var-btn') || e.target.closest('.remove-var-btn'))) {
                var row = e.target.closest('.var-row');
                var totalRows = container.querySelectorAll('.var-row').length;
                if (totalRows > 1) {
                    row.remove();
                } else {
                    alert('حداقل یک متغیر برای هر الگو الزامی است.');
                }
            }
        });
    }
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
