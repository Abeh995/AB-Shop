<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="max-width:920px;">
    <form method="post" id="themeForm">
        <?= csrfField() ?>
        <div class="form-group">
            <label>نام تم</label>
            <input class="form-control" type="text" name="name" required
                   value="<?= e($theme['name'] ?? '') ?>" style="max-width:320px;">
        </div>

        <div style="display:grid; grid-template-columns: 1fr 260px; gap:28px; align-items:start; margin-top:10px;">
            <div>
                <?php foreach ($colorTokenDefs as $key => $def):
                    $value = $currentTokens[$key] ?? $def['default'];
                ?>
                <div class="form-row" style="align-items:center; margin-bottom:10px;">
                    <label style="margin:0;"><?= e($def['label']) ?> <code style="font-size:.75rem; color:var(--color-muted);"><?= e($key) ?></code></label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="color" class="theme-color-picker" data-key="<?= e($key) ?>" value="<?= e($value) ?>" style="width:44px; height:38px; padding:2px; border:1px solid var(--color-border); border-radius:8px; cursor:pointer;">
                        <input type="text" class="form-control theme-color-text" data-key="<?= e($key) ?>" value="<?= e($value) ?>" dir="ltr" style="max-width:110px;">
                        <input type="hidden" name="token[<?= e($key) ?>]" class="theme-color-hidden" data-key="<?= e($key) ?>" value="<?= e($value) ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="position:sticky; top:16px;">
                <p style="font-size:.8rem; color:var(--color-muted); margin-bottom:8px;">پیش‌نمایش زنده</p>
                <div id="themePreview" style="border:1px solid var(--color-border); border-radius:14px; overflow:hidden;">
                    <div id="pv-surface" style="padding:16px;">
                        <div id="pv-card" style="border-radius:10px; padding:14px; border:1px solid; margin-bottom:12px;">
                            <div id="pv-text" style="font-weight:700; font-size:.95rem; margin-bottom:6px;">جوراب ساقدار طرح‌دار</div>
                            <div id="pv-muted" style="font-size:.8rem; margin-bottom:10px;">۱۵۶,۰۰۰ تومان</div>
                            <button type="button" id="pv-btn" style="border:none; border-radius:8px; padding:8px 16px; color:#fff; font-family:inherit; font-size:.85rem; font-weight:700;">افزودن به سبد</button>
                        </div>
                        <span id="pv-badge" style="display:inline-block; border-radius:20px; padding:4px 12px; font-size:.75rem; font-weight:700;">پیشنهاد ویژه</span>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top:22px;">
            <button type="submit" class="btn btn-primary">ذخیره تم</button>
            <a href="themes.php" class="btn btn-outline">انصراف</a>
        </div>
    </form>
</div>

<script>
(function () {
    function applyPreview() {
        var t = {};
        document.querySelectorAll('.theme-color-hidden').forEach(function (el) { t[el.dataset.key] = el.value; });
        var surface = document.getElementById('pv-surface');
        var card = document.getElementById('pv-card');
        var text = document.getElementById('pv-text');
        var muted = document.getElementById('pv-muted');
        var btn = document.getElementById('pv-btn');
        var badge = document.getElementById('pv-badge');
        surface.style.background = t['bg'] || '#fff';
        card.style.background = t['surface'] || '#fff';
        card.style.borderColor = t['border'] || '#eee';
        text.style.color = t['text'] || '#222';
        muted.style.color = t['muted'] || '#888';
        btn.style.background = t['primary'] || '#333';
        badge.style.background = t['primary-light'] || '#eee';
        badge.style.color = t['primary-dark'] || '#333';
    }

    document.querySelectorAll('.theme-color-picker').forEach(function (picker) {
        var key = picker.dataset.key;
        var textInput = document.querySelector('.theme-color-text[data-key="' + key + '"]');
        var hiddenInput = document.querySelector('.theme-color-hidden[data-key="' + key + '"]');

        picker.addEventListener('input', function () {
            textInput.value = picker.value;
            hiddenInput.value = picker.value;
            applyPreview();
        });
        textInput.addEventListener('input', function () {
            if (/^#[0-9a-fA-F]{6}$/.test(textInput.value)) {
                picker.value = textInput.value;
            }
            hiddenInput.value = textInput.value;
            applyPreview();
        });
    });

    applyPreview();
})();
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
