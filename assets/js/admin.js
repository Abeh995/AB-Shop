/**
 * Admin Panel JavaScript Controller
 * - Global Live Search with Auto-suggest across Pages, Settings, Orders, and Products (FEAT-A004)
 * - Keyboard shortcuts (Ctrl+K / Slash to focus search)
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initAdminGlobalSearch();
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

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

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
})();
