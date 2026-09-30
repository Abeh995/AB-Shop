/**
 * AB-Socks Admin Categories Client Logic (admin-categories.js)
 * Master-Detail Smart Editor, Live Tree Search, Slug Auto-Generator & Optimistic Toggles.
 */

(function () {
    'use strict';

    // State
    var categoriesMap = window.categoriesDataMap || {};
    var activeEditingId = null;

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    if (!csrfToken) {
        var csrfInput = document.querySelector('input[name="csrf_token"]');
        if (csrfInput) csrfToken = csrfInput.value;
    }

    // DOM Elements - Editor
    var editorCard = document.getElementById('catEditorCard');
    var form = document.getElementById('catForm');
    var inputId = document.getElementById('catInputId');
    var inputAction = document.getElementById('catInputAction');
    var inputName = document.getElementById('catInputName');
    var inputSlug = document.getElementById('catInputSlug');
    var selectParent = document.getElementById('catSelectParent');
    var inputSort = document.getElementById('catInputSort');
    var textareaDesc = document.getElementById('catTextareaDesc');
    var checkboxActive = document.getElementById('catCheckboxActive');
    var btnSubmit = document.getElementById('catBtnSubmit');
    var btnCancel = document.getElementById('catBtnCancel');
    var btnResetHeader = document.getElementById('catBtnResetHeader');
    var modeBadge = document.getElementById('catModeBadge');
    var slugPreview = document.getElementById('catSlugPreviewText');
    var imageInput = document.getElementById('catImageInput');
    var imagePreview = document.getElementById('catImagePreview');
    var btnRemoveImage = document.getElementById('catBtnRemoveImage');
    var inputRemoveImage = document.getElementById('catInputRemoveImage');

    // DOM Elements - Tree & Toolbar
    var searchInput = document.getElementById('catSearchInput');
    var filterBtns = document.querySelectorAll('.cat-filter-btn');
    var rows = document.querySelectorAll('.cat-row');
    var emptyNotice = document.getElementById('catEmptySearch');
    var toastEl = document.getElementById('catToast');

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
        }, 3000);
    }

    // ==========================================
    // 2. Persian Slug Generator
    // ==========================================
    function slugify(text) {
        return text
            .toString()
            .trim()
            .toLowerCase()
            .replace(/[\s\-_]+/g, '-')
            .replace(/[^\u0600-\u06FFa-z0-9\-]/g, '')
            .replace(/^-+|-+$/g, '');
    }

    function updateSlugPreview(val) {
        if (slugPreview) {
            slugPreview.textContent = val || 'category-name';
        }
    }

    // ==========================================
    // 3. Smart Form State Management
    // ==========================================
    function setEditMode(id) {
        var c = categoriesMap[id];
        if (!c || !editorCard) return;

        activeEditingId = id;
        editorCard.classList.add('mode-editing');

        if (inputId) inputId.value = c.id;
        if (inputAction) inputAction.value = 'update';
        if (inputName) inputName.value = c.name;
        if (inputSlug) inputSlug.value = c.slug;
        if (selectParent) selectParent.value = c.parent_id !== null ? c.parent_id : '';
        if (inputSort) inputSort.value = c.sort_order || 0;
        if (textareaDesc) textareaDesc.value = c.description || '';
        if (checkboxActive) checkboxActive.checked = !!c.is_active;

        // Image preview
        if (inputRemoveImage) inputRemoveImage.value = '0';
        if (imageInput) imageInput.value = '';
        if (c.image && imagePreview) {
            imagePreview.src = '/uploads/' + c.image;
            if (btnRemoveImage) btnRemoveImage.style.display = 'inline-block';
        } else if (imagePreview) {
            imagePreview.src = '/assets/img/placeholder-sock.svg';
            if (btnRemoveImage) btnRemoveImage.style.display = 'none';
        }

        updateSlugPreview(c.slug);

        if (modeBadge) modeBadge.textContent = 'ویرایش: ' + c.name;
        if (btnSubmit) {
            btnSubmit.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> ذخیره تغییرات دسته';
        }

        // Highlight active row in tree table
        rows.forEach(function (r) {
            r.classList.toggle('is-active-editing', r.getAttribute('data-id') == id);
        });

        // Focus input
        if (inputName) {
            inputName.focus();
            inputName.select();
        }
    }

    function resetCreateMode() {
        activeEditingId = null;
        if (editorCard) editorCard.classList.remove('mode-editing');

        if (inputId) inputId.value = '0';
        if (inputAction) inputAction.value = 'create';
        if (inputName) inputName.value = '';
        if (inputSlug) inputSlug.value = '';
        if (selectParent) selectParent.value = '';
        if (inputSort) inputSort.value = '0';
        if (textareaDesc) textareaDesc.value = '';
        if (checkboxActive) checkboxActive.checked = true;

        // Reset image
        if (inputRemoveImage) inputRemoveImage.value = '0';
        if (imageInput) imageInput.value = '';
        if (imagePreview) imagePreview.src = '/assets/img/placeholder-sock.svg';
        if (btnRemoveImage) btnRemoveImage.style.display = 'none';

        updateSlugPreview('');

        if (modeBadge) modeBadge.textContent = 'افزودن دسته‌بندی جدید';
        if (btnSubmit) {
            btnSubmit.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> + ثبت دسته‌بندی';
        }

        rows.forEach(function (r) {
            r.classList.remove('is-active-editing');
        });
    }

    // ==========================================
    // 4. Optimistic AJAX Status Toggle
    // ==========================================
    function handleToggle(checkbox) {
        var id = checkbox.getAttribute('data-id');
        var isChecked = checkbox.checked;
        var switchWrapper = checkbox.closest('.cat-switch');

        if (switchWrapper) switchWrapper.classList.add('is-busy');

        var payload = new FormData();
        payload.append('action', 'toggle');
        payload.append('id', id);
        payload.append('csrf_token', csrfToken);
        payload.append('ajax', '1');

        fetch('categories.php', {
            method: 'POST',
            body: payload,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.ok) {
                    showToast('وضعیت انتشار دسته تغییر کرد.');
                    if (categoriesMap[id]) {
                        categoriesMap[id].is_active = data.new_value;
                    }
                    // If currently editing this item, sync the editor checkbox too
                    if (activeEditingId == id && checkboxActive) {
                        checkboxActive.checked = !!data.new_value;
                    }
                } else {
                    checkbox.checked = !isChecked;
                    showToast(data.error || 'خطا در تغییر وضعیت.', true);
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
    // 5. Live Search & Filter Chips
    // ==========================================
    var currentFilter = 'all';

    function applySearchAndFilter() {
        var q = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var visibleCount = 0;

        rows.forEach(function (row) {
            var name = (row.getAttribute('data-name') || '').toLowerCase();
            var slug = (row.getAttribute('data-slug') || '').toLowerCase();
            var depth = parseInt(row.getAttribute('data-depth') || '0', 10);
            var count = parseInt(row.getAttribute('data-count') || '0', 10);

            var matchesQuery = !q || name.indexOf(q) !== -1 || slug.indexOf(q) !== -1;
            var matchesFilter = true;

            if (currentFilter === 'root') {
                matchesFilter = (depth === 0);
            } else if (currentFilter === 'empty') {
                matchesFilter = (count === 0);
            }

            if (matchesQuery && matchesFilter) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (emptyNotice) {
            emptyNotice.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    // ==========================================
    // 6. Event Listeners
    // ==========================================
    document.addEventListener('DOMContentLoaded', function () {
        // Name input auto-slug
        if (inputName && inputSlug) {
            inputName.addEventListener('input', function () {
                if (!activeEditingId || inputSlug.getAttribute('data-manual') !== 'true') {
                    var s = slugify(this.value);
                    inputSlug.value = s;
                    updateSlugPreview(s);
                }
            });

            inputSlug.addEventListener('input', function () {
                this.setAttribute('data-manual', 'true');
                updateSlugPreview(this.value);
            });
        }

        // Reset mode buttons
        if (btnCancel) btnCancel.addEventListener('click', resetCreateMode);
        if (btnResetHeader) btnResetHeader.addEventListener('click', resetCreateMode);

        // Edit buttons in table
        document.querySelectorAll('.btn-cat-edit').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var id = this.getAttribute('data-id');
                if (id) setEditMode(id);
            });
        });

        // Click row title to edit
        document.querySelectorAll('.cat-title-text').forEach(function (el) {
            el.addEventListener('click', function () {
                var row = this.closest('.cat-row');
                var id = row ? row.getAttribute('data-id') : null;
                if (id) setEditMode(id);
            });
        });

        // Toggle switches in table
        document.querySelectorAll('.cat-switch-row-input').forEach(function (cb) {
            cb.addEventListener('change', function () {
                handleToggle(this);
            });
        });

        // Image file preview
        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', function () {
                var file = this.files && this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        imagePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Live Search
        if (searchInput) {
            searchInput.addEventListener('input', applySearchAndFilter);
        }

        // Filter chips
        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                filterBtns.forEach(function (b) { b.classList.remove('active'); });
                this.classList.add('active');
                currentFilter = this.getAttribute('data-filter') || 'all';
                applySearchAndFilter();
            });
        });

        // KPI card click to filter empty categories
        var kpiEmpty = document.getElementById('kpiEmptyCategories');
        if (kpiEmpty) {
            kpiEmpty.addEventListener('click', function () {
                filterBtns.forEach(function (b) {
                    b.classList.toggle('active', b.getAttribute('data-filter') === 'empty');
                });
                currentFilter = 'empty';
                applySearchAndFilter();
            });
        }

        // Escape key to reset mode
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && activeEditingId) {
                resetCreateMode();
            }
        });
    });

})();
