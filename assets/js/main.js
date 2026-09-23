// ==========================================================================
// Lightweight vanilla JS — no dependencies, no build step
// ==========================================================================

function toPersianDigits(str) {
    if (str === null || str === undefined) return '';
    var persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return String(str).replace(/[0-9]/g, function (d) {
        return persianDigits[parseInt(d, 10)];
    });
}

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function () {

    // ---------- Mobile nav menu ----------
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('mainNav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
    }

    // ---------- Header search bar & Live Search Autocomplete (FEAT-C001) ----------
    var searchToggle = document.getElementById('searchToggle');
    var searchBarPanel = document.getElementById('searchBarPanel');
    var searchForm = document.getElementById('headerSearchForm');
    var searchInput = document.getElementById('searchBarInput');
    var searchSpinner = document.getElementById('searchSpinner');
    var searchClearBtn = document.getElementById('searchClearBtn');
    var searchSuggestions = document.getElementById('searchSuggestions');

    if (searchToggle && searchBarPanel) {
        searchToggle.addEventListener('click', function () {
            var isOpen = searchBarPanel.classList.toggle('open');
            searchToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen && searchInput) {
                searchInput.focus();
            } else if (!isOpen && searchSuggestions) {
                searchSuggestions.style.display = 'none';
                if (searchInput) searchInput.setAttribute('aria-expanded', 'false');
            }
        });
    }

    if (searchForm && searchInput && searchSuggestions) {
        var liveEnabled = searchForm.getAttribute('data-live-enabled') !== '0';
        var minChars = parseInt(searchForm.getAttribute('data-min-chars') || '2', 10);
        var searchDebounceTimer = null;
        var searchAbortCtrl = null;
        var searchCache = {};
        var activeItemIndex = -1;

        function closeSuggestions() {
            searchSuggestions.style.display = 'none';
            searchInput.setAttribute('aria-expanded', 'false');
            activeItemIndex = -1;
        }

        function highlightQuery(text, query) {
            if (!query) return escapeHtml(text);
            var safeText = escapeHtml(text);
            var safeQuery = escapeHtml(query).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            var regex = new RegExp('(' + safeQuery + ')', 'gi');
            return safeText.replace(regex, '<mark>$1</mark>');
        }

        function renderSuggestions(data, query) {
            var html = '';
            var hasCategories = data.categories && data.categories.length > 0;
            var hasProducts = data.results && data.results.length > 0;

            if (!hasCategories && !hasProducts) {
                html = '<div class="search-empty">محصولی مطابق با «<strong>' + escapeHtml(query) + '</strong>» یافت نشد.</div>';
            } else {
                if (hasCategories) {
                    html += '<div class="search-suggest-cats">';
                    html += '<span class="search-suggest-cat-label">دسته‌بندی‌ها:</span>';
                    data.categories.forEach(function (cat) {
                        html += '<a href="' + escapeHtml(cat.url) + '" class="search-suggest-cat-pill search-nav-item" role="option">' + escapeHtml(cat.name) + '</a>';
                    });
                    html += '</div>';
                }

                if (hasProducts) {
                    data.results.forEach(function (prod) {
                        var stockBadge = prod.in_stock ? '' : '<span class="search-suggest-stockout">ناموجود</span>';
                        var oldPriceHtml = prod.old_price_formatted ? '<div class="search-suggest-oldprice">' + escapeHtml(prod.old_price_formatted) + '</div>' : '';

                        html += '<a href="' + escapeHtml(prod.url) + '" class="search-suggest-item search-nav-item" role="option">';
                        html += '<img src="' + escapeHtml(prod.image) + '" alt="' + escapeHtml(prod.name) + '" class="search-suggest-thumb" loading="lazy">';
                        html += '<div class="search-suggest-info">';
                        html += '<div class="search-suggest-title">' + highlightQuery(prod.name, query) + '</div>';
                        html += '<div class="search-suggest-meta">';
                        html += '<span class="search-suggest-cat">' + escapeHtml(prod.category_name) + '</span>';
                        if (prod.discount_percent > 0) {
                            html += '<span class="badge-discount" style="position:static; font-size:0.72rem; padding:1px 5px;">' + toPersianDigits(prod.discount_percent) + '%-</span>';
                        }
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="search-suggest-pricing">';
                        html += '<div class="search-suggest-price">' + escapeHtml(prod.price_formatted) + '</div>';
                        html += oldPriceHtml;
                        html += stockBadge;
                        html += '</div>';
                        html += '</a>';
                    });
                }

                var viewAllText = 'مشاهده همه نتایج (' + toPersianDigits(data.total) + ' محصول) ←';
                html += '<a href="/search?q=' + encodeURIComponent(query) + '" class="search-view-all search-nav-item" role="option">' + viewAllText + '</a>';
            }

            searchSuggestions.innerHTML = html;
            searchSuggestions.style.display = 'block';
            searchInput.setAttribute('aria-expanded', 'true');
            activeItemIndex = -1;
        }

        function triggerSearch(val) {
            if (!liveEnabled) return;
            var q = val.trim();
            if (q.length < minChars) {
                if (searchAbortCtrl) searchAbortCtrl.abort();
                if (searchSpinner) searchSpinner.style.display = 'none';
                closeSuggestions();
                return;
            }

            if (searchCache[q]) {
                if (searchSpinner) searchSpinner.style.display = 'none';
                renderSuggestions(searchCache[q], q);
                return;
            }

            if (searchSpinner) searchSpinner.style.display = 'inline-block';
            if (searchAbortCtrl) searchAbortCtrl.abort();
            searchAbortCtrl = new AbortController();

            fetch('/ajax/search_suggest.php?q=' + encodeURIComponent(q), {
                signal: searchAbortCtrl.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (searchSpinner) searchSpinner.style.display = 'none';
                if (data && data.ok) {
                    searchCache[q] = data;
                    if (searchInput.value.trim() === q) {
                        renderSuggestions(data, q);
                    }
                }
            })
            .catch(function (err) {
                if (err.name !== 'AbortError') {
                    if (searchSpinner) searchSpinner.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', function () {
            var val = searchInput.value;
            if (searchClearBtn) {
                searchClearBtn.style.display = val.length > 0 ? 'flex' : 'none';
            }
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function () {
                triggerSearch(val);
            }, 250);
        });

        searchInput.addEventListener('focus', function () {
            var val = searchInput.value.trim();
            if (val.length >= minChars && searchSuggestions.style.display === 'none') {
                triggerSearch(val);
            }
        });

        if (searchClearBtn) {
            searchClearBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchClearBtn.style.display = 'none';
                closeSuggestions();
                searchInput.focus();
            });
        }

        // Keyboard navigation for suggestions
        searchInput.addEventListener('keydown', function (e) {
            var items = searchSuggestions.querySelectorAll('.search-nav-item');
            if (!items.length || searchSuggestions.style.display === 'none') return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeItemIndex = (activeItemIndex + 1) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeItemIndex = (activeItemIndex - 1 + items.length) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'Enter') {
                if (activeItemIndex >= 0 && items[activeItemIndex]) {
                    e.preventDefault();
                    window.location.href = items[activeItemIndex].getAttribute('href');
                }
            } else if (e.key === 'Escape') {
                closeSuggestions();
            }
        });

        function updateActiveItem(items) {
            items.forEach(function (el, idx) {
                if (idx === activeItemIndex) {
                    el.classList.add('is-active');
                    el.setAttribute('aria-selected', 'true');
                    el.scrollIntoView({ block: 'nearest' });
                } else {
                    el.classList.remove('is-active');
                    el.removeAttribute('aria-selected');
                }
            });
        }

        // Close on click outside
        document.addEventListener('click', function (e) {
            if (!searchForm.contains(e.target)) {
                closeSuggestions();
            }
        });
    }

    // ---------- Homepage product carousels (Featured / Newest) ----------
    document.querySelectorAll('.carousel-wrap').forEach(function (wrap) {
        var track = wrap.querySelector('.carousel-track');
        if (!track) return;
        var isRtl = getComputedStyle(document.documentElement).direction === 'rtl';
        var sign = isRtl ? -1 : 1; // scrollLeft grows negative in RTL in modern browsers
        var prevBtn = wrap.querySelector('.carousel-nav.prev');
        var nextBtn = wrap.querySelector('.carousel-nav.next');
        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                track.scrollBy({ left: sign * track.clientWidth, behavior: 'smooth' });
            });
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                track.scrollBy({ left: -sign * track.clientWidth, behavior: 'smooth' });
            });
        }
    });

    // ---------- Variant (size/color) selection on the product page (BUG-C001) ----------
    var variantChips = document.querySelectorAll('.variant-chip input[type="radio"]');
    var selectedVariantLabel = document.getElementById('selectedVariantLabel');
    var priceCurrent = document.getElementById('productPriceCurrent');
    var stockInfo = document.getElementById('productStockInfo');
    var qtyInput = document.getElementById('productQtyInput');
    var addToCartBtn = document.getElementById('addToCartBtn');

    variantChips.forEach(function (input) {
        input.addEventListener('change', function () {
            var group = input.closest('.variant-options');
            if (group) {
                group.querySelectorAll('.variant-chip').forEach(function (chip) {
                    chip.classList.remove('selected');
                });
            }
            if (input.checked) {
                var parentChip = input.closest('.variant-chip');
                if (parentChip) parentChip.classList.add('selected');

                // Update variant label
                var labelText = input.getAttribute('data-label') || '';
                if (selectedVariantLabel) {
                    selectedVariantLabel.textContent = labelText;
                }

                // Update formatted price if variant has price override
                var formattedPrice = input.getAttribute('data-price-formatted');
                if (priceCurrent && formattedPrice) {
                    priceCurrent.textContent = formattedPrice;
                }

                // Update stock status badge & Add to Cart button
                var stock = parseInt(input.getAttribute('data-stock') || '0', 10);
                if (stockInfo) {
                    if (stock <= 0) {
                        stockInfo.innerHTML = '<span class="stock-out">این گزینه در حال حاضر ناموجود است</span>';
                    } else if (stock <= 5) {
                        stockInfo.innerHTML = '<span class="stock-low">فقط ' + toPersianDigits(stock) + ' عدد باقی مانده</span>';
                    } else {
                        stockInfo.innerHTML = '<span class="stock-ok">موجود در انبار</span>';
                    }
                }

                if (addToCartBtn) {
                    if (stock <= 0) {
                        addToCartBtn.disabled = true;
                        addToCartBtn.textContent = 'این گزینه ناموجود است';
                    } else {
                        addToCartBtn.disabled = false;
                        addToCartBtn.textContent = 'افزودن به سبد خرید';
                    }
                }

                // Adjust quantity input max & current value
                if (qtyInput) {
                    var newMax = Math.min(Math.max(1, stock), 20);
                    qtyInput.setAttribute('max', newMax);
                    var currentQty = parseInt(qtyInput.value, 10) || 1;
                    if (currentQty > newMax) {
                        qtyInput.value = newMax;
                    }
                }
            }
        });
    });

    // ---------- Quantity stepper on the product page ----------
    document.querySelectorAll('.qty-selector').forEach(function (box) {
        var input = box.querySelector('input');
        box.querySelector('.qty-minus').addEventListener('click', function () {
            var v = Math.max(1, (parseInt(input.value, 10) || 1) - 1);
            input.value = v;
        });
        box.querySelector('.qty-plus').addEventListener('click', function () {
            var max = parseInt(input.getAttribute('max') || '99', 10);
            var v = Math.min(max, (parseInt(input.value, 10) || 1) + 1);
            input.value = v;
        });
    });

    // ---------- Add to cart (AJAX) ----------
    var addForm = document.getElementById('addToCartForm');
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = addForm.querySelector('button[type="submit"]');
            var originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'در حال افزودن...';

            fetch('/ajax/cart_add.php', {
                method: 'POST',
                body: new FormData(addForm),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) {
                    var counter = document.getElementById('cartCount');
                    if (counter) counter.textContent = data.cartCount;
                    btn.textContent = 'به سبد اضافه شد ✓';
                    setTimeout(function () {
                        btn.textContent = originalText;
                        btn.disabled = false;
                    }, 1500);
                } else {
                    alert(data.message || 'خطا در افزودن به سبد خرید');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(function () {
                alert('ارتباط با سرور برقرار نشد.');
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    }

    // ---------- Quantity change / remove on the cart page ----------
    document.querySelectorAll('.cart-qty-input').forEach(function (input) {
        input.addEventListener('change', function () {
            input.closest('form').submit();
        });
    });

    // ---------- Product image gallery ----------
    document.querySelectorAll('.gallery-thumbs img').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            var mainImg = document.getElementById('mainProductImage');
            if (mainImg) mainImg.src = thumb.getAttribute('data-full');
            document.querySelectorAll('.gallery-thumbs img').forEach(function (t) { t.classList.remove('active'); });
            thumb.classList.add('active');
        });
    });

});
