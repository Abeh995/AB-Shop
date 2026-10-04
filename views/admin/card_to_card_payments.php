<?php
/**
 * Modern Desktop-First Admin Card-to-Card Payment Reconciliation Hub.
 * Features:
 * - Reference store destination bank banner
 * - Bento 4-KPI metrics grid with pending alert pulse
 * - Segmented status tabs with counter badges
 * - Batch selection & approval bar
 * - High-density desktop data table with live receipt thumbnails
 * - Split-View receipt inspection workstation (zoom, rotate, reconciliation, shortcuts)
 * - Intelligent rejection flow with reason presets & SMS notification
 * - Admin manual receipt upload modal
 */
require APP_ROOT . '/views/admin/layout/header.php';

$orders = $c2cData['orders'] ?? [];
$currentPageNum = $c2cData['page'] ?? 1;
$totalPages = $c2cData['totalPages'] ?? 1;
$totalCount = $c2cData['total'] ?? 0;
$currentTab = $tab ?? 'pending';

// Client-side lightweight JSON dataset for instant inspection workstation
$ordersMap = [];
$navOrderIds = [];
foreach ($orders as $o) {
    $navOrderIds[] = (int)$o['id'];
    $ordersMap[(int)$o['id']] = [
        'id'            => (int)$o['id'],
        'order_code'    => $o['order_code'],
        'customer_name' => $o['customer_name'],
        'phone'         => $o['phone'],
        'total'         => (int)$o['total'],
        'total_fmt'     => formatPrice($o['total']),
        'has_receipt'   => !empty($o['card_to_card_receipt']),
        'receipt_url'   => !empty($o['card_to_card_receipt']) ? '/admin/order_receipt.php?id=' . (int)$o['id'] : '',
        'submitted_at'  => !empty($o['card_to_card_submitted_at']) ? appDateTime($o['card_to_card_submitted_at'], 'full_shamsi') : appDateTime($o['created_at'], 'full_shamsi'),
        'payment_status'=> $o['payment_status'],
        'payment_label' => $paymentLabels[$o['payment_status']] ?? $o['payment_status'],
        'status'        => $o['status'],
        'status_label'  => $statusLabels[$o['status']] ?? $o['status'],
        'notes'         => $o['notes'] ?? '',
        'items'         => array_map(function($it) {
            return [
                'name'  => $it['product_name'] . ($it['variant_label'] ? ' (' . $it['variant_label'] . ')' : ''),
                'qty'   => (int)$it['quantity'],
                'price' => formatPrice($it['unit_price']),
                'total' => formatPrice($it['line_total']),
            ];
        }, $o['items'] ?? []),
    ];
}
?>

