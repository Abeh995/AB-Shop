<?php
/**
 * Create/edit a gift box / post-order catalog item.
 */

$id = (int) ($_GET['id'] ?? 0);
$item = null;

if ($id) {
    $stmt = db()->prepare("SELECT * FROM gift_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) redirect('gift_items.php');
}

$pageTitle = $item ? 'ویرایش آیتم' : 'آیتم جدید';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isGiftable = isset($_POST['is_giftable']) ? 1 : 0;
    $isPostOrderable = isset($_POST['is_post_orderable']) ? 1 : 0;
    $costPrice = (int) preg_replace('/\D/', '', $_POST['cost_price'] ?? '0');
    $postOrderPriceRaw = trim($_POST['post_order_price'] ?? '');
    $postOrderPrice = $postOrderPriceRaw === '' ? null : (int) preg_replace('/\D/', '', $postOrderPriceRaw);
    $stock = (int) ($_POST['stock'] ?? 0);

    if ($name === '') $errors[] = 'نام آیتم الزامی است.';
    if ($costPrice < 1) $errors[] = 'قیمت تمام‌شده معتبر وارد کنید.';
    if (!$isGiftable && !$isPostOrderable) $errors[] = 'حداقل یکی از دو حالت «قابل اهدا» یا «قابل فروش به‌عنوان پیشنهاد بعد از سبد» را انتخاب کنید.';
    if ($isPostOrderable && $postOrderPrice === null) $errors[] = 'برای آیتم قابل‌فروش، قیمت پیشنهاد بعد از سبد را وارد کنید.';
    if ($stock < 0) $errors[] = 'موجودی نمی‌تواند منفی باشد.';

    $newImageName = $item['image'] ?? null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // For existing items use the standard name immediately;
        // for new items use a temp name (renamed after INSERT below).
        $uploadResult = $item
            ? handleProductImageUpload($_FILES['image'], 'giftitem', $id, 'main')
            : handleProductImageUpload($_FILES['image']);
        if ($uploadResult['ok']) {
            if ($newImageName && file_exists(UPLOAD_DIR . $newImageName)) {
                @unlink(UPLOAD_DIR . $newImageName);
            }
            $newImageName = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }

    if (empty($errors)) {
        if ($item) {
            $stmt = db()->prepare("UPDATE gift_items SET name=?, image=?, is_active=?, is_giftable=?, is_post_orderable=?, cost_price=?, post_order_price=?, stock=? WHERE id=?");
            $stmt->execute([$name, $newImageName, $isActive, $isGiftable, $isPostOrderable, $costPrice, $postOrderPrice, $stock, $id]);
            setFlash('success', 'آیتم به‌روزرسانی شد.');
        } else {
            $adminId = (int) ($_SESSION['admin_id'] ?? 0);
            $stmt = db()->prepare("INSERT INTO gift_items (name, image, is_active, is_giftable, is_post_orderable, cost_price, post_order_price, stock, created_by) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$name, $newImageName, $isActive, $isGiftable, $isPostOrderable, $costPrice, $postOrderPrice, $stock, $adminId]);
            $newItemId = (int) db()->lastInsertId();

            // Rename the temp-named image now that we have the item id
            if ($newImageName) {
                $renamed = renameUploadedImage($newImageName, 'giftitem', $newItemId, 'main');
                if ($renamed) {
                    db()->prepare("UPDATE gift_items SET image = ? WHERE id = ?")->execute([$renamed, $newItemId]);
                }
            }
            setFlash('success', 'آیتم با موفقیت اضافه شد.');
        }
        redirect('gift_items.php');
    }

    // If there were errors, keep the submitted values so the form re-renders them
    $item = [
        'id' => $id, 'name' => $name, 'image' => $newImageName,
        'is_active' => $isActive, 'is_giftable' => $isGiftable, 'is_post_orderable' => $isPostOrderable,
        'cost_price' => $costPrice, 'post_order_price' => $postOrderPrice, 'stock' => $stock,
    ];
}

renderView('admin/gift_item_edit', compact('pageTitle', 'item', 'errors'));
