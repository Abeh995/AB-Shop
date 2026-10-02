<?php
/**
 * Modern Admin Order Detail View
 * Connected with live store database, two-column responsive workspace,
 * interactive card-to-card receipt review, postal label copy, and print engine.
 */
require APP_ROOT . '/views/admin/layout/header.php';

$statusPillClass = [
    'pending'    => 'amber',
    'confirmed'  => 'emerald',
    'processing' => 'blue',
    'shipped'    => 'purple',
    'delivered'  => 'emerald',
    'cancelled'  => 'rose',
];

$payPillClass = [
    'unpaid' => 'amber',
    'paid'   => 'emerald',
    'failed' => 'rose',
];

$payLabels = [
    'unpaid' => 'پرداخت‌نشده',
    'paid'   => 'پرداخت‌شده',
    'failed' => 'ناموفق',
];

// Determine stepper progress
$curStatus = $order['status'];
$isPaid = ($order['payment_status'] === 'paid');
$step1Class = 'completed';
$step2Class = $isPaid ? 'completed' : ($curStatus === 'pending' ? 'current' : '');
$step3Class = in_array($curStatus, ['shipped', 'delivered'], true) ? 'completed' : (in_array($curStatus, ['processing', 'confirmed'], true) ? 'current' : '');
$step4Class = ($curStatus === 'delivered') ? 'completed' : ($curStatus === 'shipped' ? 'current' : '');
$step5Class = ($curStatus === 'delivered') ? 'completed' : '';
?>

