# Store Architecture Documentation

**Current version: 1.4.0**

This document is the canonical technical reference for the store's architecture and business logic. Whenever a technical change is made to the project, both this document and `CHANGELOG.md` must be updated.

---

## 1. Architectural Philosophy

The project is intentionally built **without a framework** (Laravel, Symfony, etc.) and **without Composer/npm on the server**, so it can be deployed to inexpensive shared hosting with severe operational restrictions such as limited SSH access and no guaranteed CLI tooling.

The architecture follows a **Lightweight Manual MVC** pattern.

There is no framework routing layer, ORM, complex router, or dependency-injection container. The application consists of procedural PHP plus a small number of simple service classes.

---

## 2. Directory Structure and Responsibilities

```text
/
├── index.php                  Public storefront front controller (routes requests to controllers)
├── install.php                First-time installer (creates the initial admin without SSH)
├── .htaccess                  URL rewriting, forced HTTPS, security headers
│
├── robots.php                 Dynamic robots.txt (based on seo_indexing_enabled) — since 1.2.1
├── sitemap.php                Dynamic sitemap.xml (categories + active products) — since 1.2.1
├── favicon.ico, favicon-*.png, apple-touch-icon.png,
│   android-chrome-*.png, site.webmanifest
│                               Browser and mobile home-screen icons — since 1.4.0
│
├── config/                    Sensitive configuration (protected by .htaccess Deny)
│   └── config.php             DB, Zarinpal, SMS, Faraz SMS, SMTP, Debug mode
│
├── app/                       All application/backend logic — protected by .htaccess Deny
│   ├── bootstrap.php          Shared bootstrap for all entry points; also derives BRANDING_UPLOAD_DIR/URL — since 1.3.0
│   ├── vendor/PHPMailer/      Official PHPMailer library (without Composer) — since 1.2.1
│   ├── core/                  Shared application core
│   │   ├── db.php             PDO connection (Singleton) + PHP/MySQL timezone synchronization — since 1.2.1
│   │   ├── functions.php      Shared helpers (price formatting, slugs, automatic SKU, category hierarchy,
│   │   │                       tags, effective stock, site logo URL, etc.)
│   │   ├── csrf.php           CSRF token generation/validation
│   │   ├── settings.php       Key-value store settings — since 1.2.0
│   │   ├── auth.php           Admin authentication + roles (super_admin/admin)
│   │   ├── customer_auth.php  Customer authentication by mobile number + password + mandatory phone verification
│   │   │                       (since 1.2.0, updated in 1.2.1)
│   │   └── cart.php           Cart logic (guest=Session, logged-in=database with price guarantee) — rewritten in 1.2.0
│   ├── services/              Reusable independent services
│   │   ├── ZarinpalService.php      Payment gateway integration
│   │   ├── SmsService.php           Standard SMS notifications / logging
│   │   ├── FarazSmsService.php      Pattern-based OTP SMS through Faraz SMS — since 1.2.1
│   │   ├── EmailService.php          SMTP email delivery with authenticated PHPMailer — since 1.2.1
│   │   ├── VerificationService.php  Phone/email verification-code lifecycle — since 1.2.1
│   │   └── CouponService.php         Coupon validation and discount calculation
│   └── controllers/            Controllers — data fetching/processing only, zero HTML
│       ├── site/               Storefront controllers (home, product, cart, checkout, tags, search, authentication, etc.)
│       └── admin/               Admin-panel controllers
│
├── views/                      All HTML/templates (Presentation Layer) — protected by .htaccess Deny
│   ├── layout/                 Shared storefront header/footer (search bar, announcement bar, logo, social
│   │                            links, SEO robots/canonical/Open Graph tags) — extended in 1.3.0

│   ├── site/                   Storefront views + partials (homepage/carousel redesign in 1.4.0)
│   └── admin/                  Admin-panel views + admin-specific layouts
│
├── admin/                      Public admin entry points (thin files: bootstrap + auth guard + controller require)
│   ├── diagnostics.php         Live Faraz SMS/SMTP diagnostics (super_admin only) — since 1.2.2
│   └── notifications_log.php   Detailed SMS/email attempt log (super_admin only) — since 1.2.2
├── ajax/                       AJAX endpoints (cart operations, coupon application/removal, etc.)
├── payment/                    Payment gateway endpoints (callback, retry)
├── assets/                     Public CSS/JS/static images
├── uploads/products/           Uploaded product images (PHP execution disabled in this directory)
├── uploads/branding/           Uploaded site logo (same PHP-execution restriction, derived from UPLOAD_DIR) — since 1.3.0
├── database/
│   ├── schema.sql              Complete schema for fresh installations
│   └── migrations/             Upgrade scripts for existing databases
└── docs/                        Project documentation
```

