# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.33.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

## 1.34.6 — 2026-10-16

### Admin UI Pilot Migration — users.php (Phase 3)

- **Pilot Page Full Migration (`views/admin/users.php`, `views/admin/users_partials/`)**:
  - Rebuilt master view into the List Archetype (`.ab-pattern-list`) using AB-Kit v2 design tokens and components.
  - Replaced legacy KPI cards with Bento `component('kpi_grid')` featuring tone-colored cards (`slate`, `amber`, `emerald`, `sky`) with accent stripes.
  - Replaced legacy toolbar with `component('toolbar')` featuring quick filter chips (`.ab-chip`) and unified search.
  - Migrated admin users table to `component('data_table')` in `mode => 'stack'` with column priority hierarchy (`data-priority`), custom avatar bubbles (`.ab-avatar`), semantic badges, and stacked card view at `< 40rem`.
  - Migrated audit trail table to `component('data_table')` in `mode => 'scroll'` with responsive horizontal scrolling and sticky headers.
  - Migrated Create Admin, Edit Admin, and Change Password modals to native `<dialog class="ab-modal">` via `component('modal')` and `component('form_field')`.
  - Achieved strictly 0 inline styles (`style=""`) and 0 inline `<script>` tags across all users views.
- **Page Stylesheet Deletion & CSS Debt Reduction (`assets/css/admin-users.css`, `views/admin/layout/header.php`)**:
  - Completely deleted `assets/css/admin-users.css` (-530 lines of CSS, -17 raw hex color codes, and -1 competing `:root` palette).
  - Removed `'users.php'` entry from `$pageCssMap` in admin header.
- **Workspace Client Script (`assets/js/admin-users.js`, `app/controllers/admin/users.php`)**:
  - Encapsulated live table search and role/status filtering into external `assets/js/admin-users.js` passed via `$pageScripts`.
  - Implemented delegated modal openers with JSON payload parsing and maintained backward-compatible global helpers (`window.openCreateModal`, `window.openEditModal`, `window.openPasswordModal`, `window.closeAllModals`).
- **Shared Component Library & UI Kit Expansion (`assets/css/admin-components.css`, `views/admin/ui-kit.php`)**:
  - Added `.ab-avatar` base component with size variants (`.ab-avatar--sm`, `.ab-avatar--lg`) and user initials presentation.
  - Added `.ab-inline-form` utility for table action forms and `.ab-icon-btn.ab-btn--danger` styling.
  - Added User Avatars showcase section to `views/admin/ui-kit.php`.
- **Automated Verification & Quality Gate (`tools/ui-test/test-users-interaction.mjs`, `tools/verify.php`)**:
  - Validated 0 console errors and 0 page horizontal overflows across all 14 viewports in the Playwright audit.
  - Verified 100% success on end-to-end interactive suite: modal open/close, field population, non-destructive form submission with flash feedback, quick filter chips, and live search.
  - Locked 13 ratcheted metric reductions in `tools/verify-baseline.json`.

## 1.34.5 — 2026-10-16

### Admin UI Stabilization Gate & Responsive Audit Baseline (Phase 2.5b)

- **Mobile Shell & Nav Regression Resolution (`assets/css/admin.css`)**:
  - Aligned legacy topbar `.dash-topbar` z-index from unlayered `100` to `var(--z-sticky)`. Previously, `z-index: 100` hovered above the mobile off-canvas drawer (`var(--z-overlay): 60`) and intercepted click and touch interactions on `#adminSidebarCloseBtn`.
  - Verified mobile hamburger menu drawer toggle (`#adminMobileMenuBtn`), sidebar slide-out animation, backdrop dismiss, and bottom navigation bar (`.admin-bottom-nav`) across mobile viewports.
