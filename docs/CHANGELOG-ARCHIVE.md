# Changelog Archive (v1.0.0 — v1.30.0)

Historical release notes for AB-Socks versions 1.0.0 through 1.30.0.
For recent and active releases, see [CHANGELOG.md](./CHANGELOG.md).

---

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
    - `views/admin/shipping_methods_partials/_kpis.php`: Bento KPI statistics header displaying active methods count, average customer fee, average courier/postal expense, net unit shipping subsidy indicator, and free shipping threshold status.
    - `views/admin/shipping_methods_partials/_table.php`: high-density priority matrix table with coverage scope badges, customer fee vs postal actual cost, unit margin/subsidy tags, free shipping threshold indicator, and instant AJAX status toggle.
    - `views/admin/shipping_methods_partials/_studio.php`: sticky interactive studio with instant mode switching (`جدید` / `ویرایش`), form controls with quick-chip delivery suggestions, and live simulation box.
- **Live Customer Checkout Simulation Studio (`assets/js/admin-shipping.js`, `views/admin/shipping_methods_partials/_studio.php`)**:
  - Embedded real-time visual simulator rendering exactly how the shipping method, delivery time expectation, and fee/free rules will appear to customers in checkout.
- **Estimated Delivery Time Tracking & Storefront Checkout Integration (`database/migrations/024_v1.26.0_shipping_enhancement.sql`, `database/schema.sql`, `app/services/ShippingService.php`, `ajax/shipping_estimate.php`, `views/site/checkout.php`)**:
  - Added `estimated_delivery VARCHAR(120)` column to `shipping_methods` table via guarded Migration 024 and synchronized baseline `database/schema.sql`.
  - Extended `calculateShippingCost()` in `ShippingService.php` to include `estimated_delivery` in calculation results.
- **Logistics Economics & Summary Analytics (`app/services/ShippingService.php`)**:
  - Implemented `getShippingSummaryMetrics()`, `toggleShippingMethodActive()`, and `reorderShippingMethods()`.
- **Ultra-Lean Controller Footprint (`app/controllers/admin/shipping_methods.php`, `app/controllers/admin/shipping_method_edit.php`)**:
  - Maintained `shipping_methods.php` controller at 72 lines (well within Rule 7 soft ceiling of 80 lines).

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


## 1.23.2 — 2026-10-03

### Inventory Valuation Fatal 500 Column Mismatch Elimination

- **Inventory Valuation Schema & Query Alignment (`app/services/AccountingService.php`)**:
  - Eliminated fatal uncaught `PDOException` in `getInventoryValuationReport()` caused by invalid database columns (`p.has_variants`, `p.status`, `pv.label`).
  - Replaced `p.has_variants` with dynamic variant detection and subquery `variant_count` on `product_variants`.
  - Replaced non-existent `p.status != 'deleted'` with standard active filter `p.is_active = 1`.
  - Replaced non-existent `pv.label` with `TRIM(CONCAT_WS(' - ', pv.size, pv.color)) AS label`.
  - Optimized product image resolution by directly utilizing `p.image` rather than subquerying `product_images`.

## 1.23.1 — 2026-10-02

### Operating Expenses Mobile View Architecture, Inventory Valuation 404 Fix & Image Safety

- **Operating Expenses Mobile View & Card Feed (`views/admin/expenses_partials/_cards_stack.php`, `views/admin/expenses.php`, `assets/css/admin-finance.css`)**:
  - Implemented high-density adaptive financial card feed for mobile viewports ($\le 768$px) via dedicated partial `_cards_stack.php`:
    - Top meta strip with Shamsi date badge, expense nature indicator, and archive status.
    - Card body with bold title, category chip, payee tag, and notes.
    - Financial row highlighting payment source pill alongside large, formatted Tomans price.
    - Footer strip featuring single-tap invoice/receipt lightbox modal trigger, submitter meta, and touch-optimized action buttons ($\ge 36\times 36$px tap targets).
  - Compact 2x2 Bento KPI Grid on mobile, compressing all 4 key operating expense metrics within 130px height without vertical scroll waste.
  - Horizontal swipeable touch chips for category distribution breakdown and quick Shamsi date range presets.
  - Collapsible filter drawer accordion with dynamic active filter counter badge (`فیلترها (N)`).
  - Preserved 100% pixel-perfect desktop table view on $\ge 769$px.
- **Inventory Valuation Physical Entrypoint Fix (`admin/inventory_valuation.php`)**:
  - Added missing physical entrypoint `admin/inventory_valuation.php` delegating to `app/controllers/admin/inventory_valuation.php` with `requireAdmin()` guard, resolving DirectAdmin Apache 404 routing fall-through.
  - Applied `.fin-dual-grid` class to `views/admin/inventory_valuation.php` for responsive breakdown tables on tablet and mobile.
- **Admin Combobox Persian Text Anti-Clipping (`assets/css/admin-finance.css`, `views/admin/expenses.php`)**:
  - Introduced dedicated `.fin-filter-select`, `.fin-filter-input`, and `.fin-filter-date` classes with 38px height, custom inner padding, `line-height: normal`, and custom SVG chevron.
  - Resolved vertical clipping and bisected Persian characters in Chromium/Windows native select boxes.
- **Pre-emptive Fatal Error Fixes & Image Constant Standardization (`app/services/AccountingService.php`, `ajax/admin_search.php`, `views/admin/expense_edit.php`, `views/admin/expenses.php`)**:
  - Replaced undefined function call `imageUrl()` in `AccountingService.php` (`getInventoryValuationReport()`) and `productImageUrl()` in `ajax/admin_search.php` with standard `UPLOAD_URL` and `placeholder-sock.svg` fallback.
  - Standardized expense receipt preview URLs across `expenses.php` and `expense_edit.php` to use `EXPENSE_UPLOAD_URL`.

## 1.23.0 — 2026-10-02

### Operating Expenses Workstation 2.0, Dedicated Expense Service, Inventory Valuation Hub & Migration 021

- **Dedicated Expense Service Layer & Architectural Decomposition (`app/services/ExpenseService.php`, `app/services/AccountingService.php`)**:
  - Extracted expense management methods out of `AccountingService.php` to a dedicated `ExpenseService.php` (~400 lines) adhering to Rule 7 (Separation of Concerns and Boy Scout Rule).
  - Implemented comprehensive operational expense methods: `getExpensesList()`, `getExpenseSummaryMetrics()`, `getExpenseById()`, `saveExpense()`, `archiveExpense()`, `restoreExpense()`, `getExpenseCategories()`, `getExpensePaymentSources()`, `getExpensePayees()`, and `exportExpensesCsv()`.
  - Added WebP image conversion with GD compression (`uploadExpenseReceipt()`) preserving invoices and receipts under `uploads/expenses/`.
- **Database Migration 021 (`database/migrations/021_v1.23.0_expenses_enhancement.sql`, `database/schema.sql`)**:
  - Extended `expenses` table schema:
    - `payment_source VARCHAR(60) NOT NULL DEFAULT 'کارت اصلی فروشگاه'`: tracks bank card, online gateway settlement, or petty cash.
    - `payee VARCHAR(120) NULL DEFAULT NULL`: tracks vendors, workshops, suppliers, or postal contractors.
    - `expense_nature ENUM('fixed', 'variable', 'capital') NOT NULL DEFAULT 'variable'`: categorizes operational nature to refine break-even modeling.
    - `receipt_image VARCHAR(255) NULL DEFAULT NULL`: image attachment for invoices and pos slips.
  - Added indexes: `idx_payment_source`, `idx_payee`, `idx_expense_nature`.
  - Mirrored changes into baseline `database/schema.sql`.
- **Operating Expenses High-Density Workstation (`views/admin/expenses.php`, `views/admin/expense_edit.php`, `assets/css/admin-finance.css`)**:
  - Replaced legacy card-to-card styling in `expenses.php` with unified `--fin-*` Bento design tokens.
  - 4 Hero Bento KPI Cards: Filtered Period Total (with fixed vs variable breakdown), Expense Count, Average per Expense, and Top Cost Center with percentage share.
  - Visual Category Distribution Strip: stacked multi-segment progress bar illustrating real-time cost center distribution.
  - Quick Shamsi Date Presets Toolbar: `امروز | ۷ روز | ۳۰ روز | ماه جاری | ماه قبل | امسال | همه تاریخ‌ها`.
  - Active vs. Archived Status Tabs with soft-archive restoration support (`restoreExpense()`).
  - Modal Lightbox: client-side invoice and receipt preview modal without page navigation.
  - One-click UTF-8 BOM CSV / Excel export of filtered expenses.
  - Form enhancements in `expense_edit.php`: live Persian currency words formatter ("حروف"), payee datalist auto-completion, nature card selector, and receipt image uploader with preview and deletion support.
- **Inventory Valuation & Capital Health Hub (`views/admin/inventory_valuation.php`, `app/controllers/admin/inventory_valuation.php`, `app/services/AccountingService.php`)**:
  - Implemented `getInventoryValuationReport()` in `AccountingService.php`: computes physical inventory capital valuation ($\sum \text{stock} \times \text{cost\_price}$), expected retail turnover, potential unrealized gross profit, and category-level capital distribution.
  - Dead & Slow-Moving Stock Detection: automated alert card flagging products with high stock but 0 sales over the last 60 days to help liquidate trapped capital.
  - Top Capital-Concentrated Catalog Table: reveals which items tie up the most physical working capital.
  - Integrated into administrative sidebar navigation under Finance Hub (`views/admin/layout/nav_config.php`, `views/admin/layout/header.php`).

## 1.22.1 — 2026-10-02

### Image Optimizer Engine DRY Architecture, 40MB Upload Limit & High-Fidelity Quality Preset

- **Centralized Dynamic Image Optimizer (`assets/js/admin-image-optimizer.js`, `assets/css/admin.css`)**:
  - Eliminated hardcoded marketing and technical verbose text ("پشتیبانی از انواع فرمت‌ها دوربین آیفون و حذف متادیتا") across the codebase in compliance with DRY principles.
  - Formulated single-source dynamic template generation for dropzone hints: `فرمت‌های مجاز: {formats} (حداکثر {max_size})`.
  - Upgraded administrative upload threshold to 40 MB default (`data-max-size="۴۰ مگابایت"`).
  - Shifted default WebP quality baseline from aggressive 30% (`0.30`) to visually lossless 85% (`0.85`), maximizing graphic fidelity with minimal compression artifacts.
  - Added native compact layout mode (`data-layout="compact"` / `.aio-dropzone-compact`) for space-constrained sidebars, drawers, and modal workstations.
  - Added programmatic `change` event dispatching on DataTransfer sync to trigger reactive UI updates in consuming listeners.
- **Admin Views Image Upload Standardization**:
  - Decomposed and standardized image dropzones in `views/admin/gift_items_partials/_studio.php` and `views/admin/categories.php`, eliminating redundant custom markup and unifying under `data-optimize-image`.
  - Harmonized `views/admin/product_edit.php` (main and gallery), `views/admin/appearance.php` (branding logo with SVG detection), `views/admin/gift_item_edit.php`, and `views/admin/card_to_card_payments.php` modal dropzone.
- **Storefront Checkout Upload Polish (`views/site/card_to_card.php`, `assets/js/card-to-card.js`)**:
  - Cleaned customer-facing payment receipt dropzone copy: removed device-specific jargon and verbose badge labels, presenting concise format and size parameters (`فرمت‌های مجاز: JPG، PNG، WebP (حداکثر ۵ مگابایت)`).
  - Cleaned conversion loading status messages.

## 1.22.0 — 2026-10-02

### Admin Gifts & Cart Add-ons Workstation 2.0 & Merchandising Engine (Migration 020)

- **Merchandising Engine & Database Schema Additions (`database/migrations/020_v1.22.0_gift_merchandising.sql`, `database/schema.sql`)**:
  - Migration 020 introduces strategic merchandising attributes on `gift_items`:
    - `tagline VARCHAR(190)`: compelling short selling description rendered in customer-facing cart cards.
    - `badge_text VARCHAR(50)`: promotional marketing pill tags (`محبوب‌ترین`, `پیشنهاد ویژه`, `بسته‌بندی کادو`, `ارزش خرید بالا`).
    - `min_cart_total DECIMAL(12,0)`: smart targeting threshold (only offer add-on when cart subtotal meets or exceeds threshold; 0 = all carts).
    - `sort_order INT`: manual sequence prioritization with `idx_gift_sort` index.
  - Mirrored all table definitions and indices into `database/schema.sql`.
- **Advanced Unit Economics & Attach Rate Analytics (`app/services/GiftService.php`)**:
  - Implemented `lifetime_post_order_net_profit`: computes actual net profit generated by checkout add-on sales (`sold_units * (unit_selling_price - unit_cost_price)`).
  - Implemented `attach_rate_percent`: tracks the exact percentage of completed/paid orders containing checkout add-ons (Take Rate %) along with qualifying order counts.
  - Added automated `top_performer` hero detection: identifies top profit-driving add-on item with units sold and gross margin.
  - Enhanced `getAvailablePostOrderItems(?int $cartSubtotal)`: filters offerings by `min_cart_total <= $cartSubtotal` and enforces strict sorting `sort_order ASC, id DESC`.
  - Added transactional `reorderGiftItems(array $orderedIds)` for atomic sequence updates.
  - Updated `saveGiftItem()` to sanitize and persist merchandising fields.
- **Desktop Dual-Pane Workstation Architecture Overhaul (`views/admin/gift_items.php`, `views/admin/gift_items_partials/`)**:
  - Modularized legacy 704-line monolithic view down to 61 lines by decomposing into clean presentation partials (Rule 7 and Boy Scout Rule compliant):
    - `views/admin/gift_items_partials/_kpis.php`: Bento KPIs featuring Post-order Revenue & Gross Profit pill, Cart Attach Rate with micro progress gauge, Inventory Health alerts, and Top Performer Hero Card.
    - `views/admin/gift_items_partials/_table.php`: high-density Matrix Table featuring drag handle (`⠿`) for instant reordering, image thumbnail with hover micro-zoom preview card, title/tagline/badge indicators, and inline stock stepper popover without page reloads.
    - `views/admin/gift_items_partials/_studio.php`: sticky 420px studio featuring tagline input, quick-chip marketing badges, real-time live margin calculator with visual progress gauge bar, and authentic **Live Customer Cart Simulator** reflecting edits in real time.
- **Client Workstation Engine & Dedicated Styles (`assets/js/admin-gift-items.js`, `assets/css/admin-gift-items.css`)**:
  - Introduced cache-busted `assets/js/admin-gift-items.js` (412 lines) handling HTML5 drag-and-drop table sorting via AJAX, keyboard-accessible inline stepper popover (Enter to save, Esc to dismiss), optimistic status toggles, studio synchronization, and floating toast notifications.
  - Refined `assets/css/admin-gift-items.css` for desktop dual-pane workstations with smooth transitions, hover zoom cards, and visual margin gauge bars.