<main class="dash-workspace c2c-workspace">

    <!-- =================================================================== -->
    <!-- 1. Active Store Destination Bank Banner (Reference Header)          -->
    <!-- =================================================================== -->
    <header class="c2c-ref-banner">
        <div class="c2c-ref-right">
            <div class="c2c-bank-icon-pill">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div>
                <div class="c2c-ref-title">حساب بانکی مقصد فعال فروشگاه</div>
                <div class="c2c-ref-sub">شماره کارت جهت تطبیق سریع ارقام فیش‌های واریزی مشتریان</div>
            </div>
        </div>
        <div class="c2c-ref-meta">
            <?php if (!empty($storeCard['number'])): ?>
                <div class="c2c-ref-chip">
                    <span class="c2c-chip-label">شماره کارت:</span>
                    <strong class="c2c-chip-value c2c-card-num" dir="ltr" onclick="copyText('<?= e($storeCard['number']) ?>', this)" title="کلیک برای کپی">
                        <?= e(chunk_split($storeCard['number'], 4, ' ')) ?>
                    </strong>
                </div>
            <?php endif; ?>
            <?php if (!empty($storeCard['holder'])): ?>
                <div class="c2c-ref-chip">
                    <span class="c2c-chip-label">به نام:</span>
                    <strong class="c2c-chip-value"><?= e($storeCard['holder']) ?></strong>
                </div>
            <?php endif; ?>
            <?php if (!empty($storeCard['bank'])): ?>
                <div class="c2c-ref-chip">
                    <span class="c2c-chip-label">بانک:</span>
                    <strong class="c2c-chip-value"><?= e($storeCard['bank']) ?></strong>
                </div>
            <?php endif; ?>
            <a href="settings.php" class="c2c-settings-link" title="تغییر اطلاعات کارت در تنظیمات">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>تنظیمات حساب</span>
            </a>
        </div>
    </header>

    <!-- =================================================================== -->
    <!-- 2. Bento KPI Grid (Key Financial Reconciliation Metrics)            -->
    <!-- =================================================================== -->
    <section class="orders-kpi-grid c2c-kpi-grid">
        <!-- 1. Pending Queue (Urgent Attention) -->
        <div class="orders-kpi-card <?= $stats['pending_count'] > 0 ? 'highlight-amber c2c-card-urgent' : '' ?>" onclick="location.href='?tab=pending'">
            <div class="orders-kpi-header">
                <span class="orders-kpi-title">نیازمند بررسی و تأیید</span>
                <span class="orders-kpi-icon-pill" style="background:#FEF3C7; color:#B45309;">⏳</span>
            </div>
            <div class="orders-kpi-val-row">
                <span class="orders-kpi-val" style="color:<?= $stats['pending_count'] > 0 ? '#B45309' : 'inherit' ?>;"><?= toPersianDigits((string)$stats['pending_count']) ?></span>
                <span class="orders-kpi-unit">فیش معلق</span>
            </div>
            <div class="orders-kpi-footer">
                <?php if ($stats['pending_count'] > 0): ?>
                    <span class="pulse-badge">اقدام فوری</span>
                    <span style="font-weight:700; color:#B45309;"><?= formatPrice($stats['pending_sum']) ?></span>
                <?php else: ?>
                    <span style="color:#059669; font-weight:700;">به‌روز</span>
                    <span>صف بررسی خالی است</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. Approved Today -->
        <div class="orders-kpi-card highlight-green" onclick="location.href='?tab=paid'">
            <div class="orders-kpi-header">
                <span class="orders-kpi-title">تأیید شده‌های امروز</span>
                <span class="orders-kpi-icon-pill" style="background:#D1FAE5; color:#065F46;">✓</span>
            </div>
            <div class="orders-kpi-val-row">
                <span class="orders-kpi-val" style="color:#059669;"><?= toPersianDigits((string)$stats['paid_today_count']) ?></span>
                <span class="orders-kpi-unit">سفارش</span>
            </div>
            <div class="orders-kpi-footer">
                <span style="color:var(--text-muted);">حجم تسویه:</span>
                <span style="font-weight:700; color:#059669;"><?= formatPrice($stats['paid_today_sum']) ?></span>
            </div>
        </div>

        <!-- 3. Missing Receipts (Action Required) -->
        <div class="orders-kpi-card <?= $stats['no_receipt_count'] > 0 ? 'highlight-blue' : '' ?>" onclick="location.href='?tab=no_receipt'">
            <div class="orders-kpi-header">
                <span class="orders-kpi-title">سفارش‌های بدون فیش</span>
                <span class="orders-kpi-icon-pill" style="background:#EFF6FF; color:#1D4ED8;">📤</span>
            </div>
            <div class="orders-kpi-val-row">
                <span class="orders-kpi-val" style="color:#2563EB;"><?= toPersianDigits((string)$stats['no_receipt_count']) ?></span>
                <span class="orders-kpi-unit">سفارش</span>
            </div>
            <div class="orders-kpi-footer">
                <span>نیازمند پیگیری مشتری یا آپلود دستی مدیر</span>
            </div>
        </div>

        <!-- 4. Rejected Receipts -->
        <div class="orders-kpi-card highlight-rose" onclick="location.href='?tab=failed'">
            <div class="orders-kpi-header">
                <span class="orders-kpi-title">فیش‌های رد شده</span>
                <span class="orders-kpi-icon-pill" style="background:#FEE2E2; color:#991B1B;">✕</span>
            </div>
            <div class="orders-kpi-val-row">
                <span class="orders-kpi-val" style="color:#DC2626;"><?= toPersianDigits((string)$stats['failed_count']) ?></span>
                <span class="orders-kpi-unit">مورد</span>
            </div>
            <div class="orders-kpi-footer">
                <span>فیش‌های مغایر، نامعتبر یا واریز نشده</span>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 3. Navigation Tabs, Search & Batch Actions Toolbar                  -->
    <!-- =================================================================== -->
    <div class="c2c-toolbar-card">
        <!-- Segmented Navigation Tabs -->
        <div class="c2c-tabs-row">
            <nav class="c2c-nav-pills" aria-label="فیلتر وضعیت پرداخت">
                <a href="?tab=pending" class="c2c-tab-btn <?= $currentTab === 'pending' ? 'active' : '' ?>">
                    <span>در انتظار بررسی</span>
                    <?php if ($stats['pending_count'] > 0): ?>
                        <span class="c2c-badge badge-amber"><?= toPersianDigits((string)$stats['pending_count']) ?></span>
                    <?php endif; ?>
                </a>
                <a href="?tab=paid" class="c2c-tab-btn <?= $currentTab === 'paid' ? 'active' : '' ?>">
                    <span>تأیید شده</span>
                </a>
                <a href="?tab=failed" class="c2c-tab-btn <?= $currentTab === 'failed' ? 'active' : '' ?>">
                    <span>رد شده</span>
                    <?php if ($stats['failed_count'] > 0): ?>
                        <span class="c2c-badge badge-rose"><?= toPersianDigits((string)$stats['failed_count']) ?></span>
                    <?php endif; ?>
                </a>
                <a href="?tab=no_receipt" class="c2c-tab-btn <?= $currentTab === 'no_receipt' ? 'active' : '' ?>">
                    <span>فاقد فیش</span>
                    <?php if ($stats['no_receipt_count'] > 0): ?>
                        <span class="c2c-badge badge-blue"><?= toPersianDigits((string)$stats['no_receipt_count']) ?></span>
                    <?php endif; ?>
                </a>
                <a href="?tab=all" class="c2c-tab-btn <?= $currentTab === 'all' ? 'active' : '' ?>">
                    <span>همه فیش‌ها</span>
                    <span class="c2c-badge badge-gray"><?= toPersianDigits((string)$stats['total_count']) ?></span>
                </a>
            </nav>

            <!-- Search Form -->
            <form method="get" class="c2c-search-form">
                <input type="hidden" name="tab" value="<?= e($currentTab) ?>">
                <div class="c2c-search-box">
                    <svg class="c2c-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="جستجو کد، نام یا موبایل..." class="c2c-search-input">
                    <?php if ($search !== ''): ?>
                        <a href="?tab=<?= e($currentTab) ?>" class="c2c-search-clear" title="حذف جستجو">✕</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Floating Batch Action Bar (Triggered when >= 1 checkbox checked) -->
        <div id="c2cBatchBar" class="c2c-batch-bar" style="display:none;">
            <div class="c2c-batch-info">
                <span class="c2c-batch-count-pill" id="batchCountBadge">۰</span>
                <span>سفارش انتخاب شده است</span>
            </div>
            <div class="c2c-batch-actions">
                <form method="post" id="batchVerifyForm" style="display:inline;" onsubmit="return confirm('آیا از تأیید گروهی فیش‌های انتخاب‌شده اطمینان دارید؟');">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="batch_verify">
                    <input type="hidden" name="tab" value="<?= e($currentTab) ?>">
                    <input type="hidden" name="page" value="<?= (int)$currentPageNum ?>">
                    <div id="batchHiddenInputsContainer"></div>
                    <button type="submit" class="btn-dash-action c2c-btn-approve" style="padding:7px 16px;">
                        <span>تأیید گروهی موارد انتخاب‌شده ✓</span>
                    </button>
                </form>
                <button type="button" class="btn-dash-action" onclick="clearAllSelections()" style="padding:7px 14px;">
                    <span>لغو انتخاب</span>
                </button>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 4. High-Density Desktop Data Table                                  -->
    <!-- =================================================================== -->
    <div class="c2c-table-card">
        <div class="table-responsive">
            <table class="c2c-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;">
                            <input type="checkbox" id="masterCheckbox" onclick="toggleAllCheckboxes(this)" title="انتخاب همه">
                        </th>
                        <th>کد سفارش و زمان</th>
                        <th>مشخصات مشتری</th>
                        <th>مبلغ سفارش</th>
                        <th style="text-align:center;">تصویر فیش</th>
                        <th>وضعیت پرداخت</th>
                        <th>وضعیت سفارش</th>
                        <th style="text-align:left;">عملیات اعتبارسنجی</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <?php
                        $hasReceipt = !empty($o['card_to_card_receipt']);
                        $isPaid = ($o['payment_status'] === 'paid');
                        $isFailed = ($o['payment_status'] === 'failed');
                        $isUnpaid = ($o['payment_status'] === 'unpaid');
                        ?>
                        <tr class="c2c-row <?= $isUnpaid && $hasReceipt ? 'c2c-row-pending' : '' ?>" id="row-order-<?= (int)$o['id'] ?>">
                            <!-- Checkbox -->
                            <td style="text-align:center;">
                                <input type="checkbox" class="c2c-row-check" value="<?= (int)$o['id'] ?>" onchange="onCheckboxChange()" <?= $isPaid ? 'disabled title="این سفارش قبلاً تایید شده است"' : '' ?>>
                            </td>

                            <!-- Order Code & Time -->
                            <td>
                                <div class="c2c-code-wrap">
                                    <strong class="c2c-code" dir="ltr"><?= e($o['order_code']) ?></strong>
                                    <button type="button" class="c2c-copy-btn" onclick="copyText('<?= e($o['order_code']) ?>', this)" title="کپی کد سفارش">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                                <div class="c2c-time-sub">
                                    <?= toPersianDigits(date('Y/m/d H:i', strtotime($o['card_to_card_submitted_at'] ?: $o['created_at']))) ?>
                                </div>
                            </td>

                            <!-- Customer Info -->
                            <td>
                                <div class="c2c-customer-name"><?= e($o['customer_name']) ?></div>
                                <div class="c2c-customer-phone" dir="ltr">
                                    <a href="tel:<?= e($o['phone']) ?>" class="c2c-phone-link" title="تماس با مشتری">
                                        <?= e($o['phone']) ?>
                                    </a>
                                </div>
                            </td>

                            <!-- Order Total Amount -->
                            <td>
                                <div class="c2c-amount-val"><?= formatPrice($o['total']) ?></div>
                                <?php if (!empty($o['items'])): ?>
                                    <div class="c2c-items-badge">
                                        <?= toPersianDigits((string)count($o['items'])) ?> قلم کالا
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Receipt Preview / Thumbnail -->
                            <td style="text-align:center;">
                                <?php if ($hasReceipt): ?>
                                    <div class="c2c-thumb-wrap" onclick="openInspectModal(<?= (int)$o['id'] ?>)" title="کلیک برای بازرسی و بزرگنمایی فیش">
                                        <img src="/admin/order_receipt.php?id=<?= (int)$o['id'] ?>" class="c2c-thumb-img" alt="رسید">
                                        <div class="c2c-thumb-overlay">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="c2c-no-receipt-chip">
                                        <span>فاقد فیش</span>
                                        <button type="button" class="c2c-upload-trigger-btn" onclick="openUploadModal(<?= (int)$o['id'] ?>, '<?= e($o['order_code']) ?>')" title="آپلود دستی فیش توسط مدیر">
                                            <span>+ آپلود</span>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Payment Status -->
                            <td>
                                <span class="status-pill <?= $paymentClasses[$o['payment_status']] ?? '' ?>">
                                    <?= e($paymentLabels[$o['payment_status']] ?? $o['payment_status']) ?>
                                </span>
                            </td>

                            <!-- Order Status -->
                            <td>
                                <span class="status-pill status-<?= e($o['status']) ?>">
                                    <?= e($statusLabels[$o['status']] ?? $o['status']) ?>
                                </span>
                            </td>

                            <!-- Direct Actions -->
                            <td style="text-align:left;">
                                <div class="c2c-actions-cluster">
                                    <?php if ($hasReceipt): ?>
                                        <button type="button" class="btn-dash-action c2c-btn-inspect" onclick="openInspectModal(<?= (int)$o['id'] ?>)" title="باز کردن میز کار بررسی فیش">
                                            <span>بررسی فیش 🔍</span>
                                        </button>

                                        <?php if ($isUnpaid): ?>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('فیش واریزی سفارش <?= e($o['order_code']) ?> تأیید شود؟');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="verify_c2c">
                                                <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                                                <button type="submit" class="c2c-icon-action-btn c2c-approve-btn" title="تأیید فوری پرداخت">
                                                    ✓
                                                </button>
                                            </form>

                                            <button type="button" class="c2c-icon-action-btn c2c-reject-btn" onclick="openRejectModal(<?= (int)$o['id'] ?>, '<?= e($o['order_code']) ?>')" title="رد فیش با ذکر دلیل">
                                                ✕
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button type="button" class="btn-dash-action" onclick="openUploadModal(<?= (int)$o['id'] ?>, '<?= e($o['order_code']) ?>')" style="padding:6px 10px; font-size:0.75rem;">
                                            <span>آپلود دستی 📤</span>
                                        </button>
                                    <?php endif; ?>

                                    <a href="order_detail.php?id=<?= (int)$o['id'] ?>" class="c2c-link-detail" title="مشاهده پرونده کامل سفارش">
                                        ↗
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="c2c-empty-state">
                                    <div class="c2c-empty-icon">📭</div>
                                    <div class="c2c-empty-title">هیچ پرداختی در این بخش یافت نشد</div>
                                    <div class="c2c-empty-desc">سفارشی با معیارهای فیلتر یا جستجوی جاری ثبت نشده است.</div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="c2c-pagination-bar">
                <div class="c2c-page-info">
                    نمایش صفحه <?= toPersianDigits((string)$currentPageNum) ?> از <?= toPersianDigits((string)$totalPages) ?> (مجموعاً <?= toPersianDigits((string)$totalCount) ?> سفارش)
                </div>
                <div class="c2c-page-nav">
                    <?php if ($currentPageNum > 1): ?>
                        <a href="?tab=<?= e($currentTab) ?>&page=<?= $currentPageNum - 1 ?>&search=<?= urlencode($search) ?>" class="c2c-page-btn">قبلی</a>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if ($p == 1 || $p == $totalPages || abs($p - $currentPageNum) <= 2): ?>
                            <a href="?tab=<?= e($currentTab) ?>&page=<?= $p ?>&search=<?= urlencode($search) ?>" class="c2c-page-btn <?= $p === $currentPageNum ? 'active' : '' ?>">
                                <?= toPersianDigits((string)$p) ?>
                            </a>
                        <?php elseif (abs($p - $currentPageNum) == 3): ?>
                            <span class="c2c-page-dots">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($currentPageNum < $totalPages): ?>
                        <a href="?tab=<?= e($currentTab) ?>&page=<?= $currentPageNum + 1 ?>&search=<?= urlencode($search) ?>" class="c2c-page-btn">بعدی</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</main>

