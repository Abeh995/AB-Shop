<?php
/**
 * Master coupons matrix data table.
 */
?>
<section class="cpn-table-card">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>کد و عنوان تخفیف</th>
                    <th>نوع و ارزش</th>
                    <th>شرایط سبد</th>
                    <th>میزان مصرف و سقف کاربر</th>
                    <th>مهلت اعتبار (شمسی)</th>
                    <th>وضعیت</th>
                    <th style="text-align:left;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($coupons)): ?>
                    <tr>
                        <td colspan="7">
                            <?php component('empty_state', [
                                'title'   => 'هیچ کد تخفیفی با این مشخصات یافت نشد',
                                'message' => 'می‌توانید فیلترها را ریست کنید یا کد تخفیف جدیدی ایجاد نمایید.',
                            ]); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($coupons as $c): ?>
                        <?php
                        $isExpired = !empty($c['expires_at']) && strtotime($c['expires_at']) < strtotime('today');
                        $isLimitReached = $c['max_uses'] !== null && (int)$c['used_count'] >= (int)$c['max_uses'];
                        $usagePercent = !empty($c['max_uses']) ? min(100, round(((int)$c['used_count'] / (int)$c['max_uses']) * 100)) : null;
                        $type = $c['type'] ?? 'percent';
                        ?>
                        <tr>
                            <!-- 1. Code & Campaign Title -->
                            <td>
                                <div class="cpn-code-badge">
                                    <span><?= e($c['code']) ?></span>
                                    <button type="button" class="btn-copy-code" title="کپی کردن کد" onclick="copyCouponCode('<?= e($c['code']) ?>')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                                        </svg>
                                    </button>
                                </div>
                                <?php if (!empty($c['title'])): ?>
                                    <span class="cpn-title-tag" title="<?= e($c['title']) ?>">
                                        <?= e($c['title']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($c['category_name'])): ?>
                                    <span style="display:inline-block; font-size:.72rem; color:#4F46E5; background:#EEF2FF; padding:1px 6px; border-radius:4px; margin-top:3px;">
                                        دسته: <?= e($c['category_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- 2. Type & Value -->
                            <td>
                                <?php if ($type === 'free_shipping'): ?>
                                    <span class="cpn-type-pill cpn-type-free_shipping">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 17h4V5H2v12h3m11 0h2l3-3v-4h-5v7Z"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                                        ارسال رایگان
                                    </span>
                                <?php elseif ($type === 'percent'): ?>
                                    <span class="cpn-type-pill cpn-type-percent">
                                        <?= toPersianDigits((string)(float)$c['value']) ?>٪ تخفیف
                                    </span>
                                <?php else: ?>
                                    <span class="cpn-type-pill cpn-type-fixed">
                                        <?= formatPrice($c['value']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- 3. Basket Conditions -->
                            <td>
                                <div style="font-size:.82rem; font-weight:600; color:#1e293b;">
                                    <?php if (!empty($c['min_order_amount']) && (float)$c['min_order_amount'] > 0): ?>
                                        حداقل: <?= formatPrice($c['min_order_amount']) ?>
                                    <?php else: ?>
                                        <span style="color:#94a3b8; font-weight:500;">بدون حداقل</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($type === 'percent' && !empty($c['max_discount_amount']) && (float)$c['max_discount_amount'] > 0): ?>
                                    <div style="font-size:.74rem; color:#64748b; margin-top:2px;">
                                        سقف: <?= formatPrice($c['max_discount_amount']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- 4. Usage Count & Per-Customer Limit -->
                            <td>
                                <div style="font-size:.82rem; font-weight:700;">
                                    <?= toPersianDigits((string)(int)$c['used_count']) ?> 
                                    <?= $c['max_uses'] ? 'از ' . toPersianDigits((string)(int)$c['max_uses']) : 'بار (نامحدود)' ?>
                                </div>
                                <?php if ($usagePercent !== null): ?>
                                    <div class="usage-meter">
                                        <div class="usage-meter-fill <?= $usagePercent >= 100 ? 'full' : ($usagePercent >= 80 ? 'near-limit' : '') ?>" 
                                             style="width:<?= $usagePercent ?>%;"></div>
                                    </div>
                                <?php endif; ?>
                                <div style="font-size:.72rem; color:#64748b; margin-top:3px;">
                                    سقف هر کاربر: <?= toPersianDigits((string)($c['max_uses_per_customer'] ?? 1)) ?> بار
                                </div>
                            </td>

                            <!-- 5. Expiry Date (Shamsi) -->
                            <td>
                                <?php if (!empty($c['expires_at'])): ?>
                                    <div style="font-size:.82rem; font-weight:700; color:<?= $isExpired ? 'var(--cpn-rose)' : '#334155' ?>;">
                                        <?= appDateTime($c['expires_at'], 'shamsi') ?>
                                    </div>
                                    <div style="font-size:.72rem; color:<?= $isExpired ? 'var(--cpn-rose)' : '#64748b' ?>;">
                                        <?= $isExpired ? 'منقضی شده' : e($c['expires_at']) ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color:#94a3b8; font-size:.82rem;">همیشگی</span>
                                <?php endif; ?>
                            </td>

                            <!-- 6. Status -->
                            <td>
                                <?php
                                if (!$c['is_active']) {
                                    component('badge', ['text' => 'غیرفعال', 'type' => 'danger']);
                                } elseif ($isExpired) {
                                    component('badge', ['text' => 'منقضی', 'type' => 'warning']);
                                } elseif ($isLimitReached) {
                                    component('badge', ['text' => 'تکمیل ظرفیت', 'type' => 'muted']);
                                } else {
                                    component('badge', ['text' => 'فعال', 'type' => 'success', 'dot' => true]);
                                }
                                ?>
                            </td>

                            <!-- 7. Actions -->
                            <td style="text-align:left;">
                                <div style="display:inline-flex; align-items:center; gap:6px;">
                                    <!-- Analytics Drawer Button -->
                                    <button type="button" class="cpn-action-btn btn-stats" title="عملکرد مالی و سفارشات" 
                                            onclick="openCouponDrawer(<?= (int)$c['id'] ?>, '<?= e($c['code']) ?>')">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>
                                        </svg>
                                    </button>

                                    <!-- Edit Button -->
                                    <a href="/admin/coupons.php?edit=<?= (int)$c['id'] ?>" class="cpn-action-btn" title="ویرایش">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                    </a>

                                    <!-- Toggle Active Form -->
                                    <form method="post" action="/admin/coupons.php" style="margin:0; display:inline;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                        <button type="submit" class="cpn-action-btn" title="<?= $c['is_active'] ? 'تعلیق' : 'فعال‌سازی' ?>">
                                            <?php if ($c['is_active']): ?>
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                            <?php else: ?>
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                                            <?php endif; ?>
                                        </button>
                                    </form>

                                    <!-- Delete Form -->
                                    <form method="post" action="/admin/coupons.php" onsubmit="return confirm('آیا از حذف این کد تخفیف اطمینان دارید؟');" style="margin:0; display:inline;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                        <button type="submit" class="cpn-action-btn btn-delete" title="حذف">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
