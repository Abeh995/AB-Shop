# Sock Store Architecture Documentation

**Current Version: `1.2.0`**

This document is the authoritative reference for the application's architecture and core business logic. Whenever the project changes, both this document and `CHANGELOG.md` must be updated accordingly.

---

## 1. Architectural Philosophy

This project is intentionally built **without a framework** (such as Laravel or Symfony) and **without Composer/npm on the production server** so that it can be deployed on inexpensive shared hosting with strict limitations, including limited SSH access and no guaranteed CLI environment. The architecture follows a **Lightweight Manual MVC** approach.

There are no framework classes, ORM, complex router, or Dependency Injection Container in the project. The application uses procedural PHP together with a small number of focused service classes.

---

## 2. Directory Structure and Responsibilities

```text
/
├── index.php                  Public front controller (routes requests to controllers only)
├── install.php                Initial installation (creates the first admin without requiring SSH)
├── .htaccess                  Rewriting, forced HTTPS, security headers
│
├── config/                    Sensitive configuration (protected with .htaccess Deny)
│   └── config.php             DB, Zarinpal, SMS, debug mode
│
├── app/                       All application logic — entire directory protected with .htaccess Deny
│   ├── bootstrap.php          Shared bootstrap entry point for all public entry files
│   ├── core/                  Shared application core
│   │   ├── db.php             PDO connection (Singleton)
│   │   ├── functions.php      Common helpers (price formatting, slugs, automatic SKU, category hierarchy, ...)
│   │   ├── csrf.php           CSRF token generation/validation
│   │   ├── settings.php       Read/write store Key-Value settings (introduced in 1.2.0)
│   │   ├── auth.php           Admin authentication + roles (super_admin/admin)
│   │   ├── customer_auth.php  Customer authentication via mobile number + password (introduced in 1.2.0)
│   │   └── cart.php            Cart logic (guest = Session, authenticated = database with price guarantee; rewritten in 1.2.0)
│   ├── services/              Independent, reusable services
│   │   ├── ZarinpalService.php Payment gateway integration
│   │   ├── SmsService.php      SMS sending/logging
│   │   └── CouponService.php   Coupon validation and discount calculation
│   └── controllers/            Controllers — data fetching/processing only, zero HTML
│       ├── site/               Storefront controllers (home, product, cart, checkout, ...)
│       └── admin/               Admin panel controllers
│
├── views/                     All HTML/templates (Presentation Layer) — entire directory protected with .htaccess Deny
│   ├── layout/                 Shared site header/footer
│   ├── site/                   Storefront views + partials
│   └── admin/                  Admin panel views + admin-specific layout
│
├── admin/                     Public admin entry points (thin files: bootstrap + auth guard + controller require)
├── ajax/                      AJAX endpoints (add/remove/update cart, apply coupon)
├── payment/                   Payment gateway endpoints (callback, retry)
├── assets/                    Public CSS/JS/static images
├── uploads/products/          Uploaded product images (PHP execution disabled in this directory)
├── database/
│   ├── schema.sql              Complete schema for fresh installations
│   └── migrations/             Upgrade scripts for existing installations
└── docs/                      Project documentation
```

### Why Separate `app/` and `views/`?

- `app/` is never requested directly by the browser. It is loaded only through PHP `require` statements. As a result, business logic, database queries, and form processing remain fully separated from HTML.
- `views/` is presentation-only. A view uses only the variables prepared by its controller through `renderView()`, and rarely performs direct queries. The intentional exception is the category menu in `views/layout/header.php`, because it is shared data required by all pages rather than page-specific data.
- This separation means that changing the appearance of a page requires modifying only the corresponding `views/...` file, while changing behavior (for example, discount calculation) is isolated to `app/controllers/...` or `app/services/...`.

---

## 3. Request Lifecycle

### Storefront Requests (for example, `/product/men-socks`)

