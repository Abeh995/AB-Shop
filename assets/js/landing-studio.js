/**
 * Landing Studio — client engine.
 *
 * Renders the whole visual editor from the schema emitted in #lbStudioBoot
 * (app/services/LandingSchemaService.php), keeps a working copy of the landing
 * config, autosaves it as a draft, publishes it on demand and mirrors every
 * change into the live iframe preview through the AB_LANDING_CONFIG_UPDATE
 * postMessage bridge understood by the React storefront.
 *
 * No framework, no build step: plain ES2019, event delegation and one store.
 */
(function () {
  'use strict';

  var bootEl = document.getElementById('lbStudioBoot');
  var root = document.getElementById('lbStudio');
  if (!bootEl || !root) {
    return;
  }

  var BOOT = JSON.parse(bootEl.textContent);
  var STORE_KEY = 'ab_landing_studio_state';

  /* ---------------------------------------------------------------- utils */

  function clone(value) {
    if (typeof structuredClone === 'function') {
      try {
        return structuredClone(value);
      } catch (e) {
        /* fall through to JSON clone */
      }
    }
    return JSON.parse(JSON.stringify(value));
  }

  function tokens(path) {
    return String(path).replace(/\[(\d+)\]/g, '.$1').split('.');
  }

  function getPath(obj, path) {
    var parts = tokens(path);
    var node = obj;
    for (var i = 0; i < parts.length; i += 1) {
      if (node === null || typeof node !== 'object') {
        return undefined;
      }
      node = node[parts[i]];
    }
    return node;
  }

  function setPath(obj, path, value) {
    var parts = tokens(path);
    var node = obj;
    for (var i = 0; i < parts.length - 1; i += 1) {
      if (node[parts[i]] === undefined || node[parts[i]] === null) {
        node[parts[i]] = /^\d+$/.test(parts[i + 1]) ? [] : {};
      }
      node = node[parts[i]];
    }
    node[parts[parts.length - 1]] = value;
  }

  function flatten(obj, prefix, out) {
    out = out || {};
    prefix = prefix || '';
    Object.keys(obj || {}).forEach(function (key) {
      var path = prefix ? prefix + '.' + key : key;
      var value = obj[key];
      if (value && typeof value === 'object') {
        flatten(value, path, out);
      } else {
        out[path] = typeof value === 'boolean' ? (value ? '1' : '0') : String(value);
      }
    });
    return out;
  }

  function pendingPaths(a, b) {
    var flatA = flatten(a);
    var flatB = flatten(b);
    var changed = [];
    Object.keys(flatA).forEach(function (path) {
      if (flatA[path] !== flatB[path]) {
        changed.push(path);
      }
    });
    Object.keys(flatB).forEach(function (path) {
      if (!(path in flatA)) {
        changed.push(path);
      }
    });
    return changed;
  }

  function el(tag, attrs, children) {
    var node = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (key) {
      var value = attrs[key];
      if (value === null || value === undefined || value === false) {
        return;
      }
      if (key === 'class') {
        node.className = value;
      } else if (key === 'text') {
        node.textContent = value;
      } else if (key === 'html') {
        node.innerHTML = value;
      } else if (key === 'dataset') {
        Object.keys(value).forEach(function (dataKey) {
          node.dataset[dataKey] = value[dataKey];
        });
      } else if (key.indexOf('on') === 0 && typeof value === 'function') {
        node.addEventListener(key.slice(2).toLowerCase(), value);
      } else if (value === true) {
        node.setAttribute(key, '');
      } else {
        node.setAttribute(key, value);
      }
    });
    (Array.isArray(children) ? children : children ? [children] : []).forEach(function (child) {
      if (child === null || child === undefined) {
        return;
      }
      node.appendChild(typeof child === 'string' ? document.createTextNode(child) : child);
    });
    return node;
  }

  /* --------------------------------------------------------------- icons  */

  var SVG_OPEN = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">';
  var ICONS = {
    feather: '<path d="M20.2 4.3a5 5 0 0 0-7 0L4 13.5V20h6.5l9.2-9.2a5 5 0 0 0 .5-6.5Z"/><path d="M16 8 4 20"/><path d="M17.5 15H9"/>',
    palette: '<circle cx="12" cy="12" r="9"/><circle cx="9" cy="9" r="1"/><circle cx="15" cy="9" r="1"/><circle cx="9" cy="15" r="1"/><circle cx="15" cy="15" r="1"/>',
    truck: '<path d="M3 6h11v9H3z"/><path d="M14 9h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/>',
    leaf: '<path d="M4 20c0-8 5-14 16-15 1 11-5 16-13 16H4Z"/><path d="M4 20c3-5 7-8 12-10"/>',
    foot: '<path d="M7 20c-1.7 0-2.5-1.4-2.5-3 0-2 .8-3.4 1.6-5.2C7 9.7 7 8 7.3 6.4 7.7 4 9 2.5 10.7 2.5c1.8 0 2.7 1.6 2.7 3.6 0 2.4-1 4-1.4 5.7-.3 1.4-.2 2.6.2 4.1.3 1.3.2 2.4-.6 3.1-.6.6-1.5 1-2.6 1Z"/><circle cx="16.5" cy="18" r="1.6"/><circle cx="19.5" cy="14.5" r="1.2"/>',
    shield: '<path d="M12 3 5 6v6c0 4.4 3 7.4 7 9 4-1.6 7-4.6 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
    heart: '<path d="M12 20s-7-4.4-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 4.6-7 9-7 9Z"/>',
    sparkles: '<path d="m12 3 1.9 5.6L19.5 10l-5.6 1.9L12 17.5l-1.9-5.6L4.5 10l5.6-1.4L12 3Z"/><path d="M18.5 17.5 19 19l1.5.5-1.5.5-.5 1.5-.5-1.5L16.5 19l1.5-.5Z"/>',
    zap: '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>',
    award: '<circle cx="12" cy="9" r="5"/><path d="m8.5 13.5-1.5 7 5-2.5 5 2.5-1.5-7"/>',
    package: '<path d="M12 3 3 7.5v9L12 21l9-4.5v-9L12 3Z"/><path d="M3 7.5 12 12l9-4.5"/><path d="M12 12v9"/>',
    refresh: '<path d="M20 12a8 8 0 1 1-2.6-5.9"/><path d="M20 4v4h-4"/>',
    gift: '<path d="M4 10h16v10H4z"/><path d="M2 6h20v4H2z"/><path d="M12 6v14"/><path d="M12 6C9 6 7 5 7 3.5S8.5 2 9.6 3 12 6 12 6Zm0 0c3 0 5-1 5-2.5S15.5 2 14.4 3 12 6 12 6Z"/>',
    badge: '<path d="m12 3 2.2 1.6 2.7-.2 1 2.5 2.3 1.4-.6 2.6.6 2.6-2.3 1.4-1 2.5-2.7-.2L12 21l-2.2-1.6-2.7.2-1-2.5L3.8 15l.6-2.6-.6-2.6 2.3-1.4 1-2.5 2.7.2L12 3Z"/><path d="m9.5 12 1.8 1.8L15 10"/>',
    card: '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19"/>',
    bank: '<path d="M3 10 12 4l9 6"/><path d="M5 10v8M10 10v8M14 10v8M19 10v8"/><path d="M3 20h18"/>',
    bag: '<path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
    scissors: '<circle cx="6" cy="6" r="2.4"/><circle cx="6" cy="18" r="2.4"/><path d="M8 8l12 10M20 6 8 16"/>',
    send: '<path d="M21 3 3 10.5l7 2.5 2.5 7L21 3Z"/><path d="M21 3 10 13"/>',
    check: '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.2 2.4 2.4 4.6-4.9"/>',
    Sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
    Dumbbell: '<path d="M4 9v6M7 7v10M17 7v10M20 9v6M7 12h10"/>',
    Moon: '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5Z"/>',
    Sparkles: '<path d="m12 3 1.9 5.6L19.5 10l-5.6 1.9L12 17.5l-1.9-5.6L4.5 10l5.6-1.4L12 3Z"/>',
    Palette: '<circle cx="12" cy="12" r="9"/><circle cx="9" cy="9" r="1"/><circle cx="15" cy="9" r="1"/><circle cx="9" cy="15" r="1"/><circle cx="15" cy="15" r="1"/>',
    Shuffle: '<path d="M17 4h4v4"/><path d="M21 4 3 20"/><path d="M17 20h4v-4"/><path d="M3 4l6 7"/>',
    Footprints: '<path d="M7 20c-1.7 0-2.5-1.4-2.5-3 0-2 .8-3.4 1.6-5.2C7 9.7 7 8 7.3 6.4 7.7 4 9 2.5 10.7 2.5c1.8 0 2.7 1.6 2.7 3.6 0 2.4-1 4-1.4 5.7-.3 1.4-.2 2.6.2 4.1.3 1.3.2 2.4-.6 3.1-.6.6-1.5 1-2.6 1Z"/><circle cx="16.5" cy="18" r="1.6"/>',
    Zap: '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>',
    megaphone: '<path d="M4 10v4l10 5V5L4 10Z"/><path d="M14 8.5a3.5 3.5 0 0 1 0 7"/><path d="M7 14v5"/>',
    search: '<circle cx="11" cy="11" r="7.5"/><path d="m21 21-4.4-4.4"/>',
    layout: '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M9 9v11"/>',
    grid: '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>',
    camera: '<rect x="3" y="6.5" width="18" height="13" rx="2.5"/><circle cx="12" cy="13" r="3.5"/><path d="M8 6.5 9.5 4h5L16 6.5"/>',
    book: '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5V5.5Z"/><path d="M4 17.5h16"/>',
    instagram: '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1"/>',
    ruler: '<path d="m3 15 6-6 6 6-6 6-6-6Z"/><path d="m9 3 12 12-3 3L6 6l3-3Z"/>',
    cpu: '<rect x="7" y="7" width="10" height="10" rx="2"/><path d="M10 3v4M14 3v4M10 17v4M14 17v4M3 10h4M3 14h4M17 10h4M17 14h4"/>',
    help: '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.7.4-1 .9-1 1.7"/><circle cx="12" cy="17" r=".8"/>',
    dice: '<rect x="3.5" y="3.5" width="17" height="17" rx="3.5"/><circle cx="8.5" cy="8.5" r="1"/><circle cx="15.5" cy="8.5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="8.5" cy="15.5" r="1"/><circle cx="15.5" cy="15.5" r="1"/>',
    wand: '<path d="M15 4V2M15 10V8M19 6h2M9 6H7M18.4 3.6 17 5M18.4 8.4 17 7M11.6 3.6 13 5M4 20 13.5 10.5"/><path d="m13 21 8-8"/>',
    cart: '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 11h11L21 6H6"/>',
    list: '<path d="M8 6h13M8 12h13M8 18h13"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/>',
    eye: '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/>',
    eyeOff: '<path d="M3 3l18 18"/><path d="M10.6 6.1A9.7 9.7 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-3.2 3.7"/><path d="M6.3 8.2A15.7 15.7 0 0 0 2.5 12S6 18 12 18c1 0 2-.2 2.8-.5"/>',
    grip: '<circle cx="9" cy="6" r="1"/><circle cx="15" cy="6" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="9" cy="18" r="1"/><circle cx="15" cy="18" r="1"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    trash: '<path d="M4 7h16"/><path d="M9 7V5h6v2"/><path d="M6 7l1 13h10l1-13"/>',
    copy: '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M15 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h3"/>',
    chevron: '<path d="m6 9 6 6 6-6"/>',
    upload: '<path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/>',
    link: '<path d="M9.5 14.5 14.5 9.5"/><path d="M8 12 6 14a3.5 3.5 0 0 0 5 5l2-2"/><path d="M16 12l2-2a3.5 3.5 0 0 0-5-5l-2 2"/>'
  };

  function icon(name, size) {
    var body = ICONS[name] || ICONS.sparkles;
    return (
      '<svg viewBox="0 0 24 24" width="' +
      (size || 16) +
      '" height="' +
      (size || 16) +
      '" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">' +
      body +
      '</svg>'
    );
  }

  /* --------------------------------------------------------------- state  */

  var state = {
    config: clone(BOOT.config),
    baseline: clone(BOOT.config),
    meta: BOOT.meta || {},
    revisions: BOOT.revisions || [],
    sectionId: (BOOT.sections[0] || {}).id || 'hero',
    pending: [],
    undoStack: [],
    redoStack: [],
    undoLockUntil: 0,
    saveTimer: null,
    saving: false,
    previewReady: false,
    device: 'desktop',
    devicePicked: false,
    selectMode: false,
    focusMode: false,
    collapsedGroups: {},
    changedOnly: false,
    openRepeaters: {},
    lastSavedAt: null
  };

  var sectionsById = {};
  BOOT.sections.forEach(function (section) {
    sectionsById[section.id] = section;
  });

  var orderableIds = (BOOT.defaults.sectionOrder || []).map(function (row) {
    return row.id;
  });

  var dom = {
    railList: document.getElementById('lbSectionList'),
    searchInput: document.getElementById('lbSearchInput'),
    searchResults: document.getElementById('lbSearchResults'),
    panelHead: document.getElementById('lbPanelHead'),
    panelBody: document.getElementById('lbPanelBody'),
    previewStage: document.getElementById('lbPreviewStage'),
    previewFrame: document.getElementById('lbPreviewFrame'),
    previewFrameWrap: document.getElementById('lbPreviewFrameWrap'),
    previewLoading: document.getElementById('lbPreviewLoading'),
    previewUrl: document.getElementById('lbPreviewUrl'),
    statusPill: document.getElementById('lbStatusPill'),
    statusText: document.getElementById('lbStatusText'),
    statusMeta: document.getElementById('lbStatusMeta'),
    publishBtn: document.getElementById('lbPublishBtn'),
    publishCount: document.getElementById('lbPublishCount'),
    undoBtn: document.getElementById('lbUndoBtn'),
    redoBtn: document.getElementById('lbRedoBtn'),
    selectBtn: document.getElementById('lbSelectModeBtn'),
    focusBtn: document.getElementById('lbFocusBtn'),
    historyBtn: document.getElementById('lbHistoryBtn'),
    moreBtn: document.getElementById('lbMoreBtn'),
    moreMenu: document.getElementById('lbMoreMenu'),
    modalHost: document.getElementById('lbModalHost'),
    modalTitle: document.getElementById('lbModalTitle'),
    modalBody: document.getElementById('lbModalBody'),
    modalFoot: document.getElementById('lbModalFoot'),
    toasts: document.getElementById('lbToasts'),
    fileInput: document.getElementById('lbFileInput'),
    jsonInput: document.getElementById('lbJsonInput'),
    versionTag: document.getElementById('lbVersionTag'),
    workspace: document.getElementById('lbWorkspace'),
    splitRail: document.getElementById('lbSplitRail'),
    splitPanel: document.getElementById('lbSplitPanel')
  };

  /* --------------------------------------------------------------- toast  */

  function toast(message, tone) {
    if (!message) {
      return;
    }
    var node = el('div', { class: 'lb-toast', dataset: { tone: tone || 'info' }, text: message });
    dom.toasts.appendChild(node);
    window.setTimeout(function () {
      node.style.opacity = '0';
      node.style.transform = 'translateY(.4rem)';
      window.setTimeout(function () {
        node.remove();
      }, 200);
    }, 3200);
  }

  /* ---------------------------------------------------------- server I/O  */

  function post(action, payload, options) {
    options = options || {};
    var body = payload instanceof FormData ? payload : new FormData();
    if (!(payload instanceof FormData)) {
      Object.keys(payload || {}).forEach(function (key) {
        body.append(key, typeof payload[key] === 'string' ? payload[key] : JSON.stringify(payload[key]));
      });
    }
    body.append('action', action);
    body.append('csrf_token', BOOT.csrf);

    return fetch(BOOT.endpoints.ajax, {
      method: 'POST',
      body: body,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (response) {
        return response.json().catch(function () {
          throw new Error('پاسخ سرور قابل خواندن نبود (کد ' + response.status + ').');
        });
      })
      .then(function (json) {
        if (!json.ok) {
          throw new Error(json.error || 'عملیات ناموفق بود.');
        }
        if (json.meta) {
          state.meta = json.meta;
        }
        if (json.revisions) {
          state.revisions = json.revisions;
        }
        return json;
      });
  }

  function recomputePending() {
    state.pending = pendingPaths(state.config, state.baseline);
  }

  /* ------------------------------------------------------------ autosave  */

  function scheduleSave() {
    window.clearTimeout(state.saveTimer);
    state.saveTimer = window.setTimeout(saveDraft, 1000);
  }

  function saveDraft(silent) {
    window.clearTimeout(state.saveTimer);
    if (state.saving) {
      return Promise.resolve();
    }
    state.saving = true;
    setBusy(true);

    return post('save_draft', { config: JSON.stringify(state.config) })
      .then(function (json) {
        state.saving = false;
        setBusy(false);
        state.baseline = clone(json.config || state.config);
        state.lastSavedAt = new Date();
        recomputePending();
        renderStatus();
        renderRail();
        if (!silent) {
          toast('پیش‌نویس ذخیره شد.', 'success');
        }
      })
      .catch(function (error) {
        state.saving = false;
        setBusy(false);
        renderStatus();
        toast(error.message, 'error');
      });
  }

  /* --------------------------------------------------------------- commit */

  function commit(path, value, options) {
    options = options || {};
    var previous = getPath(state.config, path);
    if (JSON.stringify(previous) === JSON.stringify(value)) {
      return;
    }

    var now = Date.now();
    if (options.snapshot !== false && now - state.undoLockUntil > 600) {
      state.undoStack.push(JSON.stringify(state.config));
      if (state.undoStack.length > 60) {
        state.undoStack.shift();
      }
      state.redoStack = [];
      state.undoLockUntil = now;
    }

    setPath(state.config, path, value);
    recomputePending();
    markFieldDirty(path);
    pushPreview();
    renderStatus();
    scheduleSave();

    if (options.rerender) {
      renderPanel();
      renderRail();
    }
  }

  function markFieldDirty(path) {
    var wrapper = dom.panelBody.querySelector('[data-field-path="' + cssEscape(path) + '"]');
    if (!wrapper) {
      return;
    }
    var isDirty = state.pending.indexOf(path) !== -1;
    var defaultValue = getPath(BOOT.defaults, path);
    var current = getPath(state.config, path);
    wrapper.classList.toggle('is-changed', String(defaultValue) !== String(current) || isDirty);
  }

  function cssEscape(value) {
    return String(value).replace(/"/g, '\\"');
  }

  function undo() {
    if (!state.undoStack.length) {
      return;
    }
    state.redoStack.push(JSON.stringify(state.config));
    state.config = JSON.parse(state.undoStack.pop());
    afterHistoryMove();
  }

  function redo() {
    if (!state.redoStack.length) {
      return;
    }
    state.undoStack.push(JSON.stringify(state.config));
    state.config = JSON.parse(state.redoStack.pop());
    afterHistoryMove();
  }

  function afterHistoryMove() {
    recomputePending();
    pushPreview();
    renderPanel();
    renderRail();
    renderStatus();
    scheduleSave();
  }

  /* ------------------------------------------------------------ preview   */

  function previewTarget() {
    try {
      return dom.previewFrame.contentWindow;
    } catch (e) {
      return null;
    }
  }

  var previewTimer = null;

  function pushPreview(immediate) {
    window.clearTimeout(previewTimer);
    var send = function () {
      var target = previewTarget();
      if (!target) {
        return;
      }
      target.postMessage({ type: 'AB_LANDING_CONFIG_UPDATE', config: state.config }, '*');
    };
    if (immediate) {
      send();
    } else {
      previewTimer = window.setTimeout(send, 90);
    }
  }

  function scrollPreviewTo(section, fieldPath) {
    var target = previewTarget();
    if (!target) {
      return;
    }
    var preview = section && section.preview ? section.preview : '';
    target.postMessage({ type: 'AB_PREVIEW_SCROLL_TO', sectionId: preview, fieldKey: fieldPath || null }, '*');
  }

  function setSelectMode(enabled) {
    state.selectMode = enabled;
    dom.selectBtn.setAttribute('aria-pressed', enabled ? 'true' : 'false');
    var target = previewTarget();
    if (target) {
      target.postMessage({ type: 'AB_SET_SELECT_MODE', enabled: enabled }, '*');
    }
    toast(enabled ? 'حالت انتخاب فعال شد؛ روی هر بخش پیش‌نمایش کلیک کنید.' : 'حالت انتخاب خاموش شد.');
  }

  var DEVICE_WIDTHS = { desktop: 1440, tablet: 834, mobile: 414 };

  /**
   * Emulate a real viewport width inside the preview pane and scale it down to
   * fit, so the storefront renders its actual desktop/tablet/mobile layout.
   */
  /**
   * Pick the largest device that still reads at ~1:1 in the current pane.
   *
   * A 1440px desktop preview squeezed into a 420px column is unreadable, so the
   * pane width decides — until the editor overrides it with the device switch.
   */
  function autoFitDevice() {
    if (state.devicePicked) {
      return;
    }
    var paneWidth = dom.previewStage.clientWidth - 24;
    var wanted = paneWidth < 430 ? 'mobile' : (paneWidth < 620 ? 'tablet' : 'desktop');
    if (wanted === state.device) {
      return;
    }
    state.device = wanted;
    dom.previewStage.dataset.device = wanted;
    document.querySelectorAll('.lb-device-switch button').forEach(function (btn) {
      btn.classList.toggle('is-active', btn.dataset.device === wanted);
    });
  }

  function applyDeviceScale() {
    autoFitDevice();
    var width = DEVICE_WIDTHS[state.device] || DEVICE_WIDTHS.desktop;
    var padding = 24;
    var stageWidth = Math.max(240, dom.previewStage.clientWidth - padding);
    var stageHeight = Math.max(320, dom.previewStage.clientHeight - padding);
    var scale = Math.min(1, stageWidth / width);

    dom.previewFrameWrap.style.width = width + 'px';
    dom.previewFrameWrap.style.height = Math.max(stageHeight, Math.round(stageHeight / scale)) + 'px';
    dom.previewFrameWrap.style.transform = 'scale(' + scale + ')';
  }

  function loadPreview() {
    var url = BOOT.endpoints.preview + (BOOT.endpoints.preview.indexOf('?') === -1 ? '?lb_preview=1&t=' : '&t=') + Date.now();
    state.previewReady = false;
    dom.previewLoading.hidden = false;
    dom.previewFrame.src = url;
  }

  window.addEventListener('message', function (event) {
    var data = event.data;
    if (!data || typeof data !== 'object') {
      return;
    }
    if (data.type === 'AB_ELEMENT_SELECTED') {
      var schemaSection = findSchemaSectionByPreview(data.section);
      if (schemaSection) {
        selectSection(schemaSection.id);
        if (data.field) {
          focusField(data.field);
        }
      }
      setSelectMode(false);
    }
    if (data.type === 'AB_SET_SELECT_MODE') {
      state.selectMode = Boolean(data.enabled);
      dom.selectBtn.setAttribute('aria-pressed', state.selectMode ? 'true' : 'false');
    }
  });

  function findSchemaSectionByPreview(previewId) {
    if (!previewId) {
      return null;
    }
    for (var i = 0; i < BOOT.sections.length; i += 1) {
      if (BOOT.sections[i].preview === previewId) {
        return BOOT.sections[i];
      }
    }
    return null;
  }

  /* ------------------------------------------------------------- rail    */

  function railGroups() {
    var order = (state.config.sectionOrder || []).map(function (row) {
      return row.id;
    });
    var ordered = [];

    order.forEach(function (id) {
      if (sectionsById[id]) {
        ordered.push(id);
      }
    });

    return [
      {
        title: 'چیدمان صفحه',
        items: ['topStrip', 'header'].filter(function (id) {
          return sectionsById[id];
        })
      },
      { title: 'سکشن‌های اصلی', items: ordered, orderable: true },
      {
        title: 'ابزارها و پنجره‌ها',
        items: ['footer', 'pageOrder', 'luckyWheel', 'styleMaker', 'trackModal', 'cartDrawer', 'visualEffects', 'seo'].filter(function (id) {
          return sectionsById[id];
        })
      }
    ];
  }

  function sectionEnabled(id) {
    var section = sectionsById[id];
    if (!section) {
      return true;
    }
    if (section.toggle === 'enabled') {
      return getPath(state.config, section.path + '.enabled') !== false;
    }
    var isOrderable = orderableIds.indexOf(id) !== -1;
    if (section.toggle === 'order' || isOrderable) {
      var row = (state.config.sectionOrder || []).find(function (item) {
        return item.id === id;
      });
      return row ? row.enabled !== false : true;
    }
    return true;
  }

  function toggleSectionEnabled(id) {
    var section = sectionsById[id];
    if (!section) {
      return;
    }
    if (section.toggle === 'enabled') {
      var current = getPath(state.config, section.path + '.enabled');
      commit(section.path + '.enabled', current === false);
      renderPanel();
      return;
    }
    var rows = clone(state.config.sectionOrder || []);
    var row = rows.find(function (item) {
      return item.id === id;
    });
    if (row) {
      row.enabled = row.enabled === false;
      commit('sectionOrder', rows);
      renderRail();
      if (state.sectionId === id) {
        renderPanel();
      }
    }
  }

  function sectionPendingCount(id) {
    var section = sectionsById[id];
    if (!section) {
      return 0;
    }
    var prefix = section.path;
    return state.pending.filter(function (path) {
      if (id === 'pageOrder') {
        return path.indexOf('sectionOrder') === 0;
      }
      return prefix === '' ? true : path.indexOf(prefix) === 0;
    }).length;
  }

  function renderRail() {
    dom.railList.textContent = '';
    railGroups().forEach(function (group) {
      if (!group.items.length) {
        return;
      }
      dom.railList.appendChild(
        el('div', {
          class: 'lb-rail-group-title',
          text: group.title,
          style: 'font-size:.62rem;font-weight:800;color:var(--text-muted);padding:.5rem .45rem .2rem;letter-spacing:.02em'
        })
      );

      group.items.forEach(function (id) {
        var section = sectionsById[id];
        var active = state.sectionId === id;
        var enabled = sectionEnabled(id);
        var pendingCount = sectionPendingCount(id);
        var canReorder = group.orderable;

        var eye = el('button', {
          type: 'button',
          class: 'lb-rail-eye',
          title: enabled ? 'خاموش کردن این بخش' : 'روشن کردن این بخش',
          'aria-label': 'تغییر نمایش ' + section.title,
          dataset: { on: enabled ? 'true' : 'false' },
          onClick: function (event) {
            event.stopPropagation();
            toggleSectionEnabled(id);
          }
        });
        eye.innerHTML = icon(enabled ? 'eye' : 'eyeOff', 14);

        var tools = el('div', { class: 'lb-rail-tools' }, [
          pendingCount > 0 ? el('span', { class: 'lb-rail-count', text: String(pendingCount) }) : null,
          section.toggle || orderableIds.indexOf(id) !== -1 ? eye : null
        ]);

        var handle = el('span', { class: 'lb-rail-handle' });
        handle.innerHTML = icon('grip', 13);

        var row = el(
          'div',
          {
            class: 'lb-rail-item' + (active ? ' is-active' : '') + (enabled ? '' : ' is-off'),
            role: 'button',
            tabindex: '0',
            title: section.title + (enabled ? '' : ' — پنهان در سایت'),
            draggable: canReorder ? 'true' : 'false',
            dataset: { sectionId: id, orderable: canReorder ? '1' : '0' },
            onClick: function () {
              selectSection(id);
            },
            onKeydown: function (event) {
              if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectSection(id);
              }
            }
          },
          [
            handle,
            (function () {
              var box = el('span', { class: 'lb-rail-icon' });
              box.innerHTML = icon(section.icon, 14);
              return box;
            })(),
            el('span', { class: 'lb-rail-label', text: section.title }),
            tools
          ]
        );

        if (canReorder) {
          bindOrderDrag(row, id, group.items, function (draggedId, targetId) {
            reorderSections(draggedId, targetId);
          });
        }

        dom.railList.appendChild(row);
      });
    });
  }

  function bindOrderDrag(node, id, items, onDrop) {
    node.addEventListener('dragstart', function (event) {
      event.dataTransfer.setData('text/plain', id);
      event.dataTransfer.effectAllowed = 'move';
      node.classList.add('is-dragging');
    });
    node.addEventListener('dragend', function () {
      node.classList.remove('is-dragging');
      Array.prototype.forEach.call(dom.railList.querySelectorAll('.is-drop-target'), function (elNode) {
        elNode.classList.remove('is-drop-target');
      });
    });
    node.addEventListener('dragover', function (event) {
      event.preventDefault();
      node.classList.add('is-drop-target');
    });
    node.addEventListener('dragleave', function () {
      node.classList.remove('is-drop-target');
    });
    node.addEventListener('drop', function (event) {
      event.preventDefault();
      node.classList.remove('is-drop-target');
      var draggedId = event.dataTransfer.getData('text/plain');
      if (draggedId && draggedId !== id && items.indexOf(draggedId) !== -1) {
        onDrop(draggedId, id);
      }
    });
  }

  function reorderSections(draggedId, targetId) {
    var rows = clone(state.config.sectionOrder || []);
    var from = rows.findIndex(function (row) {
      return row.id === draggedId;
    });
    var to = rows.findIndex(function (row) {
      return row.id === targetId;
    });
    if (from === -1 || to === -1) {
      return;
    }
    var moved = rows.splice(from, 1)[0];
    rows.splice(to, 0, moved);
    commit('sectionOrder', rows);
    renderRail();
    renderPanel();
  }

  /* -------------------------------------------------------------- panel   */

  function selectSection(id) {
    if (!sectionsById[id]) {
      return;
    }
    state.sectionId = id;
    Array.prototype.forEach.call(dom.railList.querySelectorAll('.lb-rail-item'), function (node) {
      node.classList.toggle('is-active', node.dataset.sectionId === id);
    });
    renderPanel();
    var section = sectionsById[id];
    scrollPreviewTo(section);
    if (isCompactLayout()) {
      root.dataset.mobileView = 'editor';
      syncMobileSwitch('editor');
    }
  }

  function focusField(path) {
    var wrapper = findFieldWrapper(path);
    if (!wrapper) {
      return;
    }
    wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
    var input = wrapper.querySelector('input, textarea, select');
    if (input) {
      window.setTimeout(function () {
        input.focus({ preventScroll: true });
      }, 260);
    }
    wrapper.style.transition = 'background 200ms ease';
    wrapper.style.background = 'color-mix(in srgb, var(--brand-primary) 10%, transparent)';
    window.setTimeout(function () {
      wrapper.style.background = '';
    }, 1600);
  }

  function findFieldWrapper(path) {
    var exact = dom.panelBody.querySelector('[data-field-path="' + cssEscape(path) + '"]');
    if (exact) {
      return exact;
    }
    // Fall back to the closest ancestor field (repeater item / child paths).
    var parts = String(path).split('.');
    while (parts.length > 1) {
      parts.pop();
      var candidate = dom.panelBody.querySelector('[data-field-path="' + cssEscape(parts.join('.')) + '"]');
      if (candidate) {
        return candidate;
      }
    }
    return null;
  }

  function renderPanel() {
    var section = sectionsById[state.sectionId];
    if (!section) {
      return;
    }

    renderPanelHead(section);
    dom.panelBody.textContent = '';
    dom.panelBody.scrollTop = 0;

    var visibleGroups = 0;

    section.groups.forEach(function (group, index) {
      var groupKey = section.id + ':' + index;
      var isOpen = state.collapsedGroups[groupKey] !== false;

      var groupFields = group.fields.filter(function (field) {
        return !state.changedOnly || fieldIsChanged(field.path);
      });

      if (state.changedOnly && !groupFields.length) {
        return;
      }
      visibleGroups += 1;

      var head = el(
        'button',
        {
          type: 'button',
          class: 'lb-group-head',
          'aria-expanded': isOpen ? 'true' : 'false',
          onClick: function () {
            state.collapsedGroups[groupKey] = !isOpen;
            renderPanel();
          }
        },
        [
          (function () {
            var chev = el('span');
            chev.innerHTML = icon('chevron', 14);
            return chev.firstChild;
          })(),
          el('span', { text: group.title }),
          el('span', { class: 'lb-group-count', text: String(groupFields.length) })
        ]
      );

      var body = el('div', { class: 'lb-group-body' });
      groupFields.forEach(function (field) {
        var rendered = renderField(field, section.path);
        if (rendered) {
          body.appendChild(rendered);
        }
      });

      dom.panelBody.appendChild(
        el('div', { class: 'lb-group', dataset: { open: isOpen ? 'true' : 'false' } }, [head, body])
      );
    });

    refreshEmptyFieldsChip();

    if (state.changedOnly && visibleGroups === 0) {
      dom.panelBody.appendChild(
        el('div', { class: 'lb-panel-empty' }, [
          el('strong', { text: 'این بخش هنوز مقدار پیش‌فرض دارد.' }),
          el('span', { text: 'فیلتر «فقط تغییرات» را خاموش کنید تا همه فیلدها دیده شوند.' })
        ])
      );
    }
  }

  /** Paths rendered empty in the panel while the factory default has text. */
  function emptyFieldPaths() {
    var paths = [];
    Array.prototype.forEach.call(dom.panelBody.querySelectorAll('.lb-field'), function (wrapper) {
      var input = wrapper.querySelector('input.lb-input, textarea.lb-textarea');
      if (!input || String(input.value).trim() !== '') {
        return;
      }
      var path = wrapper.dataset.fieldPath;
      var fallback = getPath(BOOT.defaults, path);
      if (fallback !== undefined && String(fallback).trim() !== '') {
        paths.push(path);
      }
    });
    return paths;
  }

  function refreshEmptyFieldsChip() {
    var chip = document.getElementById('lbEmptyFixChip');
    if (!chip) {
      return;
    }
    var count = emptyFieldPaths().length;
    chip.hidden = count === 0;
    var label = chip.querySelector('span');
    if (label) {
      label.textContent = 'بازیابی ' + count.toLocaleString('fa-IR') + ' خالی';
    }
  }

  /** Recover a draft whose text fields were emptied (stale/partial config). */
  function restoreEmptyFields() {
    var paths = emptyFieldPaths();
    if (!paths.length) {
      return;
    }
    paths.forEach(function (path) {
      commit(path, clone(getPath(BOOT.defaults, path)), { snapshot: true });
    });
    renderPanel();
    toast(paths.length.toLocaleString('fa-IR') + ' فیلد خالی بازگردانی شد.', 'success');
  }

  /**
   * True when a field differs from the factory default.
   *
   * Matches the amber dot on the field itself, and — unlike the unsaved-change
   * counter — stays meaningful after an autosave, which is what an editor
   * scanning for "what did we already customise?" expects.
   */
  function fieldIsChanged(path) {
    if (!path) {
      return false;
    }
    var fallback = getPath(BOOT.defaults, path);
    var current = getPath(state.config, path);
    if (fallback === undefined && current === undefined) {
      return false;
    }
    return JSON.stringify(fallback) !== JSON.stringify(current);
  }

  function renderPanelHead(section) {
    dom.panelHead.textContent = '';

    var totalFields = 0;
    var changedFields = 0;
    section.groups.forEach(function (group) {
      group.fields.forEach(function (field) {
        totalFields += 1;
        if (fieldIsChanged(field.path)) {
          changedFields += 1;
        }
      });
    });

    var allOpen = section.groups.every(function (group, index) {
      return state.collapsedGroups[section.id + ':' + index] !== false;
    });

    var chips = el('div', { class: 'lb-panel-chips' }, [
      el('span', { class: 'lb-chip-btn', text: totalFields.toLocaleString('fa-IR') + ' فیلد' }),
      el('button', {
        type: 'button',
        class: 'lb-chip-btn',
        'aria-pressed': state.changedOnly ? 'true' : 'false',
        title: 'نمایش فقط فیلدهایی که با مقدار پیش‌فرض کارخانه تفاوت دارند',
        onClick: function () {
          state.changedOnly = !state.changedOnly;
          renderPanel();
        }
      }, [
        el('span', { text: 'فقط تغییرات' }),
        el('b', { text: changedFields.toLocaleString('fa-IR') })
      ]),
      el('button', {
        type: 'button',
        class: 'lb-chip-btn',
        id: 'lbCollapseAllChip',
        title: allOpen ? 'بستن همه گروه‌ها' : 'باز کردن همه گروه‌ها',
        onClick: function () {
          section.groups.forEach(function (group, index) {
            state.collapsedGroups[section.id + ':' + index] = !allOpen;
          });
          renderPanel();
        }
      }, [el('span', { text: allOpen ? 'بستن همه' : 'باز کردن همه' })]),
      el('button', {
        type: 'button',
        class: 'lb-chip-btn',
        id: 'lbEmptyFixChip',
        hidden: true,
        title: 'فیلدهایی که خالی شده‌اند ولی مقدار پیش‌فرض دارند را برمی‌گرداند',
        onClick: function () {
          restoreEmptyFields();
        }
      }, [el('span', { text: 'بازیابی خالی‌ها' })])
    ]);

    var titleWrap = el('div', {}, [
      el('div', { class: 'lb-panel-title-row' }, [
        el('div', {}, [
          el('h2', { text: section.title }),
          section.desc ? el('p', { text: section.desc }) : null
        ]),
        el('div', { class: 'lb-panel-tools' }, [
          el(
            'button',
            {
              type: 'button',
              class: 'lb-mini-btn',
              title: 'بازنشانی این بخش به مقادیر پیش‌فرض کارخانه',
              onClick: function () {
                confirmDialog(
                  'بازنشانی «' + section.title + '»',
                  'همه فیلدهای این بخش در پیش‌نویس به مقدار پیش‌فرض برگردند. ادامه می‌دهید؟',
                  function () {
                    runAction('reset_section', { section: section.path || 'pageOrder' }, 'این بخش بازنشانی شد.');
                  }
                );
              }
            },
            'بازنشانی بخش'
          )
        ])
      ]),
      chips
    ]);
    dom.panelHead.appendChild(titleWrap);
  }

  /* --------------------------------------------------------- field render */

  function fieldValue(path) {
    var value = getPath(state.config, path);
    return value === undefined ? '' : value;
  }

  function fieldWrapper(field, fullPath) {
    var isChanged =
      String(getPath(BOOT.defaults, fullPath)) !== String(getPath(state.config, fullPath));
    var wrapper = el('div', {
      class: 'lb-field' + (isChanged ? ' is-changed' : ''),
      dataset: { fieldPath: fullPath, fieldType: field.type, fieldLabel: field.label }
    });

    var head = el('div', { class: 'lb-field-head' }, [
      el('label', { class: 'lb-field-label', text: field.label }),
      el('span', { class: 'lb-field-path', text: fullPath }),
      el(
        'button',
        {
          type: 'button',
          class: 'lb-field-reset',
          title: 'بازگردانی این فیلد به مقدار پیش‌فرض',
          text: 'پیش‌فرض',
          onClick: function () {
            var fallback = getPath(BOOT.defaults, fullPath);
            commit(fullPath, clone(fallback), { snapshot: true });
            renderPanel();
          }
        }
      )
    ]);
    wrapper.appendChild(head);

    var control = renderControl(field, fullPath);
    if (control) {
      wrapper.appendChild(control);
    }

    var footParts = [];
    if (field.hint) {
      footParts.push(el('span', { class: 'lb-field-hint', text: field.hint }));
    }
    if (field.maxLength) {
      var counter = el('span', { class: 'lb-counter' });
      footParts.push(counter);
      wrapper.dataset.maxLength = String(field.maxLength);
      if (field.suggestLength) {
        wrapper.dataset.suggestLength = String(field.suggestLength);
      }
      updateCounter(counter, field, fullPath);
    }
    if (footParts.length) {
      wrapper.appendChild(el('div', { class: 'lb-field-foot' }, footParts));
    }

    bindShowIf(wrapper, field);
    return wrapper;
  }

  function updateCounter(node, field, fullPath) {
    // Repeater children carry a template path ("items[].title") in the schema;
    // the live value only exists at the indexed path.
    var value = String(fieldValue(fullPath || field.path) || '');
    var length = Array.from(value).length;
    node.textContent = length + ' / ' + field.maxLength + ' نویسه';
    node.classList.toggle('is-over', length > field.maxLength);
    if (field.suggestLength) {
      node.classList.toggle('is-good', length > 0 && length <= field.suggestLength);
    }
  }

  function bindShowIf(wrapper, field) {
    if (!field.showIf) {
      return;
    }
    var refresh = function () {
      var visible = Object.keys(field.showIf).every(function (key) {
        return String(getPath(state.config, key)) === String(field.showIf[key]);
      });
      wrapper.dataset.hidden = visible ? 'false' : 'true';
    };
    refresh();
    wrapper.dataset.showIf = JSON.stringify(field.showIf);
  }

  function refreshShowIf() {
    Array.prototype.forEach.call(dom.panelBody.querySelectorAll('[data-show-if]'), function (node) {
      var rules = JSON.parse(node.dataset.showIf);
      var visible = Object.keys(rules).every(function (key) {
        return String(getPath(state.config, key)) === String(rules[key]);
      });
      node.dataset.hidden = visible ? 'false' : 'true';
    });
  }

  function renderField(field, basePath) {
    if (field.type === 'order') {
      return renderOrderField(field);
    }
    return fieldWrapper(field, field.path);
  }

  function renderControl(field, fullPath) {
    var value = fieldValue(fullPath);
    var input;

    switch (field.type) {
      case 'textarea':
        input = el('textarea', {
          class: 'lb-textarea',
          rows: String(field.rows || 3),
          dir: field.dir || 'rtl',
          'aria-label': field.label
        });
        input.value = String(value || '');
        bindText(input, fullPath);
        return input;

      case 'toggle':
        return renderSwitch(field, fullPath);

      case 'range':
        return renderRange(field, fullPath, value);

      case 'number':
        return renderNumber(field, fullPath, value);

      case 'select':
        return renderSelect(field, fullPath, value);

      case 'color':
        return renderColor(field, fullPath, value);

      case 'image':
        return renderImage(field, fullPath, value);

      case 'icon':
        return renderIconPicker(field, fullPath, value);

      case 'list':
        return renderList(field, fullPath);

      case 'repeater':
        return renderRepeater(field, fullPath);

      case 'link':
        input = el('input', {
          class: 'lb-input',
          type: 'text',
          dir: 'ltr',
          placeholder: '#catalog یا https://…',
          'aria-label': field.label
        });
        input.value = String(value || '');
        bindText(input, fullPath);
        return el('div', { class: 'lb-number-row' }, [
          (function () {
            var wrap = el('span');
            wrap.innerHTML = icon('link', 14);
            return wrap.firstChild;
          })(),
          input
        ]);

      default:
        input = el('input', {
          class: 'lb-input',
          type: 'text',
          dir: field.dir || 'rtl',
          'aria-label': field.label
        });
        input.value = String(value || '');
        bindText(input, fullPath);
        return input;
    }
  }

  function bindText(input, fullPath) {
    input.addEventListener('input', function () {
      commit(fullPath, input.value);
      var wrapper = input.closest('.lb-field');
      var counter = wrapper && wrapper.querySelector('.lb-counter');
      if (counter && wrapper.dataset.maxLength) {
        var maxLength = Number(wrapper.dataset.maxLength);
        var suggestLength = Number(wrapper.dataset.suggestLength || 0);
        var length = Array.from(input.value).length;
        counter.textContent = length + ' / ' + maxLength + ' نویسه';
        counter.classList.toggle('is-over', length > maxLength);
        if (suggestLength) {
          counter.classList.toggle('is-good', length > 0 && length <= suggestLength);
        }
      }
      refreshShowIf();
    });
    input.addEventListener('focus', function () {
      scrollPreviewTo(sectionsById[state.sectionId], fullPath);
    });
  }

  function renderSwitch(field, fullPath) {
    var checked = fieldValue(fullPath) === true;
    var input = el('input', { type: 'checkbox', 'aria-label': field.label });
    input.checked = checked;
    input.addEventListener('change', function () {
      commit(fullPath, input.checked);
      renderRail();
    });
    return el('label', { class: 'lb-switch' }, [
      input,
      el('span', { class: 'lb-switch-track' }),
      el('span', { text: checked ? 'فعال' : 'خاموش' })
    ]);
  }

  function renderRange(field, fullPath, value) {
    var min = field.min !== undefined ? field.min : 0;
    var max = field.max !== undefined ? field.max : 100;
    var step = field.step || 1;
    var input = el('input', {
      type: 'range',
      min: String(min),
      max: String(max),
      step: String(step),
      'aria-label': field.label
    });
    input.value = String(value === '' ? min : value);
    var readout = el('span', { class: 'lb-range-value', text: formatNumber(input.value) + (field.unit ? ' ' + field.unit : '') });
    input.addEventListener('input', function () {
      var numeric = Number(input.value);
      readout.textContent = formatNumber(numeric) + (field.unit ? ' ' + field.unit : '');
      commit(fullPath, numeric);
    });
    return el('div', { class: 'lb-range-row' }, [input, readout]);
  }

  function renderNumber(field, fullPath, value) {
    var input = el('input', {
      class: 'lb-input',
      type: 'number',
      min: field.min !== undefined ? String(field.min) : null,
      max: field.max !== undefined ? String(field.max) : null,
      step: field.step !== undefined ? String(field.step) : '1',
      'aria-label': field.label
    });
    input.value = value === '' || value === null ? '' : String(value);
    input.addEventListener('input', function () {
      commit(fullPath, input.value === '' ? 0 : Number(input.value));
    });
    return el('div', { class: 'lb-number-row' }, [input, field.unit ? el('span', { class: 'lb-unit', text: field.unit }) : null]);
  }

  function renderSelect(field, fullPath, value) {
    var select = el('select', { class: 'lb-select', 'aria-label': field.label });
    (field.options || []).forEach(function (option) {
      var opt = el('option', { value: option.value, text: option.label });
      select.appendChild(opt);
    });
    select.value = String(value);
    select.addEventListener('change', function () {
      commit(fullPath, select.value);
      refreshShowIf();
    });
    return select;
  }

  function renderColor(field, fullPath, value) {
    var text = el('input', { class: 'lb-input', type: 'text', dir: 'ltr', 'aria-label': field.label });
    var picker = el('input', { class: 'lb-color-input', type: 'color', 'aria-label': field.label + ' (انتخاب رنگ)' });
    text.value = String(value || '');
    picker.value = /^#[0-9a-f]{6}$/i.test(String(value)) ? String(value) : '#000000';
    picker.addEventListener('input', function () {
      text.value = picker.value;
      commit(fullPath, picker.value);
    });
    text.addEventListener('input', function () {
      if (/^#[0-9a-f]{3,8}$/i.test(text.value)) {
        picker.value = text.value.length === 4 || text.value.length === 5 ? text.value : text.value;
        commit(fullPath, text.value);
      }
    });
    return el('div', { class: 'lb-color-row' }, [picker, text]);
  }

  function renderImage(field, fullPath, value) {
    var url = String(value || '');
    var thumb = el('div', { class: 'lb-image-thumb' });
    var paintThumb = function (source) {
      thumb.textContent = '';
      if (source) {
        thumb.appendChild(el('img', { src: source, alt: '', loading: 'lazy' }));
      } else {
        thumb.innerHTML = icon('camera', 16);
      }
    };
    paintThumb(url);

    var input = el('input', { class: 'lb-input', type: 'text', dir: 'ltr', 'aria-label': field.label });
    input.value = url;
    input.addEventListener('input', function () {
      paintThumb(input.value);
      commit(fullPath, input.value);
    });

    var uploadBtn = el(
      'button',
      {
        type: 'button',
        class: 'lb-mini-btn is-primary',
        text: 'بارگذاری تصویر',
        onClick: function () {
          dom.fileInput.value = '';
          dom.fileInput.onchange = function () {
            var file = dom.fileInput.files && dom.fileInput.files[0];
            if (!file) {
              return;
            }
            var data = new FormData();
            data.append('file', file);
            toast('در حال بارگذاری تصویر…');
            post('upload', data)
              .then(function (json) {
                input.value = json.url;
                paintThumb(json.url);
                commit(fullPath, json.url);
                toast('تصویر بارگذاری شد.', 'success');
              })
              .catch(function (error) {
                toast(error.message, 'error');
              });
          };
          dom.fileInput.click();
        }
      }
    );

    var clearBtn = el('button', {
      type: 'button',
      class: 'lb-mini-btn',
      text: 'خالی کردن',
      onClick: function () {
        input.value = '';
        paintThumb('');
        commit(fullPath, '');
      }
    });

    return el('div', { class: 'lb-image-row' }, [
      thumb,
      el('div', { class: 'lb-image-fields' }, [input, el('div', { class: 'lb-image-actions' }, [uploadBtn, clearBtn])])
    ]);
  }

  function renderIconPicker(field, fullPath, value) {
    var current = String(value || '');
    var grid = el('div', { class: 'lb-icon-grid' });
    (field.options || []).forEach(function (option) {
      var btn = el('button', {
        type: 'button',
        class: 'lb-icon-option' + (option.value === current ? ' is-active' : ''),
        title: option.label,
        'aria-label': option.label,
        onClick: function () {
          commit(fullPath, option.value);
          renderPanel();
        }
      });
      btn.innerHTML = icon(option.value, 17);
      grid.appendChild(btn);
    });
    return grid;
  }

  function renderList(field, fullPath) {
    var items = fieldValue(fullPath);
    if (!Array.isArray(items)) {
      items = [];
    }
    var wrap = el('div', { class: 'lb-list' });

    items.forEach(function (item, index) {
      var row = el('div', { class: 'lb-list-row' });
      var input;
      if (field.itemType === 'textarea') {
        input = el('textarea', { class: 'lb-textarea', rows: '2', 'aria-label': field.label + ' ' + (index + 1) });
        input.value = String(item);
        input.addEventListener('input', function () {
          var next = items.slice();
          next[index] = input.value;
          items = next;
          commit(fullPath, next);
        });
      } else if (field.itemType === 'color') {
        var picker = el('input', { class: 'lb-color-input', type: 'color', 'aria-label': field.label + ' ' + (index + 1) });
        picker.value = /^#[0-9a-f]{6}$/i.test(String(item)) ? String(item) : '#000000';
        picker.addEventListener('input', function () {
          var nextColor = items.slice();
          nextColor[index] = picker.value;
          items = nextColor;
          commit(fullPath, nextColor);
        });
        input = picker;
      } else {
        input = el('input', { class: 'lb-input', type: 'text', 'aria-label': field.label + ' ' + (index + 1) });
        if (field.suggest) {
          input.setAttribute('list', 'lb-suggest-' + field.suggest);
        }
        input.value = String(item);
        input.addEventListener('input', function () {
          var nextText = items.slice();
          nextText[index] = input.value;
          items = nextText;
          commit(fullPath, nextText);
        });
      }

      var removeBtn = el('button', {
        type: 'button',
        class: 'lb-icon-btn lb-icon-btn--sm',
        title: 'حذف',
        'aria-label': 'حذف مورد',
        onClick: function () {
          var nextRemoved = items.slice();
          nextRemoved.splice(index, 1);
          commit(fullPath, nextRemoved, { rerender: true });
        }
      });
      removeBtn.innerHTML = icon('trash', 13);

      row.appendChild(input);
      row.appendChild(removeBtn);
      wrap.appendChild(row);
    });

    if (field.suggest) {
      var datalist = el('datalist', { id: 'lb-suggest-' + field.suggest });
      ((BOOT.suggestions || {})[field.suggest] || []).forEach(function (option) {
        datalist.appendChild(el('option', { value: option.value, label: option.label }));
      });
      wrap.appendChild(datalist);
    }

    var max = field.max || 30;
    if (items.length < max) {
      var addBtn = el('button', {
        type: 'button',
        class: 'lb-mini-btn lb-list-add',
        text: '+ افزودن مورد',
        onClick: function () {
          var template = getPath(BOOT.defaults, fullPath);
          var sample = Array.isArray(template) && template.length ? template[0] : '';
          var nextAdded = items.slice();
          nextAdded.push(typeof sample === 'string' ? '' : clone(sample));
          commit(fullPath, nextAdded, { rerender: true });
        }
      });
      wrap.appendChild(addBtn);
    }

    return wrap;
  }

  function renderRepeater(field, fullPath) {
    var items = fieldValue(fullPath);
    if (!Array.isArray(items)) {
      items = [];
    }
    var wrap = el('div', { class: 'lb-repeater' });

    if (!items.length) {
      wrap.appendChild(el('div', { class: 'lb-empty-note', text: 'هنوز آیتمی ثبت نشده است.' }));
    }

    items.forEach(function (item, index) {
      var itemPath = fullPath + '[' + index + ']';
      var itemKey = itemPath;
      var isOpen = state.openRepeaters[itemKey] !== false;
      var titleSource = item.title || item.label || item.q || item.caption || item.handle || item.title || 'آیتم';
      var title = (field.itemLabel || 'آیتم') + ' ' + (index + 1) + ' — ' + String(titleSource).slice(0, 34);

      var head = el('div', { class: 'lb-repeater-head' }, [
        (function () {
          var handle = el('span', { class: 'lb-order-handle', title: 'جابه‌جایی' });
          handle.innerHTML = icon('grip', 13);
          return handle;
        })(),
        el('strong', { text: title }),
        el('div', { class: 'lb-repeater-actions' }, [
          (function () {
            var dup = el('button', {
              type: 'button',
              class: 'lb-icon-btn lb-icon-btn--sm',
              title: 'تکثیر',
              'aria-label': 'تکثیر آیتم',
              onClick: function () {
                var nextDup = clone(items);
                nextDup.splice(index + 1, 0, clone(item));
                commit(fullPath, nextDup, { rerender: true });
              }
            });
            dup.innerHTML = icon('copy', 13);
            return dup;
          })(),
          (function () {
            var del = el('button', {
              type: 'button',
              class: 'lb-icon-btn lb-icon-btn--sm',
              title: 'حذف',
              'aria-label': 'حذف آیتم',
              onClick: function () {
                var nextDel = clone(items);
                nextDel.splice(index, 1);
                commit(fullPath, nextDel, { rerender: true });
              }
            });
            del.innerHTML = icon('trash', 13);
            return del;
          })(),
          (function () {
            var toggle = el('button', {
              type: 'button',
              class: 'lb-icon-btn lb-icon-btn--sm',
              title: 'جمع/باز کردن',
              'aria-label': 'جمع یا باز کردن آیتم',
              onClick: function () {
                state.openRepeaters[itemKey] = !isOpen;
                renderPanel();
              }
            });
            toggle.innerHTML = icon('chevron', 13);
            return toggle;
          })()
        ])
      ]);

      var body = el('div', { class: 'lb-repeater-body' });
      (field.fields || []).forEach(function (subField) {
        var subPath = itemPath + '.' + subField.path;
        if (subField.type === 'repeater') {
          body.appendChild(renderNestedRepeater(subField, subPath));
        } else if (subField.type === 'list') {
          body.appendChild(nestedWrapper(subField, subPath, renderList(subField, subPath)));
        } else {
          body.appendChild(fieldWrapper(subField, subPath));
        }
      });

      var itemNode = el('div', { class: 'lb-repeater-item', dataset: { open: isOpen ? 'true' : 'false' } }, [head, body]);
      itemNode.draggable = true;
      bindRepeaterDrag(itemNode, items, index, function (from, to) {
        var nextMove = clone(items);
        var moved = nextMove.splice(from, 1)[0];
        nextMove.splice(to, 0, moved);
        commit(fullPath, nextMove, { rerender: true });
      });

      wrap.appendChild(itemNode);
    });

    var max = field.max || 30;
    if (items.length < max) {
      wrap.appendChild(
        el('div', { class: 'lb-repeater-foot' }, [
          el('button', {
            type: 'button',
            class: 'lb-mini-btn is-primary',
            text: '+ ' + (field.itemLabel || 'آیتم') + ' جدید',
            onClick: function () {
              var template = getPath(BOOT.defaults, fullPath);
              var sample = Array.isArray(template) && template.length ? template[0] : {};
              var nextNew = clone(items);
              nextNew.push(clone(sample));
              commit(fullPath, nextNew, { rerender: true });
            }
          })
        ])
      );
    }

    return wrap;
  }

  function nestedWrapper(field, fullPath, control) {
    var wrapper = el('div', { class: 'lb-field', dataset: { fieldPath: fullPath, fieldType: field.type } }, [
      el('div', { class: 'lb-field-head' }, [el('label', { class: 'lb-field-label', text: field.label })]),
      control
    ]);
    return wrapper;
  }

  function renderNestedRepeater(field, fullPath) {
    var wrapper = el('div', { class: 'lb-field', dataset: { fieldPath: fullPath, fieldType: 'repeater' } }, [
      el('div', { class: 'lb-field-head' }, [el('label', { class: 'lb-field-label', text: field.label })]),
      renderRepeater(field, fullPath)
    ]);
    return wrapper;
  }

  function bindRepeaterDrag(node, items, index, onDrop) {
    node.addEventListener('dragstart', function (event) {
      event.stopPropagation();
      event.dataTransfer.setData('text/plain', 'rep:' + index);
      node.classList.add('is-dragging');
    });
    node.addEventListener('dragend', function () {
      node.classList.remove('is-dragging');
    });
    node.addEventListener('dragover', function (event) {
      event.preventDefault();
      node.classList.add('is-drop-target');
    });
    node.addEventListener('dragleave', function () {
      node.classList.remove('is-drop-target');
    });
    node.addEventListener('drop', function (event) {
      event.preventDefault();
      event.stopPropagation();
      node.classList.remove('is-drop-target');
      var raw = event.dataTransfer.getData('text/plain');
      if (raw.indexOf('rep:') !== 0) {
        return;
      }
      var from = Number(raw.slice(4));
      if (from !== index) {
        onDrop(from, index);
      }
    });
  }

  function renderOrderField(field) {
    var rows = state.config.sectionOrder || [];
    var list = el('div', { class: 'lb-order-list' });

    rows.forEach(function (row, index) {
      var section = sectionsById[row.id];
      var enabled = row.enabled !== false;

      var handle = el('span', { class: 'lb-order-handle' });
      handle.innerHTML = icon('grip', 13);

      var iconBox = el('span', { class: 'lb-rail-icon' });
      iconBox.innerHTML = icon(section ? section.icon : 'list', 14);

      var eye = el('button', {
        type: 'button',
        class: 'lb-rail-eye',
        dataset: { on: enabled ? 'true' : 'false' },
        title: enabled ? 'خاموش کردن' : 'روشن کردن',
        onClick: function () {
          var nextOrder = clone(rows);
          nextOrder[index].enabled = !enabled;
          commit('sectionOrder', nextOrder);
          renderPanel();
          renderRail();
        }
      });
      eye.innerHTML = icon(enabled ? 'eye' : 'eyeOff', 14);

      var node = el(
        'div',
        { class: 'lb-order-row' + (enabled ? '' : ' is-off'), draggable: 'true' },
        [handle, iconBox, el('span', { text: row.label || (section ? section.title : row.id) }), eye]
      );

      node.addEventListener('dragstart', function (event) {
        event.dataTransfer.setData('text/plain', 'ord:' + index);
        node.classList.add('is-dragging');
      });
      node.addEventListener('dragend', function () {
        node.classList.remove('is-dragging');
      });
      node.addEventListener('dragover', function (event) {
        event.preventDefault();
        node.classList.add('is-drop-target');
      });
      node.addEventListener('dragleave', function () {
        node.classList.remove('is-drop-target');
      });
      node.addEventListener('drop', function (event) {
        event.preventDefault();
        node.classList.remove('is-drop-target');
        var raw = event.dataTransfer.getData('text/plain');
        if (raw.indexOf('ord:') !== 0) {
          return;
        }
        var from = Number(raw.slice(4));
        if (from === index) {
          return;
        }
        var nextOrder = clone(rows);
        var moved = nextOrder.splice(from, 1)[0];
        nextOrder.splice(index, 0, moved);
        commit('sectionOrder', nextOrder);
        renderPanel();
        renderRail();
      });

      list.appendChild(node);
    });

    var wrap = el('div', { class: 'lb-field', dataset: { fieldPath: 'sectionOrder', fieldType: 'order' } }, [
      el('div', { class: 'lb-field-head' }, [
        el('label', { class: 'lb-field-label', text: field.label }),
        el('span', { class: 'lb-field-reset', text: field.hint || '' })
      ]),
      list
    ]);
    return wrap;
  }

  function formatNumber(value) {
    var number = Number(value);
    if (Number.isNaN(number)) {
      return String(value);
    }
    return Number.isInteger(number) ? String(number) : String(Math.round(number * 100) / 100);
  }

  /* ------------------------------------------------------------- status  */

  function setBusy(busy) {
    dom.statusPill.dataset.busy = busy ? 'true' : 'false';
  }

  function renderStatus() {
    recomputePending();
    var count = state.pending.length;
    dom.publishCount.hidden = count === 0;
    dom.publishCount.textContent = String(count);
    dom.publishBtn.disabled = count === 0 && !state.meta.hasDraft;
    dom.undoBtn.disabled = state.undoStack.length === 0;
    dom.redoBtn.disabled = state.redoStack.length === 0;

    var pill = dom.statusPill;
    if (state.saving) {
      pill.dataset.tone = 'neutral';
      dom.statusText.textContent = 'در حال ذخیره…';
    } else if (count > 0) {
      pill.dataset.tone = 'dirty';
      dom.statusText.textContent = count.toLocaleString('fa-IR') + ' تغییر ذخیره‌نشده';
    } else if (state.meta.hasDraft) {
      pill.dataset.tone = 'saved';
      dom.statusText.textContent = 'پیش‌نویس همگام است';
    } else {
      pill.dataset.tone = 'neutral';
      dom.statusText.textContent = 'نسخه منتشرشده';
    }

    var metaParts = [];
    if (state.lastSavedAt) {
      metaParts.push('آخرین ذخیره ' + state.lastSavedAt.toLocaleTimeString('fa-IR'));
    } else if (state.meta.draftUpdatedAt) {
      metaParts.push('آخرین ویرایش ' + state.meta.draftUpdatedAt);
    }
    if (state.meta.publishedAt) {
      metaParts.push('انتشار ' + state.meta.publishedAt);
    }
    dom.statusMeta.textContent = metaParts.join(' • ');
    dom.versionTag.textContent = 'v' + (BOOT.appVersion || '');
  }

  /* -------------------------------------------------------------- modal   */

  function openModal(title, bodyNode, footerNodes) {
    dom.modalTitle.textContent = title;
    dom.modalBody.textContent = '';
    dom.modalBody.appendChild(bodyNode);
    dom.modalFoot.textContent = '';
    (footerNodes || []).forEach(function (node) {
      dom.modalFoot.appendChild(node);
    });
    dom.modalHost.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function hideStudioModal() {
    dom.modalHost.hidden = true;
    document.body.style.overflow = '';
  }

  function confirmDialog(title, message, onConfirm, confirmLabel) {
    openModal(
      title,
      el('p', { text: message }),
      [
        el('button', { type: 'button', class: 'lb-btn', text: 'انصراف', onClick: hideStudioModal }),
        el('button', {
          type: 'button',
          class: 'lb-btn lb-btn--primary',
          text: confirmLabel || 'تأیید و ادامه',
          onClick: function () {
            hideStudioModal();
            onConfirm();
          }
        })
      ]
    );
  }

  function runAction(action, payload, successMessage) {
    return post(action, payload || {})
      .then(function (json) {
        if (json.config) {
          state.config = clone(json.config);
          state.baseline = clone(json.config);
          state.undoStack = [];
          state.redoStack = [];
        }
        recomputePending();
        pushPreview(true);
        renderRail();
        renderPanel();
        renderStatus();
        toast(json.message || successMessage || 'انجام شد.', 'success');
      })
      .catch(function (error) {
        toast(error.message, 'error');
      });
  }

  /* ------------------------------------------------------------ dialogs   */

  function openPublishDialog() {
    var count = state.pending.length;
    var noteInput = el('input', { class: 'lb-input', type: 'text', placeholder: 'توضیح کوتاه برای تاریخچه (اختیاری)' });
    var summary = el('div', {
      class: 'lb-field-hint',
      text: count > 0 ? count.toLocaleString('fa-IR') + ' فیلد تغییر کرده و با انتشار روی سایت اعمال می‌شود.' : 'پیش‌نویس فعلی روی صفحه اصلی منتشر می‌شود.'
    });

    openModal(
      'انتشار روی صفحه اصلی',
      el('div', {}, [summary, noteInput]),
      [
        el('button', { type: 'button', class: 'lb-btn', text: 'انصراف', onClick: hideStudioModal }),
        el('button', {
          type: 'button',
          class: 'lb-btn lb-btn--primary',
          text: 'انتشار نهایی',
          onClick: function () {
            hideStudioModal();
            var doPublish = function () {
              return post('publish', { note: noteInput.value }).then(function (json) {
                state.baseline = clone(json.config || state.config);
                state.lastSavedAt = new Date();
                recomputePending();
                renderStatus();
                renderRail();
                toast(json.message || 'منتشر شد.', 'success');
              });
            };
            (count > 0 ? saveDraft(true).then(doPublish) : doPublish()).catch(function (error) {
              toast(error.message, 'error');
            });
          }
        })
      ]
    );
  }

  function openHistoryDialog() {
    var list = el('div', { class: 'lb-history-list' });

    if (!state.revisions.length) {
      list.appendChild(el('div', { class: 'lb-empty-note', text: 'هنوز نسخه‌ای منتشر نشده است.' }));
    }

    state.revisions.forEach(function (revision) {
      var restoreBtn = el('button', {
        type: 'button',
        class: 'lb-mini-btn',
        text: 'بارگذاری در پیش‌نویس',
        onClick: function () {
          hideStudioModal();
          confirmDialog(
            'بازگردانی نسخه',
            'این نسخه در پیش‌نویس بارگذاری می‌شود و تا زمان انتشار، سایت تغییری نمی‌کند. ادامه می‌دهید؟',
            function () {
              runAction('restore_revision', { id: revision.id }, 'نسخه در پیش‌نویس بارگذاری شد.');
            }
          );
        }
      });

      list.appendChild(
        el('div', { class: 'lb-history-row' }, [
          el('div', {}, [
            el('strong', { text: revision.note || 'نسخه بدون عنوان' }),
            el('small', { text: [revision.created_at, revision.author ? 'توسط ' + revision.author : ''].filter(Boolean).join(' • ') })
          ]),
          restoreBtn
        ])
      );
    });

    openModal('نسخه‌های منتشرشده', list, [el('button', { type: 'button', class: 'lb-btn', text: 'بستن', onClick: hideStudioModal })]);
  }

  function openImportDialog() {
    var textarea = el('textarea', { class: 'lb-json-input', placeholder: '{ "config": { … } }' });
    var fileBtn = el('button', {
      type: 'button',
      class: 'lb-mini-btn',
      text: 'انتخاب فایل JSON',
      onClick: function () {
        dom.jsonInput.value = '';
        dom.jsonInput.onchange = function () {
          var file = dom.jsonInput.files && dom.jsonInput.files[0];
          if (!file) {
            return;
          }
          var reader = new FileReader();
          reader.onload = function () {
            textarea.value = String(reader.result);
          };
          reader.readAsText(file);
        };
        dom.jsonInput.click();
      }
    });

    openModal(
      'ورود تنظیمات از JSON',
      el('div', {}, [el('p', { text: 'محتوای JSON را بچسبانید یا فایل خروجی استودیو را انتخاب کنید. داده‌ها ابتدا در پیش‌نویس بارگذاری می‌شوند.' }), textarea, fileBtn]),
      [
        el('button', { type: 'button', class: 'lb-btn', text: 'انصراف', onClick: hideStudioModal }),
        el('button', {
          type: 'button',
          class: 'lb-btn lb-btn--primary',
          text: 'بارگذاری',
          onClick: function () {
            hideStudioModal();
            runAction('import', { json: textarea.value }, 'تنظیمات بارگذاری شد.');
          }
        })
      ]
    );
  }

  function openShortcutsDialog() {
    var rows = [
      ['Ctrl + S', 'ذخیره پیش‌نویس'],
      ['Ctrl + Z', 'واگرد'],
      ['Ctrl + Shift + Z', 'ازنو'],
      ['Ctrl + K', 'جستجوی فیلد'],
      ['V', 'حالت انتخاب از پیش‌نمایش'],
      ['F', 'حالت تمام‌صفحه'],
      ['Esc', 'بستن پنجره یا خروج از حالت انتخاب']
    ];
    var table = el('div', { class: 'lb-kbd-table' });
    rows.forEach(function (row) {
      table.appendChild(el('kbd', { text: row[0] }));
      table.appendChild(el('span', { text: row[1] }));
    });
    openModal('کلیدهای میانبر', table, [el('button', { type: 'button', class: 'lb-btn', text: 'بستن', onClick: hideStudioModal })]);
  }

  function downloadExport() {
    var payload = {
      app: 'AB-Socks',
      kind: 'landing-config',
      exported_at: new Date().toISOString(),
      app_version: BOOT.appVersion,
      config: state.config
    };
    var blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
    var link = el('a', { href: URL.createObjectURL(blob), download: 'landing-config-' + Date.now() + '.json' });
    document.body.appendChild(link);
    link.click();
    window.setTimeout(function () {
      URL.revokeObjectURL(link.href);
      link.remove();
    }, 500);
    toast('فایل JSON دانلود شد.');
  }

  /* -------------------------------------------------------------- search  */

  function searchFields(query) {
    var needle = String(query || '').trim().toLowerCase();
    if (needle.length < 2) {
      return [];
    }
    var results = [];

    BOOT.sections.forEach(function (section) {
      section.groups.forEach(function (group) {
        group.fields.forEach(function (field) {
          var haystack = (field.label + ' ' + field.path + ' ' + section.title + ' ' + group.title).toLowerCase();
          var candidate = field.type === 'repeater' || field.type === 'list' || field.type === 'order' ? haystack : haystack + ' ' + String(getPath(state.config, field.path) || '').toLowerCase();
          if (candidate.indexOf(needle) !== -1) {
            results.push({ section: section, field: field, group: group });
          }
        });
      });
    });

    return results.slice(0, 40);
  }

  function renderSearchResults(query) {
    var results = searchFields(query);
    dom.searchResults.textContent = '';

    if (!query || query.trim().length < 2) {
      dom.searchResults.hidden = true;
      return;
    }

    dom.searchResults.hidden = false;

    if (!results.length) {
      dom.searchResults.appendChild(el('div', { class: 'lb-result-empty', text: 'نتیجه‌ای پیدا نشد.' }));
      return;
    }

    results.forEach(function (result) {
      dom.searchResults.appendChild(
        el(
          'button',
          {
            type: 'button',
            class: 'lb-result-item',
            onClick: function () {
              selectSection(result.section.id);
              window.setTimeout(function () {
                focusField(result.field.path);
              }, 80);
              dom.searchResults.hidden = true;
              dom.searchInput.value = '';
            }
          },
          [el('span', { text: result.field.label }), el('small', { text: result.section.title + ' • ' + result.group.title })]
        )
      );
    });
  }

  /* ---------------------------------------------------------- mobile tabs */

  /**
   * Size the shell to the exact space left under the admin chrome.
   *
   * The CSS calc() fallback cannot know how tall the topbar + tabs end up being
   * (they wrap on narrow screens), which left dead canvas below the studio.
   */
  function syncStudioHeight() {
    if (!root) {
      return;
    }
    // Phone layout flows naturally; every wider layout gets a fixed shell height
    // so the three panes scroll internally instead of the page.
    if (isCompactLayout() && window.innerWidth < 640) {
      root.style.removeProperty('--lb-studio-h');
      return;
    }
    var offsetTop = root.getBoundingClientRect().top + window.scrollY;
    var shell = root.parentElement;
    var padBottom = shell ? (parseFloat(window.getComputedStyle(shell).paddingBottom) || 0) : 0;
    var available = Math.round(window.innerHeight - offsetTop - padBottom - 4);
    var height = Math.max(460, Math.min(available, 1600));
    root.style.setProperty('--lb-studio-h', height + 'px');

    // Second pass: the first measurement can be off (fonts, wrapping tabs,
    // scrollbar appearing), which either overflowed the fold or left dead
    // canvas under the shell. Correct towards the fold in both directions.
    for (var pass = 0; pass < 2; pass += 1) {
      var delta = window.innerHeight - document.documentElement.scrollHeight;
      if (Math.abs(delta) <= 2) {
        break;
      }
      var next = Math.max(460, Math.min(height + delta, 1600));
      if (next === height) {
        break;
      }
      height = next;
      root.style.setProperty('--lb-studio-h', height + 'px');
    }
  }

  /** True when the studio itself is narrow (container query breakpoint). */
  function isCompactLayout() {
    var switcher = document.querySelector('.lb-mobile-switch');
    return Boolean(switcher) && window.getComputedStyle(switcher).display !== 'none';
  }

  function syncMobileSwitch(view) {
    Array.prototype.forEach.call(document.querySelectorAll('[data-mobile-view]'), function (btn) {
      btn.classList.toggle('is-active', btn.dataset.mobileView === view);
    });
  }

  /* --------------------------------------------------------------- wiring */

  /** Collapse/restore a side pane; the preference survives reloads. */
  function togglePane(pane) {
    var attr = pane === 'rail' ? 'railCollapsed' : 'panelCollapsed';
    var collapsed = root.dataset[attr] === 'true';
    root.dataset[attr] = collapsed ? 'false' : 'true';
    try {
      window.localStorage.setItem(STORE_KEY + ':' + pane, collapsed ? '0' : '1');
    } catch (e) {
      /* storage disabled */
    }
    var btn = document.getElementById(pane === 'rail' ? 'lbRailToggle' : 'lbPanelToggle');
    if (btn) {
      btn.setAttribute('aria-pressed', collapsed ? 'false' : 'true');
      btn.setAttribute('title', pane === 'rail'
        ? (collapsed ? 'جمع کردن ستون سکشن‌ها' : 'باز کردن ستون سکشن‌ها')
        : (collapsed ? 'جمع کردن ستون ویرایش' : 'باز کردن ستون ویرایش'));
    }
    window.requestAnimationFrame(function () {
      syncStudioHeight();
      applyDeviceScale();
    });
  }

  function restorePaneState() {
    [['rail', 'railCollapsed', 'lbRailToggle'], ['panel', 'panelCollapsed', 'lbPanelToggle']].forEach(function (row) {
      var collapsed = false;
      try {
        collapsed = window.localStorage.getItem(STORE_KEY + ':' + row[0]) === '1';
      } catch (e) {
        collapsed = false;
      }
      root.dataset[row[1]] = collapsed ? 'true' : 'false';
      var btn = document.getElementById(row[2]);
      if (btn) {
        btn.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
      }
    });
  }

  /* ------------------------------------------------------------ splitters  */

  /**
   * The rail and the field panel can be resized by dragging the gaps between the
   * panes. Widths are written to inline custom properties on the studio root (so
   * they beat the container-query defaults) and remembered per browser.
   * The workspace is RTL, so dragging towards the left grows a pane.
   */
  var PANE_LIMITS = {
    rail: { min: 176, max: 340, varName: '--lb-rail-w' },
    panel: { min: 264, max: 640, varName: '--lb-panel-w' }
  };

  function paneWidth(kind) {
    var limits = PANE_LIMITS[kind];
    var value = window.getComputedStyle(root).getPropertyValue(limits.varName);
    var parsed = parseFloat(value);
    if (isNaN(parsed)) {
      return kind === 'rail' ? 224 : 376;
    }
    // rem/em → px
    if (String(value).indexOf('rem') !== -1) {
      return parsed * parseFloat(window.getComputedStyle(document.documentElement).fontSize || '16');
    }
    return parsed;
  }

  function applyPaneWidth(kind, px) {
    var limits = PANE_LIMITS[kind];
    var clamped = Math.max(limits.min, Math.min(limits.max, Math.round(px)));
    root.style.setProperty(limits.varName, clamped + 'px');
    try {
      window.localStorage.setItem(STORE_KEY + ':w:' + kind, String(clamped));
    } catch (e) {
      /* storage disabled */
    }
    return clamped;
  }

  function resetPaneWidth(kind) {
    root.style.removeProperty(PANE_LIMITS[kind].varName);
    try {
      window.localStorage.removeItem(STORE_KEY + ':w:' + kind);
    } catch (e) {
      /* storage disabled */
    }
    applyDeviceScale();
    autoFitDevice();
  }

  function restorePaneWidths() {
    ['rail', 'panel'].forEach(function (kind) {
      var stored = null;
      try {
        stored = window.localStorage.getItem(STORE_KEY + ':w:' + kind);
      } catch (e) {
        stored = null;
      }
      if (stored) {
        applyPaneWidth(kind, parseFloat(stored));
      }
    });
  }

  function bindSplitter(node, kind) {
    if (!node) {
      return;
    }
    var startX = 0;
    var startWidth = 0;
    var dragging = false;

    function finish() {
      if (!dragging) {
        return;
      }
      dragging = false;
      node.dataset.dragging = 'false';
      document.body.style.removeProperty('cursor');
      document.body.style.removeProperty('user-select');
      applyDeviceScale();
      autoFitDevice();
    }

    node.addEventListener('pointerdown', function (event) {
      if (event.button !== 0) {
        return;
      }
      dragging = true;
      startX = event.clientX;
      startWidth = paneWidth(kind);
      node.dataset.dragging = 'true';
      document.body.style.cursor = 'col-resize';
      document.body.style.userSelect = 'none';
      if (node.setPointerCapture) {
        node.setPointerCapture(event.pointerId);
      }
      event.preventDefault();
    });

    var frame = 0;
    node.addEventListener('pointermove', function (event) {
      if (!dragging) {
        return;
      }
      // RTL workspace: moving the pointer left widens the pane on the right.
      var delta = startX - event.clientX;
      applyPaneWidth(kind, startWidth + delta);
      // Re-fit the emulated device while dragging so the preview stays responsive
      // instead of jumping only after the drop.
      if (!frame) {
        frame = window.requestAnimationFrame(function () {
          frame = 0;
          applyDeviceScale();
          autoFitDevice();
        });
      }
    });

    node.addEventListener('pointerup', finish);
    node.addEventListener('pointercancel', finish);

    node.addEventListener('dblclick', function () {
      resetPaneWidth(kind);
    });

    node.addEventListener('keydown', function (event) {
      var step = event.shiftKey ? 48 : 16;
      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        applyPaneWidth(kind, paneWidth(kind) + step);
      } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        applyPaneWidth(kind, paneWidth(kind) - step);
      } else if (event.key === 'Home' || event.key === 'Escape') {
        event.preventDefault();
        resetPaneWidth(kind);
      } else {
        return;
      }
      applyDeviceScale();
      autoFitDevice();
    });
  }

  function bindSplitters() {
    bindSplitter(dom.splitRail, 'rail');
    bindSplitter(dom.splitPanel, 'panel');
    restorePaneWidths();
  }

  function bindToolbar() {
    var railToggle = document.getElementById('lbRailToggle');
    if (railToggle) {
      railToggle.addEventListener('click', function () {
        togglePane('rail');
      });
    }
    var panelToggle = document.getElementById('lbPanelToggle');
    if (panelToggle) {
      panelToggle.addEventListener('click', function () {
        togglePane('panel');
      });
    }

    dom.publishBtn.addEventListener('click', openPublishDialog);
    dom.historyBtn.addEventListener('click', openHistoryDialog);
    dom.undoBtn.addEventListener('click', undo);
    dom.redoBtn.addEventListener('click', redo);

    dom.selectBtn.addEventListener('click', function () {
      setSelectMode(!state.selectMode);
    });

    dom.focusBtn.addEventListener('click', function () {
      state.focusMode = !state.focusMode;
      document.documentElement.classList.toggle('lb-focus-mode', state.focusMode);
      dom.focusBtn.setAttribute('aria-pressed', state.focusMode ? 'true' : 'false');
      window.requestAnimationFrame(function () {
        syncStudioHeight();
        applyDeviceScale();
      });
    });

    dom.moreBtn.addEventListener('click', function (event) {
      event.stopPropagation();
      var isOpen = !dom.moreMenu.hidden;
      dom.moreMenu.hidden = isOpen;
      dom.moreBtn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    });

    document.addEventListener('click', function (event) {
      if (!dom.moreMenu.hidden && !dom.moreMenu.contains(event.target) && event.target !== dom.moreBtn) {
        dom.moreMenu.hidden = true;
        dom.moreBtn.setAttribute('aria-expanded', 'false');
      }
    });

    dom.moreMenu.addEventListener('click', function (event) {
      var btn = event.target.closest('[data-action]');
      if (!btn) {
        return;
      }
      dom.moreMenu.hidden = true;
      dom.moreBtn.setAttribute('aria-expanded', 'false');
      var action = btn.dataset.action;
      var section = sectionsById[state.sectionId];

      if (action === 'reset-section') {
        confirmDialog(
          'بازنشانی این بخش',
          'همه فیلدهای «' + (section ? section.title : '') + '» در پیش‌نویس به مقدار پیش‌فرض برگردند؟',
          function () {
            runAction('reset_section', { section: section.path || 'pageOrder' }, 'بخش بازنشانی شد.');
          }
        );
      } else if (action === 'reset-all') {
        confirmDialog('بازنشانی همه‌ی فیلدها', 'تمام محتوای صفحه اصلی در پیش‌نویس به حالت اولیه برمی‌گردد. سایت تا انتشار تغییر نمی‌کند.', function () {
          runAction('reset_all', {}, 'همه فیلدها بازنشانی شدند.');
        });
      } else if (action === 'import') {
        openImportDialog();
      } else if (action === 'export') {
        downloadExport();
      } else if (action === 'discard') {
        confirmDialog('دور ریختن پیش‌نویس', 'پیش‌نویس فعلی حذف و آخرین نسخه منتشرشده بازیابی می‌شود.', function () {
          runAction('discard', {}, 'پیش‌نویس دور ریخته شد.');
        });
      } else if (action === 'shortcuts') {
        openShortcutsDialog();
      } else if (action === 'select-mode') {
        setSelectMode(!state.selectMode);
      } else if (action === 'focus-mode') {
        dom.focusBtn.click();
      } else if (action === 'history') {
        openHistoryDialog();
      }
    });

    document.querySelectorAll('[data-lb-close]').forEach(function (node) {
      node.addEventListener('click', hideStudioModal);
    });

    document.querySelectorAll('.lb-device-switch button').forEach(function (btn) {
      btn.addEventListener('click', function () {
        state.devicePicked = true;
        state.device = btn.dataset.device;
        dom.previewStage.dataset.device = state.device;
        document.querySelectorAll('.lb-device-switch button').forEach(function (other) {
          other.classList.toggle('is-active', other === btn);
        });
        applyDeviceScale();
      });
    });

    document.getElementById('lbReloadPreviewBtn').addEventListener('click', loadPreview);

    var zenBtn = document.getElementById('lbZenBtn');
    if (zenBtn) {
      zenBtn.addEventListener('click', function () {
        var zen = root.dataset.zen !== 'true';
        root.dataset.zen = zen ? 'true' : 'false';
        zenBtn.setAttribute('aria-pressed', zen ? 'true' : 'false');
        zenBtn.title = zen ? 'بازگشت به حالت ویرایش' : 'بزرگ کردن پیش‌نمایش';
        window.setTimeout(applyDeviceScale, 60);
      });
    }
    document.getElementById('lbPreviewUrl').textContent = BOOT.endpoints.preview;

    document.querySelectorAll('.lb-mobile-switch [data-mobile-view]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        root.dataset.mobileView = btn.dataset.mobileView;
        syncMobileSwitch(btn.dataset.mobileView);
        // The pane was display:none until now, so its measurements are stale.
        window.requestAnimationFrame(function () {
          syncStudioHeight();
          applyDeviceScale();
        });
      });
    });

    dom.searchInput.addEventListener('input', function () {
      renderSearchResults(dom.searchInput.value);
    });

    dom.previewFrame.addEventListener('load', function () {
      dom.previewLoading.hidden = true;
      state.previewReady = true;
      window.setTimeout(function () {
        pushPreview(true);
      }, 120);
    });

    var resizeTimer = null;
    window.addEventListener('resize', function () {
      window.clearTimeout(resizeTimer);
      resizeTimer = window.setTimeout(function () {
        syncStudioHeight();
        applyDeviceScale();
      }, 140);
    });

    document.addEventListener('keydown', function (event) {
      var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
      var mod = isMac ? event.metaKey : event.ctrlKey;

      if (mod && event.key.toLowerCase() === 's') {
        event.preventDefault();
        saveDraft();
        return;
      }
      if (mod && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        dom.searchInput.focus();
        return;
      }
      if (mod && event.key.toLowerCase() === 'z') {
        event.preventDefault();
        if (event.shiftKey) {
          redo();
        } else {
          undo();
        }
        return;
      }
      if (event.key === 'Escape') {
        if (!dom.modalHost.hidden) {
          hideStudioModal();
        } else if (state.selectMode) {
          setSelectMode(false);
        } else if (!dom.moreMenu.hidden) {
          dom.moreMenu.hidden = true;
        }
        return;
      }
      var typing = /^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName);
      if (typing) {
        return;
      }
      if (event.key.toLowerCase() === 'v') {
        setSelectMode(!state.selectMode);
      }
      if (event.key.toLowerCase() === 'f') {
        dom.focusBtn.click();
      }
    });

    window.addEventListener('beforeunload', function (event) {
      if (state.pending.length > 0) {
        event.preventDefault();
        event.returnValue = '';
      }
    });
  }

  /* ---------------------------------------------------------------- boot  */

  function boot() {
    dom.previewStage.dataset.device = 'desktop';
    root.dataset.mobileView = 'editor';
    renderRail();
    renderPanel();
    renderStatus();
    restorePaneState();
    bindToolbar();
    bindSplitters();
    syncStudioHeight();
    applyDeviceScale();
    loadPreview();

    // Restore the previously edited section (per-browser convenience only).
    try {
      var remembered = window.localStorage.getItem(STORE_KEY);
      if (remembered && sectionsById[remembered]) {
        state.sectionId = remembered;
        renderRail();
        renderPanel();
      }
    } catch (e) {
      /* storage disabled */
    }

    window.addEventListener('pagehide', function () {
      try {
        window.localStorage.setItem(STORE_KEY, state.sectionId);
      } catch (e) {
        /* ignore */
      }
    });

    Array.prototype.forEach.call(dom.railList.querySelectorAll('.lb-rail-item'), function (node) {
      node.addEventListener('click', function () {
        try {
          window.localStorage.setItem(STORE_KEY, node.dataset.sectionId);
        } catch (e) {
          /* ignore */
        }
      });
    });
  }

  boot();
})();
