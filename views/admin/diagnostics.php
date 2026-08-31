<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card">
    <h3 style="margin-bottom:14px;">مقادیر فعلی تنظیمات (کلیدها جزئی مخفی شده‌اند)</h3>
    <table class="admin-table">
        <tbody>
        <?php foreach ($configSnapshot as $key => $value): ?>
            <tr><td style="font-weight:600;"><?= e($key) ?></td><td dir="ltr" style="font-family:monospace;"><?= e($value) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($lineNumberWarning): ?>
        <div class="alert alert-error" style="margin-top:14px;">⚠️ <?= e($lineNumberWarning) ?></div>
    <?php endif; ?>
</div>

<div class="admin-card">
    <h3 style="margin-bottom:10px;">۱) تست کلید API فراز اس‌ام‌اس</h3>
    <p style="color:var(--color-muted); font-size:.88rem; margin-bottom:14px;">این تست فقط موجودی حساب را می‌خواند؛ هیچ پیامکی ارسال نمی‌شود و هزینه‌ای ندارد.</p>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="check_balance">
        <button type="submit" class="btn btn-primary">تست اتصال و موجودی</button>
    </form>
    <?php if ($balanceResult): ?>
        <div class="alert alert-<?= $balanceResult['ok'] ? 'success' : 'error' ?>" style="margin-top:14px;">
            <?= $balanceResult['ok'] ? '✅' : '❌' ?> <?= e($balanceResult['summary']) ?>
        </div>
        <?php if ($balanceResult['debug']): ?>
        <details style="margin-top:10px;">
            <summary style="cursor:pointer; color:var(--color-muted); font-size:.85rem;">جزئیات فنی (درخواست/پاسخ خام)</summary>
            <pre style="background:#f6f2ea; padding:12px; border-radius:8px; font-size:.78rem; overflow-x:auto; direction:ltr; text-align:left;"><?= e(json_encode($balanceResult['debug'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></pre>
        </details>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="admin-card">
    <h3 style="margin-bottom:10px;">۲) بررسی جزئیات پترن (تایید نام واقعی متغیر)</h3>
    <p style="color:var(--color-muted); font-size:.88rem; margin-bottom:14px;">
        این تست نام واقعی متغیر(های) پترن ثبت‌شده در پنل فراز را نشان می‌دهد تا با مقدار
        <code>FARAZ_OTP_PATTERN_VAR</code> در config.php مقایسه کنید. اگر یکی نباشند، پیامک ارسال نمی‌شود.
    </p>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="check_pattern">
        <button type="submit" class="btn btn-primary">بررسی پترن</button>
    </form>
    <?php if ($patternResult): ?>
        <div class="alert alert-<?= $patternResult['ok'] ? 'success' : 'error' ?>" style="margin-top:14px;">
            <?= $patternResult['ok'] ? '✅' : '❌' ?> <?= e($patternResult['summary']) ?>
        </div>
        <?php if (!empty($patternResult['raw'])): ?>
        <pre style="background:#f6f2ea; padding:12px; border-radius:8px; font-size:.78rem; overflow-x:auto; direction:ltr; text-align:left;"><?= e(json_encode($patternResult['raw'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></pre>
        <?php endif; ?>
        <?php if ($patternResult['debug']): ?>
        <details style="margin-top:10px;">
            <summary style="cursor:pointer; color:var(--color-muted); font-size:.85rem;">جزئیات فنی</summary>
            <pre style="background:#f6f2ea; padding:12px; border-radius:8px; font-size:.78rem; overflow-x:auto; direction:ltr; text-align:left;"><?= e(json_encode($patternResult['debug'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></pre>
        </details>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="admin-card">
    <h3 style="margin-bottom:10px;">۳) تست اتصال SMTP (بدون ارسال ایمیل واقعی)</h3>
    <p style="color:var(--color-muted); font-size:.88rem; margin-bottom:14px;">این تست فقط اتصال، STARTTLS و احراز هویت را بررسی می‌کند؛ هیچ ایمیلی ارسال نمی‌شود.</p>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="check_smtp">
        <button type="submit" class="btn btn-primary">تست اتصال SMTP</button>
    </form>
    <?php if ($smtpResult): ?>
        <div class="alert alert-<?= $smtpResult['ok'] ? 'success' : 'error' ?>" style="margin-top:14px;">
            <?= $smtpResult['ok'] ? '✅' : '❌' ?> <?= e($smtpResult['summary']) ?>
        </div>
        <?php if ($smtpResult['debug']): ?>
        <details style="margin-top:10px;" open>
            <summary style="cursor:pointer; color:var(--color-muted); font-size:.85rem;">مکالمه کامل SMTP</summary>
            <pre style="background:#f6f2ea; padding:12px; border-radius:8px; font-size:.78rem; overflow-x:auto; direction:ltr; text-align:left; max-height:300px;"><?= e($smtpResult['debug']) ?></pre>
        </details>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="admin-card">
    <a href="notifications_log.php" class="btn btn-outline">مشاهده لاگ کامل تمام تلاش‌های ارسال (پیامک و ایمیل) ←</a>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