### Why Separate `app/` and `views/`?

- `app/` is never directly requested by the browser. It is loaded only through server-side `require` calls. Business logic, database queries, and form processing are therefore isolated from HTML.
- `views/` is presentation-only. A view consumes variables prepared by its controller through `renderView()` and rarely performs its own queries.
- The intentional exception is the shared category navigation in `views/layout/header.php`, because it is common data required by every page rather than data specific to a single controller.
- A visual redesign can therefore be implemented in `views/...` without changing business logic, while a business rule such as discount calculation can be changed in `app/controllers/...` or `app/services/...`.

---

## 3. Request Lifecycle

### Storefront Requests (Example: `/product/mens-socks`)

1. `.htaccess` rewrites requests that do not match a physical file/directory to `index.php?route=product/mens-socks`. Physical paths such as `assets/`, `ajax/`, `admin/`, and `uploads/` bypass the main rewrite.
2. `index.php` loads the shared bootstrap (`app/bootstrap.php`), including sessions, configuration, core modules, and services.
3. `index.php` extracts the first route segment (`product`) and requires the corresponding controller: `app/controllers/site/product.php`.
4. The controller fetches the required data and calls `renderView('site/product', [...])`.
5. `renderView()` extracts the provided data and requires `views/site/product.php`.
6. The view loads `views/layout/header.php` and `views/layout/footer.php`.

### Admin Requests (Example: `/admin/products.php`)

The overall pattern is the same, except `admin/products.php` is a thin physical entry point served directly by Apache. It loads the bootstrap, calls `requireAdmin()`, and then requires `app/controllers/admin/products.php`.

### AJAX Requests (Example: Add to Cart)

Files under `ajax/` are standalone entry points. Each file loads the bootstrap, validates request data including CSRF, and returns JSON or a redirect.

---

## 4. Data Model (Database Tables)

| Table | Description |
|---|---|
| `admins` | Admin accounts. `role` (`super_admin`/`admin`) and `is_active` were added in 1.1.0. |
| `customers` | Customer accounts based on mobile number (since 1.2.0); `email`, `phone_verified_at`, and `email_verified_at` were added in 1.2.1. |
| `verification_codes` | Hashed phone/email verification codes with expiry and attempt limits — since 1.2.1. |
| `categories` | Product categories; `parent_id` enables one-level subcategories (since 1.2.0, self-referencing, nullable). |
| `products` | Products with `price`, `discount_price`, `stock` (global stock when variants are not used), unique auto-generated `sku`, etc. |
| `product_images` | Additional product gallery images. |
| `product_variants` | Product size/color variants with independent inventory. |
| `tags` / `product_tags` | Product tags and the many-to-many relationship between products and tags — since 1.2.1. |
| `coupons` | Discount codes supporting percentage or fixed-amount discounts. |
| `orders` | Orders with customer details, totals, `status`, `payment_status` (since 1.1.0), and nullable `customer_id` (since 1.2.0; guest orders are supported). |
| `order_items` | Order line items storing a snapshot of product name/price at purchase time. |
| `cart_items` | Persistent cart items for authenticated customers; each row stores a `locked_unit_price` — since 1.2.0. |
| `settings` | Store-wide key-value configuration, including price guarantee (1.2.0), product tags, SEO indexing (1.2.1), and site logo/announcement bar/footer content/social links/eNamad embed (1.3.0). |
| `sms_log` | SMS delivery/log records (since 1.1.0); `debug_info` was added in 1.2.2 for detailed API diagnostics. |
| `email_log` | Email delivery-attempt log with SMTP diagnostics stored in `debug_info` — since 1.2.2. |

