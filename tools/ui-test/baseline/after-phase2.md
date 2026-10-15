# AB-Socks Admin UI Stabilization Gate — After Phase 2 Audit Report

- **Date:** 2026-10-15T22:40:12.812Z
- **Baseline Version:** `v1.34.4` (Phase 2.5b Stabilization Gate)
- **Target URL:** `https://absocks.test`
- **Total Pages Audited:** 34
- **Viewports per Page:** 14 (10 resolution viewports + 4 font-scale stress tests)
- **Total Test Assertions:** 476
- **Critical Failures Detected:** 131
- **Pages with Zero Overflow (Clean):** 4 / 34 (داشبورد (`dashboard`), ارزش‌گذاری انبار (`inventory_valuation`), ورود به مدیریت (`login`), راهنمای استایل و کیت رابط کاربری (`ui_kit`))

---

## 1. Executive Summary & Diff vs Phase 0 Baseline

| Metric | Phase 0 Baseline (Oct 5) | Phase 2.5b Gate (Current) | Variance / Analysis |
|---|---|---|---|
| **Total Tests** | 462 | 476 | +14 (Addition of `ui_kit` page × 14 viewports) |
| **HTTP 404/500 Failures** | 28 (order 1 missing) | 0 | Resolved via `pages.local.json` entity ID configuration |
| **Timeout Failures** | 3 (coupons, appearance, diagnostics) | 0 | Resolved via 25s timeout & stable local test server |
| **UI Kit Coverage** | N/A | 14/14 PASS (0 errors, 0 overflow) | 100% clean across all 14 viewports and font scales |
| **Shell Regressions** | Topbar z-index collision | 0 (Fixed) | Aligned legacy topbar z-index to `var(--z-sticky)` |
| **Pages with Overflow** | Unmeasured (hidden behind 404s/timeouts) | 30 / 34 | Fully measured & prioritized for Phase 3/4 migrations |

---

## 2. Prioritized Overflow & Migration Queue

The following table provides the exact prioritized sequence for Phase 3 (Pilot) and Phase 4 (Batch Migrations), ranked by horizontal overflow severity and mobile-criticality:

| Rank | Page ID | Page Name | Archetype | Mobile-Critical | Overflowing Viewports Count | Overflowing Viewport IDs |
|---|---|---|---|---|---|---|
| 1 | `product_edit` | **ویرایش محصول** | form | ⭐ YES | 10 / 14 | `android-s`, `iphone`, `tablet`, `md-edge`, `zoom200`, `zoom300`, `laptop_font125`, `laptop_font200`, `iphone_font125`, `iphone_font200` |
| 2 | `shipping_methods` | **روش‌های ارسال** | list | No | 8 / 14 | `android-s`, `iphone`, `md-edge`, `zoom300`, `laptop_font125`, `laptop_font200`, `iphone_font125`, `iphone_font200` |
| 3 | `themes` | **قالب‌ها** | list | No | 7 / 14 | `android-s`, `iphone`, `md-edge`, `laptop_font125`, `laptop_font200`, `iphone_font125`, `iphone_font200` |
| 4 | `card_to_card_payments` | **فیش‌های کارت‌به‌کارت** | workstation | ⭐ YES | 6 / 14 | `android-s`, `iphone`, `zoom300`, `laptop_font200`, `iphone_font125`, `iphone_font200` |
| 5 | `pricing` | **تغییر قیمت گروهی** | workstation | No | 6 / 14 | `android-s`, `iphone`, `zoom300`, `laptop_font200`, `iphone_font125`, `iphone_font200` |
| 6 | `tags` | **برچسب‌ها و مشخصات** | list | No | 5 / 14 | `android-s`, `iphone`, `laptop_font200`, `iphone_font125`, `iphone_font200` |
| 7 | `gift_items` | **هدایای سبد و جانبی** | list | No | 5 / 14 | `android-s`, `iphone`, `laptop_font200`, `iphone_font125`, `iphone_font200` |
| 8 | `gift_item_edit` | **ویرایش هدیه سبد** | form | No | 5 / 14 | `android-s`, `iphone`, `laptop_font200`, `iphone_font125`, `iphone_font200` |
| 9 | `finance_dashboard` | **داشبورد مالی** | dashboard | No | 5 / 14 | `android-s`, `iphone`, `laptop_font200`, `iphone_font125`, `iphone_font200` |
| 10 | `theme_edit` | **ویرایش قالب** | form | No | 5 / 14 | `android-s`, `md-edge`, `laptop_font125`, `laptop_font200`, `iphone_font200` |
| 11 | `email_accounts` | **حساب‌های ایمیل** | list | No | 5 / 14 | `md-edge`, `laptop_font125`, `laptop_font200`, `iphone_font125`, `iphone_font200` |
| 12 | `order_receipt` | **رسید چاپ سفارش** | print | No | 4 / 14 | `android-s`, `iphone`, `iphone_font125`, `iphone_font200` |
| 13 | `expenses` | **هزینه‌ها** | list | No | 4 / 14 | `android-s`, `iphone`, `iphone_font125`, `iphone_font200` |
| 14 | `sms_pattern_edit` | **ویرایش الگوی پیامک** | form | No | 4 / 14 | `md-edge`, `laptop_font125`, `laptop_font200`, `iphone_font200` |
| 15 | `order_detail` | **جزئیات سفارش** | workstation | ⭐ YES | 3 / 14 | `md-edge`, `laptop_font200`, `iphone_font200` |
| 16 | `expense_edit` | **ویرایش هزینه** | form | No | 3 / 14 | `android-s`, `iphone_font125`, `iphone_font200` |
| 17 | `settings` | **تنظیمات عمومی** | form | No | 3 / 14 | `md-edge`, `laptop_font125`, `laptop_font200` |
| 18 | `appearance` | **ظاهر و صفحه اصلی** | form | No | 3 / 14 | `md-edge`, `laptop_font125`, `laptop_font200` |
| 19 | `shipping_method_edit` | **ویرایش روش ارسال** | form | No | 3 / 14 | `md-edge`, `laptop_font125`, `laptop_font200` |
| 20 | `sms_patterns` | **الگوهای پیامک** | list | No | 3 / 14 | `md-edge`, `laptop_font125`, `laptop_font200` |
| 21 | `emails` | **ایمیل‌ها و وب‌میل** | workstation | No | 3 / 14 | `md-edge`, `laptop_font125`, `laptop_font200` |
| 22 | `email_read` | **مشاهده ایمیل** | workstation | No | 3 / 14 | `md-edge`, `laptop_font125`, `laptop_font200` |
| 23 | `email_compose` | **ارسال ایمیل جدید** | workstation | No | 3 / 14 | `md-edge`, `laptop_font125`, `laptop_font200` |
| 24 | `users` | **مدیران سایت** | list | No | 3 / 14 | `md-edge`, `laptop_font125`, `laptop_font200` |
| 25 | `diagnostics` | **عیب‌یابی و لاگ** | workstation | No | 3 / 14 | `md-edge`, `laptop_font125`, `laptop_font200` |
| 26 | `notifications_log` | **گزارش پیامک‌ها و اعلان‌ها** | list | No | 3 / 14 | `md-edge`, `laptop_font125`, `laptop_font200` |
| 27 | `orders` | **سفارش‌ها** | workstation | ⭐ YES | 2 / 14 | `laptop_font200`, `iphone_font200` |
| 28 | `products` | **همه محصولات** | list | No | 1 / 14 | `laptop_font200` |
| 29 | `categories` | **دسته‌بندی‌ها** | list | No | 1 / 14 | `laptop_font200` |
| 30 | `coupons` | **کدهای تخفیف** | list | No | 1 / 14 | `laptop_font200` |

---

## 3. Pages with Zero Overflow (Clean Foundation)

| Page ID | Page Name | Archetype | Result | Notes |
|---|---|---|---|---|
| `dashboard` | **داشبورد** | dashboard | ✅ 100% PASS (14/14) | Zero overflow, zero console errors across all viewports |
| `inventory_valuation` | **ارزش‌گذاری انبار** | dashboard | ✅ 100% PASS (14/14) | Zero overflow, zero console errors across all viewports |
| `login` | **ورود به مدیریت** | workstation | ✅ 100% PASS (14/14) | Zero overflow, zero console errors across all viewports |
| `ui_kit` | **راهنمای استایل و کیت رابط کاربری** | list | ✅ 100% PASS (14/14) | Zero overflow, zero console errors across all viewports |

---

## 4. Shell & Navigation Regression Resolution

