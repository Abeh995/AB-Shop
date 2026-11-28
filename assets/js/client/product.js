/**
 * Storefront Product Detail Module (assets/js/client/product.js)
 * Declarative variant selector, gallery switcher, and add-to-cart feedback.
 */
(function (window, document) {
  'use strict';

  const ABClient = window.ABClient;
  if (!ABClient || !ABClient.behavior) return;

  // Behavior: Variant size/color radio selector
  ABClient.behavior.register('variant-selector', '[data-ab-variant-group], .variant-options', function (groupEl) {
    const selectedLabelEl = document.getElementById('selectedVariantLabel');
    const priceCurrentEl = document.getElementById('productPriceCurrent');
    const priceOldEl = document.getElementById('productPriceOld');
    const badgeDiscountEl = document.getElementById('productBadgeDiscount');
    const stockInfoEl = document.getElementById('productStockInfo');
    const qtyInputEl = document.getElementById('productQtyInput');
    const addBtnEl = document.getElementById('addToCartBtn');

    groupEl.querySelectorAll('.variant-chip input[type="radio"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        groupEl.querySelectorAll('.variant-chip').forEach(function (chip) {
          chip.classList.remove('selected');
        });
        if (!radio.checked) return;

        const parentChip = radio.closest('.variant-chip');
        if (parentChip) parentChip.classList.add('selected');

        const labelText = radio.getAttribute('data-label') || '';
        if (selectedLabelEl) {
          selectedLabelEl.textContent = labelText;
        }

        const formattedPrice = radio.getAttribute('data-price-formatted');
        if (priceCurrentEl && formattedPrice) {
          priceCurrentEl.textContent = formattedPrice;
        }

        const oldPriceFormatted = radio.getAttribute('data-old-price-formatted') || '';
        const discountPct = parseInt(radio.getAttribute('data-discount-percent') || '0', 10);
        if (priceOldEl) {
          priceOldEl.textContent = oldPriceFormatted;
          priceOldEl.hidden = !oldPriceFormatted || discountPct <= 0;
        }
        if (badgeDiscountEl) {
          badgeDiscountEl.textContent = discountPct > 0 ? (ABClient.fmt.toFaDigits(discountPct) + '%-') : '';
          badgeDiscountEl.hidden = discountPct <= 0;
        }

        const stock = parseInt(radio.getAttribute('data-stock') || '0', 10);
        if (stockInfoEl) {
          if (stock <= 0) {
            stockInfoEl.innerHTML = '<span class="stock-out">این گزینه در حال حاضر ناموجود است</span>';
          } else if (stock <= 5) {
            stockInfoEl.innerHTML = '<span class="stock-low">فقط ' + ABClient.fmt.toFaDigits(stock) + ' عدد باقی مانده</span>';
          } else {
            stockInfoEl.innerHTML = '<span class="stock-ok">موجود در انبار</span>';
          }
        }

        if (addBtnEl) {
          if (stock <= 0) {
            addBtnEl.disabled = true;
            addBtnEl.textContent = 'این گزینه ناموجود است';
          } else {
            addBtnEl.disabled = false;
            addBtnEl.textContent = 'افزودن به سبد خرید';
          }
        }

        if (qtyInputEl) {
          const newMax = Math.min(Math.max(1, stock), 20);
          qtyInputEl.setAttribute('max', String(newMax));
          const currentQty = parseInt(qtyInputEl.value, 10) || 1;
          if (currentQty > newMax) {
            qtyInputEl.value = String(newMax);
          }
        }

        ABClient.events.emit('variant:changed', {
          variantId: radio.value,
          stock: stock,
          priceFormatted: formattedPrice || '',
          label: labelText
        });
      });
    });
  });

  // Behavior: Product image thumbnail gallery
  ABClient.behavior.register('product-gallery', '[data-ab-gallery], .product-gallery', function (galleryEl) {
    const mainImg = galleryEl.querySelector('#mainProductImage');
    const thumbs = galleryEl.querySelectorAll('.gallery-thumbs [data-full]');
    if (!mainImg || !thumbs.length) return;

    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        const fullSrc = thumb.getAttribute('data-full');
        if (fullSrc) mainImg.src = fullSrc;
        thumbs.forEach(function (t) { t.classList.remove('active'); });
        thumb.classList.add('active');
      });
    });
  });

  // Guarded module-level feedback listener prevents subscription accumulation across mounts
  const addFormStates = new WeakMap();
  ABClient.events.on('async-form:done', function (detail) {
    if (!detail || !detail.form) return;
    const state = addFormStates.get(detail.form);
    if (!state) return;

    window.clearTimeout(state.restoreTimer);
    if (detail.ok) {
      state.btn.textContent = 'به سبد اضافه شد ✓';
      state.restoreTimer = window.setTimeout(function () {
        state.btn.textContent = state.originalText;
      }, 1500);
    } else {
      state.btn.textContent = state.originalText;
    }
  });

  // Button label state feedback for product Add-to-Cart async form
  ABClient.behavior.register('product-add-feedback', '#addToCartForm[data-ab-async-form]', function (formEl) {
    const btn = formEl.querySelector('button[type="submit"]');
    if (!btn) return;

    const state = {
      btn: btn,
      originalText: btn.textContent,
      restoreTimer: null
    };
    addFormStates.set(formEl, state);

    formEl.addEventListener('submit', function () {
      window.clearTimeout(state.restoreTimer);
      state.originalText = btn.textContent;
      btn.textContent = 'در حال افزودن...';
    });
  });
})(window, document);