<!-- ======================================================================= -->
<!-- 5. Split-View Receipt Inspection Workstation Modal                      -->
<!-- ======================================================================= -->
<div id="inspectModal" class="c2c-modal-backdrop" onclick="closeInspectModal()">
    <div class="c2c-modal-window c2c-inspect-window" onclick="event.stopPropagation()">
        <!-- Workstation Header -->
        <div class="c2c-modal-header">
            <div class="c2c-ws-header-info">
                <span class="c2c-ws-tag">میز کار اعتبارسنجی فیش</span>
                <span class="c2c-ws-code" id="wsOrderCode" dir="ltr">-</span>
                <span class="c2c-ws-cust" id="wsCustomerName">-</span>
            </div>
            <div class="c2c-ws-header-nav">
                <button type="button" class="c2c-btn-nav-step" onclick="stepInspectOrder(-1)" title="فیش قبلی (کلید ←)">
                    <span>→ فیش بعدی</span>
                </button>
                <span class="c2c-step-pos" id="wsNavPos">- / -</span>
                <button type="button" class="c2c-btn-nav-step" onclick="stepInspectOrder(1)" title="فیش بعدی (کلید →)">
                    <span>فیش قبلی ←</span>
                </button>
                <button type="button" class="c2c-modal-close-btn" onclick="closeInspectModal()" title="بستن (Esc)">✕</button>
            </div>
        </div>

        <!-- Workstation Split-Body -->
        <div class="c2c-modal-body c2c-split-body">
            <!-- Right: Receipt Viewport & Controls -->
            <div class="c2c-viewport-pane">
                <div class="c2c-view-toolbar">
                    <button type="button" class="c2c-tool-btn" onclick="rotateReceipt()" title="چرخش ۹۰ درجه">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                        <span>چرخش ۹۰°</span>
                    </button>
                    <button type="button" class="c2c-tool-btn" onclick="zoomReceipt(0.25)" title="بزرگ‌نمایی">
                        <span>🔍+</span>
                    </button>
                    <button type="button" class="c2c-tool-btn" onclick="zoomReceipt(-0.25)" title="کوچک‌نمایی">
                        <span>🔍-</span>
                    </button>
                    <button type="button" class="c2c-tool-btn" onclick="resetReceiptTransform()" title="بازنشانی زوم و چرخش">
                        <span>⟲ ریست</span>
                    </button>
                    <a href="#" id="wsOpenNewTab" target="_blank" rel="noopener" class="c2c-tool-btn" title="مشاهده فایل اصلی در برگه جدید">
                        <span>↗ برگه جدید</span>
                    </a>
                </div>

                <div class="c2c-canvas-area" id="c2cCanvasArea">
                    <img id="wsReceiptImg" src="" alt="فیش واریز مشتری" class="c2c-inspect-photo">
                </div>
            </div>

            <!-- Left: Reconciliation & Order Data Sidebar -->
            <div class="c2c-recon-pane">
                <!-- Highlighted Expected Amount -->
                <div class="c2c-recon-card c2c-recon-amount-card">
                    <div class="c2c-recon-label">مبلغ سفارش (باید دقیقاً برابر فیش باشد):</div>
                    <div class="c2c-recon-val" id="wsOrderTotal">-</div>
                </div>

                <!-- Active Store Destination Card Info -->
                <?php if (!empty($storeCard['number'])): ?>
                <div class="c2c-recon-card c2c-dest-card">
                    <div class="c2c-dest-head">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <span>کارت مقصد فروشگاه:</span>
                    </div>
                    <div class="c2c-dest-num" dir="ltr"><?= e(chunk_split($storeCard['number'], 4, ' ')) ?></div>
                    <div class="c2c-dest-holder">به نام: <strong><?= e($storeCard['holder']) ?></strong> (<?= e($storeCard['bank']) ?>)</div>
                </div>
                <?php endif; ?>

                <!-- Customer Details -->
                <div class="c2c-recon-card">
                    <div class="c2c-meta-row">
                        <span class="c2c-meta-label">خریدار:</span>
                        <strong class="c2c-meta-val" id="wsCustomerNameVal">-</strong>
                    </div>
                    <div class="c2c-meta-row">
                        <span class="c2c-meta-label">موبایل:</span>
                        <strong class="c2c-meta-val" id="wsCustomerPhoneVal" dir="ltr">-</strong>
                    </div>
                    <div class="c2c-meta-row">
                        <span class="c2c-meta-label">زمان بارگذاری:</span>
                        <span class="c2c-meta-val" id="wsSubmittedAtVal">-</span>
                    </div>
                </div>

                <!-- Order Items Summary -->
                <div class="c2c-recon-card c2c-items-card">
                    <div style="font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:6px;">اقلام فاکتور:</div>
                    <ul class="c2c-items-list" id="wsItemsList">
                        <!-- Populated by JS -->
                    </ul>
                </div>

                <!-- Immediate Action Buttons -->
                <div class="c2c-recon-footer">
                    <form method="post" id="wsApproveForm">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="verify_c2c">
                        <input type="hidden" name="order_id" id="wsApproveOrderId" value="">
                        <input type="hidden" name="tab" value="<?= e($currentTab) ?>">
                        <input type="hidden" name="page" value="<?= (int)$currentPageNum ?>">
                        <button type="submit" class="c2c-btn-modal-action c2c-btn-approve-big" id="wsApproveBtn">
                            <span>تأیید فیش و ثبت پرداخت (A) ✓</span>
                        </button>
                    </form>

                    <button type="button" class="c2c-btn-modal-action c2c-btn-reject-big" id="wsRejectBtn" onclick="triggerRejectFromInspect()">
                        <span>رد فیش با ذکر دلیل (R) ✕</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- 6. Rejection Reason Modal (With Presets & SMS Notification)              -->
