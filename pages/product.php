<?php
$slug = $_GET['slug'] ?? '';

$stmt = db()->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                        FROM products p JOIN categories c ON c.id = p.category_id
                        WHERE p.slug = ? AND p.is_active = 1 LIMIT 1");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return;
}

$pageTitle = $product['name'];

$imgStmt = db()->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$imgStmt->execute([$product['id']]);
$gallery = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
$mainImage = $product['image'] ? UPLOAD_URL . $product['image'] : '/assets/img/placeholder-sock.svg';
if (empty($gallery) && $product['image']) $gallery = [$product['image']];

$varStmt = db()->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
$varStmt->execute([$product['id']]);
$variants = $varStmt->fetchAll();

$hasVariants = count($variants) > 0;
$totalStock = $hasVariants ? array_sum(array_column($variants, 'stock')) : (int) $product['stock'];

$discount = discountPercent($product);
$finalPrice = effectivePrice($product);

require __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="product-detail">

        <div class="product-gallery">
            <img id="mainProductImage" src="<?= $mainImage ?>" alt="<?= e($product['name']) ?>">
            <?php if (count($gallery) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($gallery as $i => $img): ?>
                    <img src="<?= UPLOAD_URL . e($img) ?>" data-full="<?= UPLOAD_URL . e($img) ?>" class="<?= $i === 0 ? 'active' : '' ?>">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <div class="cat-label"><a href="/category/<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a></div>
            <h1><?= e($product['name']) ?></h1>

            <div class="price-box">
                <span class="price-current"><?= formatPrice($finalPrice) ?></span>
                <?php if ($discount > 0): ?>
                    <span class="price-old"><?= formatPrice($product['price']) ?></span>
                    <span class="badge-discount" style="position:static;"><?= toPersianDigits($discount) ?>%-</span>
                <?php endif; ?>
            </div>

            <div class="stock-info">
                <?php if ($totalStock <= 0): ?>
                    <span class="stock-out">ناموجود</span>
                <?php elseif ($totalStock <= 5): ?>
                    <span class="stock-low">فقط <?= toPersianDigits((string)$totalStock) ?> عدد باقی مانده</span>
                <?php else: ?>
                    <span class="stock-ok">موجود در انبار</span>
                <?php endif; ?>
            </div>

            <?php if ($totalStock > 0): ?>
            <form id="addToCartForm">
                <?= csrfField() ?>
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                <?php if ($hasVariants): ?>
                <div class="variant-group">
                    <label class="group-label">سایز / رنگ</label>
                    <div class="variant-options">
                        <?php foreach ($variants as $v):
                            $label = trim(($v['size'] ?? '') . ' ' . ($v['color'] ?? ''));
                            $disabled = $v['stock'] <= 0;
                        ?>
                        <label class="variant-chip <?= $disabled ? 'disabled' : '' ?>">
                            <input type="radio" name="variant_id" value="<?= (int)$v['id'] ?>" <?= $disabled ? 'disabled' : '' ?>>
                            <?= e($label ?: 'استاندارد') ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="variant-group">
                    <label class="group-label">تعداد</label>
                    <div class="qty-selector">
                        <button type="button" class="qty-minus">−</button>
                        <input type="number" name="qty" value="1" min="1" max="<?= min($totalStock, 20) ?>">
                        <button type="button" class="qty-plus">+</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">افزودن به سبد خرید</button>
            </form>
            <?php else: ?>
                <button class="btn btn-outline btn-block" disabled>ناموجود</button>
            <?php endif; ?>

            <?php if ($product['description']): ?>
            <div class="product-description">
                <?= nl2br(e($product['description'])) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