During Phase 2.5b audit execution, two specific shell regressions were diagnosed and verified:
1. **Topbar Overlay Collision:** Legacy `admin.css` had unlayered `z-index: 100` on `.dash-topbar`, which was higher than `var(--z-overlay): 60`. This caused the topbar to sit atop the mobile off-canvas drawer and intercept touch/pointer events on `#adminSidebarCloseBtn`.
   - *Fix:* Replaced `z-index: 100` with `z-index: var(--z-sticky)` in `assets/css/admin.css`.
2. **Mobile Nav & Drawer Verification:** Verified using Playwright on mobile phone viewports (`390x844`, touch enabled) across 4 key legacy hubs (`orders`, `products`, `finance_dashboard`, `settings`):
   - Hamburger button (`#adminMobileMenuBtn`) opens drawer reliably.
   - Drawer toggle adds `.is-open` and opens off-canvas sidebar.
   - Close button (`#adminSidebarCloseBtn`) and backdrop click dismiss the drawer.
   - Mobile bottom nav (`.admin-bottom-nav`) renders and navigates without obstruction.

---

## 5. Full Playwright Matrix Log

## Matrix Summary

| Page | Archetype | Viewport | Status | Overflow | Console Errors | Tap Target Issues | Notes |
|---|---|---|---|---|---|---|---|
| **داشبورد** (`dashboard`) | dashboard | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **داشبورد** (`dashboard`) | dashboard | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **داشبورد** (`dashboard`) | dashboard | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **داشبورد** (`dashboard`) | dashboard | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد** (`dashboard`) | dashboard | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد** (`dashboard`) | dashboard | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد** (`dashboard`) | dashboard | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد** (`dashboard`) | dashboard | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد** (`dashboard`) | dashboard | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد** (`dashboard`) | dashboard | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد** (`dashboard`) | dashboard | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد** (`dashboard`) | dashboard | `laptop_font200` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد** (`dashboard`) | dashboard | `iphone_font125` | ✅ PASS | No | 0 | 10 | - |
| **داشبورد** (`dashboard`) | dashboard | `iphone_font200` | ✅ PASS | No | 0 | 0 | - |
| **سفارش‌ها** (`orders`) | workstation | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **سفارش‌ها** (`orders`) | workstation | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **سفارش‌ها** (`orders`) | workstation | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **سفارش‌ها** (`orders`) | workstation | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **سفارش‌ها** (`orders`) | workstation | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **سفارش‌ها** (`orders`) | workstation | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **سفارش‌ها** (`orders`) | workstation | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **سفارش‌ها** (`orders`) | workstation | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **سفارش‌ها** (`orders`) | workstation | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **سفارش‌ها** (`orders`) | workstation | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **سفارش‌ها** (`orders`) | workstation | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **سفارش‌ها** (`orders`) | workstation | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1434px) | 0 | 0 | - |
| **سفارش‌ها** (`orders`) | workstation | `iphone_font125` | ✅ PASS | No | 0 | 10 | - |
| **سفارش‌ها** (`orders`) | workstation | `iphone_font200` | ❌ FAIL | ⚠️ Yes (448px) | 0 | 10 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `android-s` | ❌ FAIL | ⚠️ Yes (661px) | 0 | 8 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `iphone` | ❌ FAIL | ⚠️ Yes (661px) | 0 | 8 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `tablet` | ✅ PASS | No | 0 | 9 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `zoom300` | ❌ FAIL | ⚠️ Yes (665px) | 0 | 0 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1352px) | 0 | 0 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `iphone_font125` | ❌ FAIL | ⚠️ Yes (701px) | 0 | 6 | - |
| **فیش‌های کارت‌به‌کارت** (`card_to_card_payments`) | workstation | `iphone_font200` | ❌ FAIL | ⚠️ Yes (822px) | 0 | 5 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `md-edge` | ❌ FAIL | ⚠️ Yes (1118px) | 0 | 0 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `laptop` | ❌ FAIL | No | 5 | 0 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `desktop` | ❌ FAIL | No | 1 | 0 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `fhd` | ❌ FAIL | No | 1 | 0 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `zoom200` | ❌ FAIL | No | 1 | 0 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `zoom300` | ❌ FAIL | No | 1 | 0 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `zoom50` | ❌ FAIL | No | 1 | 0 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `laptop_font125` | ❌ FAIL | No | 1 | 0 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1621px) | 1 | 0 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `iphone_font125` | ❌ FAIL | No | 1 | 10 | - |
| **جزئیات سفارش** (`order_detail`) | workstation | `iphone_font200` | ❌ FAIL | ⚠️ Yes (546px) | 1 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `android-s` | ❌ FAIL | ⚠️ Yes (1200px) | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `iphone` | ❌ FAIL | ⚠️ Yes (1200px) | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `tablet` | ✅ PASS | No | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `laptop_font200` | ✅ PASS | No | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `iphone_font125` | ❌ FAIL | ⚠️ Yes (1200px) | 0 | 0 | - |
| **رسید چاپ سفارش** (`order_receipt`) | print | `iphone_font200` | ❌ FAIL | ⚠️ Yes (1200px) | 0 | 0 | - |
| **همه محصولات** (`products`) | list | `android-s` | ❌ FAIL | No | 1 | 10 | Sticky bar overlap |
| **همه محصولات** (`products`) | list | `iphone` | ❌ FAIL | No | 1 | 10 | Sticky bar overlap |
| **همه محصولات** (`products`) | list | `tablet` | ❌ FAIL | No | 1 | 10 | Sticky bar overlap |
| **همه محصولات** (`products`) | list | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **همه محصولات** (`products`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **همه محصولات** (`products`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **همه محصولات** (`products`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **همه محصولات** (`products`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | Sticky bar overlap |
| **همه محصولات** (`products`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | Sticky bar overlap |
| **همه محصولات** (`products`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **همه محصولات** (`products`) | list | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **همه محصولات** (`products`) | list | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1949px) | 0 | 0 | - |
| **همه محصولات** (`products`) | list | `iphone_font125` | ✅ PASS | No | 0 | 10 | Sticky bar overlap |
| **همه محصولات** (`products`) | list | `iphone_font200` | ✅ PASS | No | 0 | 10 | Sticky bar overlap |
| **ویرایش محصول** (`product_edit`) | form | `android-s` | ❌ FAIL | ⚠️ Yes (958px) | 0 | 10 | - |
| **ویرایش محصول** (`product_edit`) | form | `iphone` | ❌ FAIL | ⚠️ Yes (959px) | 0 | 10 | - |
| **ویرایش محصول** (`product_edit`) | form | `tablet` | ❌ FAIL | ⚠️ Yes (965px) | 0 | 10 | - |
| **ویرایش محصول** (`product_edit`) | form | `md-edge` | ❌ FAIL | ⚠️ Yes (1228px) | 0 | 0 | - |
| **ویرایش محصول** (`product_edit`) | form | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش محصول** (`product_edit`) | form | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش محصول** (`product_edit`) | form | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش محصول** (`product_edit`) | form | `zoom200` | ❌ FAIL | ⚠️ Yes (970px) | 0 | 0 | - |
| **ویرایش محصول** (`product_edit`) | form | `zoom300` | ❌ FAIL | ⚠️ Yes (962px) | 0 | 0 | - |
| **ویرایش محصول** (`product_edit`) | form | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش محصول** (`product_edit`) | form | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1401px) | 0 | 0 | - |
| **ویرایش محصول** (`product_edit`) | form | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1949px) | 0 | 0 | - |
| **ویرایش محصول** (`product_edit`) | form | `iphone_font125` | ❌ FAIL | ⚠️ Yes (1064px) | 4 | 10 | - |
| **ویرایش محصول** (`product_edit`) | form | `iphone_font200` | ❌ FAIL | ⚠️ Yes (1382px) | 0 | 10 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1949px) | 0 | 0 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `iphone_font125` | ✅ PASS | No | 0 | 10 | - |
| **دسته‌بندی‌ها** (`categories`) | list | `iphone_font200` | ✅ PASS | No | 0 | 9 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `android-s` | ❌ FAIL | ⚠️ Yes (466px) | 0 | 10 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `iphone` | ❌ FAIL | ⚠️ Yes (466px) | 0 | 10 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | Sticky bar overlap |
| **برچسب‌ها و مشخصات** (`tags`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1949px) | 0 | 0 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `iphone_font125` | ❌ FAIL | ⚠️ Yes (514px) | 0 | 10 | - |
| **برچسب‌ها و مشخصات** (`tags`) | list | `iphone_font200` | ❌ FAIL | ⚠️ Yes (657px) | 0 | 10 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `android-s` | ❌ FAIL | ⚠️ Yes (715px) | 0 | 10 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `iphone` | ❌ FAIL | ⚠️ Yes (718px) | 0 | 10 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `zoom300` | ❌ FAIL | ⚠️ Yes (722px) | 0 | 0 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1949px) | 0 | 0 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `iphone_font125` | ❌ FAIL | ⚠️ Yes (791px) | 0 | 10 | - |
| **تغییر قیمت گروهی** (`pricing`) | workstation | `iphone_font200` | ❌ FAIL | ⚠️ Yes (1012px) | 0 | 7 | - |
| **کدهای تخفیف** (`coupons`) | list | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **کدهای تخفیف** (`coupons`) | list | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **کدهای تخفیف** (`coupons`) | list | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **کدهای تخفیف** (`coupons`) | list | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **کدهای تخفیف** (`coupons`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **کدهای تخفیف** (`coupons`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **کدهای تخفیف** (`coupons`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **کدهای تخفیف** (`coupons`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **کدهای تخفیف** (`coupons`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **کدهای تخفیف** (`coupons`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **کدهای تخفیف** (`coupons`) | list | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **کدهای تخفیف** (`coupons`) | list | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1949px) | 0 | 0 | - |
| **کدهای تخفیف** (`coupons`) | list | `iphone_font125` | ✅ PASS | No | 0 | 6 | - |
| **کدهای تخفیف** (`coupons`) | list | `iphone_font200` | ✅ PASS | No | 0 | 1 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `android-s` | ❌ FAIL | ⚠️ Yes (476px) | 0 | 10 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `iphone` | ❌ FAIL | ⚠️ Yes (477px) | 0 | 10 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1949px) | 0 | 0 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `iphone_font125` | ❌ FAIL | ⚠️ Yes (480px) | 0 | 10 | - |
| **هدایای سبد و جانبی** (`gift_items`) | list | `iphone_font200` | ❌ FAIL | ⚠️ Yes (489px) | 0 | 8 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `android-s` | ❌ FAIL | ⚠️ Yes (476px) | 0 | 10 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `iphone` | ❌ FAIL | ⚠️ Yes (477px) | 0 | 10 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1949px) | 0 | 0 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `iphone_font125` | ❌ FAIL | ⚠️ Yes (480px) | 0 | 10 | - |
| **ویرایش هدیه سبد** (`gift_item_edit`) | form | `iphone_font200` | ❌ FAIL | ⚠️ Yes (489px) | 0 | 8 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `android-s` | ❌ FAIL | ⚠️ Yes (541px) | 0 | 10 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `iphone` | ❌ FAIL | ⚠️ Yes (541px) | 0 | 10 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `laptop_font200` | ❌ FAIL | ⚠️ Yes (1552px) | 0 | 0 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `iphone_font125` | ❌ FAIL | ⚠️ Yes (620px) | 0 | 10 | - |
| **داشبورد مالی** (`finance_dashboard`) | dashboard | `iphone_font200` | ❌ FAIL | ⚠️ Yes (767px) | 0 | 7 | - |
| **هزینه‌ها** (`expenses`) | list | `android-s` | ❌ FAIL | ⚠️ Yes (488px) | 0 | 10 | - |
| **هزینه‌ها** (`expenses`) | list | `iphone` | ❌ FAIL | ⚠️ Yes (489px) | 0 | 10 | - |
| **هزینه‌ها** (`expenses`) | list | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **هزینه‌ها** (`expenses`) | list | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **هزینه‌ها** (`expenses`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **هزینه‌ها** (`expenses`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **هزینه‌ها** (`expenses`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **هزینه‌ها** (`expenses`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **هزینه‌ها** (`expenses`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **هزینه‌ها** (`expenses`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **هزینه‌ها** (`expenses`) | list | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **هزینه‌ها** (`expenses`) | list | `laptop_font200` | ✅ PASS | No | 0 | 0 | - |
| **هزینه‌ها** (`expenses`) | list | `iphone_font125` | ❌ FAIL | ⚠️ Yes (552px) | 0 | 10 | - |
| **هزینه‌ها** (`expenses`) | list | `iphone_font200` | ❌ FAIL | ⚠️ Yes (746px) | 0 | 6 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `android-s` | ❌ FAIL | ⚠️ Yes (391px) | 0 | 6 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `iphone` | ✅ PASS | No | 0 | 6 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `tablet` | ✅ PASS | No | 0 | 7 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `laptop_font200` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `iphone_font125` | ❌ FAIL | ⚠️ Yes (465px) | 0 | 3 | - |
| **ویرایش هزینه** (`expense_edit`) | form | `iphone_font200` | ❌ FAIL | ⚠️ Yes (685px) | 0 | 3 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `android-s` | ✅ PASS | No | 0 | 7 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `iphone` | ✅ PASS | No | 0 | 7 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `laptop_font200` | ✅ PASS | No | 0 | 0 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `iphone_font125` | ✅ PASS | No | 0 | 4 | - |
| **ارزش‌گذاری انبار** (`inventory_valuation`) | dashboard | `iphone_font200` | ✅ PASS | No | 0 | 3 | - |
| **تنظیمات عمومی** (`settings`) | form | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **تنظیمات عمومی** (`settings`) | form | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **تنظیمات عمومی** (`settings`) | form | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **تنظیمات عمومی** (`settings`) | form | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **تنظیمات عمومی** (`settings`) | form | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **تنظیمات عمومی** (`settings`) | form | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **تنظیمات عمومی** (`settings`) | form | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **تنظیمات عمومی** (`settings`) | form | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **تنظیمات عمومی** (`settings`) | form | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **تنظیمات عمومی** (`settings`) | form | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **تنظیمات عمومی** (`settings`) | form | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **تنظیمات عمومی** (`settings`) | form | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **تنظیمات عمومی** (`settings`) | form | `iphone_font125` | ✅ PASS | No | 0 | 2 | - |
| **تنظیمات عمومی** (`settings`) | form | `iphone_font200` | ✅ PASS | No | 0 | 0 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `iphone_font125` | ✅ PASS | No | 0 | 2 | - |
| **ظاهر و صفحه اصلی** (`appearance`) | form | `iphone_font200` | ✅ PASS | No | 0 | 0 | - |
| **قالب‌ها** (`themes`) | list | `android-s` | ❌ FAIL | ⚠️ Yes (398px) | 0 | 8 | - |
| **قالب‌ها** (`themes`) | list | `iphone` | ❌ FAIL | ⚠️ Yes (398px) | 0 | 8 | - |
| **قالب‌ها** (`themes`) | list | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **قالب‌ها** (`themes`) | list | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **قالب‌ها** (`themes`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **قالب‌ها** (`themes`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **قالب‌ها** (`themes`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **قالب‌ها** (`themes`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **قالب‌ها** (`themes`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **قالب‌ها** (`themes`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **قالب‌ها** (`themes`) | list | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **قالب‌ها** (`themes`) | list | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **قالب‌ها** (`themes`) | list | `iphone_font125` | ❌ FAIL | ⚠️ Yes (438px) | 0 | 0 | - |
| **قالب‌ها** (`themes`) | list | `iphone_font200` | ❌ FAIL | ⚠️ Yes (556px) | 0 | 0 | - |
| **ویرایش قالب** (`theme_edit`) | form | `android-s` | ❌ FAIL | ⚠️ Yes (378px) | 0 | 10 | - |
| **ویرایش قالب** (`theme_edit`) | form | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **ویرایش قالب** (`theme_edit`) | form | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **ویرایش قالب** (`theme_edit`) | form | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **ویرایش قالب** (`theme_edit`) | form | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش قالب** (`theme_edit`) | form | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش قالب** (`theme_edit`) | form | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش قالب** (`theme_edit`) | form | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش قالب** (`theme_edit`) | form | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش قالب** (`theme_edit`) | form | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش قالب** (`theme_edit`) | form | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **ویرایش قالب** (`theme_edit`) | form | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **ویرایش قالب** (`theme_edit`) | form | `iphone_font125` | ✅ PASS | No | 0 | 10 | - |
| **ویرایش قالب** (`theme_edit`) | form | `iphone_font200` | ❌ FAIL | ⚠️ Yes (418px) | 0 | 9 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `android-s` | ❌ FAIL | ⚠️ Yes (703px) | 0 | 10 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `iphone` | ❌ FAIL | ⚠️ Yes (703px) | 0 | 10 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `zoom300` | ❌ FAIL | ⚠️ Yes (707px) | 0 | 0 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `iphone_font125` | ❌ FAIL | ⚠️ Yes (751px) | 0 | 8 | - |
| **روش‌های ارسال** (`shipping_methods`) | list | `iphone_font200` | ❌ FAIL | ⚠️ Yes (943px) | 0 | 6 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `android-s` | ✅ PASS | No | 0 | 8 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `iphone` | ✅ PASS | No | 0 | 8 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `tablet` | ✅ PASS | No | 0 | 9 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `iphone_font125` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش روش ارسال** (`shipping_method_edit`) | form | `iphone_font200` | ✅ PASS | No | 0 | 0 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `iphone_font125` | ✅ PASS | No | 0 | 10 | - |
| **الگوهای پیامک** (`sms_patterns`) | list | `iphone_font200` | ✅ PASS | No | 0 | 10 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `android-s` | ✅ PASS | No | 0 | 8 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `iphone` | ✅ PASS | No | 0 | 8 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `iphone_font125` | ✅ PASS | No | 0 | 1 | - |
| **ویرایش الگوی پیامک** (`sms_pattern_edit`) | form | `iphone_font200` | ❌ FAIL | ⚠️ Yes (433px) | 0 | 0 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `iphone_font125` | ✅ PASS | No | 0 | 3 | - |
| **ایمیل‌ها و وب‌میل** (`emails`) | workstation | `iphone_font200` | ✅ PASS | No | 0 | 0 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `iphone_font125` | ❌ FAIL | ⚠️ Yes (427px) | 0 | 1 | - |
| **حساب‌های ایمیل** (`email_accounts`) | list | `iphone_font200` | ❌ FAIL | ⚠️ Yes (641px) | 0 | 0 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `iphone_font125` | ✅ PASS | No | 0 | 3 | - |
| **مشاهده ایمیل** (`email_read`) | workstation | `iphone_font200` | ✅ PASS | No | 0 | 0 | - |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `android-s` | ✅ PASS | No | 0 | 10 | Sticky bar overlap |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `zoom200` | ✅ PASS | No | 0 | 0 | Sticky bar overlap |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `zoom300` | ✅ PASS | No | 0 | 0 | Sticky bar overlap |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `iphone_font125` | ✅ PASS | No | 0 | 4 | Sticky bar overlap |
| **ارسال ایمیل جدید** (`email_compose`) | workstation | `iphone_font200` | ✅ PASS | No | 0 | 0 | Sticky bar overlap |
| **مدیران سایت** (`users`) | list | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **مدیران سایت** (`users`) | list | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **مدیران سایت** (`users`) | list | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **مدیران سایت** (`users`) | list | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **مدیران سایت** (`users`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **مدیران سایت** (`users`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **مدیران سایت** (`users`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **مدیران سایت** (`users`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **مدیران سایت** (`users`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **مدیران سایت** (`users`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **مدیران سایت** (`users`) | list | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **مدیران سایت** (`users`) | list | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **مدیران سایت** (`users`) | list | `iphone_font125` | ✅ PASS | No | 0 | 10 | - |
| **مدیران سایت** (`users`) | list | `iphone_font200` | ✅ PASS | No | 0 | 10 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `android-s` | ✅ PASS | No | 0 | 9 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `iphone` | ✅ PASS | No | 0 | 9 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `iphone_font125` | ✅ PASS | No | 0 | 1 | - |
| **عیب‌یابی و لاگ** (`diagnostics`) | workstation | `iphone_font200` | ✅ PASS | No | 0 | 0 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `android-s` | ✅ PASS | No | 0 | 10 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `iphone` | ✅ PASS | No | 0 | 10 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `tablet` | ✅ PASS | No | 0 | 10 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `md-edge` | ❌ FAIL | ⚠️ Yes (1105px) | 0 | 0 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `laptop_font125` | ❌ FAIL | ⚠️ Yes (1367px) | 0 | 0 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `laptop_font200` | ❌ FAIL | ⚠️ Yes (2118px) | 0 | 0 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `iphone_font125` | ✅ PASS | No | 0 | 3 | - |
| **گزارش پیامک‌ها و اعلان‌ها** (`notifications_log`) | list | `iphone_font200` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `android-s` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `iphone` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `tablet` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `laptop_font200` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `iphone_font125` | ✅ PASS | No | 0 | 0 | - |
| **ورود به مدیریت** (`login`) | workstation | `iphone_font200` | ✅ PASS | No | 0 | 0 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `android-s` | ✅ PASS | No | 0 | 1 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `iphone` | ✅ PASS | No | 0 | 1 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `tablet` | ✅ PASS | No | 0 | 6 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `md-edge` | ✅ PASS | No | 0 | 0 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `laptop` | ✅ PASS | No | 0 | 0 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `desktop` | ✅ PASS | No | 0 | 0 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `fhd` | ✅ PASS | No | 0 | 0 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `zoom200` | ✅ PASS | No | 0 | 0 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `zoom300` | ✅ PASS | No | 0 | 0 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `zoom50` | ✅ PASS | No | 0 | 0 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `laptop_font125` | ✅ PASS | No | 0 | 0 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `laptop_font200` | ✅ PASS | No | 0 | 0 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `iphone_font125` | ✅ PASS | No | 0 | 0 | - |
| **راهنمای استایل و کیت رابط کاربری** (`ui_kit`) | list | `iphone_font200` | ✅ PASS | No | 0 | 0 | - |

## Failure Details

### سفارش‌ها (`orders`) @ `laptop_font200`

- **Overflow:** Document width 1434 exceeds viewport 1280.
  - Offending elements:
    - `div.orders-kpi-card` (left: -154px, right: 10px, width: 164px)
    - `div.orders-kpi-header` (left: -135px, right: -9px, width: 126px)
    - `span.orders-kpi-title` (left: -107px, right: -9px, width: 98px)
    - `span.orders-kpi-icon-pill` (left: -135px, right: -107px, width: 28px)
    - `div.orders-kpi-val-row` (left: -135px, right: -9px, width: 126px)
    - `span.orders-kpi-val` (left: -35px, right: -9px, width: 26px)
    - `span.orders-kpi-unit` (left: -90px, right: -41px, width: 49px)
    - `div.orders-kpi-footer` (left: -135px, right: -9px, width: 126px)

### سفارش‌ها (`orders`) @ `iphone_font200`

- **Overflow:** Document width 448 exceeds viewport 390.
  - Offending elements:
    - `form#bulkActionForm` (left: -31px, right: 363px, width: 394px)
    - `button.btn-bulk-action` (left: -31px, right: 150px, width: 181px)
    - `span` (left: -21px, right: 140px, width: 161px)
    - `nav.admin-bottom-nav` (left: -48px, right: 380px, width: 428px)
    - `a.admin-bottom-nav-item` (left: -109px, right: 6px, width: 115px)
    - `div.nav-icon-wrap` (left: -61px, right: -43px, width: 18px)
    - `svg` (left: -61px, right: -43px, width: 18px)
    - `span.nav-label` (left: -93px, right: -10px, width: 83px)

### فیش‌های کارت‌به‌کارت (`card_to_card_payments`) @ `android-s`

- **Overflow:** Document width 661 exceeds viewport 360.

### فیش‌های کارت‌به‌کارت (`card_to_card_payments`) @ `iphone`

- **Overflow:** Document width 661 exceeds viewport 390.

### فیش‌های کارت‌به‌کارت (`card_to_card_payments`) @ `zoom300`

- **Overflow:** Document width 665 exceeds viewport 640.
  - Offending elements:
    - `form.c2c-search-form` (left: -25px, right: 225px, width: 250px)
    - `div.c2c-search-box` (left: -25px, right: 225px, width: 250px)
    - `input.c2c-search-input` (left: -25px, right: 225px, width: 250px)

### فیش‌های کارت‌به‌کارت (`card_to_card_payments`) @ `laptop_font200`

- **Overflow:** Document width 1352 exceeds viewport 1280.
  - Offending elements:
    - `div.orders-kpi-card.highlight-rose` (left: -21px, right: 137px, width: 158px)
    - `div.orders-kpi-header` (left: -2px, right: 118px, width: 120px)
    - `span.orders-kpi-icon-pill` (left: -2px, right: 23px, width: 24px)
    - `div.orders-kpi-val-row` (left: -2px, right: 118px, width: 120px)
    - `div.orders-kpi-footer` (left: -2px, right: 118px, width: 120px)
    - `span` (left: -2px, right: 118px, width: 120px)
    - `form.c2c-search-form` (left: -72px, right: 178px, width: 250px)
    - `div.c2c-search-box` (left: -72px, right: 178px, width: 250px)

### فیش‌های کارت‌به‌کارت (`card_to_card_payments`) @ `iphone_font125`

- **Overflow:** Document width 701 exceeds viewport 390.

### فیش‌های کارت‌به‌کارت (`card_to_card_payments`) @ `iphone_font200`

- **Overflow:** Document width 822 exceeds viewport 390.

### جزئیات سفارش (`order_detail`) @ `md-edge`

- **Overflow:** Document width 1118 exceeds viewport 1024.
  - Offending elements:
    - `div.od-order-sidebar` (left: -94px, right: 246px, width: 340px)
    - `div#orderStatusCard` (left: -94px, right: 246px, width: 340px)
    - `div.od-card-title` (left: -73px, right: 225px, width: 298px)
    - `form` (left: -73px, right: 225px, width: 298px)
    - `div` (left: -73px, right: 225px, width: 298px)
    - `label` (left: -73px, right: 225px, width: 298px)
    - `select.form-control` (left: -73px, right: 225px, width: 298px)
    - `button.btn-verify-receipt` (left: -73px, right: 225px, width: 298px)

### جزئیات سفارش (`order_detail`) @ `laptop`

- **Console Errors:**
  - `Failed to load resource: net::ERR_QUIC_PROTOCOL_ERROR.QUIC_NETWORK_IDLE_TIMEOUT`
  - `Failed to load resource: net::ERR_QUIC_PROTOCOL_ERROR.QUIC_NETWORK_IDLE_TIMEOUT`
  - `Failed to load resource: net::ERR_QUIC_PROTOCOL_ERROR.QUIC_NETWORK_IDLE_TIMEOUT`
  - `Failed to load resource: net::ERR_QUIC_PROTOCOL_ERROR.QUIC_NETWORK_IDLE_TIMEOUT`
  - `Failed to load resource: net::ERR_QUIC_PROTOCOL_ERROR.QUIC_NETWORK_IDLE_TIMEOUT`

### جزئیات سفارش (`order_detail`) @ `desktop`

- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### جزئیات سفارش (`order_detail`) @ `fhd`

- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### جزئیات سفارش (`order_detail`) @ `zoom200`

- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### جزئیات سفارش (`order_detail`) @ `zoom300`

- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### جزئیات سفارش (`order_detail`) @ `zoom50`

- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### جزئیات سفارش (`order_detail`) @ `laptop_font125`

- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### جزئیات سفارش (`order_detail`) @ `laptop_font200`

- **Overflow:** Document width 1621 exceeds viewport 1280.
  - Offending elements:
    - `div.od-order-sidebar` (left: -341px, right: -1px, width: 340px)
    - `div#orderStatusCard` (left: -341px, right: -1px, width: 340px)
    - `div.od-card-title` (left: -320px, right: -22px, width: 298px)
    - `span.title-text` (left: -320px, right: -22px, width: 298px)
    - `span` (left: -63px, right: -22px, width: 42px)
    - `span` (left: -320px, right: -71px, width: 248px)
    - `form` (left: -320px, right: -22px, width: 298px)
    - `div` (left: -320px, right: -22px, width: 298px)
- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### جزئیات سفارش (`order_detail`) @ `iphone_font125`

- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### جزئیات سفارش (`order_detail`) @ `iphone_font200`

- **Overflow:** Document width 546 exceeds viewport 390.
  - Offending elements:
    - `div#mobileQuickDock` (left: -156px, right: 390px, width: 546px)
    - `div.od-dock-actions` (left: -140px, right: 171px, width: 311px)
    - `button.btn-dock-action` (left: -140px, right: 61px, width: 201px)
    - `span` (left: -126px, right: 8px, width: 134px)
- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### رسید چاپ سفارش (`order_receipt`) @ `android-s`

- **Overflow:** Document width 1200 exceeds viewport 360.

### رسید چاپ سفارش (`order_receipt`) @ `iphone`

- **Overflow:** Document width 1200 exceeds viewport 390.

### رسید چاپ سفارش (`order_receipt`) @ `iphone_font125`

- **Overflow:** Document width 1200 exceeds viewport 390.

### رسید چاپ سفارش (`order_receipt`) @ `iphone_font200`

- **Overflow:** Document width 1200 exceeds viewport 390.

### همه محصولات (`products`) @ `android-s`

- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### همه محصولات (`products`) @ `iphone`

- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### همه محصولات (`products`) @ `tablet`

- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### همه محصولات (`products`) @ `laptop_font200`

- **Overflow:** Document width 1949 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -195px, right: 15px, width: 211px)
    - `span.subnav-tab-text` (left: -191px, right: 11px, width: 203px)
    - `a.subnav-tab-item` (left: -401px, right: -243px, width: 158px)
    - `span.subnav-tab-text` (left: -397px, right: -247px, width: 150px)
    - `a.subnav-tab-item` (left: -669px, right: -449px, width: 220px)
    - `span.subnav-tab-text` (left: -665px, right: -453px, width: 212px)
    - `aside#prodDrawer` (left: -480px, right: 0px, width: 480px)
    - `div.prod-drawer-header` (left: -480px, right: 0px, width: 480px)

### ویرایش محصول (`product_edit`) @ `android-s`

- **Overflow:** Document width 958 exceeds viewport 360.

### ویرایش محصول (`product_edit`) @ `iphone`

- **Overflow:** Document width 959 exceeds viewport 390.

### ویرایش محصول (`product_edit`) @ `tablet`

- **Overflow:** Document width 965 exceeds viewport 768.
  - Offending elements:
    - `div` (left: -197px, right: 143px, width: 340px)
    - `div.admin-card` (left: -197px, right: 143px, width: 340px)
    - `h3` (left: -197px, right: 143px, width: 340px)
    - `div` (left: -197px, right: 143px, width: 340px)
    - `label` (left: -197px, right: 143px, width: 340px)
    - `span` (left: -85px, right: 108px, width: 193px)
    - `label` (left: -197px, right: 143px, width: 340px)
    - `span` (left: -69px, right: 108px, width: 176px)

### ویرایش محصول (`product_edit`) @ `md-edge`

- **Overflow:** Document width 1228 exceeds viewport 1024.
  - Offending elements:
    - `div` (left: -204px, right: 136px, width: 340px)
    - `div.admin-card` (left: -204px, right: 136px, width: 340px)
    - `h3` (left: -204px, right: 136px, width: 340px)
    - `div` (left: -204px, right: 136px, width: 340px)
    - `label` (left: -204px, right: 136px, width: 340px)
    - `span` (left: -95px, right: 101px, width: 196px)
    - `label` (left: -204px, right: 136px, width: 340px)
    - `span` (left: -78px, right: 101px, width: 179px)

### ویرایش محصول (`product_edit`) @ `zoom200`

- **Overflow:** Document width 970 exceeds viewport 960.
  - Offending elements:
    - `div` (left: -10px, right: 330px, width: 340px)
    - `div.admin-card` (left: -10px, right: 330px, width: 340px)
    - `h3` (left: -10px, right: 330px, width: 340px)
    - `div` (left: -10px, right: 330px, width: 340px)
    - `label` (left: -10px, right: 330px, width: 340px)
    - `label` (left: -10px, right: 330px, width: 340px)
    - `div` (left: -10px, right: 330px, width: 340px)
    - `button.btn.btn-primary` (left: -10px, right: 330px, width: 340px)

### ویرایش محصول (`product_edit`) @ `zoom300`

- **Overflow:** Document width 962 exceeds viewport 640.
  - Offending elements:
    - `div` (left: -322px, right: 18px, width: 340px)
    - `div.admin-card` (left: -322px, right: 18px, width: 340px)
    - `h3` (left: -322px, right: 18px, width: 340px)
    - `div` (left: -322px, right: 18px, width: 340px)
    - `label` (left: -322px, right: 18px, width: 340px)
    - `input` (left: -3px, right: 15px, width: 18px)
    - `span` (left: -208px, right: -17px, width: 191px)
    - `label` (left: -322px, right: 18px, width: 340px)

### ویرایش محصول (`product_edit`) @ `laptop_font125`

- **Overflow:** Document width 1401 exceeds viewport 1280.
  - Offending elements:
    - `div` (left: -121px, right: 219px, width: 340px)
    - `div.admin-card` (left: -121px, right: 219px, width: 340px)
    - `h3` (left: -121px, right: 219px, width: 340px)
    - `div` (left: -121px, right: 219px, width: 340px)
    - `label` (left: -121px, right: 219px, width: 340px)
    - `span` (left: -61px, right: 184px, width: 245px)
    - `label` (left: -121px, right: 219px, width: 340px)
    - `span` (left: -40px, right: 184px, width: 224px)

### ویرایش محصول (`product_edit`) @ `laptop_font200`

- **Overflow:** Document width 1949 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -195px, right: 15px, width: 211px)
    - `span.subnav-tab-text` (left: -191px, right: 11px, width: 203px)
    - `a.subnav-tab-item` (left: -401px, right: -243px, width: 158px)
    - `span.subnav-tab-text` (left: -397px, right: -247px, width: 150px)
    - `a.subnav-tab-item` (left: -669px, right: -449px, width: 220px)
    - `span.subnav-tab-text` (left: -665px, right: -453px, width: 212px)
    - `div` (left: -258px, right: 736px, width: 994px)
    - `div.admin-card` (left: -258px, right: 736px, width: 994px)

### ویرایش محصول (`product_edit`) @ `iphone_font125`

- **Overflow:** Document width 1064 exceeds viewport 390.
- **Console Errors:**
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`
  - `Failed to load resource: net::ERR_CONNECTION_CLOSED`

### ویرایش محصول (`product_edit`) @ `iphone_font200`

- **Overflow:** Document width 1382 exceeds viewport 390.

### دسته‌بندی‌ها (`categories`) @ `laptop_font200`

- **Overflow:** Document width 1949 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -195px, right: 15px, width: 211px)
    - `span.subnav-tab-text` (left: -191px, right: 11px, width: 203px)
    - `a.subnav-tab-item` (left: -401px, right: -243px, width: 158px)
    - `span.subnav-tab-text` (left: -397px, right: -247px, width: 150px)
    - `a.subnav-tab-item` (left: -669px, right: -449px, width: 220px)
    - `span.subnav-tab-text` (left: -665px, right: -453px, width: 212px)

### برچسب‌ها و مشخصات (`tags`) @ `android-s`

- **Overflow:** Document width 466 exceeds viewport 360.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -93px, right: 347px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -14px, right: 43px, width: 58px)
    - `span.nav-label` (left: -6px, right: 35px, width: 42px)

### برچسب‌ها و مشخصات (`tags`) @ `iphone`

- **Overflow:** Document width 466 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -63px, right: 377px, width: 440px)

### برچسب‌ها و مشخصات (`tags`) @ `laptop_font200`

- **Overflow:** Document width 1949 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -195px, right: 15px, width: 211px)
    - `span.subnav-tab-text` (left: -191px, right: 11px, width: 203px)
    - `a.subnav-tab-item` (left: -401px, right: -243px, width: 158px)
    - `span.subnav-tab-text` (left: -397px, right: -247px, width: 150px)
    - `a.subnav-tab-item` (left: -669px, right: -449px, width: 220px)
    - `span.subnav-tab-text` (left: -665px, right: -453px, width: 212px)

### برچسب‌ها و مشخصات (`tags`) @ `iphone_font125`

- **Overflow:** Document width 514 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -87px, right: 353px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -41px, right: 30px, width: 72px)
    - `div.nav-icon-wrap` (left: -14px, right: 4px, width: 18px)
    - `svg` (left: -14px, right: 4px, width: 18px)
    - `span.nav-label` (left: -31px, right: 20px, width: 52px)

### برچسب‌ها و مشخصات (`tags`) @ `iphone_font200`

- **Overflow:** Document width 657 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -158px, right: 282px, width: 440px)
    - `a.admin-bottom-nav-item.active` (left: -12px, right: 96px, width: 108px)
    - `a.admin-bottom-nav-item` (left: -94px, right: -16px, width: 78px)
    - `div.nav-icon-wrap` (left: -64px, right: -46px, width: 18px)
    - `svg` (left: -64px, right: -46px, width: 18px)
    - `span.nav-label` (left: -78px, right: -32px, width: 46px)
    - `a.admin-bottom-nav-item` (left: -213px, right: -98px, width: 115px)
    - `div.nav-icon-wrap` (left: -164px, right: -146px, width: 18px)

### تغییر قیمت گروهی (`pricing`) @ `android-s`

- **Overflow:** Document width 715 exceeds viewport 360.

### تغییر قیمت گروهی (`pricing`) @ `iphone`

- **Overflow:** Document width 718 exceeds viewport 390.

### تغییر قیمت گروهی (`pricing`) @ `zoom300`

- **Overflow:** Document width 722 exceeds viewport 640.
  - Offending elements:
    - `div.pricing-kpi-card` (left: -82px, right: 65px, width: 147px)
    - `div.pricing-kpi-info` (left: -15px, right: 44px, width: 59px)
    - `span.pricing-kpi-label` (left: -15px, right: 44px, width: 59px)
    - `span.pricing-kpi-val` (left: -15px, right: 44px, width: 59px)
    - `span.pricing-kpi-sub` (left: -15px, right: 44px, width: 59px)
    - `div.pricing-kpi-icon.success` (left: -61px, right: -15px, width: 46px)
    - `svg` (left: -50px, right: -26px, width: 24px)

### تغییر قیمت گروهی (`pricing`) @ `laptop_font200`

- **Overflow:** Document width 1949 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item.active` (left: -195px, right: 15px, width: 211px)
    - `span.subnav-tab-text` (left: -191px, right: 11px, width: 203px)
    - `a.subnav-tab-item` (left: -401px, right: -243px, width: 158px)
    - `span.subnav-tab-text` (left: -397px, right: -247px, width: 150px)
    - `a.subnav-tab-item` (left: -669px, right: -449px, width: 220px)
    - `span.subnav-tab-text` (left: -665px, right: -453px, width: 212px)
    - `div.pricing-kpi-card` (left: -31px, right: 229px, width: 259px)
    - `div.pricing-kpi-icon.warning` (left: -10px, right: 36px, width: 46px)

### تغییر قیمت گروهی (`pricing`) @ `iphone_font125`

- **Overflow:** Document width 791 exceeds viewport 390.

### تغییر قیمت گروهی (`pricing`) @ `iphone_font200`

- **Overflow:** Document width 1012 exceeds viewport 390.

### کدهای تخفیف (`coupons`) @ `laptop_font200`

- **Overflow:** Document width 1949 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -195px, right: 15px, width: 211px)
    - `span.subnav-tab-text` (left: -191px, right: 11px, width: 203px)
    - `a.subnav-tab-item.active` (left: -401px, right: -243px, width: 158px)
    - `span.subnav-tab-text` (left: -397px, right: -247px, width: 150px)
    - `a.subnav-tab-item` (left: -669px, right: -449px, width: 220px)
    - `span.subnav-tab-text` (left: -665px, right: -453px, width: 212px)

### هدایای سبد و جانبی (`gift_items`) @ `android-s`

- **Overflow:** Document width 476 exceeds viewport 360.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -98px, right: 342px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -19px, right: 38px, width: 58px)
    - `span.nav-label` (left: -11px, right: 30px, width: 42px)

### هدایای سبد و جانبی (`gift_items`) @ `iphone`

- **Overflow:** Document width 477 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -68px, right: 372px, width: 440px)

### هدایای سبد و جانبی (`gift_items`) @ `laptop_font200`

- **Overflow:** Document width 1949 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -195px, right: 15px, width: 211px)
    - `span.subnav-tab-text` (left: -191px, right: 11px, width: 203px)
    - `a.subnav-tab-item` (left: -401px, right: -243px, width: 158px)
    - `span.subnav-tab-text` (left: -397px, right: -247px, width: 150px)
    - `a.subnav-tab-item.active` (left: -669px, right: -449px, width: 220px)
    - `span.subnav-tab-text` (left: -665px, right: -453px, width: 212px)

### هدایای سبد و جانبی (`gift_items`) @ `iphone_font125`

- **Overflow:** Document width 480 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -70px, right: 370px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -24px, right: 47px, width: 72px)
    - `span.nav-label` (left: -14px, right: 37px, width: 52px)

### هدایای سبد و جانبی (`gift_items`) @ `iphone_font200`

- **Overflow:** Document width 489 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -74px, right: 366px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -10px, right: 68px, width: 78px)
    - `a.admin-bottom-nav-item` (left: -129px, right: -14px, width: 115px)
    - `div.nav-icon-wrap` (left: -80px, right: -62px, width: 18px)
    - `svg` (left: -80px, right: -62px, width: 18px)
    - `span.nav-label` (left: -113px, right: -30px, width: 83px)

### ویرایش هدیه سبد (`gift_item_edit`) @ `android-s`

- **Overflow:** Document width 476 exceeds viewport 360.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -98px, right: 342px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -19px, right: 38px, width: 58px)
    - `span.nav-label` (left: -11px, right: 30px, width: 42px)

### ویرایش هدیه سبد (`gift_item_edit`) @ `iphone`

- **Overflow:** Document width 477 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -68px, right: 372px, width: 440px)

### ویرایش هدیه سبد (`gift_item_edit`) @ `laptop_font200`

- **Overflow:** Document width 1949 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -195px, right: 15px, width: 211px)
    - `span.subnav-tab-text` (left: -191px, right: 11px, width: 203px)
    - `a.subnav-tab-item` (left: -401px, right: -243px, width: 158px)
    - `span.subnav-tab-text` (left: -397px, right: -247px, width: 150px)
    - `a.subnav-tab-item.active` (left: -669px, right: -449px, width: 220px)
    - `span.subnav-tab-text` (left: -665px, right: -453px, width: 212px)

### ویرایش هدیه سبد (`gift_item_edit`) @ `iphone_font125`

- **Overflow:** Document width 480 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -70px, right: 370px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -24px, right: 47px, width: 72px)
    - `span.nav-label` (left: -14px, right: 37px, width: 52px)

### ویرایش هدیه سبد (`gift_item_edit`) @ `iphone_font200`

- **Overflow:** Document width 489 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -74px, right: 366px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -10px, right: 68px, width: 78px)
    - `a.admin-bottom-nav-item` (left: -129px, right: -14px, width: 115px)
    - `div.nav-icon-wrap` (left: -80px, right: -62px, width: 18px)
    - `svg` (left: -80px, right: -62px, width: 18px)
    - `span.nav-label` (left: -113px, right: -30px, width: 83px)

### داشبورد مالی (`finance_dashboard`) @ `android-s`

- **Overflow:** Document width 541 exceeds viewport 360.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -130px, right: 310px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -54px, right: 3px, width: 58px)
    - `div.nav-icon-wrap` (left: -35px, right: -17px, width: 18px)
    - `svg` (left: -35px, right: -17px, width: 18px)
    - `span.nav-label` (left: -46px, right: -5px, width: 42px)

### داشبورد مالی (`finance_dashboard`) @ `iphone`

- **Overflow:** Document width 541 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -100px, right: 340px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -25px, right: 33px, width: 58px)
    - `div.nav-icon-wrap` (left: -5px, right: 13px, width: 18px)
    - `svg` (left: -5px, right: 13px, width: 18px)
    - `span.nav-label` (left: -17px, right: 25px, width: 42px)

### داشبورد مالی (`finance_dashboard`) @ `laptop_font200`

- **Overflow:** Document width 1552 exceeds viewport 1280.
  - Offending elements:
    - `div.fin-micro-card` (left: -115px, right: 99px, width: 215px)
    - `div.fin-micro-lead` (left: -21px, right: 84px, width: 106px)
    - `span.fin-micro-title` (left: -21px, right: 68px, width: 90px)
    - `span.fin-micro-val` (left: -100px, right: -21px, width: 79px)
    - `span` (left: -100px, right: -21px, width: 79px)
    - `div.fin-col-block` (left: -7px, right: 716px, width: 723px)
    - `div.fin-table-card` (left: -7px, right: 716px, width: 723px)
    - `div.fin-table-card` (left: -7px, right: 716px, width: 723px)

### داشبورد مالی (`finance_dashboard`) @ `iphone_font125`

- **Overflow:** Document width 620 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -140px, right: 300px, width: 440px)
    - `a.admin-bottom-nav-item.active` (left: -21px, right: 20px, width: 41px)
    - `div.nav-icon-wrap` (left: -10px, right: 8px, width: 18px)
    - `svg` (left: -10px, right: 8px, width: 18px)
    - `span.nav-label` (left: -11px, right: 10px, width: 21px)
    - `a.admin-bottom-nav-item` (left: -97px, right: -25px, width: 72px)
    - `div.nav-icon-wrap` (left: -70px, right: -52px, width: 18px)
    - `svg` (left: -70px, right: -52px, width: 18px)

### داشبورد مالی (`finance_dashboard`) @ `iphone_font200`

- **Overflow:** Document width 767 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -213px, right: 227px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -83px, right: 45px, width: 128px)
    - `div.nav-icon-wrap` (left: -28px, right: -10px, width: 18px)
    - `svg` (left: -28px, right: -10px, width: 18px)
    - `span.nav-label` (left: -67px, right: 29px, width: 96px)
    - `a.admin-bottom-nav-item.active` (left: -152px, right: -87px, width: 65px)
    - `div.nav-icon-wrap` (left: -129px, right: -111px, width: 18px)
    - `svg` (left: -129px, right: -111px, width: 18px)

### هزینه‌ها (`expenses`) @ `android-s`

- **Overflow:** Document width 488 exceeds viewport 360.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -104px, right: 336px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -28px, right: 30px, width: 58px)
    - `div.nav-icon-wrap` (left: -8px, right: 10px, width: 18px)
    - `svg` (left: -8px, right: 10px, width: 18px)
    - `span.nav-label` (left: -20px, right: 22px, width: 42px)

### هزینه‌ها (`expenses`) @ `iphone`

- **Overflow:** Document width 489 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -74px, right: 366px, width: 440px)

### هزینه‌ها (`expenses`) @ `iphone_font125`

- **Overflow:** Document width 552 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -106px, right: 334px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -63px, right: 9px, width: 72px)
    - `div.nav-icon-wrap` (left: -36px, right: -18px, width: 18px)
    - `svg` (left: -36px, right: -18px, width: 18px)
    - `span.nav-label` (left: -53px, right: -1px, width: 52px)

### هزینه‌ها (`expenses`) @ `iphone_font200`

- **Overflow:** Document width 746 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -203px, right: 237px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -73px, right: 55px, width: 128px)
    - `div.nav-icon-wrap` (left: -18px, right: 0px, width: 18px)
    - `svg` (left: -18px, right: 0px, width: 18px)
    - `span.nav-label` (left: -57px, right: 39px, width: 96px)
    - `a.admin-bottom-nav-item.active` (left: -142px, right: -77px, width: 65px)
    - `div.nav-icon-wrap` (left: -118px, right: -100px, width: 18px)
    - `svg` (left: -118px, right: -100px, width: 18px)

### ویرایش هزینه (`expense_edit`) @ `android-s`

- **Overflow:** Document width 391 exceeds viewport 360.

### ویرایش هزینه (`expense_edit`) @ `iphone_font125`

- **Overflow:** Document width 465 exceeds viewport 390.

### ویرایش هزینه (`expense_edit`) @ `iphone_font200`

- **Overflow:** Document width 685 exceeds viewport 390.

### تنظیمات عمومی (`settings`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### تنظیمات عمومی (`settings`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### تنظیمات عمومی (`settings`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### ظاهر و صفحه اصلی (`appearance`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### ظاهر و صفحه اصلی (`appearance`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### ظاهر و صفحه اصلی (`appearance`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item` (left: -427px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -423px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -475px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -479px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### قالب‌ها (`themes`) @ `android-s`

- **Overflow:** Document width 398 exceeds viewport 360.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -28px, right: 350px, width: 378px)

