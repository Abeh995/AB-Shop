# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.13.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

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
