<?php
/**
 * AB-Socks SMS Patterns — Filter Toolbar & Search Bar
 * @var array $patterns
 */
$catCounts = [
    'all'    => count($patterns),
    'auth'   => 0,
    'orders' => 0,
    'c2c'    => 0,
    'admin'  => 0,
    'unset'  => 0,
];

foreach ($patterns as $p) {
    $cat = getSmsEventCategory($p['event_key'] ?? '');
    if (isset($catCounts[$cat])) {
        $catCounts[$cat]++;
    }
    if (empty($p['pattern_code']) || $p['pattern_code'] === 'unset') {
        $catCounts['unset']++;
    }
}
?>
<div class="sms-toolbar-card">
    <!-- Category Tabs -->
    <div class="sms-nav-pills">
        <button type="button" class="sms-tab-btn active" data-filter="all">
            <span>🌐</span> همه الگوها <span class="badge-count"><?= toPersianDigits((string)$catCounts['all']) ?></span>
        </button>
        <button type="button" class="sms-tab-btn" data-filter="auth">
            <span>🔐</span> احراز هویت (OTP) <span class="badge-count"><?= toPersianDigits((string)$catCounts['auth']) ?></span>
        </button>
        <button type="button" class="sms-tab-btn" data-filter="orders">
            <span>🛍️</span> سفارشات مشتری <span class="badge-count"><?= toPersianDigits((string)$catCounts['orders']) ?></span>
        </button>
        <button type="button" class="sms-tab-btn" data-filter="c2c">
            <span>💳</span> کارت‌به‌کارت <span class="badge-count"><?= toPersianDigits((string)$catCounts['c2c']) ?></span>
        </button>
        <button type="button" class="sms-tab-btn" data-filter="admin">
            <span>🔔</span> هشدارهای ادمین <span class="badge-count"><?= toPersianDigits((string)$catCounts['admin']) ?></span>
        </button>
        <?php if ($catCounts['unset'] > 0): ?>
            <button type="button" class="sms-tab-btn" data-filter="unset" style="border-color:rgba(239,68,68,0.4); color:var(--sms-danger);">
                <span>⚠️</span> تنظیم‌نشده‌ها <span class="badge-count" style="background:rgba(239,68,68,0.15); color:var(--sms-danger);"><?= toPersianDigits((string)$catCounts['unset']) ?></span>
            </button>
        <?php endif; ?>
    </div>

    <!-- Search Box & Add Action -->
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <div class="sms-search-box">
            <span class="sms-search-icon">🔍</span>
            <input type="text" id="smsSearchInput" placeholder="جستجوی پترن، عنوان، رویداد...">
        </div>
        <a href="sms_pattern_edit.php" class="btn btn-primary" style="white-space:nowrap; padding:7px 16px;">
            ➕ الگوی جدید
        </a>
    </div>
</div>
