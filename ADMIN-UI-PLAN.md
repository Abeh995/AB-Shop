# ADMIN-UI-PLAN — Unified Design System & Intrinsic Responsiveness for the AB Socks Admin Panel

Target repo: `github.com/Abeh995/AB-Shop` · Baseline version: `1.32.3` · Scope: **admin panel only** (`views/admin/**`, `assets/css/admin*.css`, admin JS). Storefront is out of scope.

This file is an execution plan for an AI coding agent. Work through it phase by phase. Do not skip ahead. Do not improvise architecture; if something here is wrong or impossible, stop and report instead of silently deviating.

---

## 0. Agent operating protocol

1. Read first, in this order: `AGENTS.md`, `docs/COMPONENTS.md`, `docs/DESIGN.md`, this file. All `AGENTS.md` rules stay in force (hosting constraints, no build step in production, English comments / Persian UI text, anti-bloat, atomic releases, `php tools/verify.php` gate).
2. **One phase (or one batch inside a phase) per session.** Finish it, run the gate, report, stop. Never start the next phase unless the owner says so.
3. **Presentation-only changes.** Do not alter business logic, SQL, services, routes, form field names, or POST handling. Controllers may change only to pass presentation variables (e.g. `$pageTone`).
4. Never hand-edit generated output: `deploy/`, `dist/`. Never touch `config/config.php`.
5. You cannot run a browser. State that plainly. Verification you can do: `php -l`, `php tools/verify.php`, grep-based audits. Visual/responsive verification is done by the owner with the Playwright script from Phase 0 — tell the owner exactly which command to run and which pages to look at.
6. Every release follows `AGENTS.md` rule 8 (bump `APP_VERSION`, prepend `docs/CHANGELOG.md`, update `docs/COMPONENTS.md` / `docs/DESIGN.md` / `docs/architecture/*` in place, Conventional Commit message). The owner's delivery format: zip with **only changed files** + commit message in chat.
7. Persian UI strings are never translated or reworded. New comments are English.
8. If a task would increase any ratchet metric in `tools/verify-baseline.json`, stop. Metrics may only go down.

---

## 1. Measured baseline (do not trust memory; re-measure in Phase 0)

| Area | Finding |
|---|---|
| Admin CSS volume | ~17k lines across 16 files (`admin.css` 3220, `admin-orders.css` 3105, `admin-finance.css` 1607, `admin-gift-items.css` 1478, `admin-products.css` 1407, ...) |
| Palettes | 15 independent `:root` blocks. Orders = warm brown (`--brand-primary`), Finance = blue (`--fin-primary`), SMS = indigo (`--sms-primary`), Products = `--prod-*`, etc. |
| Raw hex colors | ~1280 occurrences (`admin.css` 319, `admin-orders.css` 178, `admin-products.css` 115, `admin-pricing.css` 101, `admin-finance.css` 77) |
| KPI cards | `ab-kpi-card` exists, but categories/products/finance/etc. keep private `cat-kpi-*`, `prod-kpi-*`, `fin-kpi-*` (11 files) |
| Tables | 26 `<table>`, 15 different class names (`admin-table` ×12, `diag-log-table`, `od-table`, `fin-data-table`, `usr-table`, `prod-table`, ...). Table CSS repeated in 12 files |
| Buttons | `btn` + ~40 other families (`action-btn`, `fin-btn-outline`, `btn-dash-action`, `act-btn`, `chip-btn`, `tool-btn`, ...) |
| Breakpoints | ~50 distinct `max-width` values; 0 container queries; 0 `clamp()`. `admin-gift-items.css`, `admin-pricing.css`, `admin-settings.css` have no `@media` at all |
| Container width | 4 different values (1280 / 1480 / 1560 / 1680) |
| Inline `style=""` in admin views | 1365 occurrences (worst: `finance_dashboard.php` 102, `product_edit.php` 101, `order_detail.php` 89, `inventory_valuation.php` 86, `expenses.php` 70, `expense_edit.php` 70, `orders.php` 63) |
| `!important` | `admin-finance.css` 137, `admin-orders.css` 111, `admin.css` 19, others few |
| Motion | `transition: all` in ~100 places, 6+ different durations |
| JS breakpoints | `admin.js` hard-codes 768 and 900 independently of CSS |
| Hidden coupling | Admin loads storefront `style.css`; admin views rely on `.btn`, `.form-group`, `.form-control`, `.alert*` defined **there** |
| Existing good base | `admin-components.css` (`ab-kpi`, `ab-badge`, `ab-modal`, `ab-nav-tabs`, `ab-empty`, `ab-toast`, `ab-autocomplete`), `component()` helper (`app/core/functions.php:701`), 5 PHP components, `ab-kit.js`, ratchet in `tools/verify.php` section 7, bottom nav on mobile, `nav_config.php` domain groups |

Root cause: the system exists but pages were redesigned without going through it. The fix is a stricter, more complete system plus migrating pages onto it.

---

## 2. Locked decisions

| # | Decision |
|---|---|
| D1 | Pure CSS, no build step, no external library, no framework. Vendored/hand-written only. |
| D2 | Modern browsers only (container queries, `@layer`, `color-mix()`, `dvh`, `:has()`, `<dialog>` are allowed). No fallbacks. |
| D3 | Visual base = existing `admin.css` mocha/latte palette. Finance's blue is preserved as a **tone**, not as a separate palette. |
| D4 | Pages never write `@media` for layout. Responsiveness belongs to the shell (3 breakpoints) and to components (container queries). |
| D5 | Structure, shape, shadow, motion, spacing, typography are identical everywhere. **Color identity varies per domain via one attribute `data-tone`.** Semantic state colors (success/warning/danger/info) never depend on tone. |
| D6 | Admin tokens are independent of storefront `theme_tokens`, but built so a future "admin theme settings" feature only needs to override tokens (Section 9). |
| D7 | Dark mode is out of scope, but do not preclude it (semantic tokens only). |
| D8 | Admin is used on mobile for quick tasks: **order review, card-to-card receipt verification, product upload/edit, dashboard glance.** These four are "mobile-critical" and have stricter acceptance criteria. |
| D9 | Verification = live UI Kit page + new `verify.php` rules + local Playwright audit script. |

---

## 3. Target architecture

### 3.1 File map

