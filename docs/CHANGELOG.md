# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.17.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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

