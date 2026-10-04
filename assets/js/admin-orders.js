/**
 * AB-Socks Admin Orders Workstation Client Logic (admin-orders.js)
 * Master Grid, Dossier Drawer, Stepper Updates, Bulk Actions, & Gestures.
 */

(function () {
    'use strict';

    window.LIVE_ORDERS = window.LIVE_ORDERS || {};
    var dataEl = document.getElementById('ordersDataMap');
    if (dataEl && (!window.LIVE_ORDERS || Object.keys(window.LIVE_ORDERS).length === 0)) {
        try {
            window.LIVE_ORDERS = JSON.parse(dataEl.textContent || '{}');
        } catch (e) {
            window.LIVE_ORDERS = {};
        }
    }

    var selectedOrders = new Set();
    var currentOpenOrderId = null;

    function formatPrice(amount) {
        if (window.AB && window.AB.fmt && typeof window.AB.fmt.price === 'function') {
            return window.AB.fmt.price(amount);
        }
        return new Intl.NumberFormat('fa-IR').format(amount) + ' تومان';
    }

    function notify(msg, type) {
        if (window.AB && window.AB.toast) {
            window.AB.toast(msg, type || 'success');
        } else if (typeof window.showToast === 'function') {
            window.showToast(msg);
        }
    }

    // Open Order Dossier Drawer (Desktop) / Bottom Sheet (Mobile)
    window.openOrderDrawer = function (orderId) {
        var o = window.LIVE_ORDERS[orderId];
        if (!o) return;
        currentOpenOrderId = orderId;

        // Header info
        var dCode = document.getElementById('dOrderCode');
        if (dCode) dCode.textContent = o.order_code;

        // Status
        var statusPill = document.getElementById('dStatusPill');
        var statusText = document.getElementById('dStatusText');
        var statusLabels = {
            'pending': 'در انتظار بررسی',
            'confirmed': 'تأیید شده',
            'processing': 'در حال بسته‌بندی',
            'shipped': 'ارسال شده با پست',
            'delivered': 'تحویل داده شده',
            'cancelled': 'لغو شده'
        };
        var pillClass = 'amber';
        if (o.status === 'processing') pillClass = 'purple';
        else if (o.status === 'shipped') pillClass = 'blue';
        else if (o.status === 'delivered') pillClass = 'emerald';
        else if (o.status === 'cancelled') pillClass = 'rose';

        if (statusPill) statusPill.className = 'dash-status-pill ' + pillClass;
        if (statusText) statusText.textContent = statusLabels[o.status] || o.status;

        var statusSelect = document.getElementById('dStatusChangeSelect');
        if (statusSelect) statusSelect.value = o.status;

        // Stepper updates
        updateStepperUI(o.status, o.payment_status);

        // C2C Card section
        var c2cSection = document.getElementById('dC2cSection');
        if (c2cSection) {
            if (o.payment_method === 'card_to_card' && o.receipt_img) {
                c2cSection.style.display = 'block';
                var rImg = document.getElementById('dReceiptImg');
                if (rImg) rImg.src = o.receipt_img;
                var rAmt = document.getElementById('dReceiptAmount');
                if (rAmt) rAmt.textContent = formatPrice(o.total);
                var rDate = document.getElementById('dReceiptDate');
                if (rDate) rDate.textContent = 'ارسال شده: ' + (o.receipt_time || o.created_at_persian || o.created_at);
            } else {
                c2cSection.style.display = 'none';
            }
        }

        // Customer Details
        var dTime = document.getElementById('dOrderTime');
        if (dTime) dTime.textContent = o.created_at_persian || o.created_at;
        var dCust = document.getElementById('dCustomerName');
        if (dCust) dCust.textContent = o.customer_name;
        var dPhone = document.getElementById('dCustomerPhone');
        if (dPhone) dPhone.textContent = o.phone;
        var dCity = document.getElementById('dProvinceCity');
        if (dCity) dCity.textContent = 'استان ' + o.province + '، ' + o.city;
        var dAddr = document.getElementById('dFullAddress');
        if (dAddr) dAddr.textContent = o.address;
        var dPost = document.getElementById('dPostalCode');
        if (dPost) dPost.textContent = o.postal_code || '—';

        // Note
        var noteBox = document.getElementById('dCustomerNoteBox');
        if (noteBox) {
            if (o.notes && o.notes.trim()) {
                noteBox.style.display = 'block';
                var dNote = document.getElementById('dCustomerNoteText');
                if (dNote) dNote.textContent = o.notes;
            } else {
                noteBox.style.display = 'none';
            }
        }

        // Items Table
        var tbody = document.getElementById('dItemsTableBody');
        if (tbody) {
            var itemsHtml = '';
            (o.items || []).forEach(function (it) {
                itemsHtml += '<tr>' +
                    '<td><div style="font-weight:700;">' + it.name + (it.is_gift ? ' <span class="item-gift-badge">🎁 هدیه</span>' : '') + '</div>' +
                    '<div style="font-size:0.68rem; color:var(--text-muted);">' + (it.variant || '') + '</div></td>' +
                    '<td style="text-align:center; font-weight:700;">×' + it.qty + '</td>' +
                    '<td style="text-align:left;">' + formatPrice(it.price) + '</td>' +
                    '<td style="text-align:left; font-weight:700;">' + formatPrice(it.total) + '</td>' +
                    '</tr>';
            });
            (o.gifts || []).forEach(function (g) {
                itemsHtml += '<tr style="background:#F0FDF4;">' +
                    '<td><div style="font-weight:700; color:#047857;">🎁 ' + g.name + ' <span class="item-gift-badge">اشانتیون</span></div>' +
                    '<div style="font-size:0.68rem; color:#059669;">هدیه وفاداری / مناسبتی</div></td>' +
                    '<td style="text-align:center; font-weight:700; color:#047857;">×' + g.qty + '</td>' +
                    '<td style="text-align:left; color:#059669;">رایگان</td>' +
                    '<td style="text-align:left; font-weight:700; color:#047857;">۰ تومان</td>' +
                    '</tr>';
            });
            tbody.innerHTML = itemsHtml;
        }

        var dCount = document.getElementById('dItemsCount');
        if (dCount) dCount.textContent = ((o.items ? o.items.length : 0) + (o.gifts ? o.gifts.length : 0)) + ' قلم کالا';

        // Financial breakdown
        var dSub = document.getElementById('dSubtotal');
        if (dSub) dSub.textContent = formatPrice(o.subtotal);
        var dShipName = document.getElementById('dShippingName');
        if (dShipName) dShipName.textContent = o.shipping_method || 'پست پیشتاز';
        var dShipCost = document.getElementById('dShippingCost');
        if (dShipCost) dShipCost.textContent = o.shipping_cost > 0 ? formatPrice(o.shipping_cost) : 'رایگان';
        var dTot = document.getElementById('dTotalAmount');
        if (dTot) dTot.textContent = formatPrice(o.total);

        var discRow = document.getElementById('dDiscountRow');
        if (discRow) {
            if (o.discount_total > 0) {
                discRow.style.display = 'flex';
                var dCpn = document.getElementById('dCouponCode');
                if (dCpn) dCpn.textContent = o.coupon_code || 'کوپن';
                var dDisc = document.getElementById('dDiscount');
                if (dDisc) dDisc.textContent = '−' + formatPrice(o.discount_total);
            } else {
                discRow.style.display = 'none';
            }
        }

        // Tracking code
        var dTrack = document.getElementById('dTrackingInput');
        if (dTrack) dTrack.value = o.tracking_code || '';

        // Profit
        var dProfit = document.getElementById('dGrossProfit');
        if (dProfit) dProfit.textContent = formatPrice(o.gross_profit || 0);
        var dCost = document.getElementById('dCostPrice');
        if (dCost) dCost.textContent = formatPrice(o.cost_price || 0) + ' ت';
        var margin = o.total > 0 ? Math.round(((o.gross_profit || 0) / o.total) * 100) : 0;
        var dMargin = document.getElementById('dMarginPct');
        if (dMargin) dMargin.textContent = margin + '٪';

        // Hidden Order IDs for forms
        document.querySelectorAll('.drawer-hidden-order-id').forEach(function (el) {
            el.value = o.id;
        });
        var dDeepLink = document.getElementById('dDeepLinkBtn');
        if (dDeepLink) dDeepLink.href = 'order_detail.php?id=' + o.id;

        // Show Drawer
        var drawerEl = document.getElementById('orderDrawerBackdrop');
        if (drawerEl) drawerEl.classList.add('open');
    };

    function updateStepperUI(status, paymentStatus) {
        var sPay = document.getElementById('stepPay');
        var sPack = document.getElementById('stepPack');
        var sShip = document.getElementById('stepShip');
        var sDeliver = document.getElementById('stepDeliver');
        if (!sPay) return;

        [sPay, sPack, sShip, sDeliver].forEach(function (el) {
            if (el) el.className = 'step-item';
        });

        if (paymentStatus === 'paid') sPay.classList.add('completed');
        else sPay.classList.add('current');

        if (status === 'processing') {
            sPay.classList.add('completed');
            if (sPack) sPack.classList.add('current');
        } else if (status === 'shipped') {
            sPay.classList.add('completed');
            if (sPack) sPack.classList.add('completed');
            if (sShip) sShip.classList.add('current');
        } else if (status === 'delivered') {
            sPay.classList.add('completed');
            if (sPack) sPack.classList.add('completed');
            if (sShip) sShip.classList.add('completed');
            if (sDeliver) sDeliver.classList.add('completed');
        }
    }

    window.closeOrderDrawer = function () {
        var drawerEl = document.getElementById('orderDrawerBackdrop');
        if (drawerEl) drawerEl.classList.remove('open');
        currentOpenOrderId = null;
    };

    // 1-Click Copy for Postal Shipping Labels
    window.copyPostalLabel = function () {
        if (!currentOpenOrderId) return;
        var o = window.LIVE_ORDERS[currentOpenOrderId];
        if (!o) return;
        var label = 'گیرنده: ' + o.customer_name + '\nتلفن: ' + o.phone + '\nنشانی: استان ' + o.province + '، ' + o.city + '، ' + o.address + '\nکد پستی: ' + (o.postal_code || '—');
        if (!navigator.clipboard) return;
        navigator.clipboard.writeText(label).then(function () {
            notify('مشخصات کامل نشانی پستی جهت چاپ برچسب کپی شد ✓');
        });
    };

    // Receipt Modal Zoom
    window.openReceiptModal = function () {
        if (!currentOpenOrderId) return;
        var o = window.LIVE_ORDERS[currentOpenOrderId];
        if (!o || !o.receipt_img) return;
        var rImg = document.getElementById('receiptModalImg');
        if (rImg) rImg.src = o.receipt_img;
        var rMod = document.getElementById('receiptModal');
        if (rMod) rMod.classList.add('open');
    };

    window.closeReceiptModal = function () {
        var rMod = document.getElementById('receiptModal');
        if (rMod) rMod.classList.remove('open');
    };

    // Checkbox and Bulk Actions
    window.toggleSelectRow = function (orderId) {
        if (selectedOrders.has(orderId)) {
            selectedOrders.delete(orderId);
        } else {
            selectedOrders.add(orderId);
        }
        updateBulkDockState();
    };

    window.toggleSelectAll = function (el) {
        var isAll = el.classList.contains('checked');
        var checkboxes = document.querySelectorAll('.custom-checkbox[data-order-id]');
        if (isAll) {
            selectedOrders.clear();
            el.classList.remove('checked');
            checkboxes.forEach(function (c) { c.classList.remove('checked'); });
        } else {
            selectedOrders.clear();
            checkboxes.forEach(function (c) {
                var oid = parseInt(c.getAttribute('data-order-id'), 10);
                if (oid) selectedOrders.add(oid);
                c.classList.add('checked');
            });
            el.classList.add('checked');
        }
        updateBulkDockState();
    };

    window.clearSelection = function () {
        selectedOrders.clear();
        var selectAll = document.getElementById('selectAllCheckbox');
        if (selectAll) selectAll.classList.remove('checked');
        document.querySelectorAll('.custom-checkbox[data-order-id]').forEach(function (c) {
            c.classList.remove('checked');
        });
        updateBulkDockState();
    };

    function updateBulkDockState() {
        var dock = document.getElementById('bulkActionsDock');
        var countSpan = document.getElementById('selectedCountText');
        var hiddenInput = document.getElementById('bulkOrderIds');
        if (!dock) return;

        document.querySelectorAll('.custom-checkbox[data-order-id]').forEach(function (c) {
            var oid = parseInt(c.getAttribute('data-order-id'), 10);
            c.classList.toggle('checked', selectedOrders.has(oid));
            var row = document.getElementById('row-' + oid);
            if (row) row.classList.toggle('row-selected', selectedOrders.has(oid));
            var mcard = document.getElementById('mcard-' + oid);
            if (mcard) mcard.classList.toggle('m-card-selected', selectedOrders.has(oid));
        });

        if (selectedOrders.size > 0) {
            dock.classList.add('active');
            if (countSpan) countSpan.textContent = selectedOrders.size;
            if (hiddenInput) hiddenInput.value = Array.from(selectedOrders).join(',');
        } else {
            dock.classList.remove('active');
            if (hiddenInput) hiddenInput.value = '';
        }
    }

    window.submitBulkStatus = function (status) {
        if (selectedOrders.size === 0) return;
        var sInput = document.getElementById('bulkNewStatus');
        if (sInput) sInput.value = status;
        var bForm = document.getElementById('bulkActionForm');
        if (bForm) bForm.submit();
    };

    // Copy to Clipboard helper
    window.copyCode = function (text, label) {
        if (!navigator.clipboard) return;
        navigator.clipboard.writeText(text).then(function () {
            notify((label || '') + ' «' + text + '» کپی شد ✓');
        });
    };

    // Touch swipe-down to dismiss bottom sheet
    var startY = 0;
    var currentY = 0;
    var drawer = document.querySelector('.order-drawer');
    var handle = document.querySelector('.bottom-sheet-handle-bar');
    if (drawer && handle) {
        handle.addEventListener('touchstart', function (e) {
            startY = e.touches[0].clientY;
        }, { passive: true });

        handle.addEventListener('touchmove', function (e) {
            currentY = e.touches[0].clientY;
            var diff = currentY - startY;
            if (diff > 0) {
                drawer.style.transform = 'translateY(' + diff + 'px)';
            }
        }, { passive: true });

        handle.addEventListener('touchend', function () {
            var diff = currentY - startY;
            drawer.style.transform = '';
            if (diff > 70) {
                window.closeOrderDrawer();
            }
            startY = 0;
            currentY = 0;
        });
    }

    // Keybinds (Esc to close drawers)
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            window.closeReceiptModal();
            window.closeOrderDrawer();
        }
    });
})();
