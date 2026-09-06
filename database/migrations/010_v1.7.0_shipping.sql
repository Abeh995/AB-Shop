-- ============================================================
-- Migration 010 — upgrade from v1.6.0 to v1.7.0
-- Run this once against the existing production database (phpMyAdmin > SQL tab).
--
-- Introduces shipping cost calculation. checkout.php has hardcoded
-- $shippingCost = 0 since the first release; this replaces that with a
-- small rule table instead of another hardcoded constant.
--
-- A method matches a customer's order in one of two ways:
--   - match_type = 'province_contains': applies when the customer's
--     (free-text) province field contains match_value (e.g. "تهران").
--   - match_type = 'default': applies when no more specific method matched
--     — the fallback for everything else.
-- Methods are checked in sort_order, so specific rules can be placed ahead
-- of the fallback. free_above_amount, when set, makes a method free once
-- the order subtotal reaches it. This is deliberately simpler than a full
-- per-city or weight-based system — provinces/cities are free-text input
-- today, and products don't carry a weight — but match_type is an enum
-- specifically so a 'city_contains' or future 'weight_tier' value can be
-- added later without restructuring the table.
--
-- Two starter methods are seeded, matching the shipping copy already
-- shown in the site footer ("courier for Tehran, post for other cities"),
-- both at zero cost until an admin sets real prices.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS shipping_methods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    match_type ENUM('province_contains','default') NOT NULL DEFAULT 'default',
    match_value VARCHAR(100) DEFAULT NULL,
    cost DECIMAL(12,0) NOT NULL DEFAULT 0,
    free_above_amount DECIMAL(12,0) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO shipping_methods (name, description, match_type, match_value, cost, is_active, sort_order) VALUES
    ('پیک تهران', 'ارسال با پیک موتوری برای سفارش‌های داخل تهران', 'province_contains', 'تهران', 0, 1, 1),
    ('پست سایر شهرها', 'ارسال با پست پیشتاز برای بقیه شهرها', 'default', NULL, 0, 1, 2);

-- Which shipping method was actually applied to an order, kept for the
-- same reason order_items/order_gift_items snapshot their own data:
-- shipping_methods.cost can change later without altering a placed order.
ALTER TABLE orders
    ADD COLUMN shipping_method_name VARCHAR(100) DEFAULT NULL AFTER shipping_cost;

-- End of Migration 010
