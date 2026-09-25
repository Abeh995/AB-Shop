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

### GiftService (`app/services/GiftService.php`)
- Manages a unified catalog (`gift_items`) that supports two distinct capabilities on the same physical entity:
  - `is_giftable`: Free gift allocated to an order by an admin from `order_detail.php`.
  - `is_post_orderable`: Paid upsell add-on presented to the customer during cart/checkout.
- Snapshots cost and selling prices into `order_gift_items`.

### AccountingService (`app/services/AccountingService.php`)
- **Read-Only Reporting**: Never mutates the database.
- Calculates order-level gross profitability (`getOrderProfitability($orderId)`):
  $$\text{Gross Profit} = (\text{Order Items Total} - \text{Discount}) + \text{Post-Order Upsells} + \text{Shipping Collected} - \text{Total Cost of Goods Sold} - \text{Actual Shipping Cost}$$
- Aggregates financial performance (`getFinancialSummary($startDate, $endDate)`) incorporating operational expenses from the `expenses` ledger.

### ShippingService (`app/services/ShippingService.php`)
- Evaluates rules in `shipping_methods` ordered by `sort_order`.
- Matches customer province (e.g. `province_contains: تهران`) with fallback to `default`.
- Computes customer-facing shipping fee (with support for `free_above_amount` thresholds) while tracking the store's actual postal cost (`actual_cost`).

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
