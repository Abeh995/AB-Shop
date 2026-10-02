<?php
/**
 * Adaptive Mobile Expenses Card Stack
 * Renders on mobile devices (<= 768px) as a high-density, touch-friendly financial card feed.
 * Included by views/admin/expenses.php
 */
?>
<div class="fin-expense-cards-stack">
    <?php if (empty($expensesData['items'])): ?>
        <div class="fin-mobile-empty">
            <div style="font-size: 2.2rem; margin-bottom: 8px;">📂</div>
            <div style="font-weight: 700; font-size: 0.95rem; color: var(--fin-text-primary);">هیچ سند هزینه‌ای یافت نشد</div>
            <div style="font-size: 0.8rem; color: var(--fin-text-muted); margin-top: 4px;">با تغییر فیلترها یا ثبت هزینه جدید، اسناد را مشاهده کنید.</div>
        </div>
    <?php else: ?>
        <?php foreach ($expensesData['items'] as $ex): 
            $natClass = $natureBadgeClasses[$ex['expense_nature'] ?? 'variable'] ?? 'nature-variable';
            $natText = $natureLabels[$ex['expense_nature'] ?? 'variable'] ?? 'متغیر';
            $hasReceipt = !empty($ex['receipt_image']);
            $receiptUrl = $hasReceipt ? (EXPENSE_UPLOAD_URL . e($ex['receipt_image'])) : '';
            $isArchived = ($ex['status'] === 'archived');
        ?>
        <div class="fin-mobile-card <?= $isArchived ? 'is-archived' : '' ?>" id="mexp-<?= (int)$ex['id'] ?>">
            <!-- Top Meta Strip -->
            <div class="fin-mcard-top">
                <div class="fin-mcard-date-badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span><?= toPersianDigits(appDateTime($ex['expense_date'], 'shamsi_date')) ?></span>
                </div>
                <div class="fin-mcard-badges">
                    <span class="fin-nature-badge <?= $natClass ?>"><?= $natText ?></span>
                    <?php if ($isArchived): ?>
                        <span class="fin-badge-archived">بایگانی</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Content Title & Payee -->
            <div class="fin-mcard-body">
                <div class="fin-mcard-title"><?= e($ex['title']) ?></div>
                <div class="fin-mcard-sub-meta">
                    <span class="fin-cat-pill"><?= e($ex['category']) ?></span>
                    <?php if (!empty($ex['payee'])): ?>
                        <span class="fin-payee-pill" title="طرف حساب">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <?= e($ex['payee']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($ex['description'])): ?>
                    <div class="fin-mcard-desc"><?= e($ex['description']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Financial Amount & Payment Source Row -->
            <div class="fin-mcard-finance-row">
                <div class="fin-mcard-source">
                    <span class="fin-source-pill">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        <?= e($ex['payment_source'] ?? 'کارت اصلی') ?>
                    </span>
                </div>
                <div class="fin-mcard-amount">
                    <span class="fin-mcard-amount-num"><?= formatPrice((int)$ex['amount']) ?></span>
                </div>
            </div>

            <!-- Card Footer: Receipt & Action Buttons -->
            <div class="fin-mcard-footer">
                <div class="fin-mcard-footer-left">
                    <?php if ($hasReceipt): ?>
                        <button type="button" class="fin-receipt-btn-mobile" onclick="openReceiptModal('<?= $receiptUrl ?>', '<?= e(addslashes($ex['title'])) ?>')">
                            <img src="<?= $receiptUrl ?>" alt="فاکتور" class="fin-receipt-mini-thumb">
                            <span>مشاهده فاکتور</span>
                        </button>
                    <?php else: ?>
                        <span class="fin-no-receipt-text">فاقد فاکتور</span>
                    <?php endif; ?>
                    <span class="fin-creator-meta">ثبت: <?= e($ex['admin_username'] ?? 'مدیر') ?></span>
                </div>

                <div class="fin-mcard-actions">
                    <a href="expense_edit.php?id=<?= (int)$ex['id'] ?>" class="fin-btn-icon" title="ویرایش سند">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>

                    <?php if ($ex['status'] === 'active'): ?>
                        <form method="post" action="expenses.php" onsubmit="return confirm('آیا از انتقال این سند به بایگانی اطمینان دارید؟');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="archive">
                            <input type="hidden" name="id" value="<?= (int)$ex['id'] ?>">
                            <input type="hidden" name="return_url" value="<?= e($returnUrl) ?>">
                            <button type="submit" class="fin-btn-icon text-rose" title="بایگانی سند">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="expenses.php" onsubmit="return confirm('آیا می‌خواهید این سند هزینه را مجدداً فعال کنید؟');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="restore">
                            <input type="hidden" name="id" value="<?= (int)$ex['id'] ?>">
                            <input type="hidden" name="return_url" value="<?= e($returnUrl) ?>">
                            <button type="submit" class="fin-btn-icon text-emerald" title="فعال‌سازی مجدد">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
