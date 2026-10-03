# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.29.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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

## 1.30.0 — 2026-10-04

### System Diagnostics & Health Studio Redesign, Host Resource Quota Monitoring & Unified Log Center

- **Unified System Diagnostics & Health Studio Architecture (`views/admin/diagnostics.php`, `views/admin/diagnostics_partials/`, `assets/css/admin-diagnostics.css`)**:
  - Replaced legacy disjointed diagnostic screens with a modern Master-Tabbed Health Studio adhering strictly to Rule 7:
    - `views/admin/diagnostics_partials/_header_pulse.php`: Real-time system pulse displaying live database quota gauge (200MB limit), media disk quota gauge (1500MB limit), clock synchronization status, and active PHP version.
    - `views/admin/diagnostics_partials/_nav_tabs.php`: Master tab navigation with responsive styling and URL hash state synchronization (`health`, `tests`, `notifications`, `errors`, `audit`).
    - `views/admin/diagnostics_partials/_tab_server_health.php`: Host and database resource metrics, top 6 tables by storage and row count, upload directory breakdowns (`products`, `branding`, `card_to_card`, `expenses`, `tmp`), directory write permissions, PHP environment variables, critical extensions check (PDO, cURL, Mbstring, GD with WebP, OpenSSL, IMAP), and server clock synchronization metrics.
    - `views/admin/diagnostics_partials/_tab_connectivity.php`: Interactive live connectivity test suite for Faraz SMS balance & credit, OTP pattern validation, SMTP email socket handshake, MySQL database ping latency (ms), and Zarinpal payment gateway reachability, alongside a masked active configuration table.
    - `views/admin/diagnostics_partials/_tab_notifications_log.php`: Filterable notification dispatch logs (SMS and Email) with search, status filters (`sent`, `failed`, `logged`), and expandable technical debug payloads.
    - `views/admin/diagnostics_partials/_tab_system_errors.php`: Real-time PHP and database error viewer parsing `storage_errors.log` by severity (`Fatal`, `Warning`, `Notice`, `Database`) with one-click log clearing action.
    - `views/admin/diagnostics_partials/_tab_audit_trail.php`: Visual security audit viewer for `admin_audit_logs` with admin, action type, description, and IP address filtering.
- **Diagnostic & Resource Management Service (`app/services/DiagnosticService.php`)**:
  - Implemented `getServerHealthMetrics()` measuring database size via `information_schema.TABLES`, upload directory usage, extension availability, and timezone drift between PHP and MySQL session.
  - Implemented `testConnectivity()` providing safe, non-destructive connection testing for Faraz SMS, SMTP, database roundtrip latency, and Zarinpal payment gateway.
  - Implemented `getNotificationLogs()` encapsulating prepared-statement queries for `sms_log` and `email_log`.
  - Implemented `getSystemErrorLogs()` and `clearSystemErrorLogs()` reading and clearing `storage_errors.log`.
  - Implemented `getAdminAuditTrail()` joining `admin_audit_logs` with `admins` metadata.
  - Implemented `cleanTemporaryUploads()` garbage-collecting temporary card-to-card upload receipts older than 24 hours.
- **Strict Layer Boundary & Anti-Bloat Refactoring**:
  - Refactored `app/controllers/admin/notifications_log.php`: removed all raw SQL queries (`db()->query(...)`), transforming the controller into a clean 10-line forwarder to the unified Diagnostic Studio.
  - Re-architected `app/controllers/admin/diagnostics.php` to strictly remain under 65 lines (soft ceiling: 80 lines), delegating all business logic to `DiagnosticService`.
  - Guaranteed 100% view purity across all diagnostics partials (0 SQL queries, 0 direct form processing).

## 1.29.0 — 2026-10-04

### Site Admins Workspace Redesign, Security & Activity Audit Trail & Migration 026

- **Modernized Site Admins Workspace Architecture (`views/admin/users.php`, `views/admin/users_partials/`, `assets/css/admin-users.css`)**:
  - Replaced legacy table with modular Bento-inspired administrative workstation adhering strictly to Rule 7:
    - `views/admin/users_partials/_kpis.php`: Bento KPIs featuring total admin count, active accounts, super admins ratio, and real-time last active session card with IP.
    - `views/admin/users_partials/_toolbar.php`: interactive live search by name, username, email or mobile, role/status filter pills, and modal trigger CTA.
    - `views/admin/users_partials/_table.php`: interactive admin table featuring dynamic gradient initials avatars, contact badges (phone, email), role pills (gold for super admin, blue for admin), active pulsing status indicators, and quick action buttons.
    - `views/admin/users_partials/_modals.php`: accessible, animated dialog modals for creating new admins, editing existing profiles, and resetting passwords without page reloads.
    - `views/admin/users_partials/_audit_trail.php`: real-time security timeline displaying recent administrative operations with actor metadata, event tags, descriptions, IP addresses, and Persian timestamps.
- **Service Layer Expansion & Security Audit Trail (`app/services/AdminUserService.php`)**:
  - Implemented `getAdminUsersMetrics()` aggregating total, active, super_admins, and latest login session.
  - Implemented `updateAdminUserRecord()` enabling super admins to update username, full name, phone, email, and role, while strictly enforcing safety invariants (preventing demotion of the last super admin).
  - Implemented `logAdminAction()` and `getRecentAdminAuditLogs()` recording administrative operations into `admin_audit_logs` with exception insulation.
  - Enhanced `createAdminUserRecord()` with input format validations and backwards-compatible payload handling.
  - Hardened `toggleAdminUserActiveStatus()` and `deleteAdminUserRecord()` to guarantee that at least one active super admin remains.
- **Session Tracking & Login Audit Integration (`app/core/auth.php`)**:
  - Enhanced `attemptAdminLogin()`: automatically updates `last_login_at` timestamp and `last_login_ip` address on verified logins.
  - Emits immutable `login` audit event into `admin_audit_logs`.
- **Database Schema Migration 026 (`database/migrations/026_v1.29.0_admin_users_and_audit.sql`, `database/schema.sql`)**:
  - Added guarded columns `phone`, `email`, `last_login_at`, and `last_login_ip` to `admins` table.
  - Created `admin_audit_logs` table with foreign key cascading and indexes on `(admin_id, action)` and `created_at`.
- **Ultra-Lean Controller Footprint & View Purity**:
  - `app/controllers/admin/users.php` strictly bounded at 53 lines (soft ceiling: 80 lines).
  - Exactly zero SQL queries or form mutations in view templates.