1. `.htaccess` rewrites the request to `index.php?route=product/men-socks`, except for physical files/directories such as `assets/`, `ajax/`, `admin/`, and `uploads/`, which are served directly.
2. `index.php` loads the bootstrap (`app/bootstrap.php`), including Session, Config, Core, and Services.
3. `index.php` selects the controller based on the first route segment (`product` in this example) and requires `app/controllers/site/product.php`.
4. The controller fetches the required data from the database and calls `renderView('site/product', [...])`.
5. `renderView()` (defined in the bootstrap) extracts the supplied variables and requires `views/site/product.php`.
6. The view requires `views/layout/header.php` and `views/layout/footer.php` around its content.

### Admin Panel Requests (for example, `/admin/products.php`)

The flow is similar, except that `admin/products.php` is a thin physical entry point and is called directly by Apache because this path is a physical file and therefore bypasses the rewrite rule. It loads the bootstrap, calls `requireAdmin()`, and then requires `app/controllers/admin/products.php`.

### AJAX Requests (for example, adding an item to the cart)

Files under `ajax/` are independent endpoints. Each endpoint loads the bootstrap, validates input (including CSRF validation), and returns either a JSON response or a redirect.

---

## 4. Data Model (Database Tables)

| Table | Description |
|---|---|
| `admins` | Admin panel accounts. The `role` (`super_admin`/`admin`) and `is_active` columns were introduced in 1.1.0. |
| `customers` | Customer accounts identified by mobile number (introduced in 1.2.0). |
| `categories` | Product categories; `parent_id` supports one-level child categories (introduced in 1.2.0, self-referencing, nullable). |
| `products` | Products; includes `price`, `discount_price`, `stock` (overall stock when variants are not used), and `sku` with a `UNIQUE KEY` and automatic generation (introduced in 1.2.0). |
| `product_images` | Additional image gallery for each product. |
| `product_variants` | Product size/color variants with independent stock. |
| `coupons` | Discount codes (percentage-based or fixed amount). |
| `orders` | Orders; includes customer information, amounts, `status`, `payment_status` (introduced in 1.1.0), and optional `customer_id` (introduced in 1.2.0; guest orders remain supported). |
| `order_items` | Order line items containing a snapshot of product name/price at purchase time, independent of later product changes. |
| `cart_items` | Persistent cart items for authenticated users; every row stores a `locked_unit_price` (introduced in 1.2.0). |
| `settings` | Store-wide Key-Value settings; currently used for price guarantee configuration (introduced in 1.2.0). |
| `sms_log` | Log of all sent/recorded SMS messages (introduced in 1.1.0). |

### Why Do `order_items` Store Snapshots?

If a product's price or name changes later, historical orders must not change. Therefore, `product_name` and `unit_price` are stored directly in `order_items` rather than relying only on a link to `products`.

---

## 5. Critical Security and Business Logic

### 5.1 Cart (`app/core/cart.php`) — Rewritten in 1.2.0

Starting with 1.2.0, the cart has two completely separate storage paths, while the function interface (`cartAdd`, `cartUpdateQty`, `cartRemove`, `cartClear`, `cartCount`, `cartDetails`) remains identical for both. Controllers and views do not need to know the user's authentication state.

- **Guest user:** The cart is stored in `$_SESSION['cart']` as before, containing only `product_id`, `variant_id`, and `qty`. **Price and stock are never stored in the Session**; they are always read live from the database. Guest carts do not have price guarantees because there is no persistent identity to which a multi-day guarantee can be attached.
- **Authenticated user:** The cart is stored in the `cart_items` database table and persists across devices and sessions. Each row contains a `locked_unit_price` representing the price at the time the item was added. This mode also supports the **price guarantee** described in 5.1.1.
- If a product is deleted/disabled or does not have sufficient stock, it is automatically ignored in both modes. `cartDetails()` performs this validation at read time.
- **Guest cart merge after authentication:** when a guest who already has cart items signs in or registers, `mergeGuestCartIntoCustomerCart()` transfers the Session items into `cart_items` using a merge operation rather than replacement, then clears the Session cart.

#### 5.1.1 Cart Price Guarantee

