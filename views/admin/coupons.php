<?php
/**
 * Modern High-Density Admin Coupons & Promotions Management Workstation
 * Pure presentation, zero direct SQL queries, zero form mutations (Rule 7).
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<link rel="stylesheet" href="/assets/css/admin-coupons.css?v=<?= APP_VERSION ?>">

<main class="cpn-workspace">

    <!-- 1. Alert Messages -->
    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">کد تخفیف با موفقیت ذخیره و به‌روزرسانی شد.</div>
    <?php endif; ?>
    <?php if (isset($_GET['toggled'])): ?>
        <div class="alert alert-success">وضعیت فعال‌بودن کد تخفیف با موفقیت تغییر یافت.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">کد تخفیف با موفقیت حذف گردید.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- 2. Bento KPI Metrics -->
    <?php require __DIR__ . '/coupons_partials/_kpis.php'; ?>

    <!-- 3. Toolbar & Status Filter Tabs -->
    <?php require __DIR__ . '/coupons_partials/_toolbar.php'; ?>

    <!-- 4. Master Coupons Matrix Data Table -->
    <?php require __DIR__ . '/coupons_partials/_table.php'; ?>

    <!-- 5. Create / Edit Coupon Modal -->
    <?php require __DIR__ . '/coupons_partials/_modal.php'; ?>

    <!-- 6. Slide-over Analytics Drawer -->
    <?php require __DIR__ . '/coupons_partials/_drawer_stats.php'; ?>

</main>

<script>
// ---------- Modal Controls ----------
function openCouponModal() {
    document.getElementById('couponModal').classList.add('active');
}

function closeCouponModal() {
    document.getElementById('couponModal').classList.remove('active');
    if (window.location.search.includes('edit=')) {
        window.location.href = '/admin/coupons.php';
    }
}

function handleCouponTypeChange(val) {
    const valueGroup = document.getElementById('couponValueGroup');
    const valueLabel = document.getElementById('couponValueLabel');
    const valueInput = document.getElementById('couponValueInput');
    const maxCapGroup = document.getElementById('couponMaxCapGroup');

    if (val === 'free_shipping') {
        valueGroup.style.display = 'none';
        maxCapGroup.style.display = 'none';
        valueInput.value = '0';
        valueInput.removeAttribute('required');
    } else if (val === 'fixed') {
        valueGroup.style.display = 'flex';
        maxCapGroup.style.display = 'none';
        valueLabel.textContent = 'مبلغ تخفیف (تومان) *';
        valueInput.setAttribute('required', 'required');
        valueInput.placeholder = 'مثلاً: ۵۰,۰۰۰';
    } else {
        // percent
        valueGroup.style.display = 'flex';
        maxCapGroup.style.display = 'flex';
        valueLabel.textContent = 'درصد تخفیف (۱ تا ۱۰۰) *';
        valueInput.setAttribute('required', 'required');
        valueInput.placeholder = 'مثلاً: ۲۰';
    }
}

// ---------- 1-Click Code Copy ----------
function copyCouponCode(code) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(code).then(() => {
            alert('کد تخفیف «' + code + '» با موفقیت کپی شد.');
        }).catch(() => {
            prompt('کد تخفیف را کپی کنید:', code);
        });
    } else {
        prompt('کد تخفیف را کپی کنید:', code);
    }
}

// ---------- Quick Random Code Generator via AJAX ----------
function generateRandomCouponCode() {
    const csrfToken = document.querySelector('input[name="csrf_token"]') ? document.querySelector('input[name="csrf_token"]').value : '';
    const formData = new FormData();
    formData.append('action', 'generate_code');
    formData.append('csrf_token', csrfToken);
    formData.append('prefix', 'OFF');

    fetch('/admin/coupons.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok && data.code) {
            document.getElementById('couponCodeInput').value = data.code;
        }
    })
    .catch(err => {
        console.error('Failed to generate coupon code', err);
    });
}

// ---------- Slide-Over Analytics Drawer ----------
function openCouponDrawer(couponId, code) {
    const overlay = document.getElementById('couponDrawerOverlay');
    const codeDisplay = document.getElementById('drawerCouponCode');
    const loading = document.getElementById('drawerLoading');
    const content = document.getElementById('drawerContent');
    const tbody = document.getElementById('drawerOrdersTbody');
    const emptyNotice = document.getElementById('drawerEmptyOrders');

    codeDisplay.textContent = code;
    loading.style.display = 'block';
    content.style.display = 'none';
    overlay.classList.add('active');

    fetch('/admin/coupons.php?ajax=1&action=performance&id=' + couponId)
    .then(res => res.json())
    .then(res => {
        loading.style.display = 'none';
        if (!res.ok || !res.data) return;

        const m = res.data.metrics || {};
        const orders = res.data.recent_orders || [];

        document.getElementById('statOrdersCount').textContent = Number(m.total_orders || 0).toLocaleString('fa-IR');
        document.getElementById('statGrossRevenue').textContent = Number(m.gross_revenue || 0).toLocaleString('fa-IR') + ' تومان';
        document.getElementById('statTotalDiscount').textContent = Number(m.total_discount_given || 0).toLocaleString('fa-IR') + ' تومان';
        document.getElementById('statAvgOrder').textContent = Math.round(Number(m.avg_order_value || 0)).toLocaleString('fa-IR') + ' تومان';

        tbody.innerHTML = '';
        if (orders.length === 0) {
            emptyNotice.style.display = 'block';
        } else {
            emptyNotice.style.display = 'none';
            orders.forEach(o => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <a href="/admin/order_detail.php?id=${o.id}" style="font-weight:700; color:var(--cpn-primary); text-decoration:none;">
                            ${o.order_code}
                        </a>
                        <div style="font-size:.7rem; color:#64748b;">${o.created_at ? o.created_at.substring(0, 10) : ''}</div>
                    </td>
                    <td>
                        <div style="font-weight:600;">${o.customer_name || 'کاربر'}</div>
                        <div style="font-size:.7rem; color:#64748b;" dir="ltr">${o.phone || ''}</div>
                    </td>
                    <td style="font-weight:700;">${Number(o.total || 0).toLocaleString('fa-IR')} ت</td>
                    <td style="color:var(--cpn-rose); font-weight:700;">${Number(o.discount_total || 0).toLocaleString('fa-IR')} ت</td>
                    <td>
                        <span class="badge" style="background:${o.payment_status === 'paid' ? '#d1fae5' : '#fee2e2'}; color:${o.payment_status === 'paid' ? '#065f46' : '#b91c1c'}; font-size:.7rem; padding:2px 6px;">
                            ${o.payment_status === 'paid' ? 'پرداخت‌شده' : 'پرداخت‌نشده'}
                        </span>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
        content.style.display = 'flex';
    })
    .catch(err => {
        loading.innerHTML = '<div style="color:#dc2626;">خطا در دریافت آمار عملکرد کد تخفیف.</div>';
    });
}

function closeCouponDrawer() {
    document.getElementById('couponDrawerOverlay').classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('couponTypeSelect');
    if (sel) handleCouponTypeChange(sel.value);
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
