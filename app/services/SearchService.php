<?php
declare(strict_types=1);

/**
 * SearchService — Centralized Admin Search Provider Registry (SSoT).
 *
 * Implements extensible provider registration for entity searches:
 * - 'pages': Navigation topics and admin pages
 * - 'orders': Orders by code, customer name, phone
 * - 'products': Products by name, SKU with stock snapshot
 * - 'tags': Catalog tags and SEO taxonomy
 * - 'customers': Customer accounts by name, phone, email
 */
class SearchService
{
    /** @var array<string, callable> */
    private static array $providers = [];
    private static bool $initialized = false;

    /**
     * Register a new search provider closure.
     *
     * @param string $type
     * @param callable $handler fn(string $query, int $limit): array
     */
    public static function registerProvider(string $type, callable $handler): void
    {
        self::$providers[$type] = $handler;
    }

    /**
     * Returns list of all registered search provider keys.
     *
     * @return string[]
     */
    public static function getRegisteredProviders(): array
    {
        self::ensureInitialized();
        return array_keys(self::$providers);
    }

    /**
     * Executes search for a specific provider or global hub.
     *
     * @param string $query
     * @param string|null $type Specific provider (e.g. 'tags', 'products') or null for global hub.
     * @param int $limit
     * @return array
     */
    public static function search(string $query, ?string $type = null, int $limit = 10): array
    {
        self::ensureInitialized();
        $query = trim($query);
        $limit = max(1, min(50, $limit));

        if ($type !== null && $type !== '' && $type !== 'all') {
            if (isset(self::$providers[$type])) {
                $results = (self::$providers[$type])($query, $limit);
                return [
                    'ok' => true,
                    'type' => $type,
                    'query' => $query,
                    'total' => count($results),
                    'results' => $results,
                ];
            }
            return [
                'ok' => false,
                'error' => "Search provider '{$type}' is not registered.",
                'results' => [],
            ];
        }

        // Global multi-hub search (backward compatible with admin.js global palette)
        $pages = isset(self::$providers['pages']) ? (self::$providers['pages'])($query, min(5, $limit)) : [];
        $orders = isset(self::$providers['orders']) ? (self::$providers['orders'])($query, min(5, $limit)) : [];
        $products = isset(self::$providers['products']) ? (self::$providers['products'])($query, min(5, $limit)) : [];

        return [
            'ok' => true,
            'query' => $query,
            'pages' => $pages,
            'orders' => $orders,
            'products' => $products,
        ];
    }

    /**
     * Initializes default core providers.
     */
    private static function ensureInitialized(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        self::registerProvider('pages', function (string $query, int $limit): array {
            return self::searchPages($query, $limit);
        });

        self::registerProvider('orders', function (string $query, int $limit): array {
            return self::searchOrders($query, $limit);
        });

        self::registerProvider('products', function (string $query, int $limit): array {
            return self::searchProducts($query, $limit);
        });

        self::registerProvider('tags', function (string $query, int $limit): array {
            return self::searchTags($query, $limit);
        });

        self::registerProvider('customers', function (string $query, int $limit): array {
            return self::searchCustomers($query, $limit);
        });
    }

