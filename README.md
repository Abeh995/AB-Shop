<div align="center">

# AB Socks

A live, production Persian-language e-commerce store — built and maintained
as a framework-free PHP application, deliberately engineered for a
constrained, low-cost shared-hosting environment rather than a VPS or
cloud platform.

</div>

---

## What this is

AB Socks sells socks online, in Persian, to customers mostly browsing on
mobile. The code here runs it — this is not a demo or a template. It's
included in the repository as a portfolio piece and technical reference,
with the real business logic that a small but genuinely operating store
needs: accounts, cart, coupons, an online payment gateway, an SMS/email
verification flow, and — beyond the storefront basics — a full internal
system for pricing history, gift/post-order items, shipping cost rules,
and store accounting. See `docs/ARCHITECTURE.md` for the complete map.

## Why framework-free PHP

The production host is a shared hosting plan: PHP 8.x, no shell access
beyond a file manager and phpMyAdmin, 1.5 GB disk, a 200 MB database, and
80 GB/month of bandwidth. No Composer, no build step, no queue worker, no
Redis. Every architectural decision in this codebase — how migrations are
applied, how uploads are validated, how a background job would even be
possible — is downstream of that constraint, not of unfamiliarity with the
alternatives. `AGENTS.md` spells this out in more detail for anyone (human
or AI) about to make a change here.

## What's built

- **Storefront**: categories, product variants (size/color), search, tags,
  coupons, a persistent cart with a price-guarantee window for logged-in
  customers, phone/email verification, Zarinpal payment integration.
- **Admin panel**: full product/category/order management, bulk pricing
  with a preview-then-confirm flow, an admin-managed multi-theme system,
  configurable shipping rules, a gift/post-order catalog, and a financial
  dashboard with order-level profitability.
- **Data integrity by design**: every price change, gift assignment, and
  order line is snapshotted at the moment it happens — a later change to a
  product's price or cost never rewrites the financial record of an order
  that already shipped. This shows up throughout `docs/ARCHITECTURE.md`
  §5 as a repeated, deliberate pattern, not a one-off feature.
- **SEO and security**, actively being hardened — see `docs/SEO.md` and
  `SECURITY.md` for current status and the open roadmap.
- **A UI/UX redesign in progress** toward a glass/depth-based visual
  language — see `docs/DESIGN.md` for the direction and the performance
  constraints it's being built within.

## Tech stack

PHP 8.x, MySQL/MariaDB (PDO, prepared statements throughout), vanilla
JS/CSS with no build step, PHPMailer (vendored, not via Composer), Zarinpal
for payments. No framework — routing, templating, and the service layer are
all hand-rolled and documented in `docs/ARCHITECTURE.md`.

## Local development

```bash
git clone https://github.com/Abeh995/AB-Shop.git
cd AB-Shop
```

1. Create a MySQL database and import `database/schema.sql`.
2. Copy `config/config.example.php` to `config/config.php` and fill in your
   own database credentials and secrets (this file is gitignored — never
   commit real credentials).
3. Point a PHP 8.x server at the project root (e.g.
   `php -S localhost:8000`) and visit `/install.php` once to create the
   first admin account.
4. If you add or change a table, add a new file under
   `database/migrations/` rather than editing `schema.sql`'s existing
   statements — see `AGENTS.md`.

## Working on this project with AI agents

This repository is set up for AI-agent-assisted development:
`AGENTS.md` is the entry point (works with Claude Code, Cursor, Codex, and
any other Agent-Skills/AGENTS.md-compatible tool); `CLAUDE.md` points to it
for tools that look for that filename specifically. `docs/AI_SKILLS.md`
lists recommended Agent Skills for this stack.

## Documentation

| File | Covers |
|---|---|
| `AGENTS.md` | Start here for any code change — constraints, critical rules, process |
| `docs/ARCHITECTURE.md` / `.en.md` | Full technical design, by domain, including why several past bugs happened |
| `docs/DESIGN.md` | Visual design system and the current UI/UX redesign direction |
| `docs/SEO.md` | SEO standards, what's implemented, what's planned |
| `SECURITY.md` | Security posture and hardening roadmap |
| `CONTRIBUTING.md` | Commit conventions, versioning, code style |
| `docs/CHANGELOG.md` / `.en.md` | Full version history |

Persian versions of bilingual docs are canonical; English versions are
translations for wider readability.

## License

Source-available for portfolio and reference purposes — not open source.
See [`LICENSE`](./LICENSE) for exact terms before reusing any part of this
code.