### Why Does `order_items` Store a Snapshot?

If a product's name or price changes later, historical orders must remain unchanged. Therefore `product_name` and `unit_price` are stored directly in `order_items` instead of relying solely on the current `products` row.

---

## 5. Critical Security and Business Logic

### 5.1 Cart (`app/core/cart.php`) — Rewritten in 1.2.0

Since 1.2.0, the cart has two distinct storage paths while exposing the same function interface:

`cartAdd`, `cartUpdateQty`, `cartRemove`, `cartClear`, `cartCount`, `cartDetails`

Controllers and views therefore do not need to know whether the current customer is a guest or authenticated.

- **Guest:** stored in `$_SESSION['cart']` as `product_id`, `variant_id`, and `qty`. **Price and stock are never stored in Session**; they are always read live from the database. Guest carts have no price guarantee because there is no persistent identity to which a multi-day guarantee can be attached.
- **Authenticated customer:** stored in `cart_items`, persistent across sessions and devices. Each row has a `locked_unit_price`, enabling the **price guarantee** described below.
- If a product is deleted, disabled, or out of stock, it is ignored automatically in both cart modes because `cartDetails()` rechecks the current product state.
- When a guest logs in or registers, `mergeGuestCartIntoCustomerCart()` transfers guest-session items into the persistent cart using a merge operation rather than replacement, and then clears the guest session.

#### 5.1.1 Cart Price Guarantee

- The cart start time is `MIN(added_at)` across the user's **current** `cart_items`.
- Because this is calculated dynamically, fully emptying the cart and adding a new item later automatically starts a new guarantee period.
- Settings are stored through `app/core/settings.php` and the `settings` table and can be managed through `admin/settings.php`:
  - `price_guarantee_enabled`
  - `price_guarantee_days` (default: 7)
- `cartDetailsForCustomer()` compares `now - cartStartedAt` with `price_guarantee_days`.
- While the guarantee is active, each item's `locked_unit_price` is used.
- After expiry, the live product/variant price replaces the locked price permanently for that cart cycle until the cart becomes empty and a new cycle begins.
- **Important:** inventory is always checked live. The guarantee affects price only; it does not freeze stock or active/inactive state.

### 5.1.2 Customer Authentication (`app/core/customer_auth.php`) — Introduced in 1.2.0

Authentication uses mobile number + password rather than SMS OTP as the original default customer-login mechanism.

Security measures include:

- `password_hash()` / `password_verify()`
- `session_regenerate_id()` after login/registration
- artificial delay on failed login attempts
- protection against open redirects in the `next` parameter; only safe internal paths beginning with `/` and not with `//` are accepted

From 1.2.1 onward, newly registered customers must also complete phone verification before a full authenticated session is established.

### 5.2 Final Order Price Calculation (`app/controllers/site/checkout.php`)

During order creation:

1. The cart is rebuilt from database state rather than trusting quantities or totals submitted by the browser.
2. Stock is revalidated for every item.
3. The applied coupon is revalidated because it may have expired or reached its usage limit since it was applied to the cart.
4. `subtotal`, `discount_total`, and `total` are calculated server-side. No monetary value is trusted directly from the client.
5. Order insertion, stock decrement, and coupon usage increment are executed inside a single database transaction so the operation either fully succeeds or fully rolls back.

### 5.3 Payment Gateway (`app/services/ZarinpalService.php`)

