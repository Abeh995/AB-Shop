# Changelog

This document provides a detailed history of all project releases. Each version records the changes made **relative to the previous version**.

Versioning follows `MAJOR.MINOR.PATCH` (Semantic Versioning).

---

## [1.2.1] — Product Tags, Full Customer Profile, Phone/Email Verification, SEO, and Stock Label Fix

### 🐛 Reported Bug Fix: Incorrect "Out of Stock" Label on Variant-Based Products

**Problem:** On storefront listing pages (home and category pages), every product with variants was incorrectly displayed with an "Out of stock" label, even when one or more variants had inventory.

**Root cause:** Since version 1.2.0, variant-based products intentionally store `0` in `products.stock` because their actual inventory must be calculated from the variant rows. However, `product_card.php` in listing queries still read `stock` directly when determining availability.

**Solution:** Added `effectiveStockSqlFragment()` in `app/core/functions.php`. It returns a SQL fragment that uses the sum of variant inventory when variants exist and otherwise falls back to the product's own `stock` column. The computed `effective_stock` was added to the home page (`home.php`, Featured and Newest queries), category page (`category.php`), and new tag page (`tag.php`). `product_card.php` now checks this value first and falls back to `stock` when unavailable.

**Tested:** A product with `stock=0` and a variant with stock `12` was correctly treated as available. A variant-based product with total variant stock `0` was correctly shown as out of stock. Both cases were verified.

### 🐛 Additional Critical Bug: PHP/MySQL Timezone Mismatch

**Problem:** MySQL on the hosting environment commonly runs with `UTC`, while PHP uses `Asia/Tehran` (`UTC+3:30`). Values generated with MySQL `CURRENT_TIMESTAMP`/`NOW()` could therefore differ from PHP `time()`/`strtotime()` by approximately 3.5 hours.

**Affected areas:**
- **Cart Price Guarantee:** the seven-day, daily-granularity logic rarely produced an incorrect final result, but the comparison was technically wrong.
- **Verification-code resend interval:** the 60-second limit introduced in 1.2.1 was effectively disabled because PHP interpreted a newly created code as having been sent hours earlier. This was reproduced directly in testing.

**Solution:** `app/core/db.php` now runs `SET time_zone = '+03:30'` immediately after connecting, with the offset calculated from the project's configured `date_default_timezone_get()` rather than hardcoded.

**Tested:** MySQL `NOW()` and PHP `date('Y-m-d H:i:s')` matched after the fix. The first verification request succeeded, an immediate second request was rejected, and the cart price guarantee remained active immediately after adding an item.

### ✨ New Feature: Product Tags

- Added `tags` (unique name/slug) and `product_tags` (many-to-many) tables.
- The add/edit product form displays all existing tags, including tags attached to active or inactive products, as checkboxes.
- **Any admin can modify any product's tags at any time.**
- New tags can be entered as comma-separated text and are created immediately when missing.
- Added `show_product_tags` store setting (default: enabled) to control tag visibility on product pages.
- Added `/tag/{slug}` to list products associated with a tag, serving both product discovery and SEO.
- Product pages display small `#tag` links to the corresponding tag pages.

### ✨ New Feature: Full Customer Profile

- `/account` was fully redesigned with editable name/email fields, phone/email verification badges, a current-cart summary, and order history.
- Added `/account/order/{order_code}` for complete order details, including items, amounts, and shipping address.
- Server-side ownership validation returns `404` when the requested order does not belong to the authenticated customer.
- Changing the email address automatically resets email verification.

### ✨ New Feature: Phone Verification via SMS (Faraz SMS)

- **Registration flow changed:** registration no longer logs the customer in immediately. The account is created, a six-digit SMS code is sent, and the customer must enter it at `/verify-phone` before a full authenticated Session is established.
- Correct-password login for an unverified account redirects to `/verify-phone` and sends a new code.
- Customers created in versions 1.2.0 and earlier are automatically marked as phone-verified during migration because phone verification was not previously required.
- Added `app/services/FarazSmsService.php` for the pattern-based Faraz SMS / Iran Payamak API.
- `FARAZ_OTP_PATTERN_CODE` and `FARAZ_OTP_PATTERN_VAR` are configurable because the provider requires a pre-approved pattern.
- ⚠️ **Manual configuration required:** `FARAZ_LINE_NUMBER` must be filled from the Faraz panel under "Lines". This value was not available in the supplied configuration information.
- Fail-Safe by default: real SMS is not sent until `FARAZ_SMS_ENABLED` is enabled and the API key, pattern, and line are configured; messages remain logged and the site does not crash.

