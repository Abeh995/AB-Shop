# Financial Logic, Stock Management & Orders

This document details the financial snapshotting architecture, concurrency guarantees, pricing engine, inventory rules, and accounting subsystems of AB-Socks.

---

## 1. Immutable Financial Snapshots

A central architectural pattern across the application is **historical snapshotting**. Once a transaction occurs, its financial reality is frozen.

- **`order_items`**: Captures `product_name`, `variant_label`, `unit_price`, and `unit_cost_price` at the moment of order placement.
- **`order_gift_items`**: Captures `name`, `unit_selling_price`, and `unit_cost_price`.
- **`price_history`**: Captures previous value, new value, percentage change, admin ID, reason, and `variant_label`.
- **`orders`**: Snapshots shipping cost, shipping method name, discount amounts, and card-to-card receipt filename.

> **Principle**: A future price increase, cost alteration, product deletion, or variant update must **never** alter the financial figures or profit calculations of orders that were already finalized.

---

## 2. Concurrency & Stock Integrity

Overselling the last unit during simultaneous checkout attempts is strictly prevented by database transactions and row-level locks:

1. **Atomic Transactions**: All checkout operations (`OrderService::createFromCheckout`), gift allocations (`GiftService::assignGiftToOrder`), and bulk price changes run inside PDO transactions (`beginTransaction()`, `commit()`, `rollBack()`).
2. **Row Locking**: Code that decrements stock locks the target row using `SELECT ... FOR UPDATE` before applying updates.
3. **Conditional Deductions**: Deductions execute conditional updates:
   ```sql
   UPDATE product_variants SET stock = stock - ? WHERE id = ? AND stock >= ?
   ```
   If the rows affected is `0`, the transaction rolls back immediately with an out-of-stock exception.
4. **Effective Stock Calculation**: When a product has variants (`has_variants = 1`), `products.stock` is explicitly set to `0`. The true inventory is computed via `SUM(product_variants.stock)` using the `effectiveStockSqlFragment()` helper.

---

## 3. Services Overview

### PricingService (`app/services/PricingService.php`)
- The **only** component permitted to write to `products.cost_price`, `products.price`, `product_variants.cost_price`, and `product_variants.price_override`.
- Every modification creates an immutable entry in `price_history`.
- Supports single-product updates and bulk operations (`applyBulkPriceChange()`) with an interactive **Preview-then-Commit** two-step workflow in the admin panel.

### OrderService (`app/services/OrderService.php`)
- Centralizes order creation (`createFromCheckout()`) for both payment gateways (Zarinpal) and offline payments (Card-to-Card).
- Validates that the customer is authenticated and phone-verified before placing an order.
- Re-reads live server-side prices, stock, applied coupons, post-order gifts, and shipping charges inside the transaction to eliminate client-side tampering.
- **Admin Order Lifecycle & Logistics (v1.17.0)**:
  - Encapsulates administrative order retrieval (`getAdminOrders()`) with paginated querying, batch loading of `order_items` and `order_gift_items`, and multi-field search.
  - Aggregates real-time Bento KPI metrics (`getAdminOrderStats()`) and status breakdown counts (`getAdminStatusCounts()`).
  - Transactional status transitions (`updateOrderStatus()`, `bulkUpdateStatus()`) triggering automatic customer SMS notifications via `dispatchSmsEvent()` (`order_shipped`, `order_delivered`, `order_cancelled`).
  - Postal shipment tracking (`updateTrackingCode()`) persisting courier barcodes into `orders.tracking_code` (Migration 017).
  - Defensive migration feature detection (`hasTrackingCodeColumn()`) guaranteeing compatibility across DirectAdmin shared hosting before manual phpMyAdmin execution.
  - Responsive order detail hydration (`getOrder()`, `getOrderItemsWithGallery()`) resolving batch multi-image product galleries and decoupling database queries from presentation controllers.

