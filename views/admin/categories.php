<?php require APP_ROOT . '/views/admin/layout/header.php'; ?>

<div style="display: grid; grid-template-columns: 340px 1fr; gap: 24px; align-items: start;">
    
    <!-- 1. Add New Category Panel (Right / Sticky) -->
    <div class="admin-card" style="position: sticky; top: 20px;">
        <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            افزودن دسته‌بندی جدید
        </h3>

        <form method="post" action="categories.php">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label style="font-weight: 600;">نام دسته‌بندی <span style="color: var(--color-danger);">*</span></label>
                <input class="form-control" type="text" name="name" placeholder="مثلاً: کالج مردانه" required>
            </div>

            <div class="form-group">
                <label style="font-weight: 600;">دسته والد (اختیاری)</label>
                <select class="form-control" name="parent_id">
                    <option value="">بدون والد (دسته اصلی)</option>
                    <?php foreach ($topLevelCategories as $tc): ?>
                        <option value="<?= (int)$tc['id'] ?>"><?= e($tc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p style="font-size: 0.75rem; color: var(--color-muted); margin-top: 4px;">برای ایجاد ساختار درختی و تفکیک زیردسته‌ها.</p>
            </div>

            <div class="form-group">
                <label style="font-weight: 600;">ترتیب نمایش عددی</label>
                <input class="form-control" type="number" name="sort_order" value="0">
            </div>

            <div class="form-group">
                <label style="font-weight: 600;">توضیحات کوتاه (اختیاری)</label>
                <textarea class="form-control" name="description" rows="3" placeholder="توضیح کوتاه جهت نمایش در صفحه آرشیو..."></textarea>
            </div>

            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px; cursor: pointer; user-select: none;">
                <input type="checkbox" name="is_active" checked style="width: 16px; height: 16px;">
                <span style="font-weight: 600;">فعال (نمایش در منو و سایت)</span>
            </label>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 9px 16px;">
                + ثبت دسته‌بندی
            </button>
        </form>
    </div>

    <!-- 2. Categories Hierarchy List Panel (Left) -->
    <div class="admin-card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                درخت دسته‌بندی‌ها (<?= toPersianDigits((string)count($categories)) ?> دسته)
            </h3>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table" style="margin: 0; width: 100%;">
                <thead>
                    <tr style="background: var(--color-bg-subtle, rgba(0,0,0,0.02)); border-bottom: 1px solid var(--color-border);">
                        <th>نام دسته و عنوان</th>
                        <th style="width: 160px;">دسته والد</th>
                        <th style="width: 90px; text-align: center;">کالاها</th>
                        <th style="width: 90px; text-align: center;">وضعیت</th>
                        <th style="width: 70px; text-align: center;">ترتیب</th>
                        <th style="width: 140px; text-align: center;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $c): ?>
                <tr style="border-bottom: 1px solid var(--color-border); vertical-align: middle;">
                    <form method="post" action="categories.php">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <input type="hidden" name="description" value="<?= e($c['description'] ?? '') ?>">

                        <!-- Category Name with Depth Indent -->
                        <td style="padding: 10px 14px;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <?php if ($c['depth'] > 0): ?>
                                    <span style="color: var(--color-muted); font-size: 1.1rem; margin-right: <?= ($c['depth'] - 1) * 16 ?>px;">└</span>
                                <?php endif; ?>
                                <input class="form-control" type="text" name="name" value="<?= e($c['name']) ?>" style="font-weight: <?= $c['depth'] === 0 ? '600' : 'normal' ?>; font-size: 0.9rem; min-width: 160px;">
                            </div>
                        </td>

                        <!-- Parent Selector -->
                        <td>
                            <select class="form-control" name="parent_id" style="font-size: 0.82rem;">
                                <option value="">دسته اصلی</option>
                                <?php foreach ($topLevelCategories as $tc): if ($tc['id'] == $c['id']) continue; ?>
                                    <option value="<?= (int)$tc['id'] ?>" <?= ($c['parent_id'] == $tc['id']) ? 'selected' : '' ?>><?= e($tc['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>

                        <!-- Product Count -->
                        <td style="text-align: center;">
                            <a href="products.php?category_id=<?= (int)$c['id'] ?>" class="status-pill status-shipped" style="text-decoration: none; font-size: 0.78rem;">
                                <?= toPersianDigits((string)$c['product_count']) ?> کالا
                            </a>
                        </td>

                        <!-- Active State -->
                        <td style="text-align: center;">
                            <label style="display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                                <input type="checkbox" name="is_active" <?= !empty($c['is_active']) ? 'checked' : '' ?>>
                            </label>
                        </td>

                        <!-- Sort Order -->
                        <td style="text-align: center;">
                            <input class="form-control" type="number" name="sort_order" value="<?= (int)$c['sort_order'] ?>" style="width: 55px; text-align: center; margin: 0 auto; padding: 4px;">
                        </td>

                        <!-- Actions -->
                        <td style="text-align: center;">
                            <div class="admin-actions" style="justify-content: center; gap: 4px;">
                                <button type="submit" class="btn btn-sm btn-outline" style="padding: 4px 8px;" title="ذخیره تغییرات">
                                    ذخیره
                                </button>
                    </form>
                                <form method="post" action="categories.php" onsubmit="return confirm('آیا از حذف این دسته‌بندی اطمینان دارید؟');" style="display:inline;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 8px;" title="حذف دسته">✕</button>
                                </form>
                            </div>
                        </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: var(--color-muted);">هیچ دسته‌بندی‌ای یافت نشد.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
