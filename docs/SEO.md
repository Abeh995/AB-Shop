# SEO & Generative Engine Optimization (GEO) Standards

This document tracks implemented SEO/GEO features and technical standards for the AB-Socks repository.

## Implemented Architecture

### 1. Indexing & Webmaster Control
- **`seo_indexing_enabled`**: Single source of truth driving `<meta name="robots">`, `robots.php`'s rules, and sitemap exposure.
- **Search Engine Verification**: Native admin inputs for `google-site-verification` and Bing `msvalidate.01` meta tags in `views/layout/header.php` and `views/site/home.php`.
- **AI Crawlers & Generative Search**: `robots.php` explicitly handles and authorizes LLM search bots (`GPTBot`, `PerplexityBot`, `ClaudeBot`, `Google-Extended`, `Applebot-Extended`) for citation and answer generation while shielding administrative and transactional routes (`/admin/`, `/cart`, `/checkout`, `/account`).

### 2. Sitemaps & Knowledge Feeds
- **Image Sitemap (`sitemap.php` / `sitemap.xml`)**: Upgraded to support Google Image XML namespace (`xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"`), indexing product main images with localized captions, category hubs, and active tags with exact `<lastmod>` timestamps.
- **AI Knowledge Directory (`/llms.txt` & `/llms-full.txt`)**: Implemented per the https://llmstxt.org open standard, serving high-density structured Markdown to AI search engines (brand identity, materials, fabric specifications, sizing and care guides, and catalog links).

### 3. URL Architecture & 301 Redirect Engine
- **Human-Readable Slugs**: Automated hyphenated slugs (`slugify()`) on products, categories, and tags.
- **Permanent 301 Redirection**: Backed by `url_redirects` table and `seoResolveRedirect()` in `index.php`. Modifying a product or category slug in the admin panel automatically records a 301 redirect from the legacy URL to the new slug, preventing 404 errors and preserving page authority in Google.

### 4. Rich Structured Data (Schema.org JSON-LD)
All schemas are generated centrally in `app/services/SeoService.php`:
- **`Product`**: Merchant listing compliance with currency (`IRR`), real-time stock availability, `sku`, `brand`, `hasMerchantReturnPolicy` (7 days), and `shippingDetails`.
- **`BreadcrumbList`**: Emitted on product and category pages with matching visual breadcrumb trails.
- **`ClothingStore` & `WebSite`**: Emitted on the homepage with `SearchAction` (Sitelinks Searchbox enablement).
- **`CollectionPage` & `ItemList`**: Emitted on category listings.
- **`FAQPage`**: Dynamic accordion FAQ schema on category pages.
- **Entity Grounding (`additionalProperty`)**: Structured material specs (cotton percentage, sock height, fabric thickness, season) mapped for AI comprehension.

### 5. On-Page Semantic HTML & Open Graph
- **Heading Hierarchy**: Strict single `<h1>` per page.
- **Social Tags**: Complete Open Graph (`og:site_name`, `og:title`, `og:description`, `og:type`, `og:url`, `og:image`, `og:locale="fa_IR"`) and Twitter Cards (`twitter:card="summary_large_image"`).
- **Image Optimization**: Descriptive `alt` tags, lazy loading for below-the-fold assets, and direct WebP image delivery.

## Admin Documentation
A comprehensive operational playbook for store managers is available at `docs/SEO-ADMIN-GUIDE.md`.
