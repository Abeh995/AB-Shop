<?php
/**
 * Floating Bulk Action Bar for Products Catalog (views/admin/products_partials/_bulk_bar.php)
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 */
?>
<div class="prod-bulk-bar" id="prodBulkBar" role="region" aria-label="عملیات گروهی کالاها">
    <span class="prod-bulk-count" id="prodBulkCount">۰ کالا انتخاب شد</span>
    <div class="prod-bulk-btns">
        <button type="button" class="ab-btn ab-btn--sm ab-btn--ghost" id="bulkActivate">فعال‌سازی</button>
        <button type="button" class="ab-btn ab-btn--sm ab-btn--ghost" id="bulkDeactivate">غیرفعال‌سازی</button>
        <div class="ab-cluster ab-cluster--xs">
            <select id="bulkCategorySelect" class="ab-select ab-select--sm" aria-label="انتخاب دسته‌بندی مقصد">
                <option value="">انتقال به دسته...</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="ab-btn ab-btn--sm ab-btn--secondary" id="bulkCategory">انتقال</button>
        </div>
        <button type="button" class="ab-btn ab-btn--sm ab-btn--danger" id="bulkDelete">حذف گروهی</button>
    </div>
    <button type="button" class="ab-icon-btn ab-icon-btn--sm" id="bulkCancel" title="لغو انتخاب" aria-label="لغو انتخاب">✕</button>
</div>