- **Full Responsive Audit & Baseline Analysis (`tools/ui-test/baseline/after-phase2.md`, `tools/ui-test/audit.mjs`)**:
  - Executed automated Playwright audit across all 34 admin pages × 14 viewports (476 total assertions) with 0 HTTP 4xx/5xx errors and 0 navigation timeouts.
  - Confirmed 4 clean foundation pages with 0 horizontal scroll overflow (`ui_kit`, `dashboard`, `inventory_valuation`, `login`).
  - Diagnosed and cataloged horizontal scroll overflow across 30 legacy unmigrated pages, establishing the ranked, prioritized migration queue for Phase 3 (Pilot) and Phase 4 (Batch Migrations).
  - Increased navigation timeout in test harness to 25s for resilient execution across asset-heavy views.

## 1.34.4 — 2026-10-16

### UI Kit Full Audit Matrix & Shared Component Stabilization (Phase 2.5a)

- **UI Kit Master Showcase Expansion (`views/admin/ui-kit.php`, `app/controllers/admin/ui_kit.php`)**:
  - Added interactive triggers and dialog instances for all 4 modal sizes (`sm`, `md`, `lg`, `xl`) and bilateral drawer directions (`end` left-side and `start` right-side drawer with filter form).
  - Implemented interactive tab panels (`#pane-all`, `#pane-buttons`, `#pane-tables`, `#pane-overlays`) demonstrating real-time panel switching with zero inline JavaScript.
  - Implemented non-destructive demo form `#uiKitDemoForm` with CSRF protection, POST validation, success toast flash notification, and clean redirect.
- **Component Prop Scope Collision Fix (`app/core/functions.php`)**:
  - Resolved PHP variable collision in `component()` and `render_component()` where local parameter `$name` masked the `$props['name']` passed to components under `EXTR_SKIP`.
  - Renamed internal variables to `$_componentName`, `$_componentProps`, and `$_componentFile` and applied `EXTR_OVERWRITE`, guaranteeing form inputs receive their specified field names.
- **KPI Card Look Alignment (`assets/css/admin-components.css`)**:
  - Added tone-driven top accent stripe (`.ab-kpi-card::before`, 3px height) and subtle hover lift/shadow elevation to match the signature aesthetic of the Expenses workstation cards.
- **Mobile Touch Target & Legibility Standards (`assets/css/admin-shell.css`, `assets/css/admin-components.css`)**:
  - Enforced 44x44px minimum tap targets on mobile hamburger trigger (`.admin-mobile-menu-btn`), mobile storefront button (`.btn-dash-store-compact`), and bottom navigation items (`.admin-bottom-nav-item`).
  - Added `min-height: var(--control-h)` to `.ab-tab-btn` ensuring WCAG compliance across coarse pointer touchscreens.
  - Upgraded sub-12px dates and separators in topbar to `var(--text-xs)`.
- **Navigation Tabs Engine Sync (`assets/js/ab-kit.js`, `assets/css/admin-components.css`)**:
  - Added standard `.ab-tab-pane, [data-tab-pane] { display: none; }` and `.ab-tab-pane.active, [data-tab-pane].active { display: block; }` classes.
  - Added `.ab-tab-pane` to `AB.tabs.activate` panel query selector.
- **Playwright Audit Harness Polish (`tools/ui-test/audit.mjs`)**:
  - Refined tap target check to recognize parent `<label>` dimensions for checkboxes and radio switches per WCAG 2.5.5 / 2.5.8.
  - Confirmed 0 critical failures, 0 horizontal scroll overflows, and 0 console errors across all 14 viewports for the UI Kit.

## 1.34.3 — 2026-10-11

### Admin Mobile Touch Targets & Playwright Audit Harness Resiliency

- **Admin Mobile Header Touch Target Refinements (`assets/css/admin-shell.css`)**:
  - Enlarged mobile hamburger drawer button (`.admin-mobile-menu-btn`) dimensions to `40x40px` (`min-width: 40px; min-height: 40px;`), providing an ergonomic touch target within the 54px header.
  - Enlarged compact storefront header link (`.btn-dash-store-compact`) on narrow mobile screens (`< 40rem`) to `38x38px` with circular boundary preservation.
  - Standardized `.admin-search-input-wrap` with `min-height: 38px` and full-height inner input for seamless touch interaction.