### قالب‌ها (`themes`) @ `iphone`

- **Overflow:** Document width 398 exceeds viewport 390.

### قالب‌ها (`themes`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### قالب‌ها (`themes`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### قالب‌ها (`themes`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item` (left: -427px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -423px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -475px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -479px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### قالب‌ها (`themes`) @ `iphone_font125`

- **Overflow:** Document width 438 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -38px, right: 380px, width: 418px)
    - `a.admin-bottom-nav-item.active` (left: -3px, right: 55px, width: 58px)

### قالب‌ها (`themes`) @ `iphone_font200`

- **Overflow:** Document width 556 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -108px, right: 332px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -65px, right: 14px, width: 78px)
    - `div.nav-icon-wrap` (left: -34px, right: -16px, width: 18px)
    - `svg` (left: -34px, right: -16px, width: 18px)
    - `span.nav-label` (left: -49px, right: -2px, width: 46px)
    - `a.admin-bottom-nav-item.active` (left: -161px, right: -69px, width: 93px)
    - `div.nav-icon-wrap` (left: -124px, right: -106px, width: 18px)
    - `svg` (left: -124px, right: -106px, width: 18px)

### ویرایش قالب (`theme_edit`) @ `android-s`

- **Overflow:** Document width 378 exceeds viewport 360.

