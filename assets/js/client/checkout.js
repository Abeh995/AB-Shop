/**
 * Storefront Checkout Shipping Estimator (assets/js/client/checkout.js)
 * Debounced AJAX shipping recalculation with AbortController race prevention.
 */
(function (window, document) {
  'use strict';

  const ABClient = window.ABClient;
  if (!ABClient || !ABClient.behavior) return;

  ABClient.behavior.register('shipping-estimate', '[data-ab-shipping-estimate]', function (provinceInput) {
    const note = document.getElementById('shippingEstimateNote');
    const shippingRow = document.getElementById('shippingRow');
    const shippingCostValue = document.getElementById('shippingCostValue');
    const grandTotalValue = document.getElementById('grandTotalValue');

    let timer = null;
    let abortCtrl = null;

    const refreshShipping = function () {
      if (abortCtrl) abortCtrl.abort();
      abortCtrl = new AbortController();

      const fd = new FormData();
      fd.append('province', provinceInput.value);

      ABClient.api.post('/ajax/shipping_estimate.php', fd, { signal: abortCtrl.signal })
        .then(function (data) {
          if (!data || !data.ok) {
            if (note && data && data.message) {
              note.textContent = data.message;
            }
            return;
          }
          const deliveryText = data.estimated_delivery ? (' • ' + data.estimated_delivery) : '';
          const label = data.method_name
            ? ('هزینه ارسال (' + data.method_name + deliveryText + ')')
            : 'هزینه ارسال';

          if (shippingRow && shippingRow.querySelector('span')) {
            shippingRow.querySelector('span').textContent = label;
          }
          if (shippingCostValue) {
            shippingCostValue.textContent = data.is_free ? 'رایگان' : data.cost_formatted;
          }
          if (note) {
            note.textContent = data.method_name
              ? (label + ': ' + (data.is_free ? 'رایگان' : data.cost_formatted))
              : 'هزینه ارسال بر اساس استانی که وارد می‌کنید محاسبه می‌شود.';
          }
          if (grandTotalValue && data.grand_total_formatted) {
            grandTotalValue.textContent = data.grand_total_formatted;
          }
        })
        .catch(function (err) {
          if (err && err.name === 'AbortError') return;
          if (note) {
            note.textContent = (err && err.status === 403)
              ? 'نشست شما منقضی شده است؛ لطفاً صفحه را تازه‌سازی کنید.'
              : 'محاسبه آنلاین هزینه ارسال انجام نشد؛ هنگام ثبت سفارش مبلغ دقیق محاسبه می‌شود.';
          }
          if (err && err.status === 403) {
            ABClient.toast.show('نشست شما منقضی شده است؛ صفحه را تازه‌سازی کنید.', 'error');
          }
        });
    };

    provinceInput.addEventListener('input', function () {
      window.clearTimeout(timer);
      timer = window.setTimeout(refreshShipping, 500);
    });
    provinceInput.addEventListener('blur', refreshShipping);
  });
})(window, document);