- **Storefront Cart Add-ons Polish (`views/site/cart.php`, `app/controllers/site/cart.php`)**:
  - Updated cart controller to pass `$cart['subtotal']` for threshold filtering.
  - Enhanced customer cart add-on cards with promotional marketing badge pills and helper tagline descriptions.
- **Lean Controller Maintenance (`app/controllers/admin/gift_items.php`)**:
  - Added `reorder` action handling AJAX sort requests while strictly maintaining controller size at 80 lines.

## 1.21.0 — 2026-10-01

### Financial Dashboard Workstation & Unit Economics Overhaul

- **Financial Analytics & Aggregation Engine (`app/services/AccountingService.php`, `app/core/functions.php`)**:
  - Implemented `jalaliToGregorian()` in `functions.php` to enable precise boundary conversions for Jalali calendar cycles (Farvardin through Esfand, accounting for 31-day and 30-day Iranian solar months).
  - Added `resolveFinancialDateRange()` providing true Shamsi month and year filters (`today`, `yesterday`, `7days`, `30days`, `this_month`, `last_month`, `this_year`, `all`, and custom dates).
  - Extended `getFinancialSummary()` with advanced unit economics: Average Order Value (AOV), Net Profit per Order, Shipping P&L balance, Shipping Subsidy, Discount Penetration Rate, Break-Even Revenue and Order Targets, and Product Cost Health score.
  - Implemented `getFinancialDailyTrends()`: batch time-series timeline aggregation generating daily gross revenue, COGS, operating expenses, and net profit with zero N+1 database queries.
  - Added `getTopProfitProducts()`: identifies top 5 profit-driving catalog products ranked by net contribution margin.
  - Added `getPaymentMethodBreakdown()`: calculates sales volume, order counts, and share percentages across Zarinpal and Card-to-Card gateways.
  - Added `getShippingMethodFinancialBreakdown()`: compares customer-paid shipping fees against actual courier costs to monitor logistical subsidies.
  - Added `getIncompleteCostProducts()`: audits orders with zero-cost snapshots to ensure financial precision.
  - Added `exportFinancialCsv()` and `getFinancialExportRows()`: direct UTF-8 BOM CSV streaming for Iranian Excel compatibility.
- **Dedicated Financial Design System (`assets/css/admin-finance.css`, `views/admin/layout/header.php`)**:
  - Introduced modular stylesheet `assets/css/admin-finance.css` linked dynamically in `header.php` with cache-busted versioning.
  - Comprehensive design tokens for financial status indicators (emerald for profit, rose for expense/loss, indigo for break-even, amber for subsidies and audit warnings).
  - High-density Bento workstation grid with interactive hover depth, responsive flex-wrap controls, and custom SVG charting primitives.
  - Dedicated `@media print` print-sheet styling rendering an official corporate Profit & Loss (P&L) A4 statement with hide-on-print controls for interactive UI elements.
- **Pure SVG Interactive Dual-Layer Timeline (`views/admin/finance_dashboard.php`)**:
  - Zero-external-dependency charting engine built entirely with inline SVG vectors, cubic bezier smooth paths, and dual-layer gradient fills (gross revenue vs net profit).
  - Interactive tooltip badge with crosshair tracking indicator and dynamic Jalali date labels.
- **Ultra-Lean Controller Architecture (`app/controllers/admin/finance_dashboard.php`)**:
  - Reduced controller footprint to 34 lines (under Rule 7 soft ceiling of 80 lines).
  - Pure presentation delegation: handles CSV export requests, resolves date ranges, and fetches analytical datasets in single service calls.
- **Desktop Bento Workstation & Deep Analytics Matrix (`views/admin/finance_dashboard.php`)**:
  - 4 Hero KPI Cards (Gross Revenue, Product Cost COGS, Operating Expenses, Net Settled Profit) with dynamic margin badges.
  - 4 Micro-Metric Indicators (Average Order Value, Net Profit/Order, Shipping P&L Balance, Customer Discounts).
  - Break-Even Progress Gauge displaying real-time fixed cost coverage percentage and required sales volume.
  - Cash Flow Allocation Waterfall bar visualizing capital distribution across inventory, operations, and retained earnings.
  - Dual-column analytical deck: Top Profit Generators, Gateway Distribution, Shipping Subsidies, and Expense Categorization.
  - Missing Cost Audit Drawer with 1-click navigation to Product Pricing Hub (`pricing.php`).

## 1.20.1 — 2026-10-01

### Admin Gifts & Cart Add-ons Workstation Architecture Overhaul

- **Master-Detail Dual-Pane Workstation Architecture (`views/admin/gift_items.php`, `assets/css/admin-gift-items.css`, `views/admin/layout/header.php`)**:
  - Re-engineered the Gifts & Add-ons catalog page into a modern desktop Master-Detail workstation with a high-density Matrix Table (62% width) and a Sticky Smart Studio (38% width, `position: sticky; top: 18px`).
  - Introduced dedicated CSS token engine `assets/css/admin-gift-items.css` linked dynamically in `header.php` with cache-busted versioning.
  - Implemented client-side lightweight JSON catalog map enabling instant row selection, editing, live search filtering, and state synchronization without full page reloads.
- **Top Bento KPI Metrics Deck (`views/admin/gift_items.php`, `app/services/GiftService.php`)**:
  - 4 Key Bento KPI metric cards Above the Fold with clickable direct filter anchors:
    1. *Catalog Health*: Total items, active vs inactive count.
    2. *Admin Free Gifts*: Lifetime attachment count to orders (`role = 'gift'`).
    3. *Cart Add-on Upsells*: Lifetime post-order revenue and units sold (`role = 'post_order'`).
    4. *Inventory Valuation & Alerts*: Total catalog inventory valuation in Tomans (`SUM(stock * cost_price)`) and critical low-stock alert badge ($\le 5$).
- **Sticky Smart Studio & Live Profit Margin Calculator (`views/admin/gift_items.php`)**:
  - Integrated dynamic creation and editing studio supporting real-time mode switching between "Add New Item" and "Edit Item #ID".
  - Dynamic JavaScript Live Profit Margin Calculator: calculates gross unit profit (`post_order_price - cost_price`) and gross profit margin percentage in real-time as prices are typed, color-coded by margin bracket.
  - Instant WebP image dropzone with client-side FileReader preview integrated with `data-optimize-image="giftitem"`.
  - Interactive role toggle cards for admin free gifting and checkout post-order add-ons.
- **Service Layer Extensions & Zero N+1 Batch Aggregation (`app/services/GiftService.php`)**:
  - Implemented `getAdminGiftItemsMetrics()` returning comprehensive catalog and lifetime order utilization metrics in 2 vectorized queries.
  - Enhanced `getAdminGiftItemsList($search, $roleFilter)` attaching lifetime `gifted_units`, `sold_units`, `gross_revenue`, and calculated margin percentages via single `LEFT JOIN` on `order_gift_items`.
  - Added `toggleGiftItemActive(int $id)` and `updateGiftItemStock(int $id, int $stock)` with optimistic AJAX UI toggles.
- **Controller Modernization & Seamless Routing (`app/controllers/admin/gift_items.php`, `app/controllers/admin/gift_item_edit.php`)**:
  - Kept `app/controllers/admin/gift_items.php` at 69 lines (strictly under Rule 7 80-line ceiling), handling AJAX toggles, stock adjustments, and studio saves.
  - Refactored `gift_item_edit.php` to a 13-line seamless redirect proxying legacy links directly to the workstation studio (`gift_items.php?edit=ID`).

## 1.20.0 — 2026-09-30

### SMS Events Catalog & Dynamic Variable Data-Binding Architecture (FEAT-A007 & Database Migration 019)

- **Comprehensive 11-Event SMS System Catalog (`app/services/SmsPatternService.php`, `database/migrations/019_v1.20.0_sms_events_catalog.sql`, `database/schema.sql`)**:
  - Expanded system events to 11 standardized lifecycle triggers across customer and admin workflows:
    - Customer Authentication: `otp` (login and registration verification).
    - Customer Orders & Logistics: `order_created`, `order_paid`, `order_shipped` (with postal tracking code), `order_delivered`, `order_cancelled`.
    - Customer Card-to-Card: `c2c_instructions`, `card_to_card_approved`, `card_to_card_rejected` (with administrative rejection reason).
    - Store Admin Alerts: `admin_new_order`, `admin_c2c_receipt`.
  - Migration 019 pre-seeds all inactive event patterns with default data-binding configurations, allowing store administrators to activate any event with a single click and enter their Faraz pattern code.
  - Mirrored catalog baseline into `database/schema.sql`.
- **Dynamic Variable Data-Binding Engine (`app/services/SmsPatternService.php`)**:
  - Introduced `getSmsEventTokens(?string $eventKey)` and `getSmsGlobalTokens()` cataloging contextual system tokens (`order_code`, `customer_name`, `customer_phone`, `total_price`, `tracking_code`, `ref_id`, `rejection_reason`, `card_number`, `card_holder`, `code`, `payment_method`, `site_title`).
  - Added `source_token` attribute to `variables_config` in `sms_patterns`, allowing any arbitrary variable name registered in Faraz SMS to cleanly map to contextual system tokens without hardcoded attribute assumptions.
  - Implemented `dispatchSmsEvent(string $eventKey, array $contextData, ?string $recipientPhone)`:
    - Queries active pattern for the target event key.
    - Zero-failure invariant: safely returns `{ok: true, skipped: true}` if unconfigured or inactive, guaranteeing SMS issues never break customer checkouts or status transitions.
    - Resolves tokens with data type guards (strips non-digits for numeric variables; trims to `max_len`).
    - Resolves store admin mobile automatically from `store_mobile` setting for administrative notification events.
- **Transactional Event Hook Wiring (`app/services/OrderService.php`, `app/controllers/site/card_to_card.php`, `payment/zarinpal_callback.php`, `app/services/FarazSmsService.php`)**:
  - `OrderService::createOrder()`: dispatches `order_created`, `c2c_instructions` (conditional on card-to-card), and `admin_new_order` immediately following successful transaction commit.
  - `OrderService::updateOrderStatus()`: dispatches `order_shipped` (including tracking code), `order_delivered`, and `order_cancelled`.
  - `OrderService::verifyCardToCardReceipt()`: dispatches `card_to_card_approved` and `order_paid`.
  - `OrderService::rejectCardToCardReceiptWithReason()`: dispatches `card_to_card_rejected` with custom rejection reason.
  - `OrderService::updatePaymentStatus()`: dispatches `order_paid` on transition to `paid`.
  - `app/controllers/site/card_to_card.php`: dispatches `admin_c2c_receipt` upon customer receipt submission.
  - `payment/zarinpal_callback.php`: dispatches `order_paid` with Zarinpal ref_id upon bank verification.
  - `FarazSmsService::sendPatternByEvent()`: delegates directly to `dispatchSmsEvent()` for unified execution.
- **Admin SMS Pattern Workstation Polish (`views/admin/sms_pattern_edit.php`, `app/controllers/admin/sms_pattern_edit.php`, `views/admin/sms_patterns.php`)**:
  - Integrated dynamic "داده متصل سیستمی (Token)" select input per variable row with optgroups for recommended event tokens and global tokens.
  - Real-time JavaScript event listener updating token dropdowns dynamically when the event select input changes.
  - Enhanced pattern index table with "تنظیم‌نشده" badges for unconfigured patterns and token link indicators (`🔗`) on variable tags.
  - Kept `app/controllers/admin/sms_pattern_edit.php` at 78 lines (< 80 lines ceiling).

## 1.19.0 — 2026-09-30

### Smart Default Product Variant Strategy Architecture (FEAT-A006 & Database Migration 018)

- **Configurable Default Variant Selection Engine (`app/services/ProductService.php`, `app/controllers/site/product.php`)**:
  - Implemented `resolveDefaultProductVariant(array $variants, bool $useGlobalStrategy, ?string $strategy)`:
    - **Global Strategy**: Evaluates store-wide setting (`highest_stock`, `lowest_stock`, `first_created`). Defaults to `highest_stock` for optimized sock apparel inventory turnover and conversion.
    - **Manual Per-Product Override**: Honors explicit admin selection (`is_default = 1`).
    - **Inventory Fallback Invariant**: If the selected variant is out of stock (`stock <= 0`), the client page automatically falls back to an in-stock variant so customers never encounter a disabled add-to-cart button.
    - If all variants are out of stock, gracefully returns the target variant with accurate out-of-stock badge.
  - Refactored `app/controllers/site/product.php` down to 58 lines (< 80 lines ceiling) with zero business calculations in controller or view.
- **Store-wide Variant Strategy Setting (`app/services/SettingService.php`, `views/admin/settings.php`)**:
  - Added `default_variant_strategy` configuration to Store Settings (`highest_stock`, `lowest_stock`, `first_created`).
  - Integrated dedicated strategy select box in Settings Hub under "تنظیمات ویترین و محصولات".
- **Admin Product Workstation Enhancements (`views/admin/product_edit.php`, `app/services/ProductService.php`)**:
  - Added global strategy toggle checkbox (`#useGlobalVariantStrategy`) at the top of the variants section (checked by default for seamless high-volume product creation).
  - Added "پیش‌فرض" radio button column in the variants table with real-time JavaScript synchronization (radios are disabled when global strategy is active; enabled when manual override is selected).
  - Preserved transactional variant upsert-in-place integrity without breaking existing variant IDs.
- **Database Migration 018 (`database/migrations/018_v1.19.0_product_default_variant_strategy.sql`, `database/schema.sql`)**:
  - Added `use_global_variant_strategy TINYINT(1) NOT NULL DEFAULT 1` to `products` table.
  - Added `is_default TINYINT(1) NOT NULL DEFAULT 0` to `product_variants` table.
  - Seeded `default_variant_strategy = 'highest_stock'` in `settings` table.
  - Synchronized baseline `database/schema.sql`.

## 1.18.1 — 2026-09-30

### Hotfix: Fatal Error getActiveTheme Collision Elimination & Quality Gate Hardening

- **Hotfix: Fatal Function Redeclaration Fix (`app/services/ThemeService.php`)**:
  - Eliminated duplicate `getActiveTheme()` function declaration from `app/services/ThemeService.php` which was previously declared in `app/core/functions.php`.
  - Resolved production HTTP 500 fatal crash on direct server bootstrap.
- **Architectural Guard: Global Function Collision Scanner (`tools/verify.php`)**:
  - Added automated pre-flight collision scanner in `tools/verify.php` inspecting all function declarations across `app/core` and `app/services`.
  - Reduced `app/controllers/site/product.php` to 71 lines and eliminated variant calculation logic from `views/site/product.php`.

