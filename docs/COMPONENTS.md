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

## 1. JavaScript Client Utilities (`assets/js/ab-kit.js`)

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

---

### 1.3 Modals & Dialogs (`AB.modal` / Native `<dialog>`)

Modern admin workstations utilize the native HTML5 `<dialog>` element or declarative data-attributes:

```html
<!-- Trigger Button -->
<button type="button" class="ab-btn ab-btn--primary" data-ab-modal-open="editUserModal">
    ویرایش
</button>

<!-- Native Dialog Modal -->
<dialog id="editUserModal" class="ab-modal" data-ab-modal>
    <header class="ab-modal__header">
        <h3 class="ab-modal__title">عنوان مودال</h3>
        <button type="button" class="ab-icon-btn ab-icon-btn--sm" data-ab-modal-close aria-label="بستن"></button>
    </header>
    <div class="ab-modal__body">
        <!-- Content -->
    </div>
</dialog>
```
- Supports backdrop auto-closing when clicking outside dialog content.
- Backward compatibility: continues supporting legacy `div.admin-modal` structures.

---

### 1.4 Autocomplete & Live Tokenizer (`AB.autocomplete`)

Used for product search, tag assignment, customer lookup, and order linking.

**Declarative Usage:**
```html
<div class="ab-autocomplete" data-ab-autocomplete="tags" data-ab-multiple="true">
    <input type="text" class="ab-input" placeholder="جست‌وجوی تگ..." autocomplete="off">
    <div class="ab-autocomplete-suggestions" hidden></div>
    <div class="ab-autocomplete-tokens"></div>
</div>
```

---

### 1.5 Table & List Live Filtering (`AB.tableFilter`)

For instant client-side searching across table rows without reloading:

```html
<input type="search" class="ab-input" placeholder="فیلتر زنده..." data-ab-filter-target="#ordersTable">

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
    .then(data => AB.toast('عملیات با موفقیت انجام شد.'))
    .catch(err => AB.toast(err.message || 'خطا در ارتباط با سرور', 'error'));
```

---

### 1.7 Split View Workstation Layout (`AB.split`)

Manages master/detail collapsing on narrow screens:

```html
<div class="ab-split" data-split-view="master">
    <div class="ab-split__master">
        <button type="button" data-ab-split-select="pane-1">انتخاب آیتم ۱</button>
    </div>
    <div class="ab-split__detail">
        <button type="button" class="ab-split__back" data-ab-split-back>بازگشت</button>
        <div data-split-pane="pane-1">محتوا</div>
    </div>
</div>
```
- JS API: `AB.split.showDetail(container, detailId)`, `AB.split.showMaster(container)`.

---

### 1.8 Responsive Breakpoints (`AB.bp`)

Single source of truth constants mirrored in `assets/css/admin-tokens.css`:
- `AB.bp.sm = 640` (40rem)
- `AB.bp.md = 1024` (64rem)
- `AB.bp.lg = 1440` (90rem)

---

## 2. Server-side / PHP Presentation Components (`component()` & `render_component()`)

All reusable UI presentation components reside in `views/admin/components/` and are rendered using:
```php
component(string $name, array $props = []): void
$html = render_component(string $name, array $props = []): string
```
*Rule: Presentation components must have exactly 0 SQL queries, 0 DB mutations, and 0 `$_POST` access.*

---

### 2.1 KPI Card & Grid (`kpi_card` / `kpi_grid`)
- **Location**: `views/admin/components/kpi_card.php`, `views/admin/components/kpi_grid.php`
- **CSS**: `.ab-kpi-grid`, `.ab-kpi-card`, `container: ab-kpi / inline-size`
- **Props**:
  - `title` (string): Metric headline
  - `value` (string|int): Metric value
  - `unit` (string, optional): Suffix unit (e.g. تومان, کالا)
  - `sub` (string, optional): Explanatory note or sub-line
  - `trend` (string, optional): `'up'`, `'down'`, `'flat'`
  - `tone` (string, optional): `'brand'`, `'emerald'`, `'rose'`, `'sky'`, `'amber'`, `'purple'`, `'blue'` (aliased to old `color`)
  - `icon` (string, optional): SVG icon markup
  - `href` (string, optional): If provided, card renders as an `<a>` link (aliased to old `url`)
  - `raw_value` (bool, optional): If true, renders `$value` unescaped

---

### 2.2 Navigation Tabs (`nav_tabs`)
- **Location**: `views/admin/components/nav_tabs.php`
- **CSS**: `.ab-nav-tabs`, `.ab-tab-btn`, `.ab-tab-icon`, `.ab-tab-label`, `.ab-tab-badge`
- **Props**:
  - `tabs` (array): Array of tab definitions (`tab`, `label`, `icon`, `url`, `badge`, `badge_class`)
  - `activeTab` (string, optional): Key of currently active tab
  - `class` (string, optional): Additional class for nav container
  - `btnClass` (string, optional): Additional class for buttons
  - `ariaLabel` (string, optional): Accessibility label

---

