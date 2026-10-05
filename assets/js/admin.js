/**
 * Admin Panel JavaScript Controller
 * - Global Live Search with Auto-suggest across Pages, Settings, Orders, and Products (FEAT-A004)
 * - Keyboard shortcuts (Ctrl+K / Slash to focus search)
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initAdminGlobalSearch();
        initLiveClock();
        initAdminSidebar();
    });

    function initAdminGlobalSearch() {
        var searchInput = document.getElementById('adminGlobalSearch');
        var resultsWrap = document.getElementById('adminSearchResults');
        var searchBox = document.querySelector('.admin-search-box');

        if (!searchInput || !resultsWrap) {
            return;
        }

        var debounceTimer = null;
        var currentAbortController = null;
        var selectedIndex = -1;

        // Keyboard shortcut: Press Ctrl+K or / anywhere (except when typing in inputs/textareas) to focus search
        document.addEventListener('keydown', function (e) {
            if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable)) {
                return;
            }
            if ((e.ctrlKey && e.key === 'k') || e.key === '/') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        });

        // Input event with debounce
        searchInput.addEventListener('input', function () {
            var query = searchInput.value.trim();

            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }

            if (query.length < 1) {
                closeDropdown();
                return;
            }

            debounceTimer = setTimeout(function () {
                executeSearch(query);
            }, 180);
        });

        // Keyboard navigation within search input
        searchInput.addEventListener('keydown', function (e) {
            var items = resultsWrap.querySelectorAll('.admin-search-item');
            if (!items.length || resultsWrap.style.display === 'none') {
                if (e.key === 'Escape') {
                    searchInput.blur();
                    closeDropdown();
                }
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % items.length;
                updateSelection(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                updateSelection(items);
            } else if (e.key === 'Enter') {
                if (selectedIndex >= 0 && selectedIndex < items.length) {
                    e.preventDefault();
                    items[selectedIndex].click();
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closeDropdown();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!searchBox || !searchBox.contains(e.target)) {
                closeDropdown();
            }
        });

        // Re-open if query is present when clicking input
        searchInput.addEventListener('focus', function () {
            if (searchInput.value.trim().length >= 1 && resultsWrap.children.length > 0) {
                resultsWrap.style.display = 'block';
            }
        });

        function updateSelection(items) {
            items.forEach(function (el, idx) {
                if (idx === selectedIndex) {
                    el.classList.add('selected');
                    el.scrollIntoView({ block: 'nearest' });
                } else {
                    el.classList.remove('selected');
                }
            });
        }

        function closeDropdown() {
            resultsWrap.style.display = 'none';
            selectedIndex = -1;
            if (currentAbortController) {
                currentAbortController.abort();
                currentAbortController = null;
            }
        }

        function executeSearch(query) {
            if (currentAbortController) {
                currentAbortController.abort();
            }
            currentAbortController = new AbortController();

            fetch('/ajax/admin_search.php?q=' + encodeURIComponent(query), {
                signal: currentAbortController.signal
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (!data || !data.ok) {
                        return;
                    }
                    renderResults(data, query);
                })
                .catch(function (err) {
                    if (err.name !== 'AbortError') {
                        console.error('Admin search error:', err);
                    }
                });
        }

        const escapeHtml = str => (window.AB && window.AB.fmt ? window.AB.fmt.esc(str) : String(str || ''));


        function renderResults(data, query) {
            var pages = data.pages || [];
            var orders = data.orders || [];
            var products = data.products || [];

            selectedIndex = -1;

            if (pages.length === 0 && orders.length === 0 && products.length === 0) {
                resultsWrap.innerHTML = '<div class="admin-search-empty">نتیجه‌ای برای «' + escapeHtml(query) + '» پیدا نشد.</div>';
                resultsWrap.style.display = 'block';
                return;
            }

            var html = '';

            // Group 1: Pages & Settings
            if (pages.length > 0) {
                html += '<div class="admin-search-group-title">صفحات و بخش‌های ادمین</div>';
                pages.forEach(function (p) {
                    html += '<a href="' + escapeHtml(p.url) + '" class="admin-search-item admin-search-page-item">' +
                        '<div class="admin-search-item-info">' +
                        '<span class="admin-search-title">' + escapeHtml(p.title) + '</span>' +
                        '</div>' +
                        '<span class="admin-search-badge">' + escapeHtml(p.badge) + '</span>' +
                        '</a>';
                });
            }

            // Group 2: Orders
            if (orders.length > 0) {
                html += '<div class="admin-search-group-title">سفارش‌ها</div>';
                orders.forEach(function (o) {
                    html += '<a href="' + escapeHtml(o.url) + '" class="admin-search-item admin-search-order-item">' +
                        '<div class="admin-search-item-info">' +
                        '<span class="admin-search-title">سفارش #' + escapeHtml(o.order_code) + ' — ' + escapeHtml(o.customer_name) + '</span>' +
                        '<span class="admin-search-sub">' + escapeHtml(o.customer_phone) + ' • ' + escapeHtml(o.total_amount_formatted) + ' تومان</span>' +
                        '</div>' +
                        '<span class="status-pill status-' + escapeHtml(o.status) + '" style="font-size:.72rem;">' + escapeHtml(o.status_label) + '</span>' +
                        '</a>';
                });
            }

            // Group 3: Products
            if (products.length > 0) {
                html += '<div class="admin-search-group-title">محصولات</div>';
                products.forEach(function (pr) {
                    var thumb = pr.image_url ? '<img src="' + escapeHtml(pr.image_url) + '" alt="" class="admin-search-thumb">' : '';
                    html += '<a href="' + escapeHtml(pr.url) + '" class="admin-search-item admin-search-product-item">' +
                        thumb +
                        '<div class="admin-search-item-info">' +
                        '<span class="admin-search-title">' + escapeHtml(pr.name) + '</span>' +
                        '<span class="admin-search-sub">' + (pr.sku ? 'کد: ' + escapeHtml(pr.sku) + ' • ' : '') + escapeHtml(pr.price_formatted) + ' تومان • موجودی: ' + escapeHtml(pr.stock) + '</span>' +
                        '</div>' +
                        '</a>';
                });
            }

            resultsWrap.innerHTML = html;
            resultsWrap.style.display = 'block';
        }
    }

    /**
     * Real-Time Live Clock with correct Persian date ordering (FEAT-A004)
     * Format: Line 1 = HH:MM:SS (LTR), Line 2 = weekday, day month year (RTL)
     */
    function initLiveClock() {
        var timeEl = document.getElementById('liveClockTime');
        var dateEl = document.getElementById('liveClockDate');
        if (!timeEl && !dateEl) return;

        const toFaDigits = str => (window.AB && window.AB.fmt ? window.AB.fmt.faDigits(str) : String(str || ''));


        function getJalaliDate(d) {
            try {
                var formatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
                var parts = formatter.formatToParts(d);
                var weekday = '', day = '', month = '', year = '';
                parts.forEach(function (p) {
                    if (p.type === 'weekday') weekday = p.value;
                    if (p.type === 'day') day = p.value;
                    if (p.type === 'month') month = p.value;
                    if (p.type === 'year') year = p.value;
                });
                if (weekday && day && month && year) {
                    return weekday + '، ' + day + ' ' + month + ' ' + year;
                }
            } catch (e) {}
            return '';
        }

        function tick() {
            var bpMd = (window.AB && window.AB.bp) ? window.AB.bp.md : 1024;
            if (window.innerWidth < bpMd) {
                return;
            }
            var now = new Date();
            if (timeEl) {
                var h = String(now.getHours()).padStart(2, '0');
                var m = String(now.getMinutes()).padStart(2, '0');
                var s = String(now.getSeconds()).padStart(2, '0');
                timeEl.textContent = toFaDigits(h + ':' + m + ':' + s);
            }
            if (dateEl) {
                var jDate = getJalaliDate(now);
                if (jDate) {
                    dateEl.textContent = jDate;
                }
            }
        }

        tick();
        setInterval(tick, 1000);
    }

    /**
     * Modern Admin Sidebar Controller
     * - Collapsible Sidebar (Expanded 240px <-> Slim Rail 68px)
     * - Accordion category sub-menus with active-group auto-expansion
     * - LocalStorage persistence for both sidebar state & open accordions
     * - Keyboard shortcut ( [ or Ctrl+B ) to toggle sidebar
     * - Collapsed mode: logo badge click expands sidebar
     */
    function initAdminSidebar() {
        var sidebar = document.getElementById('adminSidebar');
        var toggleBtn = document.getElementById('sidebarToggleBtn');
        var logoEl = document.getElementById('adminSidebarLogo') || (sidebar ? sidebar.querySelector('.admin-logo') : null);
        if (!sidebar) return;

        var STORAGE_KEY_COLLAPSED = 'admin_sidebar_collapsed';
        var STORAGE_KEY_GROUPS = 'admin_sidebar_groups';

        function isMobileView() {
            var bpMd = (window.AB && window.AB.bp) ? window.AB.bp.md : 1024;
            return window.innerWidth < bpMd;
        }

        // 1. Initial State Restoration
        var isCollapsed = false;
        try {
            isCollapsed = localStorage.getItem(STORAGE_KEY_COLLAPSED) === 'true';
        } catch (e) {}

        function applySidebarState(collapsed) {
            if (isMobileView()) {
                document.documentElement.classList.remove('sidebar-collapsed');
                document.body.classList.remove('sidebar-collapsed');
                return;
            }
            if (collapsed) {
                document.documentElement.classList.add('sidebar-collapsed');
                document.body.classList.add('sidebar-collapsed');
            } else {
                document.documentElement.classList.remove('sidebar-collapsed');
                document.body.classList.remove('sidebar-collapsed');
            }
        }

        applySidebarState(isCollapsed);

        function toggleSidebar() {
            var current = document.documentElement.classList.contains('sidebar-collapsed');
            var next = !current;
            applySidebarState(next);
            try {
                localStorage.setItem(STORAGE_KEY_COLLAPSED, next ? 'true' : 'false');
            } catch (e) {}
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                toggleSidebar();
            });
        }

        // In collapsed desktop rail mode, clicking the logo badge expands the sidebar
        if (logoEl) {
            logoEl.addEventListener('click', function (e) {
                if (!isMobileView() && document.documentElement.classList.contains('sidebar-collapsed')) {
                    e.preventDefault();
                    toggleSidebar();
                }
            });
        }

        // Window resize listener: protect mobile from desktop collapsed class and restore on expand
        window.addEventListener('resize', function () {
            if (isMobileView()) {
                document.documentElement.classList.remove('sidebar-collapsed');
                document.body.classList.remove('sidebar-collapsed');
            } else {
                var saved = false;
                try {
                    saved = localStorage.getItem(STORAGE_KEY_COLLAPSED) === 'true';
                } catch (e) {}
                applySidebarState(saved);
            }
        });

        // Keyboard Shortcut: press [ or Ctrl+B to toggle sidebar (when not inside inputs)
        document.addEventListener('keydown', function (e) {
            if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable)) {
                return;
            }
            if (e.key === '[' || (e.ctrlKey && (e.key === 'b' || e.key === 'B'))) {
                if (!isMobileView()) {
                    e.preventDefault();
                    toggleSidebar();
                }
            }
        });

        // 2. Accordion Groups with Persistence & Contextual Auto-Expansion
        var groups = sidebar.querySelectorAll('.nav-group');
        var groupStates = {};
        try {
            groupStates = JSON.parse(localStorage.getItem(STORAGE_KEY_GROUPS) || '{}');
        } catch (e) {}

        groups.forEach(function (groupEl) {
            var groupKey = groupEl.getAttribute('data-group');
            var toggle = groupEl.querySelector('.nav-group-toggle');
            var isActiveGroup = groupEl.classList.contains('active-group');

            // Contextual Rule: If group contains the active page, it must ALWAYS open initially
            if (isActiveGroup) {
                groupEl.classList.add('open');
                if (toggle) toggle.setAttribute('aria-expanded', 'true');
            } else if (groupKey && groupStates[groupKey] === true) {
                groupEl.classList.add('open');
                if (toggle) toggle.setAttribute('aria-expanded', 'true');
            } else if (groupKey && groupStates[groupKey] === false) {
                groupEl.classList.remove('open');
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            }

            if (toggle) {
                toggle.addEventListener('click', function (e) {
                    // In collapsed mode on desktop, clicking expands sidebar so user can interact directly
                    if (document.documentElement.classList.contains('sidebar-collapsed')) {
                        applySidebarState(false);
                        try {
                            localStorage.setItem(STORAGE_KEY_COLLAPSED, 'false');
                        } catch (err) {}
                        groupEl.classList.add('open');
                        toggle.setAttribute('aria-expanded', 'true');
                        return;
                    }

                    var isOpen = groupEl.classList.toggle('open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                    if (groupKey) {
                        try {
                            var currentStates = JSON.parse(localStorage.getItem(STORAGE_KEY_GROUPS) || '{}');
                            currentStates[groupKey] = isOpen;
                            localStorage.setItem(STORAGE_KEY_GROUPS, JSON.stringify(currentStates));
                        } catch (err) {}
                    }
                });
            }
        });

        // Auto-scroll active sub-item into view smoothly if navigation was tall
        var activeSub = sidebar.querySelector('.nav-sub-item.active');
        if (activeSub) {
            setTimeout(function () {
                activeSub.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }, 100);
        }
    }
})();
