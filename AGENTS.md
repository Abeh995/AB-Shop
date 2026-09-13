# AGENTS.md

Instructions for AI coding agents (Claude Code, Cursor, Codex, or any other
agent) working on this repository. Humans are welcome to read it too, but it
is written for an agent starting a task with no prior context.

This file is intentionally short. It covers what an agent cannot infer by
reading the code — hosting constraints, non-obvious business rules, and
process. For everything else, read the code and the docs linked below rather
than expecting it restated here.

**Read `docs/ARCHITECTURE.md` (or `.en.md`) before touching billing, pricing,
inventory, orders, or gifts.** It documents *why* the current design looks
the way it does, including several bugs that were found and fixed the hard
way. Repeating one of them is a real risk, not a hypothetical one.

## What this project is

A PHP e-commerce store (socks), framework-free, live and taking real orders.
Full technical map: `docs/ARCHITECTURE.md` / `docs/ARCHITECTURE.en.md`
(Persian is canonical; English is a translation — see below).

## Hosting constraints — non-negotiable

The production host is shared PHP hosting, not a VPS. This shapes almost
every technical decision in this repo:

- **PHP 8.x only.** No CLI access assumed for deploy steps beyond phpMyAdmin's
  SQL tab. See `README-DEPLOY.md` for the exact deploy path.
- **No Composer, no npm, no build step in production.** `app/vendor/` is
  vendored by hand (see `PHPMailer`). Any new dependency must either be
  vendored the same way or avoided. Do not introduce a `package.json`,
  webpack/vite config, or anything assuming a build step runs on the server.
- **1.5 GB disk, 200 MB database, 80 GB/month bandwidth.** Keep this in mind
  for anything image- or asset-heavy — this is directly relevant to the
  current UI redesign (see `docs/DESIGN.md`).
- **No shell access implied for the site owner.** Every migration is a
  `.sql` file applied by hand through phpMyAdmin, in numeric order. See
  "Database changes" below.

## Critical rules

These are rules the codebase already follows consistently. Breaking one of
them silently is worse than asking first.

1. **Money is never written outside a service.** `PricingService.php`
   is the only code allowed to write `products.cost_price` / `.price` or
   `product_variants` equivalents — every write there also produces a
   `price_history` row. The same pattern applies to `GiftService.php`
   (stock + `order_gift_items`) and `AccountingService.php` (read-only
   reporting, writes nothing). If you're changing a price, cost, or stock
   number, find the existing service function first; do not `UPDATE` the
   table directly from a controller.
2. **Financial and inventory data is snapshotted, never live-joined.**
   `order_items`, `order_gift_items`, and `price_history` store the name,
   price, and cost *as they were at that moment* — not a foreign key you
   resolve later against a row that may have since changed or been deleted.
   A later price change or product deletion must never alter what an
   existing order says it charged or cost. When adding a new financial
   field, snapshot it the same way.
3. **Stock changes are transactional and row-locked.** Any code that
   decrements stock does `SELECT ... FOR UPDATE` (or a conditional
   `UPDATE ... WHERE stock >= ?`) inside a transaction. Two customers
   buying the last unit at the same moment must not both succeed.
4. **CSRF on every state-changing form**, via `csrfField()` /
   `verifyCsrf()`. Every `SELECT`/`INSERT`/`UPDATE`/`DELETE` is a PDO
   prepared statement — never string-concatenate user input into SQL.
   Every value echoed into HTML goes through `e()`. See `SECURITY.md` for
   the full list and what's still open.
5. **Never touch `config/config.php`.** It holds live production
   credentials and is gitignored. New configuration constants are derived
   in `app/bootstrap.php` from existing constants where at all possible
   (see how `BRANDING_UPLOAD_DIR` is derived from `UPLOAD_DIR`) specifically
   so an already-deployed site's `config.php` never needs manual editing.
6. **Comments in English, UI text in Persian.** Every PHP/JS/CSS/SQL
   comment in this repo is in English (a deliberate cleanup — see the
   1.3.0 changelog entry). User-facing strings (labels, page content, admin
   panel text, error messages shown to the store owner or customer) stay in
   Persian. Don't translate UI text; don't leave new comments in Persian.

## Making a change

