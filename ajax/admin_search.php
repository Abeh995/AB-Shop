<?php
/**
 * Admin global live search endpoint (FEAT-A004).
 * Secure, authenticated AJAX endpoint for fast searches across:
 * 1. Admin pages & settings topics
 * 2. Orders (by code, customer name, phone, tracking code)
 * 3. Products (by name, SKU)
 */

require_once __DIR__ . '/../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

// Strictly enforce admin authentication
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'دسترسی غیرمجاز.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'روش درخواست نامعتبر است.']);
    exit;
}

$rawQuery = trim($_GET['q'] ?? '');
if (mb_strlen($rawQuery) < 1) {
    echo json_encode([
        'ok' => true,
        'query' => '',
        'pages' => [],
        'orders' => [],
        'products' => [],
    ]);
    exit;
}

$qNormalized = mb_strtolower($rawQuery, 'UTF-8');
$qLike = '%' . $rawQuery . '%';

// 1. Static searchable Admin Pages & Topics catalog
$adminPages = [
    [
        'title' => 'داشبورد مدیریت',
        'url' => 'index.php',
        'badge' => 'داشبورد',
        'keywords' => 'داشبورد آمار نمودار خلاصه فروش گزارش وضعیت فروشگاه',
    ],
    [
        'title' => 'لیست سفارش‌ها',
        'url' => 'orders.php',
        'badge' => 'سفارش‌ها',
        'keywords' => 'سفارشات سفارش خرید فاکتور سبد مشتری لیست',
    ],
    [
        'title' => 'بررسی پرداخت‌های کارت‌به‌کارت',
        'url' => 'card_to_card_payments.php',
        'badge' => 'سفارش‌ها',
        'keywords' => 'کارت به کارت رسید فیش واریز تایید بانکی شماره کارت',
    ],
    [
        'title' => 'مدیریت محصولات',
        'url' => 'products.php',
        'badge' => 'محصولات',
        'keywords' => 'محصول کالا جوراب انبار موجودی قیمت سایز جنس لیست محصولات',
    ],
    [
        'title' => 'پیشنهاد ویژه محصولات',
        'url' => 'products.php?featured=1',
        'badge' => 'محصولات',
        'keywords' => 'پیشنهاد ویژه تخفیف ستاره برگزیده منتخب',
    ],
    [
        'title' => 'دسته‌بندی‌ها',
        'url' => 'categories.php',
        'badge' => 'محصولات',
        'keywords' => 'دسته بندی گروه شاخه کالایی مردانه زنانه بچگانه',
    ],
    [
        'title' => 'تغییر قیمت گروهی',
        'url' => 'pricing.php',
        'badge' => 'محصولات',
        'keywords' => 'تغییر قیمت گروهی تورم افزایش تخفیف هزینه خرید فروش',
    ],
    [
        'title' => 'هدیه و آفر بعد از سبد خرید',
        'url' => 'gift_items.php',
        'badge' => 'محصولات',
        'keywords' => 'هدیه گیفت باکس جعبه اشانتیون پیشنهاد بعد از سبد خرید',
    ],
    [
        'title' => 'داشبورد مالی و سود و زیان',
        'url' => 'finance_dashboard.php',
        'badge' => 'مالی',
        'keywords' => 'مالی حسابداری سود زیان درآمد سود خالص فروش کل',
    ],
    [
        'title' => 'مدیریت هزینه‌ها',
        'url' => 'expenses.php',
        'badge' => 'مالی',
        'keywords' => 'هزینه خرج فاکتور خرید بسته بندی اجاره هاستینگ تبلیغات',
    ],
    [
        'title' => 'تنظیمات عمومی فروشگاه',
        'url' => 'settings.php',
        'badge' => 'تنظیمات',
        'keywords' => 'تنظیمات درگاه پرداخت زرین پال شماره کارت تماس درباره ما قوانین سئو جستجو تگ ها',
    ],
    [
        'title' => 'ظاهر و صفحه اصلی (Landing Page)',
        'url' => 'appearance.php',
        'badge' => 'تنظیمات',
        'keywords' => 'ظاهر صفحه اصلی لندینگ اسلایدر بنر تم پالت رنگ لوگو برندینگ نوار اعلان فوتر',
    ],
    [
        'title' => 'قالب‌ها و پالت‌های رنگ سایت',
        'url' => 'themes.php',
        'badge' => 'تنظیمات',
        'keywords' => 'تم رنگ قالب ظاهر استایل پالت رنگی فونت',
    ],
    [
        'title' => 'الگوهای پیامک (Faraz SMS Patterns)',
        'url' => 'sms_patterns.php',
        'badge' => 'تنظیمات',
        'keywords' => 'پیامک اس ام اس sms الگو پترن فراز کد تایید otp ورود ثبت نام',
    ],
    [
        'title' => 'حساب‌های ایمیل (SMTP)',
        'url' => 'email_accounts.php',
        'badge' => 'تنظیمات',
        'keywords' => 'ایمیل میل smtp سرور ارسال میل پورت هاست',
    ],
    [
        'title' => 'روش‌های ارسال و پست',
        'url' => 'shipping_methods.php',
        'badge' => 'تنظیمات',
        'keywords' => 'ارسال پست پیشتاز تیپاکس پیک هزینه باربری ارسال رایگان',
    ],
];

