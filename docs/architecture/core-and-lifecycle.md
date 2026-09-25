# Core Architecture & Request Lifecycle

This document describes the request lifecycle, lightweight routing, directory layout, and webserver integration for the AB-Socks application.

---

## 1. Directory Structure & Layer Responsibilities

```
/
├── index.php                  # Public front controller (routes public requests)
├── install.php                # Single-use web-based admin installer
├── img.php                    # Lightweight WebP image streaming proxy
├── .htaccess                  # Root rewrite rules, HTTPS enforcement, and security headers
│
├── config/                    # Application secrets (protected by .htaccess Deny)
│   └── config.php             # DB credentials, API keys, SMTP configuration (gitignored)
│
├── app/                       # Protected backend logic (protected by .htaccess Deny)
│   ├── bootstrap.php          # Central bootstrap loaded by every entry point
│   ├── core/                  # Shared infrastructure (db, auth, csrf, cart, settings, mailbox)
│   ├── services/              # Pure business logic and database transaction handlers
│   └── controllers/           # Thin input parsers and view presenters (site/ and admin/)
│
├── views/                     # Presentation templates (protected by .htaccess Deny)
│   ├── layout/                # Public header, footer, search bar, and meta tags
│   ├── site/                  # Storefront page templates
│   └── admin/                 # Admin panel views and layout templates
│
├── admin/                     # Public admin entry points (thin wrappers requiring bootstrap)
├── ajax/                      # Asynchronous AJAX endpoints (cart, checkout, admin search)
├── payment/                   # Payment callbacks and gateway redirect handlers
├── assets/                    # Static public assets (vanilla CSS, JS, favicons, fonts)
├── uploads/                   # Runtime user uploads (products, branding, receipts)
└── database/                  # Schema baseline (schema.sql) and sequential migrations
```

---

## 2. Request Lifecycle

### Public Storefront Request (e.g. `/product/men-cotton-socks`)
1. **Web Server Interception**: `.htaccess` intercepts the request. Unless the path matches a physical file in `assets/`, `ajax/`, `admin/`, or `uploads/`, the request is rewritten to `index.php?route=product/men-cotton-socks`.
2. **Bootstrap (`app/bootstrap.php`)**:
   - Sets secure session parameters (`session.cookie_httponly = 1`, `session.cookie_samesite = Lax`, `session.cookie_secure` when HTTPS).
   - Starts the session (`session_start()`).
   - Loads `config/config.php` and derives runtime constants (e.g. `BRANDING_UPLOAD_DIR`, `CARD_TO_CARD_UPLOAD_DIR`).
   - Loads core dependencies (`db.php`, `functions.php`, `csrf.php`, `settings.php`, `auth.php`, `customer_auth.php`, `cart.php`).
   - Registers service classes.
3. **Routing (`index.php`)**:
   - Parses the `route` query parameter into segments via `explode('/', ...)`.
   - Uses an explicit `switch/case` router to dispatch the request to the matching controller (e.g. `app/controllers/site/product.php`).
4. **Controller Execution**:
   - The controller validates GET/POST parameters.
   - Calls models/services to fetch data.
   - Dispatches data to the presentation layer via `renderView('site/product', [...])`.
5. **View Presentation**:
   - `renderView()` extracts the data array in a local scope and includes `views/site/product.php`.
   - The view includes `views/layout/header.php` and `views/layout/footer.php` to emit standard semantic HTML, Open Graph tags, JSON-LD structured data, and CSS token links.

### Admin Panel Request (e.g. `/admin/orders.php`)
1. Physical entry script `admin/orders.php` is accessed directly by Apache.
2. The script loads `app/bootstrap.php`, invokes `requireAdmin()` (or `requireSuperAdmin()`) to enforce session authentication and RBAC.
3. Includes the controller `app/controllers/admin/orders.php`.
4. The controller prepares orders and metrics, then calls `renderView('admin/orders', [...])`.

### AJAX Endpoints (e.g. `/ajax/cart_add.php`)
- Standalone PHP scripts located in `ajax/`.
- Directly require `app/bootstrap.php`, verify CSRF tokens where applicable, parse JSON or POST inputs, invoke appropriate services, and output clean JSON responses (`header('Content-Type: application/json')`).

---

## 3. Web Server & DirectAdmin Shared-Hosting Architecture

### Nginx + Apache Backend Setup
The production server uses DirectAdmin with **Nginx as a reverse proxy** handling static file delivery in front of **Apache (with PHP-FPM)** executing PHP scripts.

### `.htaccess` Invariant Rules
DirectAdmin configures Apache with strict `AllowOverride` limits. The following rules are mandatory:
- **Forbidden Directives**: Directives such as `Options -ExecCGI`, `php_flag`, `ForceType`, or nested `<IfModule>` blocks inside `<FilesMatch>` cause Apache to immediately fail with **500 Internal Server Error**.
- **`uploads/.htaccess` Policy**: Kept minimal with only script execution blocking (`<FilesMatch "\.(php|php[3-8]?|phtml|pl|py|cgi|asp|sh)$"> Require all denied </FilesMatch>`) and `Options -Indexes`.

### WebP Proxy Delivery via `/img.php`
- Shared hosting server MIME tables frequently lack the `image/webp` MIME definition. Combined with the `X-Content-Type-Options: nosniff` security header, modern browsers refuse to render WebP images served as `application/octet-stream`.
- **Solution**: The root `.htaccess` routes requests for `.webp` files in `uploads/` to `/img.php?f=/uploads/$1` (the leading slash is mandatory for PHP-FPM compatibility).
- **Behavior of `img.php`**: Validates file path against directory traversal (`realpath`), enforces `Content-Type: image/webp`, sends 1-year browser cache headers (`Cache-Control: public, max-age=31536000, immutable`), and handles conditional GET requests (`ETag` and `304 Not Modified`).
- Standard images (JPG, PNG, GIF, SVG) bypass PHP and are served directly by the web server at maximum speed.

---

## 4. Database Connection & Timezone Synchronization

The database layer is managed through a lightweight PDO singleton in `app/core/db.php`:
- **Singleton Connection**: `db()` returns a shared `PDO` instance configured with `ATTR_ERRMODE => ERRMODE_EXCEPTION` and `ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC`.
- **Dynamic Timezone Synchronization**: Shared hosts typically run MySQL in `UTC`, while PHP defaults to `Asia/Tehran` (`UTC+3:30`). Without synchronization, database timestamps (`CURRENT_TIMESTAMP`) differ by 3.5 hours from PHP's `time()`, causing rate limits and token expiry checks to fail.
- **Fix**: Upon PDO connection, `db()` calculates PHP's current timezone offset and immediately executes `SET time_zone = '+HH:MM'` on the MySQL session, ensuring complete synchronization.
