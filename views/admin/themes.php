<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div style="margin-bottom:16px;">
    <a href="theme_edit.php" class="btn btn-primary">+ تم جدید</a>
</div>

<p style="color:var(--color-muted); font-size:.88rem; max-width:640px; margin-bottom:20px;">
    رنگ‌های تم فعال، بلافاصله و بدون نیاز به تغییر کد، در کل فروشگاه (سایت اصلی، نه پنل ادمین) اعمال می‌شود.
    می‌توانید چند تم بسازید، هرکدام را جداگانه ویرایش کنید، و هر زمان بین آن‌ها جابه‌جا شوید.
</p>

<table class="admin-table">
    <thead><tr><th>نام</th><th>رنگ‌ها</th><th>وضعیت</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($themes as $t): $tokens = $tokensByTheme[$t['id']]; ?>
        <tr>
            <td><?= e($t['name']) ?></td>
            <td>
                <div style="display:flex; gap:4px;">
                    <?php foreach (['bg', 'surface', 'primary', 'primary-dark', 'accent'] as $key): if (empty($tokens[$key])) continue; ?>
                        <span style="width:22px; height:22px; border-radius:50%; display:inline-block; border:1px solid var(--color-border); background:<?= e($tokens[$key]) ?>;" title="<?= e($key) ?>"></span>
                    <?php endforeach; ?>
                </div>
            </td>
            <td>
                <?php if ($t['is_active']): ?>
                    <span class="status-pill status-delivered">فعال</span>
                <?php else: ?>
                    <span style="color:var(--color-muted); font-size:.85rem;">—</span>
                <?php endif; ?>
            </td>
            <td>
                <div class="admin-actions">
                    <a href="theme_edit.php?id=<?= (int) $t['id'] ?>" class="btn btn-sm btn-outline">ویرایش</a>
                    <?php if (!$t['is_active']): ?>
                        <form method="post" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="activate">
                            <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-primary">فعال‌سازی</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="duplicate">
                        <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline">کپی</button>
                    </form>
                    <?php if (!$t['is_active']): ?>
                        <form method="post" onsubmit="return confirm('این تم حذف شود؟');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$themes): ?><tr><td colspan="4" style="text-align:center; color:var(--color-muted);">هنوز تمی ساخته نشده.</td></tr><?php endif; ?>
    </tbody>
</table>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
