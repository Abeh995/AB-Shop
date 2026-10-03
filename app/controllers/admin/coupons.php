<?php
/**
 * Admin Coupon Promotions controller.
 * Encapsulates coupon CRUD, activation toggles, and usage limits (Rule 7).
 */

$pageTitle = 'کدهای تخفیف و پروموشن‌ها';
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'save') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $res = CouponService::save($_POST, $id);
        if ($res['ok']) {
            redirect('/admin/coupons.php?saved=1');
        }
        $error = $res['error'];
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $res = CouponService::toggle($id);
        redirect('/admin/coupons.php' . ($res['ok'] ? '?toggled=1' : '&error=' . urlencode($res['error'] ?? '')));
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $res = CouponService::delete($id);
        redirect('/admin/coupons.php' . ($res['ok'] ? '?deleted=1' : ''));
    }
}

$coupons = CouponService::getAll();
$editId = (int)($_GET['edit'] ?? 0);
$editCoupon = $editId ? CouponService::getById($editId) : null;

// Calculate high-level KPI stats
$activeCount = 0;
$totalUsed = 0;
foreach ($coupons as $c) {
    if (!empty($c['is_active'])) $activeCount++;
    $totalUsed += (int)($c['used_count'] ?? 0);
}

renderView('admin/coupons', compact('pageTitle', 'coupons', 'editCoupon', 'activeCount', 'totalUsed', 'error', 'success'));