## 1.18.0 — 2026-09-30

### 5-Hub Storefront Architecture, Liquid Glass Bottom Dock, Customer Service Domain & DRY Refactoring

- **Storefront 5-Hub Information Architecture (`index.php`, `views/layout/header.php`, `views/layout/footer.php`, `views/site/partials/bottom_dock.php`)**:
  - Unified the entire client experience into 5 primary hubs:
    1. **Home Hub** (`/`): mobile product carousel showing ~3 cards per viewport with unconstrained horizontal swiping (`[FEAT-C003]`).
    2. **Categories Hub** (`/categories`): dedicated hierarchy tree with live product counts and subcategory chips.
    3. **Cart & Checkout Hub** (`/cart`, `/checkout`): modular card-to-card workflow and mobile responsive table-card conversion.
    4. **Search Hub** (`/search`): interactive trigger connected to live autocomplete search panel.
    5. **Account Hub** (`/account`): customer order history, active cart sidebar, and profile management (`[BUG-C005]`).
  - Implemented the **Floating Liquid Glass Bottom Dock** (`[FEAT-C004]`) for mobile & tablet viewports ($\le 992$px) featuring `backdrop-filter: blur(20px)`, dynamic cart badge counter (`#dockCartBadge`), active hub detection, and zero content overlap via safe area padding.
- **Dedicated Customer Service Layer & Controller Anti-Bloat (`app/services/CustomerService.php`, `app/controllers/site/account.php`, `app/bootstrap.php`)**:
  - Introduced `CustomerService.php`: encapsulated customer profile retrieval (`getById`), profile updating with duplicate email guards (`updateProfile`), and order history retrieval (`getOrders`).
  - Refactored `app/controllers/site/account.php` from direct SQL updates down to 35 lines of service delegation, adhering strictly to Rule 7.
- **Complete Customer Account Hub Visual Overhaul (`views/site/account.php`, `views/site/account_order.php`, `[BUG-C005]`)**:
  - Purged all admin styling leakages (`.admin-card`, `.admin-table`) from customer account views.
  - Implemented client-tailored components: `.customer-card`, `.customer-order-card`, `.account-user-meta`, Shamsi date pills, and active cart sidebar.
- **Card-to-Card Script Modularization (`views/site/card_to_card.php`, `assets/js/card-to-card.js`)**:
  - Extracted 274 lines of inline JavaScript (client-side WebP canvas compression, image validation, card-number auto-spacing) into cacheable asset `assets/js/card-to-card.js`.
  - Reduced `views/site/card_to_card.php` down to 207 presentation lines.
- **Order Service Payment Authority & Checkout Refactoring (`app/services/OrderService.php`, `app/controllers/site/checkout.php`)**:
  - Added `setPaymentAuthority(int $orderId, string $authority)` and `statusLabels()` helper methods to `OrderService.php`.
  - Refactored `app/controllers/site/checkout.php` down to 77 lines (< 80 lines ceiling), eliminating direct database updates.
  - Initialized `$formData` to eliminate raw `$_POST` reads in `views/site/checkout.php` and `views/site/signup.php`.
- **Reusable Pagination Partial & View Purity Guard (`views/site/partials/pagination.php`, `app/core/functions.php`, `views/layout/header.php`)**:
  - Introduced shared partial `views/site/partials/pagination.php` and integrated it across `views/site/category.php` and `views/site/search.php`.
  - Created `getNavCategories()` in `app/core/functions.php`, eliminating raw SQL query from `views/layout/header.php`.
  - Reduced `app/controllers/site/product.php` to 71 lines and eliminated variant calculation logic from `views/site/product.php`.

## 1.17.5 — 2026-09-30

### Settings Hub Architecture Overhaul, Dedicated Service Layers & Two-Tier Bento Modernization

- **Settings Hub Architecture & Service Layer Encapsulation (`app/services/SettingService.php`, `app/services/ThemeService.php`, `app/services/SmsPatternService.php`, `app/services/AdminUserService.php`, `app/services/ShippingService.php`, `app/services/MailboxService.php`, `app/bootstrap.php`)**:
  - Introduced `SettingService.php`: single source of truth for grouped store settings, safe file uploads, and branding logo sanitization (JPG, PNG, WebP, SVG with XML/script sanitization and EXIF metadata stripping).
  - Introduced `ThemeService.php`: encapsulates theme list, active theme switching, transactional duplication with color tokens, and safe deletion guards.
  - Introduced `SmsPatternService.php`: handles SMS pattern CRUD, dynamic variable configs with JSON serialization, and live testing via `FarazSmsService`.
  - Introduced `AdminUserService.php`: manages administrator accounts, secure BCRYPT password hashing, status toggles, and prevents self-deactivation or orphan super_admin deletion.
  - Enhanced `ShippingService.php` with administrative CRUD methods and adjacent sort order reordering.
  - Enhanced `MailboxService.php` with full accounts persistence and secret encryption.
- **Settings Hub Controller Modernization & Rule 7 Anti-Bloat Compliance**:
  - Eliminated 100% of raw SQL mutations and database queries from all 10 Settings Hub controllers, permanently fixing 11 architectural violations:
    - `settings.php`: Decomposed from 246 lines down to 32 lines.
    - `appearance.php`: Decomposed from 169 lines down to 32 lines.
    - `sms_pattern_edit.php`: Decomposed from 184 lines down to 74 lines.
    - `themes.php` (36 lines), `theme_edit.php` (38 lines), `sms_patterns.php` (34 lines), `shipping_methods.php` (32 lines), `shipping_method_edit.php` (34 lines), `email_accounts.php` (24 lines), `users.php` (55 lines).
  - Purged 238 lines of duplicate upload logic, raw SQL, and bloated switches across controllers.
- **Two-Tier Bento Settings Directory & Information Architecture (`views/admin/settings.php`, `views/admin/layout/nav_config.php`)**:
  - Re-architected `settings.php` into a Two-Tier system:
    - Tier 1: 6 Bento Hub tiles at the top linking to specialized workstations (Appearance & Themes, Shipping & Logistics, SMS Patterns & Alerts, Mailbox & IMAP, Team & Admins, Diagnostics & System) with real-time status badges.
    - Tier 2: Categorized, clean cards for General Store Settings (Business Info & Contact, Payment Gateways & Card-to-Card, Live Search & Catalog Settings, Social Media & Enamad, SEO Indexing).
  - Purged duplicate branding, announcement, and footer fields from `settings.php`, consolidating them exclusively in `appearance.php`.
  - Implemented client-side tabbed switching for legal and static CMS pages (About Us, Terms & Conditions, Privacy Policy), eliminating giant vertical scroll overflow.
  - Reordered sub-navigation tabs in `views/admin/layout/nav_config.php` for optimal desktop and mobile reach.

## 1.17.4 — 2026-09-30

### Finance Hub Architecture Overhaul, Batch Aggregation Optimization & Modern Bento Workstation

- **Finance Hub Service Layer Optimization & Ledger Engine (`app/services/AccountingService.php`)**:
  - Eliminated high-overhead N+1 query loop in `getFinancialSummary()`: replaced order-by-order item iteration (which executed 3+ queries per order row) with 3 vectorized batch aggregations across `orders`, `order_items`, and `order_gift_items`, cutting dashboard execution time down to milliseconds.
  - Implemented complete CRUD ledger service methods in `AccountingService.php`: `getExpensesList()`, `getExpenseById()`, `saveExpense()`, `archiveExpense()`, and `getExpenseCategories()`.
- **Finance Hub Controller Modernization & Rule 7 Compliance (`app/controllers/admin/finance_dashboard.php`, `app/controllers/admin/expenses.php`, `app/controllers/admin/expense_edit.php`)**:
  - Refactored `finance_dashboard.php` to 53 lines (zero raw SQL, preset date calculations: `today`, `7days`, `30days`, `this_month`, `this_year`).
  - Refactored `expenses.php` to 45 lines (zero raw SQL, paginated expense ledger and archive handler).
  - Refactored `expense_edit.php` to 63 lines (zero raw SQL, transactional save/edit delegation to `AccountingService::saveExpense()`).
- **Finance Dashboard Bento Matrix & Visual Analytics (`views/admin/finance_dashboard.php`)**:
  - 4 Key Bento KPI metric cards Above the Fold: Gross Settled Revenue, Cost of Goods Sold (COGS), Operating Expenses, and Net Profit (with dynamic emerald/crimson profit/loss badge and Net Margin percentage).
  - Financial Waterfall Breakdown progress bar visualizing distribution of gross revenue across COGS, operational expenses, and retained net profit.
  - Category-wise expense allocation bars showing expense concentrations.
  - Missing cost price alert card alerting admin when orders contain legacy items with zero cost valuation.
  - Quick filter chips for date ranges with Shamsi label presentation.
- **Expenses Ledger Workstation & Live Currency Formatter (`views/admin/expenses.php`, `views/admin/expense_edit.php`)**:
  - Bento metric overview on expenses page: Period Total, Expense Count, Top Expense Category, and Average per Expense.
  - High-density table with Persian formatted amounts, expense date chips, category badges, and safe archive/delete modals.
  - Expense creation/editing workstation with live Persian currency formatter displaying spelled-out Tomans in real time.
  - 1-click quick category chips for instant category population.

## 1.17.3 — 2026-09-30

### Products Hub & Card-to-Card Verification Workstation Architecture Overhaul

- **Products Hub Architecture & Dedicated Service Layer (`app/services/ProductService.php`, `app/bootstrap.php`)**:
  - Introduced `ProductService.php` as single source of truth for products catalog, pagination, inventory valuation, variant upserts, category trees, and safe image/entity deletions.
  - Eliminated 100% of raw SQL mutations and database queries from all 6 Products Hub controllers (`products.php`, `product_edit.php`, `categories.php`, `pricing.php`, `gift_items.php`, `gift_item_edit.php`), reducing them to clean, thin controllers strictly below 80 lines (Rule 7 compliant).
  - Decomposed `app/controllers/admin/product_edit.php` from 247 lines down to 74 lines, fully delegating complex transactional logic (slugs, SKUs, uploads, variants, tags, gallery) to `ProductService::saveProduct()`.
- **Card-to-Card Verification Workstation Overhaul (`views/admin/card_to_card_payments.php`, `app/controllers/admin/card_to_card_payments.php`, `app/services/OrderService.php`)**:
  - Re-engineered `card_to_card_payments.php` into a desktop verification workstation with Split-View inspection modal, sticky decision footer, 1-click preset rejection reasons, zoom/rotate image viewport, and admin receipt upload.
  - Optimized order item listing with high-density presentation and full viewport height scrolling.
  - Controller refactored to 54 lines with zero SQL queries, delegating to `OrderService` and `CardToCardReceiptService`.
- **Global Underline Tab Strip Sub-Navigation (`views/admin/layout/sub_nav.php`, `views/admin/layout/header.php`, `views/admin/layout/nav_config.php`, `assets/css/admin.css`)**:
  - Implemented the sleek Global Underline Tab Strip across all admin tabs as the unified sub-navigation standard.
  - Replaced ad-hoc dock pills with symmetric, accessible underline tabs with live numeric notification badges.
  - Streamlined Products Hub sub-navigation into 4 distinct functional workstations: All Products (`products.php`), Categories (`categories.php`), Bulk Pricing (`pricing.php`), and Gifts & Add-ons (`gift_items.php`).
  - Relocated "Featured" filter from sub-navigation tabs to an interactive status chip within `products.php`.
- **Products Catalog Visual Redesign & Bento Metrics (`views/admin/products.php`)**:
  - 4 Key Bento KPI metric cards Above the Fold: Total Catalog Products, Low Stock Warning ($\le 3$), Out-of-Stock Items (0), and Total Inventory Capital Valuation (Toman).
  - Segmented status filter chips: All, Active, Featured, Low Stock, Out of Stock, Discounted.
  - Live search input, category dropdown filter, multi-criteria sorting, quick boolean toggles for active and featured states, and modern numeric pagination.
- **Product Edit Workstation & Live Profit Margin Calculator (`views/admin/product_edit.php`)**:
  - Professional Two-Column Desktop Workstation: 65% Core & Pricing column and 35% Sticky Media & Publishing sidebar.
  - Dynamic JavaScript Live Profit Margin Calculator: real-time gross profit and margin percentage calculation upon editing sale price and cost price.
  - Interactive multi-variant matrix (size, color, stock, variant-specific cost price) and enhanced image gallery manager.
- **Categories & Pricing & Gifts Refinements (`views/admin/categories.php`, `views/admin/pricing.php`, `views/admin/gift_items.php`, `views/admin/gift_item_edit.php`)**:
  - Modern Split-View workstation for Categories with fast-add panel and visual depth tree.
  - Two-phase bulk price simulation wizard with variance highlights and audit history log.
  - Enhanced catalog presentation for post-order items and order gift box catalog.

## 1.17.2 — 2026-09-28

### Admin Header DRY Architecture, Mobile Datetime Widget & Collapsed Sidebar Refinements

- **Admin Header DRY Consolidation (`views/admin/layout/header.php`, `assets/css/admin.css`, `assets/css/admin-orders.css`)**:
  - Unified admin topbar styling into `assets/css/admin.css` as single source of truth across all admin tabs (Dashboard, Orders, Products, Finance, Settings).
  - Purged redundant `.dash-topbar*`, `.admin-search-box`, `.btn-dash-store-compact`, and `.dash-live-datetime*` overrides from `admin-orders.css`, guaranteeing 100% pixel-identical header presentation across all tabs.
  - Corrected mobile `.admin-main` padding (`0 0 92px 0`) and removed legacy width overrides on `.admin-search-box` to eliminate horizontal scroll overflow.
- **Mobile Header Datetime Widget Redesign (`views/admin/layout/header.php`, `assets/css/admin.css`, `assets/js/admin.js`)**:
  - Replaced redundant ticking seconds clock in mobile view ($\le 768$px) with a compact 2-line widget: Persian weekday on top line in bold brand color (`دوشنبه`), and numeric dates on bottom line (`۱۴۰۵/۰۷/۰۶ · 2026/09/28`).
  - Preserved full live ticking clock + weekday + Shamsi/Gregorian dates for desktop viewports ($> 768$px).
  - Optimized JavaScript timer (`tick()`) in `admin.js` to skip clock calculations on mobile viewports.
- **Mobile Bottom Navigation Bar (BNB) Layout Shift Elimination (`assets/css/admin.css`, `assets/js/admin.js`)**:
  - Replaced `transition: all 0.18s ease;` with explicit property transitions (`color`, `background`, `box-shadow`, `transform`), permanently eliminating text jumping and layout shift when switching tabs.
  - Standardized font weight to `700` across all states (`.nav-label`).
  - Guarded `applySidebarState` in `admin.js` against applying `sidebar-collapsed` classes on viewports $\le 900$px, preventing desktop localStorage states from polluting mobile layout on page load.