```
assets/css/
  admin-tokens.css      # layer order + primitives + semantic tokens + tones + density/motion. ONLY file allowed to contain :root and hex.
  admin-base.css        # reset + element defaults (replaces dependency on storefront style.css)
  admin-shell.css       # sidebar, topbar, off-canvas, bottom nav, .ab-page container, flash area
  admin-components.css  # ab-* components (v2). Existing file, rewritten in place.
  admin-patterns.css    # page archetypes: list / dashboard / form / split
  admin-utilities.css   # ab-stack, ab-cluster, ab-grid, text/visibility helpers
  admin-<workstation>.css  # ONLY for true workstations (orders, c2c, emails, appearance, pricing...). Tokens only, <=150 lines each target.
  admin.css             # legacy; shrinks per migrated page; deleted in Phase 6
views/admin/components/   # PHP presentation components (pure; 0 SQL, 0 $_POST)
views/admin/ui-kit.php + admin/ui-kit.php   # live style guide
tools/ui-test/            # dev-only Playwright audit (never deployed)
```

Load order in `views/admin/layout/header.php`: `admin-tokens` → `admin-base` → `admin-shell` → `admin-components` → `admin-patterns` → `admin-utilities` → (legacy `admin.css` while it exists) → page CSS.

### 3.2 Cascade layers

First line of `admin-tokens.css`:

```css
@layer reset, tokens, base, layout, components, patterns, pages, utilities;
```

Rules:
- New files wrap their content in the matching `@layer`.
- **Unlayered CSS beats all layered CSS.** Legacy `admin.css` and page files are unlayered during migration, so legacy keeps winning. This is intentional: Phases 0–2 must not change any existing page's look.
- **Do NOT wrap legacy files in `@layer legacy`.** `!important` order inverts across layers and would break `admin-finance.css` / `admin-orders.css` (248 `!important`).
- When a page is migrated, delete its legacy rules (and its `!important`s). At the end (Phase 6) leftover workstation CSS is wrapped in `@layer pages`.
- New `ab-*` class names must be unique; never reuse a legacy class name for new behavior, except the temporary aliases listed in 3.9.

### 3.3 Tokens (write exactly this structure; values are the starting point)

```css
@layer tokens {
  :root {
    color-scheme: light;

    /* ===== Primitives — the only place raw hex is allowed ===== */
    --c-mocha-500: #C46C46;  --c-mocha-700: #8C472E;  --c-latte-400: #B89180;
    --c-blue-500:  #3B82F6;  --c-sky-500:   #0EA5E9;  --c-emerald-500: #10B981;
    --c-amber-500: #F59E0B;  --c-rose-500:  #EF4444;  --c-purple-500: #8B5CF6;
    --c-teal-600:  #0D9488;  --c-slate-600: #475569;
    --c-sand-100:  #F3F1EC;  --c-neutral-0: #FFFFFF;  --c-neutral-50: #F8FAFC;
    --c-ink-900:   #1E293B;  --c-ink-500:   #64748B;  --c-line-200:   #E2E8F0;  --c-line-100: #F1F5F9;
    --c-shadow-rgb: 28 22 18;

    /* ===== Semantic surfaces & text ===== */
    --bg: var(--c-sand-100);
    --surface: var(--c-neutral-0);
    --surface-2: var(--c-neutral-50);
    --text: var(--c-ink-900);
    --text-muted: var(--c-ink-500);
    --border: var(--c-line-200);
    --border-subtle: var(--c-line-100);
    --focus-ring: 0 0 0 3px color-mix(in srgb, var(--tone) 35%, transparent);

    /* ===== Semantic states (independent of tone) ===== */
    --success: var(--c-emerald-500);  --warning: var(--c-amber-500);
    --danger:  var(--c-rose-500);     --info:    var(--c-blue-500);
    /* For each state S: derive -soft (12% on surface), -strong (78% + black), -ink (55% + black) with color-mix(), as in tones below. */

    /* ===== Shape / elevation ===== */
    --radius-scale: 1;
    --radius-sm: calc(6px * var(--radius-scale));
    --radius-md: calc(10px * var(--radius-scale));
    --radius-lg: calc(14px * var(--radius-scale));
    --radius-xl: calc(20px * var(--radius-scale));
    --radius-pill: 9999px;
    --shadow-1: 0 1px 2px rgb(var(--c-shadow-rgb) / .06);
    --shadow-2: 0 4px 12px rgb(var(--c-shadow-rgb) / .08);
    --shadow-3: 0 10px 25px -5px rgb(var(--c-shadow-rgb) / .12);
    --shadow-4: 0 20px 40px -8px rgb(var(--c-shadow-rgb) / .18);

    /* ===== Spacing, type ===== */
    --space-1: .25rem; --space-2: .5rem; --space-3: .75rem; --space-4: 1rem;
    --space-5: 1.5rem; --space-6: 2rem;  --space-7: 3rem;   --space-8: 4rem;
    --font-sans: 'Vazirmatn', system-ui, -apple-system, 'Segoe UI', sans-serif;
    --text-xs: clamp(.72rem, .70rem + .1vw, .78rem);
    --text-sm: clamp(.80rem, .78rem + .1vw, .88rem);
    --text-md: clamp(.90rem, .88rem + .1vw, .97rem);
    --text-lg: clamp(1.05rem, 1rem + .3vw, 1.25rem);
    --text-xl: clamp(1.25rem, 1.1rem + .8vw, 1.75rem);

    /* ===== Density & touch ===== */
    --control-h: 2.5rem;
    --control-h-sm: 2rem;

    /* ===== Motion ===== */
    --dur-1: .12s; --dur-2: .2s; --dur-3: .32s;
    --ease: cubic-bezier(.2, .7, .2, 1);

    /* ===== Layers / layout ===== */
    --z-sticky: 20; --z-nav: 40; --z-overlay: 60; --z-toast: 80;
    --page-max: 1600px;
    --page-gutter: clamp(.75rem, 2.5vw, 2rem);
    --sidebar-w: 16rem; --sidebar-w-collapsed: 4.5rem;
  }

  @media (pointer: coarse) {
    :root { --control-h: 2.75rem; --control-h-sm: 2.75rem; }   /* >= 44px touch targets */
  }
  @media (prefers-reduced-motion: reduce) {
    :root { --dur-1: 0s; --dur-2: 0s; --dur-3: 0s; }
  }
  [data-density="compact"] { --control-h: 2.25rem; --control-h-sm: 1.75rem; }
}
```

Breakpoint constants (cannot live in custom properties; document in `admin-tokens.css` header comment, mirror in `AB.bp` in `ab-kit.js`, and enforce equality in `verify.php`):

