<?php
/**
 * AB-Socks Logistics & Shipping Methods — Master Matrix Table
 * @var array $methods
 */
?>
<div class="shipping-table-card">
    <div class="shipping-card-header">
        <h2 class="shipping-card-title">
            <span>📋</span> لیست اولویت‌بندی روش‌های ارسال
        </h2>
        <span style="font-size:0.8rem; color:var(--ship-muted);">
            روش‌ها به‌ترتیب از بالا بررسی می‌شوند؛ اولین مورد مطابق اعمال خواهد شد.
        </span>
    </div>

    <div class="table-responsive" style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:70px; text-align:center;">اولویت</th>
                    <th>روش و زمان تحویل</th>
                    <th>محدوده پوشش</th>
                    <th>تعرفه و بهای واقعی</th>
                    <th>ارسال رایگان</th>
                    <th style="width:75px; text-align:center;">وضعیت</th>
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
                <!-- 1. Priority Up/Down -->
                <td style="text-align:center;">
                    <div class="admin-actions" style="justify-content:center; gap:2px;">
                        <form method="post" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="move">
                            <input type="hidden" name="direction" value="up">
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline" style="padding:2px 6px;" <?= $i === 0 ? 'disabled' : '' ?> title="افزایش اولویت">▲</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="move">
                            <input type="hidden" name="direction" value="down">
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline" style="padding:2px 6px;" <?= $i === count($methods) - 1 ? 'disabled' : '' ?> title="کاهش اولویت">▼</button>
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
                                <span>⏱️</span> <?= e($m['estimated_delivery']) ?>
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
                            <span>🏢</span> استان «<?= e($m['match_value']) ?>»
                        </span>
                    <?php else: ?>
                        <span class="badge-scope badge-default">
                            <span>🌐</span> سراسر کشور (پیش‌فرض)
                        </span>
                    <?php endif; ?>
                </td>

                <!-- 4. Fee vs Actual Cost & Subsidy Indicator -->
                <td>
                    <div class="cost-split">
                        <span class="customer-fee"><?= $cost === 0 ? 'رایگان' : formatPrice($cost) ?></span>
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
                            <span class="actual-cost-note" style="color:var(--ship-muted);">هزینه واقعی ثبت نشده</span>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- 5. Free Above Amount -->
                <td>
                    <?php if ($m['free_above_amount'] !== null && (int)$m['free_above_amount'] > 0): ?>
                        <span class="free-threshold-badge">
                            <span>🎁</span> از <?= formatPrice((int)$m['free_above_amount']) ?>
                        </span>
                    <?php else: ?>
                        <span style="color:var(--ship-muted); font-size:0.85rem;">—</span>
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
                    <div class="admin-actions" style="justify-content:center;">
                        <button type="button" class="btn btn-sm btn-outline btn-edit-method" data-id="<?= (int)$m['id'] ?>" title="ویرایش در استودیو">
                            ویرایش
                        </button>
                        <form method="post" onsubmit="return confirm('آیا از حذف این روش ارسال مطمئن هستید؟');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" style="padding:4px 8px;" title="حذف روش">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$methods): ?>
            <tr>
                <td colspan="7" style="text-align:center; padding:30px 10px; color:var(--ship-muted);">
                    هیچ روش ارسالی تعریف نشده است. بدون تعریف روش ارسال، هزینه حمل سفارش‌ها صفر محاسبه خواهد شد.
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
