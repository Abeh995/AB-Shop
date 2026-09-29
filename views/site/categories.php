<?php
/**
 * Categories Hub View — displays full category tree with active product counts.
 */
require APP_ROOT . '/views/layout/header.php';
?>

<div class="container section">
    <div class="hub-header">
        <h1 class="hub-title">دسته‌بندی محصولات</h1>
        <p class="hub-subtitle">برای مشاهده محصولات، دسته‌بندی مورد نظر خود را انتخاب کنید.</p>
    </div>

    <?php if (empty($categories)): ?>
        <div class="empty-state">هیچ دسته‌بندی فعالی یافت نشد.</div>
    <?php else: ?>
        <div class="categories-hub-grid">
            <?php foreach ($categories as $cat):
                $children = $childrenByParent[(int) $cat['id']] ?? [];
                $img = $cat['image'] ? UPLOAD_URL . e($cat['image']) : '';
            ?>
            <div class="category-hub-card">
                <a href="/category/<?= e($cat['slug']) ?>" class="category-hub-card-header">
                    <div class="category-hub-icon-wrap">
                        <?php if ($img): ?>
                            <img src="<?= $img ?>" alt="<?= e($cat['name']) ?>" class="category-hub-img">
                        <?php else: ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="category-hub-title-wrap">
                        <h3><?= e($cat['name']) ?></h3>
                        <span class="category-hub-count"><?= toPersianDigits((string)$cat['product_count']) ?> محصول</span>
                    </div>
                    <span class="category-hub-arrow" aria-hidden="true">&larr;</span>
                </a>

                <?php if ($children): ?>
                <div class="category-hub-sublist">
                    <?php foreach ($children as $sub): ?>
                        <a href="/category/<?= e($sub['slug']) ?>" class="category-hub-subchip">
                            <span><?= e($sub['name']) ?></span>
                            <small>(<?= toPersianDigits((string)$sub['product_count']) ?>)</small>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