### GiftService (`app/services/GiftService.php`)
- Manages a unified catalog (`gift_items`) that supports two distinct capabilities on the same physical entity:
  - `is_giftable`: Free gift allocated to an order by an admin from `order_detail.php`.
  - `is_post_orderable`: Paid upsell add-on presented to the customer during cart/checkout.
- **Merchandising & Cart Targeting (v1.22.0 / Migration 020)**:
  - Supports customer-facing taglines (`tagline`), promotional badges (`badge_text`), smart cart thresholds (`min_cart_total`), and manual sequence prioritization (`sort_order`).
  - Transactional sequence persistence (`reorderGiftItems()`) executing atomic batch updates inside a PDO transaction.

### CouponService (`app/services/CouponService.php`)
- Encapsulates promotional discount code validation and calculation (`validate()`, `calculateDiscount()`).
- Supports percentage discounts (`percent`) with maximum discount ceilings (`max_discount_amount`) and fixed monetary discounts (`fixed`).
- Validates minimum order amounts (`min_order_amount`), usage limits (`max_uses`), active status, and date expiration windows.
- Atomic usage incrementation (`markUsed()`) invoked directly within `OrderService::createFromCheckout()` inside the order placement transaction.
- Full administrative management via `admin/coupons.php` (`getAll()`, `save()`, `toggle()`, `delete()`).
  - Smart cart filtering (`getAvailablePostOrderItems(?int $cartSubtotal)`) matching subtotal thresholds against `min_cart_total`.
- Snapshots cost and selling prices into `order_gift_items`.
- Provides catalog workstation metrics (`getAdminGiftItemsMetrics()`) for inventory capital valuation, utilization rates, lifetime upsell gross revenue, net profit, cart attach rate (Take Rate %), and top-performer detection.
- Connects historical order attachments (`order_gift_items`) into vectorized catalog performance stats (`gifted_units`, `sold_units`, `gross_revenue`, `gross_profit`, `margin_percent`).
- Enforces row-locking (`SELECT ... FOR UPDATE` and conditional `WHERE stock >= ?`) for concurrency safety across all stock mutations.

### AccountingService (`app/services/AccountingService.php`)
- **Read-Only Reporting**: Strictly read-only; never mutates the database.
- Calculates order-level gross profitability (`getOrderProfitability($orderId)`):
  $$\text{Gross Profit} = (\text{Order Items Total} - \text{Discount}) + \text{Post-Order Upsells} + \text{Shipping Collected} - \text{Total Cost of Goods Sold} - \text{Actual Shipping Cost}$$
- Aggregates comprehensive period performance (`getFinancialSummary($startDate, $endDate)`) incorporating operational expenses from the `expenses` ledger and computing key unit economics:
  - Average Order Value (AOV) and Net Profit per Order.
  - Shipping balance & logistics subsidy metrics.
  - Customer discount penetration rate.
  - Break-Even Analysis: calculates required break-even revenue and order targets based on gross margin and fixed operational expenses.
  - Product Cost Health: monitors snapshot cost completeness across historical order items.
- **Time-Series & Deep Analytics Engine (v1.21.0)**:
  - `getFinancialDailyTrends($startDate, $endDate)`: Vectorized batch query aggregating daily revenue, COGS, expenses, and net margins for inline pure SVG charting.
  - `getTopProfitProducts($startDate, $endDate, $limit = 5)`: Top profit-generating catalog items by net contribution.
  - `getPaymentMethodBreakdown($startDate, $endDate)`: Financial distribution between online gateway (Zarinpal) and Card-to-Card.
  - `getShippingMethodFinancialBreakdown($startDate, $endDate)`: Logistics profitability comparing customer-paid shipping vs actual courier expenses.
  - `getInventoryValuationReport()` (v1.23.0): Evaluates store-wide physical inventory capital valuation ($\sum \text{stock} \times \text{cost\_price}$), expected retail turnover, potential unrealized gross profit, category capital distribution, and identifies dead stock (products with high stock but 0 sales over the last 60 days).
  - `getIncompleteCostProducts($startDate, $endDate, $limit = 20)`: Cost audit ledger finding zero-cost line items for financial auditing.
  - `exportFinancialCsv($startDate, $endDate)`: Streams UTF-8 BOM CSV export for Excel compatibility.
  - `resolveFinancialDateRange($preset, $customStart, $customEnd)`: Solar calendar boundary resolver (Shamsi months/year via `jalaliToGregorian()`).

