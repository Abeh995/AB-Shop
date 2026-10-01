/**
 * AB-Socks Gift Box & Post-Order Workstation Client Engine (admin-gift-items.js)
 * Desktop High-Density Dual-Pane Workspace: Drag-and-drop reordering, inline stock stepper popover,
 * live customer cart simulation, margin calculation, and instant AJAX persistence.
 */

(function () {
    'use strict';

    // Global references initialized from DOM
    let giftItemsMap = {};
    let csrfToken = '';
    let currentPopoverItemId = null;
    let draggedRow = null;

    // Helper: Persian Numbers
    function toFaDigits(str) {
        const f = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return String(str).replace(/[0-9]/g, w => f[+w]);
    }

    // Helper: Format Price
    function formatPriceJs(num) {
        if (isNaN(num) || num === null || num === '') return '—';
        return toFaDigits(Number(num).toLocaleString('en-US')) + ' تومان';
    }

    // Toast Notification System
    function showToast(message, type = 'success') {
        let container = document.getElementById('giftToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'giftToastContainer';
            container.className = 'gift-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'gift-toast toast-' + type;
        const icon = type === 'success' ? '✓' : '⚠️';
        toast.innerHTML = `<span class="toast-icon">${icon}</span><span class="toast-text">${message}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('is-visible');
        }, 10);

        setTimeout(() => {
            toast.classList.remove('is-visible');
            setTimeout(() => toast.remove(), 250);
        }, 3200);
    }

    // Initialize Page
    document.addEventListener('DOMContentLoaded', function () {
        // Load Client Dataset
        if (window.__GIFT_ITEMS_DATA__) {
            giftItemsMap = window.__GIFT_ITEMS_DATA__;
        }

        const metaCsrf = document.querySelector('meta[name="csrf-token"]');
        if (metaCsrf) {
            csrfToken = metaCsrf.getAttribute('content');
        }

        initLiveSearch();
        initDragAndDropReorder();
        initKeyboardShortcuts();
        calculateStudioMargin();
        syncLiveCustomerPreview();
    });

    // =========================================================================
    // 1. Instant Live Search
    // =========================================================================
    function initLiveSearch() {
        const searchInput = document.getElementById('giftLiveSearch');
        const clearBtn = document.getElementById('giftSearchClear');
        if (!searchInput) return;

        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            if (clearBtn) {
                clearBtn.style.display = query !== '' ? 'block' : 'none';
            }

            const rows = document.querySelectorAll('#giftItemsTable tbody tr[data-name]');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                if (query === '' || name.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            let noRes = document.getElementById('noResultsRow');
            if (!noRes && visibleCount === 0) {
                const tbody = document.querySelector('#giftItemsTable tbody');
                const tr = document.createElement('tr');
                tr.id = 'noResultsRow';
                tr.innerHTML = '<td colspan="11" style="text-align: center; padding: 48px; color: var(--gift-text-muted);">هیچ آیتمی با شرایط انتخابی یافت نشد.</td>';
                tbody.appendChild(tr);
            } else if (noRes && visibleCount > 0) {
                noRes.remove();
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            });
        }
    }

    // =========================================================================
    // 2. Drag & Drop Row Reordering
    // =========================================================================
    function initDragAndDropReorder() {
        const tableBody = document.getElementById('giftTableBody');
        if (!tableBody) return;

        tableBody.addEventListener('dragstart', function (e) {
            const row = e.target.closest('tr[draggable="true"]');
            if (!row) return;
            draggedRow = row;
            row.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', row.getAttribute('data-id'));
        });

        tableBody.addEventListener('dragend', function (e) {
            if (draggedRow) {
                draggedRow.classList.remove('is-dragging');
                draggedRow = null;
            }
            document.querySelectorAll('#giftTableBody tr').forEach(r => r.classList.remove('drag-over-top', 'drag-over-bottom'));
        });

        tableBody.addEventListener('dragover', function (e) {
            e.preventDefault();
            const targetRow = e.target.closest('tr[draggable="true"]');
            if (!targetRow || targetRow === draggedRow) return;

            const rect = targetRow.getBoundingClientRect();
            const mid = rect.top + rect.height / 2;
            targetRow.classList.remove('drag-over-top', 'drag-over-bottom');

            if (e.clientY < mid) {
                targetRow.classList.add('drag-over-top');
            } else {
                targetRow.classList.add('drag-over-bottom');
            }
        });

        tableBody.addEventListener('dragleave', function (e) {
            const targetRow = e.target.closest('tr[draggable="true"]');
            if (targetRow) {
                targetRow.classList.remove('drag-over-top', 'drag-over-bottom');
            }
        });

        tableBody.addEventListener('drop', async function (e) {
            e.preventDefault();
            const targetRow = e.target.closest('tr[draggable="true"]');
            if (!targetRow || !draggedRow || targetRow === draggedRow) return;

            const rect = targetRow.getBoundingClientRect();
            const mid = rect.top + rect.height / 2;

            if (e.clientY < mid) {
                tableBody.insertBefore(draggedRow, targetRow);
            } else {
                tableBody.insertBefore(draggedRow, targetRow.nextSibling);
            }

            document.querySelectorAll('#giftTableBody tr').forEach(r => r.classList.remove('drag-over-top', 'drag-over-bottom'));

            // Collect new order
            const orderedIds = [];
            tableBody.querySelectorAll('tr[data-id]').forEach(r => {
                orderedIds.push(parseInt(r.getAttribute('data-id'), 10));
            });

            // Send reorder AJAX
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('action', 'reorder');
            orderedIds.forEach(id => formData.append('order[]', id));

            try {
                const response = await fetch('gift_items.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const res = await response.json();
                if (res.ok) {
                    showToast('ترتیب نمایش در سبد خرید با موفقیت به‌روز شد.');
                } else {
                    showToast(res.error || 'خطا در ذخیره ترتیب.', 'error');
                }
            } catch (err) {
                showToast('خطای شبکه در ذخیره ترتیب.', 'error');
            }
        });
    }

    // =========================================================================
    // 3. Inline Stock Stepper Popover
    // =========================================================================
    window.openInlineStockPopover = function (event, id, currentStock, itemName) {
        event.stopPropagation();
        currentPopoverItemId = id;

        const popover = document.getElementById('giftStockPopover');
        const input = document.getElementById('stockPopoverInput');
        const title = document.getElementById('stockPopoverTitle');
        if (!popover || !input) return;

        title.textContent = 'موجودی: ' + itemName;
        input.value = currentStock;

        // Position popover relative to target button
        const btn = event.currentTarget;
        const rect = btn.getBoundingClientRect();
        popover.style.display = 'block';

        const popoverWidth = 240;
        let left = rect.left + (rect.width / 2) - (popoverWidth / 2);
        let top = rect.bottom + 8 + window.scrollY;

        if (left < 10) left = 10;
        if (left + popoverWidth > window.innerWidth) left = window.innerWidth - popoverWidth - 10;

        popover.style.left = left + 'px';
        popover.style.top = top + 'px';

        setTimeout(() => {
            input.focus();
            input.select();
        }, 50);
    };

    window.stepStockPopover = function (delta) {
        const input = document.getElementById('stockPopoverInput');
        if (!input) return;
        let val = parseInt(input.value, 10) || 0;
        val = Math.max(0, val + delta);
        input.value = val;
    };

    window.closeInlineStockPopover = function () {
        const popover = document.getElementById('giftStockPopover');
        if (popover) {
            popover.style.display = 'none';
        }
        currentPopoverItemId = null;
    };

    window.saveInlineStockPopover = async function () {
        if (!currentPopoverItemId) return;
        const input = document.getElementById('stockPopoverInput');
        const saveBtn = document.getElementById('stockPopoverSaveBtn');
        const newStock = parseInt(input.value, 10);

        if (isNaN(newStock) || newStock < 0) {
            alert('لطفاً یک عدد معتبر و مثبت وارد کنید.');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.textContent = 'در حال ذخیره...';

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('action', 'quick_stock');
        formData.append('id', currentPopoverItemId);
        formData.append('stock', newStock);

        try {
            const response = await fetch('gift_items.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const res = await response.json();
            if (res.ok) {
                // Update table cell
                const row = document.getElementById('row-' + currentPopoverItemId);
                if (row) {
                    const badge = row.querySelector('.gift-stock-badge');
                    if (badge) {
                        badge.textContent = newStock === 0 ? 'ناموجود' : toFaDigits(newStock);
                        badge.className = 'gift-stock-badge ' + (newStock === 0 ? 'gift-stock-out' : (newStock <= 5 ? 'gift-stock-low' : 'gift-stock-ok'));
                        badge.setAttribute('onclick', `openInlineStockPopover(event, ${currentPopoverItemId}, ${newStock}, '${row.querySelector('.gift-item-title').textContent.trim()}')`);
                    }
                }

                // Update memory map
                if (giftItemsMap[currentPopoverItemId]) {
                    giftItemsMap[currentPopoverItemId].stock = newStock;
                }

                // If currently open in studio, update studio stock input too
                const studioId = document.getElementById('studioItemId');
                if (studioId && parseInt(studioId.value, 10) === currentPopoverItemId) {
                    document.getElementById('studioStock').value = newStock;
                }

                showToast('موجودی انبار با موفقیت به‌روزرسانی شد.');
                closeInlineStockPopover();
            } else {
                showToast(res.error || 'خطا در ثبت موجودی.', 'error');
            }
        } catch (e) {
            showToast('خطای ارتباط با سرور.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'ذخیره (Enter)';
        }
    };

    // Close popover when clicking outside
    document.addEventListener('click', function (e) {
        const popover = document.getElementById('giftStockPopover');
        if (popover && popover.style.display !== 'none') {
            if (!popover.contains(e.target) && !e.target.closest('.gift-stock-badge')) {
                closeInlineStockPopover();
            }
        }
    });

    function initKeyboardShortcuts() {
        document.addEventListener('keydown', function (e) {
            const popover = document.getElementById('giftStockPopover');
            if (popover && popover.style.display !== 'none') {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closeInlineStockPopover();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    saveInlineStockPopover();
                }
            }
        });
    }

    // =========================================================================
    // 4. AJAX Toggle Active
    // =========================================================================
    window.toggleItemActive = async function (id, checkbox) {
        const originalChecked = checkbox.checked;
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('action', 'toggle_active');
        formData.append('id', id);

        try {
            const response = await fetch('gift_items.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (!data.ok) {
                showToast(data.error || 'خطا در تغییر وضعیت.', 'error');
                checkbox.checked = !originalChecked;
            } else {
                if (giftItemsMap[id]) {
                    giftItemsMap[id].is_active = data.is_active;
                }
                showToast(data.is_active ? 'قلم کالا فعال شد.' : 'قلم کالا غیرفعال شد.');
            }
        } catch (e) {
            showToast('خطای ارتباط با سرور.', 'error');
            checkbox.checked = !originalChecked;
        }
    };

    // =========================================================================
    // 5. Studio Population & Mode Management
    // =========================================================================
    window.loadItemIntoStudio = function (id) {
        const item = giftItemsMap[id];
        if (!item) return;

        // Highlight table row
        document.querySelectorAll('#giftItemsTable tr').forEach(r => r.classList.remove('is-selected'));
        const selectedRow = document.getElementById('row-' + id);
        if (selectedRow) selectedRow.classList.add('is-selected');

        const studio = document.getElementById('giftStudioCard');
        studio.classList.add('mode-editing');

        document.getElementById('studioTitleText').textContent = 'ویرایش: ' + item.name;
        document.getElementById('studioModeBadge').textContent = 'حالت ویرایش';
        document.getElementById('studioSubmitText').textContent = 'ذخیره تغییرات قلم';
        document.getElementById('studioResetBtn').style.display = 'inline-block';

        document.getElementById('studioItemId').value = item.id;
        document.getElementById('studioSortOrder').value = item.sort_order || 0;
        document.getElementById('studioNameInput').value = item.name;
        document.getElementById('studioTaglineInput').value = item.tagline || '';
        document.getElementById('studioBadgeInput').value = item.badge_text || '';
        document.getElementById('studioPreviewImg').src = item.image_url;
        document.getElementById('studioIsGiftable').checked = (item.is_giftable === 1);
        document.getElementById('studioIsPostOrderable').checked = (item.is_post_orderable === 1);

        handlePostOrderRoleToggle(item.is_post_orderable === 1);

        document.getElementById('studioCostPrice').value = item.cost_price ? Number(item.cost_price).toLocaleString('en-US') : '';
        document.getElementById('studioPostOrderPrice').value = item.post_order_price ? Number(item.post_order_price).toLocaleString('en-US') : '';
        document.getElementById('studioMinCartTotal').value = item.min_cart_total ? Number(item.min_cart_total).toLocaleString('en-US') : '';
        document.getElementById('studioStock').value = item.stock;
        document.getElementById('studioIsActive').checked = (item.is_active === 1);

        calculateStudioMargin();
        syncLiveCustomerPreview();

        studio.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    window.resetStudioToCreate = function () {
        document.querySelectorAll('#giftItemsTable tr').forEach(r => r.classList.remove('is-selected'));

        const studio = document.getElementById('giftStudioCard');
        studio.classList.remove('mode-editing');

        document.getElementById('studioTitleText').textContent = 'افزودن قلم هدیه یا جانبی جدید';
        document.getElementById('studioModeBadge').textContent = 'قلم جدید';
        document.getElementById('studioSubmitText').textContent = 'ثبت قلم جدید در کاتالوگ';
        document.getElementById('studioResetBtn').style.display = 'none';

        document.getElementById('studioItemId').value = '0';
        document.getElementById('studioSortOrder').value = '0';
        document.getElementById('studioNameInput').value = '';
        document.getElementById('studioTaglineInput').value = '';
        document.getElementById('studioBadgeInput').value = '';
        document.getElementById('studioPreviewImg').src = '/assets/img/placeholder-sock.svg';
        document.getElementById('studioImageInput').value = '';
        document.getElementById('studioIsGiftable').checked = true;
        document.getElementById('studioIsPostOrderable').checked = false;

        handlePostOrderRoleToggle(false);

        document.getElementById('studioCostPrice').value = '';
        document.getElementById('studioPostOrderPrice').value = '';
        document.getElementById('studioMinCartTotal').value = '';
        document.getElementById('studioStock').value = '0';
        document.getElementById('studioIsActive').checked = true;

        calculateStudioMargin();
        syncLiveCustomerPreview();
    };

    window.handlePostOrderRoleToggle = function (enabled) {
        const priceGroup = document.getElementById('studioPostOrderPriceGroup');
        const minCartGroup = document.getElementById('studioMinCartGroup');
        const simSection = document.getElementById('customerSimulationSection');

        if (priceGroup) {
            priceGroup.style.opacity = enabled ? '1' : '0.5';
            priceGroup.style.pointerEvents = enabled ? 'auto' : 'none';
        }
        if (minCartGroup) {
            minCartGroup.style.opacity = enabled ? '1' : '0.5';
            minCartGroup.style.pointerEvents = enabled ? 'auto' : 'none';
        }
        if (simSection) {
            simSection.style.display = enabled ? 'block' : 'none';
        }

        calculateStudioMargin();
        syncLiveCustomerPreview();
    };

    window.applyBadgeChip = function (text) {
        const badgeInput = document.getElementById('studioBadgeInput');
        if (badgeInput) {
            badgeInput.value = text;
            syncLiveCustomerPreview();
        }
    };

    // =========================================================================
    // 6. Live Margin Calculator with Gauge Bar
    // =========================================================================
    window.calculateStudioMargin = function () {
        const isPostOrder = document.getElementById('studioIsPostOrderable')?.checked;
        const costRaw = document.getElementById('studioCostPrice')?.value.replace(/\D/g, '') || '';
        const postRaw = document.getElementById('studioPostOrderPrice')?.value.replace(/\D/g, '') || '';

        const profitEl = document.getElementById('studioUnitProfit');
        const marginEl = document.getElementById('studioMarginPercent');
        const barEl = document.getElementById('studioMarginBar');
        if (!profitEl || !marginEl) return;

        if (!isPostOrder || postRaw === '' || costRaw === '') {
            profitEl.textContent = '—';
            marginEl.textContent = '—';
            marginEl.style.background = 'var(--gift-border-subtle)';
            marginEl.style.color = 'var(--gift-text-muted)';
            if (barEl) barEl.style.width = '0%';
            return;
        }

        const cost = parseInt(costRaw, 10);
        const sale = parseInt(postRaw, 10);

        if (sale <= 0) {
            profitEl.textContent = '—';
            marginEl.textContent = '—';
            if (barEl) barEl.style.width = '0%';
            return;
        }

        const profit = sale - cost;
        const marginPercent = Math.round((profit / sale) * 100);

        profitEl.textContent = formatPriceJs(profit);
        marginEl.textContent = toFaDigits(marginPercent) + '٪';

        const clampedWidth = Math.max(0, Math.min(100, marginPercent));
        if (barEl) barEl.style.width = clampedWidth + '%';

        if (marginPercent >= 40) {
            marginEl.style.background = 'var(--gift-success-light)';
            marginEl.style.color = 'var(--gift-success)';
            if (barEl) barEl.style.background = 'var(--gift-success)';
        } else if (marginPercent >= 20) {
            marginEl.style.background = 'var(--gift-warning-light)';
            marginEl.style.color = '#D97706';
            if (barEl) barEl.style.background = 'var(--gift-warning)';
        } else {
            marginEl.style.background = 'var(--gift-danger-light)';
            marginEl.style.color = 'var(--gift-danger)';
            if (barEl) barEl.style.background = 'var(--gift-danger)';
        }
    };

    // =========================================================================
    // 7. Live Customer Cart Simulator
    // =========================================================================
    window.syncLiveCustomerPreview = function () {
        const nameVal = document.getElementById('studioNameInput')?.value.trim() || 'عنوان قلم کالا';
        const taglineVal = document.getElementById('studioTaglineInput')?.value.trim() || '';
        const badgeVal = document.getElementById('studioBadgeInput')?.value.trim() || '';
        const postRaw = document.getElementById('studioPostOrderPrice')?.value.replace(/\D/g, '') || '';
        const previewSrc = document.getElementById('studioPreviewImg')?.src || '/assets/img/placeholder-sock.svg';

        const simTitle = document.getElementById('simTitle');
        const simTagline = document.getElementById('simTagline');
        const simBadge = document.getElementById('simBadge');
        const simPrice = document.getElementById('simPrice');
        const simThumb = document.getElementById('simThumb');

        if (simTitle) simTitle.textContent = nameVal;
        if (simThumb) simThumb.src = previewSrc;

        if (simTagline) {
            if (taglineVal !== '') {
                simTagline.textContent = taglineVal;
                simTagline.style.display = 'block';
            } else {
                simTagline.style.display = 'none';
            }
        }

        if (simBadge) {
            if (badgeVal !== '') {
                simBadge.textContent = badgeVal;
                simBadge.style.display = 'inline-block';
            } else {
                simBadge.style.display = 'none';
            }
        }

        if (simPrice) {
            simPrice.textContent = postRaw !== '' ? formatPriceJs(parseInt(postRaw, 10)) : '—';
        }
    };

    window.handleStudioImagePreview = function (input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const previewImg = document.getElementById('studioPreviewImg');
                if (previewImg) {
                    previewImg.src = e.target.result;
                }
                const simThumb = document.getElementById('simThumb');
                if (simThumb) {
                    simThumb.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

})();
