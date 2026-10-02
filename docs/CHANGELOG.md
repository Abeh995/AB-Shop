# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.21.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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