### ✨ New Feature: Email Verification via SMTP

- Added `app/services/EmailService.php` using **PHPMailer v6.9.1**, bundled under `app/vendor/PHPMailer/` without Composer.
- SMTP was chosen over PHP `mail()` to provide more reliable delivery on shared hosting.
- Added `/verify-email`. Customers can verify an email after phone verification or later from their profile.
- Email verification is optional and never blocks login; phone verification remains mandatory.
- Fail-Safe by default: real email is not sent until `SMTP_ENABLED` and real SMTP credentials are configured.

### 🔒 Shared Verification-Code Service (`app/services/VerificationService.php`)

- Verification codes are six digits, valid for 10 minutes.
- Maximum five incorrect attempts are allowed; a new code is then required.
- At least 60 seconds must pass between send requests.
- Codes are stored as `sha256` hashes rather than plaintext.
- Direct service tests confirmed incorrect-code rejection, one-time use, reuse prevention, and resend throttling.

### ✨ New Feature: SEO and Google Indexing Controls

- Added `seo_indexing_enabled` (default: disabled) to the admin settings.
- `robots.txt` and `sitemap.xml` are generated dynamically by `robots.php` and `sitemap.php`.
- When indexing is disabled, `robots.txt` returns `Disallow: /`.
- When enabled, public storefront pages are allowed, while admin, AJAX, cart, checkout, and account areas remain disallowed. The sitemap URL is also advertised.
- `sitemap.xml` includes active categories and products with `lastmod`.
- Added `robots`, `canonical`, and basic Open Graph tags to page `<head>` sections.
- Product pages now provide product descriptions, Open Graph images, and **Schema.org Product JSON-LD** containing name, image, SKU, price in Rials, and effective inventory status.

### 🗄️ Database Changes (`database/migrations/004_v1.2.1_tags_verification_seo.sql`)

- New tables: `tags`, `product_tags`, `verification_codes`.
- `customers`: added `email`, `phone_verified_at`, `email_verified_at`.
- Existing customers receive `phone_verified_at = created_at` during migration.
- New settings: `show_product_tags` (default `1`) and `seo_indexing_enabled` (default `0`).
- **Tested:** migration was executed against a simulated 1.2.0 database containing customer and order data. No data was lost, and existing customers were automatically marked as phone-verified.

### ⚙️ New `config.php` Settings

All of the following are disabled/empty by default for Fail-Safe operation:

`FARAZ_SMS_ENABLED`, `FARAZ_API_KEY`, `FARAZ_OTP_PATTERN_CODE`, `FARAZ_OTP_PATTERN_VAR`, `FARAZ_LINE_NUMBER`, `SMTP_ENABLED`, `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_FROM_EMAIL`, `SMTP_FROM_NAME`.

Full configuration instructions are documented in `README-DEPLOY.md`.

---

## [1.2.0] — Customer Accounts, Persistent Cart, Price Guarantee, Child Categories, and Product Management Improvements

### Summary

This release evolves the store from a **Guest Checkout-only** model into a store with full customer accounts, while also adding several management capabilities such as child categories, smarter multi-variant products, automatic SKUs, order deletion, and quick access to featured products. None of these changes disrupt the `v1.1.0` architecture (the `app`/`views` separation); everything is built on top of the existing foundation.

### ✨ New Feature: Customer Accounts via Mobile Number