<div class="od-wrapper">

    <!-- Print Only Header -->
    <div class="print-only-header">
        <h1 style="font-size:18pt; margin:0 0 6px;"><?= e(SITE_NAME) ?> — فاکتور رسمی سفارش</h1>
        <div style="font-size:11pt; color:#333;">
            شماره فاکتور: <strong dir="ltr"><?= e($order['order_code']) ?></strong> | 
            تاریخ ثبت: <?= appDateTime($order['created_at'], 'full_shamsi') ?>
        </div>
    </div>

    <!-- Header Breadcrumbs Bar -->
    <div class="od-header-bar">
        <div class="od-header-meta">
            <a href="orders.php" class="od-back-link">
                <span>←</span>
                <span>فهرست سفارش‌ها</span>
            </a>
            <span class="od-code-title" dir="ltr"><?= e($order['order_code']) ?></span>
            <span class="dash-status-pill <?= $statusPillClass[$order['status']] ?? 'amber' ?>">
                <span class="dash-status-dot"></span>
                <span><?= e($statusLabels[$order['status']] ?? $order['status']) ?></span>
            </span>
            <span class="dash-status-pill <?= $payPillClass[$order['payment_status']] ?? 'amber' ?>">
                <span class="dash-status-dot"></span>
                <span><?= e($payLabels[$order['payment_status']] ?? $order['payment_status']) ?></span>
            </span>
            <span style="font-size:0.75rem; color:var(--text-muted); margin-right:4px;">
                ثبت شده در: <?= appDateTime($order['created_at'], 'full_shamsi') ?>
            </span>
        </div>
        <div class="od-header-actions">
            <button type="button" class="btn-dash-action" onclick="window.print()" title="چاپ فاکتور این سفارش">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>چاپ فاکتور A4</span>
            </button>
        </div>
    </div>

    <!-- Order Progress Stepper -->
    <?php if ($order['status'] !== 'cancelled'): ?>
    <div class="od-card order-stepper-card" style="padding:16px 20px; margin-bottom:20px;">
        <div class="order-stepper">
            <div class="step-item <?= $step1Class ?>">
                <div class="step-dot">✓</div>
                <span class="step-label">ثبت سفارش</span>
            </div>
            <div class="step-item <?= $step2Class ?>">
                <div class="step-dot"><?= $isPaid ? '✓' : '۲' ?></div>
                <span class="step-label">تایید پرداخت</span>
            </div>
            <div class="step-item <?= $step3Class ?>">
                <div class="step-dot"><?= in_array($curStatus, ['shipped', 'delivered'], true) ? '✓' : '۳' ?></div>
                <span class="step-label">بسته‌بندی</span>
            </div>
            <div class="step-item <?= $step4Class ?>">
                <div class="step-dot"><?= ($curStatus === 'delivered') ? '✓' : '۴' ?></div>
                <span class="step-label">تحویل به پست</span>
            </div>
            <div class="step-item <?= $step5Class ?>">
                <div class="step-dot"><?= ($curStatus === 'delivered') ? '✓' : '۵' ?></div>
                <span class="step-label">تحویل مشتری</span>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="od-card" style="background:#FEF2F2; border-color:#FECACA; margin-bottom:20px; padding:12px 18px;">
        <span style="color:#B91C1C; font-weight:700; font-size:0.88rem;">⚠️ این سفارش لغو شده است.</span>
    </div>
    <?php endif; ?>

    <!-- Main Workspace Grid -->
    <div class="od-grid">

        <!-- Right Main Column (Items, Customer, Tracking, Intel) -->
        <div class="od-main-col">

            <!-- 1. Items & Invoice Breakdown -->
            <div class="od-card">
                <div class="od-card-title">
                    <span class="title-text">
                        <span>🛍️</span>
                        <span>اقلام سفارش و فاکتور فروش</span>
                    </span>
                    <span class="od-badge-sub"><?= toPersianDigits((string) count($items)) ?> قلم کالا</span>
                </div>

                <div class="od-items-table-desktop">
                    <div style="overflow-x:auto;">
                        <table class="od-table">
                            <thead>
                                <tr>
                                    <th class="od-col-thumb">تصویر</th>
                                    <th>نام کالا</th>
                                    <th>ویژگی / مشخصات</th>
                                    <th style="text-align:center;">تعداد</th>
                                    <th style="text-align:left;">قیمت واحد</th>
                                    <th style="text-align:left;">جمع کل</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $it): ?>
                                <tr>
                                    <td class="od-col-thumb">
                                        <div class="od-table-thumb-wrap" onclick="openProductGallery(this)" title="مشاهده تصاویر بزرگ محصول" data-title="<?= e($it['product_name']) ?>" data-variant="<?= e($it['variant_label'] ?? '') ?>" data-images="<?= htmlspecialchars(json_encode($it['images']), ENT_QUOTES, 'UTF-8') ?>">
                                            <img src="<?= e($it['thumb_url']) ?>" alt="<?= e($it['product_name']) ?>" class="od-table-thumb-img" onerror="this.src='/assets/img/placeholder-sock.svg'">
                                            <span class="od-table-thumb-zoom-icon">🔍</span>
                                            <?php if (!empty($it['images']) && count($it['images']) > 1): ?>
                                                <span class="od-table-thumb-badge"><?= toPersianDigits((string)count($it['images'])) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight:700;"><?= e($it['product_name']) ?></div>
                                        <?php if (!empty($it['variant_label'])): ?>
                                            <div class="od-mobile-variant"><span class="od-variant-chip"><?= e($it['variant_label']) ?></span></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-variant">
                                        <?php if (!empty($it['variant_label'])): ?>
                                            <span class="od-variant-chip"><?= e($it['variant_label']) ?></span>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted); font-size:0.75rem;">تک سایز / استاندارد</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center; font-weight:700; font-family:monospace; font-size:0.95rem;">
                                        <?= toPersianDigits((string)$it['quantity']) ?>
                                    </td>
                                    <td style="text-align:left; font-family:monospace;">
                                        <?= formatPrice($it['unit_price']) ?>
                                    </td>
                                    <td style="text-align:left; font-weight:700; font-family:monospace; color:var(--brand-primary);">
                                        <?= formatPrice($it['line_total']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card-View for Order Items (Screens <= 680px) -->
                <div class="od-items-cards-mobile">
                    <?php foreach ($items as $it): ?>
                    <div class="od-item-card">
                        <div class="od-item-thumb" onclick="openProductGallery(this)" title="مشاهده بزرگنمایی و گالری تصاویر" data-title="<?= e($it['product_name']) ?>" data-variant="<?= e($it['variant_label'] ?? '') ?>" data-images="<?= htmlspecialchars(json_encode($it['images']), ENT_QUOTES, 'UTF-8') ?>">
                            <img src="<?= e($it['thumb_url']) ?>" alt="<?= e($it['product_name']) ?>" class="od-item-thumb-img" onerror="this.src='/assets/img/placeholder-sock.svg'">
                            <span class="od-item-thumb-tap-hint">🔍</span>
                            <?php if (!empty($it['images']) && count($it['images']) > 1): ?>
                                <span class="od-item-thumb-badge"><?= toPersianDigits((string)count($it['images'])) ?> عکس</span>
                            <?php endif; ?>
                        </div>
                        <div class="od-item-body">
                            <div class="od-item-title"><?= e($it['product_name']) ?></div>
                            <div class="od-item-variant">
                                <?php if (!empty($it['variant_label'])): ?>
                                    <span class="od-variant-chip"><?= e($it['variant_label']) ?></span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:0.72rem;">تک سایز / استاندارد</span>
                                <?php endif; ?>
                            </div>
                            <div class="od-item-pricing">
                                <span class="od-item-calc"><?= toPersianDigits((string)$it['quantity']) ?> × <?= formatPrice($it['unit_price']) ?></span>
                                <span class="od-item-total"><?= formatPrice($it['line_total']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Financial Totals Block -->
                <div style="margin-top:16px; padding-top:12px; border-top:1px solid var(--border-subtle);">
                    <div class="financial-summary-row">
                        <span>مجموع قیمت کالاها:</span>
                        <span><?= formatPrice($order['subtotal']) ?></span>
                    </div>

                    <?php if ($order['discount_total'] > 0): ?>
                    <div class="financial-summary-row" style="color:#059669;">
                        <span>تخفیف کوپن <?= $order['coupon_code'] ? '(' . e($order['coupon_code']) . ')' : '' ?>:</span>
                        <span>−<?= formatPrice($order['discount_total']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($order['gift_items_total']) && $order['gift_items_total'] > 0): ?>
                    <div class="financial-summary-row">
                        <span>پیشنهاد بعد از سبد خرید:</span>
                        <span><?= formatPrice($order['gift_items_total']) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="financial-summary-row">
                        <span>هزینه ارسال پستی <?= !empty($order['shipping_method_name']) ? '(' . e($order['shipping_method_name']) . ')' : '' ?>:</span>
                        <span><?= $order['shipping_cost'] > 0 ? formatPrice($order['shipping_cost']) : 'رایگان' ?></span>
                    </div>

                    <div class="financial-summary-row total-row" style="margin-top:10px; padding-top:10px; border-top:1px dashed var(--border-strong);">
                        <span>مبلغ قابل پرداخت / نهایی:</span>
                        <span style="font-size:1.15rem; color:var(--brand-primary);"><?= formatPrice($order['total']) ?></span>
                    </div>
                </div>
            </div>

            <!-- 2. Customer & Shipping Destination -->
            <div class="od-card">
                <div class="od-card-title">
                    <span class="title-text">
                        <span>👤</span>
                        <span>مشخصات خریدار و نشانی تحویل گیرنده</span>
                    </span>
                    <button type="button" class="btn-copy-address" onclick="copyPostalLabel()" title="کپی اطلاعات جهت برچسب پستی">
                        <span>📋</span>
                        <span>کپی برای برچسب پستی</span>
                    </button>
                </div>

                <div class="od-customer-grid">
                    <div class="od-info-item">
                        <div class="od-info-label">نام و نام‌خانوادگی:</div>
                        <div class="od-info-val" id="custName"><?= e($order['customer_name']) ?></div>
                    </div>
                    <div class="od-info-item">
                        <div class="od-info-label">شماره تماس همراه:</div>
                        <div class="od-info-val">
                            <a href="tel:<?= e($order['phone']) ?>" class="od-phone-link" id="custPhone">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                <span><?= e($order['phone']) ?></span>
                            </a>
                        </div>
                    </div>
                    <?php if (!empty($order['email'])): ?>
                    <div class="od-info-item">
                        <div class="od-info-label">آدرس ایمیل:</div>
                        <div class="od-info-val" style="font-family:monospace; direction:ltr;" id="custEmail"><?= e($order['email']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="od-info-item">
                        <div class="od-info-label">استان و شهر:</div>
                        <div class="od-info-val" id="custCity"><?= e($order['province']) ?>، <?= e($order['city']) ?></div>
                    </div>
                </div>

                <div class="postal-address-box">
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:4px;">نشانی دقیق پستی:</div>
                    <div style="font-size:0.9rem; line-height:1.6;" id="custAddress"><?= e($order['address']) ?></div>
                    <div style="margin-top:6px; font-family:monospace; font-size:0.85rem; color:var(--text-secondary);">
                        کد پستی ۱۰ رقمی: <strong id="custPostal"><?= e($order['postal_code'] ?: '—') ?></strong>
                    </div>
                </div>

                <?php if (!empty($order['notes'])): ?>
                <div style="margin-top:12px; background:#FFFDF8; border:1px solid #FCD34D; border-radius:8px; padding:10px 14px;">
                    <span style="font-weight:700; color:#B45309; font-size:0.8rem;">💬 یادداشت و توضیحات خریدار:</span>
                    <div style="font-size:0.84rem; color:var(--text-secondary); margin-top:4px; line-height:1.5;"><?= nl2br(e($order['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 3. Postal Tracking Barcode -->
            <div class="od-card">
                <div class="od-card-title">
                    <span class="title-text">
                        <span>🚚</span>
                        <span>کد رهگیری مرسوله پست پیشتاز</span>
                    </span>
                    <span class="od-badge-sub">سامانه شرکت ملی پست ایران</span>
                </div>

                <form method="post" class="od-tracking-form">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_tracking">
                    <input type="text" name="tracking_code" value="<?= e($order['tracking_code'] ?? '') ?>" 
                           class="od-tracking-input" 
                           placeholder="کد رهگیری ۲۴ رقمی پست پیشتاز...">
                    <div class="od-tracking-actions">
                        <button type="submit" class="btn-verify-receipt" style="background:#1D4ED8; padding:9px 18px;">ثبت و ذخیره کد</button>
                        <?php if (!empty($order['tracking_code'])): ?>
                            <a href="https://tracking.post.ir/?id=<?= rawurlencode(trim($order['tracking_code'])) ?>" target="_blank" rel="noopener" class="btn-dash-action" style="padding:9px 14px;">
                                <span>رهگیری در سامانه پست ↗</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- 4. Gifts & Post-Order Offers -->
            <?php if (!empty($orderGiftItems)): ?>
            <div class="od-card">
                <div class="od-card-title">
                    <span class="title-text">
                        <span>🎁</span>
                        <span>هدایا و پیشنهادهای بعد از سبد خرید</span>
                    </span>
                    <span class="od-badge-sub"><?= toPersianDigits((string)count($orderGiftItems)) ?> قلم</span>
                </div>

                <div style="overflow-x:auto;">
                    <table class="od-table">
                        <thead>
                            <tr>
                                <th>نام آیتم</th>
                                <th>نوع تخصیص</th>
                                <th style="text-align:center;">تعداد</th>
                                <th style="text-align:left;">قیمت فاکتور</th>
                                <th>توضیحات و اهداکننده</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderGiftItems as $gi): ?>
                            <tr>
                                <td style="font-weight:700;"><?= e($gi['name']) ?></td>
                                <td>
                                    <?php if ($gi['role'] === 'gift'): ?>
                                        <span class="dash-status-pill emerald">🎁 هدیه فروشگاه</span>
                                    <?php else: ?>
                                        <span class="dash-status-pill blue">پیشنهاد بعد از سبد</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center; font-weight:700; font-family:monospace;">
                                    <?= toPersianDigits((string)$gi['quantity']) ?>
                                </td>
                                <td style="text-align:left; font-family:monospace;">
                                    <?= $gi['unit_selling_price'] > 0 ? formatPrice((int)$gi['unit_selling_price']) : 'رایگان' ?>
                                </td>
                                <td style="font-size:0.78rem; color:var(--text-secondary);">
                                    <?= e($gi['note'] ?: '—') ?>
                                    <?php if (!empty($gi['admin_username'])): ?>
                                        <div style="color:var(--text-muted); font-size:0.7rem; margin-top:2px;">(توسط: <?= e($gi['admin_username']) ?>)</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- 5. Assign Gift to Order Form -->
            <?php if (!empty($giftableItems)): ?>
            <div class="od-card">
                <div class="od-card-title">
                    <span class="title-text">
                        <span>🎀</span>
                        <span>اهدای هدیه از انبار به این سفارش</span>
                    </span>
                    <span class="od-badge-sub">رایگان برای مشتری / کسر از موجودی انبار</span>
                </div>

                <form method="post" class="od-gift-form">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="assign_gift">
                    <div class="od-gift-field-select">
                        <label style="display:block; font-size:0.75rem; color:var(--text-muted); margin-bottom:5px;">انتخاب آیتم هدیه:</label>
                        <select class="form-control" name="gift_item_id" required style="width:100%; border:1px solid var(--border-card); border-radius:8px; padding:8px 10px; background:#FFF;">
                            <?php foreach ($giftableItems as $gi): ?>
                                <option value="<?= (int)$gi['id'] ?>">
                                    <?= e($gi['name']) ?> (موجودی: <?= toPersianDigits((string)$gi['stock']) ?> عدد)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="od-gift-field-qty">
                        <label style="display:block; font-size:0.75rem; color:var(--text-muted); margin-bottom:5px;">تعداد:</label>
                        <input class="form-control" type="number" name="quantity" value="1" min="1" required style="width:100%; border:1px solid var(--border-card); border-radius:8px; padding:8px 10px; background:#FFF; text-align:center;">
                    </div>
                    <div class="od-gift-field-note">
                        <label style="display:block; font-size:0.75rem; color:var(--text-muted); margin-bottom:5px;">یادداشت هدیه (اختیاری):</label>
                        <input class="form-control" type="text" name="note" placeholder="مثلاً: هدیه بابت همراهی یا تأخیر..." style="width:100%; border:1px solid var(--border-card); border-radius:8px; padding:8px 10px; background:#FFF;">
                    </div>
                    <div class="od-gift-field-btn">
                        <button type="submit" class="btn-verify-receipt" style="background:var(--brand-primary); padding:9px 18px; width:100%;">ثبت هدیه ✓</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- 6. Profitability & Business Intelligence -->
            <?php if (!empty($profitability['ok'])): ?>
            <div class="od-card">
                <div class="od-card-title">
                    <span class="title-text">
                        <span>📈</span>
                        <span>تحلیل سودآوری و هوش تجاری سفارش</span>
                    </span>
                    <span class="od-badge-sub">اسنپ‌شات هزینه‌ها در لحظه خرید</span>
                </div>

                <?php if (!empty($profitability['has_incomplete_cost_data'])): ?>
                    <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:8px; padding:8px 12px; margin-bottom:12px; font-size:0.78rem; color:#B45309;">
                        ⚠️ حداقل یکی از کالاهای این سفارش، در لحظه فروش بهای تمام‌شده ثبت‌شده نداشته است؛ سود واقعی ممکن است با مقدار زیر تفاوت داشته باشد.
                    </div>
                <?php endif; ?>

                <div class="profit-badge-box" style="margin-bottom:16px;">
                    <div>
                        <div style="font-size:0.75rem; opacity:0.85;">سود ناخالص سفارش:</div>
                        <div style="font-size:1.2rem; font-weight:800; font-family:monospace; margin-top:2px;">
                            <?= formatPrice($profitability['gross_profit']) ?>
                        </div>
                    </div>
                    <div style="text-align:left; font-size:0.78rem;">
                        <div>حاشیه سود: <strong style="font-size:0.95rem;"><?= toPersianDigits((string)round($profitability['margin_percent'] ?? 0, 1)) ?>٪</strong></div>
                        <div style="opacity:0.8; margin-top:3px;">هزینه تمام‌شده کالا: <?= formatPrice($profitability['product_cost']) ?></div>
                    </div>
                </div>

                <div style="font-size:0.8rem; color:var(--text-secondary);">
                    <div class="financial-summary-row"><span>درآمد کل اقلام فروش رفته:</span><span><?= formatPrice($profitability['product_revenue']) ?></span></div>
                    <?php if ($profitability['discount'] > 0): ?><div class="financial-summary-row" style="color:#059669;"><span>تخفیف کسر شده:</span><span>−<?= formatPrice($profitability['discount']) ?></span></div><?php endif; ?>
                    <?php if ($profitability['post_order_revenue'] > 0): ?><div class="financial-summary-row"><span>درآمد پیشنهاد بعد از سبد:</span><span><?= formatPrice($profitability['post_order_revenue']) ?></span></div><?php endif; ?>
                    <div class="financial-summary-row"><span>مبلغ دریافتی ارسال از خریدار:</span><span><?= formatPrice($profitability['shipping_revenue']) ?></span></div>
                    <div class="financial-summary-row" style="border-top:1px solid var(--border-subtle); margin-top:6px; padding-top:6px; font-weight:700;"><span>مجموع خالص دریافتی:</span><span><?= formatPrice($profitability['revenue']) ?></span></div>

                    <div class="financial-summary-row" style="margin-top:10px;"><span>بهای تمام‌شده کالاها:</span><span>−<?= formatPrice($profitability['product_cost']) ?></span></div>
                    <?php if ($profitability['gift_cost'] > 0): ?><div class="financial-summary-row"><span>هزینه اقلام هدیه و آفرها:</span><span>−<?= formatPrice($profitability['gift_cost']) ?></span></div><?php endif; ?>
                    <div class="financial-summary-row"><span>هزینه واقعی ارسال پستی:</span><span>−<?= formatPrice($profitability['shipping_cost']) ?></span></div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Left Sidebar Column (Status, Payment & C2C Receipt, Danger Zone) -->
        <div class="od-order-sidebar">

            <!-- Order Status Management Card -->
            <div class="od-card" id="orderStatusCard">
                <div class="od-card-title">
                    <span class="title-text">
                        <span>⚙️</span>
                        <span>وضعیت کلی سفارش</span>
                    </span>
                </div>

                <form method="post">
                    <?= csrfField() ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:0.75rem; color:var(--text-muted); margin-bottom:6px;">تغییر مرحله سفارش:</label>
                        <select class="form-control" name="status" style="width:100%; border:1px solid var(--border-card); border-radius:8px; padding:9px 12px; background:#FFF; font-weight:600;">
                            <?php foreach ($statusLabels as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-verify-receipt" style="width:100%; background:var(--brand-primary); padding:10px; justify-content:center;">
                        ذخیره وضعیت سفارش
                    </button>
                </form>

                <div style="font-size:0.72rem; color:var(--text-muted); line-height:1.5; margin-top:10px; background:var(--bg-card-subtle); padding:8px 10px; border-radius:6px;">
                    ℹ️ با تغییر وضعیت، در صورت فعال بودن پنل فراز اس‌ام‌اس، پیامک خودکار به شماره مشتری ارسال خواهد شد.
                </div>
            </div>

            <!-- Payment Status & Card-to-Card Receipt Card -->
            <div class="od-card">
                <div class="od-card-title">
                    <span class="title-text">
                        <span>💳</span>
                        <span>وضعیت پرداخت و فیش</span>
                    </span>
                    <span class="dash-status-pill <?= $payPillClass[$order['payment_status']] ?? 'amber' ?>">
                        <?= e($payLabels[$order['payment_status']] ?? $order['payment_status']) ?>
                    </span>
                </div>

                <div style="margin-bottom:14px; font-size:0.82rem;">
                    <div class="financial-summary-row">
                        <span style="color:var(--text-muted);">روش پرداخت:</span>
                        <strong style="color:var(--text-primary);">
                            <?= $order['payment_method'] === 'card_to_card' ? '🧾 کارت‌به‌کارت بانکی' : '🌐 درگاه اینترنتی زرین‌پال' ?>
                        </strong>
                    </div>
                    <?php if (!empty($order['payment_ref_id'])): ?>
                    <div class="financial-summary-row" style="margin-top:6px;">
                        <span style="color:var(--text-muted);">کد پیگیری زرین‌پال:</span>
                        <strong style="font-family:monospace; direction:ltr;"><?= e($order['payment_ref_id']) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- C2C Receipt Preview & Verification Action Buttons -->
                <?php if ($order['payment_method'] === 'card_to_card'): ?>
                    <?php if (!empty($order['card_to_card_receipt'])): ?>
                        <div class="od-receipt-box">
                            <div style="font-size:0.75rem; font-weight:700; color:#B45309; margin-bottom:8px;">تصویر فیش واریز مشتری:</div>
                            <div class="od-receipt-preview">
                                <div class="od-receipt-thumb" onclick="openReceiptModal()" title="کلیک برای مشاهده بزرگنمایی">
                                    <img id="receiptThumbImg" src="/admin/order_receipt.php?id=<?= (int)$order['id'] ?>" alt="رسید کارت‌به‌کارت">
                                    <div class="od-receipt-overlay">🔍 بزرگنمایی</div>
                                </div>
                                <div style="flex:1;">
                                    <div style="font-size:0.85rem; font-weight:700;"><?= formatPrice($order['total']) ?></div>
                                    <div style="font-size:0.7rem; color:var(--text-muted); margin-top:3px;">
                                        <?= !empty($order['card_to_card_submitted_at']) ? appDateTime($order['card_to_card_submitted_at'], 'full_shamsi') : 'تاریخ نامشخص' ?>
                                    </div>
                                    <div style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;">
                                        <form method="post" style="display:inline;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="verify_c2c">
                                            <input type="hidden" name="decision" value="approved">
                                            <button type="submit" class="btn-verify-receipt" style="padding:6px 10px; font-size:0.75rem;">تایید فیش ✓</button>
                                        </form>
                                        <form method="post" style="display:inline;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="verify_c2c">
                                            <input type="hidden" name="decision" value="rejected">
                                            <button type="submit" class="btn-reject-receipt" style="padding:6px 10px; font-size:0.75rem;" onclick="return confirm('آیا از رد فیش کارت‌به‌کارت مطمئن هستید؟');">رد فیش ✕</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="background:#FFFBEB; border:1px dashed #FCD34D; border-radius:8px; padding:10px; font-size:0.78rem; color:#B45309; margin-bottom:14px; text-align:center;">
                            ⚠️ فیش واریز هنوز توسط مشتری بارگذاری نشده است.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Manual Payment Status Form -->
                <form method="post">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="payment_status">
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:0.75rem; color:var(--text-muted); margin-bottom:6px;">تغییر دستی وضعیت پرداخت:</label>
                        <select class="form-control" name="payment_status" style="width:100%; border:1px solid var(--border-card); border-radius:8px; padding:8px 10px; background:#FFF;">
                            <?php foreach ($payLabels as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $order['payment_status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-dash-action" style="width:100%; justify-content:center; padding:9px;">
                        ثبت وضعیت پرداخت
                    </button>
                </form>
            </div>

            <!-- Danger Zone (SuperAdmin Only) -->
            <?php if (isSuperAdmin()): ?>
            <div class="od-card od-danger-card">
                <div class="od-card-title" style="color:var(--rose-text); border-bottom-color:var(--rose-border);">
                    <span class="title-text">
                        <span>⚠️</span>
                        <span>منطقه خطر (مدیر کل)</span>
                    </span>
                </div>
                <p style="font-size:0.74rem; color:var(--text-muted); line-height:1.5; margin-bottom:12px;">
                    حذف سفارش غیرقابل بازگشت است و تمام رکوردهای اقلام و تراکنش وابسته به آن از سیستم پاک می‌شود.
                </p>
                <form method="post" onsubmit="return confirm('هشدار: آیا از حذف دائمی این سفارش اطمینان کامل دارید؟ این عملیات قابل بازگشت نیست.');">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn-reject-receipt" style="width:100%; justify-content:center; padding:9px;">
                        حذف کامل سفارش
                    </button>
                </form>
            </div>
            <?php endif; ?>

        </div>

    </div>

    <!-- Mobile Sticky Quick Actions Dock (Only visible on screens <= 768px) -->
    <div class="od-mobile-dock" id="mobileQuickDock">
        <div class="od-dock-info">
            <span class="od-dock-code" dir="ltr"><?= e($order['order_code']) ?></span>
            <span class="od-dock-total"><?= formatPrice($order['total']) ?></span>
        </div>
        <div class="od-dock-actions">
            <a href="tel:<?= e($order['phone']) ?>" class="btn-dock-icon" title="تماس با مشتری">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                <span>تماس</span>
            </a>
            <button type="button" class="btn-dock-action" onclick="scrollToStatusCard()" title="تغییر وضعیت سریع سفارش">
                <span>⚙️</span>
                <span>تغییر وضعیت</span>
            </button>
        </div>
    </div>

    <!-- Print Only Footer -->
    <?php if (!empty($invoiceFooterNote)): ?>
    <div class="print-only-footer">
        <p style="margin:0 0 4px; font-weight:700;"><?= nl2br(e($invoiceFooterNote)) ?></p>
        <div style="font-size:8pt; color:#666;">
            فروشگاه اینترنتی <?= e(SITE_NAME) ?> • <?= e(SITE_URL) ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Receipt Lightbox Modal -->
<div class="receipt-modal-backdrop" id="receiptModal" onclick="closeReceiptModal()">
    <div class="receipt-modal-content" onclick="event.stopPropagation()">
        <div class="receipt-modal-body">
            <img src="/admin/order_receipt.php?id=<?= (int)$order['id'] ?>" id="receiptModalImg" class="receipt-modal-img" alt="تصویر کامل فیش کارت‌به‌کارت">
        </div>
        <div class="receipt-modal-footer">
            <div style="font-size:0.8rem; color:var(--text-muted);">
                فیش واریزی سفارش <span dir="ltr"><?= e($order['order_code']) ?></span>
            </div>
            <div style="display:flex; gap:8px;">
                <a href="/admin/order_receipt.php?id=<?= (int)$order['id'] ?>" target="_blank" rel="noopener" class="btn-dash-action" style="padding:6px 12px; font-size:0.75rem;">
                    مشاهده در برگه جدید ↗
                </a>
                <button type="button" class="btn-dash-action" onclick="closeReceiptModal()" style="padding:6px 12px; font-size:0.75rem;">
                    بستن تصویر ✕
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Product Image Gallery Lightbox Modal (Large Floating Window) -->
<div class="product-gallery-modal-backdrop" id="productGalleryModal" onclick="closeProductGallery(event)">
    <div class="product-gallery-modal-content" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="product-gallery-header">
            <div class="product-gallery-info">
                <span class="product-gallery-title" id="galleryModalTitle">نام کالا</span>
                <span class="product-gallery-variant" id="galleryModalVariant">ویژگی</span>
            </div>
            <button type="button" class="product-gallery-close" onclick="closeProductGallery()" title="بستن (ESC)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <!-- Main Viewport -->
        <div class="product-gallery-viewport">
            <button type="button" class="gallery-nav-btn prev" id="galleryPrevBtn" onclick="galleryNav(-1)" title="تصویر قبلی (کلید راست)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
            <div class="product-gallery-main-img-wrap" id="galleryImgWrap">
                <img id="galleryMainImg" src="" alt="" class="product-gallery-main-img">
            </div>
            <button type="button" class="gallery-nav-btn next" id="galleryNextBtn" onclick="galleryNav(1)" title="تصویر بعدی (کلید چپ)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
        </div>

        <!-- Footer / Counter & Mini Thumbs Strip -->
        <div class="product-gallery-footer">
            <div class="product-gallery-counter" id="galleryCounter">تصویر ۱ از ۳</div>
            <div class="product-gallery-thumbs" id="galleryThumbsStrip"></div>
        </div>
    </div>
</div>

<!-- Global Toast Notification -->
<div class="dash-toast" id="dashToast">
    <span>📋</span>
    <span id="dashToastMsg">متن با موفقیت کپی شد!</span>
</div>

<script>
// Product Gallery Lightbox Engine
let currentGallery = {
    images: [],
    index: 0,
    title: '',
    variant: ''
};

function openProductGallery(el) {
    let imagesRaw = el.getAttribute('data-images') || '[]';
    let images = [];
    try {
        images = JSON.parse(imagesRaw);
    } catch(e) {
        images = [];
    }
    if (!images || images.length === 0) {
        const fallbackImg = el.querySelector('img')?.src;
        if (fallbackImg) images = [fallbackImg];
    }
    if (images.length === 0) return;

    currentGallery.images = images;
    currentGallery.index = 0;
    currentGallery.title = el.getAttribute('data-title') || 'تصویر محصول';
    currentGallery.variant = el.getAttribute('data-variant') || '';

    updateGalleryModal();
    const modal = document.getElementById('productGalleryModal');
    if (modal) modal.classList.add('open');
}

function updateGalleryModal() {
    const modal = document.getElementById('productGalleryModal');
    if (!modal) return;

    document.getElementById('galleryModalTitle').textContent = currentGallery.title;
    const variantEl = document.getElementById('galleryModalVariant');
    if (currentGallery.variant) {
        variantEl.textContent = currentGallery.variant;
        variantEl.style.display = 'inline-block';
    } else {
        variantEl.style.display = 'none';
    }

    const img = document.getElementById('galleryMainImg');
    const total = currentGallery.images.length;
    const idx = currentGallery.index;

    img.style.opacity = '0.3';
    img.src = currentGallery.images[idx];
    img.onload = () => { img.style.opacity = '1'; };
    img.onerror = () => { img.style.opacity = '1'; };

    const toPersianNum = (n) => String(n).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    document.getElementById('galleryCounter').textContent = `تصویر ${toPersianNum(idx + 1)} از ${toPersianNum(total)}`;

    const prevBtn = document.getElementById('galleryPrevBtn');
    const nextBtn = document.getElementById('galleryNextBtn');
    if (total <= 1) {
        if (prevBtn) prevBtn.classList.add('hidden');
        if (nextBtn) nextBtn.classList.add('hidden');
    } else {
        if (prevBtn) prevBtn.classList.remove('hidden');
        if (nextBtn) nextBtn.classList.remove('hidden');
    }

    const thumbsStrip = document.getElementById('galleryThumbsStrip');
    thumbsStrip.innerHTML = '';
    if (total > 1) {
        currentGallery.images.forEach((src, i) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'product-gallery-thumb-btn' + (i === idx ? ' active' : '');
            btn.title = `تصویر ${toPersianNum(i + 1)}`;
            btn.onclick = () => {
                currentGallery.index = i;
                updateGalleryModal();
            };
            const thumbImg = document.createElement('img');
            thumbImg.src = src;
            thumbImg.alt = '';
            btn.appendChild(thumbImg);
            thumbsStrip.appendChild(btn);
        });
        thumbsStrip.style.display = 'flex';
    } else {
        thumbsStrip.style.display = 'none';
    }
}

function galleryNav(direction) {
    const total = currentGallery.images.length;
    if (total <= 1) return;
    currentGallery.index = (currentGallery.index + direction + total) % total;
    updateGalleryModal();
}

function closeProductGallery(e) {
    if (e && e.target && e.target !== e.currentTarget) return;
    const modal = document.getElementById('productGalleryModal');
    if (modal) modal.classList.remove('open');
}

// Touch swipe support for gallery on mobile
let touchStartX = 0;
let touchEndX = 0;
const galleryModalEl = document.getElementById('productGalleryModal');
if (galleryModalEl) {
    galleryModalEl.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    galleryModalEl.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchEndX - touchStartX;
        if (Math.abs(diff) > 40) {
            // In RTL, swipe left goes to next (+1), swipe right goes to prev (-1)
            if (diff > 0) {
                galleryNav(-1);
            } else {
                galleryNav(1);
            }
        }
    }, { passive: true });
}

function openReceiptModal() {
    const modal = document.getElementById('receiptModal');
    if (modal) modal.classList.add('open');
}

function closeReceiptModal() {
    const modal = document.getElementById('receiptModal');
    if (modal) modal.classList.remove('open');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReceiptModal();
        closeProductGallery();
    }
    const galModal = document.getElementById('productGalleryModal');
    if (galModal && galModal.classList.contains('open')) {
        if (e.key === 'ArrowRight') galleryNav(-1);
        if (e.key === 'ArrowLeft') galleryNav(1);
    }
});

function copyPostalLabel() {
    const name = document.getElementById('custName')?.textContent?.trim() || '';
    const phone = document.getElementById('custPhone')?.textContent?.trim() || '';
    const city = document.getElementById('custCity')?.textContent?.trim() || '';
    const address = document.getElementById('custAddress')?.textContent?.trim() || '';
    const postal = document.getElementById('custPostal')?.textContent?.trim() || '';

    const labelText = `گیرنده: ${name}\nهمراه: ${phone}\nنشانی: ${city}، ${address}\nکد پستی: ${postal}`;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(labelText).then(() => {
            showToast('اطلاعات برچسب پستی کپی شد');
        }).catch(() => {
            fallbackCopy(labelText);
        });
    } else {
        fallbackCopy(labelText);
    }
}

function fallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        showToast('اطلاعات برچسب پستی کپی شد');
    } catch (err) {
        alert('امکان کپی خودکار فراهم نشد. لطفاً متن را دستی کپی کنید.');
    }
    document.body.removeChild(ta);
}

function scrollToStatusCard() {
    const card = document.getElementById('orderStatusCard');
    if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        card.classList.remove('highlight-pulse');
        // Trigger reflow to restart CSS animation
        void card.offsetWidth;
        card.classList.add('highlight-pulse');
        const statusSelect = card.querySelector('select[name="status"]');
        if (statusSelect) {
            setTimeout(() => {
                statusSelect.focus();
            }, 500);
        }
    }
}

function showToast(msg) {
    const toast = document.getElementById('dashToast');
    const msgEl = document.getElementById('dashToastMsg');
    if (!toast || !msgEl) return;
    msgEl.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 2800);
}
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