| Name | Value | Used for |
|---|---|---|
| `sm` | `40rem` (640px) | shell only: bottom nav density, page gutter |
| `md` | `64rem` (1024px) | shell only: sidebar becomes off-canvas, bottom nav appears |
| `lg` | `90rem` (1440px) | shell only: optional wider gutters |

Use range syntax: `@media (width < 64rem)`. These are the **only** media queries allowed outside `admin-shell.css` and `@media print` / `(hover)` / `(pointer)` / `(prefers-*)`.

### 3.4 Tone system (the "alive and colorful" requirement + future theming)

A tone is a set of five derived variables set by `data-tone` on `.ab-page` (or `<body>`):

```css
@layer tokens {
  [data-tone="brand"]   { --tone: var(--c-mocha-500);   --tone-on: #fff; }
  [data-tone="blue"]    { --tone: var(--c-blue-500);    --tone-on: #fff; }
  [data-tone="sky"]     { --tone: var(--c-sky-500);     --tone-on: #fff; }
  [data-tone="emerald"] { --tone: var(--c-emerald-500); --tone-on: #fff; }
  [data-tone="amber"]   { --tone: var(--c-amber-500);   --tone-on: var(--c-ink-900); }
  [data-tone="rose"]    { --tone: var(--c-rose-500);    --tone-on: #fff; }
  [data-tone="purple"]  { --tone: var(--c-purple-500);  --tone-on: #fff; }
  [data-tone="teal"]    { --tone: var(--c-teal-600);    --tone-on: #fff; }
  [data-tone="slate"]   { --tone: var(--c-slate-600);   --tone-on: #fff; }

  [data-tone] {
    --tone-strong: color-mix(in srgb, var(--tone) 78%, black);
    --tone-ink:    color-mix(in srgb, var(--tone) 55%, black);
    --tone-soft:   color-mix(in srgb, var(--tone) 12%, var(--surface));
    --tone-border: color-mix(in srgb, var(--tone) 30%, var(--border));
  }
  :root { --tone: var(--c-mocha-500); --tone-on: #fff; /* default = brand; also define the 4 derived vars here */ }
}
```

(`#fff` literals above are allowed because they are inside `admin-tokens.css`; if you prefer, add `--c-white`.)

What follows `--tone`: primary button, active tab, links, focus ring, KPI accent, page-header icon chip, table header accent line, selected row. What never follows tone: success/warning/danger/info, badges with `data-state`, text colors, surfaces.

Default domain → tone map (one source of truth: add `'tone'` key to each group in `views/admin/layout/nav_config.php`; `header.php` derives `$pageTone` from the active group, pages may override with `$pageTone` before including the header):

| Domain group | Tone |
|---|---|
| dashboard | `brand` |
| orders | `brand` |
| products (catalog, pricing, coupons, gifts) | `purple` |
| finance | `blue` *(owner explicitly likes the blue of the Expenses page)* |
| settings (general, appearance, shipping, SMS, email) | `teal` |
| users | `slate` |
| diagnostics | `amber` |

Existing KPI `color` prop values map: `primary→brand`, `emerald`, `rose`, `sky`, `amber`, `purple`, `blue` → same-named tones. Keep `color` as a deprecated alias of new `tone` prop.

### 3.5 Responsive model (the core of requirement #2)

Principle: **pages never decide breakpoints. Components decide based on their own container width; the shell decides based on the viewport.**

1. **Shell (viewport media queries, 3 total constants).** `>= md`: sidebar visible (collapsible rail). `< md`: sidebar off-canvas, `admin-bottom-nav` visible, `.ab-page` gutters shrink. JS (`admin.js` lines ~249/295/330/339/357) must read `AB.bp.md` instead of 768/900 literals.
2. **Components (container queries).** `container-type: inline-size` on: `.ab-card`, `.ab-table-wrap`, `.ab-split` panes, `.ab-form`. Named containers (`container: ab-card / inline-size`). Component layout switches via `@container`. A table inside a narrow drawer behaves exactly like on a phone. Browser zoom changes CSS-px widths, so the same mechanism handles zoom with no extra code.
3. **Intrinsic grids.** KPI/cards: `grid-template-columns: repeat(auto-fit, minmax(min(100%, 15rem), 1fr))`. Toolbars: `flex-wrap`. Forms: `repeat(auto-fit, minmax(min(100%, 18rem), 1fr))`. **No fixed `px` column tracks anywhere.**
4. **Fluid scale.** `rem` + `clamp()` tokens only. One `--page-max` for all pages. Logical properties everywhere (`margin-inline-start`, `padding-block`, `inset-inline-end`, `text-align: start`) — this is an RTL app; never use `left/right` except for intentionally physical things (e.g. progress bars, charts) with a comment.
5. **Heights.** Never `100vh`; use `100dvh` and `env(safe-area-inset-*)`. Header `<meta viewport>` must add `viewport-fit=cover`.
6. **Touch vs hover.** Hover styles only inside `@media (hover: hover)`. Minimum 44×44px targets via `--control-h` on `(pointer: coarse)`. Inputs use `font-size: max(16px, 1rem)` so iOS Safari doesn't zoom on focus.
7. **Overlays.** Modals and drawers are native `<dialog>` opened with `showModal()` (top layer). Narrow viewport: modal → bottom sheet; drawer → full-screen. **Never** `position: fixed` overlays inside a container-query element (see pitfall P1).
8. **Tables** (biggest mobile problem), two modes selected by the component, not the page:
   - `data-mode="stack"` (entity lists): below `40rem` container width each row becomes a card; cells show `data-label` as a caption via `::before { content: attr(data-label) }`.
   - `data-mode="scroll"` (numeric/financial): horizontal scroll inside `.ab-table-wrap`, first column sticky (`inset-inline-start: 0`).
   - Column `priority`: `1` always visible, `2` hidden below `40rem`, `3` hidden below `56rem` (container widths). Hidden columns remain reachable in the row drawer/detail link.
9. **Max content discipline.** Long Persian text and long unbroken tokens: `overflow-wrap: anywhere` on table cells, cards, and badges; `min-width: 0` on flex/grid children.

### 3.6 Component catalog (v2) — CSS class + PHP component, one contract each

All CSS uses tokens only. All PHP components live in `views/admin/components/<name>.php`, are called via `component('<name>', $props)`, and escape output with `e()` unless a prop is explicitly documented as pre-escaped HTML.