- Uses Zarinpal REST API v4.
- Monetary values inside the project are stored in **toman**.
- The service multiplies the amount by 10 when sending it to Zarinpal because the gateway expects rial.
- `request()` creates the payment request and returns the gateway URL.
- `verify()` finalizes the payment after the user returns from the gateway.
- Network failures and invalid responses do not throw exceptions that crash the site. The service returns `['ok'=>false, 'error'=>...]` and the controller handles the failure.

**Complete online payment flow:**

```text
Checkout (POST)
    ↓
Create order in DB
status=pending, payment_status=unpaid
    ↓
ZarinpalService::request()
    ├─ success → redirect customer to Zarinpal
    └─ failure → /order/failed/{code} with Retry button
                  (order remains stored; payment was not completed)

Customer pays or cancels on Zarinpal
    ↓
payment/zarinpal_callback.php
    ├─ Status=NOK
    │    → payment_status=failed
    │    → failure page
    │
    └─ Status=OK
         → ZarinpalService::verify()
             ├─ success → payment_status=paid, status=confirmed
             │            → confirmation SMS
             │            → success page
             │
             └─ failure → payment_status=failed
                          → failure page + Retry button
```

### 5.4 Coupons (`app/services/CouponService.php`)

Validation includes:

- enabled/active state
- expiration date
- usage limits (`max_uses` / `used_count`)
- minimum order amount

A discount can never exceed the order total because the final value is bounded with `min()`.

The usage counter is incremented only after the order is successfully committed, not merely when the coupon is applied to the cart.

### 5.5 Standard SMS Notifications (`app/services/SmsService.php`)

The service follows a **fail-safe by default** model.

Until `SMS_ENABLED` is enabled and the required provider key is configured:

- no real SMS is sent
- the message is written to `sms_log`
- notification configuration cannot break order creation or order-status updates

This service is used for standard order-related notifications, while `FarazSmsService` handles verification OTP messages.

### 5.6 SMS/Email Diagnostics (`admin/diagnostics.php`, `admin/notifications_log.php`) — Introduced in 1.2.2

The notification services distinguish between two layers of output:

1. A **user-safe error message**, such as “SMS delivery failed”, which never exposes sensitive protocol details.
2. **Full diagnostic data**, including URL, payload, HTTP status, cURL errors, raw API response, or full SMTP conversation, stored in `debug_info` and visible only to `super_admin` users through the admin panel.

`admin/diagnostics.php` provides three diagnostic actions:

- Faraz account balance lookup via `GET /account/balance`
- Faraz pattern lookup via `GET /patterns/{code}`
- raw SMTP connectivity/authentication through `smtpConnect()` without sending a message

The diagnostics deliberately avoid sending a test OTP because a live test message may create a real cost and involve a real customer. The diagnostics instead verify connectivity and configuration directly.

The same low-level mechanisms used by the production services (`callApi()` / `testConnection()`) are reused so the diagnostic path and the production path share the same logging behavior.

**Why store diagnostics in the database instead of relying on `error_log()`?**

On shared hosting, server log access often requires File Manager or SSH access that a store administrator may not have. Database-backed diagnostics make the technical evidence available directly from the admin panel without additional infrastructure.

### 5.7 Multi-Image Product Gallery — Introduced in 1.2.2

The `product_images` table has existed since 1.0.0 and storefront product pages have read from it from the beginning. Until 1.2.2, however, the admin panel could only set the primary cover image in `products.image`.

The new gallery management logic in the product editor:

- accepts `gallery_images[]` through `<input type="file" multiple>`
- validates every uploaded file using `handleProductImageUpload()` and real file-type detection with `finfo`
- inserts images using increasing `sort_order` based on the product's current maximum `sort_order`
- supports deletion using `delete_image_ids[]`
- removes both the database row and the physical file from disk
- processes deletion before insertion when both happen in one submission so the next `sort_order` is calculated correctly
- keeps the primary cover image (`products.image`) completely independent from the gallery

