# AB-Socks Architecture Reference

This is the central technical architecture index for the **AB-Socks** platform.
The architecture is structured modularly across four core domain documents.

---

## Non-Negotiable Invariants

1. **Service Encapsulation for State Mutations**: Money and stock numbers are never written outside dedicated services (`PricingService`, `GiftService`, `OrderService`).
2. **Immutable Snapshots**: Order lines, gift allocations, and price history snapshot names, prices, and unit costs at the instant of transaction. Later product modifications never alter past financial history.
3. **Transactional Integrity & Concurrency**: Inventory deductions execute inside database transactions using `SELECT ... FOR UPDATE` and conditional `UPDATE ... WHERE stock >= ?` statements.
4. **Strict Layer Boundaries**: Controllers remain thin (< 80-120 lines) with zero raw SQL queries. Views are pure presentation with zero database calls (`db()`) or direct `$_POST` mutations.
5. **Shared Hosting Compliance**: Strict avoidance of Apache directives forbidden under DirectAdmin (`Options -ExecCGI`, `ForceType`, `php_flag`), with `.webp` routing through the `/img.php` proxy.

---

## Domain Architecture Modules

Detailed system designs, diagrams, and domain explanations are organized into focused modules in [`docs/architecture/`](./architecture/):

| Module | Core Responsibilities Covered |
| :--- | :--- |
| **[Executive Overview](./architecture/README.md)** | Architectural philosophy, production hosting budgets, and system-wide design rules. |
| **[Core & Request Lifecycle](./architecture/core-and-lifecycle.md)** | Manual MVC pattern, request flow, routing table, webserver proxy (`img.php`), and database connection. |
| **[Finance, Stock & Orders](./architecture/financial-and-stock.md)** | Snapshotting principles, row locking, `PricingService`, `GiftService`, `OrderService`, `AccountingService`, shipping calculation, and 7-day cart price guarantees. |
| **[Authentication & Security](./architecture/auth-and-security.md)** | Mobile-first customer identity, OTP hashing, WebOTP API, FarazSMS pattern variables, private payment receipts, and admin RBAC. |
| **[Themes & Media Pipeline](./architecture/theme-and-media.md)** | Browser HTML5 Canvas image optimization, EXIF/GPS stripping, HEIC iPhone decoding, WebP proxy, and database-driven theme engine. |

---

## Cross-Cutting Guidelines

- **Visual Design**: See [`docs/DESIGN.md`](./DESIGN.md) for glassmorphism rules, performance constraints, and UI tokens.
- **Search Engine Optimization**: See [`docs/SEO.md`](./SEO.md) for structured data, metadata standards, and indexing hygiene.
- **Security Roadmap**: See [`SECURITY.md`](../SECURITY.md) for vulnerability reporting and security checklists.
- **Release History**: See [`docs/CHANGELOG.md`](./CHANGELOG.md) for recent versions and [`docs/CHANGELOG-ARCHIVE.md`](./CHANGELOG-ARCHIVE.md) for historical releases.
