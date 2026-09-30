# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.17.5 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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
  - Streamlined `gift_items.php` controller to 69 lines (strictly under Rule 7 80-line ceiling), handling AJAX toggles, stock adjustments, and studio saves.
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
  - Guarantees zero duplicate global function declarations in future releases, catching collisions invisible to isolated `php -l` file lints.

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