| Component | CSS | PHP props (essentials) |
|---|---|---|
| Page | `.ab-page[data-tone]` | wrapper opened/closed by layout header/footer, not by views |
| Page header | `.ab-page-header` | `title`, `subtitle`, `icon` (SVG), `actions` (array of button prop sets), `breadcrumbs` |
| Card | `.ab-card` (+ `__head`, `__body`, `__foot`) | `title`, `subtitle`, `actions`, `body_class`, `flush` (no padding, for tables) — body supplied by output buffering or `$content` |
| KPI | `.ab-kpi-grid`, `.ab-kpi` | existing `kpi_card`/`kpi_grid`, add `tone`, `trend`(`up|down|flat`), `href` |
| Button | `.ab-btn` + `--primary` (default) `--secondary` `--outline` `--ghost` `--danger` `--success`; sizes `--sm`, `--lg`; `.ab-icon-btn`; `[aria-busy=true]` spinner | `button` component: `label`, `variant`, `size`, `icon`, `href`, `type`, `attrs` |
| Field | `.ab-field`, `.ab-input`, `.ab-select`, `.ab-textarea`, `.ab-switch`, `.ab-check`, `.ab-form-grid`, `.ab-input-group` (prefix/suffix unit) | `form_field`: `name`, `label`, `type`, `value`, `options`, `hint`, `error`, `required`, `unit`, `dir` (`ltr` for numbers/email/phone), `attrs` |
| Table | `.ab-table-wrap`, `.ab-table` | `data_table`: see 3.7 |
| Toolbar | `.ab-toolbar` (search, filters, bulk actions, view toggles) | `toolbar`: `search` (name/placeholder/value), `filters` (array), `actions`, `bulk` |
| Pagination | `.ab-pagination` | `pagination`: `page`, `pages`, `base_url`, `total` |
| Tabs | `.ab-tabs` | existing `nav_tabs` (keep API), restyle only |
| Badge / chip | `.ab-badge[data-state]`, `.ab-chip[data-tone]` | `badge`: `label`, `state`/`tone`, `dot` |
| Alert / flash | `.ab-alert[data-state]` | `alert`: `state`, `title`, `message`, `dismissible`; replaces `.alert*` and the flash block |
| Empty state | `.ab-empty` | existing `empty_state` |
| Modal | `.ab-modal` on `<dialog>` | `modal`: `id`, `title`, `size` (`sm|md|lg`), `footer_actions`; keep `data-ab-modal-open/close` API in `ab-kit.js` |
| Drawer | `.ab-drawer` on `<dialog>` | `drawer`: `id`, `title`, `side` (`start|end`) |
| Split view | `.ab-split` (master/detail; collapses to single pane + back button at narrow container) | CSS + `data-ab-split` JS (used by c2c, emails) |
| Definition list | `.ab-dl` | key/value rows used on detail pages |
| Save bar | `.ab-savebar` (sticky bottom, safe-area aware) | `savebar`: `primary`, `secondary`, `dirty_hint` |
| Toast | existing `.ab-toast` | unchanged |
| Progress / meter | `.ab-progress` | `value`, `max`, `tone` |
| Utilities | `.ab-stack`, `.ab-cluster`, `.ab-grid`, `.ab-sr-only`, `.ab-nowrap`, `.ab-num` (tabular numerals, `dir=ltr` safe) | — |

### 3.7 `data_table` contract

```php
component('data_table', [
  'id'      => 'usersTable',
  'mode'    => 'stack',              // 'stack' | 'scroll'
  'columns' => [
    ['key' => 'name',   'label' => 'نام',   'priority' => 1],
    ['key' => 'role',   'label' => 'نقش',   'priority' => 2, 'type' => 'badge'],
    ['key' => 'last',   'label' => 'آخرین ورود', 'priority' => 3, 'type' => 'date'],
    ['key' => 'amount', 'label' => 'مبلغ',  'priority' => 1, 'type' => 'price', 'align' => 'end'],
    ['key' => '_',      'label' => '',      'priority' => 1, 'type' => 'actions'],
  ],
  'rows'      => $rows,              // array of assoc arrays prepared by the controller/service
  'row_attrs' => fn($r) => ['data-id' => $r['id'], 'data-search' => $r['search']],
  'cell'      => ['actions' => fn($r) => /* pre-escaped HTML built from component('button') output */ ''],
  'empty'     => ['title' => 'موردی یافت نشد'],
  'sticky_head' => true,
  'selectable'  => false,
]);
```

- Supported `type`: `text` (default), `number`, `price` (uses existing formatter), `date`, `badge`, `link`, `actions`, `html` (caller-escaped), custom via `cell[key]` callable.
- The component emits `data-label` on every `<td>` and `data-priority` on `<th>`/`<td>` automatically.
- Views must stay pure (no SQL). Row shaping happens in the controller/service as today.
- Live filtering keeps using `data-ab-filter-target` + `data-search` (already documented in `COMPONENTS.md` §1.5).

CSS skeleton to implement (adapt, do not copy blindly):

```css
@layer components {
  .ab-table-wrap { container: ab-table / inline-size; overflow-x: auto; background: var(--surface);
                   border: 1px solid var(--border); border-radius: var(--radius-lg); }
  .ab-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: var(--text-sm); }
  .ab-table :is(th, td) { padding: var(--space-3) var(--space-4); text-align: start; overflow-wrap: anywhere; }
  .ab-table th { position: sticky; inset-block-start: 0; background: var(--surface-2);
                 border-block-end: 2px solid var(--tone-border); }
  @container ab-table (width < 56rem) { .ab-table [data-priority="3"] { display: none; } }
  @container ab-table (width < 40rem) {
    .ab-table [data-priority="2"] { display: none; }
    .ab-table-wrap[data-mode="stack"] :is(.ab-table, tbody, tr, td) { display: block; }
    .ab-table-wrap[data-mode="stack"] thead { position: absolute; inline-size: 1px; block-size: 1px; overflow: hidden; }
    .ab-table-wrap[data-mode="stack"] td::before { content: attr(data-label); display: block; color: var(--text-muted); font-size: var(--text-xs); }
  }
  @media (hover: hover) { .ab-table tbody tr:hover { background: var(--tone-soft); } }
}
```

### 3.8 Page archetypes (`admin-patterns.css`)

| Archetype | Composition | Pages |
|---|---|---|
| **List** | page_header → kpi_grid? → card(toolbar + data_table + pagination) | products, categories, tags, coupons, shipping_methods, sms_patterns, gift_items, expenses, themes, users, notifications_log, email_accounts |
| **Dashboard** | page_header → kpi_grid → `.ab-grid` of cards (charts/lists) | dashboard (index), finance_dashboard, inventory_valuation |
| **Form/Edit** | page_header → `.ab-form` with cards of `.ab-form-grid` → `.ab-savebar` | product_edit, expense_edit, gift_item_edit, shipping_method_edit, sms_pattern_edit, theme_edit, settings, appearance (tabbed) |
| **Workstation** | `.ab-split` / custom, own CSS (tokens only) | orders, order_detail, card_to_card_payments, emails/email_read/email_compose, pricing, diagnostics |

