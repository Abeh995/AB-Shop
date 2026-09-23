<?php
require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="admin-topbar-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <p style="color:var(--color-muted); font-size:.9rem; margin:0;">
            مدیریت الگوهای پیامک (Faraz SMS) برای احراز هویت و اطلاع‌رسانی به مشتریان.
        </p>
    </div>
    <a href="sms_pattern_edit.php" class="btn btn-primary">➕ افزودن الگوی جدید</a>
</div>

<div class="admin-card">
    <?php if (empty($patterns)): ?>
        <p style="text-align:center; color:var(--color-muted); padding:32px 0;">
            هنوز هیچ الگوی پیامکی ثبت نشده است. روی «افزودن الگوی جدید» بزنید.
        </p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:70px;">شناسه</th>
                        <th>کد پترن (Faraz)</th>
                        <th>عنوان الگو</th>
                        <th>رویداد متناظر سیستمی</th>
                        <th>متغیرها</th>
                        <th style="width:110px;">وضعیت</th>
                        <th style="width:170px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patterns as $p): 
                        $cfg = json_decode($p['variables_config'] ?? '[]', true) ?: [];
                    ?>
                    <tr>
                        <td><code>#<?= (int) $p['id'] ?></code></td>
                        <td>
                            <strong style="font-family:monospace; direction:ltr; display:inline-block; font-size:1rem; color:var(--color-primary);">
                                <?= e($p['pattern_code']) ?>
                            </strong>
                        </td>
                        <td>
                            <strong><?= e($p['title']) ?></strong>
                            <?php if (!empty($p['description'])): ?>
                                <div style="font-size:.78rem; color:var(--color-muted); margin-top:2px;">
                                    <?= e($p['description']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($p['event_key']) && isset($availableEvents[$p['event_key']])): ?>
                                <span class="status-pill status-confirmed"><?= e($availableEvents[$p['event_key']]) ?></span>
                            <?php elseif (!empty($p['event_key'])): ?>
                                <span class="status-pill status-processing"><?= e($p['event_key']) ?></span>
                            <?php else: ?>
                                <span style="color:var(--color-muted); font-size:.82rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-pill status-pending" style="font-size:.78rem;"><?= (int) $p['variables_count'] ?> متغیر</span>
                            <?php if (!empty($cfg)): ?>
                                <div style="margin-top:4px; display:flex; flex-wrap:wrap; gap:4px;">
                                    <?php foreach ($cfg as $v): ?>
                                        <code style="background:#ECE8E1; padding:2px 6px; border-radius:4px; font-size:.75rem;">%<?= e($v['name']) ?>%</code>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <?php if ($p['is_active']): ?>
                                    <button type="submit" class="status-pill status-delivered" style="border:none; cursor:pointer;" title="کلیک برای غیرفعال کردن">
                                        ✓ فعال
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="status-pill status-cancelled" style="border:none; cursor:pointer;" title="کلیک برای فعال کردن">
                                        ✕ غیرفعال
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a href="sms_pattern_edit.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline btn-sm">ویرایش و تست</a>
                                <form method="post" style="display:inline;" onsubmit="return confirm('آیا از حذف الگوی «<?= e($p['title']) ?>» اطمینان دارید؟');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px;">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
