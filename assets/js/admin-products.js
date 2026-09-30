/**
 * AB-Socks Admin Products Catalog Client Logic (admin-products.js)
 * Side Dossier Drawer, Optimistic Toggles, Variant Popovers, Steppers, & Floating Bulk Actions.
 */

(function () {
    'use strict';

    // State
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    if (!csrfToken) {
        var csrfInput = document.querySelector('input[name="csrf_token"]');
        if (csrfInput) csrfToken = csrfInput.value;
    }

    var productsMap = window.productsDataMap || {};
    var activeDrawerId = null;
    var lastCheckedCheckbox = null;

    // DOM Elements
    var drawerBackdrop = document.getElementById('prodDrawerBackdrop');
    var drawer = document.getElementById('prodDrawer');
    var bulkBar = document.getElementById('prodBulkBar');
    var bulkCountEl = document.getElementById('prodBulkCount');
    var checkAll = document.getElementById('prodCheckAll');
    var rowCheckboxes = document.querySelectorAll('.prod-check-row');
    var toastEl = document.getElementById('prodToast');

    // ==========================================
    // 1. Toast Notification Helper
    // ==========================================
    function showToast(message, isError) {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.classList.toggle('toast-error', !!isError);
        toastEl.classList.add('is-show');
        setTimeout(function () {
            toastEl.classList.remove('is-show');
        }, 3200);
    }

    // ==========================================
    // 2. Side Dossier Drawer
    // ==========================================
    function openDrawer(id) {
        var p = productsMap[id];
        if (!p || !drawer || !drawerBackdrop) return;
        activeDrawerId = id;

        // Highlight active row in table
        document.querySelectorAll('.prod-row').forEach(function (r) {
            r.classList.remove('is-active-drawer');
        });
        var activeRow = document.getElementById('prod-row-' + id);
        if (activeRow) activeRow.classList.add('is-active-drawer');

        // Populate Header
        var thumb = document.getElementById('drawerThumb');
        if (thumb) thumb.src = p.image_url || '/assets/img/placeholder-sock.svg';

        var nameEl = document.getElementById('drawerName');
        if (nameEl) nameEl.textContent = p.name;

        var skuEl = document.getElementById('drawerSku');
        if (skuEl) skuEl.textContent = p.sku ? 'کد SKU: ' + p.sku : 'بدون SKU';

        var fullEditBtn = document.getElementById('drawerFullEdit');
        if (fullEditBtn) fullEditBtn.href = 'product_edit.php?id=' + id;

        // Populate Financial KPIs
        var priceEl = document.getElementById('drawerPrice');
        if (priceEl) priceEl.textContent = p.price_fmt;

        var costEl = document.getElementById('drawerCost');
        if (costEl) costEl.textContent = p.cost_fmt || '—';

        var marginEl = document.getElementById('drawerMargin');
        if (marginEl) {
            if (p.margin_percent !== null && p.margin_percent !== undefined) {
                marginEl.textContent = p.margin_percent + '٪';
                marginEl.style.color = p.margin_percent > 0 ? 'var(--prod-success)' : 'inherit';
            } else {
                marginEl.textContent = '—';
                marginEl.style.color = 'inherit';
            }
        }

        // Populate Gallery Strip
        var galleryBox = document.getElementById('drawerGallery');
        var gallerySection = document.getElementById('drawerGallerySection');
        if (galleryBox && gallerySection) {
            galleryBox.innerHTML = '';
            if (p.gallery_urls && p.gallery_urls.length > 0) {
                gallerySection.style.display = 'block';
                p.gallery_urls.forEach(function (url) {
                    var img = document.createElement('img');
                    img.src = url;
                    img.className = 'prod-gallery-item';
                    img.alt = '';
                    galleryBox.appendChild(img);
                });
            } else {
                gallerySection.style.display = 'none';
            }
        }

        // Populate Quick Stock Editor
        var stockContainer = document.getElementById('drawerStockEditor');
        if (stockContainer) {
            stockContainer.innerHTML = '';
            if (p.variants && p.variants.length > 0) {
                var table = document.createElement('table');
                table.className = 'prod-stock-table';
                table.innerHTML = '<thead><tr><th>واریانت (سایز / رنگ)</th><th style="width:120px;text-align:center;">موجودی</th></tr></thead><tbody></tbody>';
                var tbody = table.querySelector('tbody');

                p.variants.forEach(function (v) {
                    var label = [v.size, v.color].filter(Boolean).join(' - ') || 'پیش‌فرض';
                    var tr = document.createElement('tr');
                    tr.innerHTML = '<td><strong>' + escapeHtml(label) + '</strong></td>' +
                        '<td style="text-align:center;">' +
                        '<div class="prod-stepper">' +
                        '<button type="button" class="prod-stepper-btn btn-step-minus" data-target="v_' + v.id + '">−</button>' +
                        '<input type="number" min="0" class="prod-stepper-input" id="drawer_v_' + v.id + '" value="' + v.stock + '">' +
                        '<button type="button" class="prod-stepper-btn btn-step-plus" data-target="v_' + v.id + '">+</button>' +
                        '</div>' +
                        '</td>';
                    tbody.appendChild(tr);
                });
                stockContainer.appendChild(table);
            } else {
                // Simple product without variants
                var div = document.createElement('div');
                div.style.display = 'flex';
                div.style.alignItems = 'center';
                div.style.justifyContent = 'space-between';
                div.style.padding = '12px';
                div.style.background = '#FAF8F5';
                div.style.borderRadius = 'var(--prod-radius-sm)';
                div.innerHTML = '<span>موجودی کل انبار:</span>' +
                    '<div class="prod-stepper">' +
                    '<button type="button" class="prod-stepper-btn btn-step-minus" data-target="parent">−</button>' +
                    '<input type="number" min="0" class="prod-stepper-input" id="drawer_parent_stock" value="' + (p.stock || 0) + '">' +
                    '<button type="button" class="prod-stepper-btn btn-step-plus" data-target="parent">+</button>' +
                    '</div>';
                stockContainer.appendChild(div);
            }
        }

        // Show Drawer
        drawerBackdrop.classList.add('is-open');
        drawer.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        if (!drawer || !drawerBackdrop) return;
        drawerBackdrop.classList.remove('is-open');
        drawer.classList.remove('is-open');
        document.body.style.overflow = '';
        if (activeDrawerId) {
            var activeRow = document.getElementById('prod-row-' + activeDrawerId);
            if (activeRow) activeRow.classList.remove('is-active-drawer');
            activeDrawerId = null;
        }
    }

    // Save Quick Stock
    function saveStock() {
        if (!activeDrawerId || !productsMap[activeDrawerId]) return;
        var p = productsMap[activeDrawerId];
        var payload = new FormData();
        payload.append('action', 'quick_stock');
        payload.append('id', activeDrawerId);
        payload.append('csrf_token', csrfToken);
        payload.append('ajax', '1');

        var totalStockCalc = 0;
        if (p.variants && p.variants.length > 0) {
            p.variants.forEach(function (v) {
                var input = document.getElementById('drawer_v_' + v.id);
                var val = input ? parseInt(input.value, 10) || 0 : v.stock;
                payload.append('variant_stocks[' + v.id + ']', val);
                totalStockCalc += val;
            });
        } else {
            var pInput = document.getElementById('drawer_parent_stock');
            var pVal = pInput ? parseInt(pInput.value, 10) || 0 : 0;
            payload.append('stock', pVal);
            totalStockCalc = pVal;
        }

        var saveBtn = document.getElementById('drawerSaveStock');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'در حال ذخیره...';
        }

        fetch('products.php', {
            method: 'POST',
            body: payload,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.ok) {
                    showToast('موجودی انبار با موفقیت به‌روز شد.');
                    // Update dataset
                    p.stock = totalStockCalc;
                    if (p.variants && p.variants.length > 0) {
                        p.variants.forEach(function (v) {
                            var inp = document.getElementById('drawer_v_' + v.id);
                            if (inp) v.stock = parseInt(inp.value, 10) || 0;
                        });
                    }

                    // Reactive update of row's stock cell
                    var stockBadge = document.getElementById('stock-badge-' + activeDrawerId);
                    if (stockBadge) {
                        stockBadge.textContent = totalStockCalc.toLocaleString('fa-IR') + ' عدد';
                        stockBadge.className = 'prod-stock-pill ' + (totalStockCalc === 0 ? 'stock-red' : (totalStockCalc <= 3 ? 'stock-amber' : 'stock-green'));
                    }
                    closeDrawer();
                } else {
                    showToast(data.error || 'خطا در به‌روزرسانی موجودی.', true);
                }
            })
            .catch(function () {
                showToast('خطای ارتباط با سرور.', true);
            })
            .finally(function () {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'ذخیره تغییرات موجودی';
                }
            });
    }

    // ==========================================
    // 3. Optimistic AJAX Toggles
    // ==========================================
    function handleToggle(checkbox) {
        var id = checkbox.getAttribute('data-id');
        var field = checkbox.getAttribute('data-field');
        var isChecked = checkbox.checked;
        var switchWrapper = checkbox.closest('.prod-switch');

        if (switchWrapper) switchWrapper.classList.add('is-busy');

        var payload = new FormData();
        payload.append('action', 'toggle');
        payload.append('id', id);
        payload.append('field', field);
        payload.append('csrf_token', csrfToken);
        payload.append('ajax', '1');

        fetch('products.php', {
            method: 'POST',
            body: payload,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.ok) {
                    // Revert switch on failure
                    checkbox.checked = !isChecked;
                    showToast(data.error || 'خطا در تغییر وضعیت.', true);
                } else {
                    showToast('وضعیت کالا تغییر کرد.');
                    if (productsMap[id]) {
                        productsMap[id][field] = data.new_value;
                    }
                }
            })
            .catch(function () {
                checkbox.checked = !isChecked;
                showToast('خطای شبکه در ثبت وضعیت.', true);
            })
            .finally(function () {
                if (switchWrapper) switchWrapper.classList.remove('is-busy');
            });
    }

    // ==========================================
    // 4. Variant Popovers
    // ==========================================
    function setupVariantPopovers() {
        document.querySelectorAll('.prod-variant-trigger').forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                var popover = this.parentElement.querySelector('.prod-variant-popover');
                if (!popover) return;
                var isOpen = popover.classList.contains('is-open');
                closeAllPopovers();
                if (!isOpen) popover.classList.add('is-open');
            });
        });

        document.addEventListener('click', function () {
            closeAllPopovers();
        });
    }

    function closeAllPopovers() {
        document.querySelectorAll('.prod-variant-popover.is-open').forEach(function (p) {
            p.classList.remove('is-open');
        });
    }

    // ==========================================
    // 5. Checkboxes & Floating Bulk Action Bar
    // ==========================================
    function updateBulkBar() {
        var selected = Array.from(document.querySelectorAll('.prod-check-row:checked'));
        var count = selected.length;
        if (!bulkBar) return;

        if (count > 0) {
            bulkBar.classList.add('is-visible');
            if (bulkCountEl) bulkCountEl.textContent = count.toLocaleString('fa-IR') + ' کالا انتخاب شد';
        } else {
            bulkBar.classList.remove('is-visible');
        }

        // Update select-all state
        if (checkAll) {
            checkAll.checked = count > 0 && count === rowCheckboxes.length;
            checkAll.indeterminate = count > 0 && count < rowCheckboxes.length;
        }

        // Highlight selected rows
        rowCheckboxes.forEach(function (cb) {
            var row = cb.closest('.prod-row');
            if (row) row.classList.toggle('is-selected', cb.checked);
        });
    }

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.prod-check-row:checked')).map(function (cb) {
            return parseInt(cb.value, 10);
        });
    }

    function executeBulkAction(action, extraParams) {
        var ids = getSelectedIds();
        if (ids.length === 0) return;

        var payload = new FormData();
        payload.append('action', action);
        payload.append('csrf_token', csrfToken);
        payload.append('ajax', '1');

        ids.forEach(function (id) {
            payload.append('ids[]', id);
        });

        if (extraParams) {
            for (var key in extraParams) {
                payload.append(key, extraParams[key]);
            }
        }

        fetch('products.php', {
            method: 'POST',
            body: payload,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.ok) {
                    showToast('عملیات گروهی با موفقیت اعمال شد.');
                    setTimeout(function () { window.location.reload(); }, 600);
                } else {
                    showToast(data.error || 'خطا در اجرای عملیات گروهی.', true);
                }
            })
            .catch(function () {
                showToast('خطای ارتباط با سرور.', true);
            });
    }

    // ==========================================
    // 6. Event Listeners & Delegation
    // ==========================================
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle switches
        document.querySelectorAll('.prod-switch input').forEach(function (input) {
            input.addEventListener('change', function () {
                handleToggle(this);
            });
        });

        // Drawer openers (row click or button)
        document.querySelectorAll('.btn-inspect').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var id = this.getAttribute('data-id');
                if (id) openDrawer(id);
            });
        });

        document.querySelectorAll('.prod-title-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var id = this.getAttribute('data-id');
                if (id) openDrawer(id);
            });
        });

        // Drawer Stepper Buttons
        if (drawer) {
            drawer.addEventListener('click', function (e) {
                var btn = e.target.closest('.prod-stepper-btn');
                if (!btn) return;
                var target = btn.getAttribute('data-target');
                var input = target === 'parent'
                    ? document.getElementById('drawer_parent_stock')
                    : document.getElementById('drawer_' + target);
                if (!input) return;

                var currentVal = parseInt(input.value, 10) || 0;
                if (btn.classList.contains('btn-step-plus')) {
                    input.value = currentVal + 1;
                } else if (btn.classList.contains('btn-step-minus')) {
                    input.value = Math.max(0, currentVal - 1);
                }
            });
        }

        // Drawer Close
        var closeBtn = document.getElementById('drawerClose');
        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        if (drawerBackdrop) drawerBackdrop.addEventListener('click', closeDrawer);

        var drawerSave = document.getElementById('drawerSaveStock');
        if (drawerSave) drawerSave.addEventListener('click', saveStock);

        // Escape Key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDrawer();
                closeAllPopovers();
            }
        });

        // SKU Copy Click
        document.querySelectorAll('.prod-sku-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                var sku = this.getAttribute('data-sku');
                if (!sku) return;
                navigator.clipboard.writeText(sku).then(function () {
                    showToast('کد SKU در حافظه کپی شد: ' + sku);
                });
            });
        });

        // Popovers
        setupVariantPopovers();

        // Check All
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                var isChecked = this.checked;
                rowCheckboxes.forEach(function (cb) {
                    cb.checked = isChecked;
                });
                updateBulkBar();
            });
        }

        // Shift + Click Range Selection for Checkboxes
        rowCheckboxes.forEach(function (cb) {
            cb.addEventListener('click', function (e) {
                if (e.shiftKey && lastCheckedCheckbox && lastCheckedCheckbox !== this) {
                    var inRange = false;
                    rowCheckboxes.forEach(function (item) {
                        if (item === cb || item === lastCheckedCheckbox) {
                            inRange = !inRange;
                        }
                        if (inRange || item === cb || item === lastCheckedCheckbox) {
                            item.checked = lastCheckedCheckbox.checked;
                        }
                    });
                }
                lastCheckedCheckbox = this;
                updateBulkBar();
            });
        });

        // Bulk Actions
        var bulkActivate = document.getElementById('bulkActivate');
        if (bulkActivate) {
            bulkActivate.addEventListener('click', function () {
                executeBulkAction('bulk_status', { field: 'is_active', value: 1 });
            });
        }

        var bulkDeactivate = document.getElementById('bulkDeactivate');
        if (bulkDeactivate) {
            bulkDeactivate.addEventListener('click', function () {
                executeBulkAction('bulk_status', { field: 'is_active', value: 0 });
            });
        }

        var bulkDelete = document.getElementById('bulkDelete');
        if (bulkDelete) {
            bulkDelete.addEventListener('click', function () {
                var count = getSelectedIds().length;
                if (confirm('آیا از حذف قطعی ' + count.toLocaleString('fa-IR') + ' محصول انتخاب‌شده اطمینان دارید؟ این عملیات غیرقابل بازگشت است.')) {
                    executeBulkAction('bulk_delete');
                }
            });
        }

        var bulkCategory = document.getElementById('bulkCategory');
        if (bulkCategory) {
            bulkCategory.addEventListener('click', function () {
                var catSelect = document.getElementById('bulkCategorySelect');
                var catId = catSelect ? parseInt(catSelect.value, 10) : 0;
                if (!catId) {
                    alert('لطفاً دسته‌بندی مقصد را انتخاب کنید.');
                    return;
                }
                executeBulkAction('bulk_category', { category_id: catId });
            });
        }

        var bulkCancel = document.getElementById('bulkCancel');
        if (bulkCancel) {
            bulkCancel.addEventListener('click', function () {
                rowCheckboxes.forEach(function (cb) { cb.checked = false; });
                if (checkAll) checkAll.checked = false;
                updateBulkBar();
            });
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

})();