### 2.3 Empty State (`empty_state`)
- **Location**: `views/admin/components/empty_state.php`
- **CSS**: `.ab-empty-state`, `.ab-empty-icon`, `.ab-empty-title`, `.ab-empty-desc`
- **Props**:
  - `title` (string): Primary message headline
  - `message` (string, optional): Secondary explanatory details
  - `icon` (string, optional): SVG icon markup
  - `action_url` (string, optional): Primary CTA link URL
  - `action_label` (string, optional): Primary CTA button label
  - `action_modal` (string, optional): Modal ID to open via `data-ab-modal-open`

---

### 2.4 Status Badge & Chip (`badge`)
- **Location**: `views/admin/components/badge.php`
- **CSS**: `.ab-badge`, `[data-state]`, `[data-tone]`, `.ab-badge-dot`, `.ab-chip`
- **Props**:
  - `label` / `text` (string): Badge label
  - `state` / `type` (string, optional): `'success'`, `'danger'`, `'warning'`, `'info'`, `'primary'`, `'muted'`
  - `tone` (string, optional): Domain tone override
  - `dot` (bool, optional): Displays pulsating status dot indicator
  - `icon` (string, optional): SVG icon markup

---

### 2.5 Button & Icon Button (`button`)
- **Location**: `views/admin/components/button.php`
- **CSS**: `.ab-btn`, `.ab-icon-btn`, `.ab-btn--{primary|secondary|outline|ghost|danger|success}`, `.ab-btn--{sm|lg}`
- **Props**:
  - `label` (string, optional): Button text
  - `variant` (string, optional): `'primary'` (default), `'secondary'`, `'outline'`, `'ghost'`, `'danger'`, `'success'`
  - `size` (string, optional): `'sm'`, `'md'` (default), `'lg'`
  - `icon` (string, optional): SVG icon markup
  - `icon_position` (string, optional): `'start'` (default) or `'end'`
  - `icon_only` (bool, optional): Renders `.ab-icon-btn` with aria-label
  - `href` (string, optional): If present, renders `<a>`, else `<button>`
  - `type` (string, optional): `'button'` (default), `'submit'`, `'reset'`
  - `disabled` (bool, optional): Disabled state
  - `busy` / `loading` (bool, optional): Spinner state (`aria-busy="true"`)
  - `attrs` (array|string, optional): Extra attributes

---

### 2.6 Form Field Family (`form_field`)
- **Location**: `views/admin/components/form_field.php`
- **CSS**: `.ab-field`, `.ab-input`, `.ab-select`, `.ab-textarea`, `.ab-switch`, `.ab-check`, `.ab-input-group`
- **Props**:
  - `name` (string): Field name
  - `label` (string, optional): Field label
  - `type` (string, optional): `'text'` (default), `'number'`, `'email'`, `'password'`, `'select'`, `'textarea'`, `'switch'`, `'check'`
  - `value` (mixed, optional): Current value
  - `options` (array, optional): Key/value options for select
  - `hint` (string, optional): Explanatory note
  - `error` (string, optional): Validation error message
  - `required` (bool, optional): Required indicator
  - `unit` / `prefix` / `suffix` (string, optional): Input group addon
  - `dir` (string, optional): `'ltr'` or `'rtl'` (defaults to ltr for numbers/emails)

---

### 2.7 Card Container (`card`)
- **Location**: `views/admin/components/card.php`
- **CSS**: `.ab-card`, `container: ab-card / inline-size`, `.ab-card__head`, `.ab-card__body`, `.ab-card__foot`
- **Props**:
  - `title` (string, optional): Card title
  - `subtitle` (string, optional): Card subtitle
  - `icon` (string, optional): SVG icon
  - `actions` (array|string, optional): Header buttons
  - `content` (string, optional): Body HTML
  - `footer` (array|string, optional): Footer actions or HTML
  - `flush` (bool, optional): Removes padding for full-bleed data tables
  - `body_class` (string, optional): Additional body class

---

### 2.8 Page Header (`page_header`)
- **Location**: `views/admin/components/page_header.php`
- **CSS**: `.ab-page-header`, `.ab-breadcrumbs`, `.ab-page-header__icon`, `.ab-page-header__actions`
- **Props**:
  - `title` (string): Main headline
  - `subtitle` (string, optional): Sub-headline
  - `icon` (string, optional): SVG icon markup
  - `actions` (array, optional): Array of button props
  - `breadcrumbs` (array, optional): `[['label' => '...', 'url' => '...']]`

---

### 2.9 Toolbar & Filtering (`toolbar`)
- **Location**: `views/admin/components/toolbar.php`
- **CSS**: `.ab-toolbar`, `.ab-toolbar__lead`, `.ab-toolbar__search`, `.ab-toolbar__filters`, `.ab-toolbar__actions`
- **Props**:
  - `search` (array, optional): `['name' => 'q', 'placeholder' => '...', 'target' => '#tableId']`
  - `filters` (array, optional): Array of form_field props or HTML
  - `actions` (array, optional): Action buttons
  - `bulk` (array, optional): `['options' => [...], 'target' => '#tableId']`

---

