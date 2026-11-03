# Landing Studio (Storefront Content Control)

The Landing Studio is the admin surface that gives the store owner **full editorial
control over the public landing page**: every headline, paragraph, button label,
icon, colour, image, repeater item and section of the React storefront is editable
without touching code, and nothing goes live until it is published.

Route: `/admin/landing.php` (nav: تنظیمات → استودیو صفحه اصلی)

---

## 1. Why it exists

The v1.36 storefront is a compiled React island (`assets/dist/landing/landing.bundle.js`)
consumed by `views/site/home.php`. Its copy lived in TypeScript (`src/data/site.ts` and
`src/context/LandingConfigContext.tsx`), which meant every wording change required a
developer, a Node toolchain and a redeploy — impossible for a store owner on shared
hosting with no build step.

The Studio moves the copy out of the bundle and into the database:

```
React bundle ──reads──► window.__AB_INITIAL_DATA__.landingConfig
                              ▲
                              │ published JSON
                        landing_config (single row: published_json / draft_json)
                              ▲
                              │ sanitized writes
                    LandingService ← LandingSchemaService (schema + defaults)
                              ▲
                        Landing Studio UI (admin/landing.php)
```

The bundle still ships the old TypeScript defaults as a fallback; the database
snapshot wins because `LandingConfigProvider` deep-merges the injected config over
them (`new keys are additive, scalars are replaced`).

---

## 2. Data model

| Table | Purpose |
| :--- | :--- |
| `landing_config` | Singleton row (`id = 1`). `published_json` is what visitors get; `draft_json` is the Studio working copy; `published_at/by` + `updated_at/by` provide attribution. |
| `landing_config_revisions` | Append-only archive written on every publish (kept to the newest 25). Powers the history drawer and rollback. |

Migration: `database/migrations/028_v1.37.0_landing_studio.sql`. Until it is applied,
`landingRow()` degrades to the shipped defaults instead of throwing, so a
half-ordered deploy cannot take the storefront down.

**Draft → Publish is the safety contract.** The storefront reads *only*
`published_json`; the Studio iframe reads the draft (`/?lb_preview=1`, admin session
required — guests get the published snapshot). Autosave writes the draft every ~1s of
idle time, so an accidental edit can never reach customers.

---

## 3. Schema as the single source of truth

`app/services/LandingSchemaService.php` owns three things:

1. **`landingStudioSections()`** — the editor tree: 21 sections → groups → fields, each
   with a Persian label, control type (`text`, `textarea`, `number`, `range`, `toggle`,
   `select`, `color`, `image`, `icon`, `list`, `repeater`, `order`), constraints
   (`min`/`max`/`maxLength`/`suggestLength`), Persian hints, icon vocabularies and
   conditional visibility (`showIf`). The Studio renders itself entirely from this
   array — no hand-written form HTML.
