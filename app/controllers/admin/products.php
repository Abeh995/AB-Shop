<?php
$pageTitle = 'محصولات';

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
    redirect('products.php');
}

$search = trim($_GET['q'] ?? '');
$where = '1=1';
$params = [];
if ($search !== '') {
    $where .= ' AND p.name LIKE ?';
    $params[] = '%' . $search . '%';
}

$stmt = db()->prepare("SELECT p.*, c.name AS category_name FROM products p
                        JOIN categories c ON c.id = p.category_id
                        WHERE $where ORDER BY p.created_at DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();

renderView('admin/products', compact('pageTitle', 'search', 'products'));
