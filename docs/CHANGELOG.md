# Changelog

All notable changes to the AB-Socks project.
This project adheres to [Semantic Versioning](https://semver.org/).

> **Looking for older releases?** Releases prior to v1.39.0 are archived in [CHANGELOG-ARCHIVE.md](./CHANGELOG-ARCHIVE.md).

---

## 1.41.0 — 2026-11-07

### Master SEO, AI Discovery (GEO) & Product Catalog Infrastructure Expansion

- **English Slug Architecture & Domain Taxonomy Helper (`ab-kit.js`, `SeoService.php`, `ProductService.php`, `admin-product-edit.js`, `admin-categories.js`)**:
  - Implemented client-side real-time `input` and `paste` character filtering enforcing strictly `[a-z0-9-]`, instantly converting uppercase to lowercase and stripping invalid characters.
  - Ported sock domain dictionary (`seoGetDomainDictionary()`) into `SeoService.php` to enable intelligent server-side slug generation even when slug input is left blank.
  - Protected against pure-numeric slugs (e.g. `20`) by automatically prefixing domain descriptor (`socks-20`).
  - Added strict duplicate slug prevention halting product and category saves with validation errors instead of silent hash appending.
  - Added 1-click intelligent slug suggestion button (`.ab-slug-suggest-btn`) in product edit and categories workstations with real-time SERP preview synchronization.
- **Unified Storefront Routing & 301 Redirection (`index.php`, `views/admin/`)**:
  - Corrected legacy `/product.php?slug=` and `/category.php?slug=` links across admin workstations to canonical `/product/{slug}` and `/category/{slug}`.
  - Added early 301 backward-compatibility redirects in `index.php` for `product.php` and `category.php` to eliminate 404 errors from legacy bookmarks or external crawlers.
- **Product Entity Enrichment for Google Merchant Center & Schema.org (`database/migrations/031_v1.41.0_product_entity_enrichment.sql`, `database/schema.sql`, `views/admin/product_edit.php`)**:
  - Enriched `products` table schema with dedicated columns: `barcode` (GTIN/EAN-13/UPC), `mpn` (Manufacturer Part Number), `brand_name` (custom workshop/label), `gender` (`unisex`, `men`, `women`, `kids`), `pattern` (`solid`, `striped`, `graphic`, `novelty`, `geometric`, `floral`, `plaid`), and `cuff_height` (`crew`, `ankle`, `no-show`, `knee-high`, `quarter`, `tights`), indexed via `idx_product_entities`.
  - Added "اطلاعات تجاری و طبقه‌بندی هوشمند (Google Merchant & Schema)" card in product edit workstation.
  - Enhanced `seoBuildProductJsonLd()` in `SeoService.php` to inject `gtin13`, `mpn`, custom `brand`, `audience` (suggestedGender), `pattern`, and physical dimensions into JSON-LD payload.
- **Advanced Technical On-Page SEO, FAQ Rich Snippets & CWV LCP Preloading (`views/layout/header.php`, `views/site/product.php`, `app/controllers/site/product.php`)**:
  - Implemented `seoBuildImageAttributes()` in `SeoService.php` generating descriptive, keyword-optimized `alt` and `title` tags combining Persian name, English slug, and sequential index for all gallery images.
  - Implemented `seoBuildProductFaqJsonLd()` generating factual, product-grounded FAQ schemas (washing care, elastic/cuff durability, fabric shrinkage/pilling resistance, and shoe size fit) and rendered an accessible `<details>` accordion on product pages.
  - Added `<link rel="preload" as="image" ... type="image/webp" fetchpriority="high">` in `header.php` for hero product images, eliminating Largest Contentful Paint delays.
  - Standardized Open Graph image dimension tags (1200x630).
- **LLM/GEO Caching, AI Agent Protocol & CLI Migration Runner (`app/controllers/site/llms.php`, `.well-known/ai-plugin.json`, `.well-known/openapi.yaml`, `robots.php`, `tools/migrate.php`)**:
  - Added Conditional GET support with strong ETag computation and `HTTP 304 Not Modified` fast-exit in `/llms.txt` and `/llms-full.txt` alongside `Cache-Control` headers to protect shared hosting bandwidth against aggressive AI bot polling.
  - Deployed standard AI agent manifest (`.well-known/ai-plugin.json`) and OpenAPI 3.0 specification (`.well-known/openapi.yaml`) for autonomous AI catalog queries.
  - Introduced `tools/migrate.php` CLI migration runner for safe, idempotent schema updates with `schema_migrations` tracking.

## 1.40.1 — 2026-11-07

### Dynamic Technical Product Specification Presets & Taxonomy (`product_spec_presets`)

- **Database-Driven Attribute Taxonomy (`database/migrations/030_v1.40.1_product_spec_presets.sql`, `database/schema.sql`)**:
  - Replaced static view hardcoding with a dedicated relational preset table `product_spec_presets` for managing technical sock specifications (cuff heights, fiber compositions, seasons/thickness, usage/style, toe seam construction, wash care instructions).
  - Seeded initial dictionary covering comprehensive industry taxonomy: 8 cuff heights, 6 fiber compositions, 4 seasonality grades, 6 usage types, 3 toe seam types, and 3 wash care instructions.
- **Service Layer & Process Caching (`app/services/ProductService.php`)**:
  - Implemented `getProductSpecPresets(?string $specKey = null)` with in-memory static process caching and fallback defaults ensuring high performance and zero chance of fatal errors on unmigrated databases.
- **Admin Product Workstation Integration (`app/controllers/admin/product_edit.php`, `views/admin/product_edit.php`)**:
  - Replaced hardcoded chip HTML in the AI Grounding card with dynamic database-driven loops.
  - Connected 1-click quick-fill buttons via declarative `data-ab-quick-chip` allowing effortless administration and future storefront faceted filtering.

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





