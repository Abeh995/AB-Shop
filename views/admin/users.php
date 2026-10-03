<?php
/**
 * Admin Users & Security Audit Workspace View
 * Decomposed into modular partials with Bento KPIs, instant filtering,
 * modal editors, and security audit trail.
 *
 * Strict Layer Boundaries: Pure presentation only, 0 SQL, 0 direct form processing (Rule 7).
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="usr-workspace">

    <!-- 1. Bento KPI Grid -->
    <?php require __DIR__ . '/users_partials/_kpis.php'; ?>

    <!-- 2. Action & Filter Toolbar -->
    <?php require __DIR__ . '/users_partials/_toolbar.php'; ?>

    <!-- 3. Interactive Admins Table -->
    <?php require __DIR__ . '/users_partials/_table.php'; ?>

    <!-- 4. Security Audit Trail -->
    <?php require __DIR__ . '/users_partials/_audit_trail.php'; ?>

</div>

<!-- 5. Interactive Modals -->
<?php require __DIR__ . '/users_partials/_modals.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Modal Controllers
    window.openCreateModal = function () {
        closeAllModals();
        const m = document.getElementById('modalCreateAdmin');
        if (m) m.classList.add('active');
    };

    window.openEditModal = function (data) {
        closeAllModals();
        document.getElementById('editAdminId').value = data.id || '';
        document.getElementById('editAdminUsername').value = data.username || '';
        document.getElementById('editAdminFullName').value = data.full_name || '';
        document.getElementById('editAdminRole').value = data.role || 'admin';
        document.getElementById('editAdminPhone').value = data.phone || '';
        document.getElementById('editAdminEmail').value = data.email || '';
        
        const m = document.getElementById('modalEditAdmin');
        if (m) m.classList.add('active');
    };

    window.openPasswordModal = function (id, username) {
        closeAllModals();
        document.getElementById('pwAdminId').value = id;
        document.getElementById('pwAdminUsername').textContent = '@' + username;
        
        const m = document.getElementById('modalPasswordAdmin');
        if (m) m.classList.add('active');
    };

    window.closeAllModals = function () {
        document.querySelectorAll('.usr-modal-backdrop').forEach(m => m.classList.remove('active'));
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });

    // Real-time Search & Filtering
    const searchInput = document.getElementById('adminSearchInput');
    const filterPills = document.querySelectorAll('.usr-filter-pill');
    const tableRows = document.querySelectorAll('.usr-row');

    let activeFilter = 'all';

    function applyFilters() {
        const query = (searchInput.value || '').trim().toLowerCase();

        tableRows.forEach(row => {
            const role = row.getAttribute('data-role');
            const status = row.getAttribute('data-status');
            const searchIndex = row.getAttribute('data-search') || '';

            // Check pill filter
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

            // Check search query
            const matchesQuery = !query || searchIndex.includes(query);

            if (matchesFilter && matchesQuery) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    filterPills.forEach(pill => {
        pill.addEventListener('click', function () {
            filterPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            activeFilter = this.getAttribute('data-filter') || 'all';
            applyFilters();
        });
    });
});
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
