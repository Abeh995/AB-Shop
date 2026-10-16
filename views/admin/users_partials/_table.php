<?php
/**
 * Admin Users Partial: Interactive Table
 * Rendered via component('data_table', ...)
 * Strict Presentation: 0 SQL, 0 $_POST, pure component integration.
 */

$columns = [
    ['key' => 'profile',    'label' => 'مشخصات مدیر', 'priority' => 1],
    ['key' => 'contact',    'label' => 'اطلاعات تماس', 'priority' => 2],
    ['key' => 'role',       'label' => 'سطح دسترسی',   'priority' => 2],
    ['key' => 'status',     'label' => 'وضعیت',        'priority' => 1],
    ['key' => 'last_login', 'label' => 'آخرین ورود',   'priority' => 3],
    ['key' => 'actions',    'label' => 'عملیات',       'priority' => 1, 'align' => 'end'],
];

component('data_table', [
    'id'        => 'adminUsersTable',
    'mode'      => 'stack',
    'columns'   => $columns,
    'rows'      => $admins,
    'row_attrs' => function ($a) {
        $searchIndex = mb_strtolower(($a['username'] ?? '') . ' ' . ($a['full_name'] ?? '') . ' ' . ($a['email'] ?? '') . ' ' . ($a['phone'] ?? ''));
        return [
            'data-role'   => $a['role'],
            'data-status' => !empty($a['is_active']) ? 'active' : 'inactive',
            'data-search' => $searchIndex,
        ];
    },
    'cell'      => [
        'profile' => function ($a) use ($currentAdminId) {
            $isMe = ((int)$a['id'] === (int)$currentAdminId);
            $initial = mb_substr($a['full_name'] ?: $a['username'], 0, 1, 'UTF-8');
            $selfBadge = $isMe ? ' ' . render_component('badge', ['label' => 'شما', 'state' => 'primary', 'tone' => 'slate']) : '';

            return '<div class="ab-cluster ab-cluster--nowrap">
                <span class="ab-avatar">' . e($initial) . '</span>
                <div class="ab-stack ab-stack--xs">
                    <span class="font-bold">' . e($a['full_name'] ?: $a['username']) . $selfBadge . '</span>
                    <span class="ab-text-xs ab-text-muted ab-num" dir="ltr">@' . e($a['username']) . '</span>
                </div>
            </div>';
        },
        'contact' => function ($a) {
            $out = '<div class="ab-stack ab-stack--xs">';
            $hasAny = false;
            if (!empty($a['phone'])) {
                $hasAny = true;
                $out .= '<span class="ab-cluster ab-cluster--xs ab-text-xs ab-text-muted" title="شماره تماس">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span class="ab-num" dir="ltr">' . e($a['phone']) . '</span>
                </span>';
            }
            if (!empty($a['email'])) {
                $hasAny = true;
                $out .= '<span class="ab-cluster ab-cluster--xs ab-text-xs ab-text-muted" title="ایمیل">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    <span class="ab-num" dir="ltr">' . e($a['email']) . '</span>
                </span>';
            }
            if (!$hasAny) {
                $out .= '<span class="ab-text-muted ab-text-xs">—</span>';
            }
            $out .= '</div>';
            return $out;
        },
        'role' => function ($a) {
            if ($a['role'] === 'super_admin') {
                return render_component('badge', [
                    'label' => 'مدیر ارشد',
                    'tone'  => 'amber',
                    'icon'  => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                ]);
            }
            return render_component('badge', [
                'label' => 'مدیر عادی',
                'tone'  => 'slate',
            ]);
        },
        'status' => function ($a) {
            if (!empty($a['is_active'])) {
                return render_component('badge', [
                    'label' => 'فعال',
                    'state' => 'success',
                    'dot'   => true,
                ]);
            }
            return render_component('badge', [
                'label' => 'غیرفعال',
                'state' => 'danger',
                'dot'   => true,
            ]);
        },
        'last_login' => function ($a) {
            if (!empty($a['last_login_at'])) {
                return '<div class="ab-stack ab-stack--xs">
                    <span class="ab-num font-bold ab-text-sm">' . toPersianDigits(date('Y/m/d H:i', strtotime($a['last_login_at']))) . '</span>
                    <span class="ab-text-xs ab-text-muted ab-num" dir="ltr">' . e($a['last_login_ip'] ?? '127.0.0.1') . '</span>
                </div>';
            }
            return '<span class="ab-text-muted ab-text-xs">ورود ثبت نشده</span>';
        },
        'actions' => function ($a) use ($currentAdminId) {
            $isMe = ((int)$a['id'] === (int)$currentAdminId);
            $userJson = htmlspecialchars(json_encode([
                'id'        => (int)$a['id'],
                'username'  => $a['username'],
                'full_name' => $a['full_name'] ?? '',
                'role'      => $a['role'],
                'phone'     => $a['phone'] ?? '',
                'email'     => $a['email'] ?? '',
            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

            $out = '<div class="ab-cluster ab-cluster--nowrap ab-cluster--xs">';
            // Edit Button
            $out .= '<button type="button" class="ab-icon-btn ab-icon-btn--sm" title="ویرایش مشخصات" data-ab-modal-open="modalEditAdmin" data-edit-user="' . $userJson . '">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
            </button>';

            // Change Password Button
            $out .= '<button type="button" class="ab-icon-btn ab-icon-btn--sm" title="تغییر رمز عبور" data-ab-modal-open="modalPasswordAdmin" data-pw-id="' . (int)$a['id'] . '" data-pw-username="' . e($a['username']) . '">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </button>';

            if (!$isMe) {
                // Toggle Active Status
                $toggleTitle = !empty($a['is_active']) ? 'غیرفعال کردن حساب' : 'فعال‌سازی حساب';
                $toggleIcon = !empty($a['is_active'])
                    ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>'
                    : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>';

                $out .= '<form method="post" action="users.php" class="ab-inline-form">
                    ' . csrfField() . '
                    <input type="hidden" name="action" value="toggle_active">
                    <input type="hidden" name="id" value="' . (int)$a['id'] . '">
                    <button type="submit" class="ab-icon-btn ab-icon-btn--sm" title="' . e($toggleTitle) . '">
                        ' . $toggleIcon . '
                    </button>
                </form>';

                // Delete Admin
                $out .= '<form method="post" action="users.php" class="ab-inline-form" onsubmit="return confirm(\'آیا از حذف دائمی این حساب مدیر اطمینان دارید؟ این عمل غیرقابل بازگشت است.\');">
                    ' . csrfField() . '
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="' . (int)$a['id'] . '">
                    <button type="submit" class="ab-icon-btn ab-icon-btn--sm ab-btn--danger" title="حذف حساب">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </button>
                </form>';
            }

            $out .= '</div>';
            return $out;
        },
    ],
    'empty'     => [
        'title'   => 'هیچ مدیری یافت نشد',
        'message' => 'حساب کاربری با مشخصات وارد شده در سیستم ثبت نشده است.',
    ],
]);
