/**
 * AB-Socks Shipping Methods Workstation Interactive Studio
 * v1.26.0 - Live Checkout Preview, Instant Studio Population, and Ajax Active Toggles
 */

document.addEventListener('DOMContentLoaded', function () {
    var methodsData = window.__SHIPPING_METHODS__ || {};
    var studioForm = document.getElementById('shippingStudioForm');
    var methodIdInput = document.getElementById('studioMethodId');
    var nameInput = document.getElementById('studioName');
    var deliveryInput = document.getElementById('studioDelivery');
    var descInput = document.getElementById('studioDescription');
    var matchTypeSelect = document.getElementById('studioMatchType');
    var matchValueGroup = document.getElementById('studioMatchValueGroup');
    var matchValueInput = document.getElementById('studioMatchValue');
    var costInput = document.getElementById('studioCost');
    var actualCostInput = document.getElementById('studioActualCost');
    var freeAboveInput = document.getElementById('studioFreeAbove');
    var isActiveCheckbox = document.getElementById('studioIsActive');
    var studioTitle = document.getElementById('studioTitleText');
    var studioModePill = document.getElementById('studioModePill');
    var submitBtn = document.getElementById('studioSubmitBtn');
    var resetBtn = document.getElementById('studioResetBtn');

    // Live Preview DOM elements
    var previewName = document.getElementById('previewMethodName');
    var previewDelivery = document.getElementById('previewDeliveryTime');
    var previewCost = document.getElementById('previewCostText');

    function updatePreview() {
        if (!previewName || !previewCost) return;
        var name = (nameInput && nameInput.value.trim()) || 'نام روش ارسال';
        var delivery = (deliveryInput && deliveryInput.value.trim()) || '';
        var rawCost = (costInput && costInput.value.replace(/\D/g, '')) || '0';
        var cost = parseInt(rawCost, 10) || 0;
        var freeAbove = parseInt((freeAboveInput && freeAboveInput.value.replace(/\D/g, '')) || '0', 10);

        previewName.textContent = name;
        if (previewDelivery) {
            previewDelivery.textContent = delivery ? (' • ' + delivery) : '';
            previewDelivery.style.display = delivery ? 'inline' : 'none';
        }

        var priceFmt = (window.AB && window.AB.fmt) ? window.AB.fmt.price : function(n) { return n + ' تومان'; };

        if (cost === 0) {
            previewCost.textContent = 'رایگان';
        } else if (freeAbove > 0) {
            previewCost.textContent = priceFmt(cost) + ' (رایگان از ' + priceFmt(freeAbove) + ')';
        } else {
            previewCost.textContent = priceFmt(cost);
        }
    }

    function toggleMatchValue() {
        if (!matchTypeSelect || !matchValueGroup) return;
        matchValueGroup.style.display = matchTypeSelect.value === 'province_contains' ? 'block' : 'none';
    }

    if (matchTypeSelect) {
        matchTypeSelect.addEventListener('change', toggleMatchValue);
    }

    // Attach preview event listeners
    [nameInput, deliveryInput, costInput, freeAboveInput].forEach(function (el) {
        if (el) {
            el.addEventListener('input', updatePreview);
        }
    });

    // Reset Studio to "New Method"
    function resetStudio() {
        if (!studioForm) return;
        methodIdInput.value = '0';
        nameInput.value = '';
        if (deliveryInput) deliveryInput.value = '';
        if (descInput) descInput.value = '';
        if (matchTypeSelect) matchTypeSelect.value = 'province_contains';
        if (matchValueInput) matchValueInput.value = '';
        if (costInput) costInput.value = '0';
        if (actualCostInput) actualCostInput.value = '';
        if (freeAboveInput) freeAboveInput.value = '';
        if (isActiveCheckbox) isActiveCheckbox.checked = true;

        if (studioTitle) studioTitle.textContent = 'روش ارسال جدید';
        if (studioModePill) {
            studioModePill.textContent = 'جدید';
            studioModePill.className = 'studio-mode-pill mode-new';
        }
        if (submitBtn) submitBtn.textContent = 'افزودن روش ارسال';
        if (resetBtn) resetBtn.style.display = 'none';

        toggleMatchValue();
        updatePreview();

        document.querySelectorAll('.shipping-method-row').forEach(function (r) {
            r.classList.remove('selected');
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function (e) {
            e.preventDefault();
            resetStudio();
        });
    }

    // Populate Studio for Editing
    function loadMethodIntoStudio(id) {
        var m = methodsData[id];
        if (!m) return;

        methodIdInput.value = m.id;
        nameInput.value = m.name || '';
        if (deliveryInput) deliveryInput.value = m.estimated_delivery || '';
        if (descInput) descInput.value = m.description || '';
        if (matchTypeSelect) matchTypeSelect.value = m.match_type || 'default';
        if (matchValueInput) matchValueInput.value = m.match_value || '';
        if (costInput) costInput.value = m.cost || '0';
        if (actualCostInput) actualCostInput.value = m.actual_cost !== null && m.actual_cost !== undefined ? m.actual_cost : '';
        if (freeAboveInput) freeAboveInput.value = m.free_above_amount !== null && m.free_above_amount !== undefined ? m.free_above_amount : '';
        if (isActiveCheckbox) isActiveCheckbox.checked = !!m.is_active;

        if (studioTitle) studioTitle.textContent = 'ویرایش روش ارسال';
        if (studioModePill) {
            studioModePill.textContent = 'شناسه #' + (window.AB && window.AB.fmt ? window.AB.fmt.faDigits(m.id) : m.id);
            studioModePill.className = 'studio-mode-pill mode-edit';
        }
        if (submitBtn) submitBtn.textContent = 'ذخیره تغییرات';
        if (resetBtn) resetBtn.style.display = 'inline-block';

        toggleMatchValue();
        updatePreview();

        // Highlight row
        document.querySelectorAll('.shipping-method-row').forEach(function (r) {
            r.classList.toggle('selected', r.getAttribute('data-id') == id);
        });

        // Scroll to studio if on mobile
        if (window.innerWidth < 1024 && studioForm) {
            studioForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Bind Edit Buttons on Table
    document.querySelectorAll('.btn-edit-method').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var id = this.getAttribute('data-id');
            loadMethodIntoStudio(id);
        });
    });

    // Toggle Active Switch via Ajax
    document.querySelectorAll('.shipping-active-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var id = this.getAttribute('data-id');
            var isChecked = this.checked;
            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            var fd = new FormData();
            fd.append('action', 'toggle_active');
            fd.append('id', id);
            fd.append('csrf_token', csrfToken);

            fetch('shipping_methods.php', {
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
                    if (methodsData[id]) methodsData[id].is_active = res.is_active;
                }
            })
            .catch(function () {
                toggle.checked = !isChecked;
            });
        });
    });

    // Check if URL has ?edit=X
    var urlParams = new URLSearchParams(window.location.search);
    var editId = urlParams.get('edit');
    if (editId && methodsData[editId]) {
        loadMethodIntoStudio(editId);
    } else {
        updatePreview();
    }
});
