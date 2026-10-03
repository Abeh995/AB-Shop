# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.22.1 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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
