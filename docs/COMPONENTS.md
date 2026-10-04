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
| `AB.fmt.faDigits(str)` | `assets/js/ab-kit.js` | Converts English ASCII digits (0-9) to Persian numerals (۰-۹). | `AB.fmt.faDigits(12500)` → `"۱۲۵۰۰"` |
| `AB.fmt.bytes(bytes)` | `assets/js/ab-kit.js` | Formats byte sizes into readable Persian units (بایت، کیلوبایت، مگابایت). | `AB.fmt.bytes(1048576)` → `"۱.۰ مگابایت"` |
| `AB.fmt.esc(str)` | `assets/js/ab-kit.js` | Sanitizes strings to prevent XSS when inserting into DOM. | `AB.fmt.esc(userInput)` |
| `AB.fmt.price(amount, unit)` | `assets/js/ab-kit.js` | Formats numbers with Persian thousand separators + currency suffix. | `AB.fmt.price(45000)` → `"۴۵,۰۰۰ تومان"` |
| `AB.fmt.normalizeText(str)` | `assets/js/ab-kit.js` | Normalizes Arabic/Persian letters and numerals for fast substring matching. | `AB.fmt.normalizeText('جوراب')` |

*Rule: Never declare private `toPersianDigits`, `toFaDigits`, `formatBytes`, or `escapeHtml` inside page scripts or views.*

---

### 1.2 Feedback & Notifications (`AB.toast` & `window.showToast`)

| Function | Canonical Location | Description |
|---|---|---|
| `AB.toast(msg, type)` / `showToast(msg, type)` | `assets/js/ab-kit.js` | Displays an animated floating notification (toast) on the screen. `type`: `'success'` (default), `'error'`, `'warning'`, `'info'`. |

**Usage:**
```javascript
AB.toast('تغییرات با موفقیت ذخیره شد.', 'success');
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

### 1.6 Lightweight API & CSRF Fetch (`AB.api`)

Centralized AJAX helper with automatic CSRF header injection and JSON parsing:

```javascript
AB.api.post('/admin/ajax/some_action.php', { id: 42, status: 'active' })
    .then(data => {
        AB.toast('عملیات با موفقیت انجام شد.');
    })
    .catch(err => {
        AB.toast(err.message || 'خطا در ارتباط با سرور', 'error');
    });
