-- ============================================================
-- Migration 003 — upgrade from version 1.1.0 to 1.2.0
-- Run this script once through phpMyAdmin (SQL tab) against the current database.
-- No data is deleted; only new tables/columns are added and initialized.
--
-- Changes in this version:
--   1) Customer accounts by mobile number       -> new customers table
--   2) Persistent cart for authenticated users  -> new cart_items table
--   3) Cart price guarantee for a configurable period -> settings table
--   4) Super-admin order deletion                 -> no schema change (application logic only)
--   5) Subcategories                               -> parent_id column in categories
--   6) Required unique SKU for all products       -> backfill + UNIQUE KEY on products.sku
--   7) Optional customer-to-order relation       -> customer_id column in orders
-- ============================================================

SET NAMES utf8mb4;

-- ---------- 1) Customer accounts ----------
CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 2) Persistent cart (authenticated customers only) ----------
-- Note: variant_id uses 0 instead of NULL for products without variants because
-- MySQL does not treat multiple NULL values in a UNIQUE KEY as duplicates,
-- while 0 is a concrete value that prevents duplicate cart rows for the same product.
CREATE TABLE IF NOT EXISTS cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 1,
    locked_unit_price DECIMAL(12,0) NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_customer_product_variant (customer_id, product_id, variant_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 3) Store settings (general key/value storage for price guarantees and future options) ----------
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (setting_key, setting_value) VALUES
    ('price_guarantee_enabled', '1'),
    ('price_guarantee_days', '7')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ---------- 5) Subcategories ----------
ALTER TABLE categories ADD COLUMN parent_id INT UNSIGNED DEFAULT NULL AFTER id;
ALTER TABLE categories ADD FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL;
ALTER TABLE categories ADD INDEX idx_parent (parent_id);

-- ---------- 6) Required unique SKU ----------
-- Generate a unique fallback SKU for products that do not have one.
SET @cnt = 0;
UPDATE products SET sku = CONCAT('SOCK-', LPAD((@cnt:=@cnt+1) + 10000, 6, '0')) WHERE sku IS NULL OR sku = '';
-- Enforce SKU uniqueness at the database level.
ALTER TABLE products ADD UNIQUE KEY uniq_sku (sku);

-- ---------- 7) Optional order-to-customer relation; guest checkout remains supported ----------
ALTER TABLE orders ADD COLUMN customer_id INT UNSIGNED DEFAULT NULL AFTER id;
ALTER TABLE orders ADD FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL;
ALTER TABLE orders ADD INDEX idx_customer (customer_id);

-- End of Migration 003
