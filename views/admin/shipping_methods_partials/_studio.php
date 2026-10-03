<?php
/**
 * AB-Socks Logistics & Shipping Methods — Sticky Smart Studio & Live Customer Simulation
 * @var ?array $editMethod
 */
$isEditing = !empty($editMethod['id']);
$initId = $isEditing ? (int)$editMethod['id'] : 0;
$initName = $isEditing ? ($editMethod['name'] ?? '') : '';
$initDelivery = $isEditing ? ($editMethod['estimated_delivery'] ?? '') : '';
$initDesc = $isEditing ? ($editMethod['description'] ?? '') : '';
$initMatchType = $isEditing ? ($editMethod['match_type'] ?? 'province_contains') : 'province_contains';
$initMatchValue = $isEditing ? ($editMethod['match_value'] ?? '') : '';
$initCost = $isEditing ? (string)($editMethod['cost'] ?? '0') : '0';
$initActualCost = $isEditing ? (string)($editMethod['actual_cost'] ?? '') : '';
$initFreeAbove = $isEditing ? (string)($editMethod['free_above_amount'] ?? '') : '';
$initIsActive = $isEditing ? !empty($editMethod['is_active']) : true;
?>
<div class="shipping-studio-card" id="studio">
    <div class="studio-header">
        <h3 class="studio-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            <span id="studioTitleText"><?= $isEditing ? 'ویرایش روش ارسال' : 'روش ارسال جدید' ?></span>
        </h3>
        <div style="display:flex; align-items:center; gap:8px;">
            <span id="studioModePill" class="studio-mode-pill <?= $isEditing ? 'mode-edit' : 'mode-new' ?>">
                <?= $isEditing ? 'شناسه #' . toPersianDigits((string)$initId) : 'جدید' ?>
            </span>
            <button type="button" id="studioResetBtn" class="btn-reset-studio" style="<?= $isEditing ? 'display:inline-block;' : 'display:none;' ?>">
                + روش جدید
            </button>
        </div>
    </div>

    <!-- 1. Live Customer Simulation Card -->
    <div class="shipping-preview-box">
        <div class="preview-badge-label">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            <span>پیش‌نمایش در فاکتور تسویه حساب خریدار</span>
        </div>
        <div class="preview-checkout-row">
            <div>
                <span class="preview-name-part" id="previewMethodName"><?= e($initName ?: 'نام روش ارسال') ?></span>
                <span class="preview-delivery-part" id="previewDeliveryTime" style="<?= $initDelivery ? 'display:inline;' : 'display:none;' ?>">
                    <?= $initDelivery ? ' • ' . e($initDelivery) : '' ?>
                </span>
            </div>
            <span class="preview-cost-part" id="previewCostText">
                <?php if ((int)$initCost === 0): ?>
                    رایگان
                <?php elseif (!empty($initFreeAbove) && (int)$initFreeAbove > 0): ?>
                    <?= formatPrice((int)$initCost) ?> (رایگان از <?= formatPrice((int)$initFreeAbove) ?>)
                <?php else: ?>
                    <?= formatPrice((int)$initCost) ?>
                <?php endif; ?>
            </span>
        </div>
    </div>

    <!-- 2. Studio Form -->
    <form method="post" id="shippingStudioForm" action="shipping_methods.php">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" id="studioMethodId" value="<?= $initId ?>">

        <div class="form-group" style="margin-bottom:14px;">
            <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">نام روش ارسال <span style="color:var(--ship-danger);">*</span></label>
            <input class="form-control" type="text" name="name" id="studioName" value="<?= e($initName) ?>" required placeholder="مثلاً: پست پیشتاز سراسری">
        </div>

        <div class="form-group" style="margin-bottom:14px;">
            <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">زمان تخمینی تحویل (نمایش به مشتری)</label>
            <input class="form-control" type="text" name="estimated_delivery" id="studioDelivery" value="<?= e($initDelivery) ?>" placeholder="مثلاً: ۲ الی ۴ روز کاری">
            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top:6px;">
                <button type="button" class="btn btn-sm btn-outline" style="font-size:0.72rem; padding:2px 8px;" onclick="document.getElementById('studioDelivery').value='۲ الی ۴ روز کاری (پست پیشتاز)'; document.getElementById('studioDelivery').dispatchEvent(new Event('input'));">۲ تا ۴ روز کاری</button>
                <button type="button" class="btn btn-sm btn-outline" style="font-size:0.72rem; padding:2px 8px;" onclick="document.getElementById('studioDelivery').value='تحویل همان‌روز یا ۲۴ ساعته'; document.getElementById('studioDelivery').dispatchEvent(new Event('input'));">تحویل همان‌روز (پیک)</button>
                <button type="button" class="btn btn-sm btn-outline" style="font-size:0.72rem; padding:2px 8px;" onclick="document.getElementById('studioDelivery').value='۱ الی ۲ روز کاری'; document.getElementById('studioDelivery').dispatchEvent(new Event('input'));">۱ تا ۲ روز کاری</button>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:14px;">
            <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">نوع تطبیق محدوده</label>
            <select class="form-control" name="match_type" id="studioMatchType">
                <option value="province_contains" <?= $initMatchType === 'province_contains' ? 'selected' : '' ?>>فقط وقتی استان مشتری شامل متن خاصی باشد</option>
                <option value="default" <?= $initMatchType === 'default' ? 'selected' : '' ?>>سایر موارد (سراسر کشور / پیش‌فرض fallback)</option>
            </select>
        </div>

        <div class="form-group" id="studioMatchValueGroup" style="margin-bottom:14px; <?= $initMatchType !== 'province_contains' ? 'display:none;' : '' ?>">
            <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">متن استان جهت تطبیق <span style="color:var(--ship-danger);">*</span></label>
            <input class="form-control" type="text" name="match_value" id="studioMatchValue" value="<?= e($initMatchValue) ?>" placeholder="مثلاً: تهران یا اصفهان">
        </div>

        <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
            <div class="form-group" style="margin:0;">
                <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">کرایه مشتری (تومان) <span style="color:var(--ship-danger);">*</span></label>
                <input class="form-control" type="text" inputmode="numeric" name="cost" id="studioCost" value="<?= e($initCost) ?>" required placeholder="0">
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">بهای واقعی پست/پیک (تومان)</label>
                <input class="form-control" type="text" inputmode="numeric" name="actual_cost" id="studioActualCost" value="<?= e($initActualCost) ?>" placeholder="هزینه قبض پست">
            </div>
        </div>

        <div class="form-group" style="margin-bottom:14px;">
            <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">ارسال رایگان از این مبلغ خرید به بالا (اختیاری)</label>
            <input class="form-control" type="text" inputmode="numeric" name="free_above_amount" id="studioFreeAbove" value="<?= e($initFreeAbove) ?>" placeholder="مثلاً: 700000">
        </div>

        <div class="form-group" style="margin-bottom:14px;">
            <label style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:4px;">یادداشت یا توضیحات داخلی (اختیاری)</label>
            <input class="form-control" type="text" name="description" id="studioDescription" value="<?= e($initDesc) ?>" placeholder="مثلاً: پیک اختصاصی برای مناطق ۲۲گانه">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; font-size:0.9rem;">
                <input type="checkbox" name="is_active" id="studioIsActive" <?= $initIsActive ? 'checked' : '' ?>>
                این روش ارسال فعال باشد و در سبد خرید اعمال شود
            </label>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" id="studioSubmitBtn" class="btn btn-primary" style="flex:1;">
                <?= $isEditing ? 'ذخیره تغییرات' : 'افزودن روش ارسال' ?>
            </button>
        </div>
    </form>
</div>