Each archetype is implemented as a PHP partial/component sequence plus a small pattern CSS block. If a page needs more than ~150 lines of its own CSS, it is a workstation; justify it in the changelog.

### 3.9 Temporary backward-compat aliases (removed in Phase 6)

Because admin currently depends on storefront `style.css` for `.btn`, `.form-group`, `.form-control`, `.alert*`:
- In `admin-components.css` define the new component and also match the legacy class: `:is(.ab-btn, .btn)`, `:is(.ab-field, .form-group)`, `:is(.ab-input, .form-control)`, `:is(.ab-alert, .alert)`. Mark with `/* LEGACY ALIAS — remove in Phase 6 */`.
- Legacy custom properties (`--bg-app`, `--bg-card`, `--border-card`, `--text-primary`, `--brand-primary`, `--fin-*`, `--prod-*`, `--sms-*`, ...): generate the complete list with grep, add to `admin-tokens.css` as an alias block mapping each to a new token (`*-primary` → `var(--tone)`), under `/* LEGACY ALIASES — delete in Phase 6 */`. This lets per-page `:root` blocks be deleted early without breaking untouched rules.

### 3.10 JS rules

- `AB.bp = { sm: 640, md: 1024, lg: 1440 }` in `ab-kit.js`; replace all literals in `admin.js`.
- Dialog-based modal/drawer API stays `data-ab-modal-open`, `data-ab-modal-close`, `AB.modal.open(id)`; internally uses `showModal()`.
- No new inline `<script>` (>20 lines remains forbidden). No new helper that duplicates `AB.fmt`, `AB.toast`, `AB.api`.
- `prefers-reduced-motion`: JS-driven animations must check `matchMedia`.

---

## 4. Phase plan (checklists)

Suggested versions are guidance; follow the `PATCH`/`MINOR` rule from `AGENTS.md`.

### Phase 0 — Baseline & safety net (tooling only; PATCH `1.32.4`)

- [ ] Amend `AGENTS.md`: add explicit exception — `tools/ui-test/` is a **dev-only** Node project (own `package.json`), never deployed; production still has no build step. Confirm `tools/build-deploy.ps1` allowlist excludes `tools/`.
- [ ] `.gitignore`: add `tools/ui-test/node_modules/`, `tools/ui-test/output/`, `tools/ui-test/.auth/`.
- [ ] Create `tools/ui-test/` (spec in Section 6): `package.json`, `audit.mjs`, `pages.json`, `README.md`.
- [ ] `pages.json`: every admin route from `nav_config.php` plus edit pages (`product_edit.php?id=…`, `expense_edit.php`, `gift_item_edit.php`, `shipping_method_edit.php`, `sms_pattern_edit.php`, `theme_edit.php`, `order_detail.php?id=…`, `email_read.php`, `email_compose.php`). IDs supplied via env/`pages.local.json` (gitignored), not hard-coded.
- [ ] Add a `tools/ui-test/baseline/README.md` explaining: owner runs the audit **before Phase 1** and commits `baseline-report.md` (counts only, not screenshots) so before/after is measurable.
- [ ] Record current metrics in `docs/DESIGN.md` §"Admin UI migration status" table (CSS lines, hex count, inline styles, distinct breakpoints, pages migrated 0/32).
- [ ] Gate: `php tools/verify.php` passes unchanged.

**Owner action after Phase 0:** `cd tools/ui-test && npm i && npx playwright install chromium && AB_BASE_URL=... AB_ADMIN_USER=... AB_ADMIN_PASS=... node audit.mjs` → keep the report as "before".

### Phase 1 — Tokens, base, shell (MINOR `1.33.0`, **no visible change**)

- [ ] Create `admin-tokens.css` per 3.3/3.4 (layer order first line, primitives, semantic, tones, density, motion, legacy alias block).
- [ ] Create `admin-base.css` (`@layer reset, base`): box-sizing, form control font inheritance, `font-size: max(16px,1rem)` for inputs, headings, links, focus-visible ring, `[hidden]`, reduced motion, tabular numerals helper. Replace what admin takes from storefront `style.css`.
- [ ] Audit what admin needs from `style.css` (`body` font, `a`, headings, `.btn`, `.form-group`, `.form-control`, `.alert*`, `textarea.form-control`). Implement equivalents in the new files with the 3.9 aliases. **Then stop loading `style.css` in `views/admin/layout/header.php`** (and `views/admin/login.php` if it loads it separately). Verify with grep that no other admin class relies on it.
- [ ] Extract shell rules (sidebar, collapsed rail, off-canvas, bottom nav, flash area, topbar) out of `admin.css` into `admin-shell.css`; unify breakpoint at `md` (replace 768/900). Remove the `body:not(.admin-page-dashboard):not(.admin-page-orders) .admin-main > :not(.dash-topbar)` hack and the `admin-page-*` body classes once `.ab-page` wrapper exists.
- [ ] `header.php`: add `viewport-fit=cover`; emit `data-tone` from nav group (`$pageTone`); load new files in the order from 3.1; remove inline `style="display:none;"` on `.nav-flyout-header`.
- [ ] `nav_config.php`: add `'tone'` per group (3.4 table).
- [ ] `admin-utilities.css` (`ab-stack`, `ab-cluster`, `ab-grid`, `ab-sr-only`, `ab-nowrap`, `ab-num`).
- [ ] `ab-kit.js`: add `AB.bp`; `admin.js`: replace literals.
- [ ] `tools/verify.php` (see Section 7): add rules but baseline them at current values so the gate stays green.
- [ ] Acceptance: every existing page renders as before (owner runs Playwright; screenshot diffs only in sidebar/topbar spacing tolerated). No page uses new classes yet.

### Phase 2 — Component library v2 + UI Kit (MINOR `1.34.0`)

For **each** component in 3.6, one checklist item = CSS (tokens only) + PHP component (if listed) + UI Kit section + `COMPONENTS.md` entry:

