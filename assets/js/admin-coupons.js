/**
 * Admin Coupons & Promotion Campaign Workstation JavaScript (assets/js/admin-coupons.js)
 * Clean, declarative interaction handler for coupons table, modal, code generation, and analytics drawer.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const couponModal = document.getElementById('couponModal');
        const couponForm = document.getElementById('couponForm');
        const couponTypeSelect = document.getElementById('couponTypeSelect');
        const valueGroup = document.getElementById('couponValueGroup');
        const valueLabel = document.getElementById('couponValueLabel');
        const valueInput = document.getElementById('couponValueInput');
        const maxCapGroup = document.getElementById('couponMaxCapGroup');

        // Drawer elements
        const couponDrawer = document.getElementById('couponDrawer');
        const drawerCouponCode = document.getElementById('drawerCouponCode');
        const drawerLoading = document.getElementById('drawerLoading');
        const drawerContent = document.getElementById('drawerContent');
        const drawerOrdersTbody = document.getElementById('drawerOrdersTbody');
        const drawerEmptyOrders = document.getElementById('drawerEmptyOrders');

        // 1. Coupon Type toggle (percent vs fixed vs free_shipping)
        function updateCouponTypeState(type) {
            if (!valueGroup || !valueLabel || !valueInput || !maxCapGroup) return;

            if (type === 'free_shipping') {
                valueGroup.style.display = 'none';
                maxCapGroup.style.display = 'none';
                valueInput.value = '0';
                valueInput.removeAttribute('required');
            } else if (type === 'fixed') {
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

        if (couponTypeSelect) {
            couponTypeSelect.addEventListener('change', function () {
                updateCouponTypeState(this.value);
            });
            updateCouponTypeState(couponTypeSelect.value);
        }

        // 2. Delegated Click Handlers
        document.addEventListener('click', function (e) {
            // A. 1-Click Code Copy
            const copyBtn = e.target.closest('[data-copy-code]');
            if (copyBtn) {
                e.preventDefault();
                const code = copyBtn.getAttribute('data-copy-code');
                if (!code) return;

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(code).then(function () {
                        if (window.AB && window.AB.toast) {
                            window.AB.toast('کد تخفیف «' + code + '» با موفقیت کپی شد.', 'success');
                        }
                    }).catch(function () {
                        prompt('کد تخفیف را کپی کنید:', code);
                    });
                } else {
                    prompt('کد تخفیف را کپی کنید:', code);
                }
                return;
            }

            // B. Random Code Generator via AJAX
            const genBtn = e.target.closest('[data-action="generate-random-code"]');
            if (genBtn) {
                e.preventDefault();
                const csrfInput = document.querySelector('input[name="csrf_token"]');
                const csrfToken = csrfInput ? csrfInput.value : '';
                const codeInput = document.getElementById('couponCodeInput');

                const formData = new FormData();
                formData.append('action', 'generate_code');
                formData.append('csrf_token', csrfToken);
                formData.append('prefix', 'OFF');

                fetch('/admin/coupons.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.ok && data.code && codeInput) {
                        codeInput.value = data.code;
                        if (window.AB && window.AB.toast) {
                            window.AB.toast('کد تصادفی جدید درج شد: ' + data.code, 'info');
                        }
                    }
                })
                .catch(function (err) {
                    console.error('Failed to generate coupon code', err);
                    if (window.AB && window.AB.toast) {
                        window.AB.toast('خطا در تولید کد تصادفی.', 'error');
                    }
                });
                return;
            }

            // C. Analytics Drawer Open
            const statsBtn = e.target.closest('[data-coupon-stats]');
            if (statsBtn) {
                e.preventDefault();
                const couponId = statsBtn.getAttribute('data-id');
                const couponCode = statsBtn.getAttribute('data-code') || '';

                if (drawerCouponCode) drawerCouponCode.textContent = couponCode;
                if (drawerLoading) {
                    drawerLoading.hidden = false;
                    drawerLoading.style.display = '';
                }
                if (drawerContent) drawerContent.hidden = true;

                if (window.AB && window.AB.modal) {
                    window.AB.modal.open('couponDrawer', statsBtn);
                } else if (couponDrawer && typeof couponDrawer.showModal === 'function') {
                    couponDrawer.showModal();
                }

                fetch('/admin/coupons.php?ajax=1&action=performance&id=' + encodeURIComponent(couponId))
                .then(function (res) { return res.json(); })
                .then(function (res) {
                    if (drawerLoading) drawerLoading.hidden = true;
                    if (!res.ok || !res.data) {
                        if (window.AB && window.AB.toast) {
                            window.AB.toast('خطا در بارگذاری آمار کد تخفیف.', 'error');
                        }
                        return;
                    }

                    const m = res.data.metrics || {};
                    const orders = res.data.recent_orders || [];

                    const elOrdersCount = document.getElementById('statOrdersCount');
                    const elGrossRevenue = document.getElementById('statGrossRevenue');
                    const elTotalDiscount = document.getElementById('statTotalDiscount');
                    const elAvgOrder = document.getElementById('statAvgOrder');

                    if (elOrdersCount) {
                        elOrdersCount.textContent = (window.AB && window.AB.fmt)
                            ? window.AB.fmt.faDigits(m.total_orders || 0)
                            : Number(m.total_orders || 0).toLocaleString('fa-IR');
                    }
                    if (elGrossRevenue) {
                        elGrossRevenue.textContent = (window.AB && window.AB.fmt)
                            ? window.AB.fmt.price(m.gross_revenue || 0)
                            : Number(m.gross_revenue || 0).toLocaleString('fa-IR') + ' تومان';
                    }
                    if (elTotalDiscount) {
                        elTotalDiscount.textContent = (window.AB && window.AB.fmt)
                            ? window.AB.fmt.price(m.total_discount_given || 0)
                            : Number(m.total_discount_given || 0).toLocaleString('fa-IR') + ' تومان';
                    }
                    if (elAvgOrder) {
                        elAvgOrder.textContent = (window.AB && window.AB.fmt)
                            ? window.AB.fmt.price(Math.round(m.avg_order_value || 0))
                            : Math.round(Number(m.avg_order_value || 0)).toLocaleString('fa-IR') + ' تومان';
                    }

                    if (drawerOrdersTbody) {
                        drawerOrdersTbody.innerHTML = '';
                        if (orders.length === 0) {
                            if (drawerEmptyOrders) drawerEmptyOrders.hidden = false;
                        } else {
                            if (drawerEmptyOrders) drawerEmptyOrders.hidden = true;
                            orders.forEach(function (o) {
                                const tr = document.createElement('tr');
                                const totalFormatted = (window.AB && window.AB.fmt)
                                    ? window.AB.fmt.price(o.total || 0)
                                    : Number(o.total || 0).toLocaleString('fa-IR') + ' ت';
                                const discFormatted = (window.AB && window.AB.fmt)
                                    ? window.AB.fmt.price(o.discount_total || 0)
                                    : Number(o.discount_total || 0).toLocaleString('fa-IR') + ' ت';
                                const isPaid = o.payment_status === 'paid';
                                const badgeState = isPaid ? 'success' : 'danger';
                                const badgeLabel = isPaid ? 'پرداخت‌شده' : 'پرداخت‌نشده';

                                tr.innerHTML = [
                                    '<td>',
                                    '  <a href="/admin/order_detail.php?id=' + encodeURIComponent(o.id) + '" class="ab-font-bold">',
                                    '    ' + (o.order_code ? (window.AB && window.AB.fmt ? window.AB.fmt.esc(o.order_code) : o.order_code) : ''),
                                    '  </a>',
                                    '  <div class="ab-text-xs ab-text-muted ab-num">' + (o.created_at ? o.created_at.substring(0, 10) : '') + '</div>',
                                    '</td>',
                                    '<td>',
                                    '  <div class="ab-font-bold ab-text-sm">' + (window.AB && window.AB.fmt ? window.AB.fmt.esc(o.customer_name || 'کاربر') : (o.customer_name || 'کاربر')) + '</div>',
                                    '  <div class="ab-text-xs ab-text-muted ab-num" dir="ltr">' + (window.AB && window.AB.fmt ? window.AB.fmt.esc(o.phone || '') : (o.phone || '')) + '</div>',
                                    '</td>',
                                    '<td class="ab-text-sm ab-num">' + totalFormatted + '</td>',
                                    '<td class="ab-text-sm ab-num">' + discFormatted + '</td>',
                                    '<td><span class="ab-badge" data-state="' + badgeState + '">' + badgeLabel + '</span></td>'
                                ].join('');
                                drawerOrdersTbody.appendChild(tr);
                            });
                        }
                    }

                    if (drawerContent) drawerContent.hidden = false;
                })
                .catch(function (err) {
                    if (drawerLoading) {
                        drawerLoading.hidden = false;
                        drawerLoading.innerHTML = '<div class="ab-text-danger ab-text-sm">خطا در دریافت آمار عملکرد کد تخفیف.</div>';
                    }
                });
                return;
            }

            // D. Create New Coupon Button (reset modal to default empty state)
            const openModalBtn = e.target.closest('[data-ab-modal-open="couponModal"]');
            if (openModalBtn && !window.location.search.includes('edit=')) {
                if (couponForm && !couponForm.querySelector('input[name="id"][value]:not([value="0"])')) {
                    couponForm.reset();
                    updateCouponTypeState('percent');
                }
            }
        });

        // 3. Modal URL sync on close
        if (couponModal) {
            couponModal.addEventListener('close', function () {
                if (window.location.search.includes('edit=')) {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('edit');
                    window.history.replaceState({}, '', url.pathname + (url.search ? url.search : ''));
                }
            });
        }
    });
})();
