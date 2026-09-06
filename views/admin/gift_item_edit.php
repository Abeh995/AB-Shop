<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="max-width:640px;">
    <?php if ($errors): ?>
        <div class="alert alert-error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <?= csrfField() ?>

        <div class="form-group">
            <label>نام آیتم</label>
            <input class="form-control" type="text" name="name" value="<?= e($item['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>تصویر</label>
            <?php if (!empty($item['image'])): ?>
                <img src="<?= e(UPLOAD_URL . $item['image']) ?>" alt="" style="width:80px; height:80px; object-fit:cover; border-radius:8px; margin-bottom:8px; display:block;">
            <?php endif; ?>
            <input class="form-control" type="file" name="image" accept="image/png,image/jpeg,image/webp">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>قیمت تمام‌شده (تومان)</label>
                <input class="form-control" type="text" inputmode="numeric" name="cost_price" value="<?= e((string)($item['cost_price'] ?? '')) ?>" required>
                <p style="font-size:.75rem; color:var(--color-muted); margin-top:4px;">هزینه واقعی این آیتم برای فروشگاه؛ به مشتری نمایش داده نمی‌شود.</p>
            </div>
            <div class="form-group">
                <label>موجودی</label>
                <input class="form-control" type="number" name="stock" value="<?= e((string)($item['stock'] ?? 0)) ?>" min="0" required>
            </div>
        </div>

        <label style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
            <input type="checkbox" name="is_active" <?= (!$item || $item['is_active']) ? 'checked' : '' ?>> فعال
        </label>
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
            <input type="checkbox" name="is_giftable" id="isGiftable" <?= (!$item || $item['is_giftable']) ? 'checked' : '' ?>> قابل اهدای رایگان توسط ادمین به یک سفارش
        </label>
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
            <input type="checkbox" name="is_post_orderable" id="isPostOrderable" <?= !empty($item['is_post_orderable']) ? 'checked' : '' ?>> قابل فروش به‌عنوان «پیشنهاد بعد از سبد» در صفحه سبد خرید
        </label>
        <p style="font-size:.78rem; color:var(--color-muted); margin-bottom:14px;">
            این دو گزینه مستقل از هم هستند و می‌توانید هر دو را همزمان فعال کنید — یک آیتم می‌تواند هم هدیه باشد، هم قابل خرید.
        </p>

        <div class="form-group" id="postOrderPriceGroup" style="<?= empty($item['is_post_orderable']) ? 'display:none;' : '' ?>">
            <label>قیمت فروش به‌عنوان پیشنهاد بعد از سبد (تومان)</label>
            <input class="form-control" type="text" inputmode="numeric" name="post_order_price" value="<?= e((string)($item['post_order_price'] ?? '')) ?>">
        </div>

        <button type="submit" class="btn btn-primary"><?= $item && !empty($item['id']) ? 'ذخیره تغییرات' : 'افزودن آیتم' ?></button>
        <a href="gift_items.php" class="btn btn-outline">انصراف</a>
    </form>
</div>

<script>
document.getElementById('isPostOrderable').addEventListener('change', function () {
    document.getElementById('postOrderPriceGroup').style.display = this.checked ? 'block' : 'none';
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