    /**
     * Search static admin workstation pages and navigation keywords.
     */
    public static function searchPages(string $query, int $limit = 10): array
    {
        $pages = [
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
                'title' => 'مدیریت کاتالوگ محصولات',
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
                'title' => 'دسته‌بندی‌های کالا',
                'url' => 'categories.php',
                'badge' => 'محصولات',
                'keywords' => 'دسته بندی گروه شاخه کالایی مردانه زنانه بچگانه',
            ],
            [
                'title' => 'برچسب‌ها و تگ‌های سئو',
                'url' => 'tags.php',
                'badge' => 'محصولات',
                'keywords' => 'برچسب تگ سئو taxonomy برچسب ها کلمات کلیدی',
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
                'title' => 'کدهای تخفیف و پروموشن',
                'url' => 'coupons.php',
                'badge' => 'تخفیف',
                'keywords' => 'کوپن کد تخفیف آفر یلدا نوروز کمپین کوپنز',
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
                'keywords' => 'تنظیمات درگاه پرداخت زرین پال شماره کارت تماس درباره ما قوانین سئو جستجو',
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
                'title' => 'الگوهای پیامک (Faraz SMS)',
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

        if (function_exists('isSuperAdmin') && isSuperAdmin()) {
            $pages[] = [
                'title' => 'مدیریت مدیران و دسترسی‌ها',
                'url' => 'users.php',
                'badge' => 'مدیریت',
                'keywords' => 'مدیر ادمین ادمین ها رمز کارمندان سطح دسترسی امنیت',
            ];
            $pages[] = [
                'title' => 'عیب‌یابی سیستم و لاگ‌ها',
                'url' => 'diagnostics.php',
                'badge' => 'مدیریت',
                'keywords' => 'عیب یابی لاگ پیامک لاگ ایمیل تست سیستم پترن موجودی پیامک',
            ];
        }

        if ($query === '') {
            return array_slice($pages, 0, $limit);
        }

        $matched = [];
        foreach ($pages as $p) {
            if (
                mb_stripos($p['title'], $query) !== false ||
                mb_stripos($p['keywords'], $query) !== false ||
                mb_stripos($p['badge'], $query) !== false
            ) {
                $matched[] = [
                    'id' => $p['url'],
                    'title' => $p['title'],
                    'url' => $p['url'],
                    'badge' => $p['badge'],
                ];
                if (count($matched) >= $limit) {
                    break;
                }
            }
        }
        return $matched;
    }

    /**
     * Search orders by code, customer name, or customer phone.
     */
    public static function searchOrders(string $query, int $limit = 10): array
    {
        if ($query === '') {
            return [];
        }

        $qLike = '%' . $query . '%';
        $statusLabels = [
            'pending'    => 'در انتظار بررسی',
            'confirmed'  => 'تایید شده',
            'processing' => 'در حال آماده‌سازی',
            'shipped'    => 'ارسال شده',
            'delivered'  => 'تحویل شده',
            'cancelled'  => 'لغو شده',
        ];

        try {
            $stmt = db()->prepare("
                SELECT id, order_code, customer_name, phone, total, status, created_at
                FROM orders
                WHERE order_code LIKE ? OR customer_name LIKE ? OR phone LIKE ?
                ORDER BY id DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $qLike, PDO::PARAM_STR);
            $stmt->bindValue(2, $qLike, PDO::PARAM_STR);
            $stmt->bindValue(3, $qLike, PDO::PARAM_STR);
            $stmt->bindValue(4, $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            $results = [];
            foreach ($rows as $o) {
                $results[] = [
                    'id' => (int) $o['id'],
                    'order_code' => $o['order_code'],
                    'title' => '#' . $o['order_code'] . ' — ' . $o['customer_name'],
                    'customer_name' => $o['customer_name'],
                    'customer_phone' => $o['phone'],
                    'total_amount' => (int) $o['total'],
                    'total_amount_formatted' => formatPrice($o['total']),
                    'status' => $o['status'],
                    'status_label' => $statusLabels[$o['status']] ?? $o['status'],
                    'url' => 'order_detail.php?id=' . (int) $o['id'],
                ];
            }
            return $results;
        } catch (Exception $e) {
            error_log("SearchService orders error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search products by name or SKU with snapshot of effective stock.
     */
    public static function searchProducts(string $query, int $limit = 10): array
    {
        $qLike = '%' . $query . '%';
        $stockSql = function_exists('effectiveStockSqlFragment')
            ? effectiveStockSqlFragment('products')
            : 'products.stock';

        try {
            if ($query === '') {
                $stmt = db()->prepare("
                    SELECT id, name, sku, price, image, $stockSql AS effective_stock
                    FROM products
                    ORDER BY id DESC
                    LIMIT ?
                ");
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            } else {
                $stmt = db()->prepare("
                    SELECT id, name, sku, price, image, $stockSql AS effective_stock
                    FROM products
                    WHERE name LIKE ? OR sku LIKE ?
                    ORDER BY id DESC
                    LIMIT ?
                ");
                $stmt->bindValue(1, $qLike, PDO::PARAM_STR);
                $stmt->bindValue(2, $qLike, PDO::PARAM_STR);
                $stmt->bindValue(3, $limit, PDO::PARAM_INT);
            }
            $stmt->execute();

            $rows = $stmt->fetchAll();
            $results = [];
            foreach ($rows as $pr) {
                $results[] = [
                    'id' => (int) $pr['id'],
                    'title' => $pr['name'],
                    'name' => $pr['name'],
                    'sku' => $pr['sku'] ?: '',
                    'price' => (int) $pr['price'],
                    'price_formatted' => formatPrice($pr['price']),
                    'stock' => (int) $pr['effective_stock'],
                    'image_url' => !empty($pr['image']) ? (UPLOAD_URL . $pr['image']) : '/assets/img/placeholder-sock.svg',
                    'url' => 'product_edit.php?id=' . (int) $pr['id'],
                ];
            }
            return $results;
        } catch (Exception $e) {
            error_log("SearchService products error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search tags by name or slug with associated products count.
     */
    public static function searchTags(string $query, int $limit = 15): array
    {
        try {
            if ($query === '') {
                $stmt = db()->prepare("
                    SELECT t.id, t.name, t.slug, COUNT(pt.product_id) AS products_count
                    FROM tags t
                    LEFT JOIN product_tags pt ON pt.tag_id = t.id
                    GROUP BY t.id, t.name, t.slug
                    ORDER BY products_count DESC, t.name ASC
                    LIMIT ?
                ");
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            } else {
                $qLike = '%' . $query . '%';
                $stmt = db()->prepare("
                    SELECT t.id, t.name, t.slug, COUNT(pt.product_id) AS products_count
                    FROM tags t
                    LEFT JOIN product_tags pt ON pt.tag_id = t.id
                    WHERE t.name LIKE ? OR t.slug LIKE ?
                    GROUP BY t.id, t.name, t.slug
                    ORDER BY products_count DESC, t.name ASC
                    LIMIT ?
                ");
                $stmt->bindValue(1, $qLike, PDO::PARAM_STR);
                $stmt->bindValue(2, $qLike, PDO::PARAM_STR);
                $stmt->bindValue(3, $limit, PDO::PARAM_INT);
            }
            $stmt->execute();

            $rows = $stmt->fetchAll();
            $results = [];
            foreach ($rows as $t) {
                $results[] = [
                    'id' => (int) $t['id'],
                    'title' => $t['name'],
                    'name' => $t['name'],
                    'slug' => $t['slug'] ?? '',
                    'products_count' => (int) $t['products_count'],
                ];
            }
            return $results;
        } catch (Exception $e) {
            error_log("SearchService tags error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search customers by name, phone, or email.
     */
    public static function searchCustomers(string $query, int $limit = 10): array
    {
        if ($query === '') {
            return [];
        }

        $qLike = '%' . $query . '%';
        try {
            $stmt = db()->prepare("
                SELECT id, phone, email, full_name, created_at
                FROM customers
                WHERE phone LIKE ? OR full_name LIKE ? OR email LIKE ?
                ORDER BY id DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $qLike, PDO::PARAM_STR);
            $stmt->bindValue(2, $qLike, PDO::PARAM_STR);
            $stmt->bindValue(3, $qLike, PDO::PARAM_STR);
            $stmt->bindValue(4, $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            $results = [];
            foreach ($rows as $c) {
                $results[] = [
                    'id' => (int) $c['id'],
                    'title' => ($c['full_name'] ?: 'بدون نام') . ' (' . $c['phone'] . ')',
                    'name' => $c['full_name'] ?: '',
                    'phone' => $c['phone'],
                    'email' => $c['email'] ?: '',
                ];
            }
            return $results;
        } catch (Exception $e) {
            error_log("SearchService customers error: " . $e->getMessage());
            return [];
        }
    }
}