- [ ] button / icon-button (all variants, sizes, loading, disabled, focus, tone)
- [ ] field family (input, select, textarea, switch, check, input-group, form-grid, error/hint states)
- [ ] card, page_header, toolbar, pagination
- [ ] data_table (both modes, priority columns, sticky head, empty state, selectable optional)
- [ ] kpi (restyle existing to tone/tokens; keep old props working)
- [ ] badge/chip, alert (+ flash block in layout), empty_state restyle
- [ ] modal + drawer on `<dialog>` (bottom sheet / fullscreen at narrow container is **viewport**-based for overlays: use `@media (width < 40rem)` allowed here because overlays are not inside containers) — keep `data-ab-modal-*` API working for both old `div.admin-modal` markup and new `<dialog>` until Phase 6
- [ ] split view (`data-ab-split` JS ≤ 60 lines in `ab-kit.js`)
- [ ] savebar, definition list, progress
- [ ] Print block: shared `@media print` rules (hide shell, no shadows) so finance/orders print CSS can shrink later
- [ ] `admin/ui-kit.php` + `views/admin/ui-kit.php`: renders every component in every state — default, hover-less, disabled, loading, error, empty, long Persian text, long unbroken token, 1 vs 12 columns, 0 vs 200 rows, each tone, each state. Linked only from Diagnostics (not in main nav). Auth required like other admin pages.
- [ ] Update `docs/COMPONENTS.md` (new sections), `docs/DESIGN.md` (admin system, tones, breakpoints, layers).
- [ ] Acceptance: UI Kit passes the Playwright audit at all viewports with zero overflow; tap-target check clean on mobile viewports.

### Phase 3 — Pilot migration (PATCH `1.34.x`)

Pilot page: **`users.php`** (0 inline styles, has KPIs + toolbar + table + modals + audit trail). Then `coupons.php` as second confirmation.

- [ ] Apply the **Page Migration Recipe** (Section 5) to `users`.
- [ ] Delete `admin-users.css` entirely and its `$pageCssMap` entry.
- [ ] Record before/after in `docs/DESIGN.md` migration table (CSS lines removed, hex removed, inline styles removed).
- [ ] **STOP. Owner reviews on desktop + phone + zoom.** Collect feedback; fix the system (components), not the page.
- [ ] Repeat for `coupons`/`tags`.

### Phase 4 — Batch migration (one batch per session; PATCH each)

Page inventory (views in `views/admin/`). Columns: archetype · current page CSS · inline-style count · notes. **MC = mobile-critical.**

**4a — Lists**
| Page | Arch. | CSS file | Inline | Notes |
|---|---|---|---|---|
| products | List | admin-products | 39 | drawer (16 rules) → `ab-drawer`; variants shown inline |
| categories | List+tree | admin-categories | 27 | has `admin-categories.js` (327 lines); keep behavior, replace markup/classes |
| tags | List | admin-coupons | 28 | |
| coupons | List | admin-coupons | 8 | |
| shipping_methods | List | admin-shipping | 0 | `_studio` partial is a form → `ab-form` |
| sms_patterns | List | admin-sms | 0 | |
| gift_items | List | admin-gift-items | 0 | |
| expenses | List | admin-finance | 70 | owner's favorite look: keep tone `blue`, keep its card feel as the reference for `ab-card`/`ab-kpi` visuals |
| themes, notifications_log, email_accounts | List | various | 9/7/0 | |

**4b — Dashboards**
| Page | CSS | Inline | Notes |
|---|---|---|---|
| dashboard (index) | admin.css (dash-*) | 20 | **MC** (glance). Topbar/search stays in shell |
| finance_dashboard | admin-finance | 102 | has SVG chart built in PHP; create `chart_card` wrapper (responsive `viewBox`, no fixed px); keep print P&L (move to shared print block) |
| inventory_valuation | admin-finance | 86 | |

**4c — Forms**
| Page | CSS | Inline | Notes |
|---|---|---|---|
| product_edit | admin-products | 101 | **MC (upload).** Variant rows → stacked cards at narrow container; image uploader touch-friendly; sticky `ab-savebar`; keep `admin-product-edit.js` + image optimizer contracts |
| expense_edit | admin-finance | 70 | |
| gift_item_edit, shipping_method_edit, sms_pattern_edit (+ simulator), theme_edit | various | 29/4/17/18 | |
| settings (7 tabs) | admin-settings | 0 | tabbed via `nav_tabs`; each tab = cards of `ab-form-grid` |
| appearance (6 tabs) | admin-appearance | 0 | |

**4d — Workstations (own CSS allowed, tokens only, ≤150 lines target each)**
| Page | CSS | Inline | Notes |
|---|---|---|---|
| orders | admin-orders (3105 lines, 178 hex, 111 `!important`) | 63 | **MC.** Side dossier drawer → `ab-drawer`; list → `data_table` stack mode; biggest CSS reduction |
| order_detail | admin-orders | 89 | **MC.** `ab-dl`, gallery lightbox keeps JS contract |
| card_to_card_payments | admin-orders | 40 | **MC.** `ab-split`; receipt zoom/rotate stays; keyboard shortcuts stay; on mobile: list → detail pane with back button, large Approve/Reject buttons in sticky bar |
| emails, email_read, email_compose, email_accounts | admin-emails | 5 | `ab-split` |
| pricing | admin-pricing (101 hex, no media queries) | 47 | two-phase preview/confirm UI stays; tables → `data_table` scroll mode |
| diagnostics (+5 tab partials) | admin-diagnostics | 0 | log tables → `data_table` scroll mode |
| login | admin | 0 | uses `ab-field`, `ab-btn`; centered card |

Per-batch acceptance: Section 8 Definition of Done for every page in the batch.

### Phase 5 — Visual regression baselines (PATCH)

- [ ] After all pages are migrated, owner runs audit with `--update-snapshots`; commit **only** the `.png` snapshots of: UI Kit (all viewports) + 4 MC pages (mobile) — not all pages (repo size / 200MB DB irrelevant, but keep repo light).
- [ ] Add `npm run ui:audit` and `npm run ui:snapshots` scripts; document in `tools/ui-test/README.md` and `README`/`CONTRIBUTING.md`.
- [ ] Optional: add `@axe-core/playwright` color-contrast check for every tone × `--tone-on` pair on the UI Kit page.

### Phase 6 — Cleanup (PATCH or MINOR `1.35.0`)

