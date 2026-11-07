<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="container">
    <?php if (!empty($breadcrumbs)): ?>
    <nav class="breadcrumb-trail" aria-label="مسیر صفحه" style="margin: 1rem 0; font-size: 0.85rem; color: var(--color-muted);">
        <?php foreach ($breadcrumbs as $bi => $bc): ?>
            <?php if ($bi > 0): ?><span style="margin: 0 6px; opacity: 0.6;">/</span><?php endif; ?>
            <?php if ($bi === count($breadcrumbs) - 1): ?>
                <span style="color: var(--color-text); font-weight: 600;"><?= e($bc['name']) ?></span>
            <?php else: ?>
                <a href="<?= e($bc['url']) ?>" style="color: inherit; text-decoration: none;"><?= e($bc['name']) ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <section class="section">
        <div class="section-title category-header-wrap">
            <?php if (!empty($category['image'])): ?>
                <div class="category-header-icon">
                    <img src="<?= UPLOAD_URL . e($category['image']) ?>" alt="<?= e($category['name']) ?>" class="category-header-img">
                </div>
            <?php endif; ?>
            <div>
                <h1><?= e($category['name']) ?></h1>
            </div>
        </div>

        <?php if (!empty($category['seo_intro'])): ?>
            <div class="category-seo-intro" style="color:var(--color-text); font-size:0.95rem; line-height:1.7; margin-top:-10px; margin-bottom:20px;">
                <?= nl2br(e($category['seo_intro'])) ?>
            </div>
        <?php elseif (!empty($category['description'])): ?>
            <p style="color:var(--color-muted); margin-top:-14px; margin-bottom:24px;"><?= e($category['description']) ?></p>
        <?php endif; ?>

        <?php if ($subCategories): ?>
        <div class="category-sub-pills" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px;">
            <?php foreach ($subCategories as $sc): ?>
                <a href="/category/<?= e($sc['slug']) ?>" class="category-card" style="padding:8px 16px; font-size:.88rem; display:inline-flex; align-items:center; gap:8px;">
                    <?php if (!empty($sc['image'])): ?>
                        <span class="cat-icon" style="width:24px; height:24px; margin:0; overflow:hidden; border-radius:50%; flex-shrink:0;">
                            <img src="<?= UPLOAD_URL . e($sc['image']) ?>" alt="<?= e($sc['name']) ?>" class="cat-icon-img">
                        </span>
                    <?php endif; ?>
                    <?= e($sc['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form class="toolbar" method="get" action="/category/<?= e($slug) ?>">
            <label style="display:flex; align-items:center; gap:6px; font-size:.9rem;">
                <input type="checkbox" name="available" value="1" onchange="this.form.submit()" <?= $onlyAvailable ? 'checked' : '' ?>>
                فقط کالاهای موجود
            </label>
            <select name="sort" onchange="this.form.submit()">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>جدیدترین</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>ارزان‌ترین</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>گران‌ترین</option>
            </select>
        </form>

        <?php if ($products): ?>
            <div class="product-grid">
                <?php foreach ($products as $p): require APP_ROOT . '/views/site/partials/product_card.php'; endforeach; ?>
            </div>

            <?php
            $paginationParams = ['sort' => $sort];
            if ($onlyAvailable) $paginationParams['available'] = '1';
            require APP_ROOT . '/views/site/partials/pagination.php';
            ?>
        <?php else: ?>
            <div class="empty-state">در حال حاضر محصولی در این دسته‌بندی موجود نیست.</div>
        <?php endif; ?>
        <?php if (!empty($category['seo_guide'])): ?>
            <div class="category-seo-guide" style="margin-top:3rem; padding:1.5rem; background:#FFFFFF; border:1px solid var(--color-border); border-radius:12px; line-height:1.8;">
                <h3 style="font-size:1.15rem; font-weight:800; margin-bottom:12px; color:var(--color-text);">راهنمای جامع خرید و انتخاب <?= e($category['name']) ?></h3>
                <div style="font-size:0.95rem; color:var(--color-text);">
                    <?= nl2br(e($category['seo_guide'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($faqItems)): ?>
            <div class="category-faq-section" style="margin-top:2rem; padding:1.5rem; background:#FFFFFF; border:1px solid var(--color-border); border-radius:12px;">
                <h3 style="font-size:1.15rem; font-weight:800; margin-bottom:16px; color:var(--color-text);">پرسش‌های متداول درباره <?= e($category['name']) ?></h3>
                <div class="faq-accordion" style="display:flex; flex-direction:column; gap:12px;">
                    <?php foreach ($faqItems as $faq): ?>
                        <details style="border-bottom:1px solid var(--color-border); padding-bottom:10px; cursor:pointer;">
                            <summary style="font-weight:700; font-size:0.95rem; color:var(--color-text); outline:none;"><?= e($faq['q'] ?? '') ?></summary>
                            <p style="margin-top:8px; font-size:0.9rem; color:var(--color-muted); line-height:1.6;"><?= e($faq['a'] ?? '') ?></p>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
