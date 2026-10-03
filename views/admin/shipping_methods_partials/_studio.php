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
            <span>⚙️</span> <span id="studioTitleText"><?= $isEditing ? 'ویرایش روش ارسال' : 'روش ارسال جدید' ?></span>
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
            <span>👁️</span> پیش‌نمایش در فاکتور تسویه حساب خریدار
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
