# Changelog

This document lists all project releases in detail. Each new release documents its changes **step by step against the previous version**.

Versioning format: `MAJOR.MINOR.PATCH` (Semantic Versioning)

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
