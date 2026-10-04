/**
 * Admin Product Edit Script (assets/js/admin-product-edit.js)
 * Handles dynamic variant row creation, global pricing toggle,
 * live profit margin calculation, and tag tokenizer initialization.
 */
(function () {
    'use strict';

    function initVariantManagement() {
        var addBtn = document.getElementById('addVariantRow');
        var tbody = document.getElementById('variantRows');
        var hasVariantsToggle = document.getElementById('hasVariantsToggle');
        var useGlobalEl = document.getElementById('useGlobalVariantStrategy');
        var stockField = document.getElementById('stockField');
        var stockWrap = document.getElementById('stockFieldWrap');
        var variantSection = document.getElementById('variantSection');

        function syncGlobalVariantStrategy() {
            if (!useGlobalEl) return;
            var useGlobal = useGlobalEl.checked;
            var hasVariants = hasVariantsToggle ? hasVariantsToggle.checked : false;
            useGlobalEl.disabled = !hasVariants;
            document.querySelectorAll('.variant-default-radio').forEach(function (radio) {
                radio.disabled = useGlobal || !hasVariants;
                if (radio.closest('td')) {
                    radio.closest('td').style.opacity = (useGlobal || !hasVariants) ? '0.35' : '1';
                }
            });
        }

        function syncVariantToggle() {
            if (!hasVariantsToggle) return;
            var hasVariants = hasVariantsToggle.checked;
            if (stockField) stockField.disabled = hasVariants;
            if (stockWrap) stockWrap.style.opacity = hasVariants ? '0.5' : '1';
            if (variantSection) {
                variantSection.style.opacity = hasVariants ? '1' : '0.5';
                variantSection.style.pointerEvents = hasVariants ? 'auto' : 'none';
            }
            document.querySelectorAll('.variant-input').forEach(function (el) { el.disabled = !hasVariants; });
            syncGlobalVariantStrategy();
        }

        if (addBtn && tbody) {
            addBtn.addEventListener('click', function () {
                var nextIdx = tbody.querySelectorAll('.variant-item-row').length;
                var useGlobal = useGlobalEl ? useGlobalEl.checked : true;
                var tr = document.createElement('tr');
                tr.className = 'variant-item-row';
                tr.style.cssText = 'border-bottom: 1px solid var(--color-border);';
                tr.innerHTML = '<input type="hidden" name="variant_id[]" value="">' +
                    '<td style="padding: 8px;"><input class="form-control variant-input" type="text" name="variant_size[]" placeholder="سایز"></td>' +
                    '<td style="padding: 8px;"><input class="form-control variant-input" type="text" name="variant_color[]" placeholder="رنگ"></td>' +
                    '<td style="padding: 8px;"><input class="form-control variant-input" type="number" name="variant_stock[]" placeholder="۰"></td>' +
                    '<td style="padding: 8px;"><input class="form-control variant-input" type="text" inputmode="numeric" name="variant_cost_price[]" placeholder="پیش‌فرض"></td>' +
                    '<td style="padding: 8px; text-align: center;"><label style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 100%; height: 100%; margin: 0;"><input type="radio" class="variant-default-radio" name="default_variant_index" value="' + nextIdx + '" ' + (useGlobal ? 'disabled' : '') + '></label></td>' +
                    '<td style="padding: 8px; text-align: center;"><button type="button" class="btn btn-sm btn-outline" onclick="this.closest(\'tr\').remove()" style="color: var(--color-danger); padding: 4px 8px;">✕</button></td>';
                tbody.appendChild(tr);
                syncGlobalVariantStrategy();
            });
        }

        if (useGlobalEl) {
            useGlobalEl.addEventListener('change', syncGlobalVariantStrategy);
        }

        if (hasVariantsToggle) {
            hasVariantsToggle.addEventListener('change', syncVariantToggle);
            syncVariantToggle();
        }
    }

    function initProfitMarginCalculator() {
        var priceInput = document.getElementById('priceInput');
        var discountInput = document.getElementById('discountPriceInput');
        var costInput = document.getElementById('costPriceInput');
        var unitProfitEl = document.getElementById('unitProfitDisplay');
        var marginEl = document.getElementById('marginPercentageDisplay');

        if (!priceInput || !costInput || !unitProfitEl || !marginEl) return;

        function calculateLiveMargin() {
            var priceStr = priceInput.value.replace(/\D/g, '');
            var discountStr = discountInput ? discountInput.value.replace(/\D/g, '') : '';
            var costStr = costInput.value.replace(/\D/g, '');

            var price = parseInt(priceStr, 10) || 0;
            var discount = parseInt(discountStr, 10) || 0;
            var cost = parseInt(costStr, 10) || 0;

            var effectiveSellingPrice = (discount > 0 && discount < price) ? discount : price;

            if (effectiveSellingPrice > 0 && cost > 0) {
                var profit = effectiveSellingPrice - cost;
                var marginPct = (profit / effectiveSellingPrice) * 100;

                unitProfitEl.innerText = profit.toLocaleString('fa-IR') + ' تومان';
                unitProfitEl.style.color = profit >= 0 ? 'var(--color-success)' : 'var(--color-danger)';

                marginEl.innerText = marginPct.toFixed(1).replace('.', '٫') + '٪';
                marginEl.style.color = marginPct >= 0 ? 'var(--color-success)' : 'var(--color-danger)';
            } else {
                unitProfitEl.innerText = '—';
                unitProfitEl.style.color = 'var(--color-text)';
                marginEl.innerText = '—';
                marginEl.style.color = 'var(--color-text)';
            }
        }

        priceInput.addEventListener('input', calculateLiveMargin);
        if (discountInput) discountInput.addEventListener('input', calculateLiveMargin);
        costInput.addEventListener('input', calculateLiveMargin);
        calculateLiveMargin();
    }

    function initTagTokenizer() {
        var tagContainer = document.getElementById('tagTokenizer');
        if (!tagContainer || !window.AB || !window.AB.autocomplete) return;

        var dataEl = document.getElementById('productEditTagData');
        var tagData = { allTags: [], selectedTagIds: [] };
        if (dataEl) {
            try {
                tagData = JSON.parse(dataEl.textContent || '{}');
            } catch (e) {
                console.error('Failed to parse productEditTagData', e);
            }
        }

        var allCatalogTags = tagData.allTags || [];
        var initialSelectedIds = tagData.selectedTagIds || [];
        var tagMap = {};
        allCatalogTags.forEach(function (t) { tagMap[t.id] = t.name; });

        var initialItems = [];
        initialSelectedIds.forEach(function (id) {
            if (tagMap[id]) initialItems.push({ id: id, name: tagMap[id] });
        });

        var instance = AB.autocomplete.init(tagContainer, {
            provider: 'tags',
            multiple: true,
            allowNew: true,
            inputName: 'tag_ids[]',
            newInputName: 'new_tags'
        });

        if (instance) {
            instance.setItems(initialItems);

            // Quick chip binding
            var popularWrap = document.getElementById('tagPopularChips');
            if (popularWrap) {
                popularWrap.addEventListener('click', function (e) {
                    var chip = e.target.closest('.tag-quick-chip');
                    if (!chip) return;
                    var cId = parseInt(chip.getAttribute('data-id'), 10);
                    var cName = chip.getAttribute('data-name');
                    var current = instance.getItems();
                    var exists = current.some(function (it) { return it.id === cId; });
                    if (exists) {
                        var idx = current.findIndex(function (it) { return it.id === cId; });
                        instance.removeItem('existing', idx);
                    } else {
                        instance.addItem({ id: cId, name: cName });
                    }
                });

                tagContainer.addEventListener('ab:autocomplete:change', function (e) {
                    var selected = e.detail.selected || [];
                    popularWrap.querySelectorAll('.tag-quick-chip').forEach(function (chip) {
                        var cId = parseInt(chip.getAttribute('data-id'), 10);
                        chip.classList.toggle('selected', selected.some(function (s) { return s.id === cId; }));
                    });
                });
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initVariantManagement();
            initProfitMarginCalculator();
            initTagTokenizer();
        });
    } else {
        initVariantManagement();
        initProfitMarginCalculator();
        initTagTokenizer();
    }
})();