- **Playwright Responsive Audit Harness Polish (`tools/ui-test/audit.mjs`)**:
  - Scoped authentication session storage states by hostname and username (`state_${host}_${user}.json`), with cookie domain validation to eliminate redirect loops when switching test base URLs.
  - Implemented automatic re-authentication recovery upon encountering login redirects on authenticated test routes.
  - Added Chromium headless launch flags (`--disable-dev-shm-usage`, `--no-sandbox`, `--disable-gpu`) and guarded browser/context lifecycles to prevent crashes on high-resolution viewports (4K / 3840x2160).
  - Resolved false-positive `stickyBarOverlap` check by excluding child elements of fixed/sticky bottom bars (`.ab-savebar`, `.admin-bottom-nav`) from being measured as obscured background content.
  - Added flexible route matching for CLI `--pages` arguments, supporting trailing `.php`, paths, and hyphen/underscore variations.

## 1.34.2 — 2026-10-11

### Admin Mobile Navigation Shell, DataTable Scroll Zoom Fix, Split View Polish & Native Dialog Transitions

- **Admin Responsive Navigation Shell (`assets/css/admin-shell.css`, `assets/js/admin.js`, `views/admin/layout/header.php`, `views/admin/layout/nav_config.php`)**:
  - Added accessible topbar mobile hamburger toggle button (`#adminMobileMenuBtn`) for viewports below `1024px` (`width < 64rem`).
  - Implemented off-canvas drawer slide-out with backdrop overlay, keyboard `Escape` handler, and outside click dismiss for the sidebar on mobile and tablet devices.
  - Resolved mobile isolation bug on subpages and workstation pages where Bottom Navigation Bar (BNB) is omitted.
- **DataTable Horizontal Scroll & Priority Preservation (`assets/css/admin-components.css`)**:
  - Scoped column priority suppression (`[data-priority="3"]` and `[data-priority="2"]`) strictly to non-scroll modes (`.ab-table-wrap:not([data-mode="scroll"])`).
  - Guaranteed `width: max-content; min-width: 100%` and `white-space: nowrap` in `data-mode="scroll"`, preserving all columns (e.g. 12-column matrix tables) with horizontal scrolling under narrow containers and high zoom levels (110% to 200%).
  - Maintained sticky first column (`inset-inline-start: 0`) in RTL with negative directional border shadow (`-1px 0 0 var(--border)`).
- **Dual-Pane Workstation Polish (`assets/css/admin-components.css`, `views/admin/ui-kit.php`, `docs/COMPONENTS.md`)**:
  - Enhanced `.ab-split` with dedicated `.ab-split__list` scroll container and structured workstation item `.ab-split__item` featuring an active indicator bar (`border-inline-start: 3px solid var(--tone)`), header badges, and meta rows.
  - Structured `.ab-split__pane` with dedicated action toolbar, section cards, and definition list layouts.
  - Preserved fluid container query collapse (`@container ab-split (width < 48rem)`) with single-pane mobile back button navigation.
- **Native HTML5 Dialog Transitions & Order Drawer (`assets/css/admin-components.css`, `views/admin/ui-kit.php`, `docs/COMPONENTS.md`)**:
  - Added 60fps GPU-accelerated dialog and backdrop blur animations using modern CSS `@starting-style` and discrete transition behavior (`allow-discrete`).
  - Added mobile bottom sheet slide transitions for modals and side drawer modular classes (`.ab-drawer__section`, `.ab-drawer__timeline`, `.ab-drawer__item-list`).
  - Implemented comprehensive order dossier drawer demo with multi-step timeline, customer card, items breakdown, and postal details.




