# Security Policy

This store handles real customer data (names, phone numbers, addresses) and
real payments. This document is both the security policy GitHub expects at
this path and the working checklist for hardening the project further —
written now so security work has a defined scope instead of being "do more
security" with no end state.

## Reporting a vulnerability

This is a small, independently-run store, not a project with a bug bounty
program. If you find a vulnerability, please report it privately rather
than opening a public issue: contact the repository owner directly through
GitHub. Please include enough detail to reproduce the issue and, if
possible, a suggested fix. Please don't test against the live production
site beyond what's needed to demonstrate the issue — use a local copy
(`README-DEPLOY.md` has setup steps) wherever practical.

## What's already in place

Everything below is implemented today, not aspirational:

- **SQL injection**: every query is a PDO prepared statement
  (`db()->prepare(...)`). No user input is ever concatenated into SQL.
- **XSS**: all dynamic output goes through `e()` (`htmlspecialchars`)
  before reaching HTML.
- **CSRF**: every state-changing form includes a token (`csrfField()`),
  verified server-side (`verifyCsrf()`) before the action runs.
- **Passwords**: hashed with `password_hash()` (bcrypt), never stored or
  logged in plain text, for both admin and customer accounts.
- **Session security**: `session.use_strict_mode`, `httponly`, `SameSite=Lax`,
  `secure` when served over HTTPS, and session ID regeneration on login
  (session fixation protection).
- **File uploads**: real MIME-type detection via `finfo` (not the
  client-supplied `Content-Type` or file extension), a size cap, and
  randomly generated filenames. `uploads/` has a `.htaccess` disabling PHP
  execution, so an uploaded file can never be requested as a script even if
  upload validation were somehow bypassed.
- **Role-based access**: two admin roles (`admin`, `super_admin`); every
  admin-only page calls `requireAdmin()`, and operations that can destroy
  data (deleting an order, managing admin accounts) additionally call
  `requireSuperAdmin()`.
- **Verification codes** (phone/email): stored as a `sha256` hash, not
  plaintext; expire after 10 minutes; capped at 5 attempts; rate-limited to
  one send per 60 seconds.
- **Brute-force friction**: a deliberate `usleep()` delay on failed admin
  and customer login attempts. This is friction, not real rate limiting —
  see the roadmap below.
- **Financial auditability**: every price/cost change, gift assignment, and
  order snapshot records who did it and when (`docs/ARCHITECTURE.md`
  §5.17–§5.21) — a real security property, since it makes tampering or
  errors after the fact detectable.

## Hardening roadmap

Prioritized by impact. None of this is implemented yet — treat this as the
next security work to pick up, not a description of the current state.

### High priority

1. **HTTP security headers.** Nothing currently sets
   `Content-Security-Policy`, `X-Content-Type-Options: nosniff`,
   `X-Frame-Options: DENY` (or `frame-ancestors 'none'`),
   `Referrer-Policy: strict-origin-when-cross-origin`, or
   `Strict-Transport-Security` (once HTTPS is confirmed to always be
   available — check before adding HSTS, since it's difficult to safely
   undo). Add these in `.htaccess`, since there's no central place in the
   PHP that runs on every request. A CSP is the most involved of these:
   start in **report-only** mode, since this project loads a Google Fonts
   stylesheet (Vazirmatn) and will load whatever the design system in
   `docs/DESIGN.md` ends up needing (SVG filters are same-origin and fine;
   an external 3D/model library, if ever added, is not).
2. **Real rate limiting on authentication.** The current `usleep()` delay
   does not stop a distributed or patient attacker. At minimum, track failed
   attempts per phone number/username and per IP (a small table or the
   existing `sms_log`-style pattern works fine at this scale — no need for
   Redis) and lock out or escalate delay after a threshold.
