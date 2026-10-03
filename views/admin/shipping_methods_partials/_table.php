<?php
/**
 * AB-Socks Logistics & Shipping Methods — Master Priority Matrix Table
 * High-aesthetic data table with unified ergonomics, integrated financial breakdown,
 * and zero horizontal scroll overflow.
 * View Purity: 0 SQL, 0 $_POST, pure presentation (Rule 7).
 *
 * @var array $methods
 */

// Helper closure for contextual logistics SVG icons & styling
$getMethodIcon = function (string $name): array {
    $n = mb_strtolower($name, 'UTF-8');
    if (str_contains($n, 'پیک') || str_contains($n, 'موتور') || str_contains($n, 'تهران') || str_contains($n, 'اسنپ') || str_contains($n, 'الوپیک')) {
        return [
            'class' => 'icon-courier',
            'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/></svg>',
        ];
    }
    if (str_contains($n, 'پیشتاز') || str_contains($n, 'پست') || str_contains($n, 'سفارشی')) {
        return [
            'class' => 'icon-post',
            'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>',
        ];
    }
    if (str_contains($n, 'تیپاکس') || str_contains($n, 'باربری') || str_contains($n, 'ترمینال') || str_contains($n, 'هوایی')) {
        return [
            'class' => 'icon-express',
            'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m13 2-2 10h5l-4 10 2-10h-5l4-10z"/></svg>',
        ];
    }
    return [
        'class' => 'icon-default',
        'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
    ];
};
?>
<div class="shipping-table-card">
    <div class="shipping-card-header">
        <div class="shipping-title-group">
            <h2 class="shipping-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                <span>لیست اولویت‌بندی روش‌های ارسال</span>
            </h2>
            <span class="shipping-count-pill"><?= toPersianDigits(count($methods)) ?> روش فعال</span>
        </div>
        <span class="shipping-header-hint">
            بررسی از بالا به پایین؛ اولین تطابق برای آدرس خریدار اعمال خواهد شد.
        </span>
    </div>

    <div class="shipping-table-wrap">
        <table class="admin-table shipping-table">
            <thead>
                <tr>
                    <th class="col-priority" style="width:48px; text-align:center;">اولویت</th>
                    <th class="col-method">روش ارسال و پوشش جغرافیایی</th>
                    <th class="col-pricing" style="width:185px;">تعرفه و تراز مالی</th>
                    <th class="col-status" style="width:65px; text-align:center;">وضعیت</th>
                    <th class="col-actions" style="width:85px; text-align:center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($methods as $i => $m): 
                $cost = (int)$m['cost'];
                $actualCost = $m['actual_cost'] !== null ? (int)$m['actual_cost'] : null;
                $diff = $actualCost !== null ? ($cost - $actualCost) : null;
                $iconData = $getMethodIcon($m['name']);
            ?>
            <tr class="shipping-method-row" data-id="<?= (int)$m['id'] ?>">
                <!-- 1. Priority Stepper -->
                <td class="col-priority" style="text-align:center;">
                    <div class="priority-vertical-rank">
                        <form method="post" style="margin:0;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="move">
                            <input type="hidden" name="direction" value="up">
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button type="submit" class="rank-btn rank-up" <?= $i === 0 ? 'disabled' : '' ?> title="افزایش اولویت">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                            </button>
                        </form>
                        <span class="rank-badge-num">#<?= toPersianDigits($i + 1) ?></span>
                        <form method="post" style="margin:0;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="move">
                            <input type="hidden" name="direction" value="down">
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button type="submit" class="rank-btn rank-down" <?= $i === count($methods) - 1 ? 'disabled' : '' ?> title="کاهش اولویت">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                        </form>
                    </div>
                </td>

                <!-- 2. Method Details & Scope -->
                <td class="col-method">
                    <div class="method-flex-cell">
                        <div class="method-icon-wrap <?= $iconData['class'] ?>">
                            <?= $iconData['svg'] ?>
                        </div>
                        <div class="method-content-body">
                            <div class="method-headline">
                                <strong class="method-name-text"><?= e($m['name']) ?></strong>
                                <?php if ($m['match_type'] === 'province_contains'): ?>
                                    <span class="scope-pill scope-province">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <span>استان «<?= e($m['match_value']) ?>»</span>
                                    </span>
                                <?php else: ?>
                                    <span class="scope-pill scope-default">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                        <span>سراسر کشور</span>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="method-sub-row">
                                <?php if (!empty($m['estimated_delivery'])): ?>
                                    <span class="delivery-time-chip">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <span><?= e($m['estimated_delivery']) ?></span>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($m['description'])): ?>
                                    <span class="method-desc-snip" title="<?= e($m['description']) ?>"><?= e($m['description']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </td>

                <!-- 3. Pricing & Financial Economics -->
                <td class="col-pricing">
                    <div class="pricing-flex-cell">
                        <div class="customer-price-row">
                            <?php if ($cost === 0): ?>
                                <span class="cost-free-pill">ارسال رایگان</span>
                            <?php else: ?>
                                <span class="cost-value-main"><?= formatPrice($cost) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="cost-actual-row">
                            <?php if ($actualCost !== null): ?>
                                <span class="cost-actual-text">پست: <?= formatPrice($actualCost) ?></span>
                                <?php if ($diff < 0): ?>
                                    <span class="cost-subsidy-tag is-subsidy" title="یارانه فروشگاه">−<?= formatPrice(abs($diff)) ?> یارانه</span>
                                <?php elseif ($diff > 0): ?>
                                    <span class="cost-subsidy-tag is-margin" title="حاشیه سود فروشگاه">+<?= formatPrice($diff) ?> سود</span>
                                <?php else: ?>
                                    <span class="cost-subsidy-tag is-neutral">سربه‌سر</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="cost-actual-text" style="color:#94a3b8;">بهای پست نامشخص</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($m['free_above_amount'] !== null && (int)$m['free_above_amount'] > 0): ?>
                            <div class="free-above-chip" title="سقف سفارش برای تخفیف ۱۰۰٪ کرایه">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                                <span>رایگان بالای <?= formatPrice((int)$m['free_above_amount']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- 4. Quick Active Switch -->
                <td class="col-status" style="text-align:center;">
                    <label class="switch-label" title="تغییر وضعیت فعال/غیرفعال">
                        <input type="checkbox" class="shipping-active-toggle" data-id="<?= (int)$m['id'] ?>" <?= !empty($m['is_active']) ? 'checked' : '' ?>>
                        <span class="switch-slider"></span>
                    </label>
                </td>

                <!-- 5. Actions -->
                <td class="col-actions" style="text-align:center;">
                    <div class="table-action-group">
                        <button type="button" class="btn-action-edit btn-edit-method" data-id="<?= (int)$m['id'] ?>" title="ویرایش روش ارسال">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            <span>ویرایش</span>
                        </button>
                        <form method="post" onsubmit="return confirm('آیا از حذف این روش ارسال مطمئن هستید؟');" style="display:inline; margin:0;">
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
                <td colspan="5" style="text-align:center; padding:48px 16px; color:var(--ship-muted);">
                    <div style="font-size:1.05rem; font-weight:700; color:#475569; margin-bottom:6px;">هیچ روش ارسالی تعریف نشده است</div>
                    <div style="font-size:0.8rem; color:#94A3B8;">بدون تعریف روش ارسال، هزینه حمل سفارش‌ها صفر محاسبه خواهد شد. با استفاده از فرم استودیو، اولین روش را اضافه کنید.</div>
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