- **Desktop Collapsed Sidebar Rail Polish (`assets/css/admin.css`, `assets/js/admin.js`, `views/admin/layout/header.php`)**:
  - In collapsed 68px rail mode, hid separate toggle button (`>`) via `display: none !important;` and centered store logo mark (`AB`).
  - Added click event handler to store logo badge: clicking the badge in collapsed rail mode expands the sidebar.
  - Added hover zoom and floating tooltip `باز کردن منو ( [ )` to store logo badge.
  - Completely hid text labels (`.logo-text`, `.nav-item-label`, `.nav-chevron`, `.footer-link-text`) in collapsed mode to eliminate label overflow in 68px rail.

## 1.17.1 — 2026-09-28

### Admin Navigation DRY Architecture, Mobile Floating Pill Dock & Layer Hardening

- **Mobile Bottom Navigation Bar (BNB) DRY Architecture & Route Gatekeeper (`views/admin/layout/nav_config.php`, `views/admin/layout/footer.php`)**:
  - Implemented `shouldShowAdminBottomNav()` centralized gatekeeper in `nav_config.php`: restricts BNB rendering exclusively to the 5 primary hubs (`index.php`, `dashboard.php`, `orders.php`, `products.php`, `finance_dashboard.php`, `settings.php`).
  - Completely omitted `<nav class="admin-bottom-nav">` from DOM on all detail/sub/editor pages, permanently eliminating visual overlap and blocking of sticky action docks (e.g. `#mobileQuickDock` on `order_detail.php`).
- **Unified Floating Pill Dock Styling (`assets/css/admin.css`, `assets/css/admin-orders.css`)**:
  - Consolidated floating pill dock styles into `assets/css/admin.css` as single source of truth across all admin tabs (Dashboard, Orders, Products, Finance, Settings).
  - Stripped all redundant, conflicting `.admin-bottom-nav` CSS rules and media queries from `admin-orders.css`.
  - Converted floating dock positioning to standard `left: 0; right: 0; margin-inline: auto; width: fit-content;` preventing sub-pixel transform jitter and RTL coordinate inversion across mobile browsers.
- **Dynamic Asset Cache-Busting (`views/admin/layout/header.php`, `views/admin/layout/footer.php`)**:
  - Attached dynamic `filemtime` timestamps (`?v=APP_VERSION.timestamp`) to all admin stylesheets (`style.css`, `admin.css`, `admin-orders.css`) and scripts (`main.js`, `admin-image-optimizer.js`, `admin.js`), guaranteeing immediate client cache invalidation on deployment without requiring manual `Ctrl+F5`.
- **Defensive Styling & Fallback Hardening**:
  - Added strict user-agent button resets (`appearance: none; background: transparent; border: none;`) to all `.admin-sidebar button` elements to prevent unstyled white box defaults.
  - Added inline `style="display:none;"` to `.nav-flyout-header`, ensuring it remains hidden in standard layout flow while cleanly appearing in collapsed flyouts via `!important`.

## 1.17.0 — 2026-09-28

### Modern Visual Redesign of Admin Orders Hub & Order Detail Experience

- **Complete Visual Redesign of Orders Tab (`views/admin/orders.php`, `assets/css/admin-orders.css`)**:
  - Full-width modern Bento workspace strictly scoped via `.admin-page-orders` body class, aligned with modern dashboard aesthetics.
  - 4 Key Bento KPI metric cards Above the Fold: Urgent Pending / Receipt Inspection, Net Settled Sales, Total Store Orders, and Packaging & Dispatch Stream.
  - Interactive status tabs with real-time counters and multi-parameter filter hub (search, payment method, payment status, date ranges).
  - Adaptive Mobile Cards Stack & Native Bottom Sheet Dossier (< 860px) with touch swipe-to-dismiss gesture support, 1-tap dialer/SMS, and direct tracking code input.
- **Modern Responsive Redesign of Order Detail Page (`views/admin/order_detail.php`, `app/controllers/admin/order_detail.php`)**:
  - Two-column responsive workspace with sticky mobile quick dock (`#mobileQuickDock` for $\le 768$px) enabling fast-dial and smooth-scroll pulse focus to status card.
  - Multi-image product gallery modal (`#productGalleryModal`) with thumbnail badges, keyboard navigation (`ESC`, arrows), and touch-swipe gestures.
  - Dual presentation: clean high-density desktop items table and adaptive mobile item cards stack ($\le 680$px) with variant chips and zoom previews.
  - Card-to-Card receipt lightbox modal with DirectAdmin Apache/PHP-FPM MIME-safe authenticated streaming via `/admin/order_receipt.php?id=...`.
  - 1-click postal label clipboard copy engine (`copyPostalLabel()`) targeting structured customer delivery credentials.
  - Architecture refinement: zero SQL in `order_detail.php` controller (74 lines), full encapsulation in `OrderService::getOrderItemsWithGallery()` and `OrderService::getOrder()`.
- **Global Header Polish & Topbar Enhancements (`views/admin/layout/header.php`, `assets/css/admin.css`)**:
  - Compact pill search bar and storefront button.
  - Stacked 2-line real-time live clock and Shamsi date widget.
  - Conditioned `admin-orders.css` stylesheet injection in `<head>`.
- **Postal Parcel Tracking Integration & Database Migration**:
  - Added official `tracking_code VARCHAR(100) DEFAULT NULL` column to `orders` table via migration `database/migrations/017_v1.17.0_order_tracking_code.sql` with safe idempotent procedure.
  - Updated baseline `database/schema.sql`.
  - Defensive feature-detection (`OrderService::hasTrackingCodeColumn()`) ensuring shared hosting deployments never throw SQL errors if uploaded prior to manual migration execution via phpMyAdmin.
- **Controller Refactoring & Anti-Bloat Architecture**:
  - Refactored `app/controllers/admin/orders.php` into 55 lines and `app/controllers/admin/order_detail.php` into 74 lines of clean service delegation, adhering strictly to Rule 7 (< 80 lines ceiling) with 0 raw SQL mutations.
  - Added robust transactional administrative methods to `app/services/OrderService.php`: `getAdminOrders()`, `getAdminOrderStats()`, `getAdminStatusCounts()`, `updateOrderStatus()`, `updateTrackingCode()`, `bulkUpdateStatus()`, `deleteOrder()`, `getOrder()`, and `getOrderItemsWithGallery()`.

## 1.16.0 — 2026-09-25

### Modern Visual Redesign of Admin Dashboard Tab (High-Density UI & Adaptive Layout)

- **Complete Visual Redesign of Dashboard Tab (`views/admin/dashboard.php`)**:
  - Re-engineered the dashboard tab visually without touching or disrupting other admin tabs/sections, strictly scoped via `.admin-page-dashboard` body class.
  - Eliminated oversized greeting banners, duplicate action buttons, and status texts; placed the 5 core KPI metric cards right at the very top (Above the Fold) with zero scroll required.
  - Unlocked 100% full viewport width on desktop for the dashboard tab by removing the static 256px sidebar and introducing a centered, frosted glass **Floating Dock Bottom Navigation Bar** with 5 primary tabs.
  - Reduced global base font scale to 13.5px and refined card paddings/spacings, delivering a modern high information density UI matching tools like Linear and Stripe.
- **Dynamic Live Revenue & Orders Trend Chart (SVG Area Spline)**:
  - Computed real 7-day revenue and order counts dynamically from database records.
  - Interactive data nodes with floating tooltips displaying formatted revenue and order count for each day.
- **Recent Orders Table & Adaptive Mobile Cards Stack**:
  - Enforced `white-space: nowrap` on order codes (`#ORD-XXXX`), customer names, phone numbers, prices, and status badges, permanently resolving text-wrapping and squishing bugs on lower resolutions.
  - Responsive layout: clean table on viewports $\ge 860$px, and automatic seamless transition to an **Adaptive Order Cards Stack** on smaller tablet/mobile viewports ($< 860$px).
  - 1-click clipboard copy for order codes with visual checkmark toast feedback.
- **Operational Real-time Feeds & Widgets**:
  - Quick Actions hub with 4 direct links: New Product, Pricing Management, Appearance/Landing, and SMS Patterns.
  - Card-to-Card instant review widget: displays the latest pending receipt with direct jump to order detail, or a clean verified state when none is pending.
  - Critical low-stock monitoring widget displaying items with stock $\le 5$ with capacity progress bars.
- **Command Palette & Keyboard Navigation**:
  - Compact search trigger button in the topbar with `Ctrl+K` keycap, opening a modern search modal connected to `/ajax/admin_search.php`.
- **Versioning**:
  - Bumped `APP_VERSION` to `1.16.0` in `app/bootstrap.php`.

## 1.15.0 — 2026-09-24

### Admin Architecture Redesign (FEAT-A003), Global Live Search (FEAT-A004), SMS Patterns Management (FEAT-A002), and Landing Section Controls (FEAT-A005 / FEAT-C002)

- **Admin Navigation Restructure & Mobile Bottom Navigation Bar (FEAT-A003)**:
  - Streamlined 25+ cluttered sidebar links down to **5 core functional groups**:
    1. 📊 **Dashboard** (`index.php`)
    2. 📦 **Orders** (`orders.php`, `card_to_card_payments.php`) — includes a live numeric badge showing count of pending orders.
    3. 🛍️ **Products** (`products.php`, `categories.php`, `pricing.php`, `gift_items.php`)
    4. 📈 **Finance** (`finance_dashboard.php`, `expenses.php`)
    5. ⚙️ **Settings** (`settings.php`, `appearance.php`, `sms_patterns.php`, `email_accounts.php`, `shipping_methods.php`, `users.php`, `diagnostics.php`)
  - Introduced a floating, frosted glass **Bottom Navigation Bar** for Mobile & Tablet viewports for effortless single-hand thumb reach.
  - Eliminated the giant vertical sidebar on mobile screens (`< 900px`), maximizing usable content space.
  - Implemented an automatic horizontal topic sub-navigation pill strip (`views/admin/layout/sub_nav.php`) rendered at the top of every section with smooth touch scrolling.
- **Admin Global Live Search with Auto-suggest (FEAT-A004)**:
  - Embedded an intelligent search input in the admin topbar with keyboard shortcut support (`Ctrl+K` or `/`).
  - Secure, authenticated JSON endpoint at `/ajax/admin_search.php`.
  - Instant grouped suggestions across:
    - ⚙️ **Admin Pages & Topics** (instant search across page names and semantic keywords)
    - 📦 **Orders** (searches by order code, customer name, mobile phone number, or courier tracking code with status pills)
    - 🛍️ **Products** (searches by title and SKU, showing thumbnails, prices, and live inventory)
  - Full keyboard accessibility (ArrowDown, ArrowUp, Enter, Escape) and click-outside dismissal in `assets/js/admin.js`.
- **SMS Patterns Management via Admin Panel (FEAT-A002)**:
  - Created new `sms_patterns` table storing pattern codes, titles, reference pattern texts, descriptions, system event keys, variable counts, and active status.
  - New admin pages `admin/sms_patterns.php` and `admin/sms_pattern_edit.php` with dedicated controllers and views.
  - Dynamic Variable Builder: dynamically add/remove variables with custom variable name, data type (numeric, string, alphanumeric), max length limit, and Persian label, serialized as structured JSON.
  - Live test sending card: test send SMS patterns with real attributes to any mobile number with instant validation.
  - Service upgrade: `FarazSmsService.php` prioritizes database patterns for OTP and events, while preserving zero-breakage fallback to `config.php` constants.
- **Home / Landing Section Visibility & Appearance Controls (FEAT-A005 & FEAT-C002)**:
  - Dedicated "Appearance & Storefront" page (`admin/appearance.php`).
  - Independent visibility and title controls for all 5 homepage sections: top intro banner (disabled by default per FEAT-C002), large category cards (disabled by default on mobile), featured carousel, newest carousel, and category pill strip.
  - Conditional querying in `app/controllers/site/home.php`: disabled sections bypass database queries entirely.
  - Reorganized `settings.php` into a clean responsive grid and moved branding/themes/announcements to Appearance, directly resolving BUG-A006.
- **Database & Versioning**:
  - Migration file `database/migrations/016_v1.15.0_sms_patterns_and_home_sections.sql` mirrored in baseline `database/schema.sql`.
  - Bumped `APP_VERSION` to `1.15.0` in `app/bootstrap.php`.

## 1.14.0 — 2026-09-23

### Default Variant Selection Bug Fix (BUG-C001) & Live Search Autocomplete (FEAT-C001)

- **Product Page Default In-Stock Variant Selection (BUG-C001)**:
  - On products with size/color variants, the first in-stock variant (`stock > 0`) is now automatically selected and highlighted (`checked` and `.selected`) on page load. Direct clicks on "Add to Cart" no longer fail with false out-of-stock messages caused by missing variant IDs.
  - Displays selected variant label prominently next to the section title (`سایز / رنگ: ...`) for instant visual clarity.
  - Real-time client updates on variant change: adjusts displayed price (for variants with `price_override`), updates stock status badge for that variant, and syncs the quantity stepper max limit.
  - Preserves selected variant state visually after adding to cart, enabling immediate follow-up additions without disorientation.
  - Backend validation hardened in `ajax/cart_add.php`: enforces variant selection whenever a product has variants, and verifies that the requested quantity is within the variant's actual inventory.
- **Storefront Live Search & Autocomplete (FEAT-C001)**:
  - Dedicated lightweight JSON endpoint at `/ajax/search_suggest.php`.
  - Dropdown suggestion menu under the header search bar displaying product thumbnail, name with search query highlighted (`<mark>`), category badge, sale price/discount, and stock status.
  - Matching category suggestion pills above products and a "View all results (X products)" footer link.
  - Shared hosting performance optimizations: 250ms debouncing, in-flight request cancellation via `AbortController`, and an in-memory client cache to eliminate redundant requests when editing queries with Backspace.
  - Full keyboard accessibility: Arrow Up/Down navigation across suggestions, Enter to navigate to highlighted item, Escape to dismiss, plus a quick clear button.
  - Strict compliance with `RULE-UI001` with dedicated touch and layout considerations across Mobile, Tablet, and Desktop.
- **Configurable Storefront Search Settings in Admin Panel**:
  - New settings card under Store Settings: toggle live search on/off, set suggestion limit (default: 6), minimum character threshold (default: 2), search scope checkboxes (product name, description), and category suggestions toggle.
  - Database migration `database/migrations/015_v1.14.0_search_settings.sql` and mirrored into `schema.sql`.

