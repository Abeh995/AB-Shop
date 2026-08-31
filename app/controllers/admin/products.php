<?php
$featuredOnly = isset($_GET['featured']);
$pageTitle = $featuredOnly ? 'محصولات ویژه' : 'محصولات';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);

    $stmt = db()->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists(UPLOAD_DIR . $img)) {
        @unlink(UPLOAD_DIR . $img);
    }

    db()->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    setFlash('success', 'محصول حذف شد.');
    redirect($featuredOnly ? 'products.php?featured=1' : 'products.php');
}

$search = trim($_GET['q'] ?? '');
$where = '1=1';
$params = [];
if ($search !== '') {
    $where .= ' AND p.name LIKE ?';
    $params[] = '%' . $search . '%';
}
if ($featuredOnly) {
    $where .= ' AND p.is_featured = 1';
}

// Pre-built variant-stock summary string (size/color: stock) so the products
// table can show each variant's stock separately instead of one overall number.
$stmt = db()->prepare("SELECT p.*, c.name AS category_name,
        (SELECT GROUP_CONCAT(
                CONCAT(TRIM(CONCAT(COALESCE(v.size,''), ' ', COALESCE(v.color,''))), ': ', v.stock)
                SEPARATOR ' | ')
         FROM product_variants v WHERE v.product_id = p.id) AS variant_stock_summary
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE $where ORDER BY p.created_at DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();

renderView('admin/products', compact('pageTitle', 'search', 'products', 'featuredOnly'));