### ویرایش قالب (`theme_edit`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### ویرایش قالب (`theme_edit`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### ویرایش قالب (`theme_edit`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item` (left: -427px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -423px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -475px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -479px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### ویرایش قالب (`theme_edit`) @ `iphone_font200`

- **Overflow:** Document width 418 exceeds viewport 390.

### روش‌های ارسال (`shipping_methods`) @ `android-s`

- **Overflow:** Document width 703 exceeds viewport 360.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -211px, right: 229px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -39px, right: 25px, width: 64px)
    - `div.nav-icon-wrap` (left: -16px, right: 2px, width: 18px)
    - `svg` (left: -16px, right: 2px, width: 18px)
    - `span.nav-label` (left: -31px, right: 17px, width: 48px)
    - `a.admin-bottom-nav-item` (left: -82px, right: -43px, width: 39px)
    - `div.nav-icon-wrap` (left: -71px, right: -53px, width: 18px)
    - `svg` (left: -71px, right: -53px, width: 18px)

### روش‌های ارسال (`shipping_methods`) @ `iphone`

- **Overflow:** Document width 703 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -181px, right: 259px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -9px, right: 56px, width: 64px)
    - `a.admin-bottom-nav-item` (left: -52px, right: -13px, width: 39px)
    - `div.nav-icon-wrap` (left: -42px, right: -24px, width: 18px)
    - `svg` (left: -42px, right: -24px, width: 18px)
    - `span.nav-label` (left: -44px, right: -21px, width: 23px)
    - `a.admin-bottom-nav-item.active` (left: -103px, right: -56px, width: 47px)
    - `div.nav-icon-wrap` (left: -88px, right: -70px, width: 18px)

