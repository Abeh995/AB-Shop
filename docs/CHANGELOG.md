# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.37.1 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

## 1.39.2 — 2026-11-07

### Admin Image Optimizer — Infinite loop fix, 100% bypass, step buttons, and gallery container responsiveness

- **Event loop & infinite compression loop (`assets/js/admin-image-optimizer.js`)**:
  - Eliminated infinite loop where programmatic `syncFilesToInput()` dispatched a native `change` event on the file input, causing single cover images to get stuck in "در حال فشرده‌سازی" and gallery uploads to recursively clone and compress endlessly.
  - Implemented an internal sync guard flag (`_isInternalSync`) and custom `aio:synced` event dispatching.
- **100% Quality raw file preservation (`assets/js/admin-image-optimizer.js`)**:
  - When the quality slider reaches 100% (`q >= 0.999`), canvas WebP re-encoding is completely bypassed.
  - The untouched raw original file is preserved byte-for-byte with its original format, filename, dimensions, and EXIF, preventing file size inflation (e.g. 1MB JPEG re-encoding to 1.5MB WebP).
- **1% Granular step buttons (`assets/js/admin-image-optimizer.js`, `assets/css/admin.css`)**:
  - Added `+` and `−` step buttons on both sides of the quality slider in both the preview card and the Fullscreen Quality Inspector modal, enabling single-percentage precision adjustments.
- **Gallery card container responsiveness & sidebar overflow (`assets/css/admin.css`)**:
  - Replaced rigid `repeat(auto-fill, minmax(320px, 1fr))` on `.aio-previews-grid` with fluid `repeat(auto-fill, minmax(min(100%, 260px), 1fr))`.
  - Added `min-width: 0; width: 100%;` to `.aio-card` so cards in the 340px sidebar never overflow horizontally or lose their left margin.
  - Moved `.aio-btn-remove` from absolute positioning into `.aio-card-header` flex flow, preventing overlap with LTR file names.
  - Stacked `.aio-stats-row` vertically with space-between values to fit large image dimensions within narrow containers without clipping.
- **Action button semantics & SSoT Design Tokens (`assets/js/admin-image-optimizer.js`, `assets/css/admin.css`)**:
  - Replaced confusing green "تایید شده (کلیک برای لغو)" with an outline danger button `لغو تایید ❌` (`.aio-btn-cancel`).
  - Switched pending action to green `✓ تایید این تصویر` (`.aio-btn-confirm`).
  - Cleaned up duplicate checkmarks in Fullscreen Inspector.
  - All new styles strictly use semantic CSS variables (`var(--success)`, `var(--danger)`, `var(--surface)`, `var(--border)`).
- **Form submission guard with unconfirmed images (`assets/js/admin-image-optimizer.js`)**:
  - Fixed form submit handler so confirming the unapproved images modal warning does NOT automatically approve and upload unconfirmed images.
  - Only explicitly approved images are synced to the form submission payload, discarding unapproved uploads.

## 1.39.1 — 2026-11-02

### Landing Studio — popover stacking, focus mode and resizable panes

- **Global-search popover was clipped by the admin sidebar (`assets/css/admin.css`, `admin-tokens.css`, `assets/js/admin.js`)**:
  the results panel declares `z-index: var(--z-overlay)` but lives inside the sticky `.dash-topbar`, whose own stacking
  context was pinned at `--z-sticky` (20) — below the sidebar (40) and the studio toolbar (40), so the open menu slid
  under them and looked cut in half. The topbar now lifts itself to a dedicated `--z-dropdown` layer (50) only while the
  popover is open (`is-search-open`), and the base `.dash-topbar` rule was removed from `admin.css`: it was **unlayered**,
  and unlayered rules beat every `@layer`, so no layered rule could ever override it. The shell copy in
  `admin-shell.css` (`@layer layout`) is now the single definition.
- **`ajax/admin_search.php` returned 500 on every request** — it guarded on `isAdmin()`, a function that does not exist
  (the helper is `isAdminLoggedIn()`). The endpoint answered 500 with an empty body, so the menu never opened at all.
