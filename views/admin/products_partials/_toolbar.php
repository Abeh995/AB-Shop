<?php
/**
 * Control Toolbar & Filter Panel for Products Catalog (views/admin/products_partials/_toolbar.php)
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 */

$tabParams = $_GET;
unset($tabParams['status'], $tabParams['page']);
$qStr = !empty($tabParams) ? '&' . http_build_query($tabParams) : '';
$isFiltered = $search !== '' || $categoryId > 0 || $tagId > 0 || $sort !== 'newest' || $status !== 'all' || $perPage !== 20;
?>
<div class="ab-stack ab-stack--sm">

    <!-- 1. Status Filter Chips Row -->
    <div class="ab-toolbar">
        <div class="ab-toolbar__lead">
            <div class="ab-cluster ab-cluster--xs">
                <a href="products.php?status=all<?= $qStr ?>" 
                   class="ab-chip <?= $status === 'all' ? 'is-active' : '' ?>"
                   aria-pressed="<?= $status === 'all' ? 'true' : 'false' ?>">
                    <span>همه محصولات</span>
                    <span class="ab-badge ab-badge--sm ab-num"><?= toPersianDigits((string)($stats['total_products'] ?? 0)) ?></span>
                </a>
                <a href="products.php?status=active<?= $qStr ?>" 
                   class="ab-chip <?= $status === 'active' ? 'is-active' : '' ?>"
                   aria-pressed="<?= $status === 'active' ? 'true' : 'false' ?>"
                   data-tone="emerald">
                    <span class="ab-badge-dot"></span>
                    <span>فعال</span>
                    <span class="ab-badge ab-badge--sm ab-num"><?= toPersianDigits((string)($stats['active_products'] ?? 0)) ?></span>
                </a>
                <a href="products.php?status=featured<?= $qStr ?>" 
                   class="ab-chip <?= $status === 'featured' ? 'is-active' : '' ?>"
                   aria-pressed="<?= $status === 'featured' ? 'true' : 'false' ?>"
                   data-tone="amber">
                    <span>پیشنهاد ویژه</span>
                    <span class="ab-badge ab-badge--sm ab-num"><?= toPersianDigits((string)($stats['featured_products'] ?? 0)) ?></span>
                </a>
                <a href="products.php?status=low_stock<?= $qStr ?>" 
                   class="ab-chip <?= $status === 'low_stock' ? 'is-active' : '' ?>"
                   aria-pressed="<?= $status === 'low_stock' ? 'true' : 'false' ?>"
                   data-tone="amber">
                    <span>رو به اتمام</span>
                    <span class="ab-badge ab-badge--sm ab-num"><?= toPersianDigits((string)($stats['low_stock_count'] ?? 0)) ?></span>
                </a>
                <a href="products.php?status=out_of_stock<?= $qStr ?>" 
                   class="ab-chip <?= $status === 'out_of_stock' ? 'is-active' : '' ?>"
                   aria-pressed="<?= $status === 'out_of_stock' ? 'true' : 'false' ?>"
                   data-tone="rose">
                    <span>ناموجود</span>
                    <span class="ab-badge ab-badge--sm ab-num"><?= toPersianDigits((string)($stats['out_of_stock_count'] ?? 0)) ?></span>
                </a>
                <a href="products.php?status=discounted<?= $qStr ?>" 
                   class="ab-chip <?= $status === 'discounted' ? 'is-active' : '' ?>"
                   aria-pressed="<?= $status === 'discounted' ? 'true' : 'false' ?>"
                   data-tone="purple">
                    <span>دارای تخفیف</span>
                </a>
            </div>
        </div>

        <div class="ab-toolbar__actions">
            <a href="product_edit.php" class="ab-btn ab-btn--sm ab-btn--primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>محصول جدید</span>
            </a>
        </div>
    </div>

    <!-- 2. Form Filters Row -->
    <form method="get" action="products.php" class="ab-toolbar">
        <input type="hidden" name="status" value="<?= e($status) ?>">

        <div class="ab-toolbar__lead">
            <!-- Search Input -->
            <div class="ab-toolbar__search">
                <input class="ab-input" type="search" name="q" 
                       placeholder="جستجوی عنوان محصول، مشخصات یا کد SKU..." 
                       value="<?= e($search) ?>" autocomplete="off"
                       data-ab-filter-target="#productsTable">
            </div>

            <!-- Filters Group -->
            <div class="ab-toolbar__filters">
                <!-- Category Select -->
                <select class="ab-select ab-select--sm" name="category_id" aria-label="فیلتر دسته‌بندی">
                    <option value="">همه دسته‌بندی‌ها</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ($categoryId === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= $c['depth'] > 0 ? str_repeat('— ', $c['depth']) : '' ?><?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Tag Select -->
                <select class="ab-select ab-select--sm" name="tag_id" aria-label="فیلتر برچسب">
                    <option value="">همه برچسب‌ها</option>
                    <?php foreach ($allTags as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= ($tagId === (int)$t['id']) ? 'selected' : '' ?>>
                            <?= e($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Sort Select -->
                <select class="ab-select ab-select--sm" name="sort" aria-label="ترتیب نمایش">
                    <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>جدیدترین</option>
                    <option value="oldest" <?= ($sort === 'oldest') ? 'selected' : '' ?>>قدیمی‌ترین</option>
                    <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>ارزان‌ترین</option>
                    <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>گران‌ترین</option>
                    <option value="stock_desc" <?= ($sort === 'stock_desc') ? 'selected' : '' ?>>بیشترین موجودی</option>
                    <option value="stock_asc" <?= ($sort === 'stock_asc') ? 'selected' : '' ?>>کمترین موجودی</option>
                    <option value="name_asc" <?= ($sort === 'name_asc') ? 'selected' : '' ?>>نام کالا (الفبا)</option>
                </select>

                <!-- Per Page Select -->
                <select class="ab-select ab-select--sm" name="per_page" aria-label="تعداد در هر صفحه">
                    <option value="20" <?= ($perPage === 20) ? 'selected' : '' ?>>۲۰ در صفحه</option>
                    <option value="50" <?= ($perPage === 50) ? 'selected' : '' ?>>۵۰ در صفحه</option>
                    <option value="100" <?= ($perPage === 100) ? 'selected' : '' ?>>۱۰۰ در صفحه</option>
                </select>
            </div>
        </div>

        <div class="ab-toolbar__actions">
            <button class="ab-btn ab-btn--sm ab-btn--secondary" type="submit">اعمال فیلتر</button>
            <?php if ($isFiltered): ?>
                <a href="products.php" class="ab-btn ab-btn--sm ab-btn--ghost" title="حذف تمام فیلترها">✕ پاک‌سازی</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- 3. Active Tag Filter Banner -->
    <?php if ($tagId > 0 && !empty($activeTag)): ?>
        <?php
        $removeTagParams = $_GET;
        unset($removeTagParams['tag_id'], $removeTagParams['page']);
        $removeTagUrl = 'products.php' . (!empty($removeTagParams) ? '?' . http_build_query($removeTagParams) : '');
        ?>
        <div class="ab-alert" data-state="info">
            <div class="ab-cluster ab-cluster--between">
                <span class="ab-cluster ab-cluster--xs">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r="1.5" fill="currentColor"/></svg>
                    <span>فیلتر برچسب فعال: <strong><?= e($activeTag['name']) ?></strong></span>
                </span>
                <a href="<?= e($removeTagUrl) ?>" class="ab-btn ab-btn--sm ab-btn--ghost" title="حذف فیلتر برچسب">✕ حذف فیلتر</a>
            </div>
        </div>
    <?php endif; ?>

</div>
