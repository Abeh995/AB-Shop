# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.33.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

## 1.35.1 — 2026-10-16

### Admin UI Migration — products.php (Phase 4a)

- **Products Catalog Full Migration (`views/admin/products.php`, `views/admin/products_partials/`)**:
  - Rebuilt master products catalog workstation to the List Archetype (`.ab-pattern-list`) with strictly 0 inline styles (`style=""`) and 0 executable inline scripts.
  - Decomposed view into 5 presentation-only partials: Bento KPI metrics (`_kpis.php`), filter toolbar and active chips (`_toolbar.php`), data table with stacked responsiveness (`_table.php`), floating bulk actions bar (`_bulk_bar.php`), and side dossier inspect drawer (`_drawer_inspect.php`).
  - Implemented 4 Bento KPI cards using `component('kpi_card')` mapped with domain tones (`purple`, `amber`, `rose`, `emerald`).
  - Converted catalog table to `component('data_table')` in `mode => 'stack'` with selectable checkboxes, sticky header, column priority hierarchy (1-3), thumbnail preview button, SKU monospace chip, formatted prices/margins, stock badges, and action clusters.
  - Replaced legacy drawer with native `<dialog id="prodDrawer" class="ab-drawer" data-side="end" data-ab-modal>` featuring financial telemetry metric boxes, variant stock editor with steppers, and gallery thumbnails.
  - Replaced legacy bulk action bar with floating pill (`#prodBulkBar`) hidden by default and smoothly revealed upon row selection.
- **Stylesheet Debt Reduction (`assets/css/admin-products.css`)**:
  - Shrunk from 1,408 lines to 430 lines (-978 lines of legacy code).
  - Eliminated 115 raw hex color codes, mapping 100% of styles to CSS design tokens.
  - Eliminated competing `:root` palette block and page-level `@media` queries.
  - Wrapped 100% in `@layer pages` using RTL logical properties.
  - Added horizontal scroll containment for global subnav strip, eliminating `laptop_font200` overflow.
  - Preserved `.tag-tokenizer-*` classes required by `product_edit.php` until Phase 4c.
- **Bug Fixes & Architectural Polish**:
  - **Shared Pagination Helper & Per-Page Normalization (`app/core/functions.php`, `app/controllers/admin/products.php`, `views/admin/products_partials/_table.php`)**:
    - Root-caused pagination bug where unselected `per_page` evaluated to `0`, triggering service-level clamping to `LIMIT 1` and creating 1 row per page across the catalog.
    - Extracted SSoT helper `getPaginationParams(array $allowedPerPage, int $defaultPerPage = 20): array` in `app/core/functions.php` to safely parse and sanitize pagination input across controllers.
    - Preserved `per_page` query parameter in table pagination links to prevent resetting per-page preferences when browsing pages.
  - **Legacy Card Background & Publication Card Static Flow (`assets/css/admin-components.css`, `views/admin/product_edit.php`)**:
    - Restored `.admin-card` legacy alias in `assets/css/admin-components.css` (`:is(.ab-card, .admin-card)`) with `background: var(--surface)`, borders, and default padding, eliminating transparency across legacy admin forms.
    - Removed legacy inline `style="position: sticky; ..."` from the publication card in `views/admin/product_edit.php` so it scrolls naturally in the normal document flow alongside the rest of the form.
- **Client Script Modernization (`assets/js/admin-products.js`)**:
  - Modernized drawer interactions to use native `AB.modal.open('prodDrawer')` and `AB.modal.close('prodDrawer')`.
  - Replaced alert/toast implementations with global `showToast()` and `AB.fmt.faDigits()`.
  - Preserved all existing JS hooks (`#prodBulkBar`, `.btn-inspect`, `.prod-sku-chip`, steppers, and global `window.openProductDrawer` / `window.closeProductDrawer`).
- **Quality Gates & Verification (`tools/ui-test/test-products-interaction.mjs`, `tools/verify.php`)**:
  - Confirmed HTTP 200 and zero console errors across all viewports.
  - Validated interactive functionality: drawer open/step/close, row selection, bulk bar toggle, filter form submission, SKU clipboard copy with toast.
  - Playwright visual audit passed on all 14 responsive viewports with zero horizontal overflow.
  - Locked ratcheted debt reductions in `tools/verify-baseline.json`.