- **Focus mode now hides the whole admin chrome** — `.admin-subnav-container` (the topic tabs) joins the topbar, sidebar
  and bottom nav in `html.lb-focus-mode`, and the shell grows to `100dvh - 1.5rem` so the studio owns the entire window.
- **Resizable panes** — the two workspace gaps are now real grid tracks that host drag handles
  (`#lbSplitRail`, `#lbSplitPanel`): drag to resize the rail or the editor, the preview always takes the remaining
  `1fr`. Pointer capture + `requestAnimationFrame` re-fit keep the emulated device responsive *during* the drag;
  widths persist in `localStorage`, double-click or `Home` restores the default and arrow keys nudge by 16px
  (48px with Shift). Handles are hidden in zen mode and below the 52rem tabbed breakpoint.
- **Slimmer studio toolbar** — 76px → ~42px: tighter padding, 1.75rem mark, `--text-sm` title, 1.85rem icon buttons and
  1.9rem action buttons, so the panes get the height back.
- **Suite grown to 29 checks** — slim toolbar height, splitter drag (panel grows / preview shrinks), focus mode hides
  topbar + tabs + sidebar, focus mode still fills the fold, search popover paints above sidebar and studio.

## 1.39.0 — 2026-11-02

### Landing Studio — Full design control over the footer

The footer was the one section the Studio could only *rename*; now the whole section is design-driven.
`editorial.footer` grew by **51 keys** and the section in the rail went from 4 groups / 21 fields to
**8 groups / 77 fields**.

- **Layout** — `layoutPreset` (three-column / two-column / stacked), `align` (start / center),
  `contentWidth`, `columnGap`, `paddingTop`, `paddingBottom`, `showTopDivider`, `bottomAlign`.
- **Colour & surface** — `backgroundStyle` (solid / gradient / mesh glow), `backgroundFrom`,
  `backgroundTo`, `textColor`, `mutedOpacity`, `accentColor`, `buttonColor`, `borderColor`,
  `dividerOpacity`, `cardRadius`, `decorStyle` (none / dot grid / glow). Colours are real colour
  pickers and every derived tone (muted text, hairlines, hover surfaces) is computed from them.
- **Block visibility** — `showBrand`, `showSocial`, `showLinks`, `showNewsletter`, `showBadges`,
  `showSlogan`, `showCopyright`, `showBackToTop`, plus `logoSize`, `socialShape`, `socialSize`,
  `badgeLayout` (2-col / 1-col / row), `badgeStyle` (soft / solid), `newsletterStyle` (pill / card)
  and `backToTopText`.
- **Three editable link columns** — `col1/2/3Enabled`, `col1/2/3Title` and `col1/2/3Links`, where each
  entry is `برچسب|#مقصد`; an entry with no target renders as plain muted text (that is how
  «تعویض ۷ روزه بی‌قیدوشرط» and the support-hours line are modelled now). The columns are no longer
  hardcoded `navItems`.
- **Four configurable trust badges** — per-badge `badgeNEnabled` + `badgeNIcon` (shield / badge /
  card / bank) alongside the existing title / subtitle / click-message copy.
- **`Footer.tsx` rewritten** to read every one of those values: blocks render conditionally, colours
  are applied through a hex→rgba helper so opacity controls work on any brand colour, and the layout
  is a real CSS grid (`gridTemplateColumns` from `layoutPreset`) instead of fixed Tailwind
  `lg:col-span-*` classes — dynamic Tailwind class names never survive the build.
- Verified live in the preview: switching to two columns + centered + mesh gradient + row badges
  updates the iframe without a reload; `alignment`, `gridTemplateColumns` and `backgroundImage`
  were asserted from the preview DOM, and the fixture was reset afterwards.
- Schema/self-test: 543 / 302 → **599 leaves / 353 fields / 619 assertions OK**; config version and
  `APP_VERSION` both bumped to `1.39.0`.

## 1.38.0 — 2026-11-01