```
- **Location**: `assets/js/ab-kit.js`
- **Features**: Automatically reads CSRF token from `<meta name="csrf-token">` or hidden inputs. Automatically handles `FormData` and JSON objects.

---

### 1.7 On-Browser Image Optimizer (`admin-image-optimizer.js`)

Offloads image resizing, WebP conversion, and EXIF stripping to the admin's browser before upload.
- **Location**: `assets/js/admin-image-optimizer.js` (loaded globally in `views/admin/layout/header.php`).
- **Features**: Automatic WebP compression, side-by-side zoom/pan inspector, HEIC/HEIF client decoding.
- **Usage**: Automatically activates on file inputs with `data-optimize-image` or `.aio-input`.

---

### 1.8 Modular Workstation Scripts (`assets/js/admin-*.js`)

Views must not contain heavy inline `<script>` blocks (enforced by `tools/verify.php` with a hard limit of 20 lines). Server state is passed declaratively via `<script type="application/json" id="...">` data islands, and all client interactions are maintained in modular external scripts loaded with cache-busting `asset()`:

| Script | View | Server Data Contract | Description |
|---|---|---|---|
| `assets/js/admin-orders.js` | `views/admin/orders.php` | `<script id="ordersDataMap" type="application/json">` | Side dossier drawer, receipt modal, postal label copy, quick filter chips. |
| `assets/js/admin-c2c.js` | `views/admin/card_to_card_payments.php` | `<script id="c2cOrdersMap" type="application/json">` | Split-view inspection workstation, receipt zoom/rotate, rejection modal, manual upload, batch actions, keyboard shortcuts (`[`, `]`, `a`, `r`). |
| `assets/js/admin-order-detail.js` | `views/admin/order_detail.php` | None (reads DOM data attributes) | Product gallery lightbox (swipe & keyboard navigation), receipt zoom modal, postal label copy, smooth status anchor scroll. |
| `assets/js/admin-product-edit.js` | `views/admin/product_edit.php` | `<script id="productEditTagData" type="application/json">` | Dynamic variant rows, global pricing strategy sync, live profit margin calculator, tag tokenizer binding. |

---

## 2. Server-side / PHP Presentation Components (`component()`)

All reusable UI presentation components reside in `views/admin/components/` and are rendered using the global helper:
```php
component(string $name, array $props = []): void
```
*Rule: Presentation components must have exactly 0 SQL queries, 0 DB mutations, and 0 `$_POST` access.*

### 2.1 KPI Card & Grid (`component('kpi_card')` / `component('kpi_grid')`)
- **Location**: `views/admin/components/kpi_card.php`, `views/admin/components/kpi_grid.php`
- **CSS Tokens & Classes**: `.ab-kpi-grid`, `.ab-kpi-card`, `.theme-{primary|emerald|rose|sky|amber|purple|blue}`
- **Card Props**:
  - `title` (string): Metric headline
  - `value` (string|int): Metric value
  - `unit` (string, optional): Suffix unit (e.g. تومان, کالا)
  - `sub` (string, optional): Explanatory note or sub-line (HTML supported)
  - `color` (string, optional): Theme color (`'primary'`, `'emerald'`, `'rose'`, `'sky'`, `'amber'`, `'purple'`, `'blue'`)
  - `icon` (string, optional): SVG icon markup or emoji
  - `url` (string, optional): If provided, card renders as an `<a>` link
  - `raw_value` (bool, optional): If true, renders `$value` unescaped for custom spans
- **Usage**:
  ```php
  component('kpi_card', [
      'title' => 'فروش کل',
      'value' => formatPrice(1250000),
      'sub'   => 'رشد ۱۰٪ نسبت به ماه گذشته',
      'color' => 'emerald',
      'icon'  => '<svg ...>...</svg>',
  ]);
  ```

### 2.2 Master Navigation Tabs (`component('nav_tabs')`)
- **Location**: `views/admin/components/nav_tabs.php`
- **CSS Tokens & Classes**: `.ab-nav-tabs`, `.ab-tab-btn`, `.ab-tab-icon`, `.ab-tab-label`, `.ab-tab-badge`
- **Props**:
  - `tabs` (array): Array of tab definitions (`tab`, `label`, `icon`, `url`, `badge`, `badge_class`)
  - `activeTab` (string, optional): Key of currently active tab
  - `class` (string, optional): Additional class for nav container
  - `btnClass` (string, optional): Additional class for buttons
  - `ariaLabel` (string, optional): Accessibility label
- **Usage**:
  ```php
  component('nav_tabs', [
      'tabs'      => [
          ['tab' => 'orders', 'label' => 'عملیات سفارش', 'icon' => '<svg ...>...</svg>'],
          ['tab' => 'finance', 'label' => 'گزارش‌های مالی', 'icon' => '<svg ...>...</svg>'],
      ],
      'activeTab' => 'orders',
      'ariaLabel' => 'تب‌های تنظیمات',
  ]);
  ```

### 2.3 Empty State (`component('empty_state')`)
- **Location**: `views/admin/components/empty_state.php`
- **CSS Tokens & Classes**: `.ab-empty-state`, `.ab-empty-icon`, `.ab-empty-title`, `.ab-empty-desc`
- **Props**:
  - `title` (string): Primary message headline
  - `message` (string, optional): Secondary explanatory details
  - `icon` (string, optional): SVG icon markup or emoji (default provided)
  - `action_url` (string, optional): Primary CTA link URL
  - `action_label` (string, optional): Primary CTA button label
  - `action_modal` (string, optional): Modal ID to open via `data-ab-modal-open`
- **Usage**:
  ```php
  component('empty_state', [
      'icon'         => '🔍',
      'title'        => 'سفارشی با این مشخصات یافت نشد',
      'message'      => 'می‌توانید فیلترها را ریست کنید.',
      'action_url'   => 'orders.php',
      'action_label' => 'نمایش همه سفارش‌ها',
  ]);
  ```

### 2.4 Status Badge (`component('badge')`)
- **Location**: `views/admin/components/badge.php`
- **CSS Tokens & Classes**: `.ab-badge`, `.ab-badge-{success|danger|warning|info|primary|muted}`
- **Props**:
  - `text` (string): Badge label
  - `type` (string, optional): Variant (`'success'`, `'danger'`, `'warning'`, `'info'`, `'primary'`, `'muted'`)
  - `dot` (bool, optional): Displays pulsating status dot indicator
  - `icon` (string, optional): SVG icon markup
- **Usage**:
  ```php
  component('badge', ['text' => 'فعال', 'type' => 'success', 'dot' => true]);
  component('badge', ['text' => 'منقضی شده', 'type' => 'warning']);
  ```

### 2.5 Search Provider Registry (`SearchService`)
- Centralized server-side registry for entity search across pages, orders, products, tags, and customers.
- **Location**: `app/services/SearchService.php`
- **Dispatcher Endpoint**: `ajax/admin_search.php`
- **Usage**:
  ```php
  // Search specific entity:
  $results = SearchService::search($query, 'tags', 15);

  // Register a custom provider:
  SearchService::registerProvider('suppliers', function(string $q, int $limit) {
      return [...];
  });
  ```

### 2.6 Automated Cache-Busting Asset Helper (`asset()`)
- Eliminates manual version variables (`$styleCssVer`, `$adminCssVer`, etc.) by inspecting the file modification timestamp (`filemtime`) on disk.
- **Location**: `app/core/functions.php`
- **Signature**: `asset(string $path): string` / `assetUrl(string $path): string`
- **Usage**:
  ```php
  <link rel="stylesheet" href="<?= e(asset('/assets/css/admin.css')) ?>">
  <script src="<?= e(asset('/assets/js/ab-kit.js')) ?>"></script>
  ```
- **Output**: `/assets/css/admin.css?v=1.32.1.1728012345`

---

## 3. Design Tokens & CSS Architecture

All administrative styles follow a 3-tier stylesheet hierarchy:
1. **`assets/css/admin.css`**: Core admin shell, collapsible sidebar, header, global typography, and canonical `:root` design tokens.
2. **`assets/css/admin-components.css`**: Shared AB-Kit components (toasts, modals, autocomplete, bento KPI cards/grids, nav tabs, empty states, badges).
3. **`assets/css/admin-*.css`**: Workstation-specific layout and bespoke tables, loaded conditionally per page via `$pageCssMap` in `views/admin/layout/header.php`.

### 3.1 Core Palette Tokens (`admin.css`)
```css
:root {
    --brand-gradient: linear-gradient(135deg, #C46C46 0%, #8C472E 100%);
    --color-primary: #C46C46;
    --color-primary-hover: #AB5935;
    --color-primary-dark: #8C472E;
    --color-primary-light: rgba(196, 108, 70, 0.1);
    --color-accent: #B89180;
    --color-surface: #FFFFFF;
    --color-surface-hover: #F8FAFC;
    --color-bg: #F3F1EC;
    --color-text: #1E293B;
    --color-text-muted: #64748B;
    --color-border: #E2E8F0;
    --color-border-subtle: #F1F5F9;

    /* Semantic States */
    --color-success: #10B981;
    --color-danger: #EF4444;
    --color-warning: #F59E0B;
    --color-info: #3B82F6;
}
```

### 3.2 Radii & Elevation
```css
:root {
    --radius-xs: 4px;
    --radius-sm: 6px;
    --radius-md: 10px;
    --radius-lg: 16px;
    --radius-xl: 20px;
    --radius-pill: 9999px;

    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
    --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
```

*Rule: Never define a new `:root` block with competing palette values inside `admin-*.css` page stylesheets. Always inherit or alias canonical tokens.*

---

## 4. How to Register a New Reusable Component

When developing a pattern needed in ≥ 2 places:
1. **Extract**: Place JS in a shared module (`assets/js/ab-kit.js` or `assets/js/`), CSS in `assets/css/admin-components.css`, and PHP presentation components in `views/admin/components/`.
2. **Document**: Add an entry to this document (`docs/COMPONENTS.md`) specifying the component name, location, and markup contract.
3. **Verify**: Run `php tools/verify.php` to ensure zero syntax errors and that the ratchet baseline is maintained.