- Added the `customers` table with unique `phone`, `password_hash`, and `full_name`.
- Added new storefront pages: `/signup`, `/login`, `/logout`, and `/account` (profile + order history for orders associated with the account).
- **Important design decision:** customer authentication uses mobile number + **password**, rather than SMS one-time passwords (OTP). `SmsService` is Log-Only by default until a real SMS provider is connected, which would make OTP authentication unusable out of the box. This decision is documented in `app/core/customer_auth.php`, and OTP can be added later as an alternative.
- Security includes `password_hash`/`password_verify`, `session_regenerate_id` after login/registration to prevent Session Fixation, throttling of failed attempts via `usleep`, and Open Redirect prevention for the login `next` parameter (only internal paths are accepted).
- Checkout for authenticated users is pre-filled with the account name and mobile number for convenience; full server-side validation still applies.
- Added nullable `customer_id` to `orders`; guest orders remain fully supported.

### ✨ New Feature: Persistent Cart for Authenticated Users

- Added `cart_items` (`customer_id + product_id + variant_id` with a composite `UNIQUE KEY`). `variant_id` uses `0` instead of `NULL` to represent "no variant" because MySQL permits multiple `NULL` values in a unique key.
- Completely rewrote `app/core/cart.php` to support two distinct storage paths:
  - **Guest:** stored in `$_SESSION['cart']`.
  - **Authenticated user:** stored in the database and persisted across devices and sessions.
- The function interface (`cartAdd`, `cartUpdateQty`, `cartRemove`, `cartClear`, `cartCount`, `cartDetails`) is identical in both modes, so controllers and views do not need to know the user's authentication state.
- **Guest cart merge after authentication:** when a guest adds items before logging in, `mergeGuestCartIntoCustomerCart()` transfers them to the account's persistent cart using a merge rather than replacement, so existing cart items are not lost.
- **Tested:** an authenticated user was able to view the same cart contents from a completely new Session (new Cookie, simulating another device), confirming that the persistent cart is linked to the account rather than the browser.

### ✨ New Feature: Cart Price Guarantee

This is the most involved piece of business logic introduced in this release and was therefore tested more extensively.

- Each `cart_items` row stores a `locked_unit_price`: the effective product/variant price **at the moment the item was added**.
- The cart's guarantee start time is `MIN(added_at)` across the user's current cart items (the oldest remaining item). Because it is derived from the current rows, completely emptying the cart and later adding a new item automatically starts a new guarantee period; no dedicated reset column or logic is required.
- Configuration is manageable through `admin/settings.php` and the new Key-Value `settings` table:
  - `price_guarantee_enabled`
  - `price_guarantee_days` (default: 7)
- In `cartDetailsForCustomer()`, if the cart age is within the guarantee period, `locked_unit_price` is used. Otherwise, the live product price replaces it permanently for that cart until the cart is emptied and populated again.
- The cart page displays a status banner such as "Price guaranteed until X" or "Guarantee period expired; prices have been recalculated", and items using a locked price receive a "Guaranteed Price" label.
- **Three scenarios were tested in practice**, not only by code review:
  1. Increasing the product price during the seven-day guarantee period → the cart total **remained unchanged** and continued using the old price.
  2. The same cart was simulated with `added_at` set to eight days ago → the total immediately switched to the current live price.
  3. Disabling the feature completely from the admin panel → even a newly created cart immediately used live prices.

### ✨ New Feature: Super Admin Order Deletion

- Added a "Delete Order Permanently" action to both the order list and order detail pages; it is visible only to `super_admin`.
- **Server-side protection** is enforced through `requireSuperAdmin()`, not merely by hiding the button in the UI. A normal admin sending a manually crafted POST request is rejected (tested).
- Deleting an order also removes its `order_items` through `ON DELETE CASCADE`; no manual cleanup query is required.

### ✨ New Feature: Product Child Categories

- Added nullable, self-referencing `parent_id` to `categories`.
- In `admin/categories.php`, each category can select a parent category. Only top-level categories are offered as parents to intentionally limit the hierarchy to one nested level and keep the structure simple.
- On the storefront, a parent category page displays its child categories as clickable chips **and includes products belonging to those child categories** through `getCategoryAndChildIds()`. For example, opening "Men's Socks" also includes products from "Ankle Socks" without requiring the customer to enter the child category page directly.
- The top navigation displays only top-level categories to avoid clutter; child categories remain available from the parent category page.

