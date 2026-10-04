/**
 * Admin Card-to-Card Payment Workstation Script
 * Handles receipt inspection workstation, split-view modal, image zoom & rotate,
 * rejection modal, manual upload modal, batch selection, and keyboard shortcuts.
 */
(function() {
    'use strict';

    let c2cOrders = {};
    let navOrderIds = [];
    let activeInspectId = null;
    let currentRotation = 0;
    let currentZoom = 1;

    function initData() {
        const dataEl = document.getElementById('c2cOrdersMap');
        if (dataEl) {
            try {
                const parsed = JSON.parse(dataEl.textContent || '{}');
                c2cOrders = parsed.orders || {};
                navOrderIds = parsed.navOrderIds || [];
            } catch(e) {
                console.error('Failed to parse c2cOrdersMap', e);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initData);
    } else {
        initData();
    }

    // 1. Text Copy Helper
    function copyText(text, btnElement) {
        if (!navigator.clipboard) return;
        navigator.clipboard.writeText(text).then(() => {
            if (window.AB && AB.toast) {
                AB.toast('کپی شد!', 'success', 1600);
            }
            if (btnElement) {
                const origText = btnElement.innerHTML;
                btnElement.innerHTML = 'کپی شد!';
                setTimeout(() => { btnElement.innerHTML = origText; }, 1600);
            }
        });
    }

    // 2. Split-View Inspection Workstation
    function openInspectModal(orderId) {
        if (!Object.keys(c2cOrders).length) initData();
        const order = c2cOrders[orderId];
        if (!order) return;

        activeInspectId = orderId;
        currentRotation = 0;
        currentZoom = 1;

        const setTxt = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        setTxt('wsOrderCode', order.order_code);
        setTxt('wsCustomerName', '(' + order.customer_name + ')');
        setTxt('wsCustomerNameVal', order.customer_name);
        setTxt('wsCustomerPhoneVal', order.phone);
        setTxt('wsOrderTotal', order.total_fmt);
        setTxt('wsSubmittedAtVal', order.submitted_at);

        const openTabEl = document.getElementById('wsOpenNewTab');
        if (openTabEl) openTabEl.href = order.receipt_url;

        const approveIdEl = document.getElementById('wsApproveOrderId');
        if (approveIdEl) approveIdEl.value = order.id;

        // Load Receipt Image
        const imgEl = document.getElementById('wsReceiptImg');
        if (imgEl) {
            imgEl.src = order.receipt_url;
        }
        applyReceiptTransform();

        // Render Order Items
        const itemsContainer = document.getElementById('wsItemsList');
        if (itemsContainer) {
            itemsContainer.innerHTML = '';
            if (order.items && order.items.length) {
                order.items.forEach(it => {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <span class="c2c-item-name">${it.name}</span>
                        <span class="c2c-item-qty">× ${it.qty}</span>
                        <strong class="c2c-item-total">${it.total}</strong>
                    `;
                    itemsContainer.appendChild(li);
                });
            } else {
                itemsContainer.innerHTML = '<li style="color:var(--text-muted); padding:6px 0;">اطلاعات اقلام ثبت نشده است.</li>';
            }
        }

        // Toggle Action Buttons based on status
        const approveBtn = document.getElementById('wsApproveBtn');
        const rejectBtn = document.getElementById('wsRejectBtn');
        if (order.payment_status === 'paid') {
            if (approveBtn) approveBtn.style.display = 'none';
            if (rejectBtn) rejectBtn.style.display = 'none';
        } else {
            if (approveBtn) approveBtn.style.display = 'block';
            if (rejectBtn) rejectBtn.style.display = 'block';
        }

        // Update Navigation Indicators
        updateInspectNavPos();

        // Show Modal
        const modal = document.getElementById('inspectModal');
        if (modal) modal.style.display = 'flex';
    }

    function closeInspectModal() {
        const modal = document.getElementById('inspectModal');
        if (modal) modal.style.display = 'none';
        activeInspectId = null;
    }

    function updateInspectNavPos() {
        const idx = navOrderIds.indexOf(activeInspectId);
        const navPos = document.getElementById('wsNavPos');
        if (idx !== -1 && navPos) {
            navPos.textContent = (idx + 1) + ' از ' + navOrderIds.length;
        }
    }

    function stepInspectOrder(direction) {
        if (!activeInspectId || !navOrderIds.length) return;
        const currentIdx = navOrderIds.indexOf(activeInspectId);
        if (currentIdx === -1) return;

        let targetIdx = currentIdx + direction;
        if (targetIdx < 0) targetIdx = navOrderIds.length - 1;
        if (targetIdx >= navOrderIds.length) targetIdx = 0;

        openInspectModal(navOrderIds[targetIdx]);
    }

    // Image Zoom & Rotate
    function rotateReceipt() {
        currentRotation = (currentRotation + 90) % 360;
        applyReceiptTransform();
    }

    function zoomReceipt(delta) {
        currentZoom = Math.min(3, Math.max(0.5, currentZoom + delta));
        applyReceiptTransform();
    }

    function resetReceiptTransform() {
        currentRotation = 0;
        currentZoom = 1;
        applyReceiptTransform();
    }

    function applyReceiptTransform() {
        const img = document.getElementById('wsReceiptImg');
        if (img) {
            img.style.transform = `scale(${currentZoom}) rotate(${currentRotation}deg)`;
        }
    }

    // 3. Rejection Modal
    function openRejectModal(orderId, orderCode) {
        const idEl = document.getElementById('rejectOrderId');
        const codeEl = document.getElementById('rejectModalCode');
        const modalEl = document.getElementById('rejectModal');
        if (idEl) idEl.value = orderId;
        if (codeEl) codeEl.textContent = orderCode;
        if (modalEl) modalEl.style.display = 'flex';
    }

    function closeRejectModal() {
        const modalEl = document.getElementById('rejectModal');
        if (modalEl) modalEl.style.display = 'none';
    }

    function onReasonPresetChange(val) {
        const input = document.getElementById('rejectReasonInput');
        if (!input) return;
        if (val !== '') {
            input.value = val;
        } else {
            input.value = '';
            input.focus();
        }
    }

    function triggerRejectFromInspect() {
        if (!activeInspectId) return;
        const order = c2cOrders[activeInspectId];
        if (order) {
            openRejectModal(order.id, order.order_code);
        }
    }

    // 4. Admin Manual Upload Modal
    function openUploadModal(orderId, orderCode) {
        const idEl = document.getElementById('uploadOrderId');
        const codeEl = document.getElementById('uploadModalCode');
        const metaEl = document.getElementById('uploadFileMeta');
        const fileInput = document.getElementById('receiptFileInput');
        const modalEl = document.getElementById('uploadModal');
        if (idEl) idEl.value = orderId;
        if (codeEl) codeEl.textContent = orderCode;
        if (metaEl) metaEl.style.display = 'none';
        if (fileInput) fileInput.value = '';
        if (modalEl) modalEl.style.display = 'flex';
    }

    function closeUploadModal() {
        const modalEl = document.getElementById('uploadModal');
        if (modalEl) modalEl.style.display = 'none';
    }

    function previewUploadedFile(input) {
        const metaEl = document.getElementById('uploadFileMeta');
        if (metaEl && input.files && input.files[0]) {
            const file = input.files[0];
            const sizeKb = Math.round(file.size / 1024);
            metaEl.textContent = `فایل انتخاب شد: ${file.name} (${sizeKb} کیلوبایت)`;
            metaEl.style.display = 'block';
        }
    }

    // 5. Batch Selection Management
    function toggleAllCheckboxes(masterCheckbox) {
        const checks = document.querySelectorAll('.c2c-row-check:not(:disabled)');
        checks.forEach(c => c.checked = masterCheckbox.checked);
        onCheckboxChange();
    }

    function onCheckboxChange() {
        const checked = document.querySelectorAll('.c2c-row-check:checked');
        const batchBar = document.getElementById('c2cBatchBar');
        const countBadge = document.getElementById('batchCountBadge');
        const container = document.getElementById('batchHiddenInputsContainer');

        if (checked.length > 0) {
            if (countBadge) countBadge.textContent = checked.length;
            if (container) {
                container.innerHTML = '';
                checked.forEach(c => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'selected_orders[]';
                    hidden.value = c.value;
                    container.appendChild(hidden);
                });
            }
            if (batchBar) batchBar.style.display = 'flex';
        } else {
            if (batchBar) batchBar.style.display = 'none';
            if (container) container.innerHTML = '';
        }
    }

    function clearAllSelections() {
        const checks = document.querySelectorAll('.c2c-row-check');
        checks.forEach(c => c.checked = false);
        const master = document.getElementById('masterCheckbox');
        if (master) master.checked = false;
        onCheckboxChange();
    }

    // 6. Keyboard Shortcuts Power Features
    document.addEventListener('keydown', function(e) {
        const inspectModal = document.getElementById('inspectModal');
        const rejectModal = document.getElementById('rejectModal');
        const uploadModal = document.getElementById('uploadModal');

        // Esc closes any active modal
        if (e.key === 'Escape') {
            if (rejectModal && rejectModal.style.display === 'flex') {
                closeRejectModal();
                return;
            }
            if (uploadModal && uploadModal.style.display === 'flex') {
                closeUploadModal();
                return;
            }
            if (inspectModal && inspectModal.style.display === 'flex') {
                closeInspectModal();
                return;
            }
        }

        // Workstation shortcuts (active only when inspect modal is open)
        if (inspectModal && inspectModal.style.display === 'flex' && (!rejectModal || rejectModal.style.display !== 'flex')) {
            if (e.key === 'ArrowRight' || e.key === ']') {
                stepInspectOrder(-1);
            } else if (e.key === 'ArrowLeft' || e.key === '[') {
                stepInspectOrder(1);
            } else if (e.key.toLowerCase() === 'a') {
                const approveBtn = document.getElementById('wsApproveBtn');
                if (approveBtn && approveBtn.style.display !== 'none') {
                    if (confirm('تأیید فیش سفارش جاری ثبت شود؟')) {
                        const form = document.getElementById('wsApproveForm');
                        if (form) form.submit();
                    }
                }
            } else if (e.key.toLowerCase() === 'r') {
                const rejectBtn = document.getElementById('wsRejectBtn');
                if (rejectBtn && rejectBtn.style.display !== 'none') {
                    triggerRejectFromInspect();
                }
            }
        }
    });

    // Expose handlers to window for inline onclick attributes
    window.copyText = copyText;
    window.openInspectModal = openInspectModal;
    window.closeInspectModal = closeInspectModal;
    window.updateInspectNavPos = updateInspectNavPos;
    window.stepInspectOrder = stepInspectOrder;
    window.rotateReceipt = rotateReceipt;
    window.zoomReceipt = zoomReceipt;
    window.resetReceiptTransform = resetReceiptTransform;
    window.applyReceiptTransform = applyReceiptTransform;
    window.openRejectModal = openRejectModal;
    window.closeRejectModal = closeRejectModal;
    window.onReasonPresetChange = onReasonPresetChange;
    window.triggerRejectFromInspect = triggerRejectFromInspect;
    window.openUploadModal = openUploadModal;
    window.closeUploadModal = closeUploadModal;
    window.previewUploadedFile = previewUploadedFile;
    window.toggleAllCheckboxes = toggleAllCheckboxes;
    window.onCheckboxChange = onCheckboxChange;
    window.clearAllSelections = clearAllSelections;
})();