### 2.10 Pagination (`pagination`)
- **Location**: `views/admin/components/pagination.php`
- **CSS**: `.ab-pagination`, `.ab-pagination__info`, `.ab-pagination__nav`, `.ab-pagination__item`
- **Props**:
  - `page` (int): Current active page
  - `pages` (int): Total pages count
  - `base_url` (string): Link prefix (e.g. `?page=`)
  - `total` (int, optional): Total items count
  - `per_page` (int, optional): Items per page

---

### 2.11 Data Table (`data_table`)
- **Location**: `views/admin/components/data_table.php`
- **CSS**: `.ab-table-wrap`, `container: ab-table / inline-size`, `.ab-table`, `data-mode="stack|scroll"`
- **Props**:
  - `id` (string): Table ID
  - `mode` (string, optional): `'stack'` (default) or `'scroll'`
  - `columns` (array): `[['key' => string, 'label' => string, 'priority' => 1|2|3, 'type' => string, 'align' => string]]`
  - `rows` (array): Array of row assoc arrays
  - `row_attrs` (callable, optional): `fn($row): ['data-id' => ..., 'data-search' => ...]`
  - `cell` (array, optional): Key-to-callable custom cell renderers
  - `empty` (array, optional): Config for empty_state component
  - `sticky_head` (bool, optional): Sticky header (default true)
  - `selectable` (bool, optional): Row checkbox selection

---

### 2.12 Alert Banner (`alert`)
- **Location**: `views/admin/components/alert.php`
- **CSS**: `.ab-alert`, `[data-state="success|warning|danger|info"]`
- **Props**:
  - `state` (string): `'success'`, `'warning'`, `'danger'`, `'info'`
  - `title` (string, optional): Headline
  - `message` (string): Body text
  - `dismissible` (bool, optional): Close button toggle
  - `icon` (string, optional): Custom icon markup

---

### 2.13 Modal Dialog (`modal`)
- **Location**: `views/admin/components/modal.php`
- **CSS**: `dialog.ab-modal`, `dialog::backdrop`, `.ab-modal--{sm|md|lg|xl}`
- **Props**:
  - `id` (string): Unique dialog ID
  - `title` (string): Dialog title
  - `size` (string, optional): `'sm'`, `'md'` (default), `'lg'`, `'xl'`
  - `content` (string, optional): Body HTML
  - `footer_actions` (array|string, optional): Action buttons

---

### 2.14 Side Drawer (`drawer`)
- **Location**: `views/admin/components/drawer.php`
- **CSS**: `dialog.ab-drawer`, `[data-side="end|start"]`
- **Props**:
  - `id` (string): Unique drawer ID
  - `title` (string): Drawer title
  - `side` (string, optional): `'end'` (default) or `'start'`
  - `content` (string, optional): Body HTML
  - `footer_actions` (array|string, optional): Action buttons

---

### 2.15 Sticky Save Bar (`savebar`)
- **Location**: `views/admin/components/savebar.php`
- **CSS**: `.ab-savebar`, safe-area aware sticky footer
- **Props**:
  - `primary` (array|string): Primary submit button props
  - `secondary` (array|string, optional): Secondary button props
  - `dirty_hint` (string, optional): Explanatory note
  - `form_id` (string, optional): Linked form ID

---

### 2.16 Definition List (`definition_list`)
- **Location**: `views/admin/components/definition_list.php`
- **CSS**: `.ab-dl`, `.ab-dl--stacked`
- **Props**:
  - `items` (array): `[['label' => string, 'value' => mixed, 'raw' => bool|null]]`
  - `stacked` (bool, optional): Stacked layout mode

---

### 2.17 Progress Meter (`progress`)
- **Location**: `views/admin/components/progress.php`
- **CSS**: `.ab-progress-wrap`, `.ab-progress`, `.ab-progress__bar`
- **Props**:
  - `value` (numeric): Current progress
  - `max` (numeric, default 100): Maximum scale
  - `tone` (string, optional): Color tone
  - `label` (string, optional): Progress label
  - `show_percent` (bool, optional): Percentage indicator

---

## 3. Design Tokens & CSS Architecture

Styles follow strict CSS Cascade Layers (`@layer reset, tokens, base, layout, components, patterns, pages, utilities;`):
1. **`assets/css/admin-tokens.css`**: Primitives, semantic tokens, 9 domain tones (`brand`, `blue`, `sky`, `emerald`, `amber`, `rose`, `purple`, `teal`, `slate`). The **only** file permitted to contain `:root` and `#hex`.
2. **`assets/css/admin-base.css`**: Modern reset, element defaults, decoupled from storefront.
3. **`assets/css/admin-shell.css`**: Shell, topbar, sidebar, off-canvas, bottom nav, `.ab-page`.
4. **`assets/css/admin-components.css`**: v2 reusable component library. 100% token-driven, container queries.
5. **`assets/css/admin-patterns.css`**: Page archetypes (List, Dashboard, Form, Workstation).
6. **`assets/css/admin-utilities.css`**: Standard layout and typography utilities.

---

## 4. UI Kit Showcase

A live style guide and stress-test suite is accessible to authenticated administrators at:
`/admin/ui-kit.php` (linked from Diagnostics).
Renders every component across all states, tones, breakpoints, and edge cases.
