<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="max-width: 680px; margin: 0 auto;">
    <h3 style="margin-top: 0; margin-bottom: 18px; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
        <?= $item && !empty($item['id']) ? 'ویرایش قلم هدیه و ملزومات' : 'افزودن قلم هدیه و ملزومات جدید' ?>
    </h3>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="margin-bottom: 18px;">
            <div style="font-weight: 700; margin-bottom: 4px;">خطا در ثبت اطلاعات:</div>
            <?php foreach ($errors as $err): ?>
                <div>• <?= e($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <?= csrfField() ?>

        <div class="form-group">
            <label style="font-weight: 600;">نام قلم / آیتم <span style="color: var(--color-danger);">*</span></label>
            <input class="form-control" type="text" name="name" value="<?= e($item['name'] ?? '') ?>" placeholder="مثلاً: جعبه کادویی هاردباکس طرح رز" required>
        </div>

        <div class="form-group">
            <label style="font-weight: 600;">تصویر آیتم</label>
            <?php if (!empty($item['image'])): ?>
                <div style="width: 90px; height: 90px; border-radius: 8px; overflow: hidden; border: 1px solid var(--color-border); margin-bottom: 10px;">
                    <img src="<?= e(UPLOAD_URL . $item['image']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            <?php endif; ?>
            <input class="form-control" type="file" name="image" accept="image/*,.heic,.heif" data-optimize-image="giftitem" data-max-dimension="1600" data-default-quality="0.30">
            <p style="font-size: 0.75rem; color: var(--color-muted); margin-top: 4px;">فرمت‌های مجاز: JPG, PNG, WEBP (حداکثر ۲ مگابایت)</p>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label style="font-weight: 600;">قیمت تمام‌شده برای فروشگاه (تومان) <span style="color: var(--color-danger);">*</span></label>
                <input class="form-control" type="text" inputmode="numeric" name="cost_price" value="<?= e((string)($item['cost_price'] ?? '')) ?>" placeholder="مثلاً: 12,000" required>
                <p style="font-size: 0.75rem; color: var(--color-muted); margin-top: 4px;">جهت محاسبه در گزارش سود خالص حسابداری.</p>
            </div>
            <div class="form-group">
                <label style="font-weight: 600;">موجودی انبار <span style="color: var(--color-danger);">*</span></label>
                <input class="form-control" type="number" name="stock" value="<?= e((string)($item['stock'] ?? 0)) ?>" min="0" required>
            </div>
        </div>

        <!-- Role Configuration Cards -->
        <div style="background: var(--color-bg-subtle, rgba(0,0,0,0.02)); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 16px;">
            <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 10px;">نقش‌ها و دسترسی‌های این آیتم:</div>

            <label style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; cursor: pointer;">
                <input type="checkbox" name="is_giftable" id="isGiftable" <?= (!$item || !empty($item['is_giftable'])) ? 'checked' : '' ?> style="width: 18px; height: 18px; margin-top: 2px;">
                <div>
                    <div style="font-weight: 600; font-size: 0.9rem;">قابل اهدای رایگان به سفارش توسط مدیر</div>
                    <div style="font-size: 0.78rem; color: var(--color-muted);">در صفحه بررسی سفارش، ادمین می‌تواند این قلم را به عنوان اشانتیون یا کادو به سفارش بیفزاید.</div>
                </div>
            </label>

            <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_post_orderable" id="isPostOrderable" <?= !empty($item['is_post_orderable']) ? 'checked' : '' ?> style="width: 18px; height: 18px; margin-top: 2px;">
                <div>
                    <div style="font-weight: 600; font-size: 0.9rem;">قابل فروش به‌عنوان «پیشنهاد بعد از سبد خرید»</div>
                    <div style="font-size: 0.78rem; color: var(--color-muted);">در صفحه سبد خرید و قبل از پرداخت، به عنوان کالای مکمل به مشتری پیشنهاد داده می‌شود.</div>
                </div>
            </label>
        </div>

        <div class="form-group" id="postOrderPriceGroup" style="<?= empty($item['is_post_orderable']) ? 'display:none;' : '' ?> background: rgba(59, 130, 246, 0.04); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: var(--radius-md); padding: 12px 14px;">
            <label style="font-weight: 600; color: var(--color-primary);">قیمت فروش در پیشنهاد بعد از سبد (تومان) <span style="color: var(--color-danger);">*</span></label>
            <input class="form-control" type="text" inputmode="numeric" name="post_order_price" value="<?= e((string)($item['post_order_price'] ?? '')) ?>" placeholder="مثلاً: 25,000">
            <p style="font-size: 0.75rem; color: var(--color-muted); margin-top: 4px;">مبلغی که به فاکتور مشتری در صورت انتخاب اضافه خواهد شد.</p>
        </div>

        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px; cursor: pointer;">
            <input type="checkbox" name="is_active" <?= (!$item || !empty($item['is_active'])) ? 'checked' : '' ?> style="width: 16px; height: 16px;">
            <span style="font-weight: 600;">فعال (آیتم در دسترس است)</span>
        </label>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="padding: 9px 24px; font-weight: 700;">
                <?= $item && !empty($item['id']) ? 'ذخیره تغییرات' : 'ثبت آیتم جدید' ?>
            </button>
            <a href="gift_items.php" class="btn btn-outline">انصراف</a>
        </div>
    </form>
</div>

<script>
document.getElementById('isPostOrderable').addEventListener('change', function () {
    document.getElementById('postOrderPriceGroup').style.display = this.checked ? 'block' : 'none';
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
