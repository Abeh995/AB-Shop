<?php
/**
 * AB-Socks SMS Pattern Edit — Dynamic Variable Builder & Token Binding
 * @var array $variables
 * @var array $eventTokens
 * @var array $globalTokens
 * @var array $pattern
 */
$selectedEvent = $pattern['event_key'] ?? '';
$currEventTokens = $eventTokens[$selectedEvent] ?? [];
if (empty($variables)) {
    $variables = [
        [
            'name'         => 'code',
            'type'         => 'numeric',
            'max_len'      => 6,
            'label'        => 'کد تایید',
            'source_token' => 'code',
        ]
    ];
}
?>
<hr style="border:none; border-top:1px solid var(--sms-border); margin:20px 0 16px;">

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
    <div>
        <h4 style="margin:0; font-size:1.02rem; font-weight:800;">
            🔗 متغیرهای الگو و نگاشت داده‌های سیستمی
        </h4>
        <span style="font-size:0.78rem; color:var(--sms-muted);">
            نام متغیر را طبق تاییدیه فراز وارد کرده و آن را به توکن داده‌های سیستمی متصل کنید.
        </span>
    </div>
    <button type="button" class="btn btn-outline btn-sm" id="addVarBtn">
        ➕ افزودن متغیر
    </button>
</div>

<div id="variablesContainer" style="display:flex; flex-direction:column; gap:12px; margin-bottom:20px;">
    <?php foreach ($variables as $idx => $v): 
        $curToken = $v['source_token'] ?? '';
    ?>
    <div class="var-row-card var-row">
        <div style="flex:1; min-width:130px;">
            <label style="font-size:0.75rem; font-weight:700; display:block; margin-bottom:2px;">نام متغیر (Faraz)</label>
            <input class="form-control" type="text" name="var_name[]" dir="ltr" value="<?= e($v['name'] ?? '') ?>" placeholder="مثلاً: code" required>
        </div>

        <div style="flex:1.5; min-width:160px;">
            <label style="font-size:0.75rem; font-weight:700; display:block; margin-bottom:2px;">داده متصل سیستمی (Token)</label>
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
            <label style="font-size:0.75rem; font-weight:700; display:block; margin-bottom:2px;">نوع متغیر</label>
            <select class="form-control" name="var_type[]">
                <option value="numeric" <?= ($v['type'] ?? '') === 'numeric' ? 'selected' : '' ?>>عددی (Numeric)</option>
                <option value="string" <?= ($v['type'] ?? '') === 'string' ? 'selected' : '' ?>>متنی (String)</option>
                <option value="alphanumeric" <?= ($v['type'] ?? '') === 'alphanumeric' ? 'selected' : '' ?>>حروف و عدد</option>
            </select>
        </div>

        <div style="width:75px;">
            <label style="font-size:0.75rem; font-weight:700; display:block; margin-bottom:2px;">حداکثر طول</label>
            <input class="form-control" type="number" name="var_max_len[]" value="<?= (int)($v['max_len'] ?? 30) ?>" min="1" max="200">
        </div>

        <div style="flex:1.2; min-width:130px;">
            <label style="font-size:0.75rem; font-weight:700; display:block; margin-bottom:2px;">عنوان فارسی</label>
            <input class="form-control" type="text" name="var_label[]" value="<?= e($v['label'] ?? '') ?>" placeholder="مثلاً: کد تایید">
        </div>

        <div style="padding-top:16px;">
            <button type="button" class="btn btn-outline btn-sm remove-var-btn" style="color:var(--sms-danger); border-color:var(--sms-danger);" title="حذف متغیر">✕</button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<button type="submit" class="btn btn-primary" style="padding:10px 24px;">
    <?= !empty($pattern['id']) ? 'ذخیره تغییرات الگو' : 'ثبت و ذخیره الگو' ?>
</button>
