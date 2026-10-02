<?php
/**
 * Settings Partial: Catalog, Live Search & Price Guarantee
 */
?>
<div class="settings-tab-pane" id="pane-catalog" role="tabpanel">
    <div class="settings-card">
        <div class="settings-card-header">
            <div>
                <h3 class="settings-card-title">🔍 کاتالوگ، موتور جستجوی زنده و سیاست‌های نمایش</h3>
                <p class="settings-card-subtitle">تنظیمات پیشنهاد خودکار جستجو در هدر، برچسب‌ها، استراتژی واریانت‌ها و ضمانت قیمت</p>
            </div>
        </div>

        <form method="post" action="settings.php">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="catalog_search">
            <input type="hidden" name="tab" value="catalog">

            <div class="settings-card-body">

                <!-- 1. Live Header Search -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>⚡ موتور جستجوی زنده (Autocomplete)</span>
                    </div>

                    <label class="settings-toggle-wrap">
                        <div class="toggle-info">
                            <span class="toggle-label">پیشنهاد لحظه‌ای نتایج در نوار جستجوی بالای سایت</span>
                            <p class="toggle-desc">نمایش بلافاصله محصولات، دسته‌ها و قیمت‌ها همزمان با تایپ کاربر در کادر جستجو.</p>
                        </div>
                        <div class="toggle-switch">
                            <input type="checkbox" name="search_live_enabled" value="1" <?= ($searchLiveEnabled ?? true) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </div>
                    </label>

                    <div class="settings-grid-2" style="margin-top:14px;">
                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">حداقل حروف برای شروع جستجو</label>
                            <div class="settings-input-group">
                                <input class="form-control mono-num" type="number" name="search_min_chars" min="1" max="5" value="<?= (int)($searchMinChars ?? 2) ?>">
                                <span class="settings-input-addon addon-suffix">حرف</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">سقف نتایج پیشنهادی در منو</label>
                            <div class="settings-input-group">
                                <input class="form-control mono-num" type="number" name="search_suggest_limit" min="2" max="20" value="<?= (int)($searchSuggestLimit ?? 6) ?>">
                                <span class="settings-input-addon addon-suffix">مورد</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:14px; background:#FFFFFF; padding:14px; border:1px solid var(--set-border); border-radius:var(--set-radius-sm);">
                        <span style="font-size:.82rem; font-weight:800; color:var(--set-text-main); display:block; margin-bottom:8px;">دامنه‌های تحت پوشش جستجو:</span>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <label style="display:flex; align-items:center; gap:8px; font-size:.83rem; cursor:pointer;">
                                <input type="checkbox" name="search_scope_name" value="1" <?= ($searchScopeName ?? true) ? 'checked' : '' ?>>
                                <span>جستجو در عنوان و نام کالا</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; font-size:.83rem; cursor:pointer;">
                                <input type="checkbox" name="search_scope_description" value="1" <?= ($searchScopeDescription ?? true) ? 'checked' : '' ?>>
                                <span>جستجو در متن توضیحات و ویژگی‌های فنی جوراب</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; font-size:.83rem; cursor:pointer;">
                                <input type="checkbox" name="search_include_categories" value="1" <?= ($searchIncludeCategories ?? true) ? 'checked' : '' ?>>
                                <span>پیشنهاد دسته‌بندی‌های مرتبط در نتایج جستجو</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 2. Variant Strategy & Product Tags -->
                <div class="settings-grid-2">
                    <div class="settings-fieldset">
                        <div class="fieldset-title">
                            <span>🎯 استراتژی انتخاب واریانت پیش‌فرض</span>
                        </div>
                        <div class="form-group">
                            <label style="font-size:.82rem; font-weight:700;">نحوه انتخاب واریانت (سایز/رنگ) در صفحه محصول:</label>
                            <select class="form-control" name="default_variant_strategy" style="font-size:.85rem;">
                                <option value="highest_stock" <?= ($defaultVariantStrategy ?? 'highest_stock') === 'highest_stock' ? 'selected' : '' ?>>بیشترین موجودی انبار (پیش‌فرض)</option>
                                <option value="lowest_stock" <?= ($defaultVariantStrategy ?? '') === 'lowest_stock' ? 'selected' : '' ?>>کمترین موجودی انبار (تخلیه انبار)</option>
                                <option value="first_created" <?= ($defaultVariantStrategy ?? '') === 'first_created' ? 'selected' : '' ?>>اولین واریانت تعریف‌شده</option>
                            </select>
                        </div>
                        <p class="form-helper">روی تمام محصولاتی که در ویرایش آن‌ها گزینه «پیروی از استراتژی سراسری» انتخاب شده باشد اعمال می‌شود.</p>

                        <hr style="margin:16px 0; border:0; border-top:1px solid var(--set-border);">

                        <label class="settings-toggle-wrap" style="margin-bottom:0;">
                            <div class="toggle-info">
                                <span class="toggle-label">نمایش برچسب‌های محصول</span>
                                <p class="toggle-desc">نمایش تگ‌های کاتالوگ در صفحه معرفی محصول.</p>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="show_product_tags" value="1" <?= ($showProductTags ?? true) ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </div>
                        </label>
                    </div>

                    <!-- 3. Price Guarantee -->
                    <div class="settings-fieldset">
                        <div class="fieldset-title">
                            <span>🛡️ ضمانت ثبات قیمت سبد خرید</span>
                        </div>
                        <label class="settings-toggle-wrap">
                            <div class="toggle-info">
                                <span class="toggle-label">فعال‌سازی ضمانت قیمت</span>
                                <p class="toggle-desc">تضمین حفظ قیمت اقلام سبد خرید برای کاربران واردشده حتی در صورت افزایش قیمت کالا.</p>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="price_guarantee_enabled" value="1" <?= ($priceGuaranteeEnabled ?? true) ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </div>
                        </label>

                        <div class="form-group" style="margin-top:14px;">
                            <label style="font-size:.82rem; font-weight:700;">مدت زمان ضمانت قیمت:</label>
                            <div class="settings-input-group">
                                <input class="form-control mono-num" type="number" name="price_guarantee_days" min="1" max="90" value="<?= (int)($priceGuaranteeDays ?? 7) ?>">
                                <span class="settings-input-addon addon-suffix">روز</span>
                            </div>
                            <p class="form-helper">پس از این مدت، در صورت عدم نهایی‌سازی سفارش، قیمت سبد با قیمت روز کالا به‌روز می‌شود.</p>
                        </div>
                    </div>
                </div>

                <div class="settings-form-actions">
                    <button type="submit" class="btn-set-save">
                        <span>ذخیره تنظیمات کاتالوگ و جستجو</span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
