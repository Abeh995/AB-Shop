<?php
/**
 * Admin Users Partial: Security & Activity Audit Trail
 * Rendered via component('data_table', ['mode' => 'scroll'])
 */

$actionColorMap = [
    'login'           => ['state' => 'success', 'label' => 'ورود به سیستم'],
    'admin_create'    => ['tone' => 'purple',   'label' => 'ثبت مدیر'],
    'admin_update'    => ['tone' => 'blue',     'label' => 'ویرایش مدیر'],
    'password_change' => ['state' => 'warning', 'label' => 'تغییر رمز'],
    'status_toggle'   => ['tone' => 'slate',    'label' => 'تغییر دسترسی'],
    'admin_delete'    => ['state' => 'danger',  'label' => 'حذف مدیر'],
];

$auditColumns = [
    ['key' => 'actor',       'label' => 'مجری عملیات', 'priority' => 1],
    ['key' => 'event',       'label' => 'نوع رویداد',  'priority' => 2],
    ['key' => 'description', 'label' => 'شرح اقدام',   'priority' => 1],
    ['key' => 'ip',          'label' => 'آدرس IP',     'priority' => 3],
    ['key' => 'time',        'label' => 'زمان ثبت',    'priority' => 2],
];

component('data_table', [
    'id'      => 'adminAuditTable',
    'mode'    => 'scroll',
    'columns' => $auditColumns,
    'rows'    => $recentLogs,
    'cell'    => [
        'actor' => function ($log) {
            $name = e($log['full_name'] ?: $log['username'] ?: ('ادمین #' . $log['admin_id']));
            $u = !empty($log['username']) ? ' <span class="ab-text-xs ab-text-muted ab-num" dir="ltr">(@' . e($log['username']) . ')</span>' : '';
            return '<span class="font-bold">' . $name . '</span>' . $u;
        },
        'event' => function ($log) use ($actionColorMap) {
            $act = $log['action'] ?? '';
            $meta = $actionColorMap[$act] ?? ['state' => 'muted', 'label' => $act];
            $props = ['label' => $meta['label']];
            if (!empty($meta['tone'])) {
                $props['tone'] = $meta['tone'];
            } else {
                $props['state'] = $meta['state'] ?? 'muted';
            }
            return render_component('badge', $props);
        },
        'description' => function ($log) {
            return '<span class="ab-text-sm">' . e($log['description']) . '</span>';
        },
        'ip' => function ($log) {
            return '<span class="ab-num ab-text-xs ab-text-muted" dir="ltr">' . e($log['ip_address'] ?? '—') . '</span>';
        },
        'time' => function ($log) {
            if (!empty($log['created_at'])) {
                return '<span class="ab-num ab-text-xs ab-text-muted">' . toPersianDigits(date('Y/m/d H:i:s', strtotime($log['created_at']))) . '</span>';
            }
            return '<span class="ab-text-muted ab-text-xs">—</span>';
        },
    ],
    'empty' => [
        'title'   => 'رویدادی ثبت نشده است',
        'message' => 'رویداد امنیتی جدیدی در سیستم ثبت نشده است.',
    ],
]);
