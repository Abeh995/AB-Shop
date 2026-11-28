/**
 * Storefront WebOTP Autofill Module (assets/js/client/verify-phone.js)
 * Progressive enhancement for one-time SMS verification codes.
 */
(function (window, document) {
  'use strict';

  const ABClient = window.ABClient;
  if (!ABClient || !ABClient.behavior) return;

  const normalizeDigits = function (value) {
    return String(value || '')
      .replace(/[۰-۹]/g, function (d) { return String(d.charCodeAt(0) - 1776); })
      .replace(/[٠-٩]/g, function (d) { return String(d.charCodeAt(0) - 1632); });
  };

  ABClient.behavior.register('webotp', '[data-ab-webotp]', function (formEl) {
    const input = formEl.querySelector('#otp-code, input[name="code"]');
    if (!input) return;

    // Layer 1: Hint SMS OTP autofill & normalize Persian/Arabic numerals to ASCII
    input.setAttribute('autocomplete', 'one-time-code');
    input.addEventListener('input', function () {
      const normalized = normalizeDigits(input.value).replace(/[^\d]/g, '').slice(0, 6);
      if (input.value !== normalized) {
        input.value = normalized;
      }
    });

    // Layer 2: WebOTP Credential API (graceful progressive enhancement)
    if (!('OTPCredential' in window) || !navigator.credentials) {
      return;
    }

    const controller = new AbortController();

    formEl.addEventListener('submit', function () {
      controller.abort();
    }, { once: true });

    const timeoutId = window.setTimeout(function () {
      controller.abort();
    }, 60 * 1000);

    navigator.credentials.get({
      otp: { transport: ['sms'] },
      signal: controller.signal
    })
      .then(function (otp) {
        if (!otp || !otp.code) return;
        input.value = otp.code;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        if (typeof formEl.requestSubmit === 'function') {
          formEl.requestSubmit();
        } else {
          formEl.submit();
        }
      })
      .catch(function (error) {
        if (!error || error.name !== 'AbortError') {
          console.debug('WebOTP unavailable:', error);
        }
      })
      .finally(function () {
        window.clearTimeout(timeoutId);
      });
  });
})(window, document);