<!-- ======================================================================= -->
<div id="rejectModal" class="c2c-modal-backdrop" onclick="closeRejectModal()">
    <div class="c2c-modal-window c2c-reject-window" onclick="event.stopPropagation()">
        <div class="c2c-modal-header" style="border-bottom-color:var(--rose-border);">
            <div style="font-weight:700; color:var(--rose-text); font-size:0.95rem; display:flex; align-items:center; gap:8px;">
                <span>⚠️</span>
                <span>رد فیش واریز سفارش <span id="rejectModalCode" dir="ltr">-</span></span>
            </div>
            <button type="button" class="c2c-modal-close-btn" onclick="closeRejectModal()">✕</button>
        </div>
        <form method="post" id="rejectForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="reject_c2c">
            <input type="hidden" name="order_id" id="rejectOrderId" value="">
            <input type="hidden" name="tab" value="<?= e($currentTab) ?>">
            <input type="hidden" name="page" value="<?= (int)$currentPageNum ?>">

            <div class="c2c-modal-body" style="padding:18px 22px;">
                <p style="font-size:0.8rem; color:var(--text-secondary); line-height:1.6; margin-bottom:14px;">
                    علت رد فیش را انتخاب کنید. این علت از طریق پیامک رسمی به خریدار اطلاع‌رسانی خواهد شد:
                </p>

                <div class="c2c-reason-group">
                    <label class="c2c-reason-item">
                        <input type="radio" name="reason_preset" value="مبلغ واریزی با مبلغ سفارش مغایرت دارد." onchange="onReasonPresetChange(this.value)" checked>
                        <span>مبلغ واریزی با مبلغ سفارش مغایرت دارد.</span>
                    </label>
                    <label class="c2c-reason-item">
                        <input type="radio" name="reason_preset" value="تصویر فیش تار، مخدوش یا ناخواناست." onchange="onReasonPresetChange(this.value)">
                        <span>تصویر فیش تار، مخدوش یا ناخواناست.</span>
                    </label>
                    <label class="c2c-reason-item">
                        <input type="radio" name="reason_preset" value="مبلغ به حساب فروشگاه واریز نشده است." onchange="onReasonPresetChange(this.value)">
                        <span>مبلغ به حساب فروشگاه واریز نشده است.</span>
                    </label>
                    <label class="c2c-reason-item">
                        <input type="radio" name="reason_preset" value="فیش ارسالی تکراری یا نامعتبر است." onchange="onReasonPresetChange(this.value)">
                        <span>فیش ارسالی تکراری یا نامعتبر است.</span>
                    </label>
                    <label class="c2c-reason-item">
                        <input type="radio" name="reason_preset" value="other" onchange="onReasonPresetChange('')">
                        <span>سایر دلایل (نوشتن متن دلخواه)...</span>
                    </label>
                </div>

                <div style="margin-top:12px;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:6px;">متن نهایی علت ارسال به مشتری:</label>
                    <textarea name="reason" id="rejectReasonInput" class="form-control c2c-reason-textarea" rows="2" required>مبلغ واریزی با مبلغ سفارش مغایرت دارد.</textarea>
                </div>
            </div>

            <div class="c2c-modal-footer">
                <button type="submit" class="btn-dash-action c2c-btn-reject-confirm">
                    تأیید رد فیش و ارسال پیامک ✕
                </button>
                <button type="button" class="btn-dash-action" onclick="closeRejectModal()">
                    انصراف
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================= -->
<!-- 7. Admin Manual Receipt Upload Modal                                    -->
<!-- ======================================================================= -->
<div id="uploadModal" class="c2c-modal-backdrop" onclick="closeUploadModal()">
    <div class="c2c-modal-window c2c-upload-window" onclick="event.stopPropagation()">
        <div class="c2c-modal-header">
            <div style="font-weight:700; font-size:0.95rem; display:flex; align-items:center; gap:8px;">
                <span>📤</span>
                <span>آپلود دستی فیش برای سفارش <span id="uploadModalCode" dir="ltr">-</span></span>
            </div>
            <button type="button" class="c2c-modal-close-btn" onclick="closeUploadModal()">✕</button>
        </div>
        <form method="post" enctype="multipart/form-data" id="uploadForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="upload_receipt">
            <input type="hidden" name="order_id" id="uploadOrderId" value="">
            <input type="hidden" name="tab" value="<?= e($currentTab) ?>">
            <input type="hidden" name="page" value="<?= (int)$currentPageNum ?>">

            <div class="c2c-modal-body" style="padding:20px 24px;">
                <p style="font-size:0.8rem; color:var(--text-secondary); line-height:1.5; margin-bottom:14px;">
                    تصویر فیش دریافتی از مشتری (ارسال‌شده در ایتا/واتساپ) را انتخاب کنید. سیستم به‌طور خودکار تصویر را فشرده و بهینه‌سازی خواهد کرد.
                </p>

                <div class="c2c-dropzone" onclick="document.getElementById('receiptFileInput').click()">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--brand-primary); margin-bottom:8px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <div style="font-size:0.85rem; font-weight:700;">برای انتخاب فایل کلیک کنید</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">فرمت‌های مجاز: JPG، PNG، WebP (حداکثر ۴۰ مگابایت)</div>
                    <input type="file" name="receipt_file" id="receiptFileInput" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewUploadedFile(this)">
                </div>

                <div id="uploadFileMeta" style="margin-top:12px; font-size:0.8rem; color:var(--emerald-text); display:none; text-align:center; font-weight:700;"></div>
            </div>

            <div class="c2c-modal-footer">
                <button type="submit" class="btn-dash-action c2c-btn-approve" style="padding:8px 18px;">
                    ذخیره و پیوست فیش 📤
                </button>
                <button type="button" class="btn-dash-action" onclick="closeUploadModal()">
                    انصراف
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================= -->
<!-- Scoped Stylesheet for Card-to-Card Desktop Workstation                  -->
<!-- ======================================================================= -->
<style>
/* Workspace Container */
.c2c-workspace {
    padding-top: 14px;
}

