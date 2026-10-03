<?php
/**
 * Admin Coupon Promotions controller.
 * Encapsulates coupon CRUD, activation toggles, usage limits, and financial reporting (Rule 7).
 */

$pageTitle = 'کدهای تخفیف و پروموشن‌ها';
$error = null;
$success = null;

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_GET['ajax']) && $_GET['ajax'] === '1')
    || (isset($_POST['ajax']) && $_POST['ajax'] === '1');

// AJAX endpoint for coupon performance drawer
if ($isAjax && isset($_GET['action']) && $_GET['action'] === 'performance') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_GET['id'] ?? 0);
    $data = CouponService::getCouponPerformance($id);
    echo json_encode(['ok' => true, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'generate_code') {
        header('Content-Type: application/json; charset=utf-8');
        $prefix = trim($_POST['prefix'] ?? 'OFF');
        echo json_encode(['ok' => true, 'code' => CouponService::generateRandomCode($prefix)]);
        exit;
    }

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

$filters = [
    'q' => trim($_GET['q'] ?? ''),
    'status' => trim($_GET['status'] ?? 'all'),
    'sort' => trim($_GET['sort'] ?? 'newest'),
];

$coupons = CouponService::getAllFiltered($filters);
$editId = (int)($_GET['edit'] ?? 0);
$editCoupon = $editId ? CouponService::getById($editId) : null;
$overviewStats = CouponService::getOverviewStats();
$categories = getCategoriesForDropdown();

renderView('admin/coupons', compact('pageTitle', 'coupons', 'editCoupon', 'overviewStats', 'categories', 'filters', 'error', 'success'));
