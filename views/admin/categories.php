<?php
/**
 * Modern High-Density Admin Categories Master-Detail Workspace
 * Desktop Split View (Sticky Smart Editor + Interactive Branching Tree Table & Bento KPIs).
 */
require APP_ROOT . '/views/admin/layout/header.php';

// Prepare lightweight JSON dataset for client-side Master-Detail interactions
$categoriesJsonMap = [];
foreach ($categories as $c) {
    $categoriesJsonMap[(int)$c['id']] = [
        'id'            => (int)$c['id'],
        'name'          => $c['name'],
        'slug'          => $c['slug'] ?? '',
        'parent_id'     => $c['parent_id'] !== null ? (int)$c['parent_id'] : null,
        'description'   => $c['description'] ?? '',
        'image'         => $c['image'] ?? '',
        'sort_order'    => (int)$c['sort_order'],
        'is_active'     => (int)$c['is_active'],
        'product_count' => (int)$c['product_count'],
        'depth'         => (int)$c['depth'],
    ];
}
?>

<meta name="csrf-token" content="<?= e(csrfToken()) ?>">

<main class="cat-workspace">

    <!-- =================================================================== -->
    <!-- 1. Bento KPI Stats Header (Category Catalog Health)                 -->
    <!-- =================================================================== -->
    <section class="cat-kpi-grid">
        <!-- 1. Total Categories -->
        <div class="cat-kpi-card highlight-blue">
            <div class="cat-kpi-header">
                <span class="cat-kpi-title">مجموع دسته‌بندی‌ها</span>
                <div class="cat-kpi-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                </div>
            </div>
            <div class="cat-kpi-val-row">
                <span class="cat-kpi-val"><?= toPersianDigits((string)$stats['total']) ?></span>
                <span class="cat-kpi-unit">دسته کالا</span>
            </div>
            <div class="cat-kpi-sub">
                <span style="font-weight: 700; color: var(--cat-text-primary);"><?= toPersianDigits((string)$stats['root_count']) ?> دسته اصلی</span>
                <span>•</span>
                <span><?= toPersianDigits((string)$stats['sub_count']) ?> زیردسته</span>
            </div>
        </div>

        <!-- 2. Active in Storefront -->
        <div class="cat-kpi-card highlight-green">
            <div class="cat-kpi-header">
                <span class="cat-kpi-title">دسته‌های فعال فروشگاه</span>
                <div class="cat-kpi-icon" style="color: var(--cat-success); background: var(--cat-success-light);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                </div>
            </div>
            <div class="cat-kpi-val-row">
                <span class="cat-kpi-val" style="color: var(--cat-success);"><?= toPersianDigits((string)$stats['active_count']) ?></span>
                <span class="cat-kpi-unit">دسته منتشرشده</span>
            </div>
            <div class="cat-kpi-sub">نمایش مستقیم در منو و آرشیو سایت</div>
        </div>

        <!-- 3. Empty Categories Alert -->
        <div class="cat-kpi-card highlight-amber is-clickable" id="kpiEmptyCategories" title="کلیک جهت فیلتر دسته‌های بدون کالا">
            <div class="cat-kpi-header">
                <span class="cat-kpi-title">دسته‌های خالی (بدون کالا)</span>
                <div class="cat-kpi-icon" style="color: var(--cat-warning); background: var(--cat-warning-light);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
            </div>
            <div class="cat-kpi-val-row">
                <span class="cat-kpi-val" style="color: #D97706;"><?= toPersianDigits((string)$stats['empty_count']) ?></span>
                <span class="cat-kpi-unit">دسته فاقد محصول</span>
            </div>
            <div class="cat-kpi-sub">
                <?php if ($stats['empty_count'] > 0): ?>
                    <span style="color: #D97706; font-weight: 700;">نیازمند تخصیص کالا یا حذف</span>
                <?php else: ?>
                    <span style="color: var(--cat-success); font-weight: 700;">همه دسته‌ها دارای محصول هستند</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. Top Category by Volume -->
        <div class="cat-kpi-card highlight-purple">
            <div class="cat-kpi-header">
                <span class="cat-kpi-title">پرمخاطب‌ترین دسته</span>
                <div class="cat-kpi-icon" style="color: #8B5CF6; background: rgba(139, 92, 246, 0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
            </div>
            <div class="cat-kpi-val-row">
                <span class="cat-kpi-val" style="font-size: 1.15rem; color: #6D28D9;"><?= e($stats['top_category']['name']) ?></span>
            </div>
            <div class="cat-kpi-sub">
                <span>تعداد کالاهای متصل:</span>
                <strong style="color: var(--cat-text-primary);"><?= toPersianDigits((string)$stats['top_category']['count']) ?> قلم</strong>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 2. Split Master-Detail Workspace                                    -->
    <!-- =================================================================== -->
    <div class="cat-split-grid">

        <!-- =============================================================== -->
        <!-- Right Column: Sticky Smart Category Editor                     -->
        <!-- =============================================================== -->
        <aside class="cat-editor-card" id="catEditorCard">
            <div class="cat-editor-header">
                <div class="cat-editor-header-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                    <span class="cat-mode-badge" id="catModeBadge">افزودن دسته‌بندی جدید</span>
                </div>
                <button type="button" class="cat-btn-reset-mode" id="catBtnResetHeader" title="بازگشت به حالت ایجاد دسته جدید">
                    <span>انصراف</span>
                    <span>✕</span>
                </button>
            </div>

            <form method="post" action="categories.php" id="catForm" class="cat-editor-body" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="action" id="catInputAction" value="create">
                <input type="hidden" name="id" id="catInputId" value="0">

                <!-- 1. Name -->
                <div class="cat-form-group">
                    <label class="cat-label" for="catInputName">
                        <span>نام دسته‌بندی <span class="cat-req">*</span></span>
                    </label>
                    <input class="cat-input" type="text" name="name" id="catInputName" placeholder="مثلاً: کالج مردانه نخی" required autocomplete="off">
                </div>

                <!-- 2. Slug & Live Preview -->
                <div class="cat-form-group">
                    <label class="cat-label" for="catInputSlug">
                        <span>نامک انگلیسی / اسلاگ</span>
                    </label>
                    <input class="cat-input" type="text" name="slug" id="catInputSlug" placeholder="men-socks" dir="ltr" autocomplete="off">
                    <div class="cat-slug-preview-box">
                        <span class="cat-slug-prefix">ab-socks.ir/category/</span>
                        <span class="cat-slug-text" id="catSlugPreviewText">category-name</span>
                    </div>
                </div>

                <!-- 3. Parent Category -->
                <div class="cat-form-group">
                    <label class="cat-label" for="catSelectParent">
                        <span>دسته والد</span>
                    </label>
                    <select class="cat-select" name="parent_id" id="catSelectParent">
                        <option value="">بدون والد (دسته اصلی کاتالوگ)</option>
                        <?php foreach ($topLevelCategories as $tc): ?>
                            <option value="<?= (int)$tc['id'] ?>"><?= e($tc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 4. Category Image / Icon Preview (UI Ready) -->
                <div class="cat-form-group">
                    <label class="cat-label">تصویر یا آیکون شاخص</label>
                    <div class="cat-image-upload-wrap">
                        <div class="cat-image-preview-box">
                            <img src="/assets/img/placeholder-sock.svg" id="catImagePreview" class="cat-image-preview-img" alt="">
                        </div>
                        <div class="cat-image-upload-text" style="flex: 1;">
                            <input type="file" name="image" id="catImageInput" accept="image/*,.heic,.heif" data-optimize-image="category" data-layout="compact" data-max-dimension="1200" data-default-quality="0.85">
                            <input type="hidden" name="remove_image" id="catInputRemoveImage" value="0">
                            <button type="button" id="catBtnRemoveImage" class="cat-btn-remove-img" style="display:none; margin-top: 6px;">✕ حذف تصویر فعلی</button>
                        </div>
                    </div>
                </div>

                <!-- 5. Sort Order -->
                <div class="cat-form-group">
                    <label class="cat-label" for="catInputSort">
                        <span>ترتیب نمایش عددی</span>
                    </label>
                    <input class="cat-input" type="number" name="sort_order" id="catInputSort" value="0">
                </div>

                <!-- 6. Short Description -->
                <div class="cat-form-group">
                    <label class="cat-label" for="catTextareaDesc">
                        <span>توضیحات سئو و آرشیو</span>
                    </label>
                    <textarea class="cat-textarea" name="description" id="catTextareaDesc" rows="3" placeholder="توضیحات کوتاه جهت نمایش در سربرگ صفحه آرشیو..."></textarea>
                </div>

                <!-- 7. Active Status Switch -->
                <div class="cat-switch-row">
                    <div class="cat-switch-label-group">
                        <span class="cat-switch-title">انتشار در فروشگاه</span>
                        <span class="cat-switch-sub">نمایش در هدر منو و فیلترهای سایت</span>
                    </div>
                    <label class="cat-switch">
                        <input type="checkbox" name="is_active" id="catCheckboxActive" value="1" checked>
                        <span class="cat-slider"></span>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="cat-editor-actions">
                    <button type="submit" class="cat-btn-submit" id="catBtnSubmit">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>+ ثبت دسته‌بندی</span>
                    </button>
                    <button type="button" class="cat-btn-cancel-edit" id="catBtnCancel">انصراف</button>
                </div>
            </form>
        </aside>

        <!-- =============================================================== -->
        <!-- Left Column: Interactive Tree Table Card                       -->
        <!-- =============================================================== -->
        <section class="cat-tree-card">
            <header class="cat-tree-toolbar">
                <!-- Live Search -->
                <div class="cat-search-wrap">
                    <svg class="cat-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" id="catSearchInput" class="cat-search-input" placeholder="جستجوی سریع دسته یا اسلاگ..." autocomplete="off">
                </div>

                <!-- Filter Chips -->
                <div class="cat-tree-filters">
                    <button type="button" class="cat-filter-btn active" data-filter="all">همه (<?= toPersianDigits((string)count($categories)) ?>)</button>
                    <button type="button" class="cat-filter-btn" data-filter="root">دسته‌های اصلی (<?= toPersianDigits((string)$stats['root_count']) ?>)</button>
                    <button type="button" class="cat-filter-btn" data-filter="empty">فاقد کالا (<?= toPersianDigits((string)$stats['empty_count']) ?>)</button>
                </div>
            </header>

            <div style="overflow-x: auto;">
                <table class="cat-table">
                    <thead class="cat-thead">
                        <tr>
                            <th class="cat-th">ساختار درختی و عنوان دسته</th>
                            <th class="cat-th" style="width: 140px;">دسته والد</th>
                            <th class="cat-th cat-th-center" style="width: 100px;">کالاهای متصل</th>
                            <th class="cat-th cat-th-center" style="width: 70px;">ترتیب</th>
                            <th class="cat-th cat-th-center" style="width: 80px;">وضعیت</th>
                            <th class="cat-th cat-th-center" style="width: 110px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="catTableBody">
                    <?php foreach ($categories as $c): 
                        $cid = (int)$c['id'];
                        $depth = (int)$c['depth'];
                        $isRoot = $depth === 0;
                        $pCount = (int)$c['product_count'];
                        $slug = $c['slug'] ?? '';
                    ?>
                    <tr class="cat-row cat-depth-<?= $depth ?>" 
                        id="cat-row-<?= $cid ?>" 
                        data-id="<?= $cid ?>"
                        data-name="<?= e($c['name']) ?>"
                        data-slug="<?= e($slug) ?>"
                        data-depth="<?= $depth ?>"
                        data-count="<?= $pCount ?>">
                        
                        <!-- Tree Visual & Category Name -->
                        <td class="cat-td">
                            <div class="cat-name-cell">
                                <span class="cat-tree-branch"></span>
                                <?php if (!empty($c['image'])): ?>
                                    <div class="cat-thumb-box" title="<?= e($c['name']) ?>">
                                        <img src="<?= UPLOAD_URL . e($c['image']) ?>" class="cat-thumb-img" alt="" loading="lazy">
                                    </div>
                                <?php else: ?>
                                    <div class="cat-node-icon <?= !$isRoot ? 'is-sub' : '' ?>">
                                        <?php if ($isRoot): ?>
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                        <?php else: ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="cat-name-group">
                                    <span class="cat-title-text" title="کلیک جهت ویرایش سریع"><?= e($c['name']) ?></span>
                                    <span class="cat-slug-label">/category/<?= e($slug) ?></span>
                                </div>
                            </div>
                        </td>

                        <!-- Parent Badge -->
                        <td class="cat-td">
                            <?php if ($isRoot): ?>
                                <span class="cat-parent-badge is-root">دسته اصلی</span>
                            <?php else: 
                                $parentObj = array_values(array_filter($topLevelCategories, fn($t) => $t['id'] == $c['parent_id']))[0] ?? null;
                            ?>
                                <span class="cat-parent-badge"><?= e($parentObj['name'] ?? 'والد') ?></span>
                            <?php endif; ?>
                        </td>

                        <!-- Product Count Link -->
                        <td class="cat-td cat-td-center">
                            <a href="products.php?category_id=<?= $cid ?>" 
                               class="cat-count-pill <?= $pCount > 0 ? 'has-items' : 'is-empty' ?>" 
                               title="مشاهده کالاهای این دسته در کاتالوگ">
                                <span><?= toPersianDigits((string)$pCount) ?></span>
                                <span style="font-size: 0.68rem; font-weight: normal;">کالا</span>
                            </a>
                        </td>

                        <!-- Sort Order -->
                        <td class="cat-td cat-td-center">
                            <span class="cat-sort-badge"><?= toPersianDigits((string)$c['sort_order']) ?></span>
                        </td>

                        <!-- Active Toggle Switch (AJAX) -->
                        <td class="cat-td cat-td-center">
                            <label class="cat-switch" title="تغییر وضعیت نمایش در سایت">
                                <input type="checkbox" class="cat-switch-row-input" data-id="<?= $cid ?>" <?= !empty($c['is_active']) ? 'checked' : '' ?>>
                                <span class="cat-slider"></span>
                            </label>
                        </td>

                        <!-- Actions -->
                        <td class="cat-td cat-td-center">
                            <div class="cat-actions-group">
                                <!-- Edit Button -->
                                <button type="button" class="cat-act-btn btn-cat-edit" data-id="<?= $cid ?>" title="ویرایش در فرم هوشمند">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>

                                <!-- View in Storefront -->
                                <a href="/category.php?slug=<?= urlencode($slug) ?>" target="_blank" class="cat-act-btn" title="مشاهده در فروشگاه">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </a>

                                <!-- Delete -->
                                <form method="post" action="categories.php" onsubmit="return confirm('آیا از حذف دسته‌بندی «<?= e(addslashes($c['name'])) ?>» اطمینان دارید؟');" style="display:inline;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $cid ?>">
                                    <button type="submit" class="cat-act-btn btn-cat-del" title="حذف دسته">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <tr id="catEmptySearch" style="display: none;">
                        <td colspan="6" style="text-align: center; padding: 40px 20px; color: var(--cat-text-muted);">
                            <div style="font-size: 0.92rem; font-weight: 600;">هیچ دسته‌بندی‌ای با این جستجو پیدا نشد.</div>
                        </td>
                    </tr>

                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px 20px; color: var(--cat-text-muted);">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 8px; opacity: 0.4;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                <div style="font-size: 0.95rem; font-weight: 600;">هنوز دسته‌بندی‌ای در فروشگاه تعریف نشده است.</div>
                                <div style="font-size: 0.8rem; margin-top: 4px;">از فرم سمت راست برای ثبت اولین دسته استفاده کنید.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <!-- Toast Notification -->
    <div class="cat-toast" id="catToast"></div>

</main>

<script>
    // Hydrate client-side categories dataset
    window.categoriesDataMap = <?= json_encode($categoriesJsonMap, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/assets/js/admin-categories.js?v=<?= APP_VERSION . '.' . (@filemtime(APP_ROOT . '/assets/js/admin-categories.js') ?: 1) ?>"></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
