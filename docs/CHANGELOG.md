# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.17.2 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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