1. **Check `php -l` on every file you touch.** There is no test suite —
   this is the only automated check available. Run it before considering a
   change done:
   ```
   find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \;
   ```
   Fix anything that isn't `No syntax errors detected`.
2. **Every schema change gets a new migration file**, never an edit to an
   already-numbered one. Name it `NNN_vX.Y.Z_short-description.sql` in
   `database/migrations/`, and mirror the same change into `schema.sql` (the
   fresh-install baseline) in the same commit. Migrations must be safe to
   re-run (`CREATE TABLE IF NOT EXISTS`, `INSERT IGNORE`, guarded
   `ALTER TABLE`) and must never silently drop or truncate data.
3. **Bump `APP_VERSION` in `app/bootstrap.php`** using semver
   (MAJOR.MINOR.PATCH) for anything beyond a trivial typo fix.
4. **Update both changelogs and both architecture docs** — `docs/CHANGELOG.md`
   and `.en.md`, and `docs/ARCHITECTURE.md` and `.en.md` if the change alters
   documented behavior. Persian is canonical; keep the English version a
   faithful translation, not a shorter summary. See "Documentation hygiene"
   below before adding yet another entry to an already-large file.
5. **Never rewrite history in a way that breaks an existing variant/product
   id.** A recurring class of bug in this codebase has been "delete and
   recreate" logic that silently orphaned IDs other tables referenced (fixed
   in 1.5.0 for product variants — see `docs/ARCHITECTURE.md` §5.18). Prefer
   upsert-in-place. If you're about to write a `DELETE FROM x WHERE
   parent_id = ?` followed by re-inserting everything, stop and check
   whether anything else references those rows' ids first.

## Documentation hygiene

`docs/CHANGELOG.md` and `docs/ARCHITECTURE.md` (and their `.en.md` pairs)
have grown large over many versions and now cost meaningful context to read
in full. Handle this actively, don't just keep appending forever:

- **Changelog**: keep full detail for roughly the last 5 versions. When it
  grows past that, fold older entries into a short one-paragraph summary per
  version (or per version range) rather than deleting them — move the full
  original text to `docs/CHANGELOG-ARCHIVE.md` if it's worth keeping
  verbatim, referenced from the top of `CHANGELOG.md`. Do this in one
  consolidation pass across several old versions at once, not on every
  release.
- **Architecture doc**: organized by system/domain, not strictly
  chronological — that's intentional, keep it that way. If a numbered
  section (5.x, 6.x, ...) describes something that a later change fully
  superseded, rewrite that section to describe the current design instead of
  appending a newer section that contradicts it. If the whole file grows
  past a size where loading it costs more than it's worth for a typical
  task, split a self-contained domain (e.g. accounting, or the eventual
  design system) into its own `docs/<domain>.md` and leave a short pointer
  behind, rather than letting one file grow indefinitely.
- Prefer editing a section down when its content is superseded over leaving
  the old version in place "for history" — that's what git history and the
  changelog are for.

## Where things are

- `docs/ARCHITECTURE.md` / `.en.md` — how the system works and why,
  organized by domain, with a §8 "Known Extension Points" listing what the
  design deliberately leaves room for.
- `docs/DESIGN.md` — visual design system and the current UI/UX redesign
  direction (read before writing any CSS/HTML for the redesign effort).
- `docs/SEO.md` — SEO standards and what's implemented vs. planned.
- `docs/AI_SKILLS.md` — recommended Agent Skills for this project's stack.
- `SECURITY.md` — security posture, what's covered, what's open.
- `CONTRIBUTING.md` — commit conventions, branch/PR expectations.
- `README-DEPLOY.md` — gitignored, deployment runbook for the human owner.
  Not part of what ships to GitHub; don't reference it from public docs.

## Verifying a change actually works

There's no automated test suite and no staging environment — the owner
deploys by uploading changed files to a live shared host. Because of that:

- Prefer changes that are easy to reason about over clever ones. A bug here
  reaches paying customers directly.
- Any change touching money, stock, or orders should have its transaction
  and locking behavior re-read once after writing it, specifically for the
  concurrent-request case (two customers, one admin + one customer, etc.).
- When you can't run the code, say so plainly rather than asserting it
  works. `php -l` catches syntax errors, nothing else.
