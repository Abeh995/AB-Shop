<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<?php if ($errors): ?>
    <div class="alert alert-error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<div class="admin-card">
    <form method="post" enctype="multipart/form-data">
        <?= csrfField() ?>

        <div class="form-row">
            <div class="form-group">
                <label>نام محصول</label>
                <input class="form-control" type="text" name="name" value="<?= e($product['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>دسته‌بندی</label>
                <select class="form-control" name="category_id" required>
                    <option value="">انتخاب کنید</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (($product['category_id'] ?? 0) == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>توضیحات</label>
            <textarea class="form-control" name="description" rows="4"><?= e($product['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>قیمت (تومان)</label>
                <input class="form-control" type="text" inputmode="numeric" name="price" value="<?= e($product['price'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>قیمت با تخفیف (اختیاری)</label>
                <input class="form-control" type="text" inputmode="numeric" name="discount_price" value="<?= e($product['discount_price'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>موجودی کلی (اگر واریانت ندارد)</label>
                <input class="form-control" type="number" name="stock" value="<?= e($product['stock'] ?? '0') ?>">
            </div>
            <div class="form-group">
                <label>کد محصول / SKU (اختیاری)</label>
                <input class="form-control" type="text" name="sku" dir="ltr" value="<?= e($product['sku'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>تصویر اصلی محصول</label>
            <?php if (!empty($product['image'])): ?>
                <img src="<?= UPLOAD_URL . e($product['image']) ?>" style="width:90px; height:90px; object-fit:cover; border-radius:8px; margin-bottom:10px;">
            <?php endif; ?>
            <input class="form-control" type="file" name="image" accept="image/png,image/jpeg,image/webp">
        </div>

        <div class="form-group">
            <label class="group-label">سایزها / رنگ‌ها (اختیاری — اگر پر نشود، موجودی کلی بالا استفاده می‌شود)</label>
            <div id="variantRows">
                <?php
                $variantRows = $variants ?: [['size' => '', 'color' => '', 'stock' => '']];
                foreach ($variantRows as $v): ?>
                <div class="form-row" style="grid-template-columns: 1fr 1fr 1fr auto; align-items:center; margin-bottom:8px;">
                    <input class="form-control" type="text" name="variant_size[]" placeholder="سایز (مثلا 39-42)" value="<?= e($v['size'] ?? '') ?>">
                    <input class="form-control" type="text" name="variant_color[]" placeholder="رنگ (اختیاری)" value="<?= e($v['color'] ?? '') ?>">
                    <input class="form-control" type="number" name="variant_stock[]" placeholder="موجودی" value="<?= e((string)($v['stock'] ?? '')) ?>">
                    <button type="button" class="btn btn-sm btn-outline" onclick="this.closest('.form-row').remove()">حذف</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline" id="addVariantRow">+ افزودن ردیف</button>
        </div>

        <label style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
            <input type="checkbox" name="is_active" <?= (!$product || $product['is_active']) ? 'checked' : '' ?>> فعال (نمایش در فروشگاه)
        </label>
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:18px;">
            <input type="checkbox" name="is_featured" <?= (!empty($product['is_featured'])) ? 'checked' : '' ?>> نمایش در پیشنهاد ویژه صفحه اصلی
        </label>

        <button type="submit" class="btn btn-primary"><?= $product ? 'ذخیره تغییرات' : 'افزودن محصول' ?></button>
        <a href="products.php" class="btn btn-outline">انصراف</a>
    </form>
</div>

<script>
document.getElementById('addVariantRow').addEventListener('click', function () {
    var wrap = document.getElementById('variantRows');
    var row = document.createElement('div');
    row.className = 'form-row';
    row.style.cssText = 'grid-template-columns: 1fr 1fr 1fr auto; align-items:center; margin-bottom:8px;';
    row.innerHTML = '<input class="form-control" type="text" name="variant_size[]" placeholder="سایز">' +
        '<input class="form-control" type="text" name="variant_color[]" placeholder="رنگ (اختیاری)">' +
        '<input class="form-control" type="number" name="variant_stock[]" placeholder="موجودی">' +
        '<button type="button" class="btn btn-sm btn-outline" onclick="this.closest(\'.form-row\').remove()">حذف</button>';
    wrap.appendChild(row);
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
