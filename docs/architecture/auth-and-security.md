# Authentication & Security Architecture

This document outlines customer authentication, admin role-based access control, OTP verification, WebOTP integration, payment receipt security, and defense-in-depth measures.

---

## 1. Customer Authentication & Verification

Customer identity is built around a verified Iranian mobile phone number (`customers` table).

### Signup & Verification Flow
1. **Registration / Login**: The customer enters their mobile phone and password. Passwords are encrypted with `password_hash()` (Bcrypt).
2. **Verification Gate**: Newly registered accounts or customers lacking `phone_verified_at` are redirected to `/verify-phone`. An order cannot be finalized without a verified mobile number.
3. **`VerificationService` (`app/services/VerificationService.php`)**:
   - Generates a 6-digit numeric OTP.
   - Stores the code as a `sha256` hash in `verification_codes` (never in plaintext).
   - Enforces a 10-minute validity window.
   - Imposes a maximum of 5 invalid attempts before invalidating the code.
   - Applies a 60-second cooldown period between successive dispatch requests to mitigate abuse and avoid SMS API costs.

### Pattern-Based SMS & WebOTP Integration
- **FarazSMS Integration (`FarazSmsService.php`)**:
   - Sends pattern-based transactional SMS via API (Iran Payamak / FarazSMS).
   - Supports database-backed dynamic pattern definitions (`sms_patterns` table) with dynamic JSON variable configurations, falling back seamlessly to `config.php` constants.
- **WebOTP Browser API**:
   - View `views/site/verify_phone.php` uses `autocomplete="one-time-code"`, `inputmode="numeric"`, and `maxlength="6"` for cross-browser autocomplete.
   - In supporting browsers (Chrome/Android), executes `navigator.credentials.get({ otp: { transport: ["sms"] } })` to automatically read the incoming SMS and submit the verification form without manual user entry.
   - SMS pattern ends with the domain marker `@absocks.ir #<OTP>`.

---

## 2. Card-to-Card Receipt Security

Offline payments via Card-to-Card require strict isolation of customer financial receipts:

1. **Upload Validation (`CardToCardReceiptService.php`)**:
   - File uploads are verified using `finfo` for genuine image MIME types (`image/jpeg`, `image/png`, `image/webp`).
   - Image validity and dimensions are verified via `getimagesize()`.
   - File size is strictly capped at 2 MiB.
   - Filenames are randomized with cryptographically secure bytes (`bin2hex(random_bytes(16))`).
2. **Private Storage & Access Control**:
   - Receipts are stored outside the public document root in `uploads/card_to_card/`.
   - Direct HTTP access is blocked via `.htaccess` (`Require all denied`).
   - Admin review endpoint (`admin/order_receipt.php`) requires `requireAdmin()` and securely streams the image file via PHP (`readfile()`) with proper `Content-Type` headers.
3. **Session Binding**:
   - Uploaded receipts are tied to a temporary session token. Orders are only marked `payment_method = card_to_card` after successful upload.
   - Admin approval to `paid` status is strictly gated server-side: an order cannot be approved without a verified receipt on file.

---

## 3. Admin Access Control (RBAC)

Admin privileges are partitioned into two roles (`admins.role`):

- **`admin`**: Full operational access (managing products, updating order statuses, viewing finance dashboards, adjusting shipping methods). Protected by `requireAdmin()`.
- **`super_admin`**: Exclusive access to sensitive administrative actions (managing other admin users, viewing raw SMS/email diagnostic logs, permanently deleting orders). Protected by `requireSuperAdmin()`.
- **Safety Invariants**:
   - The system prevents an admin from deactivating or deleting their own account.
   - The system enforces that at least one active `super_admin` must always exist in the database.

---

## 4. Defense-in-Depth Core Standards

1. **SQL Injection Prevention**:
   - 100% of SQL queries run through PDO prepared statements (`$db->prepare(...)`) with bound parameters.
   - String concatenation of user input into SQL is forbidden.
2. **Cross-Site Scripting (XSS)**:
   - All dynamic output rendered into HTML is escaped using `e($val)` (wraps `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`).
   - Admin settings fields supporting rich text (legal pages, footer notes) enforce a restricted, safe Markdown format (`## heading`, `- list item`) with escaping; raw HTML is rejected except for the verified eNamad embed snippet.
3. **Cross-Site Request Forgery (CSRF)**:
   - Every state-changing form includes a hidden CSRF token generated via `csrfField()`.
   - Controllers call `verifyCsrf()` before processing POST data; mismatched tokens abort execution with an HTTP 403 status.
4. **Session Security**:
   - `session.use_strict_mode = 1`, `session.cookie_httponly = 1`, `session.cookie_samesite = 'Lax'`.
   - `session_regenerate_id(true)` is invoked immediately after successful authentication to eliminate session fixation.
