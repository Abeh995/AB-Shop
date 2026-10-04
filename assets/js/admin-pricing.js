/**
 * Admin Bulk Pricing Workstation Client-Side Engine
 * Real-time dynamic pricing calculation, live margin guards, and modal controls.
 */
document.addEventListener('DOMContentLoaded', function () {
    // DOM Elements - Sidebar Controls
    const targetBtns = document.querySelectorAll('.pricing-seg-btn');
    const methodBtns = document.querySelectorAll('.pricing-method-btn');
    const valueInput = document.getElementById('pricingValueInput');
    const unitLabel = document.getElementById('pricingUnitLabel');
    const quickChips = document.querySelectorAll('.pricing-chip');
    const roundingSelect = document.getElementById('pricingRoundingSelect');
    const customRoundingWrap = document.getElementById('pricingCustomRoundingWrap');
    const customRoundingInput = document.getElementById('pricingCustomRoundingInput');
    const applyToVariantsCheck = document.getElementById('pricingApplyVariants');
    const reasonInput = document.getElementById('pricingReasonInput');

    // Hidden form fields
    const formField = document.getElementById('formField');
    const formMethod = document.getElementById('formMethod');
    const formValue = document.getElementById('formValue');
    const formRoundingStep = document.getElementById('formRoundingStep');
    const formApplyVariants = document.getElementById('formApplyVariants');
    const formAllowNegative = document.getElementById('formAllowNegative');
    const formReason = document.getElementById('formReason');
    const hiddenProductIdsContainer = document.getElementById('hiddenProductIdsContainer');
    const bulkPricingForm = document.getElementById('bulkPricingForm');

    // Sidebar Impact Elements
    const impactSelectedCount = document.getElementById('impactSelectedCount');
    const impactAvgDelta = document.getElementById('impactAvgDelta');
    const impactAlertBox = document.getElementById('impactAlertBox');
    const impactAlertCount = document.getElementById('impactAlertCount');
    const openModalBtn = document.getElementById('openPricingModalBtn');

    // Table & Selection Elements
    const masterCheckbox = document.getElementById('pricingMasterCheckbox');
    const rowCheckboxes = document.querySelectorAll('.pricing-row-check');
    const tableRows = document.querySelectorAll('.pricing-item-row');
    const selectionCounterBadge = document.getElementById('selectionCounterBadge');

    // Preset Buttons
    const presetSelectAll = document.getElementById('presetSelectAll');
    const presetSelectNoCost = document.getElementById('presetSelectNoCost');
    const presetSelectCategory = document.getElementById('presetSelectCategory');
    const presetClearSelection = document.getElementById('presetClearSelection');

    // Modal Elements
    const modalBackdrop = document.getElementById('pricingModalBackdrop');
    const modalCloseBtn = document.getElementById('pricingModalCloseBtn');
    const modalCancelBtn = document.getElementById('pricingModalCancelBtn');
    const modalConfirmBtn = document.getElementById('pricingModalConfirmBtn');
    const modalStatCount = document.getElementById('modalStatCount');
    const modalStatTarget = document.getElementById('modalStatTarget');
    const modalStatFormula = document.getElementById('modalStatFormula');
    const modalStatDelta = document.getElementById('modalStatDelta');
    const modalDangerBox = document.getElementById('modalDangerBox');
    const modalDangerCount = document.getElementById('modalDangerCount');
    const modalAllowNegativeCheck = document.getElementById('modalAllowNegativeCheck');

    // State
    let currentTarget = 'sale_price';
    let currentMethod = 'percentage';
    let negativeMarginItemsCount = 0;

    // Helper: format number in Persian digits with commas
    function formatToman(num) {
        return Math.round(num).toLocaleString('fa-IR') + ' تومان';
    }

    const toPersian = str => (window.AB && window.AB.fmt ? window.AB.fmt.faDigits(str) : String(str));


    // Get Active Rounding Step
    function getRoundingStep() {
        if (!roundingSelect) return 0;
        const val = roundingSelect.value;
        if (val === 'custom') {
            return Math.max(0, parseInt(customRoundingInput.value.replace(/,/g, ''), 10) || 0);
        }
        return Math.max(0, parseInt(val, 10) || 0);
    }

    // 1. Recalculation Engine
    function recalculate() {
        const rawVal = parseFloat(valueInput.value.replace(/,/g, '')) || 0;
        const roundingStep = getRoundingStep();

        negativeMarginItemsCount = 0;
        let totalDelta = 0;
        let selectedCount = 0;

        tableRows.forEach(row => {
            const salePrice = parseInt(row.dataset.price, 10) || 0;
            const costPrice = parseInt(row.dataset.cost, 10) || 0;
            const isChecked = row.querySelector('.pricing-row-check')?.checked;

            const base = (currentTarget === 'cost_price') ? costPrice : salePrice;
            let newVal = base;

            switch (currentMethod) {
                case 'percentage':
                    newVal = Math.round(base * (1 + rawVal / 100));
                    break;
                case 'direct_value':
                    newVal = Math.round(rawVal);
                    break;
                case 'fixed_amount':
                default:
                    newVal = Math.round(base + rawVal);
                    break;
            }
            newVal = Math.max(0, newVal);

            if (roundingStep > 0 && newVal > 0) {
                newVal = Math.round(newVal / roundingStep) * roundingStep;
                newVal = Math.max(0, newVal);
            }

            const delta = newVal - base;
            const propCell = row.querySelector('.pricing-prop-num');
            const deltaCell = row.querySelector('.pricing-delta-tag');
            const alertCell = row.querySelector('.pricing-row-alert');

            if (propCell) {
                propCell.textContent = newVal.toLocaleString('fa-IR');
            }

            if (deltaCell) {
                if (delta > 0) {
                    deltaCell.textContent = '+' + delta.toLocaleString('fa-IR');
                    deltaCell.className = 'pricing-delta-tag up';
                } else if (delta < 0) {
                    deltaCell.textContent = delta.toLocaleString('fa-IR');
                    deltaCell.className = 'pricing-delta-tag down';
                } else {
                    deltaCell.textContent = '۰';
                    deltaCell.className = 'pricing-delta-tag neutral';
                }
            }

            // Check negative margin
            let isNegative = false;
            if (currentTarget === 'sale_price') {
                if (costPrice > 0 && newVal < costPrice) {
                    isNegative = true;
                }
            } else if (currentTarget === 'cost_price') {
                if (newVal > salePrice && salePrice > 0) {
                    isNegative = true;
                }
            }

            if (alertCell) {
                if (isNegative) {
                    alertCell.innerHTML = '<span class="pricing-negative-badge">⚠️ زیان‌ده (' + Math.abs(newVal - costPrice).toLocaleString('fa-IR') + '- تومان)</span>';
                    row.classList.add('row-negative-margin');
                    if (isChecked) negativeMarginItemsCount++;
                } else {
                    alertCell.innerHTML = '';
                    row.classList.remove('row-negative-margin');
                }
            }

            if (isChecked) {
                selectedCount++;
                totalDelta += delta;
            }
        });

        // Update Impact Box in Sidebar
        if (impactSelectedCount) {
            impactSelectedCount.textContent = toPersian(selectedCount) + ' کالا';
        }
        if (selectionCounterBadge) {
            selectionCounterBadge.textContent = toPersian(selectedCount) + ' محصول انتخاب‌شده';
        }
        if (impactAvgDelta) {
            const avgDelta = selectedCount > 0 ? Math.round(totalDelta / selectedCount) : 0;
            const prefix = avgDelta > 0 ? '+' : '';
            impactAvgDelta.textContent = prefix + avgDelta.toLocaleString('fa-IR') + ' تومان';
            impactAvgDelta.style.color = avgDelta > 0 ? '#059669' : (avgDelta < 0 ? '#dc2626' : '#64748b');
        }

        // Show/hide negative alert in sidebar
        if (impactAlertBox) {
            if (negativeMarginItemsCount > 0) {
                impactAlertBox.classList.add('active');
                if (impactAlertCount) {
                    impactAlertCount.textContent = toPersian(negativeMarginItemsCount);
                }
            } else {
                impactAlertBox.classList.remove('active');
            }
        }

        // Toggle submit button
        if (openModalBtn) {
            openModalBtn.disabled = selectedCount === 0;
        }
    }

    // 2. Target Segmented Control Switching
    targetBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            targetBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentTarget = this.dataset.target;
            if (formField) formField.value = currentTarget;
            recalculate();
        });
    });

    // 3. Method Switching
    methodBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            methodBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentMethod = this.dataset.method;
            if (formMethod) formMethod.value = currentMethod;

            if (currentMethod === 'percentage') {
                unitLabel.textContent = '٪';
                valueInput.placeholder = 'مثلاً: 15 یا 10-';
            } else if (currentMethod === 'direct_value') {
                unitLabel.textContent = 'تومان';
                valueInput.placeholder = 'مثلاً: 95000';
            } else {
                unitLabel.textContent = 'تومان';
                valueInput.placeholder = 'مثلاً: 25000 یا 10000-';
            }
            recalculate();
        });
    });

    // 4. Value Input & Quick Chips
    if (valueInput) {
        valueInput.addEventListener('input', recalculate);
    }

    quickChips.forEach(chip => {
        chip.addEventListener('click', function () {
            const chipVal = this.dataset.val;
            // Switch to percentage if percentage chip
            if (chipVal.includes('%')) {
                const numeric = parseFloat(chipVal.replace('%', ''));
                methodBtns.forEach(b => {
                    if (b.dataset.method === 'percentage') b.click();
                });
                valueInput.value = numeric;
            } else {
                valueInput.value = chipVal;
            }
            recalculate();
        });
    });

    // 5. Rounding Select & Custom Step
    if (roundingSelect) {
        roundingSelect.addEventListener('change', function () {
            if (this.value === 'custom') {
                customRoundingWrap.classList.add('active');
                customRoundingInput.focus();
            } else {
                customRoundingWrap.classList.remove('active');
            }
            recalculate();
        });
    }

    if (customRoundingInput) {
        customRoundingInput.addEventListener('input', recalculate);
    }

    // 6. Selection Handling
    function updateRowSelection(row, checked) {
        const cb = row.querySelector('.pricing-row-check');
        if (cb) cb.checked = checked;
        if (checked) {
            row.classList.add('row-selected');
        } else {
            row.classList.remove('row-selected');
        }
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const row = this.closest('tr');
            if (this.checked) {
                row.classList.add('row-selected');
            } else {
                row.classList.remove('row-selected');
            }
            recalculate();
        });
    });

    if (masterCheckbox) {
        masterCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            tableRows.forEach(row => {
                if (row.style.display !== 'none') {
                    updateRowSelection(row, isChecked);
                }
            });
            recalculate();
        });
    }

    // Preset Actions
    if (presetSelectAll) {
        presetSelectAll.addEventListener('click', function () {
            tableRows.forEach(row => {
                if (row.style.display !== 'none') {
                    updateRowSelection(row, true);
                }
            });
            if (masterCheckbox) masterCheckbox.checked = true;
            recalculate();
        });
    }

    if (presetSelectNoCost) {
        presetSelectNoCost.addEventListener('click', function () {
            tableRows.forEach(row => {
                const cost = parseInt(row.dataset.cost, 10) || 0;
                if (cost === 0 && row.style.display !== 'none') {
                    updateRowSelection(row, true);
                } else {
                    updateRowSelection(row, false);
                }
            });
            recalculate();
        });
    }

    if (presetSelectCategory) {
        presetSelectCategory.addEventListener('click', function () {
            const catSelect = document.getElementById('pricingCategoryFilter');
            const targetCat = catSelect ? catSelect.value : '';
            if (!targetCat) {
                tableRows.forEach(row => {
                    if (row.style.display !== 'none') updateRowSelection(row, true);
                });
            } else {
                tableRows.forEach(row => {
                    if (row.dataset.category === targetCat && row.style.display !== 'none') {
                        updateRowSelection(row, true);
                    } else {
                        updateRowSelection(row, false);
                    }
                });
            }
            recalculate();
        });
    }

    if (presetClearSelection) {
        presetClearSelection.addEventListener('click', function () {
            tableRows.forEach(row => updateRowSelection(row, false));
            if (masterCheckbox) masterCheckbox.checked = false;
            recalculate();
        });
    }

    // 7. Modal Confirmation Flow
    if (openModalBtn) {
        openModalBtn.addEventListener('click', function () {
            // Count selected
            let selectedCount = 0;
            tableRows.forEach(row => {
                if (row.querySelector('.pricing-row-check')?.checked) selectedCount++;
            });

            if (selectedCount === 0) {
                alert('لطفاً حداقل یک محصول را از جدول انتخاب نمایید.');
                return;
            }

            // Fill modal details
            if (modalStatCount) modalStatCount.textContent = toPersian(selectedCount) + ' محصول';
            if (modalStatTarget) modalStatTarget.textContent = (currentTarget === 'cost_price') ? 'بهای تمام‌شده (خرید)' : 'قیمت فروش به مشتری';

            let formulaText = '';
            const rawVal = parseFloat(valueInput.value.replace(/,/g, '')) || 0;
            if (currentMethod === 'percentage') {
                formulaText = (rawVal >= 0 ? '+' : '') + rawVal + '٪';
            } else if (currentMethod === 'direct_value') {
                formulaText = 'مبلغ ثابت ' + rawVal.toLocaleString('fa-IR') + ' تومان';
            } else {
                formulaText = (rawVal >= 0 ? '+' : '') + rawVal.toLocaleString('fa-IR') + ' تومان';
            }

            const rStep = getRoundingStep();
            if (rStep > 0) {
                formulaText += ' (رند به ' + rStep.toLocaleString('fa-IR') + ' تومان)';
            }
            if (modalStatFormula) modalStatFormula.textContent = formulaText;

            // Handle negative margin guard
            if (negativeMarginItemsCount > 0 && currentTarget === 'sale_price') {
                modalDangerBox.classList.add('active');
                if (modalDangerCount) modalDangerCount.textContent = toPersian(negativeMarginItemsCount);
                modalAllowNegativeCheck.checked = false;
                modalConfirmBtn.disabled = true;
            } else {
                modalDangerBox.classList.remove('active');
                modalConfirmBtn.disabled = false;
            }

            modalBackdrop.classList.add('open');
        });
    }

    if (modalAllowNegativeCheck) {
        modalAllowNegativeCheck.addEventListener('change', function () {
            modalConfirmBtn.disabled = !this.checked;
        });
    }

    const hidePricingModal = () => {
        if (modalBackdrop) modalBackdrop.classList.remove('open');
    };

    if (modalCloseBtn) modalCloseBtn.addEventListener('click', hidePricingModal);
    if (modalCancelBtn) modalCancelBtn.addEventListener('click', hidePricingModal);
    if (modalBackdrop) {
        modalBackdrop.addEventListener('click', function (e) {
            if (e.target === modalBackdrop) hidePricingModal();
        });
    }


    // 8. Final Form Submission
    if (modalConfirmBtn) {
        modalConfirmBtn.addEventListener('click', function () {
            // Populate hidden inputs
            formField.value = currentTarget;
            formMethod.value = currentMethod;
            formValue.value = valueInput.value.replace(/,/g, '');
            formRoundingStep.value = getRoundingStep();
            formApplyVariants.value = applyToVariantsCheck?.checked ? '1' : '0';
            formAllowNegative.value = (modalAllowNegativeCheck && modalAllowNegativeCheck.checked) ? '1' : '0';
            formReason.value = reasonInput?.value || '';

            // Inject checked product IDs
            hiddenProductIdsContainer.innerHTML = '';
            tableRows.forEach(row => {
                const cb = row.querySelector('.pricing-row-check');
                if (cb && cb.checked) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'product_ids[]';
                    input.value = cb.value;
                    hiddenProductIdsContainer.appendChild(input);
                }
            });

            // UI feedback
            modalConfirmBtn.disabled = true;
            modalConfirmBtn.textContent = 'در حال ذخیره‌سازی...';

            bulkPricingForm.submit();
        });
    }

    // Initial calculation on load
    recalculate();
});
