/**
 * AB-Socks Storefront Client Runtime (assets/js/ab-client.js)
 * Standalone Vanilla JS framework for customer storefront pages (zero admin leakage).
 * Exposes window.ABClient namespace with formatters, EventBus, API client, toasts,
 * clipboard helper, and WeakSet-guarded declarative behavior registry.
 */

(function (window, document) {
  'use strict';

  const ABClient = window.ABClient || {};

  // Breakpoints mirrored in assets/css/client-tokens.css (verified by tools/verify.php)
  ABClient.bp = { sm: 640, md: 860, lg: 1180 };

  // =========================================================================
  // 1. Formatters (ABClient.fmt)
  // =========================================================================
  ABClient.fmt = {
    toFaDigits: function (str) {
      if (str === null || str === undefined) return '';
      const fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
      return String(str).replace(/[0-9]/g, function (d) {
        return fa[Number(d)];
      });
    },

    price: function (amount, unit) {
      const label = unit === undefined ? 'تومان' : unit;
      if (amount === null || amount === undefined || amount === '' || isNaN(Number(amount))) {
        return '—';
      }
      const formatted = Number(amount).toLocaleString('en-US');
      const faFormatted = ABClient.fmt.toFaDigits(formatted);
      return label ? faFormatted + ' ' + label : faFormatted;
    },

    bytes: function (bytes) {
      if (!bytes || isNaN(bytes) || bytes <= 0) return '۰ بایت';
      const k = 1024;
      const sizes = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      const val = (bytes / Math.pow(k, i)).toFixed(i === 0 ? 0 : 1);
      return ABClient.fmt.toFaDigits(val) + ' ' + sizes[i];
    },

    esc: function (str) {
      if (str === null || str === undefined) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    },

    normalize: function (str) {
      if (!str) return '';
      return String(str)
        .toLowerCase()
        .trim()
        .replace(/[\u064B-\u065F\u0670]/g, '')
        .replace(/ي/g, 'ی')
        .replace(/ك/g, 'ک')
        .replace(/ة/g, 'ه')
        .replace(/[۰-۹]/g, function (d) {
          return String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
        })
        .replace(/[\s\-_]+/g, ' ');
    }
  };

  // =========================================================================
  // 2. Pub/Sub EventBus (ABClient.events)
  // =========================================================================
  const eventListeners = new Map();

  ABClient.events = {
    on: function (eventName, handler) {
      if (typeof handler !== 'function') return function () {};
      if (!eventListeners.has(eventName)) {
        eventListeners.set(eventName, new Set());
      }
      eventListeners.get(eventName).add(handler);
      return function () {
        ABClient.events.off(eventName, handler);
      };
    },

    off: function (eventName, handler) {
      const set = eventListeners.get(eventName);
      if (set) {
        set.delete(handler);
      }
    },

    emit: function (eventName, detail) {
      const set = eventListeners.get(eventName);
      if (!set) return;
      set.forEach(function (fn) {
        try {
          fn(detail);
        } catch (err) {
          if (window.console && console.error) {
            console.error('[ABClient.events]', eventName, err);
          }
        }
      });
    }
  };

  // =========================================================================
  // 3. Accessible Toast Notifications (ABClient.toast)
  // =========================================================================
  ABClient.toast = {
    show: function (message, type, duration) {
      if (!message) return;
      const toastType = type || 'info';
      const ttl = typeof duration === 'number' ? duration : 3500;

      let container = document.getElementById('abClientToastContainer');
      if (!container) {
        container = document.createElement('div');
        container.id = 'abClientToastContainer';
        container.className = 'c-toast-container';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'false');
        document.body.appendChild(container);
      }

      const item = document.createElement('div');
      item.className = 'c-toast c-toast--' + toastType;
      item.setAttribute('role', 'status');
      item.textContent = String(message);
      container.appendChild(item);

      const dismiss = function () {
        if (!item.parentNode) return;
        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reducedMotion) {
          item.remove();
          return;
        }
        item.classList.add('is-hiding');
        window.setTimeout(function () {
          if (item.parentNode) item.remove();
        }, 180);
      };

      item.addEventListener('click', dismiss);
      if (ttl > 0) {
        window.setTimeout(dismiss, ttl);
      }
    }
  };

  // =========================================================================
  // 4. CSRF-Aware API Client (ABClient.api)
  // =========================================================================
  ABClient.api = {
    csrfToken: function () {
      const meta = document.querySelector('meta[name="csrf-token"]');
      if (meta && meta.content) return meta.content;
      const input = document.querySelector('input[name="csrf_token"]');
      return input ? input.value : '';
    },

    get: function (url, opts) {
      const options = opts || {};
      return fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        signal: options.signal,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      }).then(function (res) {
        return res.json();
      });
    },

    post: function (url, payload, opts) {
      const options = opts || {};
      const token = ABClient.api.csrfToken();
      let body;

      if (payload instanceof FormData) {
        body = payload;
        if (token && !body.has('csrf_token')) {
          body.append('csrf_token', token);
        }
      } else if (payload instanceof URLSearchParams) {
        body = payload;
        if (token && !body.has('csrf_token')) {
          body.append('csrf_token', token);
        }
      } else {
        body = new URLSearchParams();
        if (payload && typeof payload === 'object') {
          Object.keys(payload).forEach(function (key) {
            if (payload[key] !== undefined && payload[key] !== null) {
              body.append(key, String(payload[key]));
            }
          });
        }
        if (token && !body.has('csrf_token')) {
          body.append('csrf_token', token);
        }
      }

      return fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        signal: options.signal,
        body: body,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      }).then(function (res) {
        return res.text().then(function (raw) {
          try {
            return JSON.parse(raw);
          } catch (e) {
            return {
              ok: false,
              message: res.ok ? 'پاسخ نامعتبر از سرور دریافت شد.' : 'خطا در ارتباط با سرور.'
            };
          }
        });
      });
    }
  };

  // =========================================================================
  // 5. Clipboard Utility (ABClient.clipboard)
  // =========================================================================
  ABClient.clipboard = {
    copy: function (text) {
      const value = text === null || text === undefined ? '' : String(text);
      if (!value) return Promise.resolve(false);

      if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
        return navigator.clipboard.writeText(value).then(function () {
          return true;
        }).catch(function () {
          return legacyCopy(value);
        });
      }
      return Promise.resolve(legacyCopy(value));
    }
  };

  const legacyCopy = function (value) {
    try {
      const textarea = document.createElement('textarea');
      textarea.value = value;
      textarea.setAttribute('readonly', '');
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      document.body.appendChild(textarea);
      textarea.select();
      const copied = document.execCommand('copy');
      textarea.remove();
      return Boolean(copied);
    } catch (e) {
      return false;
    }
  };

  // =========================================================================
  // 6. Declarative Behavior Registry (ABClient.behavior)
  // =========================================================================
  const behaviorRegistry = [];

  ABClient.behavior = {
    register: function (name, selector, setupFn) {
      if (!name || !selector || typeof setupFn !== 'function') return;
      const entry = {
        name: name,
        selector: selector,
        setup: setupFn,
        mounted: new WeakSet()
      };
      behaviorRegistry.push(entry);

      if (document.readyState !== 'loading') {
        mountSingleEntry(entry, document);
      }
    },

    mount: function (root) {
      const scope = root && typeof root.querySelectorAll === 'function' ? root : document;
      behaviorRegistry.forEach(function (entry) {
        mountSingleEntry(entry, scope);
      });
    }
  };

  const mountSingleEntry = function (entry, scope) {
    if (scope !== document && typeof scope.matches === 'function' && scope.matches(entry.selector)) {
      if (!entry.mounted.has(scope)) {
        entry.mounted.add(scope);
        entry.setup(scope);
      }
    }
    scope.querySelectorAll(entry.selector).forEach(function (el) {
      if (entry.mounted.has(el)) return;
      entry.mounted.add(el);
      entry.setup(el);
    });
  };

  // =========================================================================
  // 7. Global Declarative Storefront Behaviors
  // =========================================================================

  // Sync cart badges globally whenever cart:updated fires
  ABClient.events.on('cart:updated', function (detail) {
    const count = detail && typeof detail.cartCount !== 'undefined' ? Number(detail.cartCount) : null;
    if (count === null || isNaN(count)) return;

    const headerCounter = document.getElementById('cartCount');
    if (headerCounter) {
      headerCounter.textContent = String(count);
    }

    const dockBadge = document.getElementById('dockCartBadge');
    if (dockBadge) {
      dockBadge.textContent = ABClient.fmt.toFaDigits(count);
      dockBadge.hidden = count <= 0;
      dockBadge.classList.toggle('has-items', count > 0);
    }
  });

  // Behavior 1: Mobile Nav Drawer
  ABClient.behavior.register('nav-drawer', '[data-ab-nav-toggle]', function (toggleBtn) {
    const targetSelector = toggleBtn.getAttribute('data-ab-nav-toggle') || '#mainNav';
    const navEl = document.querySelector(targetSelector) || document.getElementById('mainNav');
    if (!navEl) return;

    const setOpen = function (open) {
      navEl.classList.toggle('open', open);
      toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    toggleBtn.addEventListener('click', function () {
      setOpen(!navEl.classList.contains('open'));
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && navEl.classList.contains('open')) {
        setOpen(false);
        toggleBtn.focus();
      }
    });
  });

  // Behavior 2: Live Header Search Panel & Autocomplete
  ABClient.behavior.register('live-search', '[data-ab-live-search]', function (formEl) {
    const panelEl = formEl.closest('#searchBarPanel') || document.getElementById('searchBarPanel');
    const toggleBtn = document.querySelector('[data-ab-search-toggle]') || document.getElementById('searchToggle');
    const inputEl = formEl.querySelector('#searchBarInput, input[type="search"]');
    const spinnerEl = formEl.querySelector('#searchSpinner');
    const clearBtn = formEl.querySelector('#searchClearBtn');
    const suggestionsEl = formEl.querySelector('#searchSuggestions');
    if (!inputEl || !suggestionsEl) return;

    const liveEnabled = formEl.getAttribute('data-live-enabled') !== '0';
    const minChars = parseInt(formEl.getAttribute('data-min-chars') || '2', 10);
    let debounceTimer = null;
    let abortCtrl = null;
    const cache = {};
    let activeIdx = -1;

    const hideSuggestions = function () {
      suggestionsEl.hidden = true;
      inputEl.setAttribute('aria-expanded', 'false');
      activeIdx = -1;
    };

    const togglePanel = function (forceState, scrollTop) {
      if (!panelEl) return;
      const next = typeof forceState === 'boolean' ? forceState : !panelEl.classList.contains('open');
      panelEl.classList.toggle('open', next);
      if (toggleBtn) toggleBtn.setAttribute('aria-expanded', next ? 'true' : 'false');
      if (next) {
        if (scrollTop) window.scrollTo({ top: 0, behavior: 'smooth' });
        inputEl.focus();
      } else {
        hideSuggestions();
      }
    };

    if (toggleBtn) {
      toggleBtn.addEventListener('click', function () {
        togglePanel();
      });
    }

    document.querySelectorAll('[data-ab-dock-search]').forEach(function (dockSearchLink) {
      dockSearchLink.addEventListener('click', function (e) {
        if (!panelEl) return;
        e.preventDefault();
        togglePanel(true, true);
      });
    });

    ABClient.events.on('search:toggle', function (opts) {
      togglePanel(opts && opts.open, Boolean(opts && opts.scrollTop));
    });

    const highlight = function (text, query) {
      const raw = String(text === null || text === undefined ? '' : text);
      const qRaw = String(query === null || query === undefined ? '' : query).trim();
      if (!qRaw) return ABClient.fmt.esc(raw);
      const escapedPattern = qRaw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const parts = raw.split(new RegExp('(' + escapedPattern + ')', 'gi'));
      let out = '';
      for (let i = 0; i < parts.length; i++) {
        const part = parts[i];
        if (!part) continue;
        out += (i % 2 === 1) ? ('<mark>' + ABClient.fmt.esc(part) + '</mark>') : ABClient.fmt.esc(part);
      }
      return out;
    };

    const renderList = function (data, query) {
      const esc = ABClient.fmt.esc;
      let html = '';
      const hasCats = Array.isArray(data.categories) && data.categories.length > 0;
      const hasProds = Array.isArray(data.results) && data.results.length > 0;

      if (!hasCats && !hasProds) {
        html = '<div class="search-empty">محصولی مطابق با «<strong>' + esc(query) + '</strong>» یافت نشد.</div>';
      } else {
        if (hasCats) {
          html += '<div class="search-suggest-cats"><span class="search-suggest-cat-label">دسته‌بندی‌ها:</span>';
          data.categories.forEach(function (cat) {
            html += '<a href="' + esc(cat.url) + '" class="search-suggest-cat-pill search-nav-item" role="option">' + esc(cat.name) + '</a>';
          });
          html += '</div>';
        }
        if (hasProds) {
          data.results.forEach(function (prod) {
            const stockBadge = prod.in_stock ? '' : '<span class="search-suggest-stockout">ناموجود</span>';
            const oldPrice = prod.old_price_formatted ? '<div class="search-suggest-oldprice">' + esc(prod.old_price_formatted) + '</div>' : '';
            html += '<a href="' + esc(prod.url) + '" class="search-suggest-item search-nav-item" role="option">';
            html += '<img src="' + esc(prod.image) + '" alt="' + esc(prod.name) + '" class="search-suggest-thumb" loading="lazy">';
            html += '<div class="search-suggest-info">';
            html += '<div class="search-suggest-title">' + highlight(prod.name, query) + '</div>';
            html += '<div class="search-suggest-meta"><span class="search-suggest-cat">' + esc(prod.category_name) + '</span>';
            if (prod.discount_percent > 0) {
              html += '<span class="c-badge c-badge--discount">' + ABClient.fmt.toFaDigits(prod.discount_percent) + '%-</span>';
            }
            html += '</div></div>';
            html += '<div class="search-suggest-pricing"><div class="search-suggest-price">' + esc(prod.price_formatted) + '</div>' + oldPrice + stockBadge + '</div>';
            html += '</a>';
          });
        }
        html += '<a href="/search?q=' + encodeURIComponent(query) + '" class="search-view-all search-nav-item" role="option">مشاهده همه نتایج (' + ABClient.fmt.toFaDigits(data.total) + ' محصول) ←</a>';
      }

      suggestionsEl.innerHTML = html;
      suggestionsEl.hidden = false;
      inputEl.setAttribute('aria-expanded', 'true');
      activeIdx = -1;
    };

    const runQuery = function (val) {
      if (!liveEnabled) return;
      const q = val.trim();
      if (q.length < minChars) {
        if (abortCtrl) abortCtrl.abort();
        if (spinnerEl) spinnerEl.hidden = true;
        hideSuggestions();
        return;
      }

      if (cache[q]) {
        if (spinnerEl) spinnerEl.hidden = true;
        renderList(cache[q], q);
        return;
      }

      if (spinnerEl) spinnerEl.hidden = false;
      if (abortCtrl) abortCtrl.abort();
      abortCtrl = new AbortController();

      ABClient.api.get('/ajax/search_suggest.php?q=' + encodeURIComponent(q), { signal: abortCtrl.signal })
        .then(function (data) {
          if (spinnerEl) spinnerEl.hidden = true;
          if (data && data.ok) {
            cache[q] = data;
            if (inputEl.value.trim() === q) {
              renderList(data, q);
            }
          }
        })
        .catch(function (err) {
          if (!err || err.name !== 'AbortError') {
            if (spinnerEl) spinnerEl.hidden = true;
          }
        });
    };

    inputEl.addEventListener('input', function () {
      const val = inputEl.value;
      if (clearBtn) clearBtn.hidden = val.length === 0;
      window.clearTimeout(debounceTimer);
      debounceTimer = window.setTimeout(function () {
        runQuery(val);
      }, 250);
    });

    inputEl.addEventListener('focus', function () {
      const val = inputEl.value.trim();
      if (val.length >= minChars && suggestionsEl.hidden) {
        runQuery(val);
      }
    });

    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        inputEl.value = '';
        clearBtn.hidden = true;
        hideSuggestions();
        inputEl.focus();
      });
    }

    inputEl.addEventListener('keydown', function (e) {
      const items = suggestionsEl.querySelectorAll('.search-nav-item');
      if (!items.length || suggestionsEl.hidden) return;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIdx = (activeIdx + 1) % items.length;
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIdx = (activeIdx - 1 + items.length) % items.length;
      } else if (e.key === 'Enter' && activeIdx >= 0 && items[activeIdx]) {
        e.preventDefault();
        window.location.href = items[activeIdx].getAttribute('href');
        return;
      } else if (e.key === 'Escape') {
        hideSuggestions();
        return;
      } else {
        return;
      }

      items.forEach(function (el, idx) {
        const active = idx === activeIdx;
        el.classList.toggle('is-active', active);
        if (active) {
          el.setAttribute('aria-selected', 'true');
          el.scrollIntoView({ block: 'nearest' });
        } else {
          el.removeAttribute('aria-selected');
        }
      });
    });

    document.addEventListener('click', function (e) {
      if (!formEl.contains(e.target)) {
        hideSuggestions();
      }
    });
  });

  // Behavior 3: 5-Hub Liquid Glass Dock
  ABClient.behavior.register('liquid-dock', '[data-ab-dock]', function (dockEl) {
    const drop = dockEl.querySelector('.liquid-drop') || document.getElementById('liquidDrop');
    const items = dockEl.querySelectorAll('.dock-item');
    if (!drop || !items.length) return;

    const prefersReducedMotion = Boolean(
      window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    );
    let currentIndex = 0;
    let resizeTimer = null;

    const positionDrop = function (targetItem, animateElastic) {
      const dockRect = dockEl.getBoundingClientRect();
      const targetRect = targetItem.getBoundingClientRect();
      if (!dockRect.width || !targetRect.width) return;

      const offsetLeft = targetRect.left - dockRect.left;
      const targetWidth = targetRect.width;
      const targetIndex = parseInt(targetItem.getAttribute('data-index') || '0', 10);
      const distance = targetIndex - currentIndex;

      if (animateElastic && !prefersReducedMotion && distance !== 0) {
        const stretchFactor = Math.min(1 + Math.abs(distance) * 0.22, 1.6);
        const squeezeY = 1 / Math.sqrt(stretchFactor);
        drop.style.transform = 'translate3d(' + offsetLeft + 'px, 0, 0) scaleX(' + stretchFactor + ') scaleY(' + squeezeY + ')';
        window.setTimeout(function () {
          drop.style.transform = 'translate3d(' + offsetLeft + 'px, 0, 0) scale(1, 1)';
        }, 160);
      } else {
        drop.style.transform = 'translate3d(' + offsetLeft + 'px, 0, 0) scale(1, 1)';
      }

      drop.style.width = targetWidth + 'px';
      currentIndex = targetIndex;
    };

    const syncActiveDrop = function () {
      const activeItem = dockEl.querySelector('.dock-item.active') || items[0];
      if (activeItem) positionDrop(activeItem, false);
    };

    window.setTimeout(syncActiveDrop, 50);
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(syncActiveDrop);
    }

    window.addEventListener('resize', function () {
      window.clearTimeout(resizeTimer);
      resizeTimer = window.setTimeout(syncActiveDrop, 100);
    });
  });

  // Behavior 4: Declarative Clipboard Copy
  ABClient.behavior.register('copy-trigger', '[data-ab-copy]', function (btn) {
    const originalHtml = btn.innerHTML;

    btn.addEventListener('click', function () {
      let text = btn.getAttribute('data-ab-copy') || '';
      if (!text) {
        const targetSel = btn.getAttribute('data-ab-copy-target');
        const targetEl = targetSel ? document.querySelector(targetSel) : null;
        text = targetEl ? targetEl.textContent.trim() : '';
      }
      if (!text) return;

      ABClient.clipboard.copy(text).then(function (ok) {
        if (!ok) {
          ABClient.toast.show('کپی خودکار انجام نشد.', 'error');
          return;
        }
        const feedback = btn.getAttribute('data-ab-copy-feedback') || 'کپی شد ✓';
        btn.classList.add('is-copied');
        btn.textContent = feedback;
        const toastMsg = btn.getAttribute('data-ab-copy-toast');
        if (toastMsg) ABClient.toast.show(toastMsg, 'success');
        window.setTimeout(function () {
          btn.classList.remove('is-copied');
          btn.innerHTML = originalHtml;
        }, 2000);
      });
    });
  });

  // Behavior 5: Quantity Stepper (buttons + manual numeric entry)
  ABClient.behavior.register('qty-stepper', '[data-ab-qty]', function (box) {
    const input = box.querySelector('input');
    const minusBtn = box.querySelector('[data-ab-qty-minus], .qty-minus, .c-qty__btn--minus');
    const plusBtn = box.querySelector('[data-ab-qty-plus], .qty-plus, .c-qty__btn--plus');
    if (!input) return;

    const autoSubmit = box.hasAttribute('data-ab-qty-auto');

    const submitParentForm = function () {
      if (!autoSubmit || !input.form) return;
      if (typeof input.form.requestSubmit === 'function') {
        input.form.requestSubmit();
      } else {
        input.form.submit();
      }
    };

    const clampValue = function () {
      const min = parseInt(input.getAttribute('min') || '1', 10);
      const max = parseInt(input.getAttribute('max') || '99', 10);
      const raw = parseInt(input.value, 10);
      const safe = Number.isNaN(raw) ? min : Math.max(min, Math.min(max, raw));
      input.value = String(safe);
      return safe;
    };

    const updateQty = function (delta) {
      const min = parseInt(input.getAttribute('min') || '1', 10);
      const max = parseInt(input.getAttribute('max') || '99', 10);
      const current = parseInt(input.value, 10);
      const base = Number.isNaN(current) ? min : current;
      const next = Math.max(min, Math.min(max, base + delta));
      if (next === base) return;
      input.value = String(next);
      input.dispatchEvent(new Event('change', { bubbles: true }));
    };

    input.addEventListener('change', function () {
      clampValue();
      submitParentForm();
    });

    if (minusBtn) {
      minusBtn.addEventListener('click', function () {
        updateQty(-1);
      });
    }
    if (plusBtn) {
      plusBtn.addEventListener('click', function () {
        updateQty(1);
      });
    }
  });

  // Behavior 6: Progressive Enhancement Async Form (HTML-over-the-Wire)
  ABClient.behavior.register('async-form', '[data-ab-async-form]', function (formEl) {
    formEl.addEventListener('submit', function (e) {
      e.preventDefault();
      const action = formEl.getAttribute('action') || window.location.href;
      const mode = formEl.getAttribute('data-ab-async-form') || 'default';
      const submitBtns = formEl.querySelectorAll('button[type="submit"], input[type="submit"]');
      const cartRoot = document.querySelector('[data-ab-cart-root]');

      let focusKey = null;
      if (cartRoot && cartRoot.contains(document.activeElement)) {
        const keyInput = formEl.querySelector('input[name="key"]');
        if (keyInput) focusKey = { mode: mode, key: keyInput.value };
      }

      submitBtns.forEach(function (b) { b.disabled = true; });
      if (cartRoot) cartRoot.classList.add('is-busy');

      ABClient.api.post(action, new FormData(formEl))
        .then(function (data) {
          if (cartRoot) cartRoot.classList.remove('is-busy');

          if (data && typeof data.html === 'string' && cartRoot) {
            cartRoot.innerHTML = data.html;
            ABClient.behavior.mount(cartRoot);
            if (focusKey) {
              const targetKeyInput = cartRoot.querySelector(
                'form[data-ab-async-form="' + focusKey.mode + '"] input[name="key"][value="' + focusKey.key.replace(/"/g, '\\"') + '"]'
              );
              const focusForm = targetKeyInput ? targetKeyInput.closest('form') : null;
              const focusTarget = focusForm ? focusForm.querySelector('input[name="qty"], button[type="submit"]') : null;
              if (focusTarget) {
                focusTarget.focus({ preventScroll: true });
              }
            }
          }

          if (data && typeof data.cartCount !== 'undefined') {
            ABClient.events.emit('cart:updated', { cartCount: data.cartCount, source: mode });
          }

          if (data && data.ok) {
            if (data.message) {
              ABClient.toast.show(data.message, 'success');
            } else if (mode === 'cart-add') {
              ABClient.toast.show('محصول به سبد خرید اضافه شد.', 'success');
            }
          } else {
            ABClient.toast.show((data && data.message) || 'عملیات انجام نشد.', 'error');
          }

          ABClient.events.emit('async-form:done', {
            ok: Boolean(data && data.ok),
            mode: mode,
            form: formEl,
            data: data
          });
        })
        .catch(function () {
          if (cartRoot) cartRoot.classList.remove('is-busy');
          ABClient.toast.show('ارتباط با سرور برقرار نشد.', 'error');
          ABClient.events.emit('async-form:done', {
            ok: false,
            mode: mode,
            form: formEl,
            data: null
          });
        })
        .finally(function () {
          submitBtns.forEach(function (b) { b.disabled = false; });
        });
    });
  });

  // Behavior 7: RTL-Aware Product Carousels
  ABClient.behavior.register('carousel', '[data-ab-carousel], .carousel-wrap', function (wrap) {
    const track = wrap.querySelector('.carousel-track');
    if (!track) return;

    const isRtl = window.getComputedStyle(document.documentElement).direction === 'rtl';
    const sign = isRtl ? -1 : 1;
    const prevBtn = wrap.querySelector('.carousel-nav.prev');
    const nextBtn = wrap.querySelector('.carousel-nav.next');

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

  // Behavior 8: Auto-submit Form Controls on Change
  ABClient.behavior.register('auto-submit', '[data-ab-auto-submit], .cart-qty-input', function (control) {
    control.addEventListener('change', function () {
      const form = control.form || control.closest('form');
      if (!form) return;
      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
      } else {
        form.submit();
      }
    });
  });

  window.ABClient = ABClient;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      ABClient.behavior.mount(document);
    });
  } else {
    ABClient.behavior.mount(document);
  }
})(window, document);
