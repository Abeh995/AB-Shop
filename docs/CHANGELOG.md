# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.31.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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

## 1.32.1 — 2026-10-04

### DRY / SSoT Architecture Governance, Reuse-First Protocol & Front-End Ratchet Quality Gate

- **Reuse-First Protocol & Architecture Governance (`AGENTS.md`, `.cursorrules`, `.github/copilot-instructions.md`)**:
  - Added Rule 11 (Reuse-First Protocol & Anti-Duplication Invariant DRY/SSoT) to `AGENTS.md`. Mandates consulting the component registry prior to authoring UI interactions, strictly prohibits redundant helper functions, enforces declarative admin views (`data-ab-*`), and bounds inline view scripts to <= 20 lines.
  - Enforced single source of truth for CSS tokens (forbidding competing `:root` declarations in component stylesheets) and harmonized controller soft ceiling (80 lines) and hard ceiling (120 lines) across all project documentation.
- **Single Source of Truth Component Registry (`docs/COMPONENTS.md`)**:
  - Established comprehensive UI and utility registry cataloging standard formatters (`toFaDigits`, `formatBytes`, `escapeHtml`, `formatPrice`), toast notifications, declarative modals, autocomplete tokenizers, live table filters, and image optimization tools.
- **Front-End Ratchet Quality Gate (`tools/verify.php`, `tools/verify-baseline.json`, `.githooks/pre-commit`)**:
  - Expanded `tools/verify.php` with Section 7: Anti-Duplication & Front-end Ratchet Baseline Guard. Scans for known duplicate functions, duplicate top-level JS symbols, inline script line budgets, and rogue CSS `:root` palettes.
  - Introduced `tools/verify-baseline.json` locking current legacy debt across 78 metrics; verification fails on any regression or new un-baselined duplication. Added `--update-baseline` flag for ratchet-down savings.
  - Installed local Git pre-commit hook in `.githooks/pre-commit` to prevent non-compliant commits.
- **Agent Skills Scaffolding (`.agents/skills/`, `.claude/skills/`)**:
  - Created `ab-socks-ui-kit` skill guiding front-end reuse and declarative patterns.
  - Created `ab-socks-admin-page` skill providing the Golden Path for scaffolding admin workstations.
  - Updated `ab-socks-workflow` and `ab-socks-refactor` to align with the new quality gates.

## 1.32.0 — 2026-10-04

### Product Tags Catalog Integration, Smart Tag Search & Interactive Tag Tokenizer Autocomplete

- **Catalog Tag Filtering & Smart Search (`app/services/ProductService.php`, `app/controllers/admin/products.php`, `views/admin/products.php`, `views/admin/tags.php`)**:
  - Implemented direct `tag_id` filtering in `ProductService::getProductsCatalog()` via performant subquery (`EXISTS (SELECT 1 FROM product_tags ...)`).
  - Upgraded general text search (`q`) to automatically match product tags (`tags.name` and `tags.slug`) in addition to titles and SKU codes, ensuring search queries like "نخی" or "پنبه" locate all tagged catalog items.
  - Linked the "محصولات متصل" counter in `views/admin/tags.php` directly to `/admin/products.php?tag_id=...`, eliminating empty search results.
  - Added dedicated Tag Filter dropdown in the products catalog filter toolbar and an active dismissible filter banner (`[ فیلتر برچسب فعال: «...» ✕ ]`).
- **Interactive Tag Tokenizer & Live Autocomplete Workstation (`views/admin/product_edit.php`, `assets/css/admin-products.css`)**:
  - Replaced legacy static checkboxes and plain comma-separated text input with a modern Tag Tokenizer component.
  - Built zero-latency client-side autocomplete with real-time substring matching, keyboard arrow navigation (`ArrowDown`/`ArrowUp`), Enter/comma selection, and automatic new tag token creation.
  - Integrated "پیشنهادات پرتکرار" (popular tags cloud) enabling 1-click tag assignment.
  - Enqueued `admin-products.css` on `product_edit.php` in `views/admin/layout/header.php`.

## 1.31.2 — 2026-10-04

### Product Tags Service Bootstrap Registration & Workstation Stylesheet Attachment

- **TagService Autoloading Registration (`app/bootstrap.php`)**:
  - Registered `TagService.php` in the core application bootstrap service registry (`app/bootstrap.php`), eliminating fatal `Class "TagService" not found` errors and resolving HTTP 500 crashes when accessing `/admin/tags.php`.
- **Tags Workstation Stylesheet Enqueuing (`views/admin/layout/header.php`)**:
  - Updated admin layout stylesheet loader to include `admin-coupons.css` on `tags.php` in addition to `coupons.php`, ensuring complete visual styling for Bento KPI cards, high-density matrix tables, search toolbars, and tag creation modals.

## 1.31.1 — 2026-10-04

### Desktop Navigation Viewport Lock, Orders Scroll Isolation & UX Smooth ScrollIntoView

