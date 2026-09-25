# Contributing

This is a solo-maintained, live commercial project (see `LICENSE`) — this
guide exists mainly so future work on it, whether by the owner, a hired
developer, or an AI agent, stays consistent with itself. For technical
context, start with `AGENTS.md`.

## Before you start

Read `AGENTS.md`. It covers hosting constraints, critical rules (how money,
stock, and snapshots are handled), and the exact verification command
(`php -l`) available in this project. Skipping it is how the "delete and
recreate" variant-id bug happened the first time — see
`docs/ARCHITECTURE.md` §5.18.

## Versioning

Semantic Versioning (`MAJOR.MINOR.PATCH`), tracked in `APP_VERSION`
(`app/bootstrap.php`):

- **PATCH** — a bug fix with no schema change and no new user-facing
  behavior.
- **MINOR** — a new feature or a schema addition (this project has not yet
  had a breaking `MAJOR` change; additive migrations only, per `AGENTS.md`).
- **MAJOR** — reserved for a change that breaks an existing integration or
  removes something. Given this is a single-deployment store rather than a
  library with external consumers, this should be rare.

Every version, however small, gets:

1. A bumped `APP_VERSION`.
2. A new dated entry at the top of `docs/CHANGELOG.md` in English (keep ~5 recent versions; archive older in `docs/CHANGELOG-ARCHIVE.md`).
3. A migration file if the schema changed (`database/migrations/`), mirrored
   into `database/schema.sql`.
4. A `docs/architecture/<domain>.md` update if the change alters documented system
   architecture or domain design.

## Commit messages

First line: `vX.Y.Z: short imperative summary` (lowercase, no trailing
period), matching the version that commit ships. Body: a short bullet list
of what changed and, where it's not obvious, *why* — future readers
(including an AI agent starting a new session) benefit far more from the
reasoning than from a restatement of the diff.

```
v1.8.0: store accounting — order profitability, expense ledger, financial dashboard

- order_items.unit_cost_price: snapshots product/variant cost at the
  moment of sale (same variant-overrides-product precedence as
  price_override); populated once at checkout
- New AccountingService: getOrderProfitability() computes per-order
  revenue/cost/gross-profit from already-snapshotted data
- ...

Migration: 011_v1.8.0_accounting.sql
```

If a change doesn't warrant a version bump (a typo, a comment fix, a
documentation-only edit), skip the `vX.Y.Z:` prefix and just describe the
change plainly.

## Branches and PRs

A single maintainer working directly on `main` is the current reality, and
there's nothing wrong with that at this project's size. If a PR workflow is
ever introduced, a PR description should answer three questions the
commit-message format above already captures: what changed, why, and what
migration/version it corresponds to — a PR template
(`.github/PULL_REQUEST_TEMPLATE.md`) exists for exactly that.

## Code style

Match what's already there rather than introducing a new convention:

- 4-space indentation, PHP `snake_case` for database columns and functions,
  `camelCase` for local variables — consistent with the existing codebase.
- No framework, no Composer autoloading beyond the one vendored library
  (`PHPMailer`) — see `AGENTS.md` for why.
- CSS custom properties for anything color/spacing-related (`assets/css/style.css`
  `:root` block) — never a hardcoded hex value outside that block and the
  small set of deliberately brand-independent status colors in
  `assets/css/admin.css`.
- Persian for anything a customer or admin reads; English for code
  comments. See `AGENTS.md` for the reasoning.

## What "done" looks like

Before considering a change finished:

- `php -l` passes on every file touched (see `AGENTS.md` for the exact
  command).
- No stray Persian left in a code comment, and no English leaked into
  user-facing text.
- The secure-development checklist in `SECURITY.md` is satisfied for
  anything touching auth, money, uploads, or customer data.
- Docs and changelog are updated in the same change, not as a follow-up —
  a change that isn't documented is, for practical purposes, not finished.
