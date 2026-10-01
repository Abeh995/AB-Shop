<?php
/**
 * Sticky Smart Studio & Live Customer Simulation Partial
 * Desktop Dual-Pane Detail Column with real-time margin gauge, quick badge chips, and cart simulation.
 */
?>
<aside class="gift-studio-card <?= $activeEditItem ? 'mode-editing' : '' ?>" id="giftStudioCard">
    <!-- Studio Header -->
    <div class="gift-studio-header">
        <div class="gift-studio-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <span id="studioTitleText"><?= $activeEditItem ? 'ویرایش: ' . e($activeEditItem['name']) : 'افزودن قلم هدیه یا جانبی جدید' ?></span>
        </div>
        <div class="gift-studio-header-actions">
            <span class="gift-mode-badge" id="studioModeBadge"><?= $activeEditItem ? 'حالت ویرایش' : 'قلم جدید' ?></span>
            <button type="button" class="gift-btn-reset-mode" id="studioResetBtn" onclick="resetStudioToCreate()" title="بازگشت به حالت ثبت قلم جدید" style="display: <?= $activeEditItem ? 'inline-block' : 'none' ?>;">
                ✕ انصراف
            </button>
        </div>
    </div>

    <!-- Studio Form -->
    <form method="post" action="gift_items.php" enctype="multipart/form-data" id="giftStudioForm" class="gift-studio-body">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" id="studioItemId" value="<?= (int)($activeEditItem['id'] ?? 0) ?>">
        <input type="hidden" name="sort_order" id="studioSortOrder" value="<?= (int)($activeEditItem['sort_order'] ?? 0) ?>">

        <!-- Image Dropzone & Preview -->
        <div class="gift-form-group">
            <label class="gift-form-label">
                <span>تصویر قلم کالا</span>
                <span class="label-hint">فرمت WebP خودکار</span>
            </label>
            <div class="gift-dropzone" onclick="document.getElementById('studioImageInput').click()">
                <div class="gift-preview-container">
                    <img src="<?= !empty($activeEditItem['image']) ? UPLOAD_URL . e($activeEditItem['image']) : '/assets/img/placeholder-sock.svg' ?>" id="studioPreviewImg" class="gift-preview-img" alt="پیش‌نمایش تصویر">
                    <div class="gift-dropzone-info">
                        <div class="dropzone-text-primary">کلیک جهت انتخاب تصویر</div>
                        <div class="dropzone-text-sub">JPG، PNG، WEBP (حداکثر ۲ مگابایت)</div>
                    </div>
                </div>
                <input type="file" name="image" id="studioImageInput" accept="image/*,.heic,.heif" data-optimize-image="giftitem" data-max-dimension="1600" data-default-quality="0.30" style="display: none;" onchange="handleStudioImagePreview(this)">
            </div>
        </div>

        <!-- Name / Title Input -->
        <div class="gift-form-group">
            <label class="gift-form-label" for="studioNameInput">
                <span>نام قلم / عنوان نمایشی <span class="req">*</span></span>
            </label>
            <input type="text" name="name" id="studioNameInput" class="gift-form-input" placeholder="مثلاً: جعبه کادویی هاردباکس طرح رز" value="<?= e($activeEditItem['name'] ?? '') ?>" oninput="syncLiveCustomerPreview()" required>
        </div>

        <!-- Tagline / Subtitle Input -->
        <div class="gift-form-group">
            <label class="gift-form-label" for="studioTaglineInput">
                <span>معرفی کوتاه در سبد خرید (Tagline)</span>
                <span class="label-hint">ترغیب مشتری</span>
            </label>
            <input type="text" name="tagline" id="studioTaglineInput" class="gift-form-input" placeholder="مثلاً: شامل جعبه سخت، پوشال کاغذی و روبان ارگانزا" value="<?= e($activeEditItem['tagline'] ?? '') ?>" oninput="syncLiveCustomerPreview()">
        </div>

        <!-- Marketing Badge Text & Quick Chips -->
        <div class="gift-form-group">
            <label class="gift-form-label" for="studioBadgeInput">
                <span>برچسب بازاریابی کارت (Badge)</span>
                <span class="label-hint">اختیاری</span>
            </label>
            <input type="text" name="badge_text" id="studioBadgeInput" class="gift-form-input" placeholder="مثلاً: محبوب‌ترین، ویژه کادو، پیشنهاد سبد" value="<?= e($activeEditItem['badge_text'] ?? '') ?>" oninput="syncLiveCustomerPreview()">
            <div class="gift-quick-chips">
                <button type="button" class="quick-chip" onclick="applyBadgeChip('محبوب‌ترین')">محبوب‌ترین</button>
                <button type="button" class="quick-chip" onclick="applyBadgeChip('پیشنهاد ویژه')">پیشنهاد ویژه</button>
                <button type="button" class="quick-chip" onclick="applyBadgeChip('بسته‌بندی کادو')">بسته‌بندی کادو</button>
                <button type="button" class="quick-chip" onclick="applyBadgeChip('ارزش خرید بالا')">ارزش خرید بالا</button>
                <button type="button" class="quick-chip quick-chip-clear" onclick="applyBadgeChip('')">حذف برچسب</button>
            </div>
        </div>

        <!-- Role Selection Cards -->
        <div class="gift-form-group">
            <label class="gift-form-label">نقش‌ها و دسترسی‌های قلم <span class="req">*</span></label>
            <div class="gift-role-selector">
                <label class="gift-role-option">
                    <input type="checkbox" name="is_giftable" id="studioIsGiftable" <?= (!$activeEditItem || !empty($activeEditItem['is_giftable'])) ? 'checked' : '' ?>>
                    <div>
                        <div class="gift-role-option-title">قابل اهدای رایگان به سفارش توسط مدیر</div>
                        <div class="gift-role-option-desc">در صفحه جزئیات سفارش، مدیر می‌تواند این قلم را به سفارش اضافه کند.</div>
                    </div>
                </label>
                <label class="gift-role-option">
                    <input type="checkbox" name="is_post_orderable" id="studioIsPostOrderable" onchange="handlePostOrderRoleToggle(this.checked)" <?= (!empty($activeEditItem['is_post_orderable'])) ? 'checked' : '' ?>>
                    <div>
                        <div class="gift-role-option-title">قابل فروش به‌عنوان پیشنهاد در سبد خرید</div>
                        <div class="gift-role-option-desc">در صفحه سبد خرید به عنوان محصول مکمل و افزایشی به خریدار پیشنهاد می‌شود.</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Financials: Cost, Post-Order Price & Min Cart Threshold -->
        <div class="gift-pricing-grid">
            <div class="gift-form-group">
                <label class="gift-form-label" for="studioCostPrice">
                    <span>بهای تمام‌شده (تومان) <span class="req">*</span></span>
                </label>
                <input type="text" inputmode="numeric" name="cost_price" id="studioCostPrice" class="gift-form-input" placeholder="مثلاً: ۱۲,۰۰۰" value="<?= e((string)($activeEditItem['cost_price'] ?? '')) ?>" oninput="calculateStudioMargin()" required>
            </div>

            <div class="gift-form-group" id="studioPostOrderPriceGroup" style="<?= empty($activeEditItem['is_post_orderable']) ? 'opacity: 0.5; pointer-events: none;' : '' ?>">
                <label class="gift-form-label" for="studioPostOrderPrice">
                    <span>قیمت فروش در سبد (تومان)</span>
                </label>
                <input type="text" inputmode="numeric" name="post_order_price" id="studioPostOrderPrice" class="gift-form-input" placeholder="مثلاً: ۲۵,۰۰۰" value="<?= e((string)($activeEditItem['post_order_price'] ?? '')) ?>" oninput="calculateStudioMargin(); syncLiveCustomerPreview();">
            </div>
        </div>

        <!-- Smart Condition: Min Cart Total -->
        <div class="gift-form-group" id="studioMinCartGroup" style="<?= empty($activeEditItem['is_post_orderable']) ? 'opacity: 0.5; pointer-events: none;' : '' ?>">
            <label class="gift-form-label" for="studioMinCartTotal">
                <span>شرط حداقل مبلغ سبد جهت نمایش (تومان)</span>
                <span class="label-hint">۰ = برای همه سبدها</span>
            </label>
            <input type="text" inputmode="numeric" name="min_cart_total" id="studioMinCartTotal" class="gift-form-input" placeholder="مثلاً: ۲۰۰,۰۰۰ (اختیاری)" value="<?= !empty($activeEditItem['min_cart_total']) ? e((string)$activeEditItem['min_cart_total']) : '' ?>">
        </div>

        <!-- Real-time Live Margin Calculator Box with Progress Gauge -->
        <div class="gift-margin-calculator" id="studioMarginBox">
            <div class="gift-calc-top-row">
                <div class="gift-calc-item">
                    <span class="gift-calc-label">سود ناخالص هر واحد:</span>
                    <span class="gift-calc-val" id="studioUnitProfit">—</span>
                </div>
                <div class="gift-calc-item text-left">
                    <span class="gift-calc-label">حاشیه سود:</span>
                    <span class="gift-calc-pill" id="studioMarginPercent">—</span>
                </div>
            </div>
            <!-- Dynamic Margin Progress Bar -->
            <div class="margin-gauge-track">
                <div class="margin-gauge-fill" id="studioMarginBar" style="width: 0%;"></div>
            </div>
        </div>

        <!-- Stock & Active Switch -->
        <div class="gift-stock-active-grid">
            <div class="gift-form-group">
                <label class="gift-form-label" for="studioStock">
                    <span>موجودی انبار <span class="req">*</span></span>
                </label>
                <input type="number" name="stock" id="studioStock" class="gift-form-input" min="0" value="<?= e((string)($activeEditItem['stock'] ?? 0)) ?>" required>
            </div>

            <div class="gift-form-group">
                <label class="gift-active-checkbox-wrap">
                    <input type="checkbox" name="is_active" id="studioIsActive" <?= (!$activeEditItem || !empty($activeEditItem['is_active'])) ? 'checked' : '' ?>>
                    <span>وضعیت قلم فعال</span>
                </label>
            </div>
        </div>

        <!-- =============================================================== -->
        <!-- LIVE CUSTOMER CART SIMULATION (شبیه‌ساز زنده نمای سبد خرید مشتری)   -->
        <!-- =============================================================== -->
        <div class="gift-customer-simulation-wrap" id="customerSimulationSection">
            <div class="simulation-header">
                <span class="simulation-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    پیش‌نمایش زنده در سبد خرید مشتری
                </span>
                <span class="simulation-badge">Live Preview</span>
            </div>
            <div class="simulation-canvas">
                <div class="sim-cart-card">
                    <span class="sim-card-badge" id="simBadge" style="display: none;">پیشنهاد ویژه</span>
                    <div class="sim-card-thumb">
                        <img src="/assets/img/placeholder-sock.svg" id="simThumb" alt="پیش‌نمایش">
                    </div>
                    <div class="sim-card-body">
                        <div class="sim-card-title" id="simTitle">عنوان قلم کالا</div>
                        <div class="sim-card-tagline" id="simTagline">معرفی کوتاه قلم در سبد خرید</div>
                        <div class="sim-card-price" id="simPrice">۲۵,۰۰۰ تومان</div>
                        <div class="sim-card-btn">+ افزودن به سبد</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Studio Footer Actions -->
        <div class="gift-studio-footer">
            <button type="submit" class="gift-btn-submit" id="studioSubmitBtn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <span id="studioSubmitText"><?= $activeEditItem ? 'ذخیره تغییرات قلم' : 'ثبت قلم جدید در کاتالوگ' ?></span>
            </button>
        </div>
    </form>
</aside>
