# AB-Socks System Architecture

Welcome to the technical architecture documentation for **AB-Socks**, a high-reliability Persian-language e-commerce platform built as a framework-free PHP application tailored specifically for constrained shared-hosting environments.

---

## 1. Architectural Philosophy & Non-Negotiable Invariants

The entire system is intentionally engineered with **zero external framework dependencies** (no Laravel, Symfony, Composer autoloading, or Node/npm build steps in production). This ensures bulletproof deployability via file upload and phpMyAdmin on standard shared hosting.

### The Five Invariants
1. **Money and Stock are Never Mutated Outside Services**: Controllers never issue raw `UPDATE` queries altering prices or inventory. All state changes flow through dedicated, auditable services (`PricingService`, `GiftService`, `OrderService`).
2. **Financial Data is Snapshotted, Never Live-Joined**: `order_items`, `order_gift_items`, and `price_history` capture the exact price, cost, and name at the moment of sale. Later modifications or deletions of products never alter historical financial audits.
3. **Transactional Integrity & Row-Level Concurrency**: All inventory deductions use conditional `UPDATE ... WHERE stock >= ?` and `SELECT ... FOR UPDATE` inside atomic database transactions to guarantee zero overselling.
4. **Strict Layer Boundaries**:
   - **Controllers**: Thin traffic directors (soft ceiling: 80 lines, hard ceiling: 120 lines). Zero raw SQL queries; delegates entirely to services and models.
   - **Views (`views/`)**: Pure presentation. Exactly zero database queries (`db()`) and zero direct `$_POST` form mutations.
   - **Services (`app/services/`)**: Pure business logic, validations, and database mutations returning structured status: `['ok' => bool, 'error' => string, ...]`.
5. **Defense-in-Depth Security**: 100% PDO prepared statements with bound parameters, HTML escaping via `e()` (`htmlspecialchars`), and strict CSRF verification on every state-changing form.

---

## 2. Production Hosting Constraints

The production environment is a shared-hosting plan powered by **DirectAdmin** with **Nginx (Reverse Proxy) + Apache Backend (PHP-FPM)** running PHP 8.x:

| Resource | Production Budget | Architectural Solution |
| :--- | :--- | :--- |
| **Disk Space** | 1.5 GB | Client-side image resizing and WebP conversion to keep uploads minimal. |
| **Database** | 200 MB | Clean relational schema, indexed foreign keys, optimized numeric fields. |
| **Bandwidth** | 80 GB / month | Aggressive client-side compression, 1-year browser caching, WebP format. |
| **CLI / SSH** | No guaranteed shell access | Manual web installer (`install.php`), sequential SQL migrations via phpMyAdmin. |
| **Server Rules** | Apache `AllowOverride` restrictions | Strict avoidance of forbidden directives (`Options -ExecCGI`, `ForceType`, `php_flag`). |

---

## 3. Architecture Domain Map

To maximize modularity and enable rapid AI-agent reasoning with minimal context token consumption, the architecture is decomposed into four self-contained domain modules:

```
docs/architecture/
├── README.md                  # This executive overview & invariant map
├── core-and-lifecycle.md      # MVC flow, routing, bootstrap, and webserver proxy
├── financial-and-stock.md     # Pricing, inventory concurrency, snapshotting, orders & accounting
├── auth-and-security.md       # Customer/admin authentication, verification, OTP & receipts
└── theme-and-media.md         # Canvas client-side optimizer, WebP delivery, and theme engine
```

### Detailed Domain Links
- **[Core & Request Lifecycle](./core-and-lifecycle.md)**: Routing without frameworks, request flow from `.htaccess` to view rendering, DirectAdmin Apache/Nginx compatibility, and database connection lifecycle.
- **[Finance, Inventory & Orders](./financial-and-stock.md)**: Snapshotting principles, row locking, `PricingService`, `GiftService`, `OrderService`, `AccountingService`, shipping calculation, and 7-day cart price guarantees.
- **[Authentication & Security](./auth-and-security.md)**: Phone-first customer accounts, `VerificationService`, FarazSMS pattern variables, WebOTP integration, private card-to-card receipt storage, and admin RBAC.
- **[Theme System & Media Pipeline](./theme-and-media.md)**: In-browser Canvas image optimizer, EXIF/GPS stripping, HEIC iPhone decoding, `/img.php` WebP proxy, and database-backed dynamic design tokens.