## 1.13.0 — 2026-09-23

### Critical post-deploy bug fixes & Image Optimizer Enhancements

- **Resolved WebP 500 Internal Server Error & rendering on live site** — Root cause: production runs on DirectAdmin with Nginx reverse proxy + Apache backend (PHP-FPM). DirectAdmin's restricted `AllowOverride` caused directives (`Options -ExecCGI`, `php_flag`, and `ForceType`) in `uploads/.htaccess` to trigger an immediate Apache 500 error on any `/uploads/` request.
  1. Completely sanitized `uploads/.htaccess`: removed non-permitted directives and retained only standard script execution denial (`<FilesMatch> Require all denied </FilesMatch>`) and `Options -Indexes`.
  2. Updated root `.htaccess`: targeted `/img.php?f=/uploads/$1` with leading slash (required for PHP-FPM) and restricted proxying specifically to real `.webp` files, allowing JPG, PNG, GIF, and SVG to be served natively with zero PHP overhead.
  3. `img.php` proxy: enforces `Content-Type: image/webp`, supports conditional GET (ETag / 304), and sets 1-year Cache-Control.
- **Image optimization quality range & default adjustments**:
  - Quality/compression slider range widened to **10% – 90%** (step 5).
  - Default optimization quality set to **30%** (across main product images, gallery, gift items, and logo).
- **Full-Screen Quality Inspector**:
  - Modal with interactive Zoom (20% to 500% via mouse wheel and buttons), Pan/drag support, live recompression slider, and hold-to-compare against original raw image.
- **CSS cached on browser after deploy** — `style.css` and `admin.css` `<link>` tags now include `?v=APP_VERSION`; each version bump forces browsers to re-fetch stylesheets.
- **Hosting constraints documentation**: fully documented DirectAdmin Nginx+Apache stack and `.htaccess` limits across `AGENTS.md`, `README.md`, `ARCHITECTURE.md`, and `ARCHITECTURE.en.md`.

## 1.12.0 — 2026-09-23


### Optimized & Fixed (BUG-A001)
- Client-side Canvas-based image optimization pipeline in admin panel (`assets/js/admin-image-optimizer.js`): automatic WebP conversion, intelligent aspect-preserving resizing, and 90-98%+ file size reduction without shared hosting RAM/CPU overhead.
- Full support for large raw uploads (up to 30-40 MB) and iPhone camera format (HEIC/HEIF) via on-demand vendored decoder (`assets/js/vendor/heic2any.min.js`).
- Complete elimination of sensitive data, GPS coordinates, and camera EXIF metadata in the browser, with defense-in-depth verification on backend.
- Interactive Live Preview widget showing original vs compressed file sizes, savings percentage, dimensions, and live quality slider.
- Integrated across product main and gallery images, gift box / gift item images, and site branding logo (preserving vector SVG).
- Full compliance with RULE-UI001 with custom responsive views for mobile, tablet, and desktop.
- Backend verification with `getimagesize()` and EXIF sanitization in `functions.php` and `settings.php`.

### Added (FEAT-A001)
- Standardized naming for all uploaded files: product main images (`product-{ID}-main-{hash}.ext`), gallery (`product-{ID}-g{sort}-{hash}.ext`), gift items (`giftitem-{ID}-main-{hash}.ext`), and site logo (`logo-site-{hash}.ext`).
- New helpers `generateStandardFilename()` and `renameUploadedImage()` in `app/core/functions.php` for producing human-readable filenames and renaming temp files after INSERT.
- `handleProductImageUpload()` now accepts optional `$entityType`, `$entityId`, and `$role` parameters.
- One-time migration script (`admin/migrate_image_names.php`) to rename existing images — super_admin only.

## 1.10.0 — 2026-09-16

### Added
- Admin mailbox client with switchable email accounts, IMAP inbox viewing, message reading, and SMTP sending via PHPMailer.
- Encrypted mailbox passwords at rest using the application secret.
- Admin email-account configuration and dedicated mailbox navigation.
- Release deployment builder now creates a web-root-only package without database files or runtime uploads.

# Changelog

## 1.9.1 — Deployment packaging and database-backed storefront content

- Moved editable public business/legal content fully into the `settings` database table; removed the private content seed files.
- Added a production deployment builder at `tools/build-deploy.ps1` that generates a clean `deploy/` tree and `dist/AB-Socks-vX.Y.Z-deploy.zip`.
- Deployment packaging uses an explicit production allowlist and excludes Git metadata, AI/editor tooling, documentation, database files, local secrets, and runtime uploads.
- Moved release deployment notes from the project root to `docs/deployment/`.

This document lists all project releases in detail. Each new release documents its changes **step by step against the previous version**.

Versioning format: `MAJOR.MINOR.PATCH` (Semantic Versioning)

---

## [1.9.0] — Verified customer checkout and card-to-card payment

### Summary
Guest shoppers can still add products to a session cart without signing in, but completing checkout now requires a customer account with a verified mobile number. The old cash-on-delivery/phone-coordination option was removed and replaced by configurable Zarinpal and card-to-card payment flows.

### 👤 Checkout and Guest Cart
- Guest session carts remain available for browsing and adding/editing items without authentication.
- A guest entering `/checkout` is redirected to `/signup?next=/checkout`.
- The `next` destination survives signup/incomplete-login, phone verification, and, when applicable, email verification.
- After successful phone verification, the existing guest session cart is merged into the customer's persistent cart and checkout resumes.
- Orders can only be created for an authenticated, phone-verified customer.

### 💳 Payment Methods
- Cash-on-delivery / phone coordination was removed from Checkout.
- Zarinpal can be enabled or disabled from the admin settings.
- Card-to-card payment is configurable with a card number, card-holder name, and optional customer-facing note.
- A card-to-card order is created only after a receipt image has been uploaded successfully, so stock is not reserved before the customer actually submits the receipt.
- Card-to-card payments remain `unpaid` until an admin reviews them and can be marked `paid` or `failed`.

### 🧾 Card-to-card Receipt
- Added `/payment/card-to-card`.
- Card number and final amount can be copied with one tap/click.
- Receipt upload supports drag & drop, immediate preview, live progress, and replacing the selected image.
- Only JPG/PNG/WEBP images up to 2 MB are accepted; files are stored privately.
- Receipts are not publicly served and are streamed only through an authenticated admin endpoint.

### 🛠️ Admin Panel
- Added a dedicated "Card-to-card review" page to the sidebar.
- Admins can review receipts, approve/reject payment status, and change the order status.
- Order-status changes trigger the existing order-status SMS; payment approval/rejection also triggers the corresponding payment notification.

### 🗄️ Database
- Added migration `012_v1.9.0_guest_checkout_card_to_card.sql`.
- Added `orders.payment_method`, `orders.card_to_card_receipt`, and `orders.card_to_card_submitted_at`.
- Payment configuration uses the existing key/value `settings` table; no new settings table was needed.

## [1.8.2] — Editable Public Content and Business Information

### Summary
The About, Terms, Privacy, and contact/business-information pages are no longer hard-coded. They are editable from the admin panel. To keep real business information out of GitHub, initial production values live in `config/private_content.php`, which is gitignored; after an admin saves a value, the database becomes the source of truth.

### ✨ Dynamic Content
- Business/contact details are stored in the existing `settings` key/value table.
- About, Terms, and Privacy copy can be edited from the admin panel.
- Page content uses a deliberately limited `## heading` / `- item` / paragraph format; arbitrary HTML is not accepted.
- The Footer now consumes the stored business/contact information and editable brand tagline.

### 🔒 Private Data Outside the Repository
- `config/private_content.php` is gitignored.
- `config/private_content.example.php` contains placeholders only and is safe to publish.
- The real About story, legal copy, address, phone numbers, and support email are not stored in public repository files.

### 🧩 Storefront Pages
- Added `/privacy` with its controller and view.
- Terms and About now render database-backed content.
- Contact displays dynamic business details and no longer shows a false success message for the currently non-functional form.

### 🗄️ Database Changes
No new table or column is required. The existing `settings` key/value table is sufficient, so there is no new migration.

## [1.8.1] — SMS OTP AutoFill and Faraz Pattern Update

### Summary
Adds origin-bound SMS OTP support for the phone-verification screen and updates the Faraz SMS integration to match the new registered pattern. The existing verification backend, database schema, expiry rules, attempt limits, and resend throttling remain unchanged. This is an application-layer/browser-integration release only; no database migration is required.

### ✨ Faraz SMS Pattern Update (`app/services/FarazSmsService.php`)
The registered Faraz pattern now uses two variables because the provider requires the OTP value to appear both in the human-readable part of the message and in the browser-recognized origin-bound OTP line:

```text
کد احراز:
%code%
AB Socks-Shop

@absocks.ir #%code2%
```

`FarazSmsService::sendOtp()` sends the same six-digit value to both variables: `%code% = $code` and `%code2% = $code`. The primary variable name remains configurable through `FARAZ_OTP_PATTERN_VAR`; the secondary `code2` variable is fixed because it belongs to the currently registered Faraz pattern. The API continues to use `number_format = 'english'`.

The final line is intentionally the last line of the SMS and binds the OTP to `absocks.ir` using the standard `@domain #OTP` structure.

### ✨ Browser OTP AutoFill (`views/site/verify_phone.php`)
The phone-verification input now declares:

- `autocomplete="one-time-code"` for browser-native OTP recognition/autofill
- `inputmode="numeric"` and `pattern="[0-9]{6}"` for a six-digit numeric code
- `maxlength="6"` to match the server-side OTP length

In addition, the page feature-detects the WebOTP API. On supporting browsers, it requests an SMS OTP using `navigator.credentials.get()` with the `sms` transport, places the returned code into the existing input, and submits the existing verification form. Unsupported browsers keep the normal manual-entry flow.

WebOTP requires HTTPS and has limited cross-browser availability, so it is an enhancement rather than the only verification path. The standardized `autocomplete="one-time-code"` + origin-bound SMS format remains the cross-browser path.

### ⚠️ Current Flow Limitation
The signup and incomplete-login controllers currently send the SMS before redirecting to `/verify-phone`. Therefore, the WebOTP listener is established after the SMS dispatch request has already happened. If the SMS arrives before the WebOTP request is active, programmatic WebOTP capture may not occur; native `one-time-code` autofill can still work independently. Making WebOTP timing fully deterministic would require a client-initiated SMS send after the WebOTP listener starts, which would be a larger authentication-flow change and is intentionally outside 1.8.1.

### 🔒 Security / Verification Contract
No changes were made to the verification database contract:

- OTPs remain six-digit values generated with `random_int()`
- only `sha256` hashes are stored
- expiry remains 10 minutes
- maximum incorrect attempts remain 5
- resend cooldown remains 60 seconds
- `verifyCode()` continues to validate against the latest unconsumed verification record

No database migration is required.

---

## [1.8.0] — Store Accounting: Order Profitability, Expense Ledger, Financial Dashboard

### Summary
Completes the accounting groundwork laid by price history (1.5.0), gift/post-order tracking (1.6.0), and shipping cost (1.7.0): every order can now show exactly how much profit it generated, a general expense ledger records costs that aren't tied to a specific sale, and a financial dashboard summarizes both over a date range. Everything here is read-only reporting over data other parts of the system already snapshot — nothing writes back to an order, product, or price record. Migration 011 adds one nullable column to `order_items`, splits `shipping_methods.cost` into a charged/actual pair, and adds the `expenses` table; nothing existing is altered in place beyond that.

### ✨ Order-Level Profitability (`app/services/AccountingService.php`)
`getOrderProfitability()` computes an order's revenue (line items minus discount, plus post-order and shipping revenue) against its cost (product/variant cost at sale time, gift/post-order cost, shipping's actual cost) to arrive at gross profit — shown as a full breakdown on the admin order detail page. This required a real gap to close: `order_items` had never recorded what a product actually cost the store at the moment it was sold, only its `price` history. `order_items.unit_cost_price` now snapshots that, resolved with the same variant-overrides-product precedence `price_override` already uses for the sale price, and populated once at checkout — `cartDetails()` (`app/core/cart.php`) had to start selecting `cost_price` alongside the fields it already returned to make that possible. Orders placed before this release, and any line whose product had no cost price on record, are excluded from the cost total rather than guessed at, and the result is flagged so the UI can show that as a caveat instead of presenting an inflated profit as exact.

### ✨ Shipping's Cost Side
`shipping_methods.cost` (what a customer is charged, from 1.7.0) and the new `shipping_methods.actual_cost` (what the store actually pays a courier or post service) are now separate — 1.7.0 only tracked the first, which meant every order looked like it shipped for free from the store's own perspective. `actual_cost` is backfilled to match `cost` for existing methods so nothing suddenly shows a fabricated 100% margin; an admin can set the real figure separately. `orders.shipping_actual_cost` snapshots it per order the same way `shipping_method_name` already does, and waiving a method's free-shipping threshold only waives the customer-facing charge, not the store's real cost.

### ✨ Expense Ledger (`admin/expenses.php`, `expense_edit.php`)
A general ledger for costs that aren't a specific product sale — hosting, packaging, advertising, and so on — with a free-text category (suggested from a `<datalist>`, not an enum, so a new category never needs a migration), search, and category/date filtering. "Deleting" an expense archives it (`status = 'archived'`) rather than removing the row, so it drops out of every report while the record and who created it survive.

### ✨ Financial Dashboard (`admin/finance_dashboard.php`)
Date-range summary (defaulting to the current month) combining every non-cancelled order's profitability with the expense ledger: total revenue, cost of goods/gifts/shipping sold, gross profit, total expenses, and net profit, plus a per-category expense breakdown. Access is gated the same as every other admin page — not restricted to `super_admin`, since there's no finer permission system to restrict it with and the requirement was explicit that finance shouldn't default to super-admin-only.

### 🗄️ Database Changes
`database/migrations/011_v1.8.0_accounting.sql` — `order_items.unit_cost_price`; `shipping_methods.actual_cost` (backfilled from `cost`) and `orders.shipping_actual_cost`; the `expenses` table.

---

## [1.7.0] — Shipping Cost Calculation

### Summary
Replaces the hardcoded `$shippingCost = 0` that's been in `checkout.php` since the first release with an actual, admin-configurable shipping cost, calculated per order from the customer's province with a live estimate shown before they submit. No changes to existing behavior for anything other than the shipping line itself; migration 010 is additive only.

### ✨ Shipping Methods (`admin/shipping_methods.php`)
A `shipping_methods` row matches an order one of two ways: `province_contains` applies when the customer's (free-text) province field contains a configured value (e.g. "تهران"), and `default` is the fallback used when nothing more specific matches. Methods are evaluated in a configurable order (reorderable from the admin list with up/down controls), so specific rules can be placed ahead of the fallback. Each method also supports an optional `free_above_amount` — once an order's subtotal reaches it, that method's cost is waived. Two starter methods are seeded, matching the shipping copy already shown in the site footer (a Tehran courier rate and a default post rate for everywhere else), both at zero cost until an admin sets real prices.

