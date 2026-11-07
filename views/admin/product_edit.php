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
                        <input class="form-control" type="text" id="productNameInput" name="name" value="<?= e($product['name'] ?? '') ?>" placeholder="مثلاً: جوراب ساق‌دار نخی اعلا طرح آلیس" required>
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

            <!-- 2. Google Merchant & Physical Entity Specs Card -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    <h3 class="admin-card-title">اطلاعات تجاری و طبقه‌بندی هوشمند (Google Merchant & Schema)</h3>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>بارکد بین‌المللی (GTIN / EAN-13)</label>
                        <input class="form-control" type="text" name="barcode" dir="ltr" inputmode="numeric" value="<?= e($product['barcode'] ?? '') ?>" placeholder="مثلاً: 6260123456789">
                        <p class="form-hint">کد بین‌المللی جهت نمایش در Google Shopping و موتورهای هوش مصنوعی.</p>
                    </div>
                    <div class="form-group">
                        <label>پارت‌نامبر سازنده (MPN)</label>
                        <input class="form-control" type="text" name="mpn" dir="ltr" value="<?= e($product['mpn'] ?? '') ?>" placeholder="مثلاً: ABS-CRW-01">
                        <p class="form-hint">شناسه تولیدی کارگاه برای قطعه یا مدل جوراب.</p>
                    </div>
                    <div class="form-group">
                        <label>برند یا کارگاه تولیدی</label>
                        <input class="form-control" type="text" name="brand_name" value="<?= e($product['brand_name'] ?? '') ?>" placeholder="پیش‌فرض: <?= e(SITE_NAME) ?>">
                        <p class="form-hint">در صورت خالی بودن، نام فروشگاه به عنوان برند ثبت می‌شود.</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>گروه کاربری / جنسیت هدف</label>
                        <?php $currentGender = $product['gender'] ?? 'unisex'; ?>
                        <select class="form-control" name="gender">
                            <option value="unisex" <?= $currentGender === 'unisex' ? 'selected' : '' ?>>مشترک / همگانی (Unisex)</option>
                            <option value="men" <?= $currentGender === 'men' ? 'selected' : '' ?>>مردانه (Men)</option>
                            <option value="women" <?= $currentGender === 'women' ? 'selected' : '' ?>>زنانه (Women)</option>
                            <option value="kids" <?= $currentGender === 'kids' ? 'selected' : '' ?>>کودک و بچگانه (Kids)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ارتفاع ساق جوراب (Cuff Height)</label>
                        <?php $currentHeight = $product['cuff_height'] ?? 'crew'; ?>
                        <select class="form-control" name="cuff_height">
                            <option value="crew" <?= $currentHeight === 'crew' ? 'selected' : '' ?>>ساق‌دار (Crew)</option>
                            <option value="ankle" <?= $currentHeight === 'ankle' ? 'selected' : '' ?>>مچی (Ankle)</option>
                            <option value="no-show" <?= $currentHeight === 'no-show' ? 'selected' : '' ?>>کالج / بدون ساق (No-show)</option>
                            <option value="quarter" <?= $currentHeight === 'quarter' ? 'selected' : '' ?>>نیم‌ساق (Quarter)</option>
                            <option value="knee-high" <?= $currentHeight === 'knee-high' ? 'selected' : '' ?>>بالای زانو (Knee-high)</option>
                            <option value="tights" <?= $currentHeight === 'tights' ? 'selected' : '' ?>>جوراب شلواری (Tights)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>طرح ظاهری (Pattern)</label>
                        <?php $currentPattern = $product['pattern'] ?? 'solid'; ?>
                        <select class="form-control" name="pattern">
                            <option value="solid" <?= $currentPattern === 'solid' ? 'selected' : '' ?>>ساده / تک‌رنگ (Solid)</option>
                            <option value="striped" <?= $currentPattern === 'striped' ? 'selected' : '' ?>>راه‌راه (Striped)</option>
                            <option value="graphic" <?= $currentPattern === 'graphic' ? 'selected' : '' ?>>گرافیکی / تایپوگرافی (Graphic)</option>
                            <option value="novelty" <?= $currentPattern === 'novelty' ? 'selected' : '' ?>>فانتزی و کارتونی (Novelty)</option>
                            <option value="geometric" <?= $currentPattern === 'geometric' ? 'selected' : '' ?>>هندسی و لوزی (Geometric)</option>
                            <option value="floral" <?= $currentPattern === 'floral' ? 'selected' : '' ?>>گل‌دار و بوته‌جقه (Floral)</option>
                            <option value="plaid" <?= $currentPattern === 'plaid' ? 'selected' : '' ?>>چهارخانه و شطرنجی (Plaid)</option>
                        </select>
                    </div>
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

            <!-- 4. Tags & SEO Taxonomy Card -->
            <div class="admin-card tag-tokenizer-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h3 style="margin:0; font-size:1.05rem; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        برچسب‌ها و تگ‌های سئو
                    </h3>
                    <a href="/admin/tags.php" target="_blank" style="font-size:0.8rem; color:var(--prod-brand-primary, #C46C46); text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-weight:700;">
                        مدیریت برچسب‌ها ↗
                    </a>
                </div>

                <div class="tag-tokenizer-container ab-autocomplete-wrap" id="tagTokenizer"
                     data-ab-autocomplete="tags"
                     data-ab-multiple="true"
                     data-ab-allow-new="true"
                     data-ab-input-name="tag_ids[]"
                     data-ab-new-input-name="new_tags">
                    <div class="tag-tokens-box ab-tokens-box" id="tagTokensBox">
                        <div class="tag-tokens-list ab-tokens-list" id="tagTokensList"></div>
                        <input type="text" class="tag-input-field ab-autocomplete-input" id="tagInputField" placeholder="تایپ یا جستجوی برچسب (Enter یا کاما)..." autocomplete="off">
                    </div>

                    <!-- Autocomplete Floating Dropdown -->
                    <div class="tag-autocomplete-dropdown ab-autocomplete-dropdown" id="tagDropdown" style="display: none;"></div>

                    <!-- Hidden inputs container for form submission -->
                    <div id="tagHiddenInputs" class="ab-autocomplete-hidden"></div>
                </div>

                <!-- Popular Suggestions Cloud -->
                <?php if (!empty($allTags)): ?>
                <div class="tag-popular-wrap" style="margin-top: 12px;">
                    <span class="tag-popular-title">پیشنهادات پرتکرار:</span>
                    <div class="tag-popular-chips" id="tagPopularChips">
                        <?php 
                        $popularPreview = array_slice($allTags, 0, 10);
                        foreach ($popularPreview as $ptag): ?>
                            <button type="button" class="tag-quick-chip" data-id="<?= (int)$ptag['id'] ?>" data-name="<?= e($ptag['name']) ?>">
                                + <?= e($ptag['name']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <small style="display:block; color:var(--text-muted, #64748b); font-size:0.78rem; margin-top:8px;">
                    💡 برای ایجاد برچسب تازه، عبارت دلخواه را تایپ کرده و کلید <strong>Enter</strong> یا <strong>کاما</strong> را بزنید.
                </small>
            </div>

            <!-- 5. SEO & AI Grounding Card -->
            <?php $pSpecs = seoParseJsonList($product['material_specs'] ?? null); ?>
            <div class="admin-card seo-ai-card">
                <div class="seo-card-header">
                    <h3 class="seo-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        تنظیمات سئو (SEO) و هوش مصنوعی (GEO)
                    </h3>
                    <span class="serp-badge">رتبه ۱ گوگل و AI</span>
                </div>

                <!-- Google SERP Snippet Preview Box -->
                <div class="serp-preview-box">
                    <span class="serp-preview-meta">پیش‌نمایش در نتایج گوگل (SERP Preview):</span>
                    <div class="serp-preview-content">
                        <div class="serp-url">
                            <span>ab-socks.ir</span>
                            <span class="serp-url-sub">› product › <span id="serpPreviewSlug"><?= e($product['slug'] ?? 'sock-slug') ?></span></span>
                        </div>
                        <div class="serp-title" id="serpPreviewTitle">
                            <?= e(!empty($product['seo_title']) ? $product['seo_title'] : (($product['name'] ?? 'نام محصول') . ' | ' . SITE_NAME)) ?>
                        </div>
                        <div class="serp-desc" id="serpPreviewDesc">
                            <?= e(!empty($product['seo_description']) ? $product['seo_description'] : seoCleanText($product['description'] ?? '', 140)) ?>
                        </div>
                    </div>
                </div>

                <!-- SEO Title Field -->
                <div class="ab-seo-field-wrap">
                    <div class="ab-seo-field-header">
                        <label for="seoTitleInput" class="cat-label">عنوان اختصاصی سئو (SEO Title):</label>
                        <button type="button" class="ab-seo-help-btn" data-ab-seo-help-toggle="helpSeoTitle" aria-expanded="false">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>راهنما و نمونه</span>
                        </button>
                    </div>

                    <input type="text" id="seoTitleInput" name="seo_title" class="form-control"
                           value="<?= e($product['seo_title'] ?? '') ?>"
                           placeholder="<?= e($product['name'] ?? '') ?> | <?= e(SITE_NAME) ?>"
                           data-ab-seo-counter
                           data-ab-min="30"
                           data-ab-optimal-min="45"
                           data-ab-optimal-max="60"
                           data-ab-max="70"
                           data-ab-sync-target="#serpPreviewTitle"
                           data-ab-sync-fallback="<?= e(($product['name'] ?? 'نام محصول') . ' | ' . SITE_NAME) ?>">

                    <div class="ab-seo-counter-bar">
                        <div class="ab-seo-counter"></div>
                        <small class="seo-hint">طول بهینه در سرپ گوگل: ۴۵ تا ۶۰ کاراکتر (در صورت خالی بودن، نام محصول استفاده می‌شود).</small>
                    </div>

                    <!-- Collapsible Help Card -->
                    <div class="ab-seo-help-card" id="helpSeoTitle" hidden>
                        <div class="ab-seo-help-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            چرا عنوان سئو کلیدی‌ترین فاکتور رتبه‌بندی است؟
                        </div>
                        <div class="ab-seo-help-desc">
                            عنوان سئو همان تیتر آبی‌رنگ لینک اول در نتایج گوگل است و سنگین‌ترین وزن کلمات کلیدی را برای موتورهای جستجو و هوش مصنوعی دارد.
                        </div>
                        <div class="ab-seo-help-formula">
                            💡 فرمول استاندارد: [نام محصول و مدل] + [جنس و کاربرد کلیدی] + [نام برند یا سایت]
                        </div>
                        <div class="ab-seo-help-example">
                            <div class="ab-seo-example-item">
                                <span class="ab-seo-example-badge good">✓ نمونه عالی (۵۲ حرف)</span>
                                <span>جوراب ساقدار نخی طرح گوزن | پنبه سوپر شانه شده | ای‌بی‌ساکس</span>
                            </div>
                            <div class="ab-seo-example-item">
                                <span class="ab-seo-example-badge bad">✕ نمونه نامناسب (اسپم)</span>
                                <span>جوراب جوراب جوراب خرید جوراب ارزان ساق دار بافتنی</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- URL Slug Field -->
                <div class="ab-seo-field-wrap">
                    <div class="ab-seo-field-header">
                        <label for="productSlugInput" class="cat-label">نامک صفحه (URL Slug):</label>
                        <div class="ab-seo-actions">
                            <button type="button" class="ab-slug-suggest-btn" data-ab-slug-suggest="#productNameInput" data-ab-slug-target="#productSlugInput">
                                ⚡ پیشنهاد نامک انگلیسی
                            </button>
                            <button type="button" class="ab-seo-help-btn" data-ab-seo-help-toggle="helpProductSlug" aria-expanded="false">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                <span>راهنما و نمونه</span>
                            </button>
                        </div>
                    </div>

                    <input type="text" id="productSlugInput" name="slug" class="form-control" dir="ltr"
                           value="<?= e($product['slug'] ?? '') ?>"
                           placeholder="crew-socks-cotton"
                           pattern="[a-z0-9\-]+"
                           inputmode="latin"
                           autocomplete="off"
                           spellcheck="false"
                           data-ab-slug-clean
                           data-ab-seo-counter
                           data-ab-min="3"
                           data-ab-optimal-min="5"
                           data-ab-optimal-max="50"
                           data-ab-max="75"
                           data-ab-sync-target="#serpPreviewSlug"
                           data-ab-sync-fallback="sock-slug">

                    <div class="ab-seo-counter-bar">
                        <div class="ab-seo-counter"></div>
                        <small class="seo-hint">💡 در صورت ویرایش نامک، سیستم خودکار <strong>ریدایرکت ۳۰۱</strong> ثبت می‌کند تا رتبه گوگل حفظ شود.</small>
                    </div>

                    <!-- Collapsible Help Card -->
                    <div class="ab-seo-help-card" id="helpProductSlug" hidden>
                        <div class="ab-seo-help-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            چرا اسلاگ انگلیسی برای سئو ارجحیت مطلق دارد؟
                        </div>
                        <div class="ab-seo-help-desc">
                            اسلاگ‌های فارسی هنگام کپی شدن در تلگرام و واتساپ به رشته‌های کدهای درصدی (%D8...) نامفهوم و چندصدکاراکتری تبدیل می‌شوند. استفاده از حروف کوچک انگلیسی و خط تیره ساختار لینک را خوانا و پرسرعت نگه می‌دارد.
                        </div>
                        <div class="ab-seo-help-formula">
                            💡 فرمول استاندارد: حروف کوچک انگلیسی + خط تیره (kebab-case)
                        </div>
                        <div class="ab-seo-help-example">
                            <div class="ab-seo-example-item">
                                <span class="ab-seo-example-badge good">✓ نمونه عالی</span>
                                <code>cotton-crew-socks-deer-navy</code>
                            </div>
                            <div class="ab-seo-example-item">
                                <span class="ab-seo-example-badge bad">✕ نمونه نامناسب</span>
                                <code>جوراب_ساقدار_طرح_گوزن_123</code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Meta Description Field -->
                <div class="ab-seo-field-wrap">
                    <div class="ab-seo-field-header">
                        <label for="seoDescInput" class="cat-label">توضیحات متا (Meta Description):</label>
                        <button type="button" class="ab-seo-help-btn" data-ab-seo-help-toggle="helpSeoDesc" aria-expanded="false">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>راهنما و نمونه</span>
                        </button>
                    </div>

                    <textarea id="seoDescInput" name="seo_description" class="form-control" rows="2"
                              placeholder="توضیحات جذاب و خلاصه برای جذب کلیک در گوگل و بینگ (۱۲۰-۱۶۰ حرف)..."
                              data-ab-seo-counter
                              data-ab-min="70"
                              data-ab-optimal-min="120"
                              data-ab-optimal-max="160"
                              data-ab-max="175"
                              data-ab-sync-target="#serpPreviewDesc"
                              data-ab-sync-fallback="<?= e(seoCleanText($product['description'] ?? '', 140)) ?>"><?= e($product['seo_description'] ?? '') ?></textarea>

                    <div class="ab-seo-counter-bar">
                        <div class="ab-seo-counter"></div>
                        <small class="seo-hint">طول بهینه در سرپ گوگل: ۱۲۰ تا ۱۶۰ کاراکتر (پاراگراف متقاعدکننده برای افزایش نرخ کلیک).</small>
                    </div>

                    <!-- Collapsible Help Card -->
                    <div class="ab-seo-help-card" id="helpSeoDesc" hidden>
                        <div class="ab-seo-help-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            راز نوشتن توضیحات متای برنده در نتایج جستجو
                        </div>
                        <div class="ab-seo-help-desc">
                            اگر توضیحات متا کمتر از ۱۲۰ کاراکتر باشد، گوگل آن را رد کرده و بخشی از متن تصادفی صفحه را نمایش می‌دهد. اگر بیشتر از ۱۶۰ کاراکتر باشد با «...» بریده می‌شود.
                        </div>
                        <div class="ab-seo-help-formula">
                            💡 فرمول استاندارد: [معرفی مزیت و کاربرد] + [الیاف و ویژگی متمایز] + [دعوت به خرید/ارسال سریع]
                        </div>
                        <div class="ab-seo-help-example">
                            <div class="ab-seo-example-item">
                                <span class="ab-seo-example-badge good">✓ نمونه عالی (۱۴۳ حرف)</span>
                                <span>خرید جوراب ساقدار زنانه و مردانه با بافت پنبه ارگانیک و کش ضد حساسیت. ضد تعریق با دوام بالا، ارسال فوری به سراسر کشور از ای‌بی‌ساکس.</span>
                            </div>
                            <div class="ab-seo-example-item">
                                <span class="ab-seo-example-badge bad">✕ نمونه نامناسب (بسیار کوتاه - ۳۵ حرف)</span>
                                <span>فروش انواع جوراب های مرغوب و شیک در فروشگاه ما.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Canonical URL Field -->
                <div class="ab-seo-field-wrap">
                    <div class="ab-seo-field-header">
                        <label for="canonicalUrlInput" class="cat-label">آدرس کنونیکال اختصاصی (Canonical URL - اختیاری):</label>
                        <button type="button" class="ab-seo-help-btn" data-ab-seo-help-toggle="helpCanonicalUrl" aria-expanded="false">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>راهنما</span>
                        </button>
                    </div>

                    <input type="text" id="canonicalUrlInput" name="canonical_url" class="form-control" dir="ltr" value="<?= e($product['canonical_url'] ?? '') ?>" placeholder="https://ab-socks.ir/product/main-color">
                    <small class="seo-hint">در صورت خالی بودن، به طور پیش‌فرض آدرس همین صفحه به عنوان کنونیکال ارسال می‌شود.</small>

                    <!-- Collapsible Help Card -->
                    <div class="ab-seo-help-card" id="helpCanonicalUrl" hidden>
                        <div class="ab-seo-help-title">آدرس کنونیکال چیست و چه زمانی پر شود؟</div>
                        <div class="ab-seo-help-desc">
                            کنونیکال به گوگل می‌گوید نسخه اصلی و مرجع این کالا کدام است. فقط در صورتی پر کنید که چند رنگ/مدل مختلف از این جوراب در صفحات مجزا ساخته شده و می‌خواهید اعتبار سرپ همگی روی یک صفحه خاص متمرکز شود.
                        </div>
                    </div>
                </div>

                <!-- Structured Facts for AI Grounding -->
                <div class="seo-specs-box">
                    <div class="ab-seo-field-header seo-specs-header">
                        <span class="seo-specs-title">🤖 مشخصات ساختاریافته الیاف و مدل (سیگنال‌های قطعی هوش مصنوعی):</span>
                        <button type="button" class="ab-seo-help-btn" data-ab-seo-help-toggle="helpAiSpecs" aria-expanded="false">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>راهنمای فکت‌های AI</span>
                        </button>
                    </div>

                    <!-- Collapsible Help Card for AI Specs -->
                    <div class="ab-seo-help-card" id="helpAiSpecs" hidden>
                        <div class="ab-seo-help-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            چرا مدل‌های هوش مصنوعی (ChatGPT, Perplexity, Gemini, Claude) به این فیلدها وابسته‌اند؟
                        </div>
                        <div class="ab-seo-help-desc">
                            موتورهای پاسخ هوش مصنوعی بر خلاف سرچ‌های سنتی، متن‌های تبلیغاتی کلیشه‌ای را نادیده گرفته و به دنبال «فکت‌های فنی قطعی» درباره جوراب هستند. مقادیر این بخش مستقیماً در فید <code>/llms.txt</code> و اسکیماهای گوگل مرچنت تزریق می‌شوند تا وقتی خریدار می‌پرسد <em>«جوراب ۱۰۰٪ نخی ضد حساسیت بدون درز پنجه»</em>، این محصول پیشنهاد اول چت‌بات باشد.
                        </div>
                        <div class="ab-seo-help-formula">
                            💡 راهنما: می‌توانید مقادیر را دستی بنویسید یا برای راحتی و یکدستی، روی چیپ‌های آماده زیر هر کادر کلیک کنید تا خودکار درج شوند.
                        </div>
                    </div>

                    <div class="seo-specs-grid">
                        <!-- 1. Fiber Composition -->
                        <div>
                            <label class="seo-specs-label" for="specCotton">درصد پنبه / الیاف:</label>
                            <input type="text" id="specCotton" name="material_specs[درصد پنبه]" class="form-control form-control-sm" value="<?= e($pSpecs['درصد پنبه'] ?? '۱۰۰٪ نخ‌پنبه سوپر شانه شده') ?>">
                            <?php if (!empty($specPresets['درصد پنبه'])): ?>
                            <div class="ab-quick-chips">
                                <?php foreach ($specPresets['درصد پنبه'] as $preset): ?>
                                <button type="button" class="ab-quick-chip" data-ab-quick-chip data-ab-target="#specCotton" data-ab-val="<?= e($preset['spec_value']) ?>"><?= e($preset['label']) ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- 2. Cuff Height -->
                        <div>
                            <label class="seo-specs-label" for="specHeight">ارتفاع ساق جوراب:</label>
                            <input type="text" id="specHeight" name="material_specs[ارتفاع ساق]" class="form-control form-control-sm" value="<?= e($pSpecs['ارتفاع ساق'] ?? 'ساق‌دار') ?>" placeholder="ساق‌دار / نیم‌ساق / مچی / کالج">
                            <?php if (!empty($specPresets['ارتفاع ساق'])): ?>
                            <div class="ab-quick-chips">
                                <?php foreach ($specPresets['ارتفاع ساق'] as $preset): ?>
                                <button type="button" class="ab-quick-chip" data-ab-quick-chip data-ab-target="#specHeight" data-ab-val="<?= e($preset['spec_value']) ?>"><?= e($preset['label']) ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- 3. Thickness & Season -->
                        <div>
                            <label class="seo-specs-label" for="specSeason">ضخامت و فصل مناسب:</label>
                            <input type="text" id="specSeason" name="material_specs[فصل و ضخامت]" class="form-control form-control-sm" value="<?= e($pSpecs['فصل و ضخامت'] ?? 'چهارفصل') ?>">
                            <?php if (!empty($specPresets['فصل و ضخامت'])): ?>
                            <div class="ab-quick-chips">
                                <?php foreach ($specPresets['فصل و ضخامت'] as $preset): ?>
                                <button type="button" class="ab-quick-chip" data-ab-quick-chip data-ab-target="#specSeason" data-ab-val="<?= e($preset['spec_value']) ?>"><?= e($preset['label']) ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- 4. Usage & Style -->
                        <div>
                            <label class="seo-specs-label" for="specUsage">کاربرد و استایل:</label>
                            <input type="text" id="specUsage" name="material_specs[کاربرد]" class="form-control form-control-sm" value="<?= e($pSpecs['کاربرد'] ?? 'روزمره و رسمی') ?>">
                            <?php if (!empty($specPresets['کاربرد'])): ?>
                            <div class="ab-quick-chips">
                                <?php foreach ($specPresets['کاربرد'] as $preset): ?>
                                <button type="button" class="ab-quick-chip" data-ab-quick-chip data-ab-target="#specUsage" data-ab-val="<?= e($preset['spec_value']) ?>"><?= e($preset['label']) ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- 5. Toe Seam -->
                        <div>
                            <label class="seo-specs-label" for="specToe">نوع دوخت و پنجه‌گیری:</label>
                            <input type="text" id="specToe" name="material_specs[نوع دوخت پنجه]" class="form-control form-control-sm" value="<?= e($pSpecs['نوع دوخت پنجه'] ?? 'پنجه‌گیری بدون درز (Seamless)') ?>">
                            <?php if (!empty($specPresets['نوع دوخت پنجه'])): ?>
                            <div class="ab-quick-chips">
                                <?php foreach ($specPresets['نوع دوخت پنجه'] as $preset): ?>
                                <button type="button" class="ab-quick-chip" data-ab-quick-chip data-ab-target="#specToe" data-ab-val="<?= e($preset['spec_value']) ?>"><?= e($preset['label']) ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- 6. Wash & Care -->
                        <div>
                            <label class="seo-specs-label" for="specWash">دستور شستشو و نگهداری:</label>
                            <input type="text" id="specWash" name="material_specs[دستور شستشو]" class="form-control form-control-sm" value="<?= e($pSpecs['دستور شستشو'] ?? 'آب سرد ۳۰ درجه، بدون سفیدکننده') ?>">
                            <?php if (!empty($specPresets['دستور شستشو'])): ?>
                            <div class="ab-quick-chips">
                                <?php foreach ($specPresets['دستور شستشو'] as $preset): ?>
                                <button type="button" class="ab-quick-chip" data-ab-quick-chip data-ab-target="#specWash" data-ab-val="<?= e($preset['spec_value']) ?>"><?= e($preset['label']) ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- LEFT COLUMN: Sidebar & Media (35%)             -->
        <!-- ============================================== -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- 1. Publication & Actions Box -->
            <div class="admin-card">
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
                    <a href="/product/<?= rawurlencode($product['slug']) ?>" target="_blank" style="font-size: 0.82rem; color: var(--color-primary); display: inline-flex; align-items: center; gap: 4px;">
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

<script id="productEditTagData" type="application/json"><?= json_encode(['allTags' => $allTags, 'selectedTagIds' => array_values($productTagIds)], JSON_UNESCAPED_UNICODE) ?></script>
<script src="<?= e(asset('/assets/js/admin-product-edit.js')) ?>"></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