3. **Admin session timeout.** Confirm admin sessions expire after a period
   of inactivity, not just on browser close. If they don't, add one — an
   admin session is far more sensitive than a customer one (it can change
   prices, gift inventory, and view every customer's data).
4. **Dependency freshness.** `app/vendor/PHPMailer` is vendored by hand,
   which means it never updates itself. Check its version against current
   PHPMailer security advisories periodically — this is exactly the kind of
   thing that's easy to forget on a project with no `composer.json` to flag
   it.

### Medium priority

5. **Two-factor authentication for admin accounts**, at least optionally.
   Given the financial data now reachable from the admin panel (§5.17–§5.21
   in the architecture doc), a compromised admin password is a serious
   incident, not just an inconvenience.
6. **IDOR review on customer-facing order/account pages.** `account_order.php`
   and similar pages should be re-checked to confirm every query scopes by
   the logged-in customer's own id, not just the order code — a sequential
   or guessable identifier anywhere in a URL is worth a second look.
7. **Structured audit log for admin actions**, beyond what's already
   captured in `price_history`/`order_gift_items`. Login attempts (success
   and failure), admin account changes, and settings changes are worth a
   simple `admin_audit_log` table if this store's data ever needs to satisfy
   an external audit.
8. **Backup verification.** The host offers daily/weekly backups
   (`README-DEPLOY.md`); confirm restores actually work, not just that
   backups exist. An untested backup is not a backup.

### Lower priority / situational

9. **CAPTCHA or similar on public forms** (signup, contact, login) if abuse
   is ever observed — not worth adding preemptively without evidence of a
   problem, since it adds friction and a third-party dependency.
10. **Incident response plan.** Even a short one — who to notify, how to
    rotate `APP_SECRET`/DB credentials, how to force-logout all sessions —
    written down before it's needed rather than during an incident.

## Card-to-card payment receipt security

Card-to-card receipts are treated as private financial/customer documents. The browser upload endpoint requires an authenticated customer session and CSRF token, validates the real MIME type with `finfo`, checks the image with `getimagesize()`, limits the file to 2 MiB, generates a random filename, and stores temporary files outside the public product-image URL path. Permanent receipt storage is denied by a directory `.htaccess`; the admin review endpoint requires `requireAdmin()` and streams only a receipt filename already stored on an order.

The receipt token is stored only in the customer's PHP session while the checkout is pending, so another customer cannot use a guessed token to attach a file to an order. A successful card-to-card order stores the final receipt filename as an order snapshot. Payment approval is also protected server-side: an admin cannot mark a card-to-card order as `paid` unless a receipt exists.

## Public storefront content

Business contact details and editable public-page copy are stored in the `settings` database table and managed through the admin settings page. These values are operational/public content, not application secrets.

Migration `013_v1.9.1_db_site_content.sql` seeds the current initial values only when the corresponding setting is missing, so an existing production value is preserved. No private content seed file is required or included in the repository.

The public content editor uses a limited text format and escapes all rendered content, so an administrator cannot accidentally turn a legal-page textarea into arbitrary HTML. The existing eNamad embed remains the only intentional raw admin-controlled HTML field.

## Secure-development checklist for new code

Anyone (human or agent) adding a feature to this project should be able to
answer yes to all of these before calling it done:

- [ ] Every new query uses a prepared statement with bound parameters.
- [ ] Every new piece of dynamic output is escaped with `e()` before
      reaching HTML (or is deliberately raw and admin-only-controlled — see
      the eNamad embed code in settings for the one existing precedent, and
      don't create a second one without equally good reason).
- [ ] Every new state-changing form has a CSRF token and server-side check.
- [ ] Every new admin page starts with `requireAdmin()` (or
      `requireSuperAdmin()` if it can affect other admins or destroy data).
- [ ] Every new customer-facing query that looks up "my" data filters by
      the logged-in customer's id — never by a value taken from the request
      alone (order code, product id, etc.) without that check.
- [ ] Any new file upload validates real MIME type via `finfo`, caps size,
      and writes to a directory that disables script execution.
- [ ] Any new money/stock write goes through the relevant service
      (`PricingService`, `GiftService`) rather than a direct `UPDATE`, per
      `AGENTS.md`.