/* Reference Bank Card Banner */
.c2c-ref-banner {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: var(--radius-lg);
    padding: 12px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    box-shadow: var(--shadow-subtle);
}
.c2c-ref-right {
    display: flex;
    align-items: center;
    gap: 12px;
}
.c2c-bank-icon-pill {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-md);
    background: var(--brand-light);
    color: var(--brand-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.c2c-ref-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--text-primary);
}
.c2c-ref-sub {
    font-size: 0.73rem;
    color: var(--text-muted);
}
.c2c-ref-meta {
    display: flex;
    align-items: center;
    gap: 12px;
}
.c2c-ref-chip {
    background: var(--bg-card-subtle);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-sm);
    padding: 6px 12px;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
.c2c-card-num {
    font-family: monospace;
    font-size: 0.9rem;
    color: var(--brand-primary);
    cursor: pointer;
    padding: 1px 4px;
    border-radius: 4px;
}
.c2c-card-num:hover {
    background: var(--brand-light);
}
.c2c-settings-link {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    color: var(--text-muted);
    text-decoration: none;
    padding: 6px 10px;
    border-radius: var(--radius-sm);
}
.c2c-settings-link:hover {
    color: var(--brand-primary);
    background: var(--brand-light);
}

/* Bento KPI Highlights */
.c2c-card-urgent {
    border-color: #F59E0B !important;
}

