# SEO Standards

This document tracks what's actually implemented for SEO, separately from
what's planned — so "SEO work" has a concrete backlog instead of being an
open-ended goal. Read this alongside `docs/DESIGN.md` before the UI redesign
touches anything on this list; several of these are load-bearing for
rankings and easy to break by accident with a design change.

## Implemented today

- **Indexing control**: `seo_indexing_enabled` (admin setting) drives
  `<meta name="robots">`, `robots.php`'s `Disallow`, and whether
  `sitemap.php` is advertised in `robots.txt`. Off by default so
  incomplete/test content is never indexed. **Confirm this is switched on
  before treating any SEO work below as live** — it's a single setting that
  silently gates everything else.
- **Sitemap**: `sitemap.php` generates categories and active products
  dynamically, independent of the indexing toggle (generating it doesn't
  force indexing; `robots.txt` is the actual gate).
- **Per-page metadata**: title format `{page title} | {SITE_NAME}`, a meta
  description per page (product, tag, search — see
  `docs/ARCHITECTURE.md` §6.7 for the tag-page-specific description), and a
  canonical URL tag emitted whenever indexing is enabled.
- **Open Graph**: `og:title`, `og:description`, `og:type`, `og:url`, and
  `og:image` where a product image is available.
- **Structured data**: `schema.org/Product` JSON-LD on product pages,
  including availability derived from the same `effectiveStockSqlFragment()`
  logic the visible "in stock" badge uses — the structured data and what a
  visitor actually sees can't drift apart.
- **URL structure**: human-readable, hyphenated slugs throughout
  (`slugify()`) — hyphens are the correct word separator for search
  engines; this was verified, not assumed, when the tag system was reviewed
  (`docs/ARCHITECTURE.md` §6.7).
- **One `<h1>` per page.** The homepage's was consolidated into a compact
  intro (`docs/ARCHITECTURE.md` §6.9) and the tag page's missing `<h1>` was
  added specifically because this was audited and found broken once
  already — don't reintroduce a page with zero or multiple `<h1>`s.
- **Image `alt` text**: product images use the actual product name, not a
  generic placeholder, and the main product-grid image is
  `loading="lazy"`.

## Planned / open

Roughly in priority order:

1. **Structured breadcrumbs** — both a visible breadcrumb trail
   (category → product) and matching `BreadcrumbList` JSON-LD. Currently
   absent everywhere; this is a real gap, not a nice-to-have, for an
   e-commerce catalog with category depth.
2. **Organization / WebSite structured data** on the homepage (name, logo,
   social links — all of which already exist as admin settings from the
   footer/social-links work; this is largely wiring existing data into a
   JSON-LD block, not new content).
3. **Core Web Vitals as an explicit, monitored budget**, not just an
   assumption. This is now directly load-bearing for the UI redesign in
   `docs/DESIGN.md`: heavy `backdrop-filter` use or an added 3D library are
   exactly the kind of change that quietly regresses LCP/INP on mobile.
   Measure before and after any glass/depth surface ships (real mid-range
   Android device, not just desktop devtools throttling — see
   `docs/DESIGN.md`'s rollout plan), and via Search Console's Core Web
   Vitals report once the site has traffic history there.
4. **Google Search Console + Bing Webmaster Tools verification and sitemap
   submission.** Purely operational (no code), but worth doing as soon as
   `seo_indexing_enabled` is switched on for real, not left until later.
5. **Duplicate/thin-content review between category and tag pages.** A
   product can legitimately appear under both a category and several tags;
   confirm each page type still has a distinct, non-boilerplate description
   (tag pages already do — §6.7) and that nothing produces two indexable
   URLs for what's effectively identical content.
6. **404 handling for changed/removed URLs.** Confirm a real `404` page and
   status code are returned (not a soft-404 that returns `200`), and that
   slug changes on renamed products/categories don't silently 404 old,
   previously-indexed URLs without at least being a deliberate decision.
7. **Internal linking audit** once the redesign lands — category/tag/related-product
   links are what let crawlers (and users) reach deep catalog pages; a
   visual redesign that quietly drops a link (e.g. collapsing related
   products behind a JS-only interaction) is an easy way to regress this
   without anyone intending to.

## Guardrails for the redesign specifically

- Any new visual component must not remove or visually hide (from a
  crawler's perspective) heading structure, internal links, or the existing
  meta tags/JSON-LD above. A glass overlay is a styling layer, not a reason
  to restructure the underlying HTML.
- Lazy-loading images (already in use on the product grid) should extend to
  any new imagery the redesign adds — but never to the single largest
  above-the-fold image on a page (that one should stay eager/high-priority,
  since lazy-loading it directly hurts LCP).
- If the redesign adds any client-side rendering for content that's
  currently server-rendered PHP (product names, prices, descriptions),
  confirm it's still present in the initial HTML response, not injected
  only after a JS bundle executes — this project has no server-side
  rendering framework to fall back on if that regresses.
