/**
 * Admin Users & Security Audit Workspace Client Script
 * assets/js/admin-users.js
 *
 * Responsibilities:
 * 1. Coordinates text search + filter chips on #adminUsersTable
 * 2. Populates edit and password modals upon activation
 * 3. Preserves backward-compatible global helpers (openCreateModal, openEditModal, openPasswordModal, closeAllModals)
 */

(function () {
    'use strict';

    // 1. Search + Filter Chips Coordination
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('adminSearchInput') || document.querySelector('.ab-toolbar__search input');
        const filterChips = document.querySelectorAll('.ab-chip[data-filter]');
        const tableRows = document.querySelectorAll('#adminUsersTable tbody tr');

        let activeFilter = 'all';

        function applyFilters() {
            const query = (searchInput ? searchInput.value : '').trim().toLowerCase();

            tableRows.forEach(function (row) {
                const role = row.getAttribute('data-role');
                const status = row.getAttribute('data-status');
                const searchIndex = (row.getAttribute('data-search') || '').toLowerCase();

                let matchesFilter = true;
                if (activeFilter === 'super_admin') {
                    matchesFilter = (role === 'super_admin');
                } else if (activeFilter === 'admin') {
                    matchesFilter = (role === 'admin');
                } else if (activeFilter === 'active') {
                    matchesFilter = (status === 'active');
                } else if (activeFilter === 'inactive') {
                    matchesFilter = (status === 'inactive');
                }

                const matchesQuery = !query || searchIndex.includes(query);

                if (matchesFilter && matchesQuery) {
                    row.removeAttribute('hidden');
                    row.style.display = '';
                } else {
                    row.setAttribute('hidden', '');
                    row.style.display = 'none';
                }
            });
        }

        filterChips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                filterChips.forEach(function (c) {
                    c.classList.remove('is-active');
                    c.setAttribute('aria-pressed', 'false');
                });
                this.classList.add('is-active');
                this.setAttribute('aria-pressed', 'true');
                activeFilter = this.getAttribute('data-filter') || 'all';
                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }
    });

    // 2. Delegated Click Handlers for Edit and Password Modals
    document.addEventListener('click', function (e) {
        const editTrigger = e.target.closest('[data-edit-user]');
        if (editTrigger) {
            try {
                const data = JSON.parse(editTrigger.getAttribute('data-edit-user') || '{}');
                window.openEditModal(data);
            } catch (_) {}
            return;
        }

        const pwTrigger = e.target.closest('[data-pw-id]');
        if (pwTrigger) {
            const id = pwTrigger.getAttribute('data-pw-id') || '';
            const username = pwTrigger.getAttribute('data-pw-username') || '';
            window.openPasswordModal(id, username);
            return;
        }
    });

    // 3. Backward Compatibility Global Methods
    window.openCreateModal = function () {
        if (window.AB && AB.modal) {
            AB.modal.open('modalCreateAdmin');
        }
    };

    window.openEditModal = function (data) {
        if (!data) return;
        const setVal = function (id, val) {
            const el = document.getElementById(id);
            if (el) el.value = val !== undefined && val !== null ? val : '';
        };

        setVal('editAdminId', data.id);
        setVal('editAdminUsername', data.username);
        setVal('editAdminFullName', data.full_name);
        setVal('editAdminRole', data.role || 'admin');
        setVal('editAdminPhone', data.phone);
        setVal('editAdminEmail', data.email);

        if (window.AB && AB.modal) {
            AB.modal.open('modalEditAdmin');
        }
    };

    window.openPasswordModal = function (id, username) {
        const idEl = document.getElementById('pwAdminId');
        if (idEl) idEl.value = id || '';

        const uEl = document.getElementById('pwAdminUsername');
        if (uEl) uEl.textContent = '@' + (username || '');

        if (window.AB && AB.modal) {
            AB.modal.open('modalPasswordAdmin');
        }
    };

    window.closeAllModals = function () {
        document.querySelectorAll('dialog.ab-modal[open]').forEach(function (m) {
            if (window.AB && AB.modal) {
                AB.modal.close(m);
            }
        });
    };
})();
