<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
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

require __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-card" style="display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap;">
    <form method="get" style="display:flex; gap:8px;">
        <input class="form-control" type="text" name="q" placeholder="جستجوی نام محصول..." value="<?= e($search) ?>">
        <button class="btn btn-outline" type="submit">جستجو</button>
    </form>
    <a href="product_edit.php" class="btn btn-primary">+ محصول جدید</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th></th><th>نام</th><th>دسته‌بندی</th><th>قیمت</th><th>موجودی</th><th>وضعیت</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p):
            $img = $p['image'] ? UPLOAD_URL . e($p['image']) : '/assets/img/placeholder-sock.svg';
        ?>
        <tr>
            <td><img class="thumb-sm" src="<?= $img ?>" alt=""></td>
            <td><?= e($p['name']) ?></td>
            <td><?= e($p['category_name']) ?></td>
            <td><?= formatPrice(effectivePrice($p)) ?></td>
            <td><?= toPersianDigits((string)$p['stock']) ?></td>
            <td><?= $p['is_active'] ? '<span class="status-pill status-delivered">فعال</span>' : '<span class="status-pill status-cancelled">غیرفعال</span>' ?></td>
            <td class="admin-actions">
                <a href="product_edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline">ویرایش</a>
                <form method="post" onsubmit="return confirm('حذف این محصول؟');" style="display:inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?><tr><td colspan="7" style="text-align:center; color:var(--color-muted);">محصولی یافت نشد.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
