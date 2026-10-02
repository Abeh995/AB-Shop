<?php
/**
 * Expense Ledger Service
 *
 * Encapsulates operational expense tracking, category & payment source analytics,
 * receipt image attachments, soft-archiving & restoration, and Excel/CSV exporting.
 *
 * Architectural Invariants:
 * - Pure service layer: all database operations regarding expenses are encapsulated here (Rule 7).
 * - Read-only reporting over financial snapshots and isolated expense mutations.
 * - Image storage handles uploads defensively and supports WebP conversion.
 */

// ==========================================
// 1. Queries & Aggregations
// ==========================================

/**
 * Fetch a paginated and filtered list of expense entries.
 *
 * @param array $filters [q, category, payment_source, expense_nature, status, start_date, end_date]
 * @param int $page
 * @param int $perPage
 * @param string $sort 'date_desc' | 'date_asc' | 'amount_desc' | 'amount_asc'
 * @return array{
 *   items: array, total_count: int, total_pages: int, current_page: int,
 *   per_page: int, total_amount: int, active_count: int, archived_count: int
 * }
 */
function getExpensesList(array $filters = [], int $page = 1, int $perPage = 25, string $sort = 'date_desc'): array
{
    $pdo = db();
    $page = max(1, $page);
    $perPage = max(1, min(100, $perPage));
    $offset = ($page - 1) * $perPage;

    $where = [];
    $params = [];

    // Status filter (active by default)
    $status = trim($filters['status'] ?? 'active');
    if ($status === 'archived') {
        $where[] = "e.status = 'archived'";
    } elseif ($status === 'all') {
        // No status filter
    } else {
        $where[] = "e.status = 'active'";
    }

    $category = trim($filters['category'] ?? '');
    if ($category !== '') {
        $where[] = 'e.category = ?';
        $params[] = $category;
    }

    $paymentSource = trim($filters['payment_source'] ?? '');
    if ($paymentSource !== '') {
        $where[] = 'e.payment_source = ?';
        $params[] = $paymentSource;
    }

    $nature = trim($filters['expense_nature'] ?? '');
    if (in_array($nature, ['fixed', 'variable', 'capital'], true)) {
        $where[] = 'e.expense_nature = ?';
        $params[] = $nature;
    }

    $startDate = trim($filters['start_date'] ?? '');
    if ($startDate !== '') {
        $where[] = 'e.expense_date >= ?';
        $params[] = $startDate;
    }

    $endDate = trim($filters['end_date'] ?? '');
    if ($endDate !== '') {
        $where[] = 'e.expense_date <= ?';
        $params[] = $endDate;
    }

    $search = trim($filters['q'] ?? '');
    if ($search !== '') {
        $where[] = '(e.title LIKE ? OR e.description LIKE ? OR e.payee LIKE ?)';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    $whereSql = !empty($where) ? implode(' AND ', $where) : '1=1';

    // Total filtered sum & count
    $statStmt = $pdo->prepare("
        SELECT COUNT(*) AS total_count, COALESCE(SUM(e.amount), 0) AS total_amount
        FROM expenses e
        WHERE $whereSql
    ");
    $statStmt->execute($params);
    $statRow = $statStmt->fetch();

    $totalCount = (int) ($statRow['total_count'] ?? 0);
    $totalAmount = (int) ($statRow['total_amount'] ?? 0);
    $totalPages = (int) ceil($totalCount / $perPage);

    // Active vs archived overall counts
    $countsStmt = $pdo->query("
        SELECT 
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_cnt,
            SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) AS archived_cnt
        FROM expenses
    ");
    $countsRow = $countsStmt->fetch();
    $activeCount = (int) ($countsRow['active_cnt'] ?? 0);
    $archivedCount = (int) ($countsRow['archived_cnt'] ?? 0);

    // Sorting order
    $orderBy = match ($sort) {
        'date_asc'    => 'e.expense_date ASC, e.id ASC',
        'amount_desc' => 'e.amount DESC, e.id DESC',
        'amount_asc'  => 'e.amount ASC, e.id ASC',
        default       => 'e.expense_date DESC, e.id DESC',
    };

    // Listing query
    $stmt = $pdo->prepare("
        SELECT e.*, a.username AS admin_username
        FROM expenses e 
        LEFT JOIN admins a ON a.id = e.created_by
        WHERE $whereSql 
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    return [
        'items' => $items,
        'total_count' => $totalCount,
        'total_pages' => $totalPages,
        'current_page' => $page,
        'per_page' => $perPage,
        'total_amount' => $totalAmount,
        'active_count' => $activeCount,
        'archived_count' => $archivedCount,
    ];
}

/**
 * Calculate KPI metrics & category/nature distribution breakdown for a filtered period.
 *
 * @param array $filters
 * @return array
 */
function getExpenseSummaryMetrics(array $filters = []): array
{
    $pdo = db();
    $where = [];
    $params = [];

    // Default to active unless explicitly overridden
    $status = trim($filters['status'] ?? 'active');
    if ($status === 'archived') {
        $where[] = "e.status = 'archived'";
    } elseif ($status === 'all') {
        // All
    } else {
        $where[] = "e.status = 'active'";
    }

    $category = trim($filters['category'] ?? '');
    if ($category !== '') {
        $where[] = 'e.category = ?';
        $params[] = $category;
    }

    $paymentSource = trim($filters['payment_source'] ?? '');
    if ($paymentSource !== '') {
        $where[] = 'e.payment_source = ?';
        $params[] = $paymentSource;
    }

    $nature = trim($filters['expense_nature'] ?? '');
    if (in_array($nature, ['fixed', 'variable', 'capital'], true)) {
        $where[] = 'e.expense_nature = ?';
        $params[] = $nature;
    }

    $startDate = trim($filters['start_date'] ?? '');
    if ($startDate !== '') {
        $where[] = 'e.expense_date >= ?';
        $params[] = $startDate;
    }

    $endDate = trim($filters['end_date'] ?? '');
    if ($endDate !== '') {
        $where[] = 'e.expense_date <= ?';
        $params[] = $endDate;
    }

    $search = trim($filters['q'] ?? '');
    if ($search !== '') {
        $where[] = '(e.title LIKE ? OR e.description LIKE ? OR e.payee LIKE ?)';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    $whereSql = !empty($where) ? implode(' AND ', $where) : '1=1';

    // 1. Overall totals
    $totStmt = $pdo->prepare("
        SELECT COUNT(*) AS total_count, COALESCE(SUM(e.amount), 0) AS total_amount
        FROM expenses e
        WHERE $whereSql
    ");
    $totStmt->execute($params);
    $totRow = $totStmt->fetch();

    $totalCount = (int) ($totRow['total_count'] ?? 0);
    $totalAmount = (int) ($totRow['total_amount'] ?? 0);
    $avgAmount = $totalCount > 0 ? (int) round($totalAmount / $totalCount) : 0;

    // 2. Category distribution
    $catStmt = $pdo->prepare("
        SELECT e.category, COUNT(*) AS count, COALESCE(SUM(e.amount), 0) AS amount
        FROM expenses e
        WHERE $whereSql
        GROUP BY e.category
        ORDER BY amount DESC
    ");
    $catStmt->execute($params);
    $catRows = $catStmt->fetchAll();

    $categoryBreakdown = [];
    $topCategory = '—';
    $topCategoryAmount = 0;
    $topCategoryShare = 0.0;

    foreach ($catRows as $idx => $r) {
        $amt = (int) $r['amount'];
        $share = $totalAmount > 0 ? round(($amt / $totalAmount) * 100, 1) : 0.0;
        if ($idx === 0) {
            $topCategory = $r['category'];
            $topCategoryAmount = $amt;
            $topCategoryShare = $share;
        }
        $categoryBreakdown[] = [
            'category' => $r['category'],
            'count' => (int) $r['count'],
            'amount' => $amt,
            'share_percent' => $share,
        ];
    }

    // 3. Nature breakdown (Fixed vs Variable vs Capital)
    $natStmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN e.expense_nature = 'fixed' THEN e.amount ELSE 0 END), 0) AS fixed_amt,
            COALESCE(SUM(CASE WHEN e.expense_nature = 'variable' THEN e.amount ELSE 0 END), 0) AS variable_amt,
            COALESCE(SUM(CASE WHEN e.expense_nature = 'capital' THEN e.amount ELSE 0 END), 0) AS capital_amt
        FROM expenses e
        WHERE $whereSql
    ");
    $natStmt->execute($params);
    $natRow = $natStmt->fetch();

    $fixedAmt = (int) ($natRow['fixed_amt'] ?? 0);
    $variableAmt = (int) ($natRow['variable_amt'] ?? 0);
    $capitalAmt = (int) ($natRow['capital_amt'] ?? 0);

    $natureBreakdown = [
        'fixed' => $fixedAmt,
        'variable' => $variableAmt,
        'capital' => $capitalAmt,
        'fixed_percent' => $totalAmount > 0 ? round(($fixedAmt / $totalAmount) * 100, 1) : 0.0,
        'variable_percent' => $totalAmount > 0 ? round(($variableAmt / $totalAmount) * 100, 1) : 0.0,
        'capital_percent' => $totalAmount > 0 ? round(($capitalAmt / $totalAmount) * 100, 1) : 0.0,
    ];

    // 4. Payment sources breakdown
    $sourceStmt = $pdo->prepare("
        SELECT e.payment_source, COUNT(*) AS count, COALESCE(SUM(e.amount), 0) AS amount
        FROM expenses e
        WHERE $whereSql
        GROUP BY e.payment_source
        ORDER BY amount DESC
    ");
    $sourceStmt->execute($params);
    $sourceBreakdown = [];
    foreach ($sourceStmt->fetchAll() as $sr) {
        $sAmt = (int) $sr['amount'];
        $sourceBreakdown[] = [
            'payment_source' => $sr['payment_source'],
            'count' => (int) $sr['count'],
            'amount' => $sAmt,
            'share_percent' => $totalAmount > 0 ? round(($sAmt / $totalAmount) * 100, 1) : 0.0,
        ];
    }

    return [
        'total_count' => $totalCount,
        'total_amount' => $totalAmount,
        'avg_amount' => $avgAmount,
        'top_category' => $topCategory,
        'top_category_amount' => $topCategoryAmount,
        'top_category_share' => $topCategoryShare,
        'category_breakdown' => $categoryBreakdown,
        'nature_breakdown' => $natureBreakdown,
        'payment_source_breakdown' => $sourceBreakdown,
    ];
}

/**
 * Fetch a single expense record by ID.
 */
function getExpenseById(int $id): ?array
{
    $stmt = db()->prepare("
        SELECT e.*, a.username AS admin_username 
        FROM expenses e 
        LEFT JOIN admins a ON a.id = e.created_by
        WHERE e.id = ?
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// ==========================================
// 2. Mutations (Create / Update / Archive / Restore)
// ==========================================

/**
 * Create or update an operational expense entry.
 *
 * @param array $data Form payload
 * @param array|null $receiptFile Uploaded file array ($_FILES['receipt'] ?? null)
 * @param int $adminId Current admin ID
 * @return array{ok: bool, id?: int, errors?: array}
 */
function saveExpense(array $data, ?array $receiptFile, int $adminId): array
{
    $pdo = db();
    $id = (int) ($data['id'] ?? 0);
    $title = trim($data['title'] ?? '');
    $amount = (int) preg_replace('/\D/', '', (string) ($data['amount'] ?? '0'));
    $expenseDate = trim($data['expense_date'] ?? '');
    $category = trim($data['category'] ?? '');
    $paymentSource = trim($data['payment_source'] ?? 'کارت اصلی فروشگاه');
    $payee = trim($data['payee'] ?? '');
    $nature = trim($data['expense_nature'] ?? 'variable');
    $description = trim($data['description'] ?? '');
    $deleteReceipt = !empty($data['delete_receipt']);

    if (!in_array($nature, ['fixed', 'variable', 'capital'], true)) {
        $nature = 'variable';
    }

    $errors = [];
    if ($title === '') {
        $errors[] = 'عنوان و شرح مختصر هزینه الزامی است.';
    } elseif (mb_strlen($title) > 180) {
        $errors[] = 'عنوان هزینه نباید بیش از ۱۸۰ کاراکتر باشد.';
    }

    if ($amount < 1) {
        $errors[] = 'مبلغ معتبر و مثبت به تومان وارد کنید.';
    }

    if ($category === '') {
        $errors[] = 'دسته‌بندی سرفصل هزینه الزامی است.';
    } elseif (mb_strlen($category) > 60) {
        $errors[] = 'عنوان دسته‌بندی نباید بیش از ۶۰ کاراکتر باشد.';
    }

    if ($paymentSource === '') {
        $paymentSource = 'کارت اصلی فروشگاه';
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expenseDate) || !strtotime($expenseDate)) {
        $errors[] = 'تاریخ معتبر وقوع هزینه را انتخاب کنید.';
    }

    // Existing record check for update
    $existing = null;
    if ($id > 0) {
        $existing = getExpenseById($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['سند هزینه مورد نظر یافت نشد.']];
        }
    }

    // Process receipt image upload if provided
    $receiptFilename = $existing['receipt_image'] ?? null;

    if ($deleteReceipt && $receiptFilename) {
        deleteExpenseReceiptFile($receiptFilename);
        $receiptFilename = null;
    }

    if ($receiptFile && ($receiptFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $uploadResult = uploadExpenseReceipt($receiptFile);
        if (!$uploadResult['ok']) {
            $errors[] = $uploadResult['error'];
        } else {
            // Delete old file if updating
            if (!empty($receiptFilename) && $receiptFilename !== $uploadResult['filename']) {
                deleteExpenseReceiptFile($receiptFilename);
            }
            $receiptFilename = $uploadResult['filename'];
        }
    }

    if (!empty($errors)) {
        return ['ok' => false, 'errors' => $errors];
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE expenses 
            SET title = ?, amount = ?, expense_date = ?, category = ?, 
                payment_source = ?, payee = ?, expense_nature = ?, 
                description = ?, receipt_image = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $title, $amount, $expenseDate, $category,
            $paymentSource, $payee ?: null, $nature,
            $description ?: null, $receiptFilename ?: null,
            $id
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO expenses (
                title, amount, expense_date, category,
                payment_source, payee, expense_nature,
                description, receipt_image, created_by, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([
            $title, $amount, $expenseDate, $category,
            $paymentSource, $payee ?: null, $nature,
            $description ?: null, $receiptFilename ?: null,
            $adminId
        ]);
        $id = (int) $pdo->lastInsertId();
    }

    return ['ok' => true, 'id' => $id];
}

/**
 * Soft-archive an expense entry.
 */
function archiveExpense(int $id): array
{
    $pdo = db();
    $stmt = $pdo->prepare("UPDATE expenses SET status = 'archived' WHERE id = ?");
    $stmt->execute([$id]);
    return ['ok' => true];
}

/**
 * Restore an archived expense entry back to active status.
 */
function restoreExpense(int $id): array
{
    $pdo = db();
    $stmt = $pdo->prepare("UPDATE expenses SET status = 'active' WHERE id = ?");
    $stmt->execute([$id]);
    return ['ok' => true];
}

// ==========================================
// 3. Receipt File Handling
// ==========================================

/**
 * Safely upload and compress an expense receipt image to WebP in uploads/expenses/.
 *
 * @param array $file
 * @return array{ok: bool, filename?: string, error?: string}
 */
function uploadExpenseReceipt(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'بارگذاری تصویر فاکتور با خطا مواجه شد.'];
    }

    $maxBytes = 15 * 1024 * 1024; // 15MB max upload before processing
    if (($file['size'] ?? 0) < 1 || $file['size'] > $maxBytes) {
        return ['ok' => false, 'error' => 'حجم فایل فاکتور بیش از سقف مجاز (۱۵ مگابایت) است.'];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'error' => 'فایل آپلود شده معتبر نیست.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedMimes[$mime])) {
        return ['ok' => false, 'error' => 'فرمت فایل انتخابی معتبر نیست. فقط JPG، PNG و WEBP پشتیبانی می‌شوند.'];
    }

    if (@getimagesize($file['tmp_name']) === false) {
        return ['ok' => false, 'error' => 'فایل انتخابی یک تصویر سالم و معتبر نیست.'];
    }

    $uploadDir = defined('EXPENSE_UPLOAD_DIR') ? EXPENSE_UPLOAD_DIR : (APP_ROOT . '/uploads/expenses/');
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $dateStr = date('Ymd-His');
    $token = bin2hex(random_bytes(3));
    $destFilename = "receipt-exp-{$dateStr}-{$token}.webp";
    $destPath = rtrim($uploadDir, '/\\') . '/' . $destFilename;

    // Convert and compress to WebP (max 1400px, quality 82)
    $converted = compressImageToWebp($file['tmp_name'], $destPath, 1400, 82);
    if (!$converted || !file_exists($destPath)) {
        // Fallback: move file directly if GD conversion failed
        $origExt = $allowedMimes[$mime];
        $destFilename = "receipt-exp-{$dateStr}-{$token}.{$origExt}";
        $destPath = rtrim($uploadDir, '/\\') . '/' . $destFilename;
        if (!@move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['ok' => false, 'error' => 'خطا در ذخیره‌سازی تصویر فاکتور در سرور.'];
        }
    }

    @chmod($destPath, 0644);

    return [
        'ok' => true,
        'filename' => $destFilename,
    ];
}

/**
 * Safely delete an existing receipt file from uploads/expenses/.
 */
function deleteExpenseReceiptFile(?string $filename): void
{
    if (!$filename) return;
    $clean = basename(trim($filename));
    if ($clean === '' || str_contains($clean, '..')) return;

    $uploadDir = defined('EXPENSE_UPLOAD_DIR') ? EXPENSE_UPLOAD_DIR : (APP_ROOT . '/uploads/expenses/');
    $filePath = rtrim($uploadDir, '/\\') . '/' . $clean;
    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

/**
 * Helper to compress source image to WebP using GD library.
 */
function compressImageToWebp(string $sourcePath, string $destinationPath, int $maxDim = 1400, int $quality = 82): bool
{
    if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
        return false;
    }

    $data = @file_get_contents($sourcePath);
    if ($data === false) return false;

    $srcImg = @imagecreatefromstring($data);
    if (!$srcImg) return false;

    $w = imagesx($srcImg);
    $h = imagesy($srcImg);

    if ($w <= 0 || $h <= 0) {
        imagedestroy($srcImg);
        return false;
    }

    // Scale down if exceeds max dimension
    if ($w > $maxDim || $h > $maxDim) {
        if ($w >= $h) {
            $newW = $maxDim;
            $newH = (int) round(($h / $w) * $maxDim);
        } else {
            $newH = $maxDim;
            $newW = (int) round(($w / $h) * $maxDim);
        }
        $targetImg = imagecreatetruecolor($newW, $newH);
        imagealphablending($targetImg, false);
        imagesavealpha($targetImg, true);
        imagecopyresampled($targetImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $w, $h);
        imagedestroy($srcImg);
        $srcImg = $targetImg;
    }

    $saved = imagewebp($srcImg, $destinationPath, $quality);
    imagedestroy($srcImg);

    return $saved;
}

// ==========================================
// 4. Auxiliary Lookups & CSV Export
// ==========================================

/**
 * Fetch distinct active expense categories list with preset fallback.
 */
function getExpenseCategories(): array
{
    $dbCategories = db()->query("
        SELECT DISTINCT category 
        FROM expenses 
        WHERE status = 'active' AND category IS NOT NULL AND category != ''
        ORDER BY category ASC
    ")->fetchAll(PDO::FETCH_COLUMN);

    $defaultSuggestions = [
        'خرید جوراب و کالای فروشگاه',
        'خرید جعبه هدیه و Gift Box',
        'ملزومات بسته‌بندی و پاکت پستی',
        'تجهیزات و سخت‌افزار',
        'هاست، سرور و دامنه',
        'سرویس‌های آنلاین و پنل پیامک',
        'تبلیغات، اینفلوئنسر و مارکتینگ',
        'کرایه حمل‌ونقل و پیک',
        'تعمیرات و نگهداری',
        'سایر مخارج عملیاتی',
    ];

    return array_values(array_unique(array_merge($defaultSuggestions, $dbCategories)));
}

/**
 * Fetch distinct payment sources list with standard preset defaults.
 */
function getExpensePaymentSources(): array
{
    $dbSources = db()->query("
        SELECT DISTINCT payment_source 
        FROM expenses 
        WHERE payment_source IS NOT NULL AND payment_source != ''
        ORDER BY payment_source ASC
    ")->fetchAll(PDO::FETCH_COLUMN);

    $defaultSources = [
        'کارت اصلی فروشگاه',
        'حساب تجاری و تسویه درگاه',
        'کارت شخصی مدیر',
        'تنخواه نقدی فروشگاه',
        'صندوق و مانده نقدی',
    ];

    return array_values(array_unique(array_merge($defaultSources, $dbSources)));
}

/**
 * Fetch distinct payees (vendors, workshops, contractors) for datalist suggestions.
 */
function getExpensePayees(): array
{
    return db()->query("
        SELECT DISTINCT payee 
        FROM expenses 
        WHERE payee IS NOT NULL AND payee != ''
        ORDER BY payee ASC
    ")->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Stream a comprehensive UTF-8 BOM CSV export of filtered expense entries.
 */
function exportExpensesCsv(array $filters = []): void
{
    // Fetch all matching records without pagination limit
    $pdo = db();
    $where = [];
    $params = [];

    $status = trim($filters['status'] ?? 'active');
    if ($status === 'archived') {
        $where[] = "e.status = 'archived'";
    } elseif ($status === 'all') {
        // all
    } else {
        $where[] = "e.status = 'active'";
    }

    $category = trim($filters['category'] ?? '');
    if ($category !== '') {
        $where[] = 'e.category = ?';
        $params[] = $category;
    }

    $paymentSource = trim($filters['payment_source'] ?? '');
    if ($paymentSource !== '') {
        $where[] = 'e.payment_source = ?';
        $params[] = $paymentSource;
    }

    $nature = trim($filters['expense_nature'] ?? '');
    if (in_array($nature, ['fixed', 'variable', 'capital'], true)) {
        $where[] = 'e.expense_nature = ?';
        $params[] = $nature;
    }

    $startDate = trim($filters['start_date'] ?? '');
    if ($startDate !== '') {
        $where[] = 'e.expense_date >= ?';
        $params[] = $startDate;
    }

    $endDate = trim($filters['end_date'] ?? '');
    if ($endDate !== '') {
        $where[] = 'e.expense_date <= ?';
        $params[] = $endDate;
    }

    $search = trim($filters['q'] ?? '');
    if ($search !== '') {
        $where[] = '(e.title LIKE ? OR e.description LIKE ? OR e.payee LIKE ?)';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    $whereSql = !empty($where) ? implode(' AND ', $where) : '1=1';

    $stmt = $pdo->prepare("
        SELECT e.*, a.username AS admin_username
        FROM expenses e 
        LEFT JOIN admins a ON a.id = e.created_by
        WHERE $whereSql 
        ORDER BY e.expense_date DESC, e.id DESC
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $natureLabels = [
        'fixed'    => 'ثابت بالاسری',
        'variable' => 'متغیر عملیاتی',
        'capital'  => 'سرمایه‌ای / تجهیزات',
    ];

    $filename = 'ab-socks-expenses-' . date('Y-m-d-His') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    // UTF-8 BOM for Microsoft Excel compatibility
    fputs($out, "\xEF\xBB\xBF");

    // Header row
    fputcsv($out, [
        'شناسه سند',
        'تاریخ ثبت (میلادی)',
        'تاریخ شمسی',
        'عنوان و شرح هزینه',
        'طرف حساب / فروشنده',
        'دسته‌بندی سرفصل',
        'ماهیت هزینه',
        'منبع پرداخت',
        'مبلغ هزینه (تومان)',
        'ثبت‌کننده',
        'وضعیت سند',
        'دارای تصویر فاکتور',
        'توضیحات تکمیلی',
    ]);

    foreach ($rows as $r) {
        $shamsiDate = appDateTime($r['expense_date'], 'shamsi_date');
        $natLabel = $natureLabels[$r['expense_nature'] ?? 'variable'] ?? 'متغیر';
        $hasReceipt = !empty($r['receipt_image']) ? 'بله' : 'خیر';
        $statusLabel = ($r['status'] === 'active') ? 'فعال' : 'بایگانی‌شده';

        fputcsv($out, [
            $r['id'],
            $r['expense_date'],
            $shamsiDate,
            $r['title'],
            $r['payee'] ?? '—',
            $r['category'],
            $natLabel,
            $r['payment_source'],
            (int) $r['amount'],
            $r['admin_username'] ?? 'سیستم',
            $statusLabel,
            $hasReceipt,
            $r['description'] ?? '',
        ]);
    }

    fclose($out);
    exit;
}