The cover image continues to serve product cards, Open Graph metadata, and JSON-LD.

### 5.8 Customer Phone/Email Verification (`app/services/VerificationService.php`, `FarazSmsService.php`, `EmailService.php`) — Introduced in 1.2.1

Customer verification is divided into three responsibilities:

- **`VerificationService`**
  - generates a six-digit numeric code
  - hashes the code with `sha256` before storage
  - enforces 10-minute expiry
  - allows at most five incorrect attempts
  - enforces a minimum 60-second interval between send requests
  - delegates delivery to the appropriate channel

- **`FarazSmsService`**
  - sends pattern-based OTP SMS through the Faraz SMS / Iran Payamak API
  - uses a pre-approved pattern rather than arbitrary message text
  - pattern code and variable name are configurable in `config.php`

- **`EmailService`**
  - sends email through authenticated SMTP
  - uses the PHPMailer package stored directly in `app/vendor/PHPMailer/`
  - intentionally avoids PHP's built-in `mail()` function

Both delivery services are fail-safe. When their respective integrations are not configured, the application logs the attempt rather than crashing.

#### Delivery Fixes in 1.2.2

After real deployment, two additional issues were identified:

1. `FarazSmsService` sent `number_format = 'en'`; the API requires `'english'`.
2. The email-verification controller did not send a code on the first GET request to `/verify-email`.

The email verification fix intentionally runs the automatic send only on `GET` requests (`REQUEST_METHOD !== 'POST'`) rather than on every controller execution.

This is important because a `POST` request may either submit a new code for validation or trigger a resend. Sending automatically during POST processing could generate a new verification code immediately before validating the code the user just entered and could therefore invalidate a still-valid previous code.

The resulting behavior was tested:

- first GET to `/verify-email` creates one delivery log entry
- subsequent POST requests do not automatically create another delivery record unless the explicit resend action is used

### 5.9 PHP/MySQL Timezone Synchronization (`app/core/db.php`) — Fixed in 1.2.1

This is an important deployment requirement for environments where MySQL does not use the same timezone as PHP.

The project configures PHP for `Asia/Tehran` (`UTC+3:30`), while many shared-hosting MySQL instances default to UTC. Without synchronization, values created using MySQL `CURRENT_TIMESTAMP` can differ from timestamps interpreted through PHP by roughly 3.5 hours.

This previously affected:

- cart price-guarantee age calculation
- 60-second verification-code resend throttling

The fix calculates the current PHP timezone offset dynamically and applies the equivalent `SET time_zone = '+HH:MM'` value to the active PDO connection.

No fixed timezone offset is hardcoded in application logic beyond the actual configured PHP timezone.

### 5.10 Effective Product Stock (`effectiveStockSqlFragment()`) — Fixed in 1.2.1

When `has_variants` is enabled, `products.stock` is intentionally stored as `0`, because real inventory is represented by the sum of `product_variants.stock`.

The storefront product cards previously ignored this and read `products.stock` directly.

`effectiveStockSqlFragment()` resolves this by using:

- the sum of variant stock when variants exist
- otherwise the product-level `stock`

The resulting SQL expression is exposed as `effective_stock` in product-list queries across the homepage, category pages, and tag pages.

`product_card.php` prefers `effective_stock` when present and falls back to the simple `stock` value when it is not.

### 5.11 Product Tags and SEO

Product tags use a simple many-to-many relationship between `tags` and `product_tags`.

`syncProductTags()` replaces the product's current tag relationships with the current selection each time the product form is saved.

There is no tag ownership model; any admin can modify any product's tags.

The `seo_indexing_enabled` setting controls:

- `<meta name="robots">` in `views/layout/header.php`
- `robots.php`
- whether `robots.txt` advertises the sitemap

`sitemap.php` always generates its list of pages independently. Making a sitemap available does not itself force indexing; crawler access is controlled through the robots configuration.