2. **`landingDefaultConfig()`** — the shipped defaults, loaded from
   `app/data/landing-defaults.json`. That JSON is generated from the React
   `defaultLandingConfig` and then normalised (icon keys lowered/mapped to what each
   component's icon map actually accepts, plus the server-rendered `seo` block). It is
   the reference shape used by the sanitizer and by the "restore default" controls.
3. **`landingFieldIndex()` / `landingSanitizeConfig()`** — a flat path → descriptor
   index and the recursive sanitizer that turns any payload back into a complete,
   schema-shaped config: unknown keys dropped, enums/icon/colour whitelisted, numbers
   clamped, text stripped of markup and clamped to its `maxLength` (Persian ZWNJ and
   other Unicode format characters preserved), URLs restricted to `#`, `/`, `http(s)`,
   `tel:` and `mailto:`, `sectionOrder` reduced to known section ids with canonical
   labels.

Invariant, enforced by `tools/landing-selftest.php`:

> every editable leaf of the config has a field descriptor, and every descriptor
> points at a real config path.

The self-test also asserts sanitizer idempotency, XSS/URL rejection and shape
preservation. Run it with `php tools/landing-selftest.php` — no database required.

---

## 4. Request flow

| Step | File | Notes |
| :--- | :--- | :--- |
| Page | `admin/landing.php` → `app/controllers/admin/landing.php` | Assembles the boot payload (schema, draft, defaults, meta, revisions, category suggestions, CSRF). A no-JS `POST` fallback performs publish/discard/reset through the same dispatcher. |
| View | `views/admin/landing.php` | Pure shell + a `<script type="application/json" id="lbStudioBoot">` payload. Zero inline logic (Rule 11). |
| Client | `assets/js/landing-studio.js` + `assets/css/admin-landing.css` | Renders rail/panel/controls from the schema, owns the store, undo/redo, autosave, dialogs and the preview bridge. |
| API | `ajax/landing_studio.php` → `landingStudioDispatch()` | Session check, CSRF check, then `save_draft`, `publish`, `discard`, `reset_section`, `reset_all`, `restore_revision`, `import`, `upload`, `meta`. Every publishing-side action is attributed in `admin_audit_logs`. |
| Storefront | `app/controllers/site/home.php` | Injects `landingConfig` (published, or draft in preview mode) into `__AB_INITIAL_DATA__`, and uses `seo.*` + `hero.*` for the server-rendered `<title>`, meta description, OG image and the no-JS fallback headline. |

---

## 5. Live preview bridge

The preview is an iframe of the real storefront (`/?lb_preview=1`), which the React
bundle renders in `previewMode` (intro loader skipped). Two postMessage channels:

| Direction | Message | Effect |
| :--- | :--- | :--- |
| Admin → iframe | `AB_LANDING_CONFIG_UPDATE` | `LandingConfigProvider` deep-merges the config → instant re-render, no reload. |
| Admin → iframe | `AB_PREVIEW_SCROLL_TO {sectionId, fieldKey}` | Scrolls to `[data-lb-section]` / `[data-lb-field]` and pulses it for 3s. |
| Admin → iframe | `AB_SET_SELECT_MODE {enabled}` | Click-to-edit mode: hovering outlines a section, clicking posts back. |
| iframe → Admin | `AB_ELEMENT_SELECTED {section, field}` | Opens the matching section and focuses the field. |

`data-lb-section` / `data-lb-field` attributes in the React components are the anchor
points; the bundle is rebuilt from `NEW_LandingPage/` and committed to
`assets/dist/landing/` (production still runs without Node — the build happens on a
developer machine only).

Device emulation renders a real viewport width (1440 / 834 / 414) inside the preview
pane and scales it down to fit, so "desktop" shows the desktop layout rather than a
squeezed mobile one. `تمامصفحه` (zen) mode collapses the rail and panel for a
full-width preview.

---

## 5b. Copy ownership rule

**No visible storefront string may live in a component.** Every label, placeholder,
ARIA name, toast and image `alt` resolves from the landing config, with the literal
kept only as a render-time fallback (`cfg.foo || "…"`). When a string mixes copy with
runtime data, use a `{placeholder}` template documented in the field hint:

| Placeholder | Meaning |
| --- | --- |
| `{code}` | discount / prize code |
| `{amount}` | remaining amount to free shipping |
| `{count}` | item count (tiers, remaining pairs) |
| `{tier}` | pack tier title |
| `{note}` | hand-written gift-card note |
| `{handle}` | Instagram handle |
| `{tags}` | style-maker tags the shopper picked |
| `{title}` | product title |

`php tools/landing-selftest.php` enforces the rule from the other side: every leaf
in `app/data/landing-defaults.json` must be reachable through a schema field
(currently 599 leaves / 353 fields / 619 assertions). Adding a config key therefore
requires adding its Studio field in the same commit.

## 5c. Footer design contract

`editorial.footer` is the widest section in the schema (8 groups / 77 fields) because the footer is
the one block that is pure design: layout, colour, density and content of every sub-block are
config, not Tailwind classes.

| Group | Keys | Notes |
| --- | --- | --- |
| چیدمان | `layoutPreset`, `align`, `contentWidth`, `columnGap`, `paddingTop`, `paddingBottom`, `showTopDivider`, `bottomAlign` | `layoutPreset` drives `gridTemplateColumns` inline |
| رنگ و ظاهر | `backgroundStyle`, `backgroundFrom`, `backgroundTo`, `textColor`, `mutedOpacity`, `accentColor`, `buttonColor`, `borderColor`, `dividerOpacity`, `cardRadius`, `decorStyle` | derived tones come from a `withAlpha(hex, pct)` helper |
| نمایش بلوک‌ها | `show*` toggles, `logoSize`, `socialShape`, `socialSize`, `badgeLayout`, `badgeStyle`, `newsletterStyle`, `backToTopText` | blocks render conditionally |
| برند | `brandTitle`, `brandSubtitle`, `tagline`, `sloganEnglish` | |
| ستون‌های لینک | `col{1,2,3}Enabled` / `Title` / `Links` | each link is `برچسب\|#مقصد`; no target = plain text |
| نشان‌های اعتماد | `badge{1..4}Enabled` / `icon` + title/sub/msg copy | icon vocabulary `badge` |
| خبرنامه | `newsletterStyle`, title, placeholder, button, note | |
| شبکه‌های اجتماعی و نوار پایین | social URLs + labels, `copyright`, `copyrightText` | |

**Never compose Tailwind class names from config** (`lg:col-span-${n}`): the build only keeps classes
that appear literally in source. Colours, spacing and grid tracks go through inline `style`.

## 6. Studio UX surface

- **Section rail** — grouped by page structure / main sections / tools & windows;
  drag to reorder (`sectionOrder`), eye toggle to disable, per-section change badges.
- **Field panel** — collapsible groups, per-field default restore, change dots,
  character counters with SEO-suggested lengths, conditional fields, icon pickers,
  colour pickers, image fields with upload, primitive lists and nested repeaters
  (duplicate / reorder / collapse).
- **Toolbar** — draft state pill ("N تغییر ذخیرهنشده" → "پیشنویس همگام است"),
  undo/redo, select-from-preview, fullscreen, history, overflow menu (reset section /
  reset all / import JSON / export JSON / discard draft / shortcuts), publish with note.
- **Publish** — writes `published_json`, archives a revision, prunes to 25, audits.
- **Keyboard** — `Ctrl+S` save draft, `Ctrl+Z` / `Ctrl+Shift+Z` undo/redo, `Ctrl+K`
  field search, `V` select mode, `F` fullscreen, `Esc` close.
- **Panel head tooling** — section title + field count, «فقط تغییرات» filter
  (fields that differ from the factory default; stable across autosaves), a
  collapse/expand-all toggle for the group list and «بازیابی خالی‌ها»
  (`#lbEmptyFixChip`, shown only when a section has empty-but-defaulted fields).
- **Focus mode** — `#lbFocusBtn` (or `F`) sets `html.lb-focus-mode`, which hides the admin topbar, the topic
  tab strip, the sidebar and the bottom nav and grows the shell to `100dvh - 1.5rem`.
- **Collapsible panes** — `#lbRailToggle` (rail head) and `#lbPanelToggle`
  (toolbar) set `data-rail-collapsed` / `data-panel-collapsed` on `.lb-studio`;
  the rail folds to a 42px icon strip, the panel hides and the preview takes the
  space. State is remembered in `localStorage`.
- **Preview device auto-fit** — the pane width picks the emulated device (mobile
  < 430px, tablet < 620px, desktop beyond) so the page is never squeezed below a
  readable scale; choosing a device manually pins it until reload.

### Layout contract

The shell is sized from JavaScript, not from a fixed height: `syncStudioHeight()`
measures the distance from the admin chrome to the fold, writes `--lb-studio-h`
and re-checks the resulting page height, so the studio always ends at the fold and
the page itself never scrolls. CSS keeps a `calc(100dvh - …)` fallback for the
first paint and for no-JS contexts.

Scrollbars: the rail list and the panel body style `::-webkit-scrollbar` (10px,
tokenised thumb) and reserve the gutter with `scrollbar-gutter: stable`. Do **not**
add `scrollbar-width`/`scrollbar-color` unconditionally — Chromium then ignores
`::-webkit-scrollbar` entirely and falls back to overlay bars that are invisible
until you scroll. Firefox, which has no webkit pseudo-elements, gets the standard
properties behind `@supports not selector(::-webkit-scrollbar)`.

Pane sizing: the workspace is a five-track grid —
`var(--lb-rail-w) var(--lb-split-w) var(--lb-panel-w) var(--lb-split-w) minmax(0, 1fr)` — so the gaps
double as drag handles (`#lbSplitRail`, `#lbSplitPanel`). Widths are written to inline custom
properties on `.lb-studio` (they must beat the container-query defaults), persisted under
`lb:studio:w:<pane>` and re-applied on boot. Because the grid is RTL, `startX - clientX` grows a pane.
Collapsed/zen/tabbed layouts switch the track list, so every state rule must hide the handle it drops
(`[data-panel-collapsed]` hides `.lb-splitter--panel`, `[data-zen]` and the 52rem breakpoint hide both) —
otherwise the extra hidden item shifts the preview into an implicit column.

CSS layering gotcha: `admin.css` is **unlayered**, so any rule there beats everything in
`@layer layout` / `@layer pages` regardless of specificity. Shell rules that must be overridable belong in
`admin-shell.css`; state overrides that must win (e.g. `html.lb-focus-mode …`) must live in an unlayered
file or in a file after `admin.css` in the same layer.

Overflow containment: any absolutely positioned helper inside a pane must have a
positioned ancestor (`.lb-switch` is `position: relative` for exactly this reason),
and the shared `.lb-rail / .lb-panel / .lb-preview` rule carries `contain: paint`
so a stray absolute box can never stretch the document again.

Container queries live on **`.lb-studio-shell`**, an ancestor of `.lb-studio` —
an element never resolves `@container` rules against itself, so querying
`.lb-studio` directly silently disabled every breakpoint. Breakpoints:

| Container width | Layout |
| :--- | :--- |
| > 96rem | rail 14rem · panel 23.5rem · preview, status timestamp visible |
| ≤ 96rem | status timestamp hidden |
| ≤ 84rem | rail 13rem · panel 21.5rem |
| ≤ 62rem | rail 12rem · panel 19.5rem, secondary copy trimmed |
| ≤ 52rem | editor/preview **tabs** (rail keeps its column beside the panel) |
| ≤ 40rem | single column, natural page height, condensed toolbar |

---

## 7. Deployment checklist

1. Upload the release (includes `assets/dist/landing/*` — no build step on the server).
2. Apply `database/migrations/028_v1.37.0_landing_studio.sql` via phpMyAdmin.
3. Confirm `uploads/landing/` is writable (created on first image upload otherwise).
4. Open `admin/landing.php`, edit, publish.
5. Optional: `php tools/landing-selftest.php` and
   `node tools/ui-test/test-landing-studio.mjs` (dev machine, Playwright).
