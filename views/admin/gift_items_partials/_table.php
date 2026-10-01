<?php
/**
 * Master Catalog Matrix Table Partial for Gift Items Workstation
 * Desktop Dual-Pane Master Column with inline stock popover, drag handles, and hover zoom.
 */
?>
<div class="gift-master-card">

    <!-- Filter Toolbar -->
    <div class="gift-toolbar">
        <div class="gift-toolbar-top">
            <!-- Live Search -->
            <div class="gift-search-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="gift-search-input" id="giftLiveSearch" placeholder="جستجوی بلادرنگ در عنوان، تگ یا نشان کالا..." value="<?= e($search) ?>" autocomplete="off">
                <button type="button" class="gift-search-clear" id="giftSearchClear" title="پاک‌کردن جستجو" style="display: <?= $search !== '' ? 'block' : 'none' ?>;">✕</button>
            </div>

            <!-- Role Filter Chips -->
            <div class="gift-filter-chips">
                <a href="gift_items.php?role=all" class="gift-chip-btn <?= $roleFilter === 'all' ? 'is-active' : '' ?>">
                    همه اقلام
                    <span class="chip-count"><?= toPersianDigits((string)$metrics['total_items']) ?></span>
                </a>
                <a href="gift_items.php?role=post_orderable" class="gift-chip-btn <?= $roleFilter === 'post_orderable' ? 'is-active' : '' ?>">
                    پیشنهاد سبد
                    <span class="chip-count"><?= toPersianDigits((string)$metrics['post_orderable_count']) ?></span>
                </a>
                <a href="gift_items.php?role=giftable" class="gift-chip-btn <?= $roleFilter === 'giftable' ? 'is-active' : '' ?>">
                    هدایای ادمین
                    <span class="chip-count"><?= toPersianDigits((string)$metrics['giftable_count']) ?></span>
                </a>
                <a href="gift_items.php?role=hybrid" class="gift-chip-btn <?= $roleFilter === 'hybrid' ? 'is-active' : '' ?>">
                    دوکاربره
                    <span class="chip-count"><?= toPersianDigits((string)$metrics['hybrid_count']) ?></span>
                </a>
                <a href="gift_items.php?role=low_stock" class="gift-chip-btn <?= $roleFilter === 'low_stock' ? 'is-active' : '' ?>">
                    کم‌موجودی
                    <span class="chip-count"><?= toPersianDigits((string)$metrics['low_stock_count']) ?></span>
                </a>
                <a href="gift_items.php?role=inactive" class="gift-chip-btn <?= $roleFilter === 'inactive' ? 'is-active' : '' ?>">
                    غیرفعال‌ها
                    <span class="chip-count"><?= toPersianDigits((string)$metrics['inactive_items']) ?></span>
                </a>
            </div>
        </div>

        <div class="gift-toolbar-hint">
            <span class="hint-icon">💡</span>
            <span>با کشیدن آیکون دستگیره <strong style="font-family: monospace;">⠿</strong> در ابتدای هر ردیف، اولویت نمایش اقلام در سبد خرید مشتری را تعیین و ذخیره کنید.</span>
        </div>
    </div>

    <!-- Master Table -->
    <div class="gift-table-container">
        <table class="gift-table" id="giftItemsTable">
            <thead>
                <tr>
                    <th style="width: 32px; text-align: center;">ترتیب</th>
                    <th style="width: 54px; text-align: center;">تصویر</th>
                    <th>نام، معرفی و نشان کالا</th>
                    <th style="width: 130px;">نقش‌های فعال</th>
                    <th style="width: 105px;">بهای تمام‌شده</th>
                    <th style="width: 135px;">قیمت فروش سبد</th>
                    <th style="width: 95px;">حاشیه سود</th>
                    <th style="width: 85px; text-align: center;">موجودی</th>
                    <th style="width: 120px;">آمار عملکرد</th>
                    <th style="width: 65px; text-align: center;">وضعیت</th>
                    <th style="width: 100px; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody id="giftTableBody">
                <?php foreach ($items as $it):
                    $img = !empty($it['image']) ? UPLOAD_URL . e($it['image']) : '/assets/img/placeholder-sock.svg';
                    $stockVal = (int) $it['stock'];
                    $salePrice = $it['post_order_price'] !== null ? (int)$it['post_order_price'] : null;
                    $margin = $it['margin_percent'];
                    $isSelected = ($activeEditItem && (int)$activeEditItem['id'] === (int)$it['id']);
                    $searchData = mb_strtolower($it['name'] . ' ' . ($it['tagline'] ?? '') . ' ' . ($it['badge_text'] ?? ''));
                ?>
                <tr id="row-<?= (int)$it['id'] ?>"
                    class="<?= $isSelected ? 'is-selected' : '' ?>"
                    data-id="<?= (int)$it['id'] ?>"
                    data-name="<?= e($searchData) ?>"
                    draggable="true">
                    
                    <!-- Drag Handle -->
                    <td class="gift-drag-handle-cell" title="جهت تغییر ترتیب بکشید">
                        <span class="gift-drag-handle">⠿</span>
                    </td>

                    <!-- Thumbnail with Hover Zoom -->
                    <td style="text-align: center;">
                        <div class="gift-thumb-wrap">
                            <img src="<?= $img ?>" alt="<?= e($it['name']) ?>" class="gift-thumb-img" loading="lazy">
                            <div class="gift-thumb-zoom-card">
                                <img src="<?= $img ?>" alt="<?= e($it['name']) ?>">
                                <div class="zoom-title"><?= e($it['name']) ?></div>
                            </div>
                        </div>
                    </td>

                    <!-- Title, Tagline & Badge -->
                    <td>
                        <div class="gift-item-title-row">
                            <span class="gift-item-title"><?= e($it['name']) ?></span>
                            <?php if (!empty($it['badge_text'])): ?>
                                <span class="gift-badge-tag"><?= e($it['badge_text']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($it['tagline'])): ?>
                            <div class="gift-item-tagline"><?= e($it['tagline']) ?></div>
                        <?php endif; ?>
                        <div class="gift-item-id-badge">شناسه: #<?= (int)$it['id'] ?> | اولویت: <?= toPersianDigits((string)$it['sort_order']) ?></div>
                    </td>

                    <!-- Roles -->
                    <td>
                        <div class="gift-role-tags">
                            <?php if (!empty($it['is_giftable'])): ?>
                                <span class="gift-role-pill gift-role-giftable" title="قابل اهدا توسط ادمین در صفحه جزئیات سفارش">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/></svg>
                                    هدیه ادمین
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($it['is_post_orderable'])): ?>
                                <span class="gift-role-pill gift-role-postorder" title="پیشنهاد پولی مکمل در سبد خرید مشتری">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                    فروش در سبد
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Cost Price -->
                    <td>
                        <span class="gift-price-cost"><?= formatPrice((int)$it['cost_price']) ?></span>
                    </td>

                    <!-- Post-Order Sale Price & Min Cart Threshold -->
                    <td>
                        <?php if ($salePrice !== null): ?>
                            <span class="gift-price-sale"><?= formatPrice($salePrice) ?></span>
                            <?php if (!empty($it['min_cart_total'])): ?>
                                <div class="gift-threshold-pill" title="شرط نمایش: ارزش سبد حداقل <?= formatPrice((int)$it['min_cart_total']) ?>">
                                    سبد ≥ <?= toPersianDigits((string)round($it['min_cart_total'] / 1000)) ?>ه تومان
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: var(--gift-text-muted);">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Margin % -->
                    <td>
                        <?php if ($margin !== null):
                            $marginClass = ($margin >= 40) ? 'gift-margin-high' : (($margin >= 20) ? 'gift-margin-mid' : 'gift-margin-low');
                        ?>
                            <span class="gift-margin-badge <?= $marginClass ?>" title="سود هر واحد: <?= formatPrice((int)$it['profit_per_unit']) ?>">
                                <?= toPersianDigits((string)$margin) ?>٪
                            </span>
                        <?php else: ?>
                            <span style="color: var(--gift-text-muted); font-size: 0.78rem;">رایگان</span>
                        <?php endif; ?>
                    </td>

                    <!-- Stock with Inline Stepper Popover -->
                    <td style="text-align: center; position: relative;">
                        <?php
                        $stockClass = ($stockVal === 0) ? 'gift-stock-out' : (($stockVal <= 5) ? 'gift-stock-low' : 'gift-stock-ok');
                        ?>
                        <button type="button"
                                class="gift-stock-badge <?= $stockClass ?>"
                                onclick="openInlineStockPopover(event, <?= (int)$it['id'] ?>, <?= $stockVal ?>, '<?= e(addslashes($it['name'])) ?>')"
                                title="کلیک جهت تنظیم سریع موجودی انبار">
                            <?= ($stockVal === 0) ? 'ناموجود' : toPersianDigits((string)$stockVal) ?>
                        </button>
                    </td>

                    <!-- Performance Stats -->
                    <td>
                        <div class="gift-perf-wrap">
                            <?php if (!empty($it['sold_units'])): ?>
                                <div class="gift-perf-line">
                                    <span>فروش:</span>
                                    <span class="gift-perf-val text-purple font-bold"><?= toPersianDigits((string)$it['sold_units']) ?></span>
                                    <span style="font-size: 0.68rem; color: var(--gift-text-muted);">(<?= toPersianDigits((string)round($it['gross_profit'] / 1000)) ?>ه سود)</span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($it['gifted_units'])): ?>
                                <div class="gift-perf-line">
                                    <span>اهدا:</span>
                                    <span class="gift-perf-val"><?= toPersianDigits((string)$it['gifted_units']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (empty($it['gifted_units']) && empty($it['sold_units'])): ?>
                                <span style="color: var(--gift-text-muted); font-size: 0.72rem;">بدون سفارش</span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Status Toggle Switch -->
                    <td style="text-align: center;">
                        <label class="gift-switch" title="فعال یا غیرفعال کردن آیتم">
                            <input type="checkbox" onchange="toggleItemActive(<?= (int)$it['id'] ?>, this)" <?= !empty($it['is_active']) ? 'checked' : '' ?>>
                            <span class="gift-slider"></span>
                        </label>
                    </td>

                    <!-- Actions -->
                    <td style="text-align: center;">
                        <div class="gift-action-group">
                            <button type="button" class="gift-action-btn" onclick="loadItemIntoStudio(<?= (int)$it['id'] ?>)" title="ویرایش قلم در استودیو">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                ویرایش
                            </button>
                            <form method="post" action="gift_items.php" onsubmit="return confirm('آیا از حذف قلم «<?= e($it['name']) ?>» اطمینان دارید؟');" style="display:inline; margin:0;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                                <button type="submit" class="gift-action-btn gift-action-danger" title="حذف قلم">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($items)): ?>
                    <tr id="noResultsRow">
                        <td colspan="11" style="text-align: center; padding: 48px; color: var(--gift-text-muted);">
                            هیچ آیتمی با شرایط انتخابی یافت نشد.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Inline Quick Stock Popover Modal/Popover -->
<div class="gift-stock-popover" id="giftStockPopover" style="display: none;">
    <div class="popover-arrow"></div>
    <div class="popover-header">
        <span class="popover-title" id="stockPopoverTitle">تنظیم موجودی انبار</span>
        <button type="button" class="popover-close-btn" onclick="closeInlineStockPopover()">✕</button>
    </div>
    <div class="popover-body">
        <div class="popover-stepper-wrap">
            <button type="button" class="stepper-btn stepper-dec" onclick="stepStockPopover(-1)">−</button>
            <input type="number" id="stockPopoverInput" class="stepper-input" min="0" value="0">
            <button type="button" class="stepper-btn stepper-inc" onclick="stepStockPopover(1)">+</button>
        </div>
    </div>
    <div class="popover-footer">
        <button type="button" class="btn-popover-cancel" onclick="closeInlineStockPopover()">انصراف (Esc)</button>
        <button type="button" class="btn-popover-save" id="stockPopoverSaveBtn" onclick="saveInlineStockPopover()">ذخیره (Enter)</button>
    </div>
</div>
