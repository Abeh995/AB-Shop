# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.32.3 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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

## 1.34.1 — 2026-10-07

### Fix Admin UI Kit Undefined Auth Function

- **UI Kit Controller Auth Guard (`app/controllers/admin/ui_kit.php`)**:
  - Removed undefined `requireLogin()` function call that triggered a fatal PHP error and HTTP 500 response during route inspection. The controller now exclusively relies on `requireAdmin()` defined in `app/core/auth.php`.

## 1.34.0 — 2026-10-07

### Admin Component Library v2 & UI Kit Showcase (Phase 2)

- **Reusable Presentation Component Library (`views/admin/components/`, `assets/css/admin-components.css`)**:
  - Implemented 13 new server-side presentation components and modernized 5 existing components under strict layer boundaries (0 SQL, 0 `$_POST` mutations): `button`, `form_field`, `card`, `page_header`, `toolbar`, `pagination`, `data_table`, `alert`, `modal`, `drawer`, `savebar`, `definition_list`, `progress`, `kpi_card`, `kpi_grid`, `badge`, `empty_state`, `nav_tabs`.
  - Added `render_component(string $name, array $props)` helper to `app/core/functions.php` for string buffering.
  - Rewrote `assets/css/admin-components.css` inside `@layer components` using 100% tokens and RTL logical properties with 0 raw hex codes, 0 `:root` overrides, and 0 `!important`.
- **Intrinsic Responsiveness & Container Query Model**:
  - Established inline container queries on `.ab-card`, `.ab-table-wrap`, `.ab-kpi-card`, and `.ab-split`.
  - Implemented `data_table` dual responsiveness: `data-mode="stack"` (turns rows into cards with `data-label` captions below 40rem container width) and `data-mode="scroll"` (sticky first column for financial data) with 3-tier priority column hiding.
- **Native HTML5 Dialogs & Split View Interaction (`assets/js/ab-kit.js`)**:
  - Upgraded modals and side drawers to native `<dialog>` elements rendered in browser top layer with auto-closing backdrop support, maintaining backward compatibility for legacy `.admin-modal` structures.
  - Implemented `AB.split` master-detail layout engine (48 lines) with declarative switching (`data-split-view`, `data-ab-split-select`, `data-ab-split-back`).
- **Interactive UI Kit Living Style Guide (`admin/ui-kit.php`, `app/controllers/admin/ui_kit.php`, `views/admin/ui-kit.php`)**:
  - Deployed comprehensive living style guide and edge-case stress test workstation at `/admin/ui-kit.php`, accessible to authenticated admins and linked from Diagnostics.
  - Showcases all components across default, disabled, loading, invalid, empty, long unbroken token, long RTL Persian text, and multi-column states.
- **Architectural Documentation & Verification Quality Gate**:
  - Documented complete component contracts, properties, and usage examples in `docs/COMPONENTS.md`.
  - Updated `docs/DESIGN.md` Admin UI migration status table and CSS layering architecture.
  - Enhanced `tools/verify.php` with `ui_kit_coverage` guard ensuring 100% component coverage in the UI Kit.

## 1.33.0 — 2026-10-05

### Admin Design System Foundation: CSS Layers, Tokens, Reset, Shell & Breakpoint Unification (Phase 1)

- **Cascade Layers Architecture & Single Source of Truth Tokens (`assets/css/admin-tokens.css`)**:
  - Established CSS `@layer reset, tokens, base, layout, components, patterns, pages, utilities;` enforcing strict cascade precedence.
  - Built unified token system defining color primitives (`--c-mocha-*`, `--c-latte-*`, `--c-blue-*`, `--c-emerald-*`, etc.) as the exclusive location for raw hex codes across the entire admin panel.
  - Implemented semantic surfaces, borders, shadows, fluid typography clamps (`--text-xs` to `--text-xl`), density variables, and tone systems (`brand`, `blue`, `sky`, `emerald`, `amber`, `rose`, `purple`, `teal`, `slate`).
  - Added complete backward-compatibility legacy alias mapping for all 15 historical `:root` blocks, ensuring 0 visual regressions for unmigrated pages.
- **Admin CSS Reset & Storefront Decoupling (`assets/css/admin-base.css`, `views/admin/layout/header.php`, `views/admin/login.php`)**:
  - Implemented `@layer reset, base` with box-sizing, form control font inheritance, focus rings, and iOS focus zoom guard (`font-size: max(16px, 1rem)`).
  - Aliased legacy UI classes (`:is(.ab-btn, .btn)`, `:is(.ab-field, .form-group)`, `:is(.ab-input, .form-control)`, `:is(.ab-alert, .alert)`), completely severing the admin panel's dependency on the storefront's `style.css`.
  - Added `viewport-fit=cover` to admin layout and login viewport headers for full iOS safe-area compliance.
- **Admin Layout Shell Extraction & Container Gutter (`assets/css/admin-shell.css`, `views/admin/layout/header.php`, `views/admin/layout/footer.php`)**:
  - Extracted 1,234 lines of sidebar, collapsed rail, off-canvas drawer, topbar, search dropdown, and bottom navigation bar (BNB) rules out of `admin.css` into `@layer layout` in `admin-shell.css`.
  - Eliminated arbitrary body hacks (`body:not(.admin-page-dashboard):not(.admin-page-orders) ...`) by introducing the `.ab-page` container wrapper with fluid responsive gutters (`clamp(.75rem, 2.5vw, 2rem)`).
  - Wired dynamic `$pageTone` derivation across navigation groups in `views/admin/layout/nav_config.php` and emitted `data-tone` on `.ab-page`.
