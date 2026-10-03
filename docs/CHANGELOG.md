# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.24.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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

## 1.28.0 — 2026-10-04

### Email Studio & Webmail Workspace Overhaul, Coupon Management Architecture & Migration 025

- **Unified Admin Email Studio & Webmail Architecture (`app/controllers/admin/emails.php`, `views/admin/emails.php`, `views/admin/emails_partials/`, `assets/css/admin-emails.css`)**:
  - Replaced scattered legacy email scripts with a unified, high-aesthetic Master-Detail Email Studio conforming to Rule 7:
    - `views/admin/emails_partials/_header_stats.php`: Bento KPIs featuring active mailbox account, total inbox messages, live IMAP extension status, and quick compose action.
    - `views/admin/emails_partials/_inbox_pane.php`: split-pane master-detail inbox with account switcher, real-time subject search, unread status indicators, and clean reading pane with sanitized HTML preview and 1-click reply modal trigger.
    - `views/admin/emails_partials/_compose_modal.php`: modal smart composer with account selector, recipient autocomplete, canned responses selector (postal tracking, payment approval, stock inquiries), and signature rendering.
    - `views/admin/emails_partials/_accounts_pane.php`: dedicated domain mailbox accounts management hub with AES-256 encrypted credentials, IMAP/SMTP configurations, active toggles, and live connection test.
- **Critical Mailbox Service Fixes & Live Diagnostics (`app/services/MailboxService.php`)**:
  - Fixed fatal error bug on line 37 where `self::logSend()` was called on an undefined method; implemented robust `logSend()` logging both successful and failed dispatches into `email_log`.
  - Implemented `testConnection()` executing live socket handshakes to both IMAP and SMTP ports with full debug trace capture.
  - Implemented secure HTML body extraction, multipart MIME parsing, charset conversion, and XSS sanitization.
  - Integrated official store signature generation (`buildHtmlEmailTemplate()`) into outgoing transactional and admin emails.
- **Admin Coupons & Promotion Management System (`app/controllers/admin/coupons.php`, `views/admin/coupons.php`, `assets/css/admin-coupons.css`, `app/services/CouponService.php`)**:
  - Introduced dedicated Coupons workstation allowing store owners to define, monitor, and manage discount codes without direct database access.
  - Extended `CouponService`: implemented CRUD (`getAll`, `getById`, `save`, `toggle`, `delete`), added percentage and fixed discount calculations with minimum basket amounts, usage caps, and expiration validations.
  - Fixed column discrepancies (`max_uses` vs `usage_limit`, `discount_type` vs `type`) with seamless backward-compatible fallbacks.
  - Added Bento station card in `views/admin/settings_partials/_tab_workstations.php` and navigation link in `views/admin/layout/nav_config.php`.
- **Database Schema Migration 025 (`database/migrations/025_v1.28.0_email_studio_and_coupons.sql`, `database/schema.sql`)**:
  - Added `signature TEXT DEFAULT NULL` to `email_accounts` table.
  - Added guarded `max_discount_amount DECIMAL(12,0) DEFAULT NULL` column and `idx_coupon_lookup` index to `coupons` table.
- **Ultra-Lean Controller Footprint & Full Layer Boundaries**:
  - `emails.php` controller strictly bounded at 68 lines (Rule 7 soft limit: 80 lines).
  - `coupons.php` controller strictly bounded at 46 lines.
  - Zero SQL queries across all view templates and partials.

## 1.27.0 — 2026-10-03

### SMS Patterns Workstation Overhaul, Live Smartphone Simulator, Telephony KPIs & Filter Toolbar

