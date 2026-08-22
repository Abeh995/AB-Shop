<?php
$slug = $_GET['slug'] ?? '';

$stmt = db()->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return;
}

$pageTitle = $category['name'];

$sort = $_GET['sort'] ?? 'newest';
$onlyAvailable = isset($_GET['available']);

$orderMap = [
    'newest'     => 'created_at DESC',
    'price_asc'  => 'COALESCE(discount_price, price) ASC',
    'price_desc' => 'COALESCE(discount_price, price) DESC',
];
$orderBy = $orderMap[$sort] ?? $orderMap['newest'];

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = 'category_id = ? AND is_active = 1';
$params = [$category['id']];
if ($onlyAvailable) {
    $where .= ' AND stock > 0';
}

$countStmt = db()->prepare("SELECT COUNT(*) FROM products WHERE $where");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

$stmt = db()->prepare("SELECT * FROM products WHERE $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <section class="section">
        <div class="section-title">
            <h2><?= e($category['name']) ?></h2>
        </div>

        <?php if ($category['description']): ?>
            <p style="color:var(--color-muted); margin-top:-14px; margin-bottom:24px;"><?= e($category['description']) ?></p>
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
                <?php foreach ($products as $p): include __DIR__ . '/partials/product_card.php'; endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?sort=<?= e($sort) ?><?= $onlyAvailable ? '&available=1' : '' ?>&page=<?= $i ?>"
                       class="<?= $i === $page ? 'active' : '' ?>"><?= toPersianDigits((string)$i) ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">در حال حاضر محصولی در این دسته‌بندی موجود نیست.</div>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
