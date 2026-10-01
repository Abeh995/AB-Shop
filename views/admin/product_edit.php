<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <div style="font-weight: 700; margin-bottom: 4px;">لطفاً خطاهای زیر را برطرف کنید:</div>
        <?php foreach ($errors as $err): ?>
            <div>• <?= e($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <?= csrfField() ?>

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
        
        <!-- ============================================== -->
        <!-- RIGHT COLUMN: Core Content & Pricing (65%)      -->
        <!-- ============================================== -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- 1. Basic Information Card -->
            <div class="admin-card">
                <h3 style="margin-top: 0; margin-bottom: 18px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    اطلاعات پایه کالا
                </h3>

                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label style="font-weight: 600;">نام کامل محصول <span style="color: var(--color-danger);">*</span></label>
                        <input class="form-control" type="text" name="name" value="<?= e($product['name'] ?? '') ?>" placeholder="مثلاً: جوراب ساق‌دار نخی اعلا طرح آلیس" required>
                    </div>
                    <div class="form-group" style="flex: 1.2;">
                        <label style="font-weight: 600;">دسته‌بندی <span style="color: var(--color-danger);">*</span></label>
                        <select class="form-control" name="category_id" required>
                            <option value="">انتخاب دسته‌بندی</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= (($product['category_id'] ?? 0) == $c['id']) ? 'selected' : '' ?>>
                                    <?= $c['depth'] > 0 ? str_repeat('— ', $c['depth']) : '' ?><?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600;">کد شناسه محصول (SKU)</label>
                    <input class="form-control" type="text" name="sku" dir="ltr" value="<?= e($product['sku'] ?? '') ?>" placeholder="اختیاری — در صورت خالی بودن، خودکار تولید می‌شود">
                    <p style="font-size: 0.78rem; color: var(--color-muted); margin-top: 4px;">کد یکتا برای رهگیری کالا در انبارداری و نرم‌افزارهای حسابداری.</p>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-weight: 600;">توضیحات و معرفی محصول</label>
                    <textarea class="form-control" name="description" rows="5" placeholder="ویژگی‌ها، جنس پارچه، الیاف و نحوه نگهداری..."><?= e($product['description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- 2. Pricing & Live Margin Calculator Card -->
            <div class="admin-card">
                <h3 style="margin-top: 0; margin-bottom: 18px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    قیمت‌گذاری و تحلیل سود
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label style="font-weight: 600;">قیمت فروش اصلی (تومان) <span style="color: var(--color-danger);">*</span></label>
                        <input class="form-control" type="text" inputmode="numeric" id="priceInput" name="price" value="<?= e($product['price'] ?? '') ?>" placeholder="مثلاً: 85,000" required>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600;">قیمت فروش ویژه / تخفیف‌دار (تومان)</label>
                        <input class="form-control" type="text" inputmode="numeric" id="discountPriceInput" name="discount_price" value="<?= e($product['discount_price'] ?? '') ?>" placeholder="اختیاری">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600;">قیمت خرید / تمام‌شده (تومان)</label>
                        <input class="form-control" type="text" inputmode="numeric" id="costPriceInput" name="cost_price" value="<?= e($product['cost_price'] ?? '') ?>" placeholder="اختیاری (محرمانه)">
                    </div>
                </div>

                <!-- Live Profit Margin Dashboard Box -->
                <div id="marginIndicatorBox" style="background: var(--color-bg-subtle, rgba(0,0,0,0.03)); border: 1px dashed var(--color-border); border-radius: var(--radius-md); padding: 12px 16px; margin-top: 6px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; gap: 16px; align-items: center;">
                        <div>
                            <span style="font-size: 0.8rem; color: var(--color-muted);">سود ناخالص هر عدد:</span>
                            <div id="unitProfitDisplay" style="font-weight: 700; font-size: 1rem; color: var(--color-text);">—</div>
                        </div>
                        <div style="width: 1px; height: 28px; background: var(--color-border);"></div>
                        <div>
                            <span style="font-size: 0.8rem; color: var(--color-muted);">حاشیه سود (مارجین):</span>
                            <div id="marginPercentageDisplay" style="font-weight: 700; font-size: 1rem; color: var(--color-text);">—</div>
                        </div>
                    </div>
                    <div style="font-size: 0.78rem; color: var(--color-muted);">
                        * به مشتری نمایش داده نمی‌شود و صرفاً در گزارش‌های حسابداری و داشبورد مالی لحاظ می‌گردد.
                    </div>
                </div>
            </div>

            <!-- 3. Variants & Stock Management Card -->
            <div class="admin-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                        موجودی انبار و تنوع (واریانت)
                    </h3>
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer; user-select: none;">
                        <input type="checkbox" id="hasVariantsToggle" name="has_variants" <?= $hasVariantsInitial ? 'checked' : '' ?> style="width: 16px; height: 16px;">
                        دارای چند سایز یا رنگ
                    </label>
                </div>

                <!-- Single Product Stock Field -->
                <div class="form-group" id="stockFieldWrap" style="background: var(--color-bg-subtle, rgba(0,0,0,0.02)); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 16px;">
                    <label style="font-weight: 600;">تعداد موجودی کلی انبار</label>
                    <input class="form-control" type="number" id="stockField" name="stock" value="<?= e($product['stock'] ?? '0') ?>" style="max-width: 200px;">
                    <p style="font-size: 0.78rem; color: var(--color-muted); margin-top: 6px;">مختص محصولات تک‌سایز و تک‌رنگ. در صورت فعال بودن واریانت‌ها، موجودی هر مدل در جدول زیر ثبت می‌شود.</p>
                </div>

                <!-- Multi-Variant Table Section -->
                <div id="variantSection">
                    <div style="background: var(--color-bg-subtle, rgba(0,0,0,0.02)); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 10px 14px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer; user-select: none; margin: 0; font-size: 0.86rem;">
                            <input type="checkbox" id="useGlobalVariantStrategy" name="use_global_variant_strategy" value="1" <?= (!isset($product['use_global_variant_strategy']) || (int)$product['use_global_variant_strategy'] === 1) ? 'checked' : '' ?> style="width: 16px; height: 16px;">
                            <span>پیروی از استراتژی سراسری فروشگاه (انتخاب خودکار بر اساس بیشترین موجودی)</span>
                        </label>
                        <span style="font-size: 0.78rem; color: var(--color-muted);">
                            (با غیرفعال‌کردن این گزینه، می‌توانید یکی از گزینه‌ها را در جدول زیر به‌صورت دستی پیش‌فرض کنید)
                        </span>
                    </div>

                    <div style="border: 1px solid var(--color-border); border-radius: var(--radius-md); overflow: hidden; margin-bottom: 12px;">
                        <table class="admin-table" style="margin: 0; width: 100%;">
                            <thead>
                                <tr style="background: var(--color-bg-subtle, rgba(0,0,0,0.03)); border-bottom: 1px solid var(--color-border);">
                                    <th style="width: 26%;">سایز (مثلاً ۳۶ تا ۴۰)</th>
                                    <th style="width: 22%;">رنگ (اختیاری)</th>
                                    <th style="width: 16%;">موجودی</th>
                                    <th style="width: 22%;">قیمت تمام‌شده (تومان)</th>
                                    <th style="width: 14%; text-align: center;">پیش‌فرض</th>
                                    <th style="width: 40px; text-align: center;">حذف</th>
                                </tr>
                            </thead>
                            <tbody id="variantRows">
                                <?php
                                $variantRows = $variants ?: [['id' => '', 'size' => '', 'color' => '', 'stock' => '', 'cost_price' => '', 'is_default' => 1]];
                                $hasAnyExplicitDefault = !empty(array_filter($variantRows, fn($x) => !empty($x['is_default'])));
                                foreach ($variantRows as $idx => $v): 
                                    $isDefaultChecked = !empty($v['is_default']) || (!$hasAnyExplicitDefault && $idx === 0);
                                ?>
                                <tr class="variant-item-row" style="border-bottom: 1px solid var(--color-border);">
                                    <input type="hidden" name="variant_id[]" value="<?= e((string)($v['id'] ?? '')) ?>">
                                    <td style="padding: 8px;">
                                        <input class="form-control variant-input" type="text" name="variant_size[]" placeholder="مثلاً: 39-42" value="<?= e($v['size'] ?? '') ?>">
                                    </td>
                                    <td style="padding: 8px;">
                                        <input class="form-control variant-input" type="text" name="variant_color[]" placeholder="مثلاً: مشکی" value="<?= e($v['color'] ?? '') ?>">
                                    </td>
                                    <td style="padding: 8px;">
                                        <input class="form-control variant-input" type="number" name="variant_stock[]" placeholder="۰" value="<?= e((string)($v['stock'] ?? '')) ?>">
                                    </td>
                                    <td style="padding: 8px;">
                                        <input class="form-control variant-input" type="text" inputmode="numeric" name="variant_cost_price[]" placeholder="پیش‌فرض محصول" value="<?= e((string)($v['cost_price'] ?? '')) ?>">
                                    </td>
                                    <td style="padding: 8px; text-align: center;">
                                        <label style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 100%; height: 100%; margin: 0;" title="انتخاب به عنوان واریانت پیش‌فرض دستی">
                                            <input type="radio" class="variant-default-radio" name="default_variant_index" value="<?= $idx ?>" <?= $isDefaultChecked ? 'checked' : '' ?>>
                                        </label>
                                    </td>
                                    <td style="padding: 8px; text-align: center;">
                                        <button type="button" class="btn btn-sm btn-outline" onclick="this.closest('tr').remove()" style="color: var(--color-danger); padding: 4px 8px;" title="حذف این واریانت">✕</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline" id="addVariantRow" style="display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        افزودن سطر واریانت جدید
                    </button>
                </div>
            </div>

            <!-- 4. Tags & SEO Card -->
            <div class="admin-card">
                <h3 style="margin-top: 0; margin-bottom: 14px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    برچسب‌ها و تگ‌های سئو
                </h3>

                <?php if ($allTags): ?>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px;">
                    <?php foreach ($allTags as $tag): 
                        $isSelected = in_array($tag['id'], $productTagIds);
                    ?>
                    <label class="variant-chip <?= $isSelected ? 'selected' : '' ?>" style="cursor: pointer; user-select: none;">
                        <input type="checkbox" name="tag_ids[]" value="<?= (int)$tag['id'] ?>" <?= $isSelected ? 'checked' : '' ?>
                               onchange="this.closest('.variant-chip').classList.toggle('selected', this.checked)">
                        <?= e($tag['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-weight: 500; font-size: 0.85rem;">افزودن تگ‌های تازه (با کاما جدا کنید)</label>
                    <input class="form-control" type="text" name="new_tags" placeholder="مثلاً: نخی, کالج, پنبه اعلا, بهاره">
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- LEFT COLUMN: Sidebar & Media (35%)             -->
        <!-- ============================================== -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- 1. Publication & Actions Box (Sticky) -->
            <div class="admin-card" style="position: sticky; top: 20px; z-index: 10;">
                <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 1.05rem;">وضعیت انتشار</h3>

                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="is_active" <?= (!$product || !empty($product['is_active'])) ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                        <span style="font-weight: 600;">فعال (نمایش و فروش در سایت)</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="is_featured" <?= (!empty($product['is_featured'])) ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                        <span>پیشنهاد ویژه در صفحه اصلی</span>
                    </label>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 700; font-size: 1rem;">
                        <?= $product ? 'ذخیره تغییرات محصول' : 'ایجاد و ثبت نهایی محصول' ?>
                    </button>
                    <a href="products.php" class="btn btn-outline" style="width: 100%; justify-content: center;">انصراف و بازگشت</a>
                </div>

                <?php if (!empty($product['id'])): ?>
                <div style="border-top: 1px solid var(--color-border); margin-top: 16px; padding-top: 12px; text-align: center;">
                    <a href="/product.php?slug=<?= urlencode($product['slug']) ?>" target="_blank" style="font-size: 0.82rem; color: var(--color-primary); display: inline-flex; align-items: center; gap: 4px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        مشاهده پیش‌نمایش در فروشگاه
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- 2. Primary Product Image (Cover) -->
            <div class="admin-card">
                <h3 style="margin-top: 0; margin-bottom: 14px; font-size: 1.05rem;">تصویر شاخص (کاور)</h3>

                <div style="text-align: center; margin-bottom: 14px;">
                    <?php if (!empty($product['image'])): ?>
                        <div style="width: 100%; height: 220px; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--color-border); background: #f9fafb; margin-bottom: 10px;">
                            <img src="<?= UPLOAD_URL . e($product['image']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php else: ?>
                        <div style="width: 100%; height: 160px; border-radius: var(--radius-md); border: 2px dashed var(--color-border); background: #f9fafb; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--color-muted); margin-bottom: 10px;">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 6px; opacity: 0.6;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <span style="font-size: 0.85rem;">هنوز تصویری انتخاب نشده است</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.85rem; font-weight: 600;">بارگذاری یا جایگزینی تصویر</label>
                    <input class="form-control" type="file" name="image" accept="image/*,.heic,.heif" data-optimize-image="product" data-max-dimension="1600" data-default-quality="0.85">
                </div>
            </div>

            <!-- 3. Additional Gallery Images -->
            <div class="admin-card">
                <h3 style="margin-top: 0; margin-bottom: 14px; font-size: 1.05rem;">گالری تصاویر بیشتر</h3>

                <?php if (!empty($galleryImages)): ?>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 14px;">
                    <?php foreach ($galleryImages as $img): ?>
                    <div style="border: 1px solid var(--color-border); border-radius: 8px; padding: 4px; background: #fff; text-align: center;">
                        <img src="<?= UPLOAD_URL . e($img['image_path']) ?>" style="width: 100%; height: 70px; object-fit: cover; border-radius: 6px; display: block; margin-bottom: 4px;">
                        <label style="display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 0.72rem; color: var(--color-danger); cursor: pointer;">
                            <input type="checkbox" name="delete_image_ids[]" value="<?= (int)$img['id'] ?>"> حذف
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.85rem; font-weight: 600;">افزودن عکس‌های جدید به گالری</label>
                    <input class="form-control" type="file" name="gallery_images[]" accept="image/*,.heic,.heif" multiple data-optimize-image="gallery" data-max-dimension="1600" data-default-quality="0.85">
                    <p style="font-size: 0.75rem; color: var(--color-muted); margin-top: 4px;">می‌توانید چند عکس را هم‌زمان انتخاب کنید.</p>
                </div>
            </div>

            <!-- 4. Price Audit History Card -->
            <?php if (!empty($priceHistory)): ?>
            <div class="admin-card">
                <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 1rem;">تاریخچه نوسانات قیمت</h3>
                <div style="max-height: 240px; overflow-y: auto; font-size: 0.8rem;">
                    <?php foreach ($priceHistory as $h): ?>
                    <div style="padding: 8px 0; border-bottom: 1px solid var(--color-border);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                            <strong><?= $h['field_changed'] === 'cost_price' ? 'قیمت تمام‌شده' : 'قیمت فروش' ?></strong>
                            <span style="color: var(--color-muted); font-size: 0.72rem;"><?= toPersianDigits(date('Y/m/d H:i', strtotime($h['created_at']))) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--color-muted);"><?= $h['previous_value'] !== null ? formatPrice((int)$h['previous_value']) : '—' ?> ← <?= formatPrice((int)$h['new_value']) ?></span>
                            <span style="color: <?= $h['change_amount'] >= 0 ? 'var(--color-success)' : 'var(--color-danger)' ?>; font-weight: 600;">
                                <?= $h['change_amount'] >= 0 ? '+' : '' ?><?= toPersianDigits(number_format((int)$h['change_amount'])) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</form>

<script>
// Variant row addition
document.getElementById('addVariantRow').addEventListener('click', function () {
    var tbody = document.getElementById('variantRows');
    var nextIdx = tbody.querySelectorAll('.variant-item-row').length;
    var useGlobalEl = document.getElementById('useGlobalVariantStrategy');
    var useGlobal = useGlobalEl ? useGlobalEl.checked : true;
    var tr = document.createElement('tr');
    tr.className = 'variant-item-row';
    tr.style.cssText = 'border-bottom: 1px solid var(--color-border);';
    tr.innerHTML = '<input type="hidden" name="variant_id[]" value="">' +
        '<td style="padding: 8px;"><input class="form-control variant-input" type="text" name="variant_size[]" placeholder="سایز"></td>' +
        '<td style="padding: 8px;"><input class="form-control variant-input" type="text" name="variant_color[]" placeholder="رنگ"></td>' +
        '<td style="padding: 8px;"><input class="form-control variant-input" type="number" name="variant_stock[]" placeholder="۰"></td>' +
        '<td style="padding: 8px;"><input class="form-control variant-input" type="text" inputmode="numeric" name="variant_cost_price[]" placeholder="پیش‌فرض"></td>' +
        '<td style="padding: 8px; text-align: center;"><label style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 100%; height: 100%; margin: 0;"><input type="radio" class="variant-default-radio" name="default_variant_index" value="' + nextIdx + '" ' + (useGlobal ? 'disabled' : '') + '></label></td>' +
        '<td style="padding: 8px; text-align: center;"><button type="button" class="btn btn-sm btn-outline" onclick="this.closest(\'tr\').remove()" style="color: var(--color-danger); padding: 4px 8px;">✕</button></td>';
    tbody.appendChild(tr);
    syncGlobalVariantStrategy();
});

// Sync global variant strategy toggle
function syncGlobalVariantStrategy() {
    var useGlobalEl = document.getElementById('useGlobalVariantStrategy');
    if (!useGlobalEl) return;
    var useGlobal = useGlobalEl.checked;
    var hasVariants = document.getElementById('hasVariantsToggle').checked;
    useGlobalEl.disabled = !hasVariants;
    document.querySelectorAll('.variant-default-radio').forEach(function (radio) {
        radio.disabled = useGlobal || !hasVariants;
        if (radio.closest('td')) {
            radio.closest('td').style.opacity = (useGlobal || !hasVariants) ? '0.35' : '1';
        }
    });
}
document.getElementById('useGlobalVariantStrategy').addEventListener('change', syncGlobalVariantStrategy);

// Sync variant toggle
function syncVariantToggle() {
    var hasVariants = document.getElementById('hasVariantsToggle').checked;
    var stockField = document.getElementById('stockField');
    var stockWrap = document.getElementById('stockFieldWrap');
    var variantSection = document.getElementById('variantSection');

    stockField.disabled = hasVariants;
    stockWrap.style.opacity = hasVariants ? '0.5' : '1';
    variantSection.style.opacity = hasVariants ? '1' : '0.5';
    variantSection.style.pointerEvents = hasVariants ? 'auto' : 'none';
    document.querySelectorAll('.variant-input').forEach(function (el) { el.disabled = !hasVariants; });
    syncGlobalVariantStrategy();
}
document.getElementById('hasVariantsToggle').addEventListener('change', syncVariantToggle);
syncVariantToggle();

// Live Profit Margin Calculator
function calculateLiveMargin() {
    var priceStr = document.getElementById('priceInput').value.replace(/\D/g, '');
    var discountStr = document.getElementById('discountPriceInput').value.replace(/\D/g, '');
    var costStr = document.getElementById('costPriceInput').value.replace(/\D/g, '');

    var price = parseInt(priceStr, 10) || 0;
    var discount = parseInt(discountStr, 10) || 0;
    var cost = parseInt(costStr, 10) || 0;

    var effectiveSellingPrice = (discount > 0 && discount < price) ? discount : price;
    var unitProfitEl = document.getElementById('unitProfitDisplay');
    var marginEl = document.getElementById('marginPercentageDisplay');

    if (effectiveSellingPrice > 0 && cost > 0) {
        var profit = effectiveSellingPrice - cost;
        var marginPct = (profit / effectiveSellingPrice) * 100;

        unitProfitEl.innerText = profit.toLocaleString('fa-IR') + ' تومان';
        unitProfitEl.style.color = profit >= 0 ? 'var(--color-success)' : 'var(--color-danger)';

        marginEl.innerText = marginPct.toFixed(1).replace('.', '٫') + '٪';
        marginEl.style.color = marginPct >= 0 ? 'var(--color-success)' : 'var(--color-danger)';
    } else {
        unitProfitEl.innerText = '—';
        unitProfitEl.style.color = 'var(--color-text)';
        marginEl.innerText = '—';
        marginEl.style.color = 'var(--color-text)';
    }
}

document.getElementById('priceInput').addEventListener('input', calculateLiveMargin);
document.getElementById('discountPriceInput').addEventListener('input', calculateLiveMargin);
document.getElementById('costPriceInput').addEventListener('input', calculateLiveMargin);
calculateLiveMargin();
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