- **Modern Telephony & SMS Patterns Workstation (`views/admin/sms_patterns.php`, `views/admin/sms_patterns_partials/`, `assets/css/admin-sms.css`)**:
  - Replaced legacy basic HTML table with high-density administrative workstation complying with Rule 7:
    - `views/admin/sms_patterns_partials/_kpis.php`: Bento KPIs featuring active patterns count, real-time Faraz SMS API gateway connectivity & balance status, unconfigured (`unset`) patterns alert, and today's dispatched SMS count.
    - `views/admin/sms_patterns_partials/_toolbar.php`: interactive category filter pills (`همه الگوها`, `احراز هویت OTP`, `سفارشات مشتری`, `کارت‌به‌کارت`, `هشدارهای ادمین`, `تنظیم‌نشده‌ها`) with dynamic item counts, instant real-time search input, and fast action CTA.
    - `views/admin/sms_patterns_partials/_table.php`: high-density pattern matrix table with monospace pattern code badge, event category badges, dynamic variable tags with contextual system token indicators (`🔗`), optimistic AJAX active toggle, and edit/test actions.
- **Dual-Pane SMS Pattern Studio & Live Smartphone Mockup (`views/admin/sms_pattern_edit.php`, `views/admin/sms_pattern_edit_partials/`, `assets/js/admin-sms.js`)**:
  - Decomposed legacy monolithic 340-line edit view down into modular partials:
    - `_form_fields.php`: pattern code, title, event mapping, active toggle, reference text, and internal description.
    - `_variables_builder.php`: interactive variable cards with type selection, max length constraints, and token mapping dropdown.
    - `_mobile_simulator.php`: authentic smartphone frame mockup rendering an incoming SMS bubble that updates in real time as the admin types pattern text or test values.
  - Built-in Unicode/Persian GSM part counter calculating character count and message parts (Part 1: 70 characters, subsequent: 67 characters/part).
  - Live Test Sending Console: integrated test dispatch panel allowing immediate verification of Faraz SMS delivery to an admin test phone number.
- **Service Layer & Telephony Analytics (`app/services/SmsPatternService.php`)**:
  - Implemented `getSmsPatternsSummaryMetrics()` aggregating pattern counts, active statuses, configured vs unset ratio, today's sent SMS count from `sms_log`, and live balance from `FarazSmsService::checkBalance()`.
  - Implemented `getSmsEventCategory()` mapping 11 core system events to logical business domains (`auth`, `orders`, `c2c`, `admin`).
- **Ultra-Lean Controller Footprint (`app/controllers/admin/sms_patterns.php`, `app/controllers/admin/sms_pattern_edit.php`)**:
  - `sms_patterns.php` maintained at 49 lines with AJAX status toggle support.
  - `sms_pattern_edit.php` maintained at 78 lines (both well below Rule 7 soft ceiling of 80 lines).

## 1.26.0 — 2026-10-03

### Shipping Methods Workstation Overhaul, Estimated Delivery Tracking, Live Checkout Simulation & Migration 024

- **Dual-Pane Logistics & Shipping Workstation Architecture (`views/admin/shipping_methods.php`, `views/admin/shipping_methods_partials/`, `assets/css/admin-shipping.css`)**:
  - Replaced legacy basic HTML table with a modern dual-pane Master-Detail Workstation matching AB-Socks design system standards:
    - `views/admin/shipping_methods_partials/_kpis.php`: Bento KPI statistics header displaying active methods count, average customer fee, average courier/postal expense, net unit shipping subsidy indicator (alerting when store subsidizes freight), and free shipping threshold status.
    - `views/admin/shipping_methods_partials/_table.php`: high-density priority matrix table with coverage scope badges (🏢 Province specific vs 🌐 Nationwide default), customer fee vs postal actual cost, unit margin/subsidy tags, free shipping threshold indicator, instant AJAX status toggle, and priority ordering.
    - `views/admin/shipping_methods_partials/_studio.php`: sticky interactive studio with instant mode switching (`جدید` / `ویرایش`), form controls with quick-chip delivery suggestions, and live simulation box.