- [ ] Remove legacy aliases (3.9), `.btn`/`.form-*`/`.alert` selectors, old `div.admin-modal` support.
- [ ] Delete `admin.css` (if empty) and any empty page CSS; shrink `$pageCssMap` to workstations only.
- [ ] Wrap remaining workstation CSS in `@layer pages`.
- [ ] Set all admin ratchet baselines to **0** for `css_root`, `css_hex`, inline styles, page `@media`.
- [ ] Update `docs/COMPONENTS.md`, `docs/DESIGN.md`, `docs/ARCHITECTURE.md` (+ `docs/architecture/*`), `AGENTS.md` rule 11 (new CSS rules), final metrics table.
- [ ] Final owner audit: zero horizontal overflow, zero console errors at all viewports.

### Phase 7 — (FUTURE, not part of this effort) Admin theme settings

Spec in Section 9. Do not implement unless the owner asks.

---

## 5. Page Migration Recipe (repeat for every page)

1. Identify archetype (3.8). Open the old view + its partials + its CSS + its JS.
2. Header block → `component('page_header', …)`. Remove page-specific header markup/CSS.
3. KPI blocks → `component('kpi_grid')` + `kpi_card` with `tone`. Delete `*-kpi-*` CSS.
4. Filters/search/bulk → `component('toolbar', …)`.
5. Every `<table>` → `component('data_table', …)` (choose `stack` for entity lists, `scroll` for numeric/financial/log tables). Assign column priorities deliberately (1 = identity + primary action, 2 = important data, 3 = nice-to-have).
6. Modals/drawers → `modal` / `drawer` components (`<dialog>`). Update JS data attributes only if needed; do not rewrite page JS logic.
7. Forms → `form_field` / `.ab-form-grid`; keep every `name`, `id` used by JS, CSRF fields, and POST contract untouched.
8. Buttons/chips/tabs/badges → components using the mapping table below.
9. **Inline `style=""` → zero.** Replace with utilities/components. Only allowed exception: dynamic CSS variables from data (`style="--pct: 42%"`), emitted through a small helper.
10. Delete every page-CSS rule made redundant. Whatever remains must: use tokens only, contain no hex/`:root`/`@media`/`!important`, and be inside `@layer pages`. If it exceeds ~150 lines, reconsider (it is a workstation).
11. Remove the page from `$pageCssMap` if its CSS file is gone; remove dead CSS classes from views.
12. Run: `php -l` on touched files, `php tools/verify.php`, update metrics baseline with `--update-baseline`. Give the owner the exact Playwright command for this page.
13. Release per `AGENTS.md` rule 8.

Starter class mapping (extend as you discover more; every mapping goes into `COMPONENTS.md`):

| Legacy | New |
|---|---|
| `btn btn-primary` | `ab-btn` (primary is default) |
| `btn btn-outline`, `fin-btn-outline`, `btn-dash-action` | `ab-btn ab-btn--outline` |
| `btn-sm` | `ab-btn--sm` |
| `action-btn`, `act-btn`, `usr-btn-icon`, `fin-btn-icon`, `close-btn` | `ab-icon-btn` |
| `chip-btn`, `pill-btn`, `preset-btn`, `filter-btn` | `ab-chip` (selectable via `aria-pressed`) |
| `tab-btn`, `subtab-btn`, `seg-btn`, `nav-btn` | `ab-tabs` / `ab-segmented` |
| `btn-danger`, `btn-reject-receipt`, `btn-appr-danger` | `ab-btn ab-btn--danger` |
| `admin-table`, `fin-data-table`, `usr-table`, `prod-table`, `cat-table`, `od-table`, … | `data_table` |
| `*-kpi-card` | `kpi_card` |
| `alert alert-success|error|danger` | `alert` with `state` |
| `admin-modal*` | `modal` |

---

## 6. Playwright audit specification (`tools/ui-test/`, dev-only, runs on the owner's machine)

`package.json`: `"type": "module"`, dependency `playwright` (and optionally `@axe-core/playwright`), scripts `"ui:audit": "node audit.mjs"`.

**Inputs (env):** `AB_BASE_URL`, `AB_ADMIN_USER`, `AB_ADMIN_PASS`, optional `AB_PAGES` (comma list to restrict), `AB_VIEWPORTS`.
**Login:** go to `/admin/login.php`, fill `input[name=username]`, `input[name=password]`, submit, save `storageState` to `.auth/state.json` (gitignored), reuse for all contexts.

**Viewports (CSS px):**

| Name | W×H | Notes |
|---|---|---|
| android-s | 360×740 | mobile, touch |
| iphone | 390×844 | mobile, touch, safe-area |
| tablet | 768×1024 | touch |
| md-edge | 1024×768 | exactly the `md` breakpoint |
| laptop | 1280×800 | = 1920 @150% zoom |
| desktop | 1440×900 | |
| fhd | 1920×1080 | |
| zoom200 | 960×540 | = 1920 @200% |
| zoom300 | 640×360 | = 1920 @300% |
| zoom50 | 3840×2160 | = 1920 @50% |

Plus two font-scale runs on `laptop` and `iphone`: inject `html{font-size:125%}` and `html{font-size:200%}` to test rem scaling.