### روش‌های ارسال (`shipping_methods`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### روش‌های ارسال (`shipping_methods`) @ `zoom300`

- **Overflow:** Document width 707 exceeds viewport 640.
  - Offending elements:
    - `div.shipping-table-card` (left: -67px, right: 624px, width: 691px)
    - `div.shipping-card-header` (left: -46px, right: 603px, width: 649px)
    - `div.shipping-table-wrap` (left: -46px, right: 603px, width: 649px)
    - `div#studio` (left: -67px, right: 624px, width: 691px)
    - `div.studio-header` (left: -44px, right: 601px, width: 645px)
    - `div` (left: -44px, right: -1px, width: 43px)
    - `span#studioModePill` (left: -44px, right: -1px, width: 43px)
    - `div.shipping-preview-box` (left: -44px, right: 601px, width: 645px)

### روش‌های ارسال (`shipping_methods`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### روش‌های ارسال (`shipping_methods`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### روش‌های ارسال (`shipping_methods`) @ `iphone_font125`

- **Overflow:** Document width 751 exceeds viewport 390.
  - Offending elements:
    - `div.shipping-table-card` (left: -1px, right: 734px, width: 736px)
    - `div#studio` (left: -1px, right: 734px, width: 736px)
    - `nav.admin-bottom-nav` (left: -205px, right: 235px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -44px, right: 36px, width: 80px)
    - `div.nav-icon-wrap` (left: -13px, right: 5px, width: 18px)
    - `svg` (left: -13px, right: 5px, width: 18px)
    - `span.nav-label` (left: -34px, right: 26px, width: 60px)
    - `a.admin-bottom-nav-item` (left: -97px, right: -48px, width: 49px)