- **Desktop Sidebar Viewport Lock & Scroll Bleed Isolation (`assets/css/admin.css`)**:
  - Pinned `.admin-sidebar` to strict `height: 100vh; height: 100dvh; max-height: 100vh; max-height: 100dvh; overflow: hidden;` eliminating page layout stretching when accordion groups are expanded.
  - Set `.admin-sidebar-nav` to `flex: 1 1 auto; min-height: 0; overscroll-behavior: contain;` allowing the navigation accordion menu to independently scroll inside the sidebar while completely preventing scroll chaining/bleeding into the global page window.
  - Adjusted desktop `.admin-main` bottom padding from legacy `95px` down to `24px` (`padding: 0 0 24px 0;`), preserving `padding-bottom: 92px` strictly for mobile screens under `@media (max-width: 900px)` for bottom navigation bar clearance.
- **Orders Workspace Body Padding Containment (`assets/css/admin-orders.css`)**:
  - Scoped the aggressive `padding-bottom: 95px` on `body.admin-page-orders` exclusively to mobile viewports (`@media (max-width: 900px)`), resetting desktop to `padding-bottom: 0`. This eliminates the window overscroll space that previously allowed sticky elements to be displaced ~95px off-screen when scrolling to the bottom of the orders table.
- **Nested Main Tag Fix (`views/admin/orders.php`)**:
  - Replaced invalid nested `<main class="dash-workspace">` with `<div class="dash-workspace">` and closed it properly before the admin layout footer, resolving DOM specification errors.
- **Active Navigation UX Enhancement (`assets/js/admin.js`)**:
  - Implemented automatic smooth `scrollIntoView({ block: 'nearest', behavior: 'smooth' })` for `.nav-sub-item.active` upon initial sidebar hydration, guaranteeing the currently selected sub-page is immediately visible in view even if multiple parent navigation groups are open.

## 1.31.0 — 2026-10-04

### Promotions & Coupons Workstation Redesign, Financial ROI Tracking, Free Shipping Campaigns, Tags Taxonomy & Migration 027

- **Modular Coupons Workstation Architecture (`views/admin/coupons.php`, `views/admin/coupons_partials/`, `assets/css/admin-coupons.css`)**:
  - Replaced legacy monolithic 294-line template with a modern component-driven workstation adhering strictly to Rule 7:
    - `views/admin/coupons_partials/_kpis.php`: Bento KPI statistics header displaying active coupons, lifetime redemption count, total discount disbursed (Toman), and total gross revenue generated with promotions.
    - `views/admin/coupons_partials/_toolbar.php`: interactive status filter pills (`همه کدهای تخفیف`, `فعال`, `منقضی‌شده`, `تکمیل ظرفیت`, `غیرفعال دستی`), real-time search box, and fast action CTAs.
    - `views/admin/coupons_partials/_table.php`: high-density data matrix featuring 1-click copyable monospace code badges, campaign title notes, discount type chips (`درصدی`, `مبلغ ثابت`, `ارسال رایگان`), minimum basket conditions, usage progress meters with customer caps, Shamsi expiry dates (`appDateTime`), and quick action buttons.
    - `views/admin/coupons_partials/_modal.php`: creation and editing modal featuring 1-click random code generation (`btn-magic-generate`), dynamic field visibility (hiding value and caps for free-shipping promotions), category scoping, and customer limits.
    - `views/admin/coupons_partials/_drawer_stats.php`: slide-over analytics drawer rendering real-time financial ROI metrics (order count, gross sales volume, total discount, average order value) and a table of the recent 12 orders that redeemed the coupon.
- **Coupon Promotion Capabilities & Database Migration (`database/migrations/027_v1.31.0_coupons_and_tags_enhancement.sql`, `database/schema.sql`, `app/services/CouponService.php`, `app/services/OrderService.php`, `app/controllers/site/checkout.php`)**:
  - Added `title VARCHAR(150)`, `max_uses_per_customer INT NOT NULL DEFAULT 1`, `category_id INT UNSIGNED DEFAULT NULL`, and updated `type` ENUM to include `free_shipping` via guarded Migration 027.
  - Implemented per-customer usage validation preventing promotion abuse across repeated orders with identical phone numbers.
  - Added category-scoped promotions verifying eligibility against cart item categories.
  - Added native Free Shipping coupon support automatically discounting full courier/postal shipping costs during checkout and order persistence.
- **Product Tags & Taxonomy Management Hub (`app/controllers/admin/tags.php`, `views/admin/tags.php`, `app/services/TagService.php`, `admin/tags.php`, `views/admin/layout/nav_config.php`)**:
  - Introduced dedicated product tags management view and controller in the Products Hub (`tags.php`) enabling store owners to manage SEO keywords, inspect connected product counts, edit slugs, and prune unused orphan tags in one click.
- **Ultra-Lean Controller Footprint (`app/controllers/admin/coupons.php`, `app/controllers/admin/tags.php`, `app/controllers/site/checkout.php`)**:
  - `app/controllers/admin/coupons.php` maintained at 65 lines with AJAX performance stats and random code generation endpoints.
  - `app/controllers/admin/tags.php` maintained at 35 lines.
  - `app/controllers/site/checkout.php` refactored to 75 lines, resolving pre-existing anti-bloat warning.