**Per page × viewport checks:**
1. **No horizontal overflow:** `document.documentElement.scrollWidth <= document.documentElement.clientWidth`. On failure list offending elements (RTL-aware: `rect.left < -1` or `rect.right > innerWidth + 1`, excluding elements inside an `overflow-x: auto|scroll` ancestor) with a CSS-path.
2. No `console.error`, no uncaught exceptions, no failed same-origin requests (4xx/5xx).
3. **Tap targets** (mobile viewports only): every visible `a, button, input, select, textarea, [role=button]` has a bounding box ≥ 44×44 (or is inside a larger tappable parent); report violations.
4. **Text legibility:** visible text nodes below 12px computed font-size are reported.
5. Sticky/fixed bars (bottom nav, savebar) must not overlap the last interactive element when scrolled to bottom (check that last focusable's bottom ≤ fixed bar top).
6. Full-page screenshot to `output/<timestamp>/<page>__<viewport>.png`.
7. Optional: `toHaveScreenshot()` against committed baselines (Phase 5) with `maxDiffPixelRatio: 0.01`.
8. Optional axe: `color-contrast`, `label`, `button-name`.

**Outputs:** `output/<timestamp>/report.md` (table: page × viewport × failures) and `report.json`; process exits non-zero on any failure from checks 1–2. Flags: `--pages a,b`, `--viewports iphone,laptop`, `--update-snapshots`.
**Interaction pass (UI Kit only):** open every modal/drawer, switch every tab, toggle table mode at narrow width; re-run checks 1–3.

---

## 7. New `tools/verify.php` rules (ratchet; baseline at current values, drive to 0)

Add in section 7 (`css_*` metrics already exist; extend them):

| Metric key | Rule |
|---|---|
| `css_root:<file>` / `css_hex:<file>` | now apply to **all** `assets/css/admin*.css` except `admin-tokens.css` (the only allowed home) |
| `css_media_page:<file>` | count of `@media` in page/workstation CSS that are not `print`, `hover`, `pointer`, `prefers-*` |
| `css_important:<file>` | count of `!important` |
| `css_transition_all:<file>` | count of `transition: all` |
| `css_physical_dir:<file>` | `margin-left/right`, `padding-left/right`, `left:`, `right:`, `text-align: left/right` (RTL hygiene; allow `/* physical */` comment escape) |
| `view_inline_style:<file>` | `style="` occurrences in `views/admin/**` (allow `style="--` dynamic vars) |
| `view_legacy_class:<file>` | usage of legacy button/table/alert class families listed in the mapping table |
| `bp_sync` | `AB.bp` values in `ab-kit.js` equal the constants documented in `admin-tokens.css` header |
| `component_registry` | every `component('<name>')` call has a file; every file in `views/admin/components/` is documented in `docs/COMPONENTS.md` |
| `ui_kit_coverage` | every component file appears in `views/admin/ui-kit.php` |

Every metric may only decrease. Phase 6 sets admin-wide targets to 0.

---

## 8. Definition of Done

**Per page**
- [ ] Uses only `ab-*` components/utilities; no private table/button/KPI/modal/badge CSS.
- [ ] 0 inline `style=""` (except dynamic vars), 0 `<script>` > 20 lines.
- [ ] Page CSS file deleted, or ≤150 lines, tokens only, in `@layer pages`.
- [ ] Playwright: no overflow, no console errors, tap targets OK at all viewports (MC pages: also screenshot reviewed by owner on a real phone).
- [ ] Works at zoom 50%/150%/200%/300% (the equivalent viewports) and 125%/200% font scale.
- [ ] RTL logical properties only.
- [ ] All forms still submit the same field names; CSRF intact.
- [ ] Persian text unchanged.

**Per phase:** `php tools/verify.php` green, metrics lower or equal, docs updated, changelog entry, release/tag per `AGENTS.md`.

**Whole effort:** admin CSS ≈ 5–6k lines (estimate, verify), 0 hex outside tokens, 1 palette system, 3 breakpoints, 0 page-level media queries, 0 inline styles, zero overflow across the audit matrix.

---

## 9. Future theming readiness (apply now; build the UI later)

Rules to follow from Phase 1 so a later "admin theme settings" page is cheap:

- **T1.** Every visual decision resolves to a semantic token. No component reads a primitive (`--c-*`) directly.
- **T2.** A theme is ≤ ~15 values: brand/`--tone` primitives per domain, `--bg`, `--surface`, `--text`, `--border`, `--radius-scale`, `--font-sans`, density, optional dark flag.
- **T3.** Derived colors use `color-mix()` from those values; never hand-picked hex per component.
- **T4.** Tone list and domain→tone map live in **one PHP source** (e.g. `adminTones()` + `nav_config.php` `'tone'` keys), used by both rendering and any future settings UI.
- **T5.** Contrast safety: every tone defines `--tone-on`; future UI must compute WCAG contrast and refuse failing pairs.
- **T6.** Future persistence: reuse the existing `themes`/`theme_tokens` pattern with a new `token_group = 'admin'` (as `docs/DESIGN.md` already prescribes for new token types) and inject a single `<style id="ab-admin-theme">:root{…}</style>` after `admin-tokens.css`. Values validated server-side (strict hex regex / allow-list), never raw CSS.
- **T7.** `[data-theme="dark"]` block may be added later by overriding only semantic tokens.
- **T8.** Keep `--radius-scale` and `[data-density]` as the only global shape/size knobs.

---

## 10. Pitfalls checklist (read before writing CSS)

- **P1 — Containment breaks `position: fixed`.** `container-type: inline-size` applies layout containment: fixed/absolute descendants are positioned relative to the container. Never put modals/drawers/toasts/dropdown-overlays inside a container element; use `<dialog>` (top layer) or mount at body level. Autocomplete dropdowns (absolute) inside a card are fine if they don't need to escape its box; otherwise render in a body-level portal.
- **P2 — Container needs explicit width.** An inline-size container cannot size to content; give it `width: 100%`/grid track and `min-width: 0` in flex parents.
- **P3 — Unlayered legacy wins** over layered new code until the legacy rule is deleted. If a new component looks wrong on an unmigrated page, check legacy selectors on element names (`table`, `th`, `a`, `button`).
- **P4 — `!important` inverts across layers.** Don't introduce any.
- **P5 — iOS zoom on focus:** inputs < 16px trigger zoom.
- **P6 — `100vh` on mobile** hides content behind browser UI; use `dvh`.
- **P7 — Persian digits / `dir`:** numeric, phone, email, SKU inputs get `dir="ltr"` and `inputmode`; use `.ab-num` for tabular figures. Directional icons (chevrons, arrows) must flip in RTL (`transform: scaleX(-1)` or logical icon variants).
- **P8 — Hover on touch** produces sticky hover; keep hover inside `(hover: hover)`.
- **P9 — Tables in dialogs** have their own container width: always wrap in `.ab-table-wrap`.
- **P10 — Do not use `backdrop-filter`** in admin (DESIGN.md: admin stays solid/legible).
- **P11 — Print:** finance P&L and order receipts rely on `@media print`; keep working via the shared print block.
- **P12 — JS contracts:** page scripts rely on ids/classes/data-attributes (`ordersDataMap`, `c2cOrdersMap`, `productEditTagData`, etc.). When changing markup, preserve every hook the JS reads, or update the JS in the same change and re-read it.
- **P13 — Cache busting** is automatic via `asset()` (mtime); new CSS files must be loaded through `asset()` too.
- **P14 — Never change `uploads/.htaccess`, never add `Options`/`ForceType`/`php_flag` directives anywhere** (hosting constraints in `AGENTS.md`).

---

## 11. Session prompt template (copy/paste per session)

```
Read AGENTS.md, docs/COMPONENTS.md, docs/DESIGN.md and ADMIN-UI-PLAN.md.
Execute ONLY: Phase <N> — <batch/page list>.
Follow section 0 (protocol), section 5 (recipe) and section 8 (Definition of Done).
Do not touch anything outside this scope. If a task conflicts with the plan or is impossible, stop and report.
Deliver: changed files only (zip), docs/CHANGELOG.md entry, Conventional Commit message,
the exact Playwright command for the owner, and a list of anything you could not verify.
```
