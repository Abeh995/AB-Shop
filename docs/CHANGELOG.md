# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.12.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---


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