/* Toolbar Card & Tabs */
.c2c-toolbar-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: var(--radius-lg);
    padding: 10px 16px;
    margin-bottom: 14px;
    box-shadow: var(--shadow-subtle);
}
.c2c-tabs-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
}
.c2c-nav-pills {
    display: flex;
    align-items: center;
    gap: 6px;
}
.c2c-tab-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: var(--radius-pill);
    transition: all 0.18s ease;
}
.c2c-tab-btn:hover {
    background: var(--bg-hover);
    color: var(--brand-primary);
}
.c2c-tab-btn.active {
    background: var(--brand-primary);
    color: #FFFFFF;
    font-weight: 700;
}
.c2c-badge {
    font-size: 0.7rem;
    padding: 2px 7px;
    border-radius: var(--radius-pill);
    font-weight: 700;
}
.c2c-tab-btn.active .c2c-badge {
    background: rgba(255, 255, 255, 0.25) !important;
    color: #FFFFFF !important;
}
.badge-amber { background: #FEF3C7; color: #B45309; }
.badge-rose { background: #FEE2E2; color: #B91C1C; }
.badge-blue { background: #EFF6FF; color: #1D4ED8; }
.badge-gray { background: #F3F4F6; color: #4B5563; }

/* Search Box */
.c2c-search-box {
    position: relative;
    display: flex;
    align-items: center;
}
.c2c-search-icon {
    position: absolute;
    right: 12px;
    color: var(--text-muted);
    pointer-events: none;
}
.c2c-search-input {
    width: 250px;
    padding: 7px 34px 7px 28px;
    border: 1px solid var(--border-card);
    border-radius: var(--radius-pill);
    font-size: 0.78rem;
    background: var(--bg-card-subtle);
    font-family: inherit;
    transition: all 0.18s ease;
}
.c2c-search-input:focus {
    outline: none;
    border-color: var(--brand-primary);
    background: #FFFFFF;
    width: 300px;
}
.c2c-search-clear {
    position: absolute;
    left: 10px;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.8rem;
}

/* Floating Batch Action Bar */
.c2c-batch-bar {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed var(--border-card);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #FFFBEB;
    border-radius: var(--radius-sm);
    padding: 8px 14px;
}
.c2c-batch-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #B45309;
}
.c2c-batch-count-pill {
    background: #B45309;
    color: #FFFFFF;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}
.c2c-batch-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Table Card & High-Density Table */
.c2c-table-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-subtle);
    overflow: hidden;
}
.c2c-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.c2c-table th {
    background: var(--bg-card-subtle);
    border-bottom: 1px solid var(--border-card);
    padding: 11px 14px;
    font-weight: 700;
    color: var(--text-secondary);
    text-align: right;
    white-space: nowrap;
}
.c2c-table td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--border-subtle);
    vertical-align: middle;
}
.c2c-row:hover {
    background: #FAF8F5;
}
.c2c-row-pending {
    background: #FFFCF7;
}