- The cart's **start time** is `MIN(added_at)` across the user's *current* `cart_items`, i.e. the oldest item that currently remains in the cart. Because it is calculated dynamically from the current rows, completely emptying the cart and adding new items later automatically starts a new guarantee period without a dedicated reset column or operation.
- Configuration is stored through `app/core/settings.php` and the `settings` table and is manageable in `admin/settings.php`:
  - `price_guarantee_enabled`: enables/disables the feature.
  - `price_guarantee_days`: guarantee duration in days (default: 7).
- In `cartDetailsForCustomer()`, if the cart age (`now - cartStartedAt`) is less than `price_guarantee_days`, each item's `locked_unit_price` is used. Otherwise, the product/variant's live price replaces it. From that point onward, the cart continues using live prices until it is emptied and populated again; it never automatically returns to the locked price.
- **Important:** stock is always checked live from the database, even for items whose price is locked. The guarantee affects the price only, not stock or product active/inactive state.

### 5.1.2 Customer Authentication (`app/core/customer_auth.php`) — Introduced in 1.2.0

Customer login and registration use **mobile number + password**, not SMS OTP. The reason is that `SmsService` is Log-Only by default, so OTP authentication would not work out of the box. Security measures include `password_hash`/`password_verify`, `session_regenerate_id()` after login/registration, an artificial delay for failed attempts, and Open Redirect prevention for the `next` parameter (only internal paths beginning with `/` and not `//` are accepted).

### 5.2 Final Order Total Calculation (`app/controllers/site/checkout.php`)

When an order is created:

1. The cart is reloaded from the database from scratch rather than trusting submitted form values.
2. Stock is checked again for every item.
3. The coupon, if any, is validated again because it may have expired or reached its usage limit during the checkout interval.
4. `subtotal`, `discount_total`, and `total` are calculated server-side. No monetary amount is accepted directly from the user.
5. Order creation, inventory deduction, and coupon usage counter increment are executed inside a database **Transaction** (all succeed or all roll back).

### 5.3 Payment Gateway (`app/services/ZarinpalService.php`)

- Uses Zarinpal REST API v4.
- Project amounts are stored in **Toman**; the service automatically multiplies them by 10 when sending requests because the Zarinpal API expects Rials.
- `request()`: creates the payment request with Zarinpal and returns the payment URL.
- `verify()`: finalizes/verifies the transaction after the customer returns from the gateway.
- On any network error or invalid response, the service never throws an Exception that could crash the site. It always returns `['ok'=>false, 'error'=>...]`, which is handled by the controller.

**Complete online payment flow:**

```text
Checkout (POST) → Order created in DB (status=pending, payment_status=unpaid)
                 → ZarinpalService::request() is called
                 → Success: customer is redirected to Zarinpal
                 → Failure (network error, etc.): customer is sent to /order/failed/{code}
                                  with a "Retry Payment" action
                                  (the order is preserved; only payment remains incomplete)

Customer pays or cancels at the gateway
                 → Zarinpal redirects to payment/zarinpal_callback.php
                 → Status=NOK (cancelled): order payment_status=failed → failure page
                 → Status=OK: ZarinpalService::verify() is called
                         → Success: payment_status=paid, status=confirmed,
                                    confirmation SMS sent → success page
                         → Failure: payment_status=failed → failure page with retry
```

### 5.4 Coupons (`app/services/CouponService.php`)

Validation covers: enabled state, expiration date, usage limit (`max_uses`/`used_count`), and minimum order amount. A discount can never exceed the order amount (`min()` is used during calculation). The usage counter is incremented only after the order is successfully committed, not when the coupon is merely applied to the cart.

### 5.5 SMS (`app/services/SmsService.php`)

Designed with a **Fail-Safe by Default** strategy: while `SMS_ENABLED` is `false` or the API key is empty, no real SMS is sent, but the message is still recorded in `sms_log`. Missing SMS configuration therefore never breaks order creation or status changes.

### 5.6 Admin Authentication and Roles (`app/core/auth.php`)