### ✨ New Feature: Automatic Unique SKU Generation

- Added `generateUniqueSku()` in `app/core/functions.php`. It generates an SKU in the `SOCK-XXXXXX` format using a random hexadecimal string, checks the database for collisions, and retries up to 10 times before falling back to `uniqid`.
- If the SKU field is empty when adding a product, one is generated automatically. A manually entered SKU is validated for uniqueness and rejected if it already exists (tested).
- Added a real database `UNIQUE KEY` to `products.sku`; uniqueness is no longer enforced only at the application layer.
- **Migration for existing data:** products without an SKU (empty/`NULL`) are backfilled during migration with default values such as `SOCK-010001`, `SOCK-010002`, etc. Existing manually assigned SKUs remain untouched. Tested with a sample manual SKU `MYOWN-001`, which remained unchanged after migration.

### ✨ New Feature: `has_variants` Product Checkbox

- Added the `has_variants` checkbox to the add/edit product form.
- When enabled through client-side JavaScript:
  - "Overall Stock" is disabled and visually de-emphasized because inventory is managed through variant rows.
  - The size/color variants section becomes enabled and highlighted.
- Server-side behavior is independent of JavaScript as defense in depth:
  - If `has_variants` is absent, submitted variant rows are completely ignored.
  - If `has_variants` is present, "Overall Stock" is always stored as `0` regardless of the submitted value because actual inventory is derived from the variants.

### ✨ New Feature: Per-Variant Stock in the Admin Product List

- In `admin/products.php`, products with variants now show stock per variant rather than a single overall number, for example `39-42 Black: 15` and `43-46 Black: 8` on separate lines.
- Instead of issuing an N+1 query for every product, the implementation uses a `GROUP_CONCAT` subquery within the main product-list query to preserve performance.

### ✨ New Feature: Quick Access to Featured Products in Admin

- Added a new admin sidebar link ("⭐ Featured") pointing directly to `products.php?featured=1`.
- It reuses the existing product list controller/view rather than duplicating code and adds only a `WHERE is_featured = 1` condition to the existing query.

### 🗄️ Database Changes (`database/migrations/003_v1.2.0_customer_accounts_cart_price_guarantee.sql`)

- New table: `customers`
- New table: `cart_items`
- New table: `settings` (with initial values `price_guarantee_enabled=1` and `price_guarantee_days=7`)
- `categories`: added `parent_id` (nullable, self-referencing foreign key)
- `products`: backfilled missing `sku` values + added a `UNIQUE KEY` on `sku`
- `orders`: added nullable `customer_id` foreign key referencing `customers`
- **Tested:** the migration was executed against a simulated copy of the v1.1.0 database containing real-like data (orders, products, and admins) with no data loss; existing manual SKUs were preserved.

### 🎨 UI Changes

- Added a login/account icon next to the cart icon in the storefront header. Depending on authentication state, it links to `/login` or `/account`.
- The main navigation now displays only top-level categories; child categories are accessible within the parent category page.
- Added the price-guarantee status banner and "Guaranteed Price" labels to the cart page.

### 📌 Backward Compatibility

- No breaking changes were introduced to existing storefront or admin routes. Guest users continue to work exactly as before.
- After the migration, running the old `install.php` (if it still exists) does not affect the `admins` table; the installer is intended only for the initial installation and automatically locks itself once an admin already exists.

---

## [1.1.0] — Payment Gateway, SMS, Coupons, Multi-Admin, and Codebase Restructuring

### Summary

This release contains three groups of changes: (a) a critical live-site bug fix, (b) a complete directory/codebase restructuring to separate application logic from presentation, and (c) new capabilities including Zarinpal payments (Sandbox), SMS infrastructure, coupon support in the purchase flow, and multi-admin role support.

### 🐛 Bug Fixes