- **Live Customer Checkout Simulation Studio (`assets/js/admin-shipping.js`, `views/admin/shipping_methods_partials/_studio.php`)**:
  - Embedded real-time visual simulator rendering exactly how the shipping method, delivery time expectation, and fee/free rules will appear to customers in the checkout summary and order invoice.
  - 1-click studio population from any table row without full page reloads, accompanied by optimistic AJAX active toggles and smooth scrolling.
- **Estimated Delivery Time Tracking & Storefront Checkout Integration (`database/migrations/024_v1.26.0_shipping_enhancement.sql`, `database/schema.sql`, `app/services/ShippingService.php`, `ajax/shipping_estimate.php`, `views/site/checkout.php`)**:
  - Added `estimated_delivery VARCHAR(120)` column to `shipping_methods` table via guarded Migration 024 and synchronized baseline `database/schema.sql`.
  - Extended `calculateShippingCost()` in `ShippingService.php` to include `estimated_delivery` in calculation results.
  - Updated `ajax/shipping_estimate.php` and `views/site/checkout.php` dynamic estimator to display delivery estimates (e.g. `هزینه ارسال (پست پیشتاز • ۲ تا ۴ روز کاری): رایگان`) directly next to shipping charges in customer checkout.
- **Logistics Economics & Summary Analytics (`app/services/ShippingService.php`)**:
  - Implemented `getShippingSummaryMetrics()` calculating aggregate metrics: total and active methods, average customer shipping fee, average courier cost, net unit shipping subsidy, and lowest active free-shipping basket threshold.
  - Implemented `toggleShippingMethodActive()` and `reorderShippingMethods()` supporting atomic database updates.
- **Ultra-Lean Controller Footprint (`app/controllers/admin/shipping_methods.php`, `app/controllers/admin/shipping_method_edit.php`)**:
  - Maintained `shipping_methods.php` controller at 72 lines (well within Rule 7 soft ceiling of 80 lines).
  - Maintained `shipping_method_edit.php` at 34 lines for seamless backward compatibility.

## 1.25.0 — 2026-10-03

### Appearance & Homepage Workspace Overhaul, Hero Promo Banner, Trust Badges, Favicon & Migration 023

- **Master-Tabbed Appearance Workspace Architecture (`views/admin/appearance.php`, `views/admin/appearance_partials/`, `assets/css/admin-appearance.css`)**:
  - Decomposed the legacy monolithic 221-line appearance template with hardcoded inline styles into an elegant 60-line master view with 5 specialized partials (`_header_stats.php`, `_nav_tabs.php`, `_tab_sections.php`, `_tab_hero.php`, `_tab_trust.php`, `_tab_branding.php`, `_tab_announcement.php`) conforming to Rule 7.
  - Introduced dedicated design system stylesheet `assets/css/admin-appearance.css` with responsive layout cards, dropzone file uploaders, live theme token color swatches, browser tab preview mockups, and URL hash tab synchronization.
- **Hero Promo Banner & Conversion Architecture (`app/controllers/site/home.php`, `views/site/home.php`, `assets/css/style.css`, `app/services/SettingService.php`)**:
  - Added configurable Hero Promo Banner supporting background image upload, seasonal promotional badge, main headline (H1), subtitle, and call-to-action (CTA) button with custom destination URL.
  - Implemented automatic responsive styling with high-contrast text overlay, graceful degradation to text Hero Intro when disabled, and live preview simulator in admin panel.
- **Value Propositions & Trust Bar (`app/controllers/site/home.php`, `views/site/home.php`, `assets/css/style.css`, `app/services/SettingService.php`)**:
  - Integrated 4 customizable trust cards (fast courier delivery, 7-day quality guarantee, 100% natural cotton fibers, hygienic gift-ready packaging) designed specifically to maximize checkout conversion for sock retail.
- **Branding Assets & Browser Favicon Management (`app/core/functions.php`, `app/services/SettingService.php`, `views/layout/header.php`, `views/admin/layout/header.php`)**:
  - Added full administrator management for site Favicon (ICO, PNG, SVG) with secure MIME validation, standardized naming (`favicon-0-site-{hash}.{ext}`), and instant browser tab mockup preview.
  - Introduced `siteFaviconUrl()` and `heroBannerImageUrl()` helper functions in `app/core/functions.php`.
