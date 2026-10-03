<?php
/**
 * AB-Socks SMS Pattern Create / Edit Studio
 * v1.27.0 - Dual-Pane Layout, Variable Builder & Live Mobile Simulation
 *
 * @var string $pageTitle
 * @var array $pattern
 * @var bool $isNew
 * @var array $availableEvents
 * @var array $variables
 * @var array $eventTokens
 * @var array $globalTokens
 */
require APP_ROOT . '/views/admin/layout/header.php';
?>

<div style="margin-bottom:16px;">
    <a href="sms_patterns.php" style="color:var(--sms-muted); text-decoration:none; font-size:.88rem; font-weight:600;">
        ← بازگشت به لیست الگوهای پیامک
    </a>
</div>

<div class="sms-edit-grid">
    <!-- Left Column: Master Form & Dynamic Variable Builder -->
    <div class="admin-card" style="box-shadow:var(--sms-shadow);">
        <h3 style="margin:0 0 8px 0; font-size:1.15rem; font-weight:800;"><?= e($pageTitle) ?></h3>
        <p style="color:var(--sms-muted); font-size:0.85rem; margin-bottom:20px;">
            کد الگو را دقیقاً مطابق با تاییدیه پنل فراز اس‌ام‌اس وارد کرده و هر متغیر را به داده‌های سیستمی متصل نمایید.
        </p>

        <form method="post" id="smsPatternForm">
            <?= csrfField() ?>
            <input type="hidden" name="sub_action" value="save">

            <!-- 1. Base Form Fields -->
            <?php require APP_ROOT . '/views/admin/sms_pattern_edit_partials/_form_fields.php'; ?>

            <!-- 2. Dynamic Variables Builder -->
            <?php require APP_ROOT . '/views/admin/sms_pattern_edit_partials/_variables_builder.php'; ?>
        </form>
    </div>

    <!-- Right Column: Mobile Phone Simulation & Live Test Console -->
    <?php require APP_ROOT . '/views/admin/sms_pattern_edit_partials/_mobile_simulator.php'; ?>
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
            row.className = 'var-row-card var-row';
            row.innerHTML = '<div style="flex:1; min-width:130px;">' +
                '<label style="font-size:0.75rem; font-weight:700; display:block; margin-bottom:2px;">نام متغیر (Faraz)</label>' +
                '<input class="form-control" type="text" name="var_name[]" dir="ltr" placeholder="param" required>' +
                '</div>' +
                '<div style="flex:1.5; min-width:160px;">' +
                '<label style="font-size:0.75rem; font-weight:700; display:block; margin-bottom:2px;">داده متصل سیستمی (Token)</label>' +
                '<select class="form-control var-token-select" name="var_token[]">' +
                buildTokenOptionsHtml(curEv, '') +
                '</select>' +
                '</div>' +
                '<div style="flex:1; min-width:105px;">' +
                '<label style="font-size:0.75rem; font-weight:700; display:block; margin-bottom:2px;">نوع متغیر</label>' +
                '<select class="form-control" name="var_type[]">' +
                '<option value="numeric">عددی (Numeric)</option>' +
                '<option value="string" selected>متنی (String)</option>' +
                '<option value="alphanumeric">حروف و عدد</option>' +
                '</select>' +
                '</div>' +
                '<div style="width:75px;">' +
                '<label style="font-size:0.75rem; font-weight:700; display:block; margin-bottom:2px;">حداکثر طول</label>' +
                '<input class="form-control" type="number" name="var_max_len[]" value="30" min="1" max="200">' +
                '</div>' +
                '<div style="flex:1.2; min-width:130px;">' +
                '<label style="font-size:0.75rem; font-weight:700; display:block; margin-bottom:2px;">عنوان فارسی</label>' +
                '<input class="form-control" type="text" name="var_label[]" placeholder="عنوان متغیر">' +
                '</div>' +
                '<div style="padding-top:16px;">' +
                '<button type="button" class="btn btn-outline btn-sm remove-var-btn" style="color:var(--sms-danger); border-color:var(--sms-danger);" title="حذف متغیر">✕</button>' +
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

<?php
$adminSmsJsVer = APP_VERSION . '.' . (@filemtime(APP_ROOT . '/assets/js/admin-sms.js') ?: 1);
?>
<script src="/assets/js/admin-sms.js?v=<?= $adminSmsJsVer ?>"></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