### ✨ Live Shipping Estimate at Checkout
The checkout page now shows a shipping cost estimate that updates as the customer types their province, via a new `ajax/shipping_estimate.php` endpoint — recomputed from scratch server-side each time (never trusting a client-supplied cost), and purely a display convenience: the authoritative calculation happens again, independently, when the order is actually submitted. The cart page shows an informational note that shipping is calculated on the next (checkout) step, since a customer's address isn't known yet at that point.

### 🗄️ Snapshotting
`orders.shipping_method_name` records the matched method's name at order time, alongside the existing `shipping_cost` column — the same reasoning as every other snapshot column already on `orders`/`order_items`/`order_gift_items`: a later change to a shipping method's name or price must not alter what an existing order says it shipped by. Both the admin order detail page and the customer's own order history page now show the shipping line (and, where applicable, paid post-order add-ons), which they didn't display at all before this release.

### 🗄️ Database Changes
`database/migrations/010_v1.7.0_shipping.sql` — `shipping_methods` table (with two seeded starter methods) and `orders.shipping_method_name`.

### 📋 Planned Next
Store accounting (see `ARCHITECTURE.md` §8) is designed but not yet implemented — it depends on price history, gift/post-order, and this release's shipping cost all already recording their own history, which they now do.

---

## [1.6.0] — Gift Box / Post-Order Items

### Summary
Introduces a single new catalog concept — a "gift item" — that an admin can attach to an order for free, offer to customers as a paid checkout add-on, or both, without it being two separate systems. Every attachment to an order is fully snapshotted, the same way products already are, so a later change to an item's cost or price never rewrites an existing order's numbers. No changes to existing behavior; migration 009 is additive only.

### ✨ Gift Box / Post-Order Catalog (`gift_items`)
A `gift_items` row has its own name, image, stock, and `cost_price`, plus two independent flags: `is_giftable` (an admin can attach it to an order for free) and `is_post_orderable` (a customer can add it as a paid checkout add-on, at its own `post_order_price`, separate from cost). Both flags can be on at once — the same item can be a free gift today and a paid add-on tomorrow without being recreated, since there was never a real distinction between the two beyond how a given attachment is used. Managed from `admin/gift_items.php` (list) and `admin/gift_item_edit.php` (create/edit), reusing the same image upload path as products.

### ✨ Admin: Gifting an Item to an Order
`admin/order_detail.php` now has a form to attach a giftable item, with a quantity and an optional note, to any existing order. `assignGiftToOrder()` (`app/services/GiftService.php`) locks the item's row, decrements stock with a guarded conditional update, and records an `order_gift_items` row with `unit_selling_price = 0` — no charge to the customer, but the store's actual cost is preserved for later accounting. The order detail page lists everything already attached, gift or post-order, with who assigned it and when.

### ✨ Storefront: Post-Order Add-Ons at Checkout
The cart page now offers active, in-stock, post-orderable items as optional paid add-ons; a selection is held in `$_SESSION['post_order_selection']`, the same session-based pattern already used for an applied coupon. It's re-validated against the live catalog on the cart page and again at the top of `checkout.php` — a line that fails re-validation at that point (e.g. stock ran out) is silently dropped rather than blocking the order, since it's an optional add-on rather than what the customer came to buy. Selected post-order lines are included in `orders.gift_items_total` and the order total, and are written into `order_gift_items` inside the same transaction as the rest of the order — a stock failure on a gift item rolls back the whole order exactly like a stock failure on a regular product would.

### 🐛 Bug Fix: Image Upload Helper Was Trapped Inside a Single Controller
`handleProductImageUpload()` was defined inside `app/controllers/admin/product_edit.php` and only existed for the duration of that one page's request — calling it from anywhere else would have been a fatal "undefined function" error. Found while wiring up gift item image uploads; moved to `app/core/functions.php`, which is loaded on every request, so both `product_edit.php` and the new `gift_item_edit.php` can use it.

### 🗄️ Database Changes
`database/migrations/009_v1.6.0_gift_post_order.sql` — `gift_items`, `order_gift_items` tables, plus `orders.gift_items_total`.

### 📋 Planned Next
Shipping cost calculation and store accounting are designed (see `ARCHITECTURE.md` §8) but not yet implemented. Accounting is sequenced after shipping since its numbers depend on shipping cost already being recorded per order.

---

## [1.5.0] — Cost/Sale Price History, Bulk Pricing, Admin-Managed Theme System, and Admin Navigation Reorganization

### Summary
This release lays the groundwork for the store's upcoming accounting and inventory features: every cost and sale price change is now individually audited, a bulk pricing tool lets several unrelated products be repriced at once with a preview step before anything is written, and a long-standing bug that silently reset product variant ids on every save has been fixed. Alongside this, the color system introduced in 1.4.0 is now a proper admin-managed multi-theme setup instead of a fixed stylesheet, and the admin sidebar has been reorganized into labeled groups. No breaking changes; migrations 007 and 008 are additive only.

### ✨ Cost/Sale Price History
`products` and `product_variants` both gained a `cost_price` column, independent of the existing sale price columns. Neither is ever written directly — every change goes through `recordPriceChange()` (`app/services/PricingService.php`), which locks the target row, computes the new value from a fixed amount, a percentage, or a direct value, and records the change in a new `price_history` table: previous value, new value, computed amount/percentage, method, an optional reason, and who made it. A `price_history` entry survives its variant being deleted later (`ON DELETE SET NULL` plus a stored `variant_label`, mirroring how `order_items` already handles this), so historical entries stay meaningful. `admin/product_edit.php` shows a product's full price history at the bottom of the edit page.

### ✨ Bulk Pricing (`admin/pricing.php`)
Lets an admin select any set of products — not tied to a category — and apply the same price change to all of them in one request. The flow is preview-then-confirm: submitting a selection computes and displays every product's new value without writing anything, and only an explicit second submission applies it. Each bulk request creates a `bulk_price_operations` row, and every resulting `price_history` row references it, so a later question like "what did this bulk update actually change" has a direct answer. Each product's change is its own transaction — one product failing (e.g. a concurrent edit) doesn't discard the rest of an otherwise-successful batch.

### 🐛 Bug Fix: Product Variants Lost Their Identity on Every Save
`admin/product_edit.php` used to delete and recreate every variant of a product on every save, regardless of whether anything about them had changed — so a variant's id (and anything referencing it) became meaningless after the very next edit. The form now tracks each variant's id in a hidden field; saving updates matching rows in place, inserts genuinely new ones, and only deletes rows that were actually removed from the form. This was found and fixed specifically because the new price-history feature needed variant ids to be stable across edits.

### 🎨 Admin-Managed Theme System
The color system is now backed by `themes`/`theme_tokens` tables instead of being fixed in `assets/css/style.css`. An admin can create, edit, and switch between full color palettes from `admin/themes.php` and `admin/theme_edit.php`; the active theme is applied site-wide by injecting its tokens as a `<style>` override in the storefront `<head>` — no file changes or deployment needed to change the site's look. Four starter themes, matching the palettes compared in `theme-preview.html`, are seeded automatically. Falls back cleanly to the stylesheet's built-in defaults if a theme has no tokens.

### 🗂️ Admin Navigation Reorganization
The admin sidebar is now grouped by domain (Products, Orders, Settings, and an Administration group visible only to `super_admin`) instead of one flat list, to keep it navigable as more admin pages are added — the new "Bulk Pricing" and "Theme" pages are filed under Products and Settings respectively rather than tacked onto the end.

### 🗄️ Database Changes
- `database/migrations/007_v1.5.0_theme_system.sql` — `themes`, `theme_tokens` tables, plus four seeded starter palettes.
- `database/migrations/008_v1.5.0_price_history.sql` — `cost_price` on `products`/`product_variants`, plus `bulk_price_operations` and `price_history` tables.

### 📋 Planned Next
Gift box / post-order items, shipping cost calculation, and store accounting are designed (see `ARCHITECTURE.md` §8) but not yet implemented — each depends on the price-history groundwork in this release, and accounting additionally depends on the other two, so they're being built in that order rather than in parallel.

---

## [1.4.0] — Brand Color Palette, Homepage Redesign, and Favicon

### Summary
This release is focused on the storefront UI. The site's color system has been aligned with the current visual identity, the homepage has been redesigned around category-first navigation and product carousels, and a complete favicon/icon set has been added. No database changes are included in this release.

### 🎨 Brand Color Palette
The previous palette no longer matched the current visual identity. The new palette is based on the main colors used in the store logo, with the core UI colors centralized in the `:root` block of `assets/css/style.css`:

```css
--color-bg:            #F9E9DA;  /* page background */
--color-surface:       #F5E5D6;  /* cards / sections */
--color-primary:       #582B1C;  /* buttons / CTAs */
--color-primary-dark:  #3E1D12;  /* button hover / dark details */
--color-primary-light: #EAD6C7;  /* badges / tinted sections */
--color-accent:        #B89180;  /* secondary details */
--color-text:          #7D5141;
--color-muted:         #9C7C6C;
--color-border:        #E7D2BF;
```

Darker tones are reserved for elements that need to carry white text. `#B89180` does not provide sufficient contrast with white text for primary controls, so it is used for badges, hover states, and secondary details instead.

A working file named `theme-preview.html` is also used to compare candidate palettes. It is a design/reference file rather than part of the application's runtime logic.

### 🖼️ Favicon and Device Icons
The site's icon set was generated from the store logo:

- `favicon.ico` at 16, 32, and 48 pixels
- `favicon-16x16.png`
- `favicon-32x32.png`
- `apple-touch-icon.png` at 180 pixels
- `android-chrome-192x192.png`
- `android-chrome-512x512.png`
- `site.webmanifest`

The icon links are included in both the storefront and admin layouts so that the same branding is used across public and administrative pages.

### 🏠 Homepage Redesign
`app/controllers/site/home.php` now loads 6 featured products and 6 latest products. `views/site/home.php` follows this structure:

1. A compact introduction containing the page's only `<h1>`.
2. The category grid near the top of the page.
3. A 6-item featured-product carousel.
4. A 6-item latest-product carousel.
5. A horizontal category strip near the bottom for quick navigation.

The carousels in `assets/css/style.css` use native CSS `scroll-snap` and do not depend on an external carousel library. Card sizing is responsive:

- Desktop: 3 cards
- Tablet: 2 cards
- Mobile: one full card plus part of the next card as a visual swipe cue

The previous/next controls in `assets/js/main.js` scroll by the current track width and detect the document direction so the controls work correctly in both RTL and LTR layouts.

Categories are intentionally presented in two forms: a full grid near the top and a lighter horizontal strip near the bottom. The second presentation keeps category navigation available after a user has browsed through the product sections.

Both category sections use a generic inline SVG icon instead of placeholder images because the current admin interface does not provide an upload flow for `categories.image`.

### 🗄️ Database Changes
This release contains no new migration and does not modify the database schema.

## [1.3.0] — Site Branding, Header/Footer Overhaul, Storefront Search, Tag SEO, and Two Admin-Panel Bug Fixes

### Summary

This release covers six separate requests: showing which product variant was ordered directly in the admin order list, letting the store logo be uploaded from the admin panel, a full header/footer rebuild (search, announcement bar, footer content, social links, an eNamad slot), a small but real SEO fix for how product tags are linked, and a CSS bug that misaligned the actions column on tall admin-table rows. As part of this pass, every Persian-language *code comment* across the project (not user-facing text) was translated to English, and `app/controllers/admin/settings.php` was refactored to a more robust per-section save pattern.

### 🐛 Bug Fix: Ordered Variant Not Visible in the Admin Order List

**Problem:** `order_items.variant_label` was already being saved correctly at checkout, and `order_detail.php` already displayed it — but the order **list** (`admin/orders.php`) showed only order-level fields (code, customer, total, status). Seeing which size/color variant was purchased required opening every single order individually.

**Fix:** `app/controllers/admin/orders.php` now runs one grouped query (`order_items WHERE order_id IN (...)`) to load every visible order's items up front, and `views/admin/orders.php` renders them in a new "Order items" column as `Product name (variant) × qty`.

### ✨ New Feature: Site Logo Upload

- New "Store logo" section in `admin/settings.php`: upload (JPG/PNG/WEBP/SVG, 2 MB limit, real MIME-type check via `finfo`) and remove.
- Stored under `uploads/branding/`, derived from the existing `UPLOAD_DIR`/`UPLOAD_URL` constants (`BRANDING_UPLOAD_DIR`/`BRANDING_UPLOAD_URL`, computed once in `app/bootstrap.php`) — no new constant needs to be added to an already-deployed `config.php`.
- `siteLogoUrl()` (`app/core/functions.php`) returns the logo's URL or `null`; both `views/layout/header.php` and `views/layout/footer.php` fall back to the text `SITE_NAME` when no logo is set.

### ✨ New Feature: Header — Search Bar and Announcement Bar

- A single collapsible search panel, toggled by a magnifier icon, rendered identically on mobile and desktop (deliberately avoiding two separate responsive layouts, which is what causes flexbox `order` bugs at breakpoints). New route `search` → `app/controllers/site/search.php` / `views/site/search.php`, a paginated `LIKE` match against product name and description.
- A site-wide announcement bar directly under the header (`announcement_bar_enabled/text/link` settings), shown on every page, optionally wrapped in a link (e.g. to a Telegram channel).

### ✨ New Feature: Footer Rebuild

- A teaser line above the footer linking to `/about` (`footer_about_teaser_text`).
- Brand column: logo, tagline, phone number (`store_phone`, also used on `/contact`), shipping-note text (`footer_shipping_badge_text`).
- Social links for Instagram, Telegram, Bale, and Torob — each independently toggleable with its own URL from the admin panel; only enabled links with a non-empty URL are rendered, using generic (non-trademarked) line icons.
- An eNamad slot: admins paste their own badge code from enamad.ir after certification (`enamad_embed_code`); nothing is rendered, and no placeholder badge is invented, until a real code is provided.
- `/about` now contains the store's real founding story instead of placeholder copy.

### 🔍 Product Tag SEO Review

- Tag slugs already used hyphens (`slugify()`), which is the correct, Google-recommended word separator — unlike underscores, which most search engines don't split into separate keywords. No change was needed there.
- The visible `#` in front of each tag on the product page was literal anchor text (`#<?= e($tag['name']) ?>`); it's now a CSS `::before` decoration (`.tag-pill` in `assets/css/style.css`), so the actual link text search engines and screen readers see is just the tag name. Added `rel="tag"`.
- `app/controllers/site/tag.php` now sets a tag-specific `$metaDescription` instead of falling back to the generic site-wide one, and `views/site/tag.php` uses `<h1>` instead of `<h2>` for its main heading.

