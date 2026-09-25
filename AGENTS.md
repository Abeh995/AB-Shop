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
- **Server stack: Nginx reverse proxy + Apache backend (PHP-FPM) under DirectAdmin.**
  - **Nginx proxy layer**: Nginx serves known static files directly from disk
    based on a server-level regex. Older DirectAdmin templates do *not* include
    `.webp` in that static regex, forwarding `.webp` requests to the Apache backend.
  - **Apache backend & `.htaccess` restrictions**: DirectAdmin configures Apache
    with a restricted `AllowOverride` (`AuthConfig FileInfo Indexes Limit Options=Indexes,...`).
    Directives like `Options -ExecCGI`, `php_flag`, `ForceType`, or `<IfModule>`
    nested inside `<FilesMatch>` in `.htaccess` are **strictly forbidden** by the server
    and cause Apache to immediately fail with **500 Internal Server Error** for the
    entire directory!
  - **`uploads/.htaccess` must remain strictly minimal**: Keep only standard script
    execution blocking (`<FilesMatch "\.(php|php[3-8]?|phtml|pl|py|cgi|asp|sh)$"> Require all denied </FilesMatch>`)
    and `Options -Indexes`. Never place MIME type overrides, `ForceType`, `Header set`,
    or `Options -ExecCGI` inside `uploads/.htaccess`.
  - **WebP image delivery via `/img.php` proxy**: Shared hosting server-level MIME
    tables often lack `image/webp`. Combined with the `X-Content-Type-Options: nosniff`
    security header, browsers refuse to display `.webp` files served as `application/octet-stream`.
    Therefore, `.webp` uploads are routed via root `.htaccess` to `/img.php?f=/uploads/$1`
    (must use a leading slash `/img.php` to prevent Apache from treating it as a filesystem
    path under PHP-FPM). `img.php` enforces `Content-Type: image/webp`, sends 1-year cache
    headers, and supports conditional GET (ETag / 304). Standard images (JPG, PNG, GIF, SVG)
    continue to be served directly by the web server.

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
7. **Anti-Bloat & Strict Layer Boundaries.** Keep code compact and extensible:
   - **Controllers**: Soft ceiling 80 lines, hard ceiling 120 lines. A controller
     must ONLY parse input, delegate to services, and hand data to a view.
     Never write raw SQL queries or business logic directly inside a controller.
   - **Views (`views/`)**: Pure presentation only. Exactly 0 SQL queries (`db()`),
     0 DB mutations, and 0 direct form processing (`$_POST`).
   - **Services (`app/services/`)**: All business logic, validations, calculations,
     and DB operations live here as plain global functions returning
     `['ok' => bool, 'error' => string]`.
   - **The Boy Scout Rule**: Never leave code more bloated than you found it.
     If a feature touches an existing long file, decompose it rather than appending.
8. **Atomic Releases & GitHub Integration.** Every code change (feature, bug fix, refactor)
   that alters application behavior MUST be shipped as an **Atomic Release**:
   - Bump `APP_VERSION` in `app/bootstrap.php` (semver).
   - Add a dated entry at the top of `docs/CHANGELOG.md` (English, single source of truth; keep recent ~5 releases, older in `docs/CHANGELOG-ARCHIVE.md`).
   - If domain architecture changed, update `docs/architecture/<domain>.md`.
   - Run verification quality gate: `php tools/verify.php`.
   - Commit with Conventional Commits format.
   - Create an annotated Git tag: `git tag -a vX.Y.Z -m "Release vX.Y.Z"`.
   - Push with tags: `git push origin main --follow-tags`.
   - Create the GitHub Release directly via GitHub CLI (`gh release create vX.Y.Z --title "Release vX.Y.Z" --notes-file ...` or inline notes).
   - **NEVER** leave untagged versions, unpushed tags, or missing GitHub releases.
9. **Quality Gate Verification before claiming Done.** Always run:
   ```
   php tools/verify.php
   ```
   Fix all syntax errors, controller bloat, view impurities, and version mismatches.

## Mandatory Commit & Release Workflow (WHEN USER SAYS "کامیت کن" OR "COMMIT")

Whenever the user instructs to **"commit"**, **"save changes"**, **"کامیت کن"**, or **"release"**, DO NOT just execute a bare `git commit`. Follow this exact sequence:

1. **Pre-flight Quality Gate**:
   - Run `php tools/verify.php` (or check `php -l` on all modified files).
   - If financial/stock logic was touched (`PricingService`, `GiftService`, `OrderService`, `cart.php`), re-verify concurrency row-locking (`SELECT ... FOR UPDATE` and conditional `WHERE stock >= ?`).
2. **Classify Change & Bump Version**:
   - `PATCH` (`1.16.x`): bug fixes, UI/UX polish, internal refactors.
   - `MINOR` (`1.x.0`): new features, database schema additions (`database/migrations/` + `schema.sql`).
   - *Skip bump only for pure documentation/typo fixes with zero code modifications.*
   - Bump `APP_VERSION` in `app/bootstrap.php`.
3. **Update Documentation**:
   - Prepend new entry to `docs/CHANGELOG.md` under `## X.Y.Z — YYYY-MM-DD` in English.
   - If system architecture or domain invariants changed, update the relevant `docs/architecture/<domain>.md` file in place.
4. **Git Commit with Conventional Commits**:
   ```bash
   git add <modified_files>
   git commit -m "type(scope): concise imperative title" -m "Release: vX.Y.Z`n`n- what changed and why`n- Migration: NNN_vX.Y.Z_... (if applicable)"
   ```
5. **Tag & Push**:
   ```bash
   git tag -a vX.Y.Z -m "Release vX.Y.Z"
   git push origin main --follow-tags
   ```
6. **Create GitHub Release via CLI**:
   ```bash
   gh release create vX.Y.Z --title "Release vX.Y.Z" --notes "### Release Summary for vX.Y.Z..."
   ```

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
3. **Never rewrite history in a way that breaks an existing variant/product
   id.** A recurring class of bug in this codebase has been "delete and
   recreate" logic that silently orphaned IDs other tables referenced (fixed
   in 1.5.0 for product variants — see `docs/architecture/financial-and-stock.md`). Prefer
   upsert-in-place. If you're about to write a `DELETE FROM x WHERE
   parent_id = ?` followed by re-inserting everything, stop and check
   whether anything else references those rows' ids first.

## Documentation hygiene

Documentation must remain compact, modular, and token-efficient for AI agents:

- **Changelog**: `docs/CHANGELOG.md` is unified in English. Keep full detail
  for roughly the last 5 versions. Releases older than that belong in
  `docs/CHANGELOG-ARCHIVE.md`. `tools/verify.php` guards against changelog
  bloat.
- **Architecture docs**: Modularized by domain under `docs/architecture/`.
  Architecture documents represent the *current state of the system*, not a
  historical journal. When code changes, update the relevant domain file in
  place rather than appending historical diaries.
- **No duplicate files**: Never maintain parallel full-text translations or
  duplicate deployment notes.

## Where things are

- `docs/ARCHITECTURE.md` & `docs/architecture/` — modular technical design by
  domain (overview, core, finance/stock, auth/security, theme/media).
- `docs/CHANGELOG.md` & `docs/CHANGELOG-ARCHIVE.md` — version history.
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