/* Table Cells Typography & Components */
.c2c-code-wrap {
    display: flex;
    align-items: center;
    gap: 5px;
}
.c2c-code {
    font-family: monospace;
    font-size: 0.88rem;
    color: var(--text-primary);
}
.c2c-copy-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    padding: 2px;
}
.c2c-copy-btn:hover {
    color: var(--brand-primary);
}
.c2c-time-sub {
    font-size: 0.72rem;
    color: var(--text-muted);
    margin-top: 2px;
}
.c2c-customer-name {
    font-weight: 700;
    color: var(--text-primary);
}
.c2c-customer-phone {
    font-size: 0.74rem;
    color: var(--text-muted);
}
.c2c-phone-link {
    color: inherit;
    text-decoration: none;
}
.c2c-phone-link:hover {
    color: var(--brand-primary);
    text-decoration: underline;
}
.c2c-amount-val {
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--text-primary);
}
.c2c-items-badge {
    font-size: 0.7rem;
    color: var(--text-muted);
}

/* Thumbnail Lens Preview */
.c2c-thumb-wrap {
    position: relative;
    width: 44px;
    height: 44px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    cursor: pointer;
    border: 1px solid var(--border-card);
    margin: 0 auto;
}
.c2c-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.2s ease;
}
.c2c-thumb-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.18s ease;
}
.c2c-thumb-wrap:hover .c2c-thumb-overlay {
    opacity: 1;
}
.c2c-thumb-wrap:hover .c2c-thumb-img {
    transform: scale(1.1);
}
.c2c-no-receipt-chip {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    font-size: 0.72rem;
    color: var(--rose-text);
}
.c2c-upload-trigger-btn {
    background: var(--blue-bg);
    color: var(--blue-text);
    border: 1px solid var(--blue-border);
    border-radius: var(--radius-pill);
    padding: 2px 7px;
    font-size: 0.68rem;
    font-weight: 700;
    cursor: pointer;
}
.c2c-upload-trigger-btn:hover {
    background: var(--blue-dot);
    color: #FFFFFF;
}

/* Actions Cluster */
.c2c-actions-cluster {
    display: flex;
    align-items: center;
    gap: 6px;
    justify-content: flex-end;
}
.c2c-btn-inspect {
    padding: 6px 11px;
    font-size: 0.74rem;
    font-weight: 700;
    background: #FFFFFF;
    border: 1px solid var(--border-card);
}
.c2c-btn-inspect:hover {
    border-color: var(--brand-primary);
    color: var(--brand-primary);
}
.c2c-icon-action-btn {
    width: 28px;
    height: 28px;
    border-radius: var(--radius-sm);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.85rem;
    transition: all 0.15s ease;
}
.c2c-approve-btn {
    background: var(--emerald-bg);
    color: var(--emerald-text);
    border: 1px solid var(--emerald-border);
}
.c2c-approve-btn:hover {
    background: var(--emerald-dot);
    color: #FFFFFF;
}
.c2c-reject-btn {
    background: var(--rose-bg);
    color: var(--rose-text);
    border: 1px solid var(--rose-border);
}
.c2c-reject-btn:hover {
    background: var(--rose-dot);
    color: #FFFFFF;
}
.c2c-link-detail {
    color: var(--text-muted);
    font-size: 0.9rem;
    text-decoration: none;
    padding: 4px;
}
.c2c-link-detail:hover {
    color: var(--brand-primary);
}

/* Empty State */
.c2c-empty-state {
    padding: 45px 20px;
    text-align: center;
}
.c2c-empty-icon {
    font-size: 2.4rem;
    margin-bottom: 8px;
}
.c2c-empty-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
}
.c2c-empty-desc {
    font-size: 0.78rem;
    color: var(--text-muted);
    margin-top: 4px;
}

/* Pagination Bar */
.c2c-pagination-bar {
    padding: 12px 18px;
    background: var(--bg-card-subtle);
    border-top: 1px solid var(--border-card);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.78rem;
}
.c2c-page-info {
    color: var(--text-muted);
}
.c2c-page-nav {
    display: flex;
    gap: 4px;
    align-items: center;
}
.c2c-page-btn {
    padding: 4px 10px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-card);
    background: #FFFFFF;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.75rem;
}
.c2c-page-btn.active {
    background: var(--brand-primary);
    color: #FFFFFF;
    border-color: var(--brand-primary);
    font-weight: 700;
}
.c2c-page-dots {
    padding: 0 4px;
    color: var(--text-muted);
}

/* ==========================================================================
   Modals & Inspection Workstation Styles
   ========================================================================== */
