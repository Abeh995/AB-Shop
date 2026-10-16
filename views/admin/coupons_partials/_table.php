<?php
/**
 * Master coupons matrix data table (views/admin/coupons_partials/_table.php)
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 */

component('data_table', [
    'id'          => 'couponsTable',
    'mode'        => 'stack',
    'columns'     => [
        ['key' => 'code_title', 'label' => 'کد و عنوان تخفیف',        'priority' => 1],
        ['key' => 'type_value', 'label' => 'نوع و ارزش',             'priority' => 1],
        ['key' => 'conditions', 'label' => 'شرایط سبد',              'priority' => 2],
        ['key' => 'usage',      'label' => 'میزان مصرف و سقف کاربر', 'priority' => 2],
        ['key' => 'expiry',     'label' => 'مهلت اعتبار (شمسی)',     'priority' => 2],
        ['key' => 'status',     'label' => 'وضعیت',                  'priority' => 1, 'align' => 'center'],
        ['key' => 'actions',    'label' => 'عملیات',                 'priority' => 1, 'type' => 'actions', 'align' => 'end'],
    ],
    'rows'        => $coupons,
    'row_attrs'   => fn($c) => [
        'data-id'     => $c['id'],
        'data-search' => $c['code'] . ' ' . ($c['title'] ?? '') . ' ' . ($c['category_name'] ?? ''),
    ],
    'cell'        => [
        'code_title' => function($c) {
            $codeHtml = '<div class="ab-cluster ab-cluster--xs">'
                . '<code class="ab-code ab-code--accent ab-num" dir="ltr">' . e($c['code']) . '</code>'
                . '<button type="button" class="ab-icon-btn ab-icon-btn--sm" title="کپی کردن کد" data-copy-code="' . e($c['code']) . '" aria-label="کپی کد">'
                . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>'
                . '</button>'
                . '</div>';

            $titleHtml = !empty($c['title'])
                ? '<div class="ab-text-xs ab-font-bold">' . e($c['title']) . '</div>'
                : '';

            $catHtml = !empty($c['category_name'])
                ? '<div class="ab-cluster ab-cluster--xs"><span class="ab-badge" data-tone="purple">دسته: ' . e($c['category_name']) . '</span></div>'
                : '';

            return '<div class="ab-stack ab-stack--xs">' . $codeHtml . $titleHtml . $catHtml . '</div>';
        },

        'type_value' => function($c) {
            $type = $c['type'] ?? 'percent';
            if ($type === 'free_shipping') {
                return '<span class="ab-badge" data-tone="sky">'
                    . '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 17h4V5H2v12h3m11 0h2l3-3v-4h-5v7Z"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>'
                    . ' ارسال رایگان'
                    . '</span>';
            } elseif ($type === 'percent') {
                return '<span class="ab-badge" data-tone="purple">'
                    . toPersianDigits((string)(float)$c['value']) . '٪ تخفیف'
                    . '</span>';
            } else {
                return '<span class="ab-badge" data-tone="emerald">'
                    . formatPrice($c['value'])
                    . '</span>';
            }
        },

        'conditions' => function($c) {
            $type = $c['type'] ?? 'percent';
            $minOrder = (!empty($c['min_order_amount']) && (float)$c['min_order_amount'] > 0)
                ? 'حداقل: ' . formatPrice($c['min_order_amount'])
                : '<span class="ab-text-muted">بدون حداقل</span>';

            $maxCap = ($type === 'percent' && !empty($c['max_discount_amount']) && (float)$c['max_discount_amount'] > 0)
                ? '<div class="ab-text-xs ab-text-muted">سقف: ' . formatPrice($c['max_discount_amount']) . '</div>'
                : '';

            return '<div class="ab-stack ab-stack--xs"><div class="ab-text-sm ab-font-bold">' . $minOrder . '</div>' . $maxCap . '</div>';
        },

        'usage' => function($c) {
            $maxUses = $c['max_uses'] !== null ? (int)$c['max_uses'] : null;
            $usedCount = (int)$c['used_count'];
            $usagePercent = !empty($maxUses) ? min(100, round(($usedCount / $maxUses) * 100)) : null;

            $countText = toPersianDigits((string)$usedCount) . ($maxUses ? ' از ' . toPersianDigits((string)$maxUses) . ' بار' : ' بار (نامحدود)');

            $progressHtml = '';
            if ($usagePercent !== null) {
                $tone = $usagePercent >= 100 ? 'rose' : ($usagePercent >= 80 ? 'amber' : 'purple');
                $progressHtml = render_component('progress', [
                    'value'        => (float)$usagePercent,
                    'tone'         => $tone,
                    'class'        => 'ab-progress-wrap--compact',
                ]);
            }

            $perCustomerText = '<div class="ab-text-xs ab-text-muted">سقف هر کاربر: '
                . toPersianDigits((string)($c['max_uses_per_customer'] ?? 1))
                . ' بار</div>';

            return '<div class="ab-stack ab-stack--xs"><div class="ab-text-sm ab-font-bold ab-num">' . $countText . '</div>' . $progressHtml . $perCustomerText . '</div>';
        },

        'expiry' => function($c) {
            $isExpired = !empty($c['expires_at']) && strtotime($c['expires_at']) < strtotime('today');
            if (!empty($c['expires_at'])) {
                if ($isExpired) {
                    return '<div class="ab-stack ab-stack--xs">'
                        . '<div class="ab-text-danger ab-font-bold ab-text-sm">' . appDateTime($c['expires_at'], 'shamsi') . '</div>'
                        . '<span class="ab-badge" data-state="danger">منقضی شده</span>'
                        . '</div>';
                }
                return '<div class="ab-stack ab-stack--xs">'
                    . '<div class="ab-text-sm ab-font-bold ab-num">' . appDateTime($c['expires_at'], 'shamsi') . '</div>'
                    . '<div class="ab-text-xs ab-text-muted ab-num" dir="ltr">' . e($c['expires_at']) . '</div>'
                    . '</div>';
            }
            return '<span class="ab-text-muted ab-text-sm">همیشگی</span>';
        },

        'status' => function($c) {
            $isExpired = !empty($c['expires_at']) && strtotime($c['expires_at']) < strtotime('today');
            $isLimitReached = $c['max_uses'] !== null && (int)$c['used_count'] >= (int)$c['max_uses'];

            if (!$c['is_active']) {
                return render_component('badge', ['text' => 'غیرفعال', 'state' => 'danger']);
            } elseif ($isExpired) {
                return render_component('badge', ['text' => 'منقضی', 'state' => 'warning']);
            } elseif ($isLimitReached) {
                return render_component('badge', ['text' => 'تکمیل ظرفیت', 'state' => 'muted']);
            } else {
                return render_component('badge', ['text' => 'فعال', 'state' => 'success', 'dot' => true]);
            }
        },

        'actions' => function($c) {
            $statsBtn = '<button type="button" class="ab-icon-btn ab-icon-btn--sm" title="عملکرد مالی و آمار سفارشات" data-coupon-stats data-id="' . (int)$c['id'] . '" data-code="' . e($c['code']) . '" aria-label="آمار عملکرد">'
                . '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>'
                . '</button>';

            $editBtn = '<a href="/admin/coupons.php?edit=' . (int)$c['id'] . '" class="ab-icon-btn ab-icon-btn--sm" title="ویرایش کد تخفیف" aria-label="ویرایش">'
                . '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>'
                . '</a>';

            $toggleBtn = '<form method="post" action="/admin/coupons.php" class="ab-inline-form">'
                . csrfField()
                . '<input type="hidden" name="action" value="toggle">'
                . '<input type="hidden" name="id" value="' . (int)$c['id'] . '">'
                . '<button type="submit" class="ab-icon-btn ab-icon-btn--sm" title="' . ($c['is_active'] ? 'تعلیق و غیرفعال‌سازی' : 'فعال‌سازی مجدد') . '" aria-label="تغییر وضعیت">'
                . ($c['is_active']
                    ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>'
                    : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>')
                . '</button>'
                . '</form>';

            $deleteBtn = '<form method="post" action="/admin/coupons.php" onsubmit="return confirm(\'آیا از حذف این کد تخفیف اطمینان دارید؟\');" class="ab-inline-form">'
                . csrfField()
                . '<input type="hidden" name="action" value="delete">'
                . '<input type="hidden" name="id" value="' . (int)$c['id'] . '">'
                . '<button type="submit" class="ab-icon-btn ab-icon-btn--sm ab-btn--danger" title="حذف کد تخفیف" aria-label="حذف">'
                . '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>'
                . '</button>'
                . '</form>';

            return '<div class="ab-cluster ab-cluster--xs ab-cluster--end">' . $statsBtn . $editBtn . $toggleBtn . $deleteBtn . '</div>';
        },
    ],
    'empty'       => [
        'title'   => 'هیچ کد تخفیفی با این مشخصات یافت نشد',
        'message' => 'می‌توانید فیلترها را ریست کنید یا کد تخفیف جدیدی ایجاد نمایید.',
    ],
    'sticky_head' => true,
]);
