# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.38.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

## 1.40.0 — 2026-11-07

### Search Engine Optimization (SEO) & Generative Engine Optimization (GEO/AI) Master Infrastructure

- **301 URL Redirection & Slug Authority Preservation (`url_redirects`, `app/services/SeoService.php`, `index.php`)**:
  - Created `url_redirects` table to store source/target paths, HTTP status codes (301/302), and visit counters.
  - Implemented automatic 301 redirect registration whenever a product or category slug is modified in the admin panel, eliminating broken indexed links (Soft-404) and preserving domain authority.
  - Added early redirect resolution middleware in `index.php` with hit metric logging.
- **AI Search & Generative Engine Optimization (`robots.php`, `app/controllers/site/llms.php`, `llms.txt`)**:
  - Implemented dynamic endpoints for `/llms.txt` and `/llms-full.txt` according to the https://llmstxt.org open standard, serving clean, high-density Markdown catalogs, material specs, sizing, and care guides for ChatGPT Search, Perplexity, Claude, and Gemini.
  - Updated `robots.php` to explicitly welcome LLM crawlers (`GPTBot`, `ChatGPT-User`, `PerplexityBot`, `ClaudeBot`, `Google-Extended`, `Applebot-Extended`) while guarding private admin, cart, and checkout paths.
- **Rich Merchant Structured Data (`app/services/SeoService.php`, `views/layout/header.php`, `views/site/home.php`, `views/site/product.php`, `views/site/category.php`)**:
  - Comprehensive `schema.org/Product` generation with Rial pricing, real-time stock availability, `sku`, `brand`, 7-day merchant return policy (`MerchantReturnPolicy`), and shipping lead time (`OfferShippingDetails`).
  - Added `schema.org/BreadcrumbList` JSON-LD coupled with accessible visual breadcrumbs on product and category pages.
  - Built `ClothingStore` and `WebSite` Schema on homepage with `SearchAction` (enabling Google Sitelinks SearchBox).
  - Built `CollectionPage`, `ItemList`, and `FAQPage` schemas for category hubs.
- **Image Sitemap Namespace Extension (`sitemap.php`)**:
  - Upgraded XML sitemap to support Google Image Sitemap namespace (`xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"`), indexing product main images with Persian captions, category hubs, and active tags with accurate `<lastmod>` timestamps.
- **Admin Workstations & Webmaster Management (`views/admin/settings_partials/_tab_seo_social.php`, `views/admin/product_edit.php`, `views/admin/categories.php`)**:
  - Added Google Search Console and Bing Webmaster meta tag configuration fields in store settings.
  - Added live Google SERP Snippet Preview card and dedicated SEO inputs (title, description, canonical override, material specs) in product edit workstation.
  - Added category SEO title, meta description, top introduction (`seo_intro`), and rich buyer guide (`seo_guide`) editors.
  - Published comprehensive operational guide at `docs/SEO-ADMIN-GUIDE.md`.
- **Automated Quality Gate & Git Privacy Guard (`tools/verify.php`, `.gitignore`, `AGENTS.md`, `.agents/skills/ab-socks-seo/SKILL.md`)**:
  - Added Section 8 (SEO, AI Search & Machine Invariant Guard) to `tools/verify.php` with 7 automated checks: Head/Meta contracts, Schema.org JSON-LD wiring, strict single `<h1>` hierarchy, 301 redirect middleware, AI bot crawler permissions, Google Image sitemap namespace, and Git privacy protection.
  - Enforced git ignore on operational admin playbooks (`*-ADMIN-GUIDE.md`, `/docs/local/`) and added hard verification failures (`Errors: 1`) preventing accidental staging or commits of internal docs.
  - Enacted Rule 13 (SEO & GEO Invariant Discipline) in `AGENTS.md` and created project skill `ab-socks-seo`.

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




