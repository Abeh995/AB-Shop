<?php
/**
 * AB-Socks Logistics & Shipping Methods — Master Matrix Table
 * High-aesthetic data table with ergonomic priority controls, clear financial breakdown,
 * and vector SVG micro-actions.
 * View Purity: 0 SQL, 0 $_POST, pure presentation (Rule 7).
 *
 * @var array $methods
 */
?>
<div class="shipping-table-card">
    <div class="shipping-card-header">
        <h2 class="shipping-card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            <span>لیست اولویت‌بندی روش‌های ارسال</span>
        </h2>
        <span class="shipping-header-hint">
            بررسی از بالا به پایین؛ اولین تطابق برای خریدار اعمال خواهد شد.
        </span>
    </div>

    <div class="table-responsive" style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:75px; text-align:center;">اولویت</th>
                    <th>روش و زمان تحویل</th>
                    <th>محدوده پوشش</th>
                    <th>تعرفه و بهای واقعی</th>
                    <th>ارسال رایگان</th>
                    <th style="width:85px; text-align:center;">وضعیت</th>
                    <th style="width:130px; text-align:center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($methods as $i => $m): 
                $cost = (int)$m['cost'];
                $actualCost = $m['actual_cost'] !== null ? (int)$m['actual_cost'] : null;
                $diff = $actualCost !== null ? ($cost - $actualCost) : null;
            ?>
            <tr class="shipping-method-row" data-id="<?= (int)$m['id'] ?>">
                <!-- 1. Priority Stepper -->
                <td style="text-align:center;">
                    <div class="priority-stepper">
                        <form method="post" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="move">
                            <input type="hidden" name="direction" value="up">
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button type="submit" class="stepper-btn" <?= $i === 0 ? 'disabled' : '' ?> title="افزایش اولویت">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                            </button>
                        </form>
                        <span class="priority-num"><?= toPersianDigits($i + 1) ?></span>
                        <form method="post" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="move">
                            <input type="hidden" name="direction" value="down">
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button type="submit" class="stepper-btn" <?= $i === count($methods) - 1 ? 'disabled' : '' ?> title="کاهش اولویت">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                        </form>
                    </div>
                </td>

                <!-- 2. Method Name & Delivery Time -->
                <td>
                    <div class="shipping-method-name-cell">
                        <div class="method-title">
                            <?= e($m['name']) ?>
                        </div>
                        <?php if (!empty($m['estimated_delivery'])): ?>
                            <div class="delivery-pill">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span><?= e($m['estimated_delivery']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($m['description'])): ?>
                            <div class="method-desc"><?= e($m['description']) ?></div>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- 3. Coverage Scope Badge -->
                <td>
                    <?php if ($m['match_type'] === 'province_contains'): ?>
                        <span class="badge-scope badge-province">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>استان «<?= e($m['match_value']) ?>»</span>
                        </span>
                    <?php else: ?>
                        <span class="badge-scope badge-default">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            <span>سراسر کشور (پیش‌فرض)</span>
                        </span>
                    <?php endif; ?>
                </td>

                <!-- 4. Fee vs Actual Cost & Subsidy Indicator -->
                <td>
                    <div class="cost-split">
                        <span class="customer-fee <?= $cost === 0 ? 'is-free' : '' ?>">
                            <?= $cost === 0 ? 'رایگان' : formatPrice($cost) ?>
                        </span>
                        <?php if ($actualCost !== null): ?>
                            <span class="actual-cost-note">پست: <?= formatPrice($actualCost) ?></span>
                            <?php if ($diff < 0): ?>
                                <span class="subsidy-tag subsidy">−<?= formatPrice(abs($diff)) ?> یارانه</span>
                            <?php elseif ($diff > 0): ?>
                                <span class="subsidy-tag margin">+<?= formatPrice($diff) ?> سود</span>
                            <?php else: ?>
                                <span class="subsidy-tag neutral">سربه‌سر</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="actual-cost-note">بهای واقعی نامشخص</span>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- 5. Free Above Amount -->
                <td>
                    <?php if ($m['free_above_amount'] !== null && (int)$m['free_above_amount'] > 0): ?>
                        <span class="free-threshold-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                            <span>از <?= formatPrice((int)$m['free_above_amount']) ?></span>
                        </span>
                    <?php else: ?>
                        <span class="no-free-badge">بدون سقف رایگان</span>
                    <?php endif; ?>
                </td>

                <!-- 6. Quick Active Switch -->
                <td style="text-align:center;">
                    <label class="switch-label" title="تغییر وضعیت فعال/غیرفعال">
                        <input type="checkbox" class="shipping-active-toggle" data-id="<?= (int)$m['id'] ?>" <?= !empty($m['is_active']) ? 'checked' : '' ?>>
                        <span class="switch-slider"></span>
                    </label>
                </td>

                <!-- 7. Actions -->
                <td style="text-align:center;">
                    <div class="table-action-group">
                        <button type="button" class="btn-action-edit btn-edit-method" data-id="<?= (int)$m['id'] ?>" title="ویرایش در استودیو">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            <span>ویرایش</span>
                        </button>
                        <form method="post" onsubmit="return confirm('آیا از حذف این روش ارسال مطمئن هستید؟');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button type="submit" class="btn-action-delete" title="حذف روش">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$methods): ?>
            <tr>
                <td colspan="7" style="text-align:center; padding:36px 10px; color:var(--ship-muted);">
                    هیچ روش ارسالی تعریف نشده است. بدون تعریف روش ارسال، هزینه حمل سفارش‌ها صفر محاسبه خواهد شد.
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