- **[Critical] `/cart` Forbidden Error**
  - **Root cause:** a physical `cart/` directory containing `add.php`, `update.php`, and `remove.php` for AJAX operations had the same name as the storefront `/cart` route. When `.htaccess` sees a requested path that maps to a real directory, the rewrite is bypassed. Because directory listing is disabled via `Options -Indexes`, Apache returned `403 Forbidden` instead of reaching the cart-page routing logic.
  - **Fix:** renamed `cart/` to `ajax/` and renamed the endpoint files to `cart_add.php`, `cart_update.php`, and `cart_remove.php` so they cannot collide with defined routes. All references in `assets/js/main.js` and `views/site/cart.php` were updated.
  - **Lesson learned:** this rule was added to `docs/ARCHITECTURE.md` section 6: **no physical directory should share the name of a route defined in `index.php`.**

- **[Minor] Incorrect Database Table Count**
  - Previous documentation incorrectly stated that the 1.0.0 schema contained nine tables. The actual count was **eight** (`admins, categories, coupons, orders, order_items, product_images, product_variants, products`). With the addition of `sms_log` in 1.1.0, the actual total became **nine**. This was a documentation/reporting error, not a code defect.

### ♻️ Refactor — Separation of Application Logic and Presentation

- Removed `includes/` and redistributed its contents:
  - `db.php`, `functions.php`, `csrf.php`, `auth.php`, `cart.php`, `bootstrap.php` → moved into `app/core/` and `app/bootstrap.php`.
  - Site `header.php` and `footer.php` → moved into `views/layout/`.
  - `admin_header.php` and `admin_footer.php` → moved into `views/admin/layout/`.
- Removed `pages/`. Each page was split into two files:
  - **Controller** (logic/query/form processing) → `app/controllers/site/{page}.php`
  - **View** (HTML only) → `views/site/{page}.php`