### روش‌های ارسال (`shipping_methods`) @ `iphone_font200`

- **Overflow:** Document width 943 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -301px, right: 139px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -44px, right: 81px, width: 124px)
    - `span.nav-label` (left: -28px, right: 65px, width: 92px)
    - `a.admin-bottom-nav-item` (left: -176px, right: -48px, width: 128px)
    - `div.nav-icon-wrap` (left: -121px, right: -103px, width: 18px)
    - `svg` (left: -121px, right: -103px, width: 18px)
    - `span.nav-label` (left: -160px, right: -64px, width: 96px)
    - `a.admin-bottom-nav-item` (left: -258px, right: -180px, width: 78px)

### ویرایش روش ارسال (`shipping_method_edit`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### ویرایش روش ارسال (`shipping_method_edit`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### ویرایش روش ارسال (`shipping_method_edit`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### الگوهای پیامک (`sms_patterns`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### الگوهای پیامک (`sms_patterns`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### الگوهای پیامک (`sms_patterns`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item.active` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### ویرایش الگوی پیامک (`sms_pattern_edit`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### ویرایش الگوی پیامک (`sms_pattern_edit`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### ویرایش الگوی پیامک (`sms_pattern_edit`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item.active` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### ویرایش الگوی پیامک (`sms_pattern_edit`) @ `iphone_font200`