### ExpenseService (`app/services/ExpenseService.php`)
- Encapsulates operational expense tracking and ledger mutations (v1.23.0 / Migration 021):
  - Extracted from `AccountingService.php` to preserve strict layer boundaries and anti-bloat ceilings (Rule 7).
  - Manages attributes: `payment_source` (bank card, gateway, petty cash), `payee` (vendor, workshop, contractor), `expense_nature` (`fixed`, `variable`, `capital`), and `receipt_image`.
  - Supports soft-archiving (`archiveExpense()`) and unarchiving/restoration (`restoreExpense()`).
  - Computes period metrics & category distribution (`getExpenseSummaryMetrics()`).
  - Converts and optimizes receipt uploads to WebP under `uploads/expenses/` (`uploadExpenseReceipt()`).
  - Streams full UTF-8 BOM CSV exports for spreadsheet analysis (`exportExpensesCsv()`).

### ShippingService (`app/services/ShippingService.php`)
- Evaluates rules in `shipping_methods` ordered by `sort_order`.
- Matches customer province (e.g. `province_contains: تهران`) with fallback to `default`.
- Computes customer-facing shipping fee (with support for `free_above_amount` thresholds) while tracking the store's actual postal cost (`actual_cost`).
- Tracks delivery expectations via `estimated_delivery` (e.g. "۲ الی ۴ روز کاری (پست پیشتاز)"), passed directly to checkout calculations and customer summaries.
- Computes logistics KPIs (`getShippingSummaryMetrics()`) including active method counts, average customer fee, average courier cost, and net unit shipping subsidy.
- Supports atomic reordering (`reorderShippingMethods()`) and instant status toggling (`toggleShippingMethodActive()`).

---

## 4. Shopping Cart & Price Guarantee

The shopping cart supports two operational modes through a unified API (`cartAdd`, `cartUpdateQty`, `cartRemove`, `cartDetails`):

1. **Guest Shoppers (Session-Based)**:
   - Stored in `$_SESSION['cart']`.
   - Stores only `product_id`, `variant_id`, and `qty`.
   - Prices and stock are always re-fetched live from the database.
2. **Authenticated Customers (Database-Backed)**:
   - Stored in the `cart_items` table with cross-device persistence.
   - Stores a `locked_unit_price` set at the moment the item was added.
3. **7-Day Price Guarantee**:
   - `cartDetailsForCustomer()` checks if the cart age (`now - MIN(added_at)`) is within the admin-configurable window (`price_guarantee_days`, default: 7).
   - If valid, the customer receives the `locked_unit_price`. If expired, live prices apply.
   - Stock is always checked live; only the unit price is guaranteed.
4. **Cart Merging**:
   - When a guest signs in or creates an account, `mergeGuestCartIntoCustomerCart()` merges session items into `cart_items` without losing existing items.

---

## 5. Store Operational Policies & Banking Infrastructure (v1.24.0)

Configurable operational parameters managed via the Settings Hub (`SettingService.php`, Migration 022):
- **Store Vacation / Pause Mode**: `store_order_status` ('active' | 'paused') disables checkout while keeping the catalog browsable, rendering an alert banner across the cart.
- **Minimum Order Value**: `min_order_amount` enforces an order subtotal floor prior to entering checkout.
- **Card-to-Card Expiration Window**: `c2c_timeout_hours` specifies the grace period for receipt upload.
- **Banking & IBAN**: `store_shaba` (24-digit IBAN) and `store_bank_name` are dynamically rendered on the customer payment card and invoice print sheets.
- **Critical Low-Stock Threshold**: `low_stock_threshold` sets the system-wide threshold for stock shortage warnings.
