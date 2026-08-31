<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="display:flex; gap:8px;">
    <a href="?tab=sms" class="btn btn-sm <?= $tab === 'sms' ? 'btn-primary' : 'btn-outline' ?>">پیامک‌ها</a>
    <a href="?tab=email" class="btn btn-sm <?= $tab === 'email' ? 'btn-primary' : 'btn-outline' ?>">ایمیل‌ها</a>
    <a href="diagnostics.php" class="btn btn-sm btn-outline" style="margin-right:auto;">صفحه عیب‌یابی ←</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th><?= $tab === 'sms' ? 'شماره موبایل' : 'ایمیل' ?></th>
                <th><?= $tab === 'sms' ? 'متن' : 'موضوع' ?></th>
                <th>وضعیت</th>
                <th>تاریخ</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r):
            $isSuccess = $r['status'] === 'sent';
            $isNotConfigured = strpos($r['status'], 'logged') === 0;
            $pillClass = $isSuccess ? 'status-delivered' : ($isNotConfigured ? 'status-pending' : 'status-cancelled');
        ?>
        <tr>
            <td dir="ltr"><?= e($r[$tab === 'sms' ? 'phone' : 'email']) ?></td>
            <td style="max-width:260px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= e($r[$tab === 'sms' ? 'message' : 'subject']) ?></td>
            <td><span class="status-pill <?= $pillClass ?>"><?= e($r['status']) ?></span></td>
            <td><?= toPersianDigits(date('Y/m/d H:i', strtotime($r['created_at']))) ?></td>
            <td>
                <?php if (!empty($r['debug_info'])): ?>
                <details>
                    <summary style="cursor:pointer; color:var(--color-primary); font-size:.82rem;">جزئیات</summary>
                    <pre style="background:#f6f2ea; padding:10px; border-radius:8px; font-size:.75rem; max-width:500px; overflow-x:auto; direction:ltr; text-align:left;"><?= e($r['debug_info']) ?></pre>
                </details>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5" style="text-align:center; color:var(--color-muted);">هنوز هیچ تلاشی ثبت نشده است.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <p style="font-size:.8rem; color:var(--color-muted); margin-top:14px;">فقط ۱۰۰ رکورد آخر نمایش داده می‌شود.</p>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
