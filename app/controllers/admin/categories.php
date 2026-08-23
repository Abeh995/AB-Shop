<?php
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

renderView('admin/categories', compact('pageTitle', 'categories'));