if (isSuperAdmin()) {
    $adminPages[] = [
        'title' => 'مدیریت مدیران و دسترسی‌ها',
        'url' => 'users.php',
        'badge' => 'مدیریت',
        'keywords' => 'مدیر ادمین ادمین ها رمز کارمندان سطح دسترسی',
    ];
    $adminPages[] = [
        'title' => 'عیب‌یابی سیستم و لاگ‌ها',
        'url' => 'diagnostics.php',
        'badge' => 'مدیریت',
        'keywords' => 'عیب یابی لاگ پیامک لاگ ایمیل تست سیستم پترن موجودی پیامک',
    ];
}

$matchedPages = [];
foreach ($adminPages as $p) {
    if (
        mb_stripos($p['title'], $rawQuery) !== false ||
        mb_stripos($p['keywords'], $rawQuery) !== false ||
        mb_stripos($p['badge'], $rawQuery) !== false
    ) {
        $matchedPages[] = [
            'title' => $p['title'],
            'url' => $p['url'],
            'badge' => $p['badge'],
        ];
    }
}

// 2. Search Orders (up to 5 rows)
$matchedOrders = [];
try {
    $stmt = db()->prepare("
        SELECT id, order_code, customer_name, customer_phone, total_amount, status, created_at
        FROM orders
        WHERE order_code LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?
        ORDER BY id DESC
        LIMIT 5
    ");
    $stmt->execute([$qLike, $qLike, $qLike]);
    $orderRows = $stmt->fetchAll();

    $statusLabels = [
        'pending'    => 'در انتظار بررسی',
        'confirmed'  => 'تایید شده',
        'processing' => 'در حال آماده‌سازی',
        'shipped'    => 'ارسال شده',
        'delivered'  => 'تحویل شده',
        'cancelled'  => 'لغو شده',
    ];

    foreach ($orderRows as $o) {
        $matchedOrders[] = [
            'id' => (int) $o['id'],
            'order_code' => $o['order_code'],
            'customer_name' => $o['customer_name'],
            'customer_phone' => $o['customer_phone'],
            'total_amount_formatted' => formatPrice($o['total_amount']),
            'status' => $o['status'],
            'status_label' => $statusLabels[$o['status']] ?? $o['status'],
            'url' => 'order_detail.php?id=' . (int) $o['id'],
        ];
    }
} catch (Exception $e) {
    error_log("Admin search order error: " . $e->getMessage());
}

// 3. Search Products (up to 5 rows)
$matchedProducts = [];
try {
    $stockSql = effectiveStockSqlFragment('products');
    $stmt = db()->prepare("
        SELECT id, name, sku, price, image, $stockSql AS effective_stock
        FROM products
        WHERE name LIKE ? OR sku LIKE ?
        ORDER BY id DESC
        LIMIT 5
    ");
    $stmt->execute([$qLike, $qLike]);
    $prodRows = $stmt->fetchAll();

    foreach ($prodRows as $pr) {
        $matchedProducts[] = [
            'id' => (int) $pr['id'],
            'name' => $pr['name'],
            'sku' => $pr['sku'] ?: '',
            'price_formatted' => formatPrice($pr['price']),
            'stock' => (int) $pr['effective_stock'],
            'image_url' => productImageUrl($pr['image']),
            'url' => 'product_edit.php?id=' . (int) $pr['id'],
        ];
    }
} catch (Exception $e) {
    error_log("Admin search product error: " . $e->getMessage());
}

echo json_encode([
    'ok' => true,
    'query' => $rawQuery,
    'pages' => $matchedPages,
    'orders' => $matchedOrders,
    'products' => $matchedProducts,
], JSON_UNESCAPED_UNICODE);
