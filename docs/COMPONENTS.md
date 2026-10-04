# AB-Socks UI & Component Registry (Single Source of Truth)

This catalog is the definitive inventory of reusable UI components, JavaScript utilities,
PHP view helpers, and design tokens in the AB-Socks project.

> [!IMPORTANT]
> **Reuse-First Protocol (`AGENTS.md` Rule 11)**:
> Before writing any new UI interaction, formatter, modal, search field, or component,
> consult this document. Re-implementing existing functionality is strictly prohibited.
> If a UI interaction or utility pattern is needed across two or more places, it MUST
> be extracted to a shared component and registered in this catalog.

---

## 1. JavaScript Client Utilities

Admin views must **never** contain heavy inline `<script>` tags (hard limit 20 lines).
Interactions are driven by declarative HTML data-attributes (`data-ab-*`) or shared functions.

### 1.1 Formatters & Escaping (`AB.fmt` / Canonical Helpers)

| Function | Canonical Location | Description | Example Usage |
|---|---|---|---|
| `toFaDigits(str)` | `assets/js/admin.js` | Converts English ASCII digits (0-9) to Persian numerals (۰-۹). | `toFaDigits(12500)` → `"۱۲۵۰۰"` |
| `formatBytes(bytes)` | `assets/js/admin-image-optimizer.js` | Formats byte sizes into readable Persian units (بایت، کیلوبایت، مگابایت). | `formatBytes(1048576)` → `"۱.۰ مگابایت"` |
| `escapeHtml(str)` | `assets/js/admin.js` | Sanitizes strings to prevent XSS when inserting into DOM. | `escapeHtml(userInput)` |
| `formatPrice(amount)` | `assets/js/admin.js` | Formats numbers with Persian thousand separators + تومان suffix. | `formatPrice(45000)` → `"۴۵,۰۰۰ تومان"` |

*Rule: Never declare private `toPersianDigits`, `toFaDigits`, `formatBytes`, or `escapeHtml` inside page scripts or views.*

---

### 1.2 Feedback & Notifications (`AB.toast`)

| Function | Canonical Location | Description |
|---|---|---|
| `showToast(msg, type)` | `assets/js/admin.js` | Displays an animated floating notification (toast) on the screen. `type`: `'success'` (default) or `'error'`. |

**Usage:**
```javascript
showToast('تغییرات با موفقیت ذخیره شد.', 'success');
showToast('خطا در برقراری ارتباط با سرور.', 'error');
```
*Rule: Do not create page-specific toast DOM structures or custom toast functions.*

---

### 1.3 Modals & Dialogs (`AB.modal` / Declarative Dialogs)

Modern admin workstations utilize the native HTML5 `<dialog>` element or declarative data-attributes:

```html
<!-- Trigger Button -->
<button type="button" class="btn btn-primary" data-ab-modal-open="editUserModal">
    ویرایش
</button>

<!-- Modal Container -->
<div id="editUserModal" class="admin-modal" data-ab-modal hidden>
    <div class="admin-modal-backdrop" data-ab-modal-close></div>
    <div class="admin-modal-content">
        <header class="admin-modal-header">
            <h3>عنوان مودال</h3>
            <button type="button" class="btn-close" data-ab-modal-close aria-label="بستن"></button>
        </header>
        <div class="admin-modal-body">
            <!-- Content -->
        </div>
    </div>
</div>
```
*Rule: Do not write bespoke `openModal()` and `closeModal()` boilerplate in page views.*

---

### 1.4 Autocomplete & Live Tokenizer (`AB.autocomplete`)

Used for product search, tag assignment, customer lookup, and order linking.

**Declarative Usage:**
```html
<div class="ab-autocomplete" data-ab-autocomplete="tags" data-ab-multiple="true">
    <input type="text" class="form-input" placeholder="جست‌وجوی تگ..." autocomplete="off">
    <div class="ab-autocomplete-suggestions" hidden></div>
    <!-- Token container for multiple selections -->
    <div class="ab-autocomplete-tokens"></div>
</div>
```
- **Backend Provider**: Handled centrally via `ajax/admin_search.php?type=tags&q=...`.
- *Rule: Do not roll custom live-search input listeners or debounce functions inside views.*

---

### 1.5 Table & List Live Filtering (`AB.tableFilter`)

For instant client-side searching across table rows without reloading:

```html
<!-- Search Input -->
<input type="search" class="form-input" placeholder="فیلتر زنده..." data-ab-filter-target="#ordersTable">

<!-- Table with data-search rows -->
<table id="ordersTable">
    <tbody>
        <tr data-search="علی رضایی 1042 تهران">...</tr>
        <tr data-search="زهرا حسینی 1043 مشهد">...</tr>
    </tbody>
</table>
```

---

### 1.6 On-Browser Image Optimizer (`admin-image-optimizer.js`)

Offloads image resizing, WebP conversion, and EXIF stripping to the admin's browser before upload.
- **Location**: `assets/js/admin-image-optimizer.js` (loaded globally in `views/admin/layout/header.php`).
- **Features**: Automatic WebP compression, side-by-side zoom/pan inspector, HEIC/HEIF client decoding.
- **Usage**: Automatically activates on file inputs with `data-optimize-image` or `.aio-input`.

---

## 2. Server-side / PHP Presentation Components

Reusable view templates live in `views/admin/` or partial folders:

### 2.1 Summary KPI Cards
- Standard KPI grids display total counts, financial sums, or status metrics:
  ```php
  <?php require __DIR__ . '/coupons_partials/_kpis.php'; ?>
  ```
- **Structure**: Always uses `.admin-kpi-card`, `.kpi-icon`, `.kpi-label`, `.kpi-value`, `.kpi-trend`.

### 2.2 Navigation Sub-tabs
- Workstation sub-navigation tab-bar:
  ```php
  <?php require __DIR__ . '/settings_partials/_nav_tabs.php'; ?>
  ```
- **Structure**: Uses `.admin-subnav-pills` or `.admin-tabs` with `role="tablist"`.

---

## 3. Design Tokens & CSS Variables (`assets/css/admin.css`)

All colors, elevations, borders, and glassmorphism styling must strictly reference `:root` tokens:

### 3.1 Core Color Tokens
```css
--color-bg          /* Canvas background */
--color-surface     /* Cards and containers */
--color-text        /* Primary text */
--color-muted       /* Secondary labels and muted text */
--color-border      /* Borders and dividers */
--color-primary     /* Brand accent and primary buttons */
--color-success     /* Positive states and green indicators */
--color-danger      /* Errors, warnings, and destructive actions */
--color-warning     /* Cautionary alerts */
```

### 3.2 Glass & Elevation
```css
--radius-sm: 6px;
--radius-md: 10px;
--radius-lg: 16px;
--shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
--shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
--shadow-lg: 0 12px 32px rgba(0, 0, 0, 0.12);
```

*Rule: Never define a new `:root` block inside `admin-*.css` page stylesheets. Never hardcode arbitrary hex colors when a theme token exists.*

---

## 4. How to Register a New Reusable Component

When developing a pattern needed in ≥ 2 places:
1. **Extract**: Place JS in a shared module (`assets/js/` or `assets/js/ab-kit/`), CSS in `assets/css/admin.css`, and PHP partials in `views/admin/partials/` or `views/admin/components/`.
2. **Document**: Add an entry to this document (`docs/COMPONENTS.md`) specifying the component name, location, and markup contract.
3. **Verify**: Run `php tools/verify.php` to ensure zero syntax errors and that the ratchet baseline is maintained.
