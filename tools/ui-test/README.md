# AB-Socks Admin UI Playwright Audit Harness

Dev-only automated testing suite for inspecting visual responsiveness, layout containment, RTL overflow, and mobile usability across the AB-Socks admin panel.

> **Note:** This is a developer-only harness and is **never deployed to production** (excluded from release packages by `tools/build-deploy.ps1`). Production hosting remains strictly framework-free with zero build steps or Node dependencies.

---

## 1. Quick Start

### Prerequisites
- Node.js 18+ installed on your machine.
- Local PHP development server running the AB-Socks application (e.g. `http://localhost:8080`).

### Installation

```bash
cd tools/ui-test
npm install
npx playwright install chromium
```

### Running the Audit

```bash
# Basic run with default credentials against local dev server
npm run ui:audit

# Or specify custom host and credentials via environment variables
AB_BASE_URL="http://localhost:8080" AB_ADMIN_USER="admin" AB_ADMIN_PASS="your_password" node audit.mjs
```

---

## 2. Configuration & Parameter Customization

### Page Parameter Overrides (`pages.local.json`)
Certain admin pages require dynamic entity IDs (such as `product_edit.php?id=1` or `order_detail.php?id=1`).

To test against your specific database records without modifying committed files, create a gitignored `pages.local.json` in `tools/ui-test/`:

```json
{
  "defaults": {
    "product_id": 42,
    "order_id": 105,
    "expense_id": 12,
    "gift_item_id": 3,
    "shipping_method_id": 2,
    "sms_pattern_id": 5,
    "theme_id": 1,
    "email_id": 1
  }
}
```

Alternatively, supply individual IDs via environment variables:
`AB_PRODUCT_ID=42 AB_ORDER_ID=105 node audit.mjs`

---

## 3. Targeted Audits (Flags & Filters)

### Restrict to Specific Pages
Use `--pages` (comma-separated page IDs matching `pages.json`):

```bash
node audit.mjs --pages dashboard,orders,product_edit,users
```

### Restrict to Specific Viewports
Use `--viewports` (comma-separated viewport IDs):

```bash
node audit.mjs --viewports iphone,laptop,android-s
```

### Headed Mode (Watch Browser Execution)
```bash
node audit.mjs --pages orders --viewports iphone --headed
```

---

## 4. Supported Viewport Matrix

| Viewport ID | Resolution | Characteristics |
|---|---|---|
| `android-s` | 360 × 740 | Compact mobile phone, touch enabled |
| `iphone` | 390 × 844 | iPhone 12/13/14, touch, safe-area inset |
| `tablet` | 768 × 1024 | Portrait tablet, touch |
| `md-edge` | 1024 × 768 | Desktop/tablet breakpoint boundary (`md`) |
| `laptop` | 1280 × 800 | 13-14" laptop / 1920 @ 150% OS display scaling |
| `desktop` | 1440 × 900 | Standard desktop display |
| `fhd` | 1920 × 1080 | Full HD monitor |
| `zoom200` | 960 × 540 | Effective resolution at 200% browser zoom |
| `zoom300` | 640 × 360 | Effective resolution at 300% browser zoom |
| `zoom50` | 3840 × 2160 | 4K display / 50% browser zoom |
| `laptop_font125` / `200` | 1280 × 800 | Testing rem-based accessibility text scaling |
| `iphone_font125` / `200` | 390 × 844 | Mobile rem-based accessibility text scaling |

---

## 5. Audit Checks & Acceptance Criteria

1. **Zero Horizontal Scroll Overflow (RTL-aware):**
   Validates `document.documentElement.scrollWidth <= document.documentElement.clientWidth`. Detects offending elements leaking outside the viewport boundaries and logs their CSS selector and coordinates.
2. **Zero Uncaught Exceptions & Console Errors:**
   Captures `console.error`, unhandled window errors, and failed same-origin HTTP requests (4xx/5xx).
3. **Mobile Tap Target Sizing (Touch Viewports):**
   Verifies interactive controls (`a`, `button`, `input`, `select`) provide at least 44×44px hit areas.
4. **Text Legibility:**
   Scans visible text nodes for sub-12px computed font size.
5. **Sticky Bar Clearance:**
   Checks that fixed navigation and action bars (`.admin-bottom-nav`, `.ab-savebar`) do not obscure content when scrolled to the bottom.
6. **Full-page Screenshots:**
   Saves full-page `.png` captures of every audited page × viewport permutation under `output/<timestamp>/`.

---

## 6. Audit Output

Reports and screenshots are saved to:
`tools/ui-test/output/<timestamp>/`

- `report.md`: Markdown summary matrix and detailed failure breakdowns.
- `report.json`: Machine-readable results data.
- `*.png`: Full-page screenshots for visual inspection.
