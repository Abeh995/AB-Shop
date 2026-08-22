<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$pageTitle = 'دسته‌بندی‌ها';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            setFlash('error', 'نام دسته‌بندی الزامی است.');
        } else {
            $slug = slugify($name);
            $check = db()->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
            $check->execute([$slug, (int)($_POST['id'] ?? 0)]);
            if ($check->fetch()) {
                $slug .= '-' . substr(md5(uniqid('', true)), 0, 4);
            }

            if ($action === 'create') {
                $stmt = db()->prepare("INSERT INTO categories (name, slug, description, sort_order, is_active) VALUES (?,?,?,?,?)");
                $stmt->execute([$name, $slug, $description, $sortOrder, $isActive]);
                setFlash('success', 'دسته‌بندی با موفقیت اضافه شد.');
            } else {
                $id = (int) ($_POST['id'] ?? 0);
                $stmt = db()->prepare("UPDATE categories SET name=?, slug=?, description=?, sort_order=?, is_active=? WHERE id=?");
                $stmt->execute([$name, $slug, $description, $sortOrder, $isActive, $id]);
                setFlash('success', 'دسته‌بندی به‌روزرسانی شد.');
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $has = db()->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $has->execute([$id]);
        if ($has->fetchColumn() > 0) {
            setFlash('error', 'این دسته‌بندی دارای محصول است و قابل حذف نیست. ابتدا محصولات را جابه‌جا یا حذف کنید.');
        } else {
            $stmt = db()->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'دسته‌بندی حذف شد.');
        }
    }
    redirect('categories.php');
}

$categories = db()->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
                            FROM categories c ORDER BY sort_order ASC")->fetchAll();

require __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-card">
    <h3 style="margin-bottom:14px;">افزودن دسته‌بندی جدید</h3>
    <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="create">
        <div class="form-row">
            <div class="form-group">
                <label>نام دسته‌بندی</label>
                <input class="form-control" type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>ترتیب نمایش</label>
                <input class="form-control" type="number" name="sort_order" value="0">
            </div>
        </div>
        <div class="form-group">
            <label>توضیحات (اختیاری)</label>
            <textarea class="form-control" name="description"></textarea>
        </div>
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
            <input type="checkbox" name="is_active" checked> فعال باشد
        </label>
        <button type="submit" class="btn btn-primary">افزودن</button>
    </form>
</div>

<div class="admin-card">
    <h3 style="margin-bottom:14px;">لیست دسته‌بندی‌ها</h3>
    <table class="admin-table">
        <thead><tr><th>نام</th><th>تعداد محصول</th><th>وضعیت</th><th>ترتیب</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($categories as $c): ?>
        <tr>
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                <td><input class="form-control" type="text" name="name" value="<?= e($c['name']) ?>" style="min-width:160px;"></td>
                <td><?= toPersianDigits((string)$c['product_count']) ?></td>
                <td><label style="display:flex; align-items:center; gap:6px;"><input type="checkbox" name="is_active" <?= $c['is_active'] ? 'checked' : '' ?>> فعال</label></td>
                <td><input class="form-control" type="number" name="sort_order" value="<?= (int)$c['sort_order'] ?>" style="width:70px;"></td>
                <td class="admin-actions">
                    <input type="hidden" name="description" value="<?= e($c['description']) ?>">
                    <button type="submit" class="btn btn-sm btn-outline">ذخیره</button>
            </form>
                    <form method="post" onsubmit="return confirm('حذف این دسته‌بندی؟');" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                    </form>
                </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