### Landing Studio — Full Copy Coverage, Collapsible Panes, Scrollable Panel

- **Every storefront string is now editable (item 4).** ~70 strings that were still hardcoded in the React components are now part of the landing config, exposed as Studio fields:
  - `catalog`: sort labels (`sortPopular`/`sortNewest`/`sortCheap`/`sortExpensive`), card-layout labels (`viewComfortLabel`/`viewDenseLabel`/`viewListLabel`), `clearSearchText`, `productNameTemplate` (the «جوراب {title}» name used on cards, modal and hero).
  - `cartDrawer`: `closeLabel`, `freeShippingRemaining`, `freeShippingReached`, `colorPrefix`, `sizePrefix`, `increaseLabel`, `decreaseLabel`, `removeLabel`.
  - `packBuilder`: `capacityText`, `trayText`, `summaryText`, `notePrefix`, `notePlaceholder`, `addButtonText`, `remainingText`, `removeLabel`.
  - `sizeGuide`: `steps` (the three measuring steps) and `shoeSizeInputLabel`.
  - `styleMaker`: `reasonTemplate`, `workshopNote`.
  - `topStrip`: `copyToast`, `codeLabel`, `copyHint`.
  - `socialStyle`: `tagCopiedToast`, `tagPrompt`, `tagCopyLabel`, `postAlt`.
  - `luckyWheel`: `copiedToast`, `codeLabel`, `spinningText`, `spinAgainText`.
  - `header`: `menuLabel`, `menuCloseLabel`, `homeLabel`, `faqLabel`, `deckHomeLabel`, `deckMenuLabel`, `deckShopLabel`, `deckWheelLabel`, `searchLabel`.
  - `trackModal`: `inputHint`; `editorial.techTrust`: `otherColumnLabel`; `editorial.faq.supportBox`: `chatUnavailableText`.
  - `editorial.footer`: `legalTitle`/`legalSub`/`legalText`, `gatewayLabel`, `secureTitle`/`secureDesc`, `unionLabel`/`unionSub`/`unionText`, `trustTitle`/`trustSub`/`trustText`, `instagramLabel`, `telegramLabel`, `newsletterNote`, `copyrightText`.
  - `editorial.story.imageAlt`, `editorial.promiseBox.giftImageAlt`.
  - Strings with runtime values use `{placeholders}` (`{code}`, `{count}`, `{tier}`, `{note}`, `{handle}`, `{tags}`, `{title}`, `{amount}`); the accepted placeholders are documented in each field's hint.
  - Schema/self-test grew from 471 leaves / 232 fields to **543 leaves / 302 fields** (`php tools/landing-selftest.php` → 563 assertions OK).
- **Collapsible panes (item 2).** The section rail and the field panel can now be folded away — `#lbRailToggle` in the rail head and `#lbPanelToggle` in the studio toolbar, backed by `data-rail-collapsed` / `data-panel-collapsed` on `.lb-studio`. Collapsing the rail leaves a 42px icon strip; collapsing the panel removes it entirely and hands the space to the preview. Both states persist in `localStorage` (`togglePane()` / `restorePaneState()` in `landing-studio.js`).
- **No more overlap with the sticky admin topbar (item 3).** The studio toolbar is no longer `position: sticky` (the shell already fills the fold, so the toolbar is always on screen); the stacking collision with `admin-shell.css`'s sticky bar is gone.
- **Dead space under the workspace (item 5) — real root cause.** The visually hidden `.lb-switch` checkbox (`position: absolute; opacity: 0`) had no positioned ancestor, so it escaped the panel and stacked five invisible 17px boxes down the document, stretching `docH` from 900px to 2863px and pushing the free white space into the viewport. `.lb-switch` is now `position: relative`, `contain: paint` on the shared `.lb-rail / .lb-panel / .lb-preview` rule keeps any future absolute helper inside its pane, and `.lb-group` plus the shared `flex: 0 0 auto` guard stop flex children from being shrunk and clipped. Verified at 1356×646, 1366×768, 1440×900, 1920×1080, 1280×800 and 1024×768: `documentElement.scrollHeight === innerHeight`, panel body scrollable.
- **Always-visible panel scrollbar (item 1).** `scrollbar-color` on the rail/panel scroll areas made Chromium ignore the `::-webkit-scrollbar` styling and fall back to overlay scrollbars that only appear while scrolling. Those declarations are gone; the custom 10px bar is back, `scrollbar-gutter: stable` reserves its space so the pane geometry never jumps, and Firefox (which has no `::-webkit-scrollbar`) gets the standard properties behind `@supports not selector(::-webkit-scrollbar)`.
- **Empty-field chip got its own id (`#lbEmptyFixChip`)** and the collapse-all chip is now `#lbCollapseAllChip`, so the smoke suite can target them unambiguously.
- **Config version** bumped to `1.38.0` in both `defaultLandingConfig` and `app/data/landing-defaults.json`; existing drafts are merged forward on load, so no migration step is needed.