- **Overflow:** Document width 433 exceeds viewport 390.
  - Offending elements:
    - `code` (left: 281px, right: 446px, width: 165px)

### ایمیل‌ها و وب‌میل (`emails`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### ایمیل‌ها و وب‌میل (`emails`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### ایمیل‌ها و وب‌میل (`emails`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item.active` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### حساب‌های ایمیل (`email_accounts`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### حساب‌های ایمیل (`email_accounts`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### حساب‌های ایمیل (`email_accounts`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item.active` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### حساب‌های ایمیل (`email_accounts`) @ `iphone_font125`

- **Overflow:** Document width 427 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -27px, right: 380px, width: 407px)

### حساب‌های ایمیل (`email_accounts`) @ `iphone_font200`

- **Overflow:** Document width 641 exceeds viewport 390.
  - Offending elements:
    - `nav.admin-bottom-nav` (left: -150px, right: 290px, width: 440px)
    - `a.admin-bottom-nav-item` (left: -25px, right: 103px, width: 128px)
    - `span.nav-label` (left: -9px, right: 87px, width: 96px)
    - `a.admin-bottom-nav-item` (left: -107px, right: -29px, width: 78px)
    - `div.nav-icon-wrap` (left: -77px, right: -59px, width: 18px)
    - `svg` (left: -77px, right: -59px, width: 18px)
    - `span.nav-label` (left: -91px, right: -45px, width: 46px)
    - `a.admin-bottom-nav-item.active` (left: -204px, right: -111px, width: 93px)

