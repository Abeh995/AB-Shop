/**
 * AB-Socks Shared Front-end Kit (assets/js/ab-kit.js)
 * Single Source of Truth for Client Utilities, Formatting, Toasts, Declarative Modals & Table Filtering.
 * Follows Rule 11 (Reuse-First Protocol & Anti-Duplication Invariant).
 */

(function (window, document) {
    'use strict';

    const AB = window.AB || {};

    // Standard Responsive Breakpoints (mirrored in admin-tokens.css and verified by verify.php)
    AB.bp = {
        sm: 640,
        md: 1024,
        lg: 1440
    };

    // =========================================================================
    // 1. Text & Number Formatters (AB.fmt)
    // =========================================================================
    AB.fmt = {
        /**
         * Converts English digits (0-9) to Persian numerals (۰-۹).
         * @param {string|number} str
         * @returns {string}
         */
        faDigits: function (str) {
            if (str === null || str === undefined) return '';
            const fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            return String(str).replace(/[0-9]/g, function (w) {
                return fa[+w];
            });
        },

        /**
         * Formats numerical amount with thousand separators and currency unit.
         * @param {number|string} amount
         * @param {string} [unit='تومان']
         * @returns {string}
         */
        price: function (amount, unit = 'تومان') {
            if (amount === null || amount === undefined || amount === '' || isNaN(Number(amount))) {
                return '—';
            }
            const formatted = Number(amount).toLocaleString('en-US');
            const faFormatted = AB.fmt.faDigits(formatted);
            return unit ? faFormatted + ' ' + unit : faFormatted;
        },

        /**
         * Formats byte size into human-readable Persian units (بایت، کیلوبایت، مگابایت).
         * @param {number} bytes
         * @returns {string}
         */
        bytes: function (bytes) {
            if (!bytes || isNaN(bytes) || bytes <= 0) return '۰ بایت';
            const k = 1024;
            const sizes = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            const val = (bytes / Math.pow(k, i)).toFixed(i === 0 ? 0 : 1);
            return AB.fmt.faDigits(val) + ' ' + sizes[i];
        },

        /**
         * Escapes HTML entities to prevent XSS in dynamic templates.
         * @param {string} str
         * @returns {string}
         */
        esc: function (str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        /**
         * Normalizes Persian/Arabic text for fast substring searching.
         * @param {string} str
         * @returns {string}
         */
        normalizeText: function (str) {
            if (!str) return '';
            return String(str)
                .toLowerCase()
                .trim()
                .replace(/[\u064B-\u065F\u0670]/g, '') // Remove Arabic harakat/tashkeel
                .replace(/ي/g, 'ی')
                .replace(/ك/g, 'ک')
                .replace(/ة/g, 'ه')
                .replace(/[۰-۹]/g, function (d) {
                    return String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
                })
                .replace(/[\s\-_]+/g, ' ');
        }
    };

    // =========================================================================
    // 2. Toast Notification Center (AB.toast & window.showToast)
    // =========================================================================
    AB.toast = function (message, type = 'success', duration = 3200) {
        if (!message) return;

        let container = document.getElementById('abToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'abToastContainer';
            container.className = 'ab-toast-container';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(container);
        }

        // Backward compatibility: boolean true for isError
        let toastType = 'success';
        if (type === true || type === 'error' || type === 'danger') {
            toastType = 'error';
        } else if (type === 'warning' || type === 'warn') {
            toastType = 'warning';
        } else if (type === 'info') {
            toastType = 'info';
        }

        const toast = document.createElement('div');
        toast.className = 'ab-toast ab-toast-' + toastType;
        toast.setAttribute('role', toastType === 'error' ? 'alert' : 'status');

        let iconSvg = '';
        if (toastType === 'error') {
            iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
        } else if (toastType === 'warning') {
            iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
        } else {
            iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
        }

        toast.innerHTML = '<span class="ab-toast-icon">' + iconSvg + '</span>' +
                          '<span class="ab-toast-msg">' + AB.fmt.esc(message) + '</span>';

        container.appendChild(toast);

        // Micro-task trigger for smooth CSS transform/opacity
        requestAnimationFrame(function () {
            toast.classList.add('is-visible');
        });

        const timer = setTimeout(function () {
            toast.classList.remove('is-visible');
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 300);
        }, duration);

        toast.addEventListener('click', function () {
            clearTimeout(timer);
            toast.classList.remove('is-visible');
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 300);
        });
    };

    // Polymorphic method alias for backward and forward compatibility
    AB.toast.show = AB.toast;

    // Global canonical alias for backward compatibility across all workstations
    window.showToast = function (message, type, duration) {
        AB.toast(message, type, duration);
    };

    // =========================================================================
    // 3. Declarative Modals & Dialogs (AB.modal)
    // =========================================================================
    AB.modal = {
        /**
         * Opens target modal by ID or element reference.
         * Supports both native HTML5 <dialog> and .admin-modal containers.
         * @param {string|HTMLElement} target
         * @param {HTMLElement} [openerEl]
         */
        open: function (target, openerEl) {
            const modal = typeof target === 'string' ? document.getElementById(target) : target;
            if (!modal) return;

            if (modal.tagName === 'DIALOG' && typeof modal.showModal === 'function') {
                modal.showModal();
            } else {
                modal.removeAttribute('hidden');
                modal.classList.add('is-open', 'active', 'is-active');
                modal.style.display = 'flex';
                document.body.classList.add('modal-open');
            }

            modal._lastOpener = openerEl || null;

            // Dispatch event for custom form reset or focus binding
            modal.dispatchEvent(new CustomEvent('ab:modal:open', { bubbles: true, detail: { modal, opener: openerEl } }));

            // Auto focus first input if available
            const autoFocusInput = modal.querySelector('input:not([type="hidden"]), select, textarea, button.btn-primary');
            if (autoFocusInput) {
                setTimeout(function () { autoFocusInput.focus(); }, 50);
            }
        },

        /**
         * Closes target modal by ID or element reference.
         * @param {string|HTMLElement} target
         */
        close: function (target) {
            const modal = typeof target === 'string' ? document.getElementById(target) : target;
            if (!modal) return;

            if (modal.tagName === 'DIALOG' && typeof modal.close === 'function') {
                modal.close();
            } else {
                modal.classList.remove('is-open', 'active', 'is-active');
                modal.style.display = 'none';
                modal.setAttribute('hidden', '');
                document.body.classList.remove('modal-open');
            }

            modal.dispatchEvent(new CustomEvent('ab:modal:close', { bubbles: true, detail: { modal } }));

            if (modal._lastOpener && typeof modal._lastOpener.focus === 'function') {
                modal._lastOpener.focus();
                modal._lastOpener = null;
            }
        }
    };

    // Delegated click listeners for data-ab-modal-open and data-ab-modal-close
    document.addEventListener('click', function (e) {
        const openTrigger = e.target.closest('[data-ab-modal-open]');
        if (openTrigger) {
            e.preventDefault();
            const targetId = openTrigger.getAttribute('data-ab-modal-open');
            AB.modal.open(targetId, openTrigger);
            return;
        }

        const closeTrigger = e.target.closest('[data-ab-modal-close]');
        if (closeTrigger) {
            e.preventDefault();
            const modal = closeTrigger.closest('dialog, .admin-modal, .fin-modal-overlay, .cpn-modal-overlay, [data-ab-modal]');
            if (modal) {
                AB.modal.close(modal);
            }
            return;
        }

        // Close on backdrop click (when clicking outside the modal content container or native dialog backdrop)
        if (e.target.tagName === 'DIALOG' ||
            e.target.classList.contains('admin-modal') ||
            e.target.classList.contains('admin-modal-backdrop') ||
            e.target.classList.contains('fin-modal-overlay') ||
            e.target.classList.contains('cpn-modal-overlay') ||
            e.target.classList.contains('c2c-modal-backdrop') ||
            e.target.classList.contains('usr-modal-backdrop')) {
            const modal = e.target.closest('dialog, .admin-modal, .fin-modal-overlay, .cpn-modal-overlay, .c2c-modal-backdrop, .usr-modal-backdrop, [data-ab-modal]');
            if (modal) {
                AB.modal.close(modal);
            }
        }
    });

    // Close active modal on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.admin-modal.is-open, .admin-modal.active, .admin-modal.is-active, .cpn-modal-overlay.active, .fin-modal-overlay.is-active, .usr-modal-backdrop.active, dialog[open], [data-ab-modal].is-open, [data-ab-modal].active');
            if (activeModal) {
                AB.modal.close(activeModal);
            }
        }
    });

    // =========================================================================
    // 4. Live Table & List Filtering (AB.tableFilter)
    // =========================================================================
    AB.tableFilter = {
        /**
         * Applies instant client-side filtering on a container.
         * @param {HTMLInputElement} inputEl
         */
        apply: function (inputEl) {
            const targetSelector = inputEl.getAttribute('data-ab-filter-target');
            if (!targetSelector) return;

            const targetContainer = document.querySelector(targetSelector);
            if (!targetContainer) return;

            const query = AB.fmt.normalizeText(inputEl.value);
            const rows = targetContainer.querySelectorAll('[data-ab-filter-item], tbody tr:not(.table-summary-row)');
            const emptyElSelector = inputEl.getAttribute('data-ab-filter-empty');
            const emptyEl = emptyElSelector ? document.querySelector(emptyElSelector) : null;
            const counterSelector = inputEl.getAttribute('data-ab-filter-counter');
            const counterEl = counterSelector ? document.querySelector(counterSelector) : null;

            let visibleCount = 0;

            rows.forEach(function (row) {
                const searchIndex = row.getAttribute('data-search') || row.getAttribute('data-ab-search') || row.textContent;
                const normalizedIndex = AB.fmt.normalizeText(searchIndex);

                const isMatch = !query || normalizedIndex.indexOf(query) !== -1;
                if (isMatch) {
                    row.removeAttribute('hidden');
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.setAttribute('hidden', '');
                    row.style.display = 'none';
                }
            });

            if (emptyEl) {
                if (visibleCount === 0 && query) {
                    emptyEl.removeAttribute('hidden');
                    emptyEl.style.display = '';
                } else {
                    emptyEl.setAttribute('hidden', '');
                    emptyEl.style.display = 'none';
                }
            }

            if (counterEl) {
                counterEl.textContent = AB.fmt.faDigits(visibleCount);
            }

            targetContainer.dispatchEvent(new CustomEvent('ab:table:filtered', {
                bubbles: true,
                detail: { query, visibleCount, total: rows.length }
            }));
        }
    };

    // Auto-listen on inputs with data-ab-filter-target
    document.addEventListener('input', function (e) {
        const input = e.target.closest('input[data-ab-filter-target]');
        if (input) {
            AB.tableFilter.apply(input);
        }
    });

    // =========================================================================
    // 5. Lightweight API & CSRF Fetch Utility (AB.api)
    // =========================================================================
    AB.api = {
        /**
         * Resolves active CSRF token from page metadata or hidden inputs.
         * @returns {string}
         */
        csrfToken: function () {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta && meta.content) return meta.content;
            const input = document.querySelector('input[name="csrf_token"]');
            if (input && input.value) return input.value;
            return window.__CSRF_TOKEN__ || '';
        },

        /**
         * Performs a POST request with automatic CSRF and JSON parsing.
         * @param {string} url
         * @param {FormData|object} payload
         * @returns {Promise<object>}
         */
        post: function (url, payload = {}) {
            let body = payload;
            const headers = {
                'X-Requested-With': 'XMLHttpRequest'
            };

            const token = AB.api.csrfToken();
            if (!(payload instanceof FormData)) {
                headers['Content-Type'] = 'application/json';
                if (token && typeof payload === 'object' && !payload.csrf_token) {
                    payload.csrf_token = token;
                }
                body = JSON.stringify(payload);
            } else if (token && !payload.has('csrf_token')) {
                payload.append('csrf_token', token);
            }

            return fetch(url, {
                method: 'POST',
                headers: headers,
                body: body
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                }
                return response.json();
            });
        }
    };

    // =========================================================================
    // 6. Centralized Autocomplete & Tokenizer Engine (AB.autocomplete)
    // =========================================================================
    AB.autocomplete = {
        instances: new WeakMap(),

        /**
         * Initialize autocomplete on a container element.
         * @param {HTMLElement} container
         * @param {object} [options]
         */
        init: function (container, options = {}) {
            if (!container || AB.autocomplete.instances.has(container)) {
                return AB.autocomplete.instances.get(container);
            }

            const provider = options.provider || container.getAttribute('data-ab-autocomplete') || 'all';
            const endpoint = options.endpoint || container.getAttribute('data-ab-endpoint') || '/ajax/admin_search.php';
            const isMultiple = options.multiple ?? (container.getAttribute('data-ab-multiple') === 'true');
            const allowNew = options.allowNew ?? (container.getAttribute('data-ab-allow-new') === 'true');
            const inputName = options.inputName || container.getAttribute('data-ab-input-name') || (provider + '_ids[]');
            const newInputName = options.newInputName || container.getAttribute('data-ab-new-input-name') || ('new_' + provider);
            const minChars = parseInt(options.minChars || container.getAttribute('data-ab-min-chars') || '1', 10);

            let inputEl = container.querySelector('input[data-ab-autocomplete-input], input.ab-autocomplete-input, input.tag-input-field');
            if (!inputEl) {
                inputEl = container.querySelector('input:not([type="hidden"])');
            }
            if (!inputEl) return null;

            let dropdownEl = container.querySelector('.ab-autocomplete-dropdown, .tag-autocomplete-dropdown');
            if (!dropdownEl) {
                dropdownEl = document.createElement('div');
                dropdownEl.className = 'ab-autocomplete-dropdown';
                dropdownEl.style.display = 'none';
                container.appendChild(dropdownEl);
            }

            let tokensListEl = container.querySelector('.ab-tokens-list, .tag-tokens-list');
            let hiddenContainer = container.querySelector('.ab-autocomplete-hidden, #tagHiddenInputs');
            if (isMultiple && !hiddenContainer) {
                hiddenContainer = document.createElement('div');
                hiddenContainer.className = 'ab-autocomplete-hidden';
                container.appendChild(hiddenContainer);
            }

            let selectedItems = [];
            let selectedNew = [];
            let activeHighlightIndex = -1;
            let currentAbortController = null;
            let debounceTimer = null;

            function renderTokens() {
                if (!isMultiple) return;
                if (tokensListEl) {
                    tokensListEl.innerHTML = '';
                    selectedItems.forEach(function (item, idx) {
                        const pill = document.createElement('span');
                        pill.className = 'ab-token-pill tag-pill';
                        pill.innerHTML = '<span>' + AB.fmt.esc(item.name || item.title) + '</span>' +
                            '<button type="button" class="ab-token-remove tag-pill-remove" data-type="existing" data-idx="' + idx + '" title="حذف">✕</button>';
                        tokensListEl.appendChild(pill);
                    });

                    selectedNew.forEach(function (name, idx) {
                        const pill = document.createElement('span');
                        pill.className = 'ab-token-pill ab-token-new tag-pill tag-pill-new';
                        pill.innerHTML = '<span>' + AB.fmt.esc(name) + '</span>' +
                            '<span class="ab-token-badge tag-badge-new">جدید</span>' +
                            '<button type="button" class="ab-token-remove tag-pill-remove" data-type="new" data-idx="' + idx + '" title="حذف">✕</button>';
                        tokensListEl.appendChild(pill);
                    });
                }

                if (hiddenContainer) {
                    hiddenContainer.innerHTML = '';
                    selectedItems.forEach(function (item) {
                        const inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = inputName;
                        inp.value = item.id;
                        hiddenContainer.appendChild(inp);
                    });

                    if (allowNew) {
                        const newInp = document.createElement('input');
                        newInp.type = 'hidden';
                        newInp.name = newInputName;
                        newInp.value = selectedNew.join(', ');
                        hiddenContainer.appendChild(newInp);
                    }
                }

                container.dispatchEvent(new CustomEvent('ab:autocomplete:change', {
                    bubbles: true,
                    detail: { selected: selectedItems, newItems: selectedNew }
                }));
            }

            function openDropdown() {
                dropdownEl.style.display = 'block';
                dropdownEl.classList.add('is-open');
            }

            function closeDropdown() {
                dropdownEl.style.display = 'none';
                dropdownEl.classList.remove('is-open');
                dropdownEl.innerHTML = '';
                activeHighlightIndex = -1;
                if (currentAbortController) {
                    currentAbortController.abort();
                    currentAbortController = null;
                }
            }

            function addExistingItem(item) {
                if (!item || !item.id) return;
                const exists = selectedItems.some(function (i) { return String(i.id) === String(item.id); });
                if (!exists) {
                    if (isMultiple) {
                        selectedItems.push(item);
                        renderTokens();
                    } else {
                        selectedItems = [item];
                        inputEl.value = item.name || item.title || '';
                        let singleHidden = container.querySelector('input[type="hidden"][name="' + inputName + '"]');
                        if (!singleHidden && hiddenContainer) {
                            singleHidden = document.createElement('input');
                            singleHidden.type = 'hidden';
                            singleHidden.name = inputName;
                            hiddenContainer.appendChild(singleHidden);
                        }
                        if (singleHidden) singleHidden.value = item.id;
                    }
                }
                inputEl.value = isMultiple ? '' : (item.name || item.title || '');
                closeDropdown();
                if (isMultiple) inputEl.focus();

                container.dispatchEvent(new CustomEvent('ab:autocomplete:select', {
                    bubbles: true,
                    detail: { item: item }
                }));
            }

            function addNewItem(rawName) {
                const name = (rawName || '').trim().replace(/,/g, '');
                if (!name || !allowNew) return;
                const existsNew = selectedNew.some(function (n) { return n.toLowerCase() === name.toLowerCase(); });
                if (!existsNew) {
                    selectedNew.push(name);
                    renderTokens();
                }
                inputEl.value = '';
                closeDropdown();
                inputEl.focus();

                container.dispatchEvent(new CustomEvent('ab:autocomplete:add-new', {
                    bubbles: true,
                    detail: { name: name }
                }));
            }

            function removeItem(type, idx) {
                if (type === 'existing') {
                    const removed = selectedItems.splice(idx, 1);
                    renderTokens();
                    container.dispatchEvent(new CustomEvent('ab:autocomplete:remove', { bubbles: true, detail: { item: removed[0] } }));
                } else if (type === 'new') {
                    const removed = selectedNew.splice(idx, 1);
                    renderTokens();
                    container.dispatchEvent(new CustomEvent('ab:autocomplete:remove-new', { bubbles: true, detail: { name: removed[0] } }));
                }
            }

            function fetchResults(q) {
                if (currentAbortController) {
                    currentAbortController.abort();
                }
                currentAbortController = new AbortController();

                const url = endpoint + '?type=' + encodeURIComponent(provider) + '&q=' + encodeURIComponent(q);
                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: currentAbortController.signal
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        const items = data.results || data[provider] || [];
                        renderDropdown(items, q);
                    })
                    .catch(function (err) {
                        if (err.name !== 'AbortError') {
                            console.error('Autocomplete search failed:', err);
                        }
                    });
            }

            function renderDropdown(items, q) {
                dropdownEl.innerHTML = '';
                activeHighlightIndex = -1;

                const filtered = items.filter(function (it) {
                    if (!isMultiple) return true;
                    return !selectedItems.some(function (si) { return String(si.id) === String(it.id); });
                });

                if (filtered.length === 0 && (!allowNew || !q)) {
                    dropdownEl.innerHTML = '<div class="ab-autocomplete-empty tag-dropdown-empty">نتیجه‌ای پیدا نشد.</div>';
                    openDropdown();
                    return;
                }

                filtered.forEach(function (it, idx) {
                    const row = document.createElement('div');
                    row.className = 'ab-autocomplete-item tag-dropdown-item';
                    row.setAttribute('data-idx', idx);

                    let titleHtml = '<span class="ab-autocomplete-title tag-drop-name">' + AB.fmt.esc(it.name || it.title) + '</span>';
                    let metaHtml = '';
                    if (it.slug) {
                        metaHtml = '<span class="ab-autocomplete-meta tag-drop-slug" dir="ltr">' + AB.fmt.esc(it.slug) + '</span>';
                    } else if (it.price_formatted) {
                        metaHtml = '<span class="ab-autocomplete-meta">' + AB.fmt.esc(it.price_formatted) + '</span>';
                    } else if (it.phone) {
                        metaHtml = '<span class="ab-autocomplete-meta" dir="ltr">' + AB.fmt.esc(it.phone) + '</span>';
                    }

                    row.innerHTML = titleHtml + metaHtml;
                    row.addEventListener('click', function () {
                        addExistingItem(it);
                    });
                    dropdownEl.appendChild(row);
                });

                if (allowNew && q) {
                    const exactMatch = filtered.some(function (it) {
                        return (it.name || it.title || '').toLowerCase() === q.toLowerCase();
                    }) || selectedNew.some(function (n) { return n.toLowerCase() === q.toLowerCase(); });

                    if (!exactMatch) {
                        const newRow = document.createElement('div');
                        newRow.className = 'ab-autocomplete-item ab-autocomplete-item-new tag-dropdown-item tag-dropdown-item-new';
                        newRow.innerHTML = '<span class="tag-drop-plus">+</span>' +
                            '<span>ایجاد <strong>«' + AB.fmt.esc(q) + '»</strong> (اینتر بزنید)</span>';
                        newRow.addEventListener('click', function () {
                            addNewItem(q);
                        });
                        dropdownEl.appendChild(newRow);
                    }
                }

                openDropdown();
            }

            // Keyboard navigation
            inputEl.addEventListener('keydown', function (e) {
                const items = dropdownEl.querySelectorAll('.ab-autocomplete-item');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (items.length > 0) {
                        activeHighlightIndex = (activeHighlightIndex + 1) % items.length;
                        highlightItem(items, activeHighlightIndex);
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (items.length > 0) {
                        activeHighlightIndex = (activeHighlightIndex - 1 + items.length) % items.length;
                        highlightItem(items, activeHighlightIndex);
                    }
                } else if (e.key === 'Enter') {
                    if (dropdownEl.style.display !== 'none' && activeHighlightIndex >= 0 && items[activeHighlightIndex]) {
                        e.preventDefault();
                        items[activeHighlightIndex].click();
                    } else if (allowNew && inputEl.value.trim().length > 0) {
                        e.preventDefault();
                        addNewItem(inputEl.value.trim());
                    }
                } else if (e.key === 'Escape') {
                    closeDropdown();
                } else if (e.key === 'Backspace' && isMultiple && inputEl.value === '') {
                    if (selectedNew.length > 0) {
                        removeItem('new', selectedNew.length - 1);
                    } else if (selectedItems.length > 0) {
                        removeItem('existing', selectedItems.length - 1);
                    }
                } else if (e.key === ',' && allowNew && isMultiple) {
                    e.preventDefault();
                    addNewItem(inputEl.value.trim());
                }
            });

            function highlightItem(items, index) {
                items.forEach(function (el, i) {
                    if (i === index) {
                        el.classList.add('is-highlighted', 'selected');
                        el.scrollIntoView({ block: 'nearest' });
                    } else {
                        el.classList.remove('is-highlighted', 'selected');
                    }
                });
            }

            // Live input with debounce
            inputEl.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                const q = inputEl.value.trim();
                if (q.length < minChars) {
                    closeDropdown();
                    return;
                }
                debounceTimer = setTimeout(function () {
                    fetchResults(q);
                }, 200);
            });

            // Delegate token remove clicks
            if (tokensListEl) {
                tokensListEl.addEventListener('click', function (e) {
                    const btn = e.target.closest('.ab-token-remove, .tag-pill-remove');
                    if (btn) {
                        e.stopPropagation();
                        removeItem(btn.getAttribute('data-type'), parseInt(btn.getAttribute('data-idx'), 10));
                    }
                });
            }

            // Close on click outside
            document.addEventListener('click', function (e) {
                if (!container.contains(e.target)) {
                    closeDropdown();
                }
            });

            const apiInstance = {
                container: container,
                addItem: addExistingItem,
                addNew: addNewItem,
                removeItem: removeItem,
                setItems: function (items) {
                    selectedItems = items ? items.slice() : [];
                    renderTokens();
                },
                setNewItems: function (names) {
                    selectedNew = names ? names.slice() : [];
                    renderTokens();
                },
                getItems: function () {
                    return selectedItems.slice();
                },
                getNewItems: function () {
                    return selectedNew.slice();
                },
                clear: function () {
                    selectedItems = [];
                    selectedNew = [];
                    renderTokens();
                }
            };

            container._abAutocomplete = apiInstance;
            AB.autocomplete.instances.set(container, apiInstance);
            return apiInstance;
        },

        /**
         * Automatically initializes all declarative autocomplete containers on the page.
         */
        autoInit: function (root = document) {
            const containers = root.querySelectorAll('[data-ab-autocomplete]');
            containers.forEach(function (c) {
                AB.autocomplete.init(c);
            });
        }
    };

    // =========================================================================
    // 7. Master Navigation Tabs Engine (AB.tabs)
    // =========================================================================
    AB.tabs = {
        /**
         * Activates target tab within a nav container and displays corresponding pane.
         * @param {HTMLElement|string} navContainer
         * @param {string} tabKey
         * @param {boolean} [updateHash=true]
         */
        activate: function (navContainer, tabKey, updateHash = true) {
            const nav = typeof navContainer === 'string' ? document.getElementById(navContainer) : navContainer;
            if (!nav || !tabKey) return;

            const buttons = nav.querySelectorAll('[data-tab], .ab-tab-btn, .settings-tab-btn, .diag-tab-btn, .appearance-tab-btn');
            let targetBtn = null;
            buttons.forEach(function (btn) {
                const k = btn.getAttribute('data-tab');
                if (k === tabKey) {
                    btn.classList.add('active');
                    targetBtn = btn;
                } else {
                    btn.classList.remove('active');
                }
            });

            // Target pane matching
            let targetPane = document.getElementById('pane-' + tabKey);
            if (!targetPane) {
                targetPane = document.querySelector('[data-tab-pane="' + tabKey + '"]');
            }

            if (targetPane) {
                const parent = targetPane.parentElement;
                if (parent) {
                    parent.querySelectorAll('.tab-pane, .settings-tab-pane, .diag-tab-pane, .appearance-tab-pane, [data-tab-pane]').forEach(function (p) {
                        p.classList.remove('active');
                    });
                }
                targetPane.classList.add('active');
            }

            if (updateHash && window.history && window.history.replaceState) {
                window.history.replaceState(null, '', '#' + tabKey);
            }

            nav.dispatchEvent(new CustomEvent('ab:tab:change', {
                bubbles: true,
                detail: { tab: tabKey, button: targetBtn, pane: targetPane }
            }));
        },

        /**
         * Initializes event listeners and hash sync for a tabs navigation container.
         * @param {HTMLElement} nav
         */
        init: function (nav) {
            if (!nav || nav._abTabsInit) return;
            nav._abTabsInit = true;

            const buttons = nav.querySelectorAll('[data-tab], .ab-tab-btn, .settings-tab-btn, .diag-tab-btn, .appearance-tab-btn');
            buttons.forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    const tabKey = this.getAttribute('data-tab');
                    if (tabKey && !this.getAttribute('href')) {
                        e.preventDefault();
                        AB.tabs.activate(nav, tabKey, true);
                    }
                });
            });

            // Sync from initial hash if present
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                const hasMatching = Array.from(buttons).some(b => b.getAttribute('data-tab') === hash);
                if (hasMatching) {
                    AB.tabs.activate(nav, hash, false);
                }
            }
        },

        /**
         * Auto-initializes all tab containers matching .ab-nav-tabs or [data-ab-tabs].
         */
        autoInit: function (root = document) {
            const navs = root.querySelectorAll('.ab-nav-tabs, [data-ab-tabs], .settings-tabs-nav, .diag-tabs-nav, .appearance-tabs-nav');
            navs.forEach(function (n) {
                AB.tabs.init(n);
            });
        }
    };

    // =========================================================================
    // 6. Split View Workstation Layout (AB.split & [data-ab-split])
    // =========================================================================
    AB.split = {
        /**
         * Switches split container to detail pane view.
         * @param {HTMLElement|string} container
         * @param {string} [detailId]
         */
        showDetail: function (container, detailId) {
            const split = typeof container === 'string' ? document.getElementById(container) : container;
            if (!split) return;
            split.setAttribute('data-split-view', 'detail');
            if (detailId) {
                const panes = split.querySelectorAll('[data-split-pane]');
                panes.forEach(function (p) {
                    p.hidden = (p.getAttribute('data-split-pane') !== detailId);
                });
            }
            split.dispatchEvent(new CustomEvent('ab:split:detail', { bubbles: true, detail: { split, detailId } }));
        },

        /**
         * Switches split container back to master list view.
         * @param {HTMLElement|string} container
         */
        showMaster: function (container) {
            const split = typeof container === 'string' ? document.getElementById(container) : container;
            if (!split) return;
            split.setAttribute('data-split-view', 'master');
            split.dispatchEvent(new CustomEvent('ab:split:master', { bubbles: true, detail: { split } }));
        }
    };

    // Delegated click handlers for split view interactions
    document.addEventListener('click', function (e) {
        const selectTrigger = e.target.closest('[data-ab-split-select]');
        if (selectTrigger) {
            const split = selectTrigger.closest('.ab-split, [data-ab-split]');
            if (split) {
                const targetPane = selectTrigger.getAttribute('data-ab-split-select');
                split.querySelectorAll('[data-ab-split-select]').forEach(function (el) {
                    el.classList.remove('is-active', 'active');
                });
                selectTrigger.classList.add('is-active', 'active');
                AB.split.showDetail(split, targetPane);
            }
            return;
        }

        const backTrigger = e.target.closest('[data-ab-split-back]');
        if (backTrigger) {
            e.preventDefault();
            const split = backTrigger.closest('.ab-split, [data-ab-split]');
            if (split) {
                AB.split.showMaster(split);
            }
        }
    });

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            AB.autocomplete.autoInit();
            AB.tabs.autoInit();
        });
    } else {
        AB.autocomplete.autoInit();
        AB.tabs.autoInit();
    }

    // Register into global scope
    window.AB = AB;

})(window, document);
