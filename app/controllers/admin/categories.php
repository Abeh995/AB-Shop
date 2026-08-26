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
        $parentId = (int) ($_POST['parent_id'] ?? 0);
        $currentId = (int) ($_POST['id'] ?? 0);
        $parentId = $parentId > 0 ? $parentId : null;

        // Prevent a category from being assigned as its own parent.
        if ($parentId !== null && $parentId === $currentId) {
            $parentId = null;
        }

        if ($name === '') {
            setFlash('error', 'نام دسته‌بندی الزامی است.');
        } else {
            $slug = slugify($name);
            $check = db()->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
            $check->execute([$slug, $currentId]);
            if ($check->fetch()) {
                $slug .= '-' . substr(md5(uniqid('', true)), 0, 4);
            }

            if ($action === 'create') {
                $stmt = db()->prepare("INSERT INTO categories (parent_id, name, slug, description, sort_order, is_active) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$parentId, $name, $slug, $description, $sortOrder, $isActive]);
                setFlash('success', 'دسته‌بندی با موفقیت اضافه شد.');
            } else {
                $stmt = db()->prepare("UPDATE categories SET parent_id=?, name=?, slug=?, description=?, sort_order=?, is_active=? WHERE id=?");
                $stmt->execute([$parentId, $name, $slug, $description, $sortOrder, $isActive, $currentId]);
                setFlash('success', 'دسته‌بندی به‌روزرسانی شد.');
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $has = db()->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $has->execute([$id]);
        $hasChildren = db()->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
        $hasChildren->execute([$id]);

        if ($has->fetchColumn() > 0) {
            setFlash('error', 'این دسته‌بندی دارای محصول است و قابل حذف نیست. ابتدا محصولات را جابه‌جا یا حذف کنید.');
        } elseif ($hasChildren->fetchColumn() > 0) {
            setFlash('error', 'این دسته‌بندی دارای زیردسته است و قابل حذف نیست. ابتدا زیردسته‌ها را حذف یا جابه‌جا کنید.');
        } else {
            $stmt = db()->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'دسته‌بندی حذف شد.');
        }
    }
    redirect('categories.php');
}

$categoriesFlat = db()->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
                            FROM categories c ORDER BY sort_order ASC")->fetchAll();

// Build a hierarchical table view (parent followed by its subcategories).
$byParent = [];
foreach ($categoriesFlat as $cat) {
    $byParent[$cat['parent_id'] ?? 0][] = $cat;
}
$categories = [];
$walkCategories = function ($parentId, $depth) use (&$walkCategories, &$byParent, &$categories) {
    foreach ($byParent[$parentId] ?? [] as $cat) {
        $cat['depth'] = $depth;
        $categories[] = $cat;
        $walkCategories($cat['id'], $depth + 1);
    }
};
$walkCategories(0, 0);

// Build the parent selector; only top-level categories can be parents to keep nesting one level deep.
$topLevelCategories = array_values(array_filter($categoriesFlat, fn($c) => $c['parent_id'] === null));

renderView('admin/categories', compact('pageTitle', 'categories', 'topLevelCategories'));
