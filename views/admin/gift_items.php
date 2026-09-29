<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div class="admin-card" style="display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; margin-bottom: 20px;">
    <form method="get" action="gift_items.php" style="display: flex; gap: 8px; flex: 1; max-width: 400px;">
        <input class="form-control" type="text" name="q" placeholder="جستجوی نام آیتم هدیه یا جانبی..." value="<?= e($search) ?>" style="height: 38px;">
        <button class="btn btn-outline" type="submit" style="height: 38px;">جستجو</button>
        <?php if ($search !== ''): ?>
            <a href="gift_items.php" class="btn btn-sm btn-outline" style="height: 38px; display: flex; align-items: center;">✕</a>
        <?php endif; ?>
    </form>
    <a href="gift_item_edit.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        افزودن آیتم جدید
    </a>
</div>

<div class="admin-card" style="padding: 0; overflow: hidden;">
    <div style="padding: 14px 20px; border-bottom: 1px solid var(--color-border); background: var(--color-bg-subtle, rgba(0,0,0,0.01));">
        <h3 style="margin: 0; font-size: 1.05rem;">کاتالوگ اقلام هدیه و ملزومات بسته‌بندی</h3>
        <p style="font-size: 0.8rem; color: var(--color-muted); margin: 2px 0 0 0;">آیتم‌هایی مانند جعبه هدیه، کارت پستال، یا اقلام قابل افزودن پس از سبد خرید.</p>
    </div>

    <div style="overflow-x: auto;">
        <table class="admin-table" style="margin: 0; width: 100%;">
            <thead>
                <tr style="background: var(--color-bg-subtle, rgba(0,0,0,0.02)); border-bottom: 1px solid var(--color-border);">
                    <th style="width: 60px; text-align: center;">تصویر</th>
                    <th>نام آیتم</th>
                    <th style="width: 180px;">نقش‌های فعال</th>
                    <th style="width: 130px;">قیمت خرید (تمام‌شده)</th>
                    <th style="width: 130px;">قیمت فروش جانبی</th>
                    <th style="width: 100px; text-align: center;">موجودی</th>
                    <th style="width: 90px; text-align: center;">وضعیت</th>
                    <th style="width: 130px; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it):
                $img = !empty($it['image']) ? UPLOAD_URL . e($it['image']) : '/assets/img/placeholder-sock.svg';
                $stockVal = (int) $it['stock'];
            ?>
            <tr style="border-bottom: 1px solid var(--color-border); vertical-align: middle;">
                <td style="text-align: center; padding: 10px;">
                    <div style="width: 44px; height: 44px; border-radius: 8px; overflow: hidden; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--color-border);">
                        <img src="<?= $img ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </td>
                <td style="font-weight: 600; font-size: 0.92rem;">
                    <?= e($it['name']) ?>
                </td>
                <td>
                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                        <?php if (!empty($it['is_giftable'])): ?>
                            <span class="status-pill status-shipped" style="font-size: 0.72rem; padding: 2px 6px;">هدیه رایگان ادمین</span>
                        <?php endif; ?>
                        <?php if (!empty($it['is_post_orderable'])): ?>
                            <span class="status-pill status-delivered" style="font-size: 0.72rem; padding: 2px 6px;">پیشنهاد بعد از سبد</span>
                        <?php endif; ?>
                    </div>
                </td>
                <td style="font-size: 0.88rem; color: var(--color-text-secondary, #4b5563);">
                    <?= formatPrice((int)$it['cost_price']) ?>
                </td>
                <td style="font-weight: 700; font-size: 0.9rem;">
                    <?= $it['post_order_price'] !== null ? formatPrice((int)$it['post_order_price']) : '—' ?>
                </td>
                <td style="text-align: center;">
                    <?php if ($stockVal === 0): ?>
                        <span class="status-pill status-cancelled" style="font-size: 0.75rem;">ناموجود</span>
                    <?php elseif ($stockVal <= 5): ?>
                        <span class="status-pill" style="background: rgba(245, 158, 11, 0.12); color: #d97706; font-size: 0.75rem;">
                            <?= toPersianDigits((string)$stockVal) ?>
                        </span>
                    <?php else: ?>
                        <span class="status-pill status-delivered" style="font-size: 0.75rem;">
                            <?= toPersianDigits((string)$stockVal) ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <?= !empty($it['is_active']) ? '<span class="status-pill status-delivered" style="font-size: 0.75rem;">فعال</span>' : '<span class="status-pill status-cancelled" style="font-size: 0.75rem;">غیرفعال</span>' ?>
                </td>
                <td style="text-align: center;">
                    <div class="admin-actions" style="justify-content: center; gap: 4px;">
                        <a href="gift_item_edit.php?id=<?= (int)$it['id'] ?>" class="btn btn-sm btn-outline" style="padding: 4px 8px;" title="ویرایش">ویرایش</a>
                        <form method="post" action="gift_items.php" onsubmit="return confirm('حذف این قلم کالا؟ (سفارش‌های قبلی تاریخچه‌شان حفظ می‌ماند)');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 8px;" title="حذف">✕</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($items)): ?>
                <tr><td colspan="8" style="text-align: center; padding: 30px; color: var(--color-muted);">هیچ آیتمی یافت نشد.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
