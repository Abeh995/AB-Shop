# Theme System & Media Processing Pipeline

This document details the client-side image optimization architecture, serverless WebP streaming proxy, and the database-backed theme engine.

---

## 1. Client-Side Image Optimizer Pipeline

Shared-hosting plans face severe PHP memory limits (`memory_limit = 128M` or `256M`) and CPU throttling. Processing raw photos from modern smartphones (48-megapixel JPEGs or 30-40 MB files) on the server frequently triggers PHP fatal out-of-memory errors.

### Browser-First Compression (`assets/js/admin-image-optimizer.js`)
All image resizing, metadata stripping, and format conversion occur **entirely inside the admin's browser** prior to form submission:

1. **HTML5 Canvas Processing**:
   - High-resolution images are rendered into an off-screen HTML5 Canvas.
   - Resized proportionally to a maximum bounding dimension of 1600px for products and 1000px for brand logos.
2. **100% Privacy & Metadata Stripping**:
   - Because Canvas reads and re-encodes raw pixel buffers, all camera EXIF metadata, camera serial numbers, and sensitive GPS geolocation tags are completely stripped at the client level.
   - Backend functions (`handleProductImageUpload`, `handleBrandingImageUpload`) perform complementary EXIF sanitation.
3. **iPhone HEIC/HEIF Decoding**:
   - When an iPhone `.heic` or `.heif` image is selected, the browser lazy-loads `assets/js/vendor/heic2any.min.js` to convert the raw buffer to JPEG in memory before feeding it into the WebP canvas pipeline.
4. **Interactive Quality & Live Preview**:
   - The admin UI displays a live comparison card: original file size, compressed size, savings percentage (typically 90-98%), and a quality slider (10% to 90%, default: 30%).
   - Includes a full-screen inspector with Zoom (20% to 500%), Pan/drag, and hold-to-compare against the raw original.
5. **Form Integration via DataTransfer**:
   - The resulting WebP blob is converted to a `File` object and injected directly into the standard `<input type="file">` via the standard `DataTransfer` API.
   - Form submission proceeds via normal POST with standard CSRF validation, uploading lightweight files (< 200 KB) with zero server-side CPU stress.

---

## 2. Standardized Asset Naming

All uploaded files are assigned deterministic, human-readable, and collision-free names:
- **Product Main Image**: `product-{ID}-main-{hash}.webp`
- **Product Gallery Image**: `product-{ID}-g{sort}-{hash}.webp`
- **Gift Item Image**: `giftitem-{ID}-main-{hash}.webp`
- **Branding Logo**: `logo-site-{hash}.{ext}`

Helper functions `generateStandardFilename()` and `renameUploadedImage()` in `app/core/functions.php` manage standard naming upon database record insertion.

---

## 3. WebP Delivery Architecture (`img.php`)

To circumvent Apache/Nginx MIME table gaps on DirectAdmin shared hosting:

```
[Browser Request: /uploads/products/product-1-main-abc.webp]
                       │
                       ▼
              [.htaccess Rewrite]
                       │
                       ▼
            [/img.php?f=/uploads/...]
                       │
         ┌─────────────┴─────────────┐
         ▼                           ▼
[File exists & valid?]       [ETag matches?]
   YES: Continue                YES: HTTP 304 Not Modified
         │
         ▼
[Headers Emitted]:
  - Content-Type: image/webp
  - Cache-Control: public, max-age=31536000, immutable
  - ETag: "hash-timestamp"
         │
         ▼
[Binary Stream via readfile()]
```

Native images (JPG, PNG, GIF, SVG) are served directly by the web server without invoking PHP.

---

## 4. Theme System & Dynamic Design Tokens

The visual style of AB-Socks is driven by design tokens rather than ad-hoc inline styles.

### Single Source of Truth
- Base tokens are defined in the `:root` block of `assets/css/style.css`:
  ```css
  --color-bg, --color-surface, --color-text, --color-muted, --color-border,
  --color-primary, --color-primary-dark, --color-primary-light, --color-accent,
  --color-success, --color-danger,
  --radius-sm, --radius-md, --radius-lg,
  --shadow-sm, --shadow-md
  ```

### Database-Backed Theme Switcher
- The `themes` table stores named palettes.
- The `theme_tokens` table stores individual `(theme_id, token_group, token_key, token_value)` records.
- The active theme is tracked by the `active_theme_id` record in the `settings` table.
- Function `activeThemeCssVars()` in `app/core/settings.php` reads the active theme's tokens and injects an overriding `<style>:root { ... }</style>` block in `views/layout/header.php`.
- **Zero-Deploy Theme Switching**: An administrator can customize and switch between themes directly from `admin/themes.php` without touching CSS files or executing a deployment.
