-- ============================================================
-- Migration 008 — upgrade from v1.5.0 (theme system) to v1.5.0 pricing
-- Run this once against the existing production database (phpMyAdmin > SQL tab).
--
-- Adds cost-price tracking and a full audit trail for every price change:
--   1) `products.cost_price` / `product_variants.cost_price` — what the
--      store paid, independent of the sale price. Nullable: existing
--      products have no cost data yet and must be backfilled by an admin.
--   2) `bulk_price_operations` — one row per bulk price-change request
--      (arbitrary product selection, not tied to a category).
--   3) `price_history` — one immutable row per individual price change,
--      whether it came from editing a single product or from a bulk
--      operation. Stores both a live `variant_id` reference (nulled, never
--      cascaded, if the variant is later removed) and a `variant_label`
--      text snapshot, so a record stays meaningful even if the live variant
--      row is gone — the same pattern already used by `order_items`.
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE products
    ADD COLUMN cost_price DECIMAL(12,0) DEFAULT NULL AFTER discount_price;

ALTER TABLE product_variants
    ADD COLUMN cost_price DECIMAL(12,0) DEFAULT NULL AFTER price_override;

CREATE TABLE IF NOT EXISTS bulk_price_operations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    field_changed ENUM('cost_price','sale_price') NOT NULL,
    method ENUM('fixed_amount','percentage','direct_value') NOT NULL,
    requested_change VARCHAR(40) NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    product_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE RESTRICT,
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS price_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED DEFAULT NULL,
    variant_label VARCHAR(80) DEFAULT NULL,
    field_changed ENUM('cost_price','sale_price') NOT NULL,
    previous_value DECIMAL(12,0) DEFAULT NULL,
    new_value DECIMAL(12,0) NOT NULL,
    change_amount DECIMAL(12,0) NOT NULL,
    change_percentage DECIMAL(8,4) DEFAULT NULL,
    method ENUM('fixed_amount','percentage','direct_value') NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    bulk_operation_id INT UNSIGNED DEFAULT NULL,
    admin_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    FOREIGN KEY (bulk_operation_id) REFERENCES bulk_price_operations(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE RESTRICT,
    INDEX idx_product (product_id),
    INDEX idx_bulk (bulk_operation_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- End of Migration 008