## 1.37.1 — 2026-11-01

### Landing Studio — Layout & Responsiveness Fixes, Editor Affordance Pass

- **Layout correctness (`assets/css/admin-landing.css`, `assets/js/landing-studio.js`, `views/admin/landing.php`)**:
  - Fixed the dead band under the editor: the shell was capped at `54rem`, so tall windows showed a bare page-canvas strip below the studio. `syncStudioHeight()` now measures the offset under the admin chrome and sizes the shell to the fold (CSS `calc()` fallback retained), so the page never scrolls and the three panes scroll internally.
  - Fixed container-query dead code: `container-type` lived on `.lb-studio` itself, so every `@container lb-studio` rule that restyled the shell was silently ignored. The query now resolves against a new `.lb-studio-shell` wrapper, which makes all breakpoints work.
  - Re-tuned the breakpoints so the live preview survives on laptops: three panes down to 52rem (was 78rem, which dropped the preview on a 1366px laptop), tighter rail/panel widths at 84rem and 62rem, tabbed editor/preview only below 52rem, single column below 40rem.
  - Section rail: the drag handle is now overlaid on hover instead of reserving column width, so long Persian section names no longer collapse to "بخش اصلی (…".
- **Editor affordance (`assets/css/admin-landing.css`)**:
  - Inputs, textareas and selects get a visible border (mixed from `--text-muted`), a hover state and a primary focus ring; font size moved to `--text-sm`. Empty-looking field rows were the main readability complaint on high-DPI/low-zoom screens.
  - Group headers gained a field-count chip; scroll areas (`rail`, `panel`, `stage`) got `overscroll-behavior: contain` and tokenised scrollbars; visible `:focus-visible` outlines added across rail items, chips and icon buttons.
- **Editor UX (`assets/js/landing-studio.js`)**:
  - Panel head now shows the section's field count plus two new controls: a **«فقط تغییرات»** filter (fields that differ from the factory default, stable across autosaves) and a **collapse/expand all** toggle; an empty state explains the filter when a section is untouched.
  - The preview auto-fits the device to the pane (mobile under 430px, tablet under 620px) so the emulated page is never squeezed to an unreadable scale; picking a device manually disables auto-fit.
  - Switching the mobile editor/preview tab now re-measures the shell and rescales the frame (the preview used to stay collapsed at its hidden size).
- **Admin shell (`assets/css/admin-shell.css`)**:
  - Fixed a pre-existing horizontal overflow of the topic-tab strip between 1024px and 1220px. The tabs widened the document, and because the admin is RTL the page opened scrolled sideways, clipping the right-hand pane of the studio (and the classic appearance page). The strip now scrolls inside itself.
- **Quality gates (`tools/ui-test/test-landing-studio.mjs`)**:
  - Suite grown to 24 checks: fold-fill assertion, changed-only filter, collapse-all, zen mode, a dedicated 1366×768 laptop pass (preview must stay mounted, no sideways/fold overflow) and a fresh `studio-laptop.png` baseline.


