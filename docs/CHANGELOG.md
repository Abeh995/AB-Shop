# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.32.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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

## 1.32.2 — 2026-10-04

### AB-Kit Front-End Framework, Presentation Components Engine & Comprehensive DRY/SSoT Refactoring

- **Unified Front-End Library (`assets/js/ab-kit.js`)**:
  - Implemented the canonical client kit providing Single Source of Truth for formatters (`AB.fmt.faDigits`, `AB.fmt.price`, `AB.fmt.bytes`, `AB.fmt.esc`, `AB.fmt.normalizeText`).
  - Built unified Toast Notification Center (`AB.toast` / `window.showToast`) with CSS transitions and accessible ARIA attributes.
  - Implemented declarative modal system (`AB.modal` via `data-ab-modal-open` / `data-ab-modal-close`) and live client-side table filter (`AB.tableFilter` via `data-ab-table-filter`).
  - Added lightweight CSRF-aware AJAX fetch helper (`AB.api`).
- **Provider-Based Search Architecture (`app/services/SearchService.php`, `ajax/admin_search.php`, `AB.autocomplete`)**:
  - Extracted inline search logic into extensible `SearchService` supporting pluggable providers (`tags`, `products`, `customers`, `orders`).
  - Refactored `ajax/admin_search.php` to a lean 22-line proxy.
  - Built multi-token autocomplete and live suggestions in `ab-kit.js`.
- **Server-Side Presentation Component Engine (`app/core/functions.php`, `views/admin/components/`)**:
  - Introduced global `component(string $name, array $props)` presentation renderer.
  - Built reusable, zero-DB presentation components: `kpi_card`, `kpi_grid`, `nav_tabs`, `empty_state`, and `badge`.
  - Migrated partial templates across 9 admin workstations (`coupons`, `gift_items`, `users`, `shipping_methods`, `sms_patterns`, `appearance`, `diagnostics`, `settings`, `expenses`).
- **CSS Design Tokens & Shared Component Kit (`assets/css/admin.css`, `assets/css/admin-components.css`)**:
  - Consolidated all admin color, radius, and shadow tokens into `:root` in `admin.css`.
  - Created standalone component stylesheet `admin-components.css` enqueued globally.
  - Introduced cache-busting asset helpers `asset()` and `assetUrl()` in `app/core/functions.php` with mtime timestamping.
- **Inline Script Extraction & Ratchet Debt Paydown (`assets/js/admin-*.js`, `tools/verify-baseline.json`)**:
  - Extracted over 1,100 lines of heavy inline JavaScript into modular external scripts:
    - `assets/js/admin-orders.js` (orders table drawer, receipt modal, copy actions).
    - `assets/js/admin-c2c.js` (card-to-card inspection workstation, split modal, zoom/rotate, keyboard shortcuts).
    - `assets/js/admin-order-detail.js` (order detail gallery lightbox, swipe navigation, receipt zoom, postal label copy).
    - `assets/js/admin-product-edit.js` (variant row manager, live profit margin calculator, tag tokenizer binding).
  - Passed dynamic server data using `<script type="application/json" id="...">` data islands.
  - Reduced technical debt across 8 metrics in `tools/verify-baseline.json` via `php tools/verify.php --update-baseline`.