Product pages also generate a complete `schema.org/Product` JSON-LD object using the same effective-stock calculation as the storefront UI.

### 5.12 Admin Authentication and Roles (`app/core/auth.php`)

- `requireAdmin()` must be called by admin pages.
- `requireSuperAdmin()` protects `admin/users.php` and full order deletion in `admin/orders.php` and `admin/order_detail.php`.
- `is_active=0` provides account suspension without deleting the admin.
- The system must always retain at least one `super_admin`.
- The last `super_admin` cannot be removed or deactivated.
- Full order deletion is server-side protected; hiding the button in the UI is only an additional usability layer.
- A normal `admin` cannot bypass the privilege check by manually crafting a POST request.

### 5.13 CSRF, XSS, and SQL Injection

- All POST forms include a CSRF token generated through `csrfField()` and validated with `verifyCsrf()`.
- Text output is escaped through `e()`, which wraps `htmlspecialchars`.
- Database queries use PDO prepared statements through `db()->prepare(...)`.
- User-controlled input is never directly concatenated into SQL strings.

### 5.14 Site Branding, Announcement Bar, and Footer Settings — Introduced in 1.3.0

The header and footer moved from being mostly static markup to reading their content from the `settings` table, so an admin can change them without touching code:

- **Logo** (`site_logo` setting): uploaded from `admin/settings.php`. The uploaded file is stored under `uploads/branding/` and validated the same way as product images (`finfo`-based real MIME-type detection, 2 MB limit, `move_uploaded_file()`). `siteLogoUrl()` in `app/core/functions.php` returns the logo's URL, or `null` when no logo has been uploaded (or the stored file is missing), so both `views/layout/header.php` and `views/layout/footer.php` can fall back to the text `SITE_NAME` logo.
- `BRANDING_UPLOAD_DIR` / `BRANDING_UPLOAD_URL` are derived once in `app/bootstrap.php` from the existing `UPLOAD_DIR` / `UPLOAD_URL` constants (`dirname(...) . '/branding/'`), rather than requiring a new constant to be added to every already-deployed site's `config.php`.
- **Announcement bar** (`announcement_bar_enabled`, `announcement_bar_text`, `announcement_bar_link`): a site-wide strip rendered right under `<header>` in `views/layout/header.php`, shown on every page when enabled. Wrapped in a link only when a link is configured.
- **Footer content** (`footer_about_teaser_text`, `footer_shipping_badge_text`, `store_phone`): free-text settings rendered by `views/layout/footer.php`. `store_phone` is also used on `/contact`.
- **Social links** (`social_{instagram,telegram,bale,torob}_enabled` / `_url`) and **eNamad** (`enamad_enabled`, `enamad_embed_code`): each social network is independently toggled and linked; only enabled networks with a non-empty URL are rendered. `enamad_embed_code` stores the raw badge markup/script an admin pastes in after eNamad certification and is echoed unescaped in the footer — this is intentional (it's admin-only input, equivalent in trust level to `config.php`), since eNamad's badge is a script/HTML snippet, not plain text.

`app/controllers/admin/settings.php` was refactored so each settings section is its own `<form>` posting a `section` hidden field, and the controller only writes the keys belonging to that section. This replaces the earlier pattern of every form re-submitting every other section's values as hidden inputs to avoid resetting them — a pattern that becomes increasingly fragile (and easy to break by forgetting one hidden field) as the number of settings grows.

### 5.15 Order List Product/Variant Summary — Introduced in 1.3.0

`app/controllers/admin/orders.php` previously only showed order-level fields (code, customer, total, status); seeing which product **variant** was purchased required opening `order_detail.php` for every single order. A single grouped query now loads every visible order's items (`product_name`, `variant_label`, `quantity`) up front and `views/admin/orders.php` renders them inline in a new "Order items" column, so the admin can see exactly what was ordered — including size/color — directly from the list.

### 5.16 Admin Table Row-Alignment Bug — Fixed in 1.3.0

`views/admin/products.php`, `categories.php`, and `users.php` all had an actions cell written as `<td class="admin-actions">`, where `.admin-actions` sets `display: flex`. Applying `display: flex` directly to a `<td>` overrides its `display: table-cell` internal type, which is what `vertical-align: middle` requires to have any effect — so that one cell silently stopped honoring the row's vertical centering. This was invisible on short rows, but on `products.php`, a row whose variant-stock summary wrapped onto several lines made the row noticeably taller, and the action buttons stayed pinned to the top of that taller cell while every other column stayed vertically centered.

Fixed by keeping the actions `<td>` a plain cell and moving `class="admin-actions"` onto an inner `<div>` instead, plus adding an explicit `vertical-align: middle` to `.admin-table th, .admin-table td` in `assets/css/admin.css` so cross-browser default differences can't reintroduce the same issue.

---

## 6. Storefront Routing (Framework-Free)

Requests that do not match a physical file or directory are rewritten by `.htaccess` to:

```text
index.php?route=<path>
```

`index.php` splits the route with `explode('/', ...)` and selects a controller based on the first segment.

Current route roots include:

```text
home
category
product
cart
checkout
order
about
contact
terms
signup
login
logout
account
verify-phone
verify-email
tag
search
```

This is intentionally implemented as a simple `switch/case` router because the number of storefront pages is small and relatively stable.

`robots.txt` and `sitemap.xml` are handled separately in `.htaccess` and rewritten directly to `robots.php` and `sitemap.php`, outside the main storefront router because their output formats are plain text and XML rather than HTML.

### 6.1 Subcategories — Since 1.2.0

Categories support one nested level through `categories.parent_id`.

A parent-category page:

- displays direct subcategories as clickable chips
- includes products assigned to those direct child categories through `getCategoryAndChildIds()`

The main navigation lists only top-level categories (`parent_id IS NULL`).

### 6.2 Automatic Unique SKU — Since 1.2.0

`generateUniqueSku()` in `app/core/functions.php` generates `SOCK-XXXXXX` values and ensures uniqueness against the database.

- Empty SKU fields are generated automatically.
- Manually supplied values are checked for uniqueness.
- `products.sku` has a real database-level `UNIQUE KEY`.

### 6.3 Products with Variants (`has_variants`) — Since 1.2.0

The product form provides a `has_variants` checkbox.

When enabled:

- JavaScript disables the global stock field.
- The variant section becomes active.
- The server independently enforces the same rule.
- Variant rows are ignored when `has_variants` is absent.
- When variants are enabled, `products.stock` is stored as `0`.

This is deliberate defense in depth and does not depend on client-side JavaScript.

### 6.4 Product Tags — Since 1.2.1

`syncProductTags()` removes the product's existing `product_tags` rows and recreates them from the currently selected checkboxes and any newly typed tags.

New tags are created in `tags` when they do not already exist.

There is no ownership concept; every admin may change any product's tags.

### 6.5 SEO Controls — Since 1.2.1

The `seo_indexing_enabled` setting is disabled by default and affects three main mechanisms:

1. The `<meta name="robots">` tag in `views/layout/header.php`
2. `robots.php`, which emits `Disallow: /` when indexing is disabled
3. `robots.txt`, which advertises the sitemap when indexing is enabled

`sitemap.php` generates the sitemap independently of the setting.

Product pages provide complete `schema.org/Product` JSON-LD and use the same `effectiveStockSqlFragment()` logic used by the storefront to keep SEO availability data consistent with visible inventory.

### 6.6 Storefront Search — Introduced in 1.3.0

`app/controllers/site/search.php` handles the `search` route. It's a plain `LIKE '%term%'` match against `products.name` and `products.description`, paginated the same way as `category.php`. This is intentionally simple (no full-text index, no relevance ranking) since it matches the product catalog's expected scale on this hosting plan; it can be swapped for `MATCH ... AGAINST` full-text search later without changing the route or view.

The search box itself (`views/layout/header.php`) is a single collapsible panel toggled by a magnifier button (`#searchToggle` / `#searchBarPanel` in `assets/js/main.js`), rendered identically on mobile and desktop, rather than two separate responsive layouts — chosen specifically to avoid the flexbox `order` bugs that come from conditionally showing/hiding different search markup at different breakpoints.

### 6.7 Product Tag SEO Refinements — Introduced in 1.3.0

The tag slug format was already correct before this version: `slugify()` (`app/core/functions.php`) turns spaces into hyphens (`-`), which is what search engines treat as a word separator — unlike underscores (`_`), which most engines do not split into separate keywords. No change was needed there.

Two related refinements were made:

1. **Decorative "#" moved out of the anchor text.** `views/site/product.php` previously rendered each tag as `#<?= e($tag['name']) ?>` — a literal `#` character inside the clickable link text. It's now `.tag-pill::before { content: "#"; }` in `assets/css/style.css`, so the tag still displays with a leading `#`, but the actual `<a>` text search engines and screen readers see is the clean tag name. A `rel="tag"` attribute (the HTML tag-cloud microformat) was also added.
2. **Per-tag meta description.** `app/controllers/site/tag.php` now sets `$metaDescription` to a tag-specific sentence (including the tag name and product count) instead of falling back to the site-wide generic description, and `views/site/tag.php` uses an `<h1>` for the page heading instead of `<h2>` (every page should have exactly one `<h1>`; the tag page previously had none).

---

### 6.8 Brand Color System — Introduced in 1.4.0

The core storefront colors are centralized in the `:root` block at the top of `assets/css/style.css`. The main variables are:

```css
--color-bg, --color-surface, --color-text, --color-muted, --color-border,
--color-primary, --color-primary-dark, --color-primary-light, --color-accent,
--color-success, --color-danger
```

`--color-primary` is reserved for primary button backgrounds and other elements that need to carry white text. `--color-primary-light` and `--color-accent` are used for badges, hover states, and secondary details.

The goal is to keep theme changes localized instead of scattering fixed color values throughout the stylesheet. A small set of semantic status colors remains independent in `assets/css/admin.css` so states such as success, error, and warning do not depend on the brand palette.

`theme-preview.html` is a design/reference tool for comparing palettes and is not part of the application's runtime flow.

### 6.9 Homepage Product Carousel — Introduced in 1.4.0

The homepage carousels use `.carousel-wrap` and `.carousel-track` in `assets/css/style.css` and rely on native CSS `scroll-snap` rather than an external carousel library.

- Cards are not grouped into separate hard-coded pages in the markup.
- Three cards fit in the carousel on desktop.
- Two cards fit at tablet widths.
- On mobile, one full card plus part of the next card is visible as a swipe cue.
- Previous/next controls in `assets/js/main.js` scroll by `track.clientWidth`.
- The document direction is detected with `getComputedStyle(document.documentElement).direction` so the controls behave correctly in the RTL storefront.
- The arrow controls are hidden on small mobile widths where touch scrolling is the primary interaction.

The component is reusable: other homepage carousels can use the same `.carousel-wrap > .carousel-track` structure without introducing new carousel-specific CSS.

## 7. Versioning and Change Documentation

Every technical change—bug fix, feature, structural modification, or behavior change—must:

1. Update `APP_VERSION` in `app/bootstrap.php` using Semantic Versioning (`MAJOR.MINOR.PATCH`).
2. Add a new, precise entry to `docs/CHANGELOG.md` describing the change relative to the previous version.
3. Add a new file under `database/migrations/` whenever an `ALTER TABLE` or another database upgrade is required. The base `schema.sql` must not be changed in a way that forces existing installations to be re-imported; existing databases should be upgraded through migrations.
4. Update this `ARCHITECTURE.md` whenever the architectural behavior described here changes.