- `requireAdmin()`: every admin panel page must call this guard.
- `requireSuperAdmin()`: the admin management page (`admin/users.php`) and order deletion operations (`admin/orders.php`, `admin/order_detail.php`, introduced in 1.2.0) use this guard.
- Any admin can have `is_active=0` without the account being deleted, allowing temporary suspension of access.
- The system must always retain at least one `super_admin`; this is enforced by the deletion/deactivation safeguards in `app/controllers/admin/users.php`.
- **Order deletion (introduced in 1.2.0):** only a `super_admin` can permanently delete an order. This restriction is enforced both in the UI (the button is rendered only for super admins) and, more importantly, server-side through `requireSuperAdmin()` in the controller. A regular admin therefore cannot bypass the restriction by manually submitting a POST request.

### 5.7 CSRF, XSS, SQL Injection

- Every POST form includes a `csrf_token` (`csrfField()`), which is checked by `verifyCsrf()`.
- All textual output is escaped through `e()` (a wrapper around `htmlspecialchars`).
- All queries use PDO Prepared Statements (`db()->prepare(...)`); raw user input is never concatenated directly into SQL.

---

## 6. Storefront Routing System (Framework-Free)

`.htaccess` sends every request that does not map to a physical file/directory to `index.php?route=<path>`. `index.php` splits the path with `explode('/', ...)` and requires the controller corresponding to the first segment (`home`, `category`, `product`, `cart`, `checkout`, `order`, `about`, `contact`, `terms`, `signup`, `login`, `logout`, `account` — the last four were introduced in 1.2.0). This is an intentionally simple `switch/case` router because the site contains a small and relatively stable number of pages.

**Important historical note:** in 1.0.0, a physical directory named `cart/` (used for AJAX endpoints) conflicted with the `/cart` storefront route and caused a Forbidden error. In 1.1.0, this directory was renamed to `ajax/` to eliminate the conflict. In general, no physical directory should share the name of a route defined in `index.php`.

### 6.1 Child Categories (Introduced in 1.2.0)

Categories support one level of nesting through `categories.parent_id`. A parent category page displays its child categories as clickable chips and includes products belonging to those child categories in the same listing. `getCategoryAndChildIds()` in `app/core/functions.php` returns the category ID plus its direct child IDs and is used in `WHERE category_id IN (...)`. The site's top navigation displays only top-level categories (`parent_id IS NULL`).

### 6.2 Automatic Unique SKU (Introduced in 1.2.0)

`generateUniqueSku()` in `app/core/functions.php` generates an SKU in the form `SOCK-XXXXXX` and guarantees uniqueness at the database level. If the SKU field is left empty in the product form, one is generated automatically. If a value is entered manually, uniqueness is validated against existing products. The `products.sku` column also has a real `UNIQUE KEY`, rather than relying only on application-level validation.

### 6.3 Products with Variants (`has_variants` Checkbox, Introduced in 1.2.0)

The product form includes a "Has Variants" checkbox. JavaScript disables the "Overall Stock" field and enables the variants section when it is checked. However, the final decision is always made **server-side** as defense in depth, independent of client-side JavaScript:

- If `has_variants` is not present in POST data, submitted variant rows are completely ignored.
- If `has_variants` is present, the product's `stock` column is always stored as `0`, because actual inventory is derived from the sum of variant stock rather than the product-level stock field.

---

## 7. Versioning and Change Documentation

Every technical change (bug fix, new feature, structural change) must:

1. Update the version number in `app/bootstrap.php` (`APP_VERSION`) using Semantic Versioning: `MAJOR.MINOR.PATCH`.
2. Add a new, precise entry to `docs/CHANGELOG.md` describing the changes relative to the previous version.
3. If the change requires an `ALTER TABLE`, add a new file under `database/migrations/`. Never modify the base `schema.sql` in a way that requires existing databases to be re-imported; use migrations instead.
4. Update this `ARCHITECTURE.md` document whenever the change affects the architecture or documented system behavior.