.c2c-modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(18, 14, 12, 0.7);
    backdrop-filter: blur(4px);
    z-index: 1050;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.c2c-modal-window {
    background: #FFFFFF;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-dropdown);
    width: 100%;
    max-height: 92vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: c2cModalIn 0.2s ease-out;
}
@keyframes c2cModalIn {
    from { opacity: 0; transform: scale(0.97) translateY(8px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.c2c-modal-header {
    padding: 12px 18px;
    border-bottom: 1px solid var(--border-card);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--bg-card-subtle);
}
.c2c-modal-close-btn {
    background: none;
    border: none;
    font-size: 1.1rem;
    color: var(--text-muted);
    cursor: pointer;
    padding: 4px 8px;
    border-radius: var(--radius-sm);
}
.c2c-modal-close-btn:hover {
    color: var(--rose-text);
    background: var(--rose-bg);
}
.c2c-modal-footer {
    padding: 12px 18px;
    border-top: 1px solid var(--border-card);
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    background: var(--bg-card-subtle);
}

/* Inspection Workstation Dimensions */
.c2c-inspect-window {
    max-width: min(1240px, 94vw);
    height: 88vh;
}
.c2c-ws-header-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.c2c-ws-tag {
    font-size: 0.72rem;
    font-weight: 700;
    background: var(--brand-light);
    color: var(--brand-primary);
    padding: 3px 8px;
    border-radius: var(--radius-pill);
}
.c2c-ws-code {
    font-family: monospace;
    font-weight: 700;
    font-size: 0.95rem;
}
.c2c-ws-cust {
    font-size: 0.82rem;
    color: var(--text-muted);
}
.c2c-ws-header-nav {
    display: flex;
    align-items: center;
    gap: 10px;
}
.c2c-btn-nav-step {
    background: #FFFFFF;
    border: 1px solid var(--border-card);
    border-radius: var(--radius-sm);
    padding: 4px 10px;
    font-size: 0.75rem;
    cursor: pointer;
}
.c2c-btn-nav-step:hover {
    border-color: var(--brand-primary);
}
.c2c-step-pos {
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Split Body Viewport */
.c2c-split-body {
    display: flex;
    flex: 1;
    overflow: hidden;
    padding: 0;
}
.c2c-viewport-pane {
    flex: 1.2;
    background: #181412;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
}
.c2c-view-toolbar {
    background: rgba(28, 22, 19, 0.85);
    backdrop-filter: blur(8px);
    padding: 8px 12px;
    display: flex;
    gap: 6px;
    z-index: 10;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.c2c-tool-btn {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.18);
    color: #FFFFFF;
    border-radius: var(--radius-sm);
    padding: 4px 10px;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s ease;
}
.c2c-tool-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    color: #FFFFFF;
}
.c2c-canvas-area {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: auto;
    padding: 20px;
    cursor: grab;
}
.c2c-inspect-photo {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    border-radius: var(--radius-sm);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    transform-origin: center center;
}

/* Reconciliation Sidebar */
.c2c-recon-pane {
    flex: 1;
    min-width: 460px;
    background: #FFFFFF;
    border-right: 1px solid var(--border-card);
    padding: 20px 22px 0;
    display: flex;
    flex-direction: column;
    gap: 14px;
    overflow-y: auto;
    position: relative;
}
.c2c-recon-card {
    background: var(--bg-card-subtle);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    padding: 12px 14px;
}
.c2c-recon-amount-card {
    background: #FFFBEB;
    border-color: #FDE68A;
    text-align: center;
}
.c2c-recon-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #B45309;
}
.c2c-recon-val {
    font-size: 1.45rem;
    font-weight: 800;
    color: #92400E;
    margin-top: 4px;
}
.c2c-dest-card {
    background: #F0FDF4;
    border-color: #BBF7D0;
}
.c2c-dest-head {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.74rem;
    font-weight: 700;
    color: #166534;
    margin-bottom: 4px;
}
.c2c-dest-num {
    font-family: monospace;
    font-size: 1rem;
    font-weight: 700;
    color: #15803D;
    letter-spacing: 1px;
}
.c2c-dest-holder {
    font-size: 0.74rem;
    color: var(--text-secondary);
    margin-top: 2px;
}
.c2c-meta-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
    padding: 3px 0;
}
.c2c-meta-label {
    color: var(--text-muted);
}
.c2c-meta-val {
    color: var(--text-primary);
}
.c2c-items-list {
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 0.76rem;
}
.c2c-items-list li {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 12px;
    padding: 6px 0;
    border-bottom: 1px dashed var(--border-subtle);
}
.c2c-items-list li:last-child {
    border-bottom: none;
}
.c2c-item-name {
    color: var(--text-primary);
    font-weight: 500;
    line-height: 1.4;
    flex: 1;
}
.c2c-item-qty {
    background: var(--bg-hover, #F6EDE8);
    color: var(--brand-primary, #8C472E);
    padding: 2px 7px;
    border-radius: var(--radius-pill, 9999px);
    font-size: 0.7rem;
    font-weight: 700;
    white-space: nowrap;
}
.c2c-item-total {
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    font-size: 0.82rem;
}
.c2c-recon-footer {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: auto;
    padding: 14px 0 16px;
    position: sticky;
    bottom: 0;
    background: #FFFFFF;
    border-top: 1px solid var(--border-subtle);
    box-shadow: 0 -8px 16px rgba(28, 22, 19, 0.05);
    z-index: 10;
}
.c2c-btn-modal-action {
    width: 100%;
    padding: 10px;
    border-radius: var(--radius-md);
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: all 0.18s ease;
}
.c2c-btn-approve-big {
    background: #059669;
    color: #FFFFFF;
}
.c2c-btn-approve-big:hover {
    background: #047857;
}
.c2c-btn-reject-big {
    background: #FEE2E2;
    color: #B91C1C;
    border: 1px solid #FECACA;
}
.c2c-btn-reject-big:hover {
    background: #DC2626;
    color: #FFFFFF;
}

/* Rejection Window & Options */
.c2c-reject-window, .c2c-upload-window {
    max-width: 520px;
}
.c2c-reason-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.c2c-reason-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: var(--bg-card-subtle);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.15s ease;
}
.c2c-reason-item:hover {
    background: #FFFDF8;
    border-color: var(--border-strong);
}
.c2c-reason-textarea {
    width: 100%;
    border: 1px solid var(--border-card);
    border-radius: var(--radius-sm);
    padding: 8px;
    font-size: 0.8rem;
    font-family: inherit;
}
.c2c-btn-reject-confirm {
    background: #DC2626;
    color: #FFFFFF;
    border: none;
    padding: 8px 16px;
    border-radius: var(--radius-sm);
}

/* Upload Dropzone */
.c2c-dropzone {
    border: 2px dashed var(--border-strong);
    border-radius: var(--radius-md);
    padding: 26px 20px;
    text-align: center;
    background: var(--bg-card-subtle);
    cursor: pointer;
    transition: all 0.2s ease;
}
.c2c-dropzone:hover {
    border-color: var(--brand-primary);
    background: #FFFDF8;
}
</style>

<!-- ======================================================================= -->
<!-- Interactive Workstation Logic (Shortcuts, Lightbox, Batch, Modals)      -->
<!-- ======================================================================= -->
<script id="c2cOrdersMap" type="application/json"><?= json_encode(['orders' => $ordersMap, 'navOrderIds' => $navOrderIds], JSON_UNESCAPED_UNICODE) ?></script>
<script src="<?= e(asset('/assets/js/admin-c2c.js')) ?>"></script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