### 🐛 Bug Fix: Admin Table Actions Column Misaligned on Tall Rows

**Problem:** In `admin/products.php`, a row whose variant-stock summary wrapped onto several lines grew noticeably taller than other rows, but the "Actions" buttons in that row stayed pinned to the top instead of staying vertically centered like every other column.

**Root cause:** `<td class="admin-actions">` applied `display: flex` directly to the table cell. This overrides the cell's internal `display: table-cell` type, which is what `vertical-align: middle` requires to have any effect — so that one cell silently stopped honoring the row's vertical centering.

**Fix:** the actions `<td>` stays a plain cell; `class="admin-actions"` moved to an inner `<div>` in `products.php`, `categories.php`, and `users.php`. Also added an explicit `vertical-align: middle` on `.admin-table th, .admin-table td` in `assets/css/admin.css` so the same class of bug can't reappear from a browser default difference.

### 🔧 Admin Settings Architecture

`app/controllers/admin/settings.php` was refactored so each settings section is its own `<form>` posting a hidden `section` field, and the controller only writes that section's keys. This replaces the previous pattern where every form had to re-submit every other section's values as hidden inputs to avoid resetting them — a pattern that gets more fragile (and easier to silently break) every time a new setting is added, which this release does a lot of.

### 🗄️ Database Changes (`database/migrations/006_v1.3.0_header_footer_branding_social.sql`)

No new tables or columns — `settings` is already a generic key/value store. The migration only seeds the new keys (logo, announcement bar, footer content, social links, eNamad) with safe, disabled/empty defaults using `INSERT IGNORE`, so it's safe to re-run and never overwrites values an admin has already configured.

### 🧹 Housekeeping: Code Comments Translated to English

Every Persian-language comment in PHP/JS/CSS/SQL source files (controllers, core, services, views, `schema.sql`, migrations) was translated to English, so the codebase reads consistently for anyone reviewing it on GitHub. This did **not** touch any user-facing Persian text (page content, labels, admin UI strings) or the Persian documents in `docs/`, per the project's stated convention.

---

## [1.2.2] — SMS/Email Diagnostics, Notification Debug Logging, and Multi-Image Product Gallery

### Summary

This release addresses a real-world deployment issue where neither SMS OTP messages nor email verification messages were being delivered after `config.php` was populated with real credentials.

The release introduces a set of **server-side diagnostics tools** for checking the Faraz SMS and SMTP integrations. Every notification delivery attempt—successful, failed, or intentionally log-only—is persisted with detailed diagnostic information.

### 🔍 Initial `config.php` Finding

```text
FARAZ_LINE_NUMBER = '<configured value>'
```

This value is unusual. SMS sender line numbers are typically 9–11 digits and are normally configured without the `+98` country prefix. The current value is a 17-character string and may therefore have been rejected by the Faraz API.

The new Diagnostics page automatically detects and warns about this kind of suspicious value.

### ✨ New Feature: Live Diagnostics (`/admin/diagnostics.php`, `super_admin` only)

Three real diagnostics run directly on the production server rather than simulating requests:

1. **Faraz API Key Test**  
   Reads the account balance without sending an SMS or generating a charge. If the API key is invalid or the connection fails, the exact API error is displayed.

2. **Pattern Details Check**  
   Retrieves the actual variable name(s) of the registered Faraz SMS pattern so they can be compared directly against `FARAZ_OTP_PATTERN_VAR` in `config.php`. The names must match exactly or the request will fail.

   The previous default value `'code'` was only an assumption based on the information available during the original implementation. This test provides the authoritative value from the Faraz account.

3. **SMTP Connection Test**  
   Tests the SMTP connection, STARTTLS negotiation, and authentication without sending an actual email. The raw SMTP conversation is exposed so failures such as network timeouts or authentication rejection can be diagnosed precisely.

The page also displays the current configuration values with sensitive values partially masked (for example, `Eb0c••••••••••1Eg4bJ`) so configuration mistakes can be spotted quickly.

### ✨ New Feature: Complete Notification Attempt Logging (`/admin/notifications_log.php`)

From this release onward, every real OTP delivery attempt—successful, failed, or log-only because the service is not configured—is stored with detailed diagnostic information.

- `sms_log` and the new `email_log` table now contain a `debug_info` column.
- `debug_info` stores request URL, request payload, HTTP response code, cURL errors (when present), and the raw server response for SMS; or the complete line-by-line SMTP conversation for email.
- A new admin page contains separate SMS and Email tabs and displays the latest 100 records with expandable `<details>` sections.
- This allows store administrators to determine why a specific SMS or email was not delivered without requiring SSH access or direct access to server logs.

### 🧪 Important Limitation of This Release

The diagnostics are designed to run in the deployment environment so the actual connectivity and service responses can be inspected.

Therefore:

- All three diagnostics were tested to ensure they execute correctly, handle actual network failures without crashing, and expose the resulting errors.
- With the same network restriction in the development environment, the diagnostics produced genuine connection failures such as unreachable hosts and connection timeouts, confirming that their error-reporting paths work as intended.
- Final confirmation that SMS and email are actually delivered can only be performed on the production server.

After deployment, all three diagnostics should be executed on the real server. If delivery still fails, the Technical Details output from the diagnostics page provides the concrete API/SMTP evidence required for further troubleshooting.

### ✨ New Feature: Multi-Image Product Gallery (Unlimited Images)

- The `product_images` table has existed since version 1.0.0, and the storefront product gallery already read from it, but the admin panel previously had no management UI for the table. Only one primary product image could be uploaded.
- The product form now includes a `multiple` file input named `gallery_images[]`, allowing any number of images to be selected and uploaded in one submission.
- Every gallery file is validated securely using the real file type detected by `finfo`, rather than trusting the file extension or the client-provided Content-Type.
- Existing gallery images are displayed with a Delete checkbox. Images can be removed and new images can be added in the same submission.
- The primary cover image remains independent. It is still the image used by product cards, the homepage, and JSON-LD.
- **Tested:** uploading three images at once to a new product, displaying all three in the storefront gallery, deleting one image while adding another in the same submission, preserving `sort_order`, removing the deleted file from disk, and rejecting a malicious PHP file disguised with `image/jpeg`.

### 🗄️ Database Changes (`database/migrations/005_v1.2.2_debug_logging.sql`)

- `sms_log`: added nullable `debug_info` (`TEXT`)
- Added new `email_log` table with the same general structure as `sms_log`, plus `debug_info`
- No schema change was required for `product_images`; only application logic and UI were added
- **Tested:** the migration was run against a simulated copy of the v1.2.1 database containing a real `sms_log` record. Existing data remained intact and the new column/table were added successfully.

### 🐛 Two Additional Delivery Bugs Found and Fixed After Deployment

After this release was deployed and the new diagnostics were used, two additional root causes were identified and fixed directly in the code. They are recorded here for completeness.

#### 1. Incorrect `number_format` Value in Faraz SMS Requests

In `app/services/FarazSmsService.php`, the API request incorrectly used:

```php
// Before (incorrect):
'number_format' => 'en',
// After (correct):
'number_format' => 'english',
```

The Faraz API expects `'english'`, not the short code `'en'`. The invalid parameter caused the API to reject the request, which explains why SMS delivery continued to fail even after `FARAZ_LINE_NUMBER` was corrected.

#### 2. Email Verification Code Was Only Sent After Clicking “Resend”

In `app/controllers/site/verify_email.php`, the original behavior called `VerificationService::sendCode()` only when the user explicitly clicked the “Resend” button.

Therefore, the first time a user opened `/verify-email`—either from registration or from the account profile—no verification code was sent.

The fix adds an `else` branch to the existing “already verified?” check so that the first visit immediately sends a code:

```php
if (!empty($customer['email_verified_at'])) {
    setFlash('info', 'Your email is already verified.');
    redirect('/account');
} else {
    $result = VerificationService::sendCode($customer['id'], 'email', $customer['email']);
    if ($result['ok']) {
        $info = 'A new verification code has been emailed.';
    } else {
        $error = $result['error'] ?: 'Email delivery is currently unavailable. Contact support when manual intervention is required.';
    }
}
```

This changes the behavior from “only clicking Resend sends a code” to “both the first page visit and Resend can send a code.”

The existing 60-second resend limit in `VerificationService::sendCode()` prevents duplicate/expensive sends. If the user refreshes or revisits the page within 60 seconds, the service returns the wait message instead of sending another message.

---

## [1.2.1] — Product Tags, Full Customer Profile, Phone/Email Verification, SEO, and Effective Stock Fix

### 🐛 Reported Bug Fix: Incorrect “Out of Stock” Label for Products with Variants

**Problem:** On storefront product listings such as the homepage and category pages, every product with variants was incorrectly marked “Out of Stock”, even when one or more variants still had inventory.

**Root cause:** Since version 1.2.0, products with variants intentionally store `products.stock = 0`, because actual inventory is derived from the sum of variant stock. However, `product_card.php` still read the `stock` column directly when deciding whether to display the “Out of Stock” label.

**Fix:** A new helper, `effectiveStockSqlFragment()`, was added to `app/core/functions.php`. It returns a SQL fragment that uses the sum of variant stock when a product has variants, and otherwise uses the product's own `stock` value.

The calculated `effective_stock` column was added to the relevant product list queries in:

- `home.php` (Featured and Newest queries)
- `category.php`
- the new `tag.php`

`product_card.php` now checks `effective_stock` first and falls back to `stock` when that calculated field is unavailable.

**Tested:** a product with `stock = 0` and a variant with stock `12` no longer shows “Out of Stock”; a product with variants whose total stock is zero correctly shows the label.

### 🐛 Important Bug Fix: PHP/MySQL Timezone Mismatch

**Problem:** MySQL on the hosting environment typically runs in UTC, while PHP in this project was configured for `Asia/Tehran` (`UTC+3:30`). Values written using MySQL `CURRENT_TIMESTAMP`/`NOW()` therefore differed from values interpreted with PHP `time()`/`strtotime()` by approximately 3.5 hours.

**Affected areas:**

- **Cart price guarantee (introduced in 1.2.0):** the cart age calculation relied on comparing timestamps generated by MySQL and PHP. Because the threshold is measured in days, the 3.5-hour difference rarely changed the final result, but the comparison was technically incorrect.
- **Verification-code resend throttling (introduced in 1.2.1):** the threshold is only 60 seconds, so the mismatch effectively disabled the protection. PHP could see the previous code as several hours old and therefore allow another send immediately.

**Fix:** In `app/core/db.php`, immediately after creating the PDO connection, the database session timezone is set with:

```sql
SET time_zone = '+03:30'
```

The offset is calculated from the project's `date_default_timezone_get()` rather than hardcoded.

**Tested:** MySQL `NOW()` and PHP `date('Y-m-d H:i:s')` now match exactly. Immediate resend attempts are rejected as expected, and the price guarantee remains active immediately after adding a cart item.

### ✨ New Feature: Product Tags

- Added `tags` and `product_tags` tables.
- The product create/edit form displays all existing tags as checkboxes, regardless of whether they are currently assigned to an active or inactive product.
- Any admin can modify tags for any product at any time; there is no ownership model.
- A text field accepts new tags separated by commas. Missing tags are created immediately.
- New store setting `show_product_tags` (default: enabled) controls whether product tags are displayed on product pages.
- Added `/tag/{slug}` to list products assigned to a tag. This serves both product discovery and SEO by giving every tag its own indexable URL.
- Product pages display tags as compact `#tag` links pointing to the corresponding tag page.

### ✨ New Feature: Full Customer Profile

- `/account` was redesigned with an editable personal-information card for name and email.
- Mobile and email verification states are shown with “Verified ✓” / “Not verified ⚠” indicators.
- The page includes the current cart summary and order history.
- Added `/account/order/{order-code}` for detailed access to an individual order, including items, totals, and shipping address.
- Server-side ownership checks ensure the requested order belongs to the currently authenticated customer; otherwise the response is 404 rather than exposing another user's order.
- Changing the email address automatically resets email verification status.

### ✨ New Feature: SMS-Based Phone Verification via Faraz SMS

- **Important registration-flow change:** registration no longer immediately creates a fully authenticated session.
- A customer account is created, a six-digit SMS code is sent, and the customer must enter the code on `/verify-phone` before a full authenticated session is established.
- If an unverified customer logs in with the correct password, they are redirected to `/verify-phone` and a fresh verification code is requested.
- Customers created before v1.2.1 are automatically marked as phone-verified during migration so existing accounts are not locked out by a requirement that did not exist when they were created.
- Added `app/services/FarazSmsService.php`, which connects to the pattern-based Faraz SMS / Iran Payamak API.
- `FARAZ_OTP_PATTERN_CODE` and `FARAZ_OTP_PATTERN_VAR` are configurable because pattern-based messages require a pre-approved pattern.
- **Manual configuration required:** `FARAZ_LINE_NUMBER` must be filled from the Faraz account's Lines section. The previously supplied project configuration did not contain a confirmed sender-line value.
- Fail-safe behavior: until `FARAZ_SMS_ENABLED` is `true` and the API key, pattern, and line are configured, no real SMS is sent; the attempt is logged to `sms_log` instead and the site does not crash.

### ✨ New Feature: Email Verification via Authenticated SMTP

- Added `app/services/EmailService.php` using the official PHPMailer package (`v6.9.1`), stored directly under `app/vendor/PHPMailer/` without Composer.
- PHPMailer was selected instead of PHP's built-in `mail()` because shared-hosting email sent via `mail()` is often more likely to be rejected as spam by providers such as Gmail and Outlook. Authenticated SMTP provides a more reliable delivery path on shared hosting.
- Added `/verify-email`. After phone verification (when an email address was provided during registration), or later from the account profile, the customer can verify the email using a six-digit code.
- Email verification is optional when an email address is present and is not required for login; phone verification remains mandatory.
- Fail-safe behavior: until `SMTP_ENABLED` is `true` and real SMTP credentials are configured, no real email is sent and the service returns a readable error without crashing the site.

### 🔒 Shared Verification-Code Service (`app/services/VerificationService.php`)

Verification logic for both phone and email is centralized in one service:

- Each code remains valid for 10 minutes.
- A maximum of 5 incorrect attempts is allowed; after that, a new code must be requested.
- A minimum 60-second interval is required between code-sending requests.
- Verification codes are stored as `sha256` hashes rather than plaintext.
- The behavior was tested directly through service calls: invalid codes are rejected, valid codes work once, reusing the same code is rejected, and resend throttling is enforced.