- The same pattern was applied to all admin pages: `app/controllers/admin/{page}.php` + `views/admin/{page}.php`.
- `admin/` no longer contains application logic; it only contains **thin entry points** that load the bootstrap, enforce access control, and require the actual controller.
- Added `renderView($view, $data)` to `app/bootstrap.php` so controllers can render a view with explicit data without directly handling the view filesystem.
- Both `app/` and `views/` are protected from direct browser access with `.htaccess` (`Require all denied`); these directories are accessed only through server-side `require`.
- **Reason for the change:** future development (for example, modifying a page's appearance) can be done without touching business logic, and vice versa. It also greatly reduces the risk of route/directory collisions like the one described above because application logic is no longer colocated with directly accessible files.

### ✨ New Feature: Zarinpal Payment Gateway (Sandbox)

- Added `app/services/ZarinpalService.php` based on Zarinpal REST API v4.
- Configured for **Sandbox** by default (test mode with no real-money transactions). Live mode can be enabled by changing `ZARINPAL_MERCHANT_ID` and `ZARINPAL_SANDBOX` in `config/config.php`.
- Added "Online Payment (Zarinpal)" as a checkout option alongside Cash on Delivery (COD).
- Added `payment/zarinpal_callback.php` to handle gateway callbacks and finalize transactions.
- Added `payment/retry.php` to retry payment for an existing order whose previous payment attempt failed, without losing the order or already-deducted stock.
- Added `/order/failed/{code}` for failed payment states with a retry action.
- Added `payment_status`, `payment_authority`, and `payment_ref_id` columns to `orders`.

### ✨ New Feature: SMS System

- Added `app/services/SmsService.php`, prepared for Kavenegar integration.
- Designed as Fail-Safe: if the API key is missing or `SMS_ENABLED=false`, no real SMS is sent and the event is logged to the new `sms_log` table. The site never fails simply because SMS configuration is incomplete.
- Automatic SMS messages are triggered for two events: successful online payment confirmation and order status changes made by an admin.

### ✨ New Feature: Coupon Support in the Checkout Flow

- Added `app/services/CouponService.php` for validation and discount calculation.
- Added a coupon field to the cart page (`views/site/cart.php`).
- The applied coupon is stored in `$_SESSION['coupon']` and validated again during checkout to protect against expiration or usage-limit changes between application and order placement.
- Added endpoints: `ajax/coupon_apply.php` and `ajax/coupon_remove.php`.
- Added nullable `coupon_id` to `orders` for a precise relationship to the coupon record, separate from the existing textual `coupon_code`.

### ✨ New Feature: Multi-Admin Role Support

- Added `role` (`super_admin` | `admin`, default `admin`) and `is_active` columns to `admins`.
- Added `admin/users.php`, restricted to `super_admin`, with:
  - Creating new admins with role selection
  - Changing another admin's password
  - Activating/deactivating accounts without deleting them
  - Deleting accounts with safeguards: the last `super_admin` cannot be deleted/deactivated, and admins cannot delete/deactivate their own account
- Added `requireSuperAdmin()` and `isSuperAdmin()` to `app/core/auth.php`.
- The first admin created through `install.php` is automatically assigned the `super_admin` role.
- The "Admin Management" sidebar link is shown only to `super_admin`.

### 🗄️ Database Changes

- Added `database/migrations/002_v1.1.0_payment_sms_coupons_admins.sql`.
  - This migration is for databases that already contain v1.0.0, such as the existing live site. It only adds columns/tables; **no data is deleted or rewritten**.
  - Existing orders that were already in a `confirmed` state or later are automatically marked with `payment_status = paid`, because online payment did not exist in the previous version.
  - The first existing admin is automatically promoted to `super_admin`.
- Updated the base `database/schema.sql` so fresh installations contain all of the new tables/columns directly; fresh installs do not need to run the migration separately.

### ⚙️ Configuration Changes (`config/config.php`)

Six new constants were added:

```php
define('ZARINPAL_MERCHANT_ID', '00000000-0000-0000-0000-000000000000'); // Default test merchant
define('ZARINPAL_SANDBOX', true);
define('SMS_ENABLED', false);
define('SMS_PROVIDER_API_KEY', '');
define('SMS_SENDER_LINE', '');
```

### 📄 Documentation

- Added `docs/ARCHITECTURE.md` as the authoritative reference for architecture, request lifecycle, data model, and critical security logic.
- Created `docs/CHANGELOG.md`.
- Added the `APP_VERSION` constant to `app/bootstrap.php` for application version tracking.

### ✅ Tests Performed for This Release

All of the following were tested and verified on a real PHP 8.3 + MariaDB environment rather than only through code review:

- Full PHP lint across all files with zero errors.
- Migration execution against a simulated v1.0.0 database with no data loss.
- Cart page bug fix verified from `403 Forbidden` to `200 OK`, including a simulation of Apache/mod_rewrite behavior.
- Coupon application/removal and discount correctness in final order totals.
- Full order creation using Cash on Delivery (COD).
- First admin login (`super_admin`) and creation of a second (`admin`) account from the admin panel.
- Permission enforcement: an `admin`-level account is blocked from the admin-management page and redirected to the dashboard while retaining access to the rest of the admin panel.
- Simulated failed Zarinpal callback with correct rendering of the payment-failure page and retry action.

### ⚠️ Deployment Notes for the Live Site (`absocks.ir`)

1. **Back up the current database first** using DirectAdmin or phpMyAdmin → Export.
2. Run `database/migrations/002_v1.1.0_payment_sms_coupons_admins.sql` **once** from phpMyAdmin's SQL tab against the live database.
3. Replace the entire project structure on the host with the new structure (`app/`, `views/`, `ajax/`, `payment/`, etc.), rather than copying only the new directories. Remove legacy `includes/`, `pages/`, and `cart/` directories to avoid collisions.
4. Re-enter the real production database credentials and other sensitive values into the new `config/config.php`; the repository version contains placeholders only.
5. After deployment, perform a test order using both payment methods (online and COD).

---

## [1.0.0] — Initial MVP Release

The first usable version of the store, including:

- Customer-facing storefront: home page, categories, product details with size/color variants, Session-based cart, Cash on Delivery checkout, order-success page, About, Contact, and Terms pages.
- Admin panel: dashboard, product CRUD with image upload and variant management, category CRUD, order management, and order status updates.
- Infrastructure: framework-free PHP, MySQL, Session-based admin authentication, CSRF/XSS/SQL Injection protection, and initial installation without CLI through `install.php`.
- Direct deployment to shared DirectAdmin hosting without Composer/npm/SSH.
