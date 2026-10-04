# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.30.1 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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

## 1.30.2 — 2026-10-04

### Shipping Methods Master Table Streamlining, Zero-Overflow 5-Column Architecture & SVG Polish

- **Streamlined 5-Column Matrix Table (`views/admin/shipping_methods_partials/_table.php`, `assets/css/admin-shipping.css`)**:
  - Eliminated table horizontal overflow and bulky browser scrollbars by restructuring into 5 balanced, high-density columns:
    - `اولویت`: Vertical rank pill (`#1`, `#2`) with compact increment/decrement micro-chevrons.
    - `روش ارسال و پوشش جغرافیایی`: Contextual SVG logistics icons (courier bike, post truck, express lightning, cargo box) with dedicated color tints, title, scope pills (`استان «تهران»` / `سراسر کشور`), delivery speed chips (`⚡ تحویل همان‌روز یا ۲۴ ساعته`), and description.
    - `تعرفه و تراز مالی`: Unified pricing cell combining customer fee, postal expense comparison, store margin/subsidy indicators (`+سود` / `−یارانه` / `سربه‌سر`), and free-shipping threshold tags (`🎁 رایگان بالای ۵۰۰,۰۰۰ ت`).
    - `وضعیت`: Centered iOS toggle switch with instant AJAX persistence.
    - `عملیات`: Grouped 32x32px edit and delete buttons with polished hover states and full selector integrity.
- **Logistics Workstation Aesthetic Upgrade (`views/admin/shipping_methods_partials/_kpis.php`, `views/admin/shipping_methods_partials/_studio.php`)**:
  - Upgraded Bento KPI cards and live checkout simulation headers from legacy emojis to crisp, scalable vector SVG icons matching the AB-Socks design system.
  - Adjusted master-detail split layout to `minmax(0, 1.55fr) minmax(350px, 1fr)` ensuring seamless responsiveness across desktop and laptop viewports.

## 1.30.1 — 2026-10-04

### Shipping Methods & SMS Patterns Data Tables Modernization, Base Admin Table Styling & SVG Icons

- **Universal Admin Table Styling Foundation (`assets/css/admin.css`)**:
  - Implemented cohesive `.admin-table` design system token specifications: clean subtle borders (`#E2E8F0`), vertical cell padding (12px 16px), dedicated `#F8FAFC` header styling with uppercase 11px font, and smooth hover highlighting (`#F8FAFC`).
  - Elevated `.switch-label` and `.switch-slider` CSS rules to global `admin.css`, permanently fixing unstyled native HTML checkboxes on the SMS patterns page and across the entire admin panel.
- **Shipping Methods Table Polish & Visual Hierarchy (`views/admin/shipping_methods_partials/_table.php`, `assets/css/admin-shipping.css`)**:
  - Replaced legacy text emojis with lightweight inline vector SVG icons (truck, package, express) for modern, cross-platform visual consistency.
  - Implemented `.priority-stepper` with distinct `#1` priority badge alongside subtle increment/decrement order steppers.
  - Revamped economics column with high-contrast customer fee pills, subsidized freight margin badges (`حاشیه سود / یارانه ارسال`), and free delivery threshold chips.
  - Restyled table action buttons (`.btn-action-edit`, `.btn-action-delete`) with unified `.table-action-group` styling matching the admin design system.
- **SMS Patterns Matrix Table High-Density Redesign (`views/admin/sms_patterns_partials/_table.php`, `assets/css/admin-sms.css`)**:
  - Compacted row height to high-density ~52px (reducing previous ~110px vertical sprawl) with clean vertical rhythm and border separators.
  - Replaced raw text IDs with subtle `.sms-id-badge` badges and introduced 1-click copyable monospace pattern code chips (`.btn-copy-code`) with hover feedback.
  - Added highlighted amber alert button (`.pattern-code-unset`) for unconfigured patterns directing the store owner straight to pattern setup.
  - Replaced multiline block variable listings with compact, horizontal inline token chips (`.var-chips-inline`) with copy-friendly monospace fonts.
  - Preserved all JavaScript selectors (`.shipping-active-toggle`, `.btn-edit-method`, `.sms-active-toggle`, `#status-badge-{$id}`) guaranteeing 100% AJAX feature parity.