### ✨ New Feature: SEO and Google Indexing Control

- Added `seo_indexing_enabled` in the admin settings panel. It is **disabled by default** until the store's real products and final content are ready.
- `robots.txt` and `sitemap.xml` are now dynamic through `robots.php` and `sitemap.php` with rewrite rules in `.htaccess`.
- When indexing is disabled, `robots.txt` returns `Disallow: /`.
- When enabled, public pages such as the homepage, about/contact pages, categories, and active products are allowed.
- Private or low-value areas such as admin pages, AJAX endpoints, cart, checkout, and customer account pages remain disallowed.
- The sitemap lists all active categories and products with `lastmod`.
- Every page now includes a `robots` meta tag, a canonical URL, and basic Open Graph metadata (`og:title`, `og:description`, and `og:image` when available).
- Product pages additionally provide a dedicated description, Open Graph image support, and a complete `schema.org/Product` JSON-LD block including product name, image, SKU, price in rials, and availability derived from effective variant-aware stock logic.

### 🗄️ Database Changes (`database/migrations/004_v1.2.1_tags_verification_seo.sql`)

- New tables: `tags`, `product_tags`, `verification_codes`
- `customers`: new `email`, `phone_verified_at`, and `email_verified_at` columns
- Existing customers receive `phone_verified_at = created_at` automatically
- New settings: `show_product_tags` (default `1`) and `seo_indexing_enabled` (default `0`)
- **Tested:** migration was executed against a simulated v1.2.0 database containing an existing customer and order. No data was lost and the existing customer was automatically marked as verified.

### ⚙️ New `config.php` Settings

This release adds several configuration constants, all disabled or empty by default for fail-safe behavior:

`FARAZ_SMS_ENABLED`, `FARAZ_API_KEY`, `FARAZ_OTP_PATTERN_CODE`, `FARAZ_OTP_PATTERN_VAR`, `FARAZ_LINE_NUMBER`, `SMTP_ENABLED`, `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_FROM_EMAIL`, `SMTP_FROM_NAME`.

Detailed configuration instructions are documented in `README-DEPLOY.md`.

---

## [1.2.0] — Customer Accounts, Persistent Cart, Price Guarantee, Subcategories, and Product Management Improvements

### Summary

This release evolves the store from a **Guest Checkout-only** model into a store with real customer accounts. It also adds several administration capabilities including subcategories, smart variant management, automatic SKU generation, order deletion, and quick access to featured products.

All 1.2.0 changes build on the v1.1.0 `app/views` separation without breaking that architecture.

### ✨ New Feature: Customer Accounts by Mobile Number

- Added `customers` table with unique phone number, `password_hash`, and `full_name`.
- Added `/signup`, `/login`, `/logout`, and `/account`.
- **Important design decision:** customer authentication uses mobile number + password rather than SMS OTP. At the time, `SmsService` was log-only by default until a real SMS provider was connected, so OTP-based login would not have been usable out of the box.
- Security includes `password_hash`/`password_verify`, `session_regenerate_id()` after registration/login to prevent session fixation, artificial delay for failed login attempts, and protection against open redirects in the `next` parameter.
- Logged-in users have checkout fields prefilled from their account for convenience, while full server-side validation still applies.
- Added nullable `customer_id` to `orders`. Guest orders remain fully supported.

### ✨ New Feature: Persistent Cart for Logged-In Customers

- Added `cart_items` with a composite unique key on `customer_id`, `product_id`, and `variant_id`.
- `variant_id = 0` represents “no variant” because MySQL allows multiple `NULL` values inside a unique key.
- `app/core/cart.php` was rewritten to maintain two separate storage strategies:
  - **Guest:** `$_SESSION['cart']`
  - **Logged-in customer:** persistent database storage
- The public cart API (`cartAdd`, `cartUpdateQty`, `cartRemove`, `cartClear`, `cartCount`, `cartDetails`) remains identical for both user types.
- `mergeGuestCartIntoCustomerCart()` merges a guest cart into the customer's persistent cart after login/registration instead of replacing the existing cart.
- **Tested:** an item added in one browser session remained visible after logging in from a completely new session, demonstrating that the cart is associated with the account rather than the browser.

### ✨ New Feature: Cart Price Guarantee

This was the most complex logic introduced in this release and was therefore tested in detail.

- Each `cart_items` row stores `locked_unit_price`, representing the effective product/variant price at the time the item was added.
- The cart guarantee start time is `MIN(added_at)` across the user's current cart items.
- Because the start time is calculated dynamically from current rows, emptying the cart and adding a new item later automatically starts a new guarantee period.
- Admin settings:
  - `price_guarantee_enabled`
  - `price_guarantee_days` (default: 7)
- In `cartDetailsForCustomer()`, the locked price is used while the cart age is within the guarantee period. After expiration, the live product/variant price is used permanently for that cart cycle until the cart becomes empty and a new cycle begins.
- The cart UI shows either the guarantee expiration date or a message that live prices are being used.
- Items using a locked price receive a “Guaranteed Price” label.
- **Tested:** price increases within the seven-day period did not change the cart total; moving `added_at` beyond the guarantee period immediately switched the cart to the live price; disabling the feature caused even fresh carts to use live pricing.

### ✨ New Feature: Order Deletion by Super Admin

- Added full-order deletion to both the order list and order-detail screens.
- The controls are visible only to `super_admin`.
- Server-side enforcement uses `requireSuperAdmin()`; UI hiding is not the security boundary.
- `order_items` are removed through `ON DELETE CASCADE`.

### ✨ New Feature: Product Subcategories

- Added nullable self-referencing `parent_id` to `categories`.
- The admin UI allows a category to select a parent, but only top-level categories can be selected as parents, intentionally keeping nesting to one level.
- Parent category pages display child categories as clickable chips and include products assigned to those direct child categories through `getCategoryAndChildIds()`.
- The main navigation shows only top-level categories to avoid visual clutter.

### ✨ New Feature: Automatic Unique SKU Generation

- Added `generateUniqueSku()` in `app/core/functions.php`.
- Generated SKUs use the `SOCK-XXXXXX` format.
- The generator checks database uniqueness before accepting a value, with up to 10 attempts and a final `uniqid` fallback.
- Empty SKU values are generated automatically; manually supplied SKUs are validated for uniqueness.
- `products.sku` now has a real database `UNIQUE KEY`.
- Migration backfills blank/NULL SKUs with values such as `SOCK-010001`, while preserving existing manual SKUs. This behavior was tested with a sample custom SKU `MYOWN-001`.

### ✨ New Feature: `has_variants` Product Option

- Added a `has_variants` checkbox to the product form.
- When enabled, JavaScript disables and visually de-emphasizes the global stock field and activates the variant management section.
- The server does not trust JavaScript. If `has_variants` is not present in the request, variant rows are ignored. If it is present, `products.stock` is always stored as `0`, because true inventory is the sum of variant stock.

### ✨ New Feature: Variant Inventory in Admin Product Lists

- Products with variants now display per-variant inventory in `admin/products.php`.
- Example: `39-42 Black: 15` and `43-46 Black: 8`.
- A single SQL query with a `GROUP_CONCAT` subquery avoids an N+1 query pattern.

### ✨ New Feature: Quick Access to Featured Products

- Added a sidebar shortcut to `products.php?featured=1`.
- The existing product-list controller/view are reused; only an additional `WHERE is_featured = 1` condition is applied.

### 🗄️ Database Changes (`database/migrations/003_v1.2.0_customer_accounts_cart_price_guarantee.sql`)

- New `customers` table
- New `cart_items` table
- New `settings` table with `price_guarantee_enabled=1` and `price_guarantee_days=7`
- `categories.parent_id` added as a nullable self-referencing foreign key
- Existing blank/NULL product SKUs backfilled and a unique constraint added to `products.sku`
- Nullable `orders.customer_id` foreign key added
- **Tested:** migration was executed against a simulated v1.1.0 database containing real-looking order, product, and admin data; no data was lost and existing manual SKUs were preserved.

### 🎨 UI Changes

- Added login/account icon next to the cart icon in the site header.
- Main navigation now shows only top-level categories.
- Added price-guarantee status banner and “Guaranteed Price” labels to the cart page.

### 📌 Backward Compatibility

- No breaking changes were introduced to existing storefront or admin routes.
- Guest users continue to work exactly as before.
- After applying the migration, the old `install.php` remains harmless if it is still present; it is only intended for first-time setup and stays locked when an admin already exists.

---

## [1.1.0] — Payment Gateway, SMS, Coupons, Multi-Admin Support, and Codebase Refactor

### Summary

This release contains three major categories of changes:

1. A critical production bug fix.
2. A complete project directory refactor to separate application logic from presentation.

### 🐛 Bug Fixes

#### [Critical] Forbidden Error on `/cart`

- The physical `cart/` directory contained AJAX endpoints (`add.php`, `update.php`, `remove.php`) and conflicted with the `/cart` storefront route.
- Apache stopped rewriting because the requested path matched a real directory.
- Since directory listing was disabled with `Options -Indexes`, Apache returned HTTP 403 instead of routing to the storefront cart page.
- The directory was renamed to `ajax/`, and the files became `cart_add.php`, `cart_update.php`, and `cart_remove.php`.
- All frontend references were updated.
- The architectural rule was documented: **no physical directory should share the same name as a route handled by `index.php`.**

#### [Minor] Incorrect Database Table Count in Previous Documentation

The previous release documentation incorrectly stated that the v1.0.0 schema contained 9 tables. The correct count was 8:

`admins, categories, coupons, orders, order_items, product_images, product_variants, products`

After adding `sms_log`, v1.1.0 contains 9 tables.

### ♻️ Refactor — Logic / Presentation Separation

- Removed `includes/` and redistributed its content:
  - `db.php`, `functions.php`, `csrf.php`, `auth.php`, `cart.php`, `bootstrap.php` → `app/core/` and `app/bootstrap.php`
  - site `header.php`, `footer.php` → `views/layout/`
  - `admin_header.php`, `admin_footer.php` → `views/admin/layout/`
- Removed `pages/`.
- Storefront pages were split into:
  - controller: `app/controllers/site/{page}.php`
  - view: `views/site/{page}.php`
- Admin pages follow the same controller/view separation.
- `admin/` now contains only thin entry points that load bootstrap, enforce authentication, and require the appropriate controller.
- Added `renderView($view, $data)` to `app/bootstrap.php`.
- Both `app/` and `views/` are protected by `.htaccess` using `Require all denied`.
- The goal is to allow presentation changes without modifying business logic and vice versa.


- Added `app/services/ZarinpalService.php` based on Zarinpal REST API v4.
- Online payment via Zarinpal was added alongside Cash on Delivery during checkout.
- Added `payment/zarinpal_callback.php` for gateway callbacks.
- Added `payment/retry.php` for retrying failed payments without losing the existing order.
- Added `/order/failed/{code}`.
- Added `payment_status`, `payment_authority`, and `payment_ref_id` to `orders`.

### ✨ New Feature: SMS Service

- Added `app/services/SmsService.php`, initially prepared for Kavenegar.
- Fail-safe behavior: without an API key or when `SMS_ENABLED=false`, no real message is sent and the attempt is only logged to `sms_log`.
- Automatic SMS notifications are triggered for successful online payment confirmation and admin-driven order-status changes.

### ✨ New Feature: Checkout Coupon Support

- Added `app/services/CouponService.php`.
- Added coupon application UI to `views/site/cart.php`.
- Applied coupons are stored in `$_SESSION['coupon']` and revalidated during checkout.
- Added `ajax/coupon_apply.php` and `ajax/coupon_remove.php`.
- Added `coupon_id` to `orders`.

### ✨ New Feature: Multi-Admin Roles

- Added `role` (`super_admin` | `admin`) and `is_active` to `admins`.
- Added `admin/users.php` for super-admin user management.
- Supports:
  - creating admins
  - assigning roles
  - changing passwords
  - activating/deactivating accounts
  - deleting accounts with safeguards
- The last remaining `super_admin` cannot be removed or deactivated.
- An admin cannot deactivate or delete their own account.
- Added `requireSuperAdmin()` and `isSuperAdmin()`.
- The first admin created through `install.php` is automatically promoted to `super_admin`.

### 🗄️ Database Changes

Added `database/migrations/002_v1.1.0_payment_sms_coupons_admins.sql` for upgrading existing v1.0.0 installations.

- Adds only new columns/tables; no existing data is deleted or overwritten.
- Existing confirmed orders are marked as `payment_status = paid` because online-payment status did not exist in v1.0.0.
- The first existing admin is promoted to `super_admin`.
- `database/schema.sql` was also updated so clean installations include the complete v1.1.0 schema directly.

### ⚙️ Configuration Changes (`config/config.php`)

Six new settings were introduced:

```php
define('ZARINPAL_MERCHANT_ID', '<placeholder>');
define('SMS_ENABLED', false);
define('SMS_PROVIDER_API_KEY', '');
define('SMS_SENDER_LINE', '');
```

### 📄 Documentation

- Added `docs/ARCHITECTURE.md`.
- Added `docs/CHANGELOG.md`.
- Added `APP_VERSION` in `app/bootstrap.php`.

### ✅ Testing

The release was tested on PHP 8.3 + MariaDB:

- Full PHP lint with zero errors
- Migration execution on a simulated v1.0.0 database without data loss
- `/cart` fix verified from HTTP 403 to HTTP 200 using Apache/mod_rewrite behavior
- Coupon apply/remove and total calculation
- Complete Cash on Delivery checkout
- First-admin (`super_admin`) creation and second-admin (`admin`) creation
- Admin-role access restriction
- Failed Zarinpal callback simulation with correct retry UI

### ⚠️ Deployment Notes

1. Back up the existing database using DirectAdmin or phpMyAdmin.
2. Run `database/migrations/002_v1.1.0_payment_sms_coupons_admins.sql` exactly once against the production database.
3. Replace the old project structure with the new `app/`, `views/`, `ajax/`, and `payment/` directories; remove the old `includes/`, `pages/`, and `cart/` directories.
4. Re-enter the real production database credentials into `config/config.php`; the repository version contains placeholders.
5. After deployment, place at least one test order using both online payment and Cash on Delivery.

---

## [1.0.0] — Initial Release (MVP)

The first usable version of the store, including:

- Customer storefront: homepage, categories, product details with size/color variants, session-based cart, Cash on Delivery checkout, order-success page, about/contact/terms pages.
- Admin panel: dashboard, product CRUD with image uploads and variant management, category CRUD, order management, and order-status updates.
- Infrastructure: framework-free PHP, MySQL, session-based admin authentication, CSRF/XSS/SQL-injection protections, and first-time setup through `install.php`.
- Direct deployment to shared DirectAdmin hosting without Composer, npm, or SSH.