- **Responsive Breakpoint Unification (`assets/js/ab-kit.js`, `assets/js/admin.js`, `assets/css/admin-shell.css`)**:
  - Unified fragmented breakpoint values (768px / 900px) to standardized shell breakpoints (`sm: 640px / 40rem`, `md: 1024px / 64rem`, `lg: 1440px / 90rem`).
  - Added `AB.bp = { sm: 640, md: 1024, lg: 1440 };` to `ab-kit.js` and replaced all literal breakpoint comparisons in `admin.js`.
- **Admin Utility Classes & Patterns Placeholder (`assets/css/admin-utilities.css`, `assets/css/admin-patterns.css`)**:
  - Introduced standard layout utilities (`.ab-stack`, `.ab-cluster`, `.ab-grid`, `.ab-sr-only`, `.ab-nowrap`, `.ab-num`) in `@layer utilities`.
  - Created placeholder `admin-patterns.css` for Phase 2 page archetypes.
- **Ratchet Baseline Quality Gate Expansion (`tools/verify.php`, `tools/verify-baseline.json`)**:
  - Expanded `tools/verify.php` with `bp_sync` (ensuring `AB.bp` in JS matches `admin-tokens.css`), `component_registry` (ensuring all components exist and are documented), and ratchet metrics for `css_media_page`, `css_important`, `css_transition_all`, `css_physical_dir`, `view_inline_style`, and `view_legacy_class`.
  - Excluded `admin-tokens.css` from `css_root`/`css_hex` checks as the single source of truth, and updated `tools/verify-baseline.json`.

## 1.32.4 — 2026-10-05

### Admin UI Modernization Safety Net, Playwright Audit Harness & Baseline Infrastructure (Phase 0)

- **Developer UI Audit Harness (`tools/ui-test/`)**:
  - Implemented dev-only Playwright test runner (`tools/ui-test/audit.mjs`) executing responsive layout, RTL overflow, tap target, and console error checks across 10 viewports and 4 font-scale variations.
  - Added full admin page inventory configuration (`tools/ui-test/pages.json`) covering all 32 admin routes and parameterized edit/detail pages.
  - Built automated credential handling and session caching (`.auth/state.json`) with support for environment variables and gitignored local parameter overrides (`pages.local.json`).
  - Added comprehensive audit documentation (`tools/ui-test/README.md`) and pre-Phase 1 baseline recording instructions (`tools/ui-test/baseline/README.md`).
- **Production Isolation & Hosting Constraints Governance (`AGENTS.md`, `.gitignore`, `tools/build-deploy.ps1`)**:
  - Documented explicit hosting exception in `AGENTS.md` for the dev-only Node harness, preserving the zero-build-step invariant for production shared hosting.
  - Ignored `tools/ui-test/node_modules/`, `tools/ui-test/output/`, `tools/ui-test/.auth/`, and `tools/ui-test/pages.local.json` in `.gitignore`.
  - Confirmed strict exclusion of `tools/` from production deploy zip archives via `tools/build-deploy.ps1`.
- **Admin Design System Baseline Tracking (`docs/DESIGN.md`)**:
  - Established quantitative Phase 0 baseline metrics table in `docs/DESIGN.md` recording 17,374 CSS lines across 16 files, 1,279 raw hex color codes, 1,378 inline styles, ~50 media query breakpoints, and 0/32 migrated pages.

## 1.32.3 — 2026-10-04

### Admin Workstation Logic Stability, SearchService Database Alignment & Script Load Order Fixes

- **Database Column Alignment in SearchService (`app/services/SearchService.php`)**:
  - Fixed database column mismatches in `SearchService::searchOrders()`: replaced invalid `customer_phone` with `phone` and `total_amount` with `total`.
  - Prevented fatal PDO SQL exceptions and restored live order search results across the admin header and dashboard.
- **Client Toast Polymorphism & Copy Handlers (`assets/js/ab-kit.js`, `assets/js/admin-c2c.js`)**:
  - Bound `AB.toast.show = AB.toast` to provide polymorphic method-chaining and prevent `TypeError: AB.toast.show is not a function`.
  - Updated card-to-card inspection workstation copy action to invoke `AB.toast` seamlessly.
- **Navigation Tabs Engine & Backward Compatible Classes (`views/admin/components/nav_tabs.php`, `assets/js/ab-kit.js`)**:
  - Restored explicit `btnClass => 'settings-tab-btn'` in `settings_partials/_nav_tabs.php` and `'btnClass' => 'diag-tab-btn'` in `diagnostics_partials/_nav_tabs.php`.
  - Added smart fallback class derivation in `views/admin/components/nav_tabs.php` based on parent navigation container classes.
  - Implemented declarative `AB.tabs` navigation engine in `assets/js/ab-kit.js` with automated pane switching and URL hash synchronization.
- **Script Lifecycle & Zero Race Conditions (`views/admin/layout/header.php`, `views/admin/layout/footer.php`)**:
  - Enqueued `assets/js/ab-kit.js` early in `<head>` via `views/admin/layout/header.php`, guaranteeing `window.AB`, `showToast`, and UI helpers exist before any page body or script executes.
  - Removed duplicate script inclusion from `views/admin/layout/footer.php`.
- **Modal Lifecycle & State Cleanup (`views/admin/tags.php`)**:
  - Restored `onclick="closeTagModal()"` on modal dismiss and cancel actions in `tags.php` to clean URL query parameters (`?edit=`) upon dismissal.

