/**
 * AB-Socks SMS Patterns Workstation Interactive Controller
 * v1.27.0 - Category Tab Filtering, Real-time Search, AJAX Toggles & Mobile Simulation
 */

document.addEventListener('DOMContentLoaded', function () {
    // ==========================================
    // 1. LIST WORKSTATION: Tabs, Search & AJAX
    // ==========================================
    var tabBtns = document.querySelectorAll('.sms-tab-btn');
    var searchInput = document.getElementById('smsSearchInput');
    var rows = document.querySelectorAll('.sms-pattern-row');
    var activeCategory = 'all';

    function filterRows() {
        var query = (searchInput && searchInput.value.trim().toLowerCase()) || '';
        var visibleCount = 0;

        rows.forEach(function (row) {
            var cat = row.getAttribute('data-category') || 'other';
            var isUnset = row.getAttribute('data-unset') === '1';
            var searchTxt = (row.getAttribute('data-search') || '').toLowerCase();

            var matchesCategory = false;
            if (activeCategory === 'all') {
                matchesCategory = true;
            } else if (activeCategory === 'unset') {
                matchesCategory = isUnset;
            } else {
                matchesCategory = (cat === activeCategory);
            }

            var matchesQuery = !query || searchTxt.indexOf(query) !== -1;

            if (matchesCategory && matchesQuery) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        var noResultsRow = document.getElementById('smsNoResultsRow');
        if (noResultsRow) {
            noResultsRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    if (tabBtns.length > 0) {
        tabBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                tabBtns.forEach(function (b) { b.classList.remove('active'); });
                this.classList.add('active');
                activeCategory = this.getAttribute('data-filter') || 'all';
                filterRows();
            });
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
    }

    // AJAX Active Status Toggle
    document.querySelectorAll('.sms-active-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var id = this.getAttribute('data-id');
            var isChecked = this.checked;
            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            var fd = new FormData();
            fd.append('action', 'toggle');
            fd.append('id', id);
            fd.append('csrf_token', csrfToken);

            fetch('sms_patterns.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.ok) {
                    toggle.checked = !isChecked;
                    alert(res.error || 'خطا در تغییر وضعیت');
                } else {
                    var statusBadge = document.getElementById('status-badge-' + id);
                    if (statusBadge) {
                        statusBadge.textContent = res.new_status ? 'فعال' : 'غیرفعال';
                        statusBadge.className = 'status-pill ' + (res.new_status ? 'status-delivered' : 'status-cancelled');
                    }
                }
            })
            .catch(function () {
                toggle.checked = !isChecked;
            });
        });
    });

    // ==========================================
    // 2. EDIT STUDIO: Live Mobile SMS Simulation
    // ==========================================
    var patternTextarea = document.querySelector('textarea[name="pattern_text"]');
    var mobileBubble = document.getElementById('mobilePreviewBubble');
    var charCountEl = document.getElementById('smsCharCount');
    var partCountEl = document.getElementById('smsPartCount');

    function calculateSmsParts(len) {
        if (len === 0) return 0;
        // Persian / Unicode GSM parts
        if (len <= 70) return 1;
        return Math.ceil(len / 67);
    }

    function updateMobilePreview() {
        if (!patternTextarea || !mobileBubble) return;

        var text = patternTextarea.value || 'متن پیش‌فرض پیامک...';

        // Gather sample variable values from inputs
        var varNames = document.querySelectorAll('input[name="var_name[]"]');
        var testInputs = document.querySelectorAll('.sample-var-input');
        var varValuesMap = {};

        varNames.forEach(function (vInput, idx) {
            var vName = (vInput.value || '').trim();
            if (vName) {
                var testVal = testInputs[idx] ? testInputs[idx].value : ('[' + vName + ']');
                varValuesMap[vName] = testVal || ('[' + vName + ']');
            }
        });

        // Replace %var% in text with sample value
        var rendered = text.replace(/%([a-zA-Z0-9_]+)%/g, function (match, p1) {
            return varValuesMap[p1] !== undefined ? varValuesMap[p1] : match;
        });

        mobileBubble.textContent = rendered;

        var charLen = rendered.length;
        var parts = calculateSmsParts(charLen);

        if (charCountEl) charCountEl.textContent = charLen;
        if (partCountEl) partCountEl.textContent = parts + ' پارت پیامک';
    }

    if (patternTextarea) {
        patternTextarea.addEventListener('input', updateMobilePreview);
    }

    document.querySelectorAll('.sample-var-input, input[name="var_name[]"]').forEach(function (el) {
        el.addEventListener('input', updateMobilePreview);
    });

    // Initial simulation render
    if (patternTextarea && mobileBubble) {
        updateMobilePreview();
    }
});