## 1.35.0 — 2026-10-16

### Admin UI Migration — coupons.php & tags.php (Phase 3)

- **Coupons Management Full Migration (`views/admin/coupons.php`, `views/admin/coupons_partials/`)**:
  - Rebuilt coupons workstation to the List Archetype (`.ab-pattern-list`) using AB-Kit v2 tokens and components, inheriting the `purple` domain tone.
  - Converted KPI statistics to a responsive Bento grid (`_kpis.php`) via `component('kpi_card')` mapped with domain tones (`emerald`, `purple`, `rose`, `sky`).
  - Implemented modern toolbar (`_toolbar.php`) with live status filter chips (`ab-chip`) and instant client-side table search (`data-ab-filter-target`).
  - Migrated coupons table (`_table.php`) to `component('data_table')` in `mode => 'stack'` with column priority hierarchy, status badges, custom coupon code styling (`.ab-code`), 1-click clipboard copy, and visual usage meter via `render_component('progress')`.
  - Converted create/edit coupon modal (`_modal.php`) to native `<dialog id="couponModal" class="ab-modal ab-modal--lg">` with `.ab-form-grid`, random code generator, and full CSRF protection.
  - Converted coupon usage analytics drawer (`_drawer_stats.php`) to native `<dialog id="couponDrawer" class="ab-drawer">` with `.ab-metric-box` stats grid and AJAX order history table.
  - Achieved strictly 0 inline styles (`style=""`) and 0 inline scripts.
- **Tags Management Full Migration (`views/admin/tags.php`)**:
  - Rebuilt tags view to the List Archetype (`.ab-pattern-list`) with `component('page_header')` and a 3-card KPI grid (`kpi_card`).
  - Migrated tags table to `component('data_table')` in `mode => 'stack'` with column priorities 1-3, `.ab-code` slug badges (`dir="auto"`), and action button clusters.
  - Converted tag create/edit modal to native `<dialog id="tagModal" class="ab-modal">` with clean form validation.
  - Achieved strictly 0 inline styles (`style=""`) and 0 inline scripts.
- **Page Stylesheet Deletion & CSS Debt Reduction (`assets/css/admin-coupons.css`, `views/admin/layout/header.php`)**:
  - Completely deleted `assets/css/admin-coupons.css` (-532 lines of legacy CSS, -44 raw hex color codes, and -1 competing `:root` palette block).
  - Removed `'coupons.php'` and `'tags.php'` entries from `$pageCssMap` in admin header.
- **Client Script Modularization (`assets/js/admin-coupons.js`, `assets/js/admin-tags.js`)**:
  - Created standalone `admin-coupons.js` handling discount type toggling (fixed/percent/free_shipping), AJAX random code generator, 1-click copy with `AB.toast`, and AJAX stats drawer loading.
  - Created standalone `admin-tags.js` handling delegated tag modal opening, form population, and query-string cleanup (`?edit=`).
- **Shared Component Library & UI Kit Expansion (`assets/css/admin-components.css`, `views/admin/ui-kit.php`)**:
  - Added `.ab-code` and `.ab-code--accent` monospace token presentation with RTL-safe font fallbacks.
  - Added `.ab-metric-box` compact tile component for side drawers and modal telemetry.
  - Added `white-space: nowrap;` to `.ab-badge` to prevent Persian word wrapping in compact table cells.
  - Added Monospace Tokens and Metric Box showcases to `views/admin/ui-kit.php`.
- **Quality Gates & Playwright Verification (`tools/ui-test/test-coupons-tags-interaction.mjs`, `tools/verify.php`)**:
  - Tested HTTP 200 and 0 console errors across all 14 responsive viewports.
  - Validated 100% interactive pass: coupon modal create/edit, type toggle, AJAX code generation, copy toast, analytics drawer fetch, tag modal create/edit, and search filtering.
  - Locked 18 ratcheted metric reductions in `tools/verify-baseline.json`.

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