- **Homepage Product Slider Limits & Streamlined Controller (`app/controllers/site/home.php`, `views/admin/appearance_partials/_tab_sections.php`)**:
  - Configurable item counts (4, 6, 8, 12, 16) for Featured Products and Newest Products sliders.
  - Streamlined `app/controllers/site/home.php` to 66 lines, strictly respecting Rule 7 soft ceiling (< 80 lines).
- **Database Migration 023 (`database/migrations/023_v1.25.0_appearance_enhancement.sql`, `database/schema.sql`)**:
  - Seeded all 18 new configuration keys into `settings` table with safe fallbacks and mirrored into baseline `schema.sql`.

## 1.24.0 — 2026-10-03

### Store Settings Hub Redesign, Order Policies, Banking/IBAN Integration & Migration 022

- **Master-Tabbed Settings Workspace Architecture (`views/admin/settings.php`, `views/admin/settings_partials/`, `assets/css/admin-settings.css`)**:
  - Decomposed the monolithic 466-line settings template into an 84-line master assembly view with 8 modular partials (`_header_stats.php`, `_nav_tabs.php`, `_tab_orders.php`, `_tab_payments.php`, `_tab_general.php`, `_tab_catalog_search.php`, `_tab_seo_social.php`, `_tab_legal_cms.php`, `_tab_workstations.php`) conforming to Rule 7.
  - Introduced dedicated design system stylesheet `assets/css/admin-settings.css` replacing all inline styles with modern design tokens, interactive tabs, iOS-style toggle switches, input addons, and Bento station cards.
  - Implemented client-side hash routing (`#orders`, `#payments`, `#general`, `#catalog`, `#seo_social`, `#legal`, `#workstations`) with automatic redirect state preservation in `app/controllers/admin/settings.php`.
- **E-Commerce Order & Inventory Operational Policies (`app/controllers/site/checkout.php`, `app/controllers/site/cart.php`, `views/site/cart.php`, `app/services/SettingService.php`)**:
  - *Store Vacation / Maintenance Mode (`store_order_status` & `store_paused_message`)*: Added administrative toggle to pause checkout with a customizable notification banner across cart and checkout flows.
  - *Minimum Order Requirement (`min_order_amount`)*: Enforces subtotal threshold prior to checkout entry, preventing uneconomic microscopic shipments.
  - *Card-to-Card Slip Upload Window (`c2c_timeout_hours`)*: Configurable expiration window for customer payment receipt uploads.
  - *Critical Low-Stock Alert Threshold (`low_stock_threshold`)*: Global threshold for triggering inventory shortage badges across dashboard and catalog tables.
- **Financial, Banking & Invoice Customization (`app/services/SettingService.php`, `app/controllers/site/card_to_card.php`, `views/site/card_to_card.php`, `views/admin/order_detail.php`, `assets/css/admin-orders.css`)**:
  - *Store Bank Account & IBAN (`store_shaba`, `store_bank_name`)*: Extended payment configuration with 24-digit Shaba (IBAN) format validation and bank name, dynamically rendered on the customer digital payment card.
  - *Printable Invoice Customization (`invoice_footer_note`)*: Custom invoice terms and return policies dynamically appended to print-sheet orders (`@media print`).
  - *VAT / Tax Architecture (`tax_enabled`, `tax_percentage`)*: Configured foundation for tax calculation modeling.
  - *Corporate Identification (`store_economic_code`, `store_national_id`, `admin_alert_mobile`)*: Added economic code, national ID, and dedicated administrative alert mobile number for event notifications.
- **Database Migration 022 (`database/migrations/022_v1.24.0_store_settings_enhancement.sql`, `database/schema.sql`)**:
  - Seeded all new configuration keys into `settings` table with safe fallbacks and mirrored into baseline `schema.sql`.