### مشاهده ایمیل (`email_read`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### مشاهده ایمیل (`email_read`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### مشاهده ایمیل (`email_read`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item.active` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### ارسال ایمیل جدید (`email_compose`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### ارسال ایمیل جدید (`email_compose`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### ارسال ایمیل جدید (`email_compose`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item.active` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### مدیران سایت (`users`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### مدیران سایت (`users`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### مدیران سایت (`users`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item.active` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### عیب‌یابی و لاگ (`diagnostics`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item.active` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### عیب‌یابی و لاگ (`diagnostics`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item.active` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### عیب‌یابی و لاگ (`diagnostics`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item.active` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

### گزارش پیامک‌ها و اعلان‌ها (`notifications_log`) @ `md-edge`

- **Overflow:** Document width 1105 exceeds viewport 1024.
  - Offending elements:
    - `a.subnav-tab-item.active` (left: -81px, right: 10px, width: 91px)
    - `span.subnav-tab-text` (left: -77px, right: 6px, width: 83px)

### گزارش پیامک‌ها و اعلان‌ها (`notifications_log`) @ `laptop_font125`

- **Overflow:** Document width 1367 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item.active` (left: -87px, right: 25px, width: 112px)
    - `span.subnav-tab-text` (left: -83px, right: 21px, width: 104px)

### گزارش پیامک‌ها و اعلان‌ها (`notifications_log`) @ `laptop_font200`

- **Overflow:** Document width 2118 exceeds viewport 1280.
  - Offending elements:
    - `a.subnav-tab-item` (left: -183px, right: -19px, width: 164px)
    - `span.subnav-tab-text` (left: -179px, right: -23px, width: 156px)
    - `a.subnav-tab-item` (left: -426px, right: -231px, width: 196px)
    - `span.subnav-tab-text` (left: -422px, right: -235px, width: 188px)
    - `a.subnav-tab-item` (left: -621px, right: -474px, width: 147px)
    - `span.subnav-tab-text` (left: -617px, right: -478px, width: 139px)
    - `a.subnav-tab-item.active` (left: -838px, right: -669px, width: 169px)
    - `span.subnav-tab-text` (left: -834px, right: -673px, width: 161px)

