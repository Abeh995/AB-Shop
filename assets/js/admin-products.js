/**
 * AB-Socks Admin Products Catalog Client Logic (admin-products.js)
 * High-Density Workstation: Side Dossier Drawer, Optimistic Toggles, Steppers, & Floating Bulk Actions.
 */

(function () {
    'use strict';

    // 1. State & Dataset Initialization
    var csrfToken = (window.AB && window.AB.api) ? window.AB.api.csrfToken() : '';
    if (!csrfToken) {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.content) csrfToken = meta.content;
        else {
            var input = document.querySelector('input[name="csrf_token"]');
            if (input && input.value) csrfToken = input.value;
        }
    }

    var jsonEl = document.getElementById('productsJsonData');
    var productsMap = {};
    if (jsonEl && jsonEl.textContent) {
        try {
            productsMap = JSON.parse(jsonEl.textContent);
        } catch (e) {
            console.error('[Products] Failed to parse productsJsonData:', e);
        }
    } else if (window.productsDataMap) {
        productsMap = window.productsDataMap;
    }
    window.productsDataMap = productsMap;

    var activeDrawerId = null;
    var lastCheckedCheckbox = null;

    // Note: showToast and toFaDigits are provided globally by assets/js/ab-kit.js

    // =========================================================================
    // 2. Side Dossier Quick Inspect Drawer
    // =========================================================================
    function openDrawer(id) {
        var p = productsMap[id];
        var drawer = document.getElementById('prodDrawer');
        if (!p || !drawer) return;
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
                var pctStr = (window.AB && window.AB.fmt) ? window.AB.fmt.faDigits(p.margin_percent) : String(p.margin_percent);
                marginEl.textContent = pctStr + '٪';
            } else {
                marginEl.textContent = '—';
            }
        }

        // Populate Gallery Strip
        var galleryBox = document.getElementById('drawerGallery');
        var gallerySection = document.getElementById('drawerGallerySection');
        if (galleryBox && gallerySection) {
            galleryBox.innerHTML = '';
            if (p.gallery_urls && p.gallery_urls.length > 0) {
                gallerySection.removeAttribute('hidden');
                p.gallery_urls.forEach(function (url) {
                    var img = document.createElement('img');
                    img.src = url;
                    img.className = 'prod-gallery-item';
                    img.alt = '';
                    galleryBox.appendChild(img);
                });
            } else {
                gallerySection.setAttribute('hidden', '');
            }
        }

        // Populate Quick Stock Editor
        var stockContainer = document.getElementById('drawerStockEditor');
        if (stockContainer) {
            stockContainer.innerHTML = '';
            if (p.variants && p.variants.length > 0) {
                var table = document.createElement('table');
                table.className = 'ab-table prod-stock-table';
                table.innerHTML = '<thead><tr><th>واریانت (سایز / رنگ)</th><th style="width:130px;text-align:center;">موجودی</th></tr></thead><tbody></tbody>';
                var tbody = table.querySelector('tbody');

                p.variants.forEach(function (v) {
                    var label = [v.size, v.color].filter(Boolean).join(' - ') || 'پیش‌فرض';
                    var tr = document.createElement('tr');
                    var safeLabel = window.AB && window.AB.fmt ? window.AB.fmt.esc(label) : label;
                    tr.innerHTML = '<td><strong>' + safeLabel + '</strong></td>' +
                        '<td style="text-align:center;">' +
                        '<div class="prod-stepper">' +
                        '<button type="button" class="prod-stepper-btn btn-step-minus" data-target="v_' + v.id + '" aria-label="کاهش">−</button>' +
                        '<input type="number" min="0" class="prod-stepper-input" id="drawer_v_' + v.id + '" value="' + v.stock + '">' +
                        '<button type="button" class="prod-stepper-btn btn-step-plus" data-target="v_' + v.id + '" aria-label="افزایش">+</button>' +
                        '</div>' +
                        '</td>';
                    tbody.appendChild(tr);
                });
                stockContainer.appendChild(table);
            } else {
                // Simple product without variants
                var div = document.createElement('div');
                div.className = 'ab-cluster ab-cluster--between';
                div.style.padding = 'var(--space-3)';
                div.style.background = 'var(--surface-2)';
                div.style.borderRadius = 'var(--radius-md)';
                div.innerHTML = '<span>موجودی کل انبار:</span>' +
                    '<div class="prod-stepper">' +
                    '<button type="button" class="prod-stepper-btn btn-step-minus" data-target="parent" aria-label="کاهش">−</button>' +
                    '<input type="number" min="0" class="prod-stepper-input" id="drawer_parent_stock" value="' + (p.stock || 0) + '">' +
                    '<button type="button" class="prod-stepper-btn btn-step-plus" data-target="parent" aria-label="افزایش">+</button>' +
                    '</div>';
                stockContainer.appendChild(div);
            }
        }

        // Open Dialog Drawer via AB.modal
        if (window.AB && window.AB.modal) {
            window.AB.modal.open('prodDrawer');
        } else if (typeof drawer.showModal === 'function') {
            drawer.showModal();
        } else {
            drawer.setAttribute('open', '');
        }
    }

    function closeDrawer() {
        var drawer = document.getElementById('prodDrawer');
        if (!drawer) return;

        if (window.AB && window.AB.modal) {
            window.AB.modal.close('prodDrawer');
        } else if (typeof drawer.close === 'function') {
            drawer.close();
        } else {
            drawer.removeAttribute('open');
        }

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
                    showToast('موجودی انبار با موفقیت به‌روز شد.', 'success');
                    p.stock = totalStockCalc;
                    if (p.variants && p.variants.length > 0) {
                        p.variants.forEach(function (v) {
                            var inp = document.getElementById('drawer_v_' + v.id);
                            if (inp) v.stock = parseInt(inp.value, 10) || 0;
                        });
                    }

                    // Reactive update of row stock badge
                    var stockBadge = document.getElementById('stock-badge-' + activeDrawerId);
                    if (stockBadge) {
                        var stockFa = (window.AB && window.AB.fmt) ? window.AB.fmt.faDigits(totalStockCalc) : String(totalStockCalc);
                        stockBadge.textContent = stockFa + ' عدد';
                        var state = totalStockCalc === 0 ? 'danger' : (totalStockCalc <= 3 ? 'warning' : 'success');
                        stockBadge.setAttribute('data-state', state);
                    }
                    closeDrawer();
                } else {
                    showToast(data.error || 'خطا در به‌روزرسانی موجودی.', 'error');
                }
            })
            .catch(function () {
                showToast('خطای ارتباط با سرور.', 'error');
            })
            .finally(function () {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'ذخیره تغییرات موجودی';
                }
            });
    }

    // =========================================================================
    // 3. Optimistic AJAX Toggles
    // =========================================================================
    function handleToggle(checkbox) {
        var id = checkbox.getAttribute('data-id');
        var field = checkbox.getAttribute('data-field');
        var isChecked = checkbox.checked;
        var switchWrapper = checkbox.closest('.ab-switch, .prod-switch');

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
                    checkbox.checked = !isChecked;
                    showToast(data.error || 'خطا در تغییر وضعیت.', 'error');
                } else {
                    showToast('وضعیت کالا تغییر کرد.', 'success');
                    if (productsMap[id]) {
                        productsMap[id][field] = data.new_value;
                    }
                }
            })
            .catch(function () {
                checkbox.checked = !isChecked;
                showToast('خطای شبکه در ثبت وضعیت.', 'error');
            })
            .finally(function () {
                if (switchWrapper) switchWrapper.classList.remove('is-busy');
            });
    }

    // =========================================================================
    // 4. Checkboxes & Floating Bulk Action Bar
    // =========================================================================
    function getRowCheckboxes() {
        return document.querySelectorAll('input[name="selected_ids[]"], .prod-check-row');
    }

    function updateBulkBar() {
        var rowCbs = getRowCheckboxes();
        var selected = Array.from(rowCbs).filter(function (cb) { return cb.checked; });
        var count = selected.length;
        var bulkBar = document.getElementById('prodBulkBar');
        var bulkCountEl = document.getElementById('prodBulkCount');
        var checkAll = document.querySelector('[data-ab-select-all]') || document.getElementById('prodCheckAll');

        if (!bulkBar) return;

        if (count > 0) {
            bulkBar.classList.add('is-visible');
            var countFa = (window.AB && window.AB.fmt) ? window.AB.fmt.faDigits(count) : String(count);
            if (bulkCountEl) bulkCountEl.textContent = countFa + ' کالا انتخاب شد';
        } else {
            bulkBar.classList.remove('is-visible');
        }

        // Update select-all state
        if (checkAll) {
            checkAll.checked = count > 0 && count === rowCbs.length;
            checkAll.indeterminate = count > 0 && count < rowCbs.length;
        }

        // Highlight selected rows
        rowCbs.forEach(function (cb) {
            var row = cb.closest('.prod-row, tr');
            if (row) row.classList.toggle('is-selected', cb.checked);
        });
    }

    function getSelectedIds() {
        return Array.from(getRowCheckboxes())
            .filter(function (cb) { return cb.checked; })
            .map(function (cb) { return parseInt(cb.value, 10); })
            .filter(Boolean);
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
                    showToast('عملیات گروهی با موفقیت اعمال شد.', 'success');
                    setTimeout(function () { window.location.reload(); }, 600);
                } else {
                    showToast(data.error || 'خطا در اجرای عملیات گروهی.', 'error');
                }
            })
            .catch(function () {
                showToast('خطای ارتباط با سرور.', 'error');
            });
    }

    // =========================================================================
    // 5. Event Binding & DOM Ready
    // =========================================================================
    function init() {
        // Toggle Switches
        document.querySelectorAll('.ab-switch input[data-field], .prod-switch input[data-field]').forEach(function (input) {
            input.addEventListener('change', function () {
                handleToggle(this);
            });
        });

        // Stepper Buttons in Drawer
        var drawer = document.getElementById('prodDrawer');
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

        // Save Stock in Drawer
        var drawerSave = document.getElementById('drawerSaveStock');
        if (drawerSave) drawerSave.addEventListener('click', saveStock);

        // SKU Copy Click
        document.querySelectorAll('.prod-sku-chip, [data-sku]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                var sku = this.getAttribute('data-sku') || this.textContent.trim();
                if (!sku) return;
                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    navigator.clipboard.writeText(sku).then(function () {
                        showToast('کد SKU کپی شد: ' + sku, 'info');
                    }).catch(function () {
                        showToast('کد SKU: ' + sku, 'info');
                    });
                } else {
                    showToast('کد SKU: ' + sku, 'info');
                }
            });
        });

        // Select All Handler
        var checkAll = document.querySelector('[data-ab-select-all]') || document.getElementById('prodCheckAll');
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                var isChecked = this.checked;
                getRowCheckboxes().forEach(function (cb) {
                    cb.checked = isChecked;
                });
                updateBulkBar();
            });
        }

        // Row Checkboxes change & Shift+Click range
        document.addEventListener('change', function (e) {
            if (e.target.matches('input[name="selected_ids[]"], .prod-check-row')) {
                updateBulkBar();
            }
        });

        document.addEventListener('click', function (e) {
            var cb = e.target.closest('input[name="selected_ids[]"], .prod-check-row');
            if (cb) {
                var rowCbs = Array.from(getRowCheckboxes());
                if (e.shiftKey && lastCheckedCheckbox && lastCheckedCheckbox !== cb) {
                    var inRange = false;
                    rowCbs.forEach(function (item) {
                        if (item === cb || item === lastCheckedCheckbox) {
                            inRange = !inRange;
                        }
                        if (inRange || item === cb || item === lastCheckedCheckbox) {
                            item.checked = lastCheckedCheckbox.checked;
                        }
                    });
                }
                lastCheckedCheckbox = cb;
                updateBulkBar();
            }
        });

        // Delegated Drawer Openers
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('.btn-inspect, .prod-title-link, [data-inspect]');
            if (trigger) {
                e.preventDefault();
                var id = trigger.getAttribute('data-id') || trigger.getAttribute('data-inspect');
                if (id) openDrawer(id);
            }
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
                var countFa = (window.AB && window.AB.fmt) ? window.AB.fmt.faDigits(count) : String(count);
                if (confirm('آیا از حذف قطعی ' + countFa + ' محصول انتخاب‌شده اطمینان دارید؟ این عملیات غیرقابل بازگشت است.')) {
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
                getRowCheckboxes().forEach(function (cb) { cb.checked = false; });
                if (checkAll) checkAll.checked = false;
                updateBulkBar();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Global hooks
    window.openProductDrawer = openDrawer;
    window.closeProductDrawer = closeDrawer;

})();